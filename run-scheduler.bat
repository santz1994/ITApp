@echo off
REM Laravel Scheduler Runner for XAMPP
REM This runs the Laravel scheduler which handles automatic backups

REM Add XAMPP PHP to PATH
SET PATH=Z:\php;%PATH%

REM Go to Laravel directory
cd /d "Z:\htdocs\quty2"

REM Run Laravel scheduler
Z:\php\php.exe artisan schedule:run >> NUL 2>&1
