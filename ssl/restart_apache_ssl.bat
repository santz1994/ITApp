@echo off
echo ========================================
echo  Restart Apache untuk SSL Certificate Baru
echo ========================================
echo.
echo Certificate baru dari mkcert sudah di-copy.
echo Sekarang restart Apache di server remote.
echo.
echo Jalankan di server remote:
echo.
echo 1. Buka Command Prompt sebagai Administrator
echo 2. Jalankan commands berikut:
echo.
echo net stop apache2.4
echo net start apache2.4
echo.
echo Atau gunakan XAMPP Control Panel:
echo - Stop Apache
echo - Start Apache
echo.
echo ========================================
echo  Test HTTPS Access
echo ========================================
echo.
echo Setelah restart, akses:
echo https://192.168.1.87/
echo.
echo Sekarang TIDAK akan ada browser warning! ✅
echo.
pause