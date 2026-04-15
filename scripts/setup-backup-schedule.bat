@echo off
REM ===================================================================
REM SCHEDULE DATABASE BACKUPS USING WINDOWS TASK SCHEDULER
REM Run this script once to setup automatic daily backups
REM ===================================================================

SET SCRIPT_PATH=Z:\htdocs\quty2\scripts\backup-database.bat
SET TASK_NAME=ITQuty_DatabaseBackup_Daily

echo.
echo ===================================================================
echo Setting up automatic database backup...
echo ===================================================================
echo.

REM Delete existing task if present
schtasks /Query /TN "%TASK_NAME%" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo Removing existing scheduled task...
    schtasks /Delete /TN "%TASK_NAME%" /F
)

REM Create new scheduled task (Daily at 2:00 AM)
echo Creating new scheduled task...
schtasks /Create /TN "%TASK_NAME%" /TR "\"%SCRIPT_PATH%\"" /SC DAILY /ST 02:00 /RU "SYSTEM" /F

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ===================================================================
    echo ✓ SUCCESS: Automatic backup configured!
    echo ===================================================================
    echo.
    echo Task Name: %TASK_NAME%
    echo Schedule:  Daily at 2:00 AM
    echo Script:    %SCRIPT_PATH%
    echo.
    echo To modify schedule:
    echo   1. Open Task Scheduler (taskschd.msc)
    echo   2. Find "%TASK_NAME%"
    echo   3. Right-click ^> Properties
    echo.
    echo To manually trigger backup now:
    echo   schtasks /Run /TN "%TASK_NAME%"
    echo.
) else (
    echo.
    echo ===================================================================
    echo ✗ ERROR: Failed to create scheduled task!
    echo ===================================================================
    echo.
    echo Please run this script as Administrator
    echo.
)

pause
