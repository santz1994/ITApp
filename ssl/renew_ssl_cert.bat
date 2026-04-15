@echo off
echo ========================================
echo  ITQuty - Let's Encrypt Certificate Renewal
echo ========================================
echo.

REM Stop Apache
echo Stopping Apache...
net stop apache2.4 >nul 2>&1

REM Renew certificates
echo Renewing certificates...
certbot renew

REM Copy renewed certificates
echo Copying renewed certificates...
for /d %%i in ("C:\ProgramData\letsencrypt\live\*") do (
    if exist "%%i\fullchain.pem" (
        copy "%%i\fullchain.pem" "C:\xampp\apache\conf\ssl.crt\server.crt"
        copy "%%i\privkey.pem" "C:\xampp\apache\conf\ssl.key\server.key"
        echo Certificates updated for domain: %%~nxi
    )
)

REM Start Apache
echo Starting Apache...
net start apache2.4

echo.
echo Certificate renewal complete!
echo.
pause