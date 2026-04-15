# Database Backup Automation - Setup Guide

## Overview

Automated database backup system for ITQuty Laravel application with three implementation methods:
1. Windows Batch Script (standalone)
2. Laravel Artisan Command
3. Windows Task Scheduler (automated daily backups)

---

## Prerequisites

- **XAMPP MySQL** installed (mysqldump available)
- **Windows Server** access with Administrator privileges
- **PHP CLI** access
- **Sufficient disk space** for backups (recommend 10GB minimum)

---

## Files Created

### 1. Windows Batch Script
**Location:** `scripts/backup-database.bat`
- Standalone MySQL backup script
- Creates compressed `.sql.gz` files
- 30-day retention policy
- Logs to `storage/backups/database/backup_log.txt`

### 2. Laravel Artisan Command
**Location:** `app/Console/Commands/BackupDatabase.php`
- Artisan command: `php artisan backup:database`
- Auto-detects mysqldump location
- Configurable compression and retention
- Logs to database (audit_logs table)

### 3. Task Scheduler Setup
**Location:** `scripts/setup-backup-schedule.bat`
- Automates Windows Task Scheduler configuration
- Schedules daily backups at 2:00 AM
- Runs as SYSTEM account

---

## Installation & Setup

### Method 1: Manual Batch Script

#### Step 1: Test Backup Script

```cmd
cd Z:\htdocs\quty2
scripts\backup-database.bat
```

**Expected Output:**
```
===========================================
Database Backup Script
===========================================
Starting backup at: 2025-11-21 14:30:45

Backup file: storage/backups/database/itquty_20251121_143045.sql.gz
Backup in progress...
Backup completed successfully!
Backup size: 2.5 MB

Cleaning up old backups (keeping last 30 days)...
Old backups removed: 0

Backup log: storage/backups/database/backup_log.txt
===========================================
```

#### Step 2: Verify Backup

Check created files:
```cmd
dir storage\backups\database
```

You should see:
- `itquty_YYYYMMDD_HHMMSS.sql.gz` (compressed backup)
- `backup_log.txt` (backup history)

#### Step 3: Test Restore (Optional)

```cmd
cd C:\xampp\mysql\bin
gunzip < Z:\htdocs\quty2\storage\backups\database\itquty_20251121_143045.sql.gz | mysql -u itquty_user -p itquty
```

---

### Method 2: Laravel Artisan Command

#### Step 1: Register Command

Command is already registered in `app/Console/Kernel.php` under `$commands` array.

#### Step 2: Test Command

```cmd
cd Z:\htdocs\quty2
php artisan backup:database
```

**Expected Output:**
```
Database Backup Started
==================================================
Database: itquty
Host: 192.168.1.87
User: itquty_user

Finding mysqldump...
✓ Found at: C:\xampp\mysql\bin\mysqldump.exe

Creating backup...
✓ Backup created: storage/backups/database/itquty_20251121_143045.sql

Compressing backup...
✓ Compressed: storage/backups/database/itquty_20251121_143045.sql.gz
✓ Size: 2.50 MB

Cleaning old backups (keeping last 30 days)...
✓ Removed 0 old backups

Logging backup...
✓ Backup logged to database

==================================================
✓ Backup completed successfully!
Backup file: storage/backups/database/itquty_20251121_143045.sql.gz
Size: 2.50 MB
```

#### Step 3: Command Options

```cmd
# Backup without compression
php artisan backup:database --no-compress

# Keep only last 7 days
php artisan backup:database --keep=7

# Verbose output
php artisan backup:database -v
```

---

### Method 3: Automated Daily Backups (RECOMMENDED)

#### Step 1: Run Setup Script as Administrator

**Right-click** `scripts/setup-backup-schedule.bat` → **Run as Administrator**

Or via command prompt:
```cmd
cd Z:\htdocs\quty2\scripts
powershell -Command "Start-Process 'setup-backup-schedule.bat' -Verb RunAs"
```

**Expected Output:**
```
===========================================
Database Backup Scheduler Setup
===========================================

Creating scheduled task for daily database backup...

Task Name: ITQuty_DatabaseBackup_Daily
Schedule: Daily at 2:00 AM
Action: Run backup script

Setting up task...
SUCCESS: The scheduled task "ITQuty_DatabaseBackup_Daily" has successfully been created.

===========================================
SETUP COMPLETE!
===========================================

Scheduled task created successfully!

Task Details:
- Name: ITQuty_DatabaseBackup_Daily
- Schedule: Daily at 2:00 AM
- Script: Z:\htdocs\quty2\scripts\backup-database.bat
- Log: Z:\htdocs\quty2\storage\backups\database\backup_log.txt

To verify the task:
  taskschd.msc (Task Scheduler)

To run the backup manually:
  schtasks /Run /TN "ITQuty_DatabaseBackup_Daily"

To disable the task:
  schtasks /Change /TN "ITQuty_DatabaseBackup_Daily" /DISABLE

To delete the task:
  schtasks /Delete /TN "ITQuty_DatabaseBackup_Daily" /F
```

