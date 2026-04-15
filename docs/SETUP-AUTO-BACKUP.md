# Setup Windows Task Scheduler for Automatic Backups

Follow these steps to enable automatic database backups every Friday at 11 PM **without needing to login**:

## Option 1: Using Task Scheduler (Recommended)

### Step 1: Open Task Scheduler
1. Press `Win + R`
2. Type `taskschd.msc` and press Enter

### Step 2: Create New Task
1. Click **"Create Task"** (not "Create Basic Task")
2. In **General** tab:
   - Name: `Laravel Database Auto Backup`
   - Description: `Runs Laravel scheduler for automatic database backups`
   - Select: **"Run whether user is logged on or not"** ✅
   - Check: **"Run with highest privileges"**
   - Configure for: `Windows 10` or your Windows version

### Step 3: Configure Triggers
1. Go to **Triggers** tab
2. Click **"New"**
3. Settings:
   - Begin the task: `On a schedule`
   - Settings: `Daily`
   - Start: Pick any date and time (e.g., today at 00:00)
   - Recur every: `1 days`
   - Repeat task every: `5 minutes`
   - For a duration of: `Indefinitely`
   - Check: **"Enabled"**
4. Click **OK**

### Step 4: Configure Actions
1. Go to **Actions** tab
2. Click **"New"**
3. Settings:
   - Action: `Start a program`
   - Program/script: `Z:\htdocs\quty2\run-scheduler.bat`
   - (Leave "Add arguments" and "Start in" empty)
4. Click **OK**

### Step 5: Configure Conditions (Important!)
1. Go to **Conditions** tab
2. **UNCHECK** these options:
   - ❌ "Start the task only if the computer is on AC power"
   - ❌ "Stop if the computer switches to battery power"
3. Keep checked:
   - ✅ "Wake the computer to run this task" (optional)

### Step 6: Configure Settings
1. Go to **Settings** tab
2. Settings:
   - ✅ "Allow task to be run on demand"
   - ✅ "Run task as soon as possible after a scheduled start is missed"
   - ❌ "Stop the task if it runs longer than" (uncheck this)
   - "If the task is already running": `Do not start a new instance`

### Step 7: Save and Enter Password
1. Click **OK**
2. Enter your Windows administrator password
3. Click **OK**

## Option 2: Quick Setup (PowerShell Script)

Run this in PowerShell **as Administrator**:

```powershell
$action = New-ScheduledTaskAction -Execute "Z:\htdocs\quty2\run-scheduler.bat"
$trigger = New-ScheduledTaskTrigger -Daily -At "00:00" -RepetitionInterval (New-TimeSpan -Minutes 5)
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

Register-ScheduledTask -TaskName "Laravel Database Auto Backup" -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description "Runs Laravel scheduler for automatic database backups"
```

## Testing the Setup

### Test 1: Run Task Manually
1. Open Task Scheduler
2. Find "Laravel Database Auto Backup"
3. Right-click → **"Run"**
4. Check if it shows "Running" then "Ready"

### Test 2: Check Backup Was Created
```bash
cd Z:\htdocs\quty2
php artisan schedule:list
```

Look for the backup schedule showing:
```
0 23 * * 5  php artisan db:backup  Weekly on Friday at 23:00
```

### Test 3: Force Backup Now
```bash
php artisan db:backup
```

Check the backup file in: `Z:\htdocs\quty2\storage\app\backups\`

## How It Works

1. **Task Scheduler** runs `run-scheduler.bat` every 5 minutes
2. **Laravel Scheduler** checks if any tasks need to run
3. **Every Friday at 11 PM**, it automatically runs `php artisan db:backup`
4. **Backup is created** in `storage/app/backups/`
5. **Old backups** (>30 days) are automatically deleted

## Important Notes

- ✅ Runs **even when you're not logged in**
- ✅ Runs as **SYSTEM account** (highest privileges)
- ✅ Works **24/7** as long as the computer is on
- ✅ Backups stored for **30 days** then auto-deleted
- ⚠️ Computer must be **powered on** at backup time
- ⚠️ MySQL service must be **running**

## Troubleshooting

If backups don't run:

1. **Check Task History**:
   - Open Task Scheduler
   - Right-click task → "Properties"
   - Go to "History" tab
   - Look for errors

2. **Check Laravel Logs**:
   ```
   Z:\htdocs\quty2\storage\logs\laravel.log
   ```

3. **Test Manually**:
   ```bash
   Z:\htdocs\quty2\run-scheduler.bat
   ```

4. **Verify MySQL is Running**:
   - Check if MySQL service is running
   - Open services.msc
   - Find "MySQL" service

## Security Notes

- The scheduled task runs with SYSTEM privileges
- Backup files contain sensitive data
- Keep `storage/app/backups/` folder secure
- Consider encrypting backup files if stored externally
- Do not commit backup files to Git

## Next Steps

After setup:
1. Wait for Friday at 11 PM to see first automatic backup
2. Or manually test: `php artisan db:backup`
3. Check logs: `storage/logs/laravel.log`
4. Verify backup file created in `storage/app/backups/`