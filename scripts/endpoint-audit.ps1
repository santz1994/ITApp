$ErrorActionPreference = 'Stop'

$routes = php artisan route:list --json | ConvertFrom-Json
$namedRoutes = $routes | Where-Object { $_.name -and $_.name.Trim() -ne '' }
$routeNames = @($namedRoutes | ForEach-Object { $_.name })

$references = New-Object System.Collections.Generic.List[string]
$patterns = @(
    'resources/views/**/*.blade.php',
    'app/**/*.php'
)

$routeLiteralPattern = 'route\([''"][A-Za-z0-9_.-]+[''"]'
$routeCapturePattern = 'route\([''"]([A-Za-z0-9_.-]+)[''"]'

$rgCommand = Get-Command rg -ErrorAction SilentlyContinue

if ($rgCommand) {
    foreach ($pattern in $patterns) {
        rg --no-heading --line-number --glob $pattern $routeLiteralPattern | ForEach-Object {
            if ($_ -match $routeCapturePattern) {
                $references.Add($matches[1]) | Out-Null
            }
        }
    }
}
else {
    $searchFiles = Get-ChildItem -Path @('resources/views', 'app') -Recurse -File -ErrorAction SilentlyContinue |
    Where-Object { $_.FullName -like '*.blade.php' -or $_.FullName -like '*.php' }

    foreach ($file in $searchFiles) {
        Select-String -Path $file.FullName -Pattern $routeCapturePattern -AllMatches -ErrorAction SilentlyContinue | ForEach-Object {
            foreach ($match in $_.Matches) {
                if ($match.Groups.Count -gt 1 -and $match.Groups[1].Value) {
                    $references.Add($match.Groups[1].Value) | Out-Null
                }
            }
        }
    }
}

$refUnique = @($references | Sort-Object -Unique)
$missingRefs = @($refUnique | Where-Object { $routeNames -notcontains $_ })
$unusedNamed = @($routeNames | Where-Object { $refUnique -notcontains $_ } | Sort-Object -Unique)

$report = [ordered]@{
    generated_at                   = (Get-Date).ToString('s')
    total_routes                   = $routes.Count
    named_routes                   = $namedRoutes.Count
    literal_route_references       = $refUnique.Count
    missing_referenced_route_names = $missingRefs
    potential_unused_named_routes  = $unusedNamed
}

New-Item -ItemType Directory -Path 'storage/app' -Force | Out-Null
$reportPath = 'storage/app/endpoint-audit-latest.json'
$report | ConvertTo-Json -Depth 6 | Set-Content -Path $reportPath

Write-Output ("total_routes=" + $routes.Count)
Write-Output ("named_routes=" + $namedRoutes.Count)
Write-Output ("literal_route_references=" + $refUnique.Count)
Write-Output ("missing_refs_count=" + $missingRefs.Count)
if ($missingRefs.Count -gt 0) {
    Write-Output 'missing_refs_sample:'
    $missingRefs | Select-Object -First 30 | ForEach-Object { Write-Output (" - " + $_) }
}
Write-Output ("potential_unused_named_routes_count=" + $unusedNamed.Count)
Write-Output 'potential_unused_named_routes_sample:'
$unusedNamed | Select-Object -First 40 | ForEach-Object { Write-Output (" - " + $_) }
Write-Output ("report_path=" + $reportPath)
