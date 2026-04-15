@echo off
echo Copying SSL certificates to local Apache...
copy "Z:\apache\conf\ssl.crt\server.crt" "C:\xampp\apache\conf\ssl.crt\server.crt"
copy "Z:\apache\conf\ssl.key\server.key" "C:\xampp\apache\conf\ssl.key\server.key"
echo.
echo Certificates copied successfully!
echo.
echo Now restart Apache on your local machine:
echo 1. Open XAMPP Control Panel
echo 2. Stop Apache
echo 3. Start Apache
echo.
echo Or run these commands in Command Prompt as Administrator:
echo net stop apache2.4
echo net start apache2.4
echo.
pause