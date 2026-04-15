@echo off
echo ========================================
echo  ITQuty - Let's Encrypt SSL Setup
echo ========================================
echo.

REM Check if Chocolatey is installed
choco --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Installing Chocolatey...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))"
    echo Chocolatey installed. Please restart command prompt and run this script again.
    pause
    exit /b 1
)

echo Installing Certbot...
choco install certbot -y

echo.
echo Certbot installed successfully!
echo.
echo Next steps:
echo 1. Make sure your domain points to this server
echo 2. Ensure port 80 is open and accessible
echo 3. Run the certificate generation script
echo.
pause