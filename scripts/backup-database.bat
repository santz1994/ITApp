@echo off
REM ===================================================================
REM DATABASE BACKUP AUTOMATION SCRIPT
REM Automated MySQL Database Backup for ITQuty2
REM ===================================================================

SET TIMESTAMP=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
SET TIMESTAMP=%TIMESTAMP: =0%
SET BACKUP_DIR=Z:\htdocs\quty2\storage\backups\database
SET DB_HOST=192.168.1.87
SET DB_PORT=3306
SET DB_NAME=itquty
SET DB_USER=itquty_user
SET DB_PASS=itquty123
SET MYSQL_BIN=C:\xampp\mysql\bin
SET BACKUP_FILE=%BACKUP_DIR%\backup_%DB_NAME%_%TIMESTAMP%.sql
SET LOG_FILE=%BACKUP_DIR%\backup_log.txt

REM Create backup directory if not exists
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Log start
echo [%date% %time%] Starting database backup... >> "%LOG_FILE%"

REM Perform backup
"%MYSQL_BIN%\mysqldump.exe" --host=%DB_HOST% --port=%DB_PORT% --user=%DB_USER% --password=%DB_PASS% --single-transaction --routines --triggers --events %DB_NAME% > "%BACKUP_FILE%" 2>&1

if %ERRORLEVEL% EQU 0 (
    echo [%date% %time%] Backup successful: %BACKUP_FILE% >> "%LOG_FILE%"
    
    REM Compress backup
    "%MYSQL_BIN%\..\..\..\php\php.exe" -r "if(file_exists('%BACKUP_FILE%')){$gz=gzopen('%BACKUP_FILE%.gz','w9');$fp=fopen('%BACKUP_FILE%','r');while($data=fread($fp,1024*1024)){gzwrite($gz,$data);}fclose($fp);gzclose($gz);unlink('%BACKUP_FILE%');echo 'Compressed to %BACKUP_FILE%.gz';}" >> "%LOG_FILE%" 2>&1
    
    REM Delete backups older than 30 days
    forfiles /P "%BACKUP_DIR%" /M backup_*.sql.gz /D -30 /C "cmd /c del @path" 2>nul
    
    echo [%date% %time%] Backup completed successfully >> "%LOG_FILE%"
    echo Backup completed: %BACKUP_FILE%.gz
) else (
    echo [%date% %time%] ERROR: Backup failed! >> "%LOG_FILE%"
    echo ERROR: Backup failed!
    exit /b 1
)

REM Keep only last 10 backup log entries
powershell -Command "if(Test-Path '%LOG_FILE%'){$content=Get-Content '%LOG_FILE%' -Tail 50;Set-Content '%LOG_FILE%' $content}"

exit /b 0