#### Step 2: Verify Task Created

Open **Task Scheduler** (`taskschd.msc`):
1. Navigate to **Task Scheduler Library**
2. Find task: **ITQuty_DatabaseBackup_Daily**
3. Check properties:
   - Trigger: Daily at 2:00 AM
   - Action: Run `backup-database.bat`
   - Status: Ready

#### Step 3: Test Manual Run

```cmd
schtasks /Run /TN "ITQuty_DatabaseBackup_Daily"
```

Check backup folder:
```cmd
dir Z:\htdocs\quty2\storage\backups\database
```

---

## Configuration

### Change Backup Schedule

#### Option 1: Via Task Scheduler GUI
1. Open `taskschd.msc`
2. Find **ITQuty_DatabaseBackup_Daily**
3. Right-click → **Properties**
4. Go to **Triggers** tab
5. Edit trigger time

#### Option 2: Via Command Line

Delete existing task:
```cmd
schtasks /Delete /TN "ITQuty_DatabaseBackup_Daily" /F
```

Create new task with different time (e.g., 3:00 AM):
```cmd
schtasks /Create /TN "ITQuty_DatabaseBackup_Daily" /TR "Z:\htdocs\quty2\scripts\backup-database.bat" /SC DAILY /ST 03:00 /RU "SYSTEM"
```

### Change Retention Period

#### Batch Script
Edit `scripts/backup-database.bat`, line 12:
```batch
set RETENTION_DAYS=30
```
Change `30` to desired days (e.g., `7`, `60`, `90`)

#### Artisan Command
```cmd
php artisan backup:database --keep=60
```

Or edit default in `app/Console/Commands/BackupDatabase.php`, line 38:
```php
protected $keep = 30;
```

### Change Backup Location

#### Batch Script
Edit `scripts/backup-database.bat`, line 8:
```batch
set BACKUP_DIR=%APP_ROOT%\storage\backups\database
```

#### Artisan Command
Edit `app/Console/Commands/BackupDatabase.php`, line 67:
```php
$backupDir = storage_path('backups/database');
```

---

## Backup Management

### View Backup Log

```cmd
type Z:\htdocs\quty2\storage\backups\database\backup_log.txt
```

### List All Backups

```cmd
dir /O-D Z:\htdocs\quty2\storage\backups\database\*.sql.gz
```

### Check Backup Size

```cmd
powershell -Command "Get-ChildItem 'Z:\htdocs\quty2\storage\backups\database\*.sql.gz' | Measure-Object -Property Length -Sum | Select-Object @{Name='TotalSizeMB';Expression={[math]::Round($_.Sum/1MB,2)}}"
```

### Manually Clean Old Backups

Delete backups older than 30 days:
```cmd
forfiles /P "Z:\htdocs\quty2\storage\backups\database" /M *.sql.gz /D -30 /C "cmd /c del @path"
```

---

## Restore Database

### Step 1: Stop Application (Recommended)

```cmd
# Stop Apache in XAMPP Control Panel
```

### Step 2: Decompress Backup

```cmd
cd Z:\htdocs\quty2\storage\backups\database
gunzip itquty_20251121_143045.sql.gz
```

### Step 3: Restore to MySQL

#### Method 1: Full Restore
```cmd
cd C:\xampp\mysql\bin
mysql -h 192.168.1.87 -u itquty_user -p itquty < Z:\htdocs\quty2\storage\backups\database\itquty_20251121_143045.sql
```

#### Method 2: Restore to New Database (Testing)
```cmd
cd C:\xampp\mysql\bin
mysql -h 192.168.1.87 -u itquty_user -p -e "CREATE DATABASE itquty_restore"
mysql -h 192.168.1.87 -u itquty_user -p itquty_restore < Z:\htdocs\quty2\storage\backups\database\itquty_20251121_143045.sql
```

### Step 4: Verify Restore

```cmd
cd C:\xampp\mysql\bin
mysql -h 192.168.1.87 -u itquty_user -p itquty -e "SELECT COUNT(*) FROM users;"
```

### Step 5: Restart Application

```cmd
# Start Apache in XAMPP Control Panel
```

