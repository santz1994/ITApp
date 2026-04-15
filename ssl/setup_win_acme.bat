@echo off
echo ========================================
echo  Win-ACME Setup untuk Let's Encrypt
echo ========================================
echo.

REM Check if domain is provided
if "%1"=="" (
    echo Usage: %0 yourdomain.com
    echo Example: %0 example.com
    pause
    exit /b 1
)

set DOMAIN=%1
set WINACME_DIR=C:\win-acme
set WEBROOT=C:\xampp\htdocs\quty2\public

echo Domain: %DOMAIN%
echo Web Root: %WEBROOT%
echo Win-ACME Directory: %WINACME_DIR%
echo.

REM Create Win-ACME directory
if not exist "%WINACME_DIR%" mkdir "%WINACME_DIR%"

REM Download Win-ACME
echo Downloading Win-ACME...
powershell -Command "Invoke-WebRequest -Uri 'https://github.com/win-acme/win-acme/releases/download/v2.2.9.1701/win-acme.v2.2.9.1701.x64.pluggable.zip' -OutFile '%WINACME_DIR%\win-acme.zip'"

REM Extract Win-ACME
echo Extracting Win-ACME...
powershell -Command "Expand-Archive -Path '%WINACME_DIR%\win-acme.zip' -DestinationPath '%WINACME_DIR%' -Force"

REM Create unattended script for Win-ACME
echo Creating Win-ACME configuration script...
(
echo ; Win-ACME configuration for %DOMAIN%
echo [Settings]
echo ClientName = %DOMAIN%
echo.
echo [ScheduledTask]
echo Enabled = true
echo.
echo [Acme]
echo BaseUri = https://acme-v02.api.letsencrypt.org/
echo.
echo [Target]
echo Plugin = Manual
echo.
echo [Manual]
echo Host = %DOMAIN%
echo WebRootPath = %WEBROOT%
echo.
echo [Store]
echo Plugin = PemFiles
echo.
echo [PemFiles]
echo Path = C:\xampp\apache\conf\ssl.crt\server.crt
echo.
echo [Validation]
echo Plugin = Http-01
echo.
echo [Http-01]
echo WebRootPath = %WEBROOT%
) > "%WINACME_DIR%\config.txt"

echo.
echo Configuration created. Now run Win-ACME:
echo cd %WINACME_DIR%
echo wacs.exe --source file --file config.txt
echo.
echo After certificate is generated, restart Apache:
echo net stop apache2.4
echo net start apache2.4
echo.
pause