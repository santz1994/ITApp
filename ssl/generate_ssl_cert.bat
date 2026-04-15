@echo off
echo ========================================
echo  ITQuty - Let's Encrypt Certificate Generation
echo ========================================
echo.

set /p DOMAIN="Enter your domain name (e.g., yourdomain.com): "
if "%DOMAIN%"=="" (
    echo Domain name is required!
    pause
    exit /b 1
)

echo.
echo Generating SSL certificate for: %DOMAIN%
echo.

REM Stop Apache temporarily
echo Stopping Apache...
net stop apache2.4 >nul 2>&1

REM Generate certificate using webroot method
certbot certonly --webroot -w "C:\xampp\htdocs\quty2\public" -d %DOMAIN% --agree-tos --email admin@%DOMAIN%

if %errorlevel% neq 0 (
    echo.
    echo Certificate generation failed!
    echo Make sure:
    echo - Domain %DOMAIN% points to this server
    echo - Port 80 is open and accessible
    echo - Apache is stopped during generation
    echo.
    pause
    exit /b 1
)

echo.
echo Certificate generated successfully!
echo.

REM Copy certificates to Apache directory
echo Copying certificates to Apache...
copy "C:\ProgramData\letsencrypt\live\%DOMAIN%\fullchain.pem" "C:\xampp\apache\conf\ssl.crt\server.crt"
copy "C:\ProgramData\letsencrypt\live\%DOMAIN%\privkey.pem" "C:\xampp\apache\conf\ssl.key\server.key"

echo.
echo Certificates copied to Apache directory.
echo.

REM Update Apache SSL configuration
echo Updating Apache SSL configuration...
powershell -Command "(Get-Content 'C:\xampp\apache\conf\extra\httpd-ssl.conf') | ForEach-Object { $_ -replace 'SSLCertificateFile.*', 'SSLCertificateFile \"conf/ssl.crt/server.crt\"' -replace 'SSLCertificateKeyFile.*', 'SSLCertificateKeyFile \"conf/ssl.key/server.key\"' } | Set-Content 'C:\xampp\apache\conf\extra\httpd-ssl.conf'"

echo.
echo Starting Apache...
net start apache2.4

echo.
echo ========================================
echo  Setup Complete!
echo ========================================
echo.
echo Your site is now accessible at:
echo HTTPS: https://%DOMAIN%
echo HTTP:  http://%DOMAIN% (redirects to HTTPS)
echo.
echo Certificate auto-renews every 3 months.
echo.
echo To test renewal: certbot renew --dry-run
echo.
pause