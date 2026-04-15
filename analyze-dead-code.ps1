# Dead Code Analysis Script for PHP Controllers
# Analyzes all controllers, extracts public methods, searches for references
# Generates a comprehensive report of unused methods and controllers

$ErrorActionPreference = "Continue"
$WarningPreference = "SilentlyContinue"

# Configuration
$controllersPath = "app\Http\Controllers"
$routesPath = "routes"
$viewsPath = "resources\views"
$outputFile = "dead-code-analysis-report.md"

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "PHP CONTROLLER DEAD CODE ANALYSIS" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Initialize results
$allControllers = @()
$unusedMethods = @()
$unusedControllers = @()
$debugOnlyMethods = @()
$totalMethodsCount = 0
$referencedMethodsCount = 0

# Get all controller files
Write-Host "[1/5] Scanning controller files..." -ForegroundColor Yellow
$controllerFiles = Get-ChildItem -Path $controllersPath -Filter "*.php" -Recurse

Write-Host "Found $($controllerFiles.Count) controller files" -ForegroundColor Green
Write-Host ""

# Function to extract public methods from a PHP file
function Get-PublicMethods {
    param($filePath)
    
    $methods = @()
    $content = Get-Content $filePath -Raw
    
    # Match public function declarations
    $pattern = 'public\s+function\s+(\w+)\s*\('
    $matches = [regex]::Matches($content, $pattern)
    
    foreach ($match in $matches) {
        $methodName = $match.Groups[1].Value
        # Skip magic methods and constructor
        if ($methodName -notmatch '^__' -and $methodName -ne 'middleware') {
            # Get line number
            $beforeMatch = $content.Substring(0, $match.Index)
            $lineNumber = ($beforeMatch -split "`n").Count
            
            $methods += @{
                Name = $methodName
                Line = $lineNumber
            }
        }
    }
    
    return $methods
}

