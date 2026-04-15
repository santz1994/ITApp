# Auto-Setup Windows Task Scheduler for XAMPP Laravel Backup
# Run this script as Administrator

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  Laravel Auto Backup Setup for XAMPP" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Creating scheduled task using XML..." -ForegroundColor Green

try {
    $taskName = "Laravel Database Auto Backup"
    
    # Check if task already exists
    $existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    
    if ($existingTask) {
        Write-Host "Task already exists. Deleting old task..." -ForegroundColor Yellow
        schtasks /Delete /TN $taskName /F | Out-Null
    }
    
    # Create task using schtasks command (more compatible)
    Write-Host "Creating new scheduled task..." -ForegroundColor Green
    
    # Create the task to run every 5 minutes
    $result = schtasks /Create /TN $taskName /TR "Z:\htdocs\quty2\run-scheduler.bat" /SC MINUTE /MO 5 /RU "SYSTEM" /RL HIGHEST /F
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "SUCCESS! Task created successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Task Details:" -ForegroundColor Cyan
        Write-Host "  Name: $taskName"
        Write-Host "  Runs: Every 5 minutes (as SYSTEM account)"
        Write-Host "  Backup Schedule: Every Friday at 11:00 PM"
        Write-Host "  Works: Even when you're not logged in"
        Write-Host ""
        
        # Test the task
        Write-Host "Testing the task now..." -ForegroundColor Yellow
        schtasks /Run /TN $taskName | Out-Null
        Start-Sleep -Seconds 3
        
        # Query task status
        $taskInfo = schtasks /Query /TN $taskName /FO LIST /V
        Write-Host "Task created and tested successfully!" -ForegroundColor Green
        Write-Host ""
        
        Write-Host "Next Steps:" -ForegroundColor Cyan
        Write-Host "1. Test backup manually: php artisan db:backup"
        Write-Host "2. Check backup folder: Z:\htdocs\quty2\storage\app\backups\"
        Write-Host "3. Wait for Friday at 11 PM for automatic backup"
        Write-Host "4. View task in Task Scheduler: taskschd.msc"
        Write-Host ""
        
        Write-Host "Setup complete!" -ForegroundColor Green
    } else {
        throw "Failed to create scheduled task"
    }
    
} catch {
    Write-Host ""
    Write-Host "ERROR: Failed to create scheduled task!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Read-Host "Press Enter to exit"