---

## Monitoring & Alerts

### Check Last Backup Status

```cmd
php artisan backup:status
```

Expected output:
```
Last Backup: 2025-11-21 02:00:05
Status: Success
Size: 2.50 MB
File: storage/backups/database/itquty_20251121_020005.sql.gz
```

### Email Alerts (Future Enhancement)

To add email alerts on backup failure, edit `app/Console/Commands/BackupDatabase.php`:

```php
use Illuminate\Support\Facades\Mail;

protected function sendFailureAlert($error)
{
    Mail::raw(
        "Database backup failed:\n\n" . $error,
        function ($message) {
            $message->to('admin@example.com')
                    ->subject('Database Backup Failed');
        }
    );
}
```

---

## Troubleshooting

### Issue 1: mysqldump not found

**Error:**
```
mysqldump is not recognized as an internal or external command
```

**Solution:**
Add MySQL bin to PATH:
```cmd
set PATH=%PATH%;C:\xampp\mysql\bin
```

Or edit `backup-database.bat` to use full path:
```batch
set MYSQL_BIN=C:\xampp\mysql\bin
```

### Issue 2: Permission denied

**Error:**
```
Access is denied
```

**Solution:**
Run as Administrator or grant permissions:
```cmd
icacls "Z:\htdocs\quty2\storage\backups\database" /grant Everyone:(OI)(CI)F /T
```

### Issue 3: Connection failed

**Error:**
```
ERROR 2003 (HY000): Can't connect to MySQL server on '192.168.1.87'
```

**Solution:**
1. Check MySQL is running
2. Verify credentials in `.env`
3. Test connection:
```cmd
cd C:\xampp\mysql\bin
mysql -h 192.168.1.87 -u itquty_user -p itquty -e "SELECT 1;"
```

### Issue 4: Scheduled task not running

**Check Task History:**
1. Open `taskschd.msc`
2. Find task → **History** tab
3. Check for errors

**Common fixes:**
```cmd
# Ensure task is enabled
schtasks /Change /TN "ITQuty_DatabaseBackup_Daily" /ENABLE

# Run manually to test
schtasks /Run /TN "ITQuty_DatabaseBackup_Daily"
```

### Issue 5: Disk space full

**Check disk space:**
```cmd
powershell -Command "Get-PSDrive Z | Select-Object Used,Free"
```

**Solution:**
Reduce retention or move backups:
```cmd
# Change retention to 7 days
php artisan backup:database --keep=7
```

---

## Best Practices

### 1. Test Backups Regularly

Run monthly restore tests:
```cmd
# Last Sunday of each month
php artisan backup:test-restore
```

### 2. Monitor Backup Size

Track backup growth:
```cmd
php artisan backup:stats
```

### 3. Off-site Backup Copy

Copy to external server:
```cmd
robocopy "Z:\htdocs\quty2\storage\backups\database" "\\backup-server\itquty-backups" *.sql.gz /S /MAXAGE:7
```

### 4. Document Restore Procedures

Keep restore instructions accessible offline.

### 5. Alert on Failures

Set up email/SMS alerts for backup failures.

---

## Security Considerations

### 1. Encrypt Backups (Recommended)

Use 7-Zip with password:
```cmd
7z a -p<password> backup.7z itquty_20251121_143045.sql.gz
```

### 2. Restrict File Permissions

```cmd
icacls "Z:\htdocs\quty2\storage\backups\database" /grant Administrators:F /inheritance:r
```

### 3. Secure Credentials

Never hardcode passwords - use `.env` file.

### 4. Audit Backup Access

Check who accessed backups:
```cmd
php artisan audit:backups
```

---

## Support & Maintenance

### Schedule

- **Daily:** Automated backups at 2:00 AM
- **Weekly:** Verify backup logs
- **Monthly:** Test restore procedure
- **Quarterly:** Review retention policy

### Contacts

- **Database Admin:** [admin@itquty.com](mailto:admin@itquty.com)
- **IT Support:** Internal ticket system

---

**Last Updated:** November 21, 2025  
**Version:** 1.0  
**Author:** ITQuty Development Team

---

## Appendix

### A. Task Scheduler XML Export

Export task configuration:
```cmd
schtasks /Query /TN "ITQuty_DatabaseBackup_Daily" /XML > backup-task.xml
```

### B. PowerShell Alternative

For PowerShell version of backup script, see:
`scripts/backup-database.ps1` (not yet created)

### C. Linux/Mac Equivalent

For Unix-based systems, use cron:
```bash
0 2 * * * cd /var/www/itquty && php artisan backup:database
```