# Function to search for method references in files
function Search-MethodReferences {
    param(
        $controllerName,
        $methodName,
        $searchPaths
    )
    
    $references = @{
        Routes = @()
        Views = @()
        DebugRoutes = @()
    }
    
    # Search in route files
    $routeFiles = Get-ChildItem -Path $routesPath -Filter "*.php" -Recurse
    foreach ($routeFile in $routeFiles) {
        $content = Get-Content $routeFile.FullName -Raw
        
        # Check for various reference patterns
        $patterns = @(
            "$controllerName::class,\s*['\`"]$methodName['\`"]",
            "$controllerName@$methodName",
            "\\$controllerName::class,\s*['\`"]$methodName['\`"]",
            "$controllerName',\s*['\`"]$methodName['\`"]"
        )
        
        foreach ($pattern in $patterns) {
            if ($content -match $pattern) {
                $refType = if ($routeFile.Name -eq "debug.php") { "Debug" } else { "Normal" }
                if ($refType -eq "Debug") {
                    $references.DebugRoutes += $routeFile.Name
                } else {
                    $references.Routes += $routeFile.Name
                }
                break
            }
        }
    }
    
    # Search in Blade views
    $viewFiles = Get-ChildItem -Path $viewsPath -Filter "*.blade.php" -Recurse -ErrorAction SilentlyContinue
    foreach ($viewFile in $viewFiles) {
        $content = Get-Content $viewFile.FullName -Raw -ErrorAction SilentlyContinue
        
        # Look for route(), action(), ajax calls with the method name
        $patterns = @(
            "route\(['\`"][^'`"]*\.$methodName['\`"]",
            "action\(['\`"][^@]*@$methodName['\`"]",
            "url:.*/$methodName",
            "['\`"]/$methodName['\`"]"
        )
        
        foreach ($pattern in $patterns) {
            if ($content -match $pattern) {
                $references.Views += $viewFile.Name
                break
            }
        }
    }
    
    return $references
}

# Analyze each controller
Write-Host "[2/5] Analyzing controllers and extracting methods..." -ForegroundColor Yellow
$progressCount = 0

foreach ($controllerFile in $controllerFiles) {
    $progressCount++
    $percentComplete = ($progressCount / $controllerFiles.Count) * 100
    Write-Progress -Activity "Analyzing Controllers" -Status "$progressCount of $($controllerFiles.Count)" -PercentComplete $percentComplete
    
    $relativePath = $controllerFile.FullName.Replace((Get-Location).Path + "\", "")
    $className = $controllerFile.BaseName
    
    # Skip base Controller class
    if ($className -eq "Controller") {
        continue
    }
    
    $methods = Get-PublicMethods -filePath $controllerFile.FullName
    $totalMethodsCount += $methods.Count
    
    $controllerInfo = @{
        Name = $className
        Path = $relativePath
        Methods = $methods
        MethodCount = $methods.Count
        ReferencedMethods = @()
    }
    
    $allControllers += $controllerInfo
}

Write-Progress -Activity "Analyzing Controllers" -Completed
Write-Host "Extracted $totalMethodsCount methods from $($allControllers.Count) controllers" -ForegroundColor Green
Write-Host ""

# Search for references
Write-Host "[3/5] Searching for method references..." -ForegroundColor Yellow
Write-Host "This may take a few minutes..." -ForegroundColor Gray
$progressCount = 0

foreach ($controller in $allControllers) {
    $progressCount++
    $percentComplete = ($progressCount / $allControllers.Count) * 100
    Write-Progress -Activity "Searching References" -Status "$progressCount of $($allControllers.Count) - $($controller.Name)" -PercentComplete $percentComplete
    
    foreach ($method in $controller.Methods) {
        $refs = Search-MethodReferences -controllerName $controller.Name -methodName $method.Name -searchPaths @($routesPath, $viewsPath)
        
        $hasReferences = ($refs.Routes.Count -gt 0) -or ($refs.Views.Count -gt 0) -or ($refs.DebugRoutes.Count -gt 0)
        
        if ($hasReferences) {
            $controller.ReferencedMethods += $method.Name
            $referencedMethodsCount++
        }
        
        # Check if method is ONLY referenced in debug routes
        if (($refs.DebugRoutes.Count -gt 0) -and ($refs.Routes.Count -eq 0) -and ($refs.Views.Count -eq 0)) {
            $debugOnlyMethods += @{
                Controller = $controller.Name
                Method = $method.Name
                Line = $method.Line
                Path = $controller.Path
            }
        }
        
        # Check if method has NO references
        if (-not $hasReferences) {
            $unusedMethods += @{
                Controller = $controller.Name
                Method = $method.Name
                Line = $method.Line
                Path = $controller.Path
            }
        }
    }
    
    # Check if controller has NO referenced methods at all
    if ($controller.ReferencedMethods.Count -eq 0 -and $controller.MethodCount -gt 0) {
        $unusedControllers += @{
            Name = $controller.Name
            Path = $controller.Path
            MethodCount = $controller.MethodCount
        }
    }
}

Write-Progress -Activity "Searching References" -Completed
Write-Host "Found $referencedMethodsCount referenced methods" -ForegroundColor Green
Write-Host "Found $($unusedMethods.Count) unused methods" -ForegroundColor Cyan
Write-Host "Found $($unusedControllers.Count) completely unused controllers" -ForegroundColor Cyan
Write-Host "Found $($debugOnlyMethods.Count) debug-only methods" -ForegroundColor Cyan
Write-Host ""

# Generate Report
Write-Host "[4/5] Generating report..." -ForegroundColor Yellow

$report = @"
# Dead Code Analysis Report
**Generated:** $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## Executive Summary

- **Total Controllers Analyzed:** $($allControllers.Count)
- **Total Public Methods Found:** $totalMethodsCount
- **Referenced Methods:** $referencedMethodsCount
- **Unused Methods:** $($unusedMethods.Count) ($([math]::Round(($unusedMethods.Count / $totalMethodsCount) * 100, 2))%)
- **Completely Unused Controllers:** $($unusedControllers.Count)
- **Debug-Only Methods (Production Candidates):** $($debugOnlyMethods.Count)

---

## 1. Completely Unused Controllers

These controllers have NO methods referenced anywhere in routes or views:

"@

if ($unusedControllers.Count -eq 0) {
    $report += "`n✅ **No completely unused controllers found!**`n"
} else {
    $report += "`n| Controller | Path | Methods Count |`n"
    $report += "|------------|------|---------------|`n"
    foreach ($uc in $unusedControllers | Sort-Object Name) {
        $report += "| ``$($uc.Name)`` | $($uc.Path) | $($uc.MethodCount) |`n"
    }
}

$report += "`n---`n`n## 2. Unused Methods (Dead Code)`n`n"
$report += "These methods are never referenced in any route or view file:`n`n"

if ($unusedMethods.Count -eq 0) {
    $report += "✅ **No unused methods found!**`n"
} else {
    # Group by controller
    $groupedUnused = $unusedMethods | Group-Object -Property Controller
    
    foreach ($group in $groupedUnused | Sort-Object Name) {
        $report += "### $($group.Name)`n`n"
        $report += "| Method | Line | Path |`n"
        $report += "|--------|------|------|`n"
        foreach ($method in $group.Group | Sort-Object Line) {
            $report += "| ``$($method.Method)`` | $($method.Line) | $($method.Path) |`n"
        }
        $report += "`n"
    }
}

$report += "---`n`n## 3. Debug-Only Methods (Candidates for Production Removal)`n`n"
$report += "These methods are ONLY referenced in debug.php routes:`n`n"

if ($debugOnlyMethods.Count -eq 0) {
    $report += "✅ **No debug-only methods found!**`n"
} else {
    # Group by controller
    $groupedDebug = $debugOnlyMethods | Group-Object -Property Controller
    
    foreach ($group in $groupedDebug | Sort-Object Name) {
        $report += "### $($group.Name)`n`n"
        $report += "| Method | Line | Path |`n"
        $report += "|--------|------|------|`n"
        foreach ($method in $group.Group | Sort-Object Line) {
            $report += "| ``$($method.Method)`` | $($method.Line) | $($method.Path) |`n"
        }
        $report += "`n"
    }
}

$report += @"

---

## 4. Detailed Controller Statistics

| Controller | Total Methods | Referenced | Unused | Usage % |
|------------|---------------|------------|--------|---------|
"@

foreach ($controller in $allControllers | Sort-Object Name) {
    $unused = $controller.MethodCount - $controller.ReferencedMethods.Count
    $usagePercent = if ($controller.MethodCount -gt 0) { 
        [math]::Round(($controller.ReferencedMethods.Count / $controller.MethodCount) * 100, 1) 
    } else { 
        0 
    }
    $report += "`n| ``$($controller.Name)`` | $($controller.MethodCount) | $($controller.ReferencedMethods.Count) | $unused | $usagePercent% |"
}

$report += @"


---

## Recommendations

### High Priority (Safe to Remove)
1. **Completely Unused Controllers:** $($unusedControllers.Count) controllers can be safely deleted
2. **Unused Methods:** $($unusedMethods.Count) methods are dead code and can be removed

### Medium Priority (Verify Before Removal)
3. **Debug-Only Methods:** $($debugOnlyMethods.Count) methods should be reviewed for production deployment

### Action Plan
1. Review each unused controller to confirm it's not dynamically loaded
2. Remove unused methods to improve code maintainability
3. Consider moving debug-only methods to separate test files
4. Run full test suite after cleanup to ensure no breakage

---

**Analysis Complete** ✅
"@

# Write report to file
$report | Out-File -FilePath $outputFile -Encoding UTF8

Write-Host "Report saved to: $outputFile" -ForegroundColor Green
Write-Host ""

# Display summary
Write-Host "[5/5] Summary" -ForegroundColor Yellow
Write-Host ""
Write-Host "CRITICAL - Completely Unused Controllers:" -ForegroundColor Red
if ($unusedControllers.Count -eq 0) {
    Write-Host "  None found" -ForegroundColor Green
} else {
    foreach ($uc in $unusedControllers | Select-Object -First 5) {
        Write-Host "  - $($uc.Name) ($($uc.MethodCount) methods)" -ForegroundColor Red
    }
    if ($unusedControllers.Count -gt 5) {
        Write-Host "  ... and $($unusedControllers.Count - 5) more (see report)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "HIGH PRIORITY - Top Unused Methods:" -ForegroundColor Yellow
$topUnused = $unusedMethods | Group-Object -Property Controller | Sort-Object Count -Descending | Select-Object -First 5
foreach ($group in $topUnused) {
    Write-Host "  - $($group.Name): $($group.Count) unused methods" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "MEDIUM PRIORITY - Debug-Only Methods:" -ForegroundColor Cyan
if ($debugOnlyMethods.Count -eq 0) {
    Write-Host "  None found" -ForegroundColor Green
} else {
    $debugControllers = $debugOnlyMethods | Group-Object -Property Controller
    foreach ($group in $debugControllers | Select-Object -First 5) {
        Write-Host "  - $($group.Name): $($group.Count) methods" -ForegroundColor Cyan
    }
}

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "Analysis complete! Check $outputFile for full details." -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Cyan
