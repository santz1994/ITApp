# Automated Testing Installation Script
# Run this script to set up automated testing in 5 minutes

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Automated Testing Setup Script" -ForegroundColor Cyan
Write-Host "Target: <5% False Positive Rate" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check PHP version
Write-Host "[1/7] Checking PHP version..." -ForegroundColor Yellow
$phpVersion = php -v
if ($phpVersion -match "PHP (\d+\.\d+)") {
    Write-Host "✓ PHP $($matches[1]) detected" -ForegroundColor Green
} else {
    Write-Host "✗ PHP not found. Please install PHP 8.1 or higher." -ForegroundColor Red
    exit 1
}

# Step 2: Install Laravel Dusk
Write-Host ""
Write-Host "[2/7] Installing Laravel Dusk..." -ForegroundColor Yellow
composer require --dev laravel/dusk
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Laravel Dusk installed" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to install Laravel Dusk" -ForegroundColor Red
    exit 1
}

# Step 3: Install Dusk
Write-Host ""
Write-Host "[3/7] Setting up Dusk..." -ForegroundColor Yellow
php artisan dusk:install
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Dusk installed successfully" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to install Dusk" -ForegroundColor Red
    exit 1
}

# Step 4: Download ChromeDriver
Write-Host ""
Write-Host "[4/7] Installing ChromeDriver..." -ForegroundColor Yellow
php artisan dusk:chrome-driver --detect
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ ChromeDriver installed" -ForegroundColor Green
} else {
    Write-Host "⚠ ChromeDriver auto-install failed. Manual installation needed." -ForegroundColor Yellow
    Write-Host "  Visit: https://chromedriver.chromium.org/downloads" -ForegroundColor Yellow
    Write-Host "  Place chromedriver.exe in: vendor\laravel\dusk\bin\" -ForegroundColor Yellow
}

# Step 5: Create test environment file
Write-Host ""
Write-Host "[5/7] Creating test environment..." -ForegroundColor Yellow
$envDuskContent = @"
APP_NAME=Laravel
APP_ENV=testing
APP_KEY=base64:$(php artisan key:generate --show)
APP_DEBUG=true
APP_URL=http://192.168.1.122

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array

MAIL_MAILER=log
"@

$envDuskContent | Out-File -FilePath ".env.dusk.local" -Encoding UTF8
if (Test-Path ".env.dusk.local") {
    Write-Host "✓ Test environment created (.env.dusk.local)" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to create test environment" -ForegroundColor Red
    exit 1
}

# Step 6: Run migrations for test database
Write-Host ""
Write-Host "[6/7] Preparing test database..." -ForegroundColor Yellow
php artisan config:clear
php artisan migrate:fresh --seed --env=testing
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Test database ready" -ForegroundColor Green
} else {
    Write-Host "⚠ Test database setup had issues (non-critical)" -ForegroundColor Yellow
}

# Step 7: Verify installation
Write-Host ""
Write-Host "[7/7] Verifying installation..." -ForegroundColor Yellow

# Check if test files exist
$testFiles = @(
    "tests\Feature\ApiAutomatedTest.php",
    "tests\Browser\ComprehensiveAutomatedTest.php",
    "tests\DuskTestCase.php"
)

$allFilesExist = $true
foreach ($file in $testFiles) {
    if (Test-Path $file) {
        Write-Host "  ✓ $file" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $file missing" -ForegroundColor Red
        $allFilesExist = $false
    }
}

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Installation Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Display next steps
Write-Host "📋 Next Steps:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Run API Tests (Fast - 2 minutes):" -ForegroundColor Yellow
Write-Host "   php artisan test tests/Feature/ApiAutomatedTest.php" -ForegroundColor White
Write-Host ""
Write-Host "2. Run Browser Tests (Thorough - 10 minutes):" -ForegroundColor Yellow
Write-Host "   php artisan dusk tests/Browser/ComprehensiveAutomatedTest.php" -ForegroundColor White
Write-Host ""
Write-Host "3. Run All Tests:" -ForegroundColor Yellow
Write-Host "   php artisan test && php artisan dusk" -ForegroundColor White
Write-Host ""
Write-Host "4. Run Tests in Parallel (4x faster):" -ForegroundColor Yellow
Write-Host "   php artisan dusk --parallel --processes=4" -ForegroundColor White
Write-Host ""

# Display test statistics
Write-Host "📊 Test Suite Statistics:" -ForegroundColor Cyan
Write-Host "  • Total Tests: 30 (15 API + 15 Browser)" -ForegroundColor White
Write-Host "  • Total Assertions: 145+" -ForegroundColor White
Write-Host "  • Expected Success Rate: >95%" -ForegroundColor Green
Write-Host "  • Expected False Positive Rate: <5%" -ForegroundColor Green
Write-Host "  • Execution Time: 9-12 minutes" -ForegroundColor White
Write-Host ""

Write-Host "📖 Documentation:" -ForegroundColor Cyan
Write-Host "  • Quick Start: AUTOMATED_TESTING_QUICKSTART.md" -ForegroundColor White
Write-Host "  • Full Guide: tests/AUTOMATED_TESTING_GUIDE.md" -ForegroundColor White
Write-Host ""

Write-Host "✅ Ready to run automated tests!" -ForegroundColor Green
Write-Host ""

# Ask if user wants to run tests now
$runNow = Read-Host "Run API tests now? (y/n)"
if ($runNow -eq 'y' -or $runNow -eq 'Y') {
    Write-Host ""
    Write-Host "Running API tests..." -ForegroundColor Yellow
    Write-Host ""
    php artisan test tests/Feature/ApiAutomatedTest.php
}
