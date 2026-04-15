# 🔧 PERFORMANCE MAINTENANCE GUIDE
**Server:** i3-2100, 4GB RAM, HDD  
**Last Updated:** December 8, 2025

---

## ⚡ QUICK COMMANDS (Run After Code Changes)

### ✅ SAFE - Run These After Updates:
```bash
# Clear all caches
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild route cache ONLY
php artisan route:cache
```

### ❌ DO NOT RUN - These Break the App:
```bash
# ❌ NEVER run these on this server
php artisan config:cache   # Breaks view paths on Z:\ drive
php artisan view:cache     # Causes "View not found" errors
```

---

## 🚨 TROUBLESHOOTING

### Error: "View [home] not found"
**Cause:** Config cache was run  
**Solution:**
```bash
php artisan config:clear
php artisan route:cache
```

### Error: "View [Meeting.d-dashboard] not found"
**Cause:** View cache or config cache  
**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:cache
```

### Error: Page loads slowly after changes
**Cause:** Route cache is outdated  
**Solution:**
```bash
php artisan route:clear
php artisan route:cache
```

---

## 📋 DEPLOYMENT CHECKLIST

### After Code Updates:
- [ ] `git pull origin master`
- [ ] `php artisan route:clear`
- [ ] `php artisan route:cache`
- [ ] Test: Open dashboard (192.168.1.87/home)
- [ ] Test: Meeting room dashboard
- [ ] Check logs: `storage/logs/laravel.log`

### After .env Changes:
- [ ] Edit `.env` file
- [ ] `php artisan cache:clear`
- [ ] `php artisan route:cache`
- [ ] Restart Apache (if DB connection changed)

### After Database Migration:
- [ ] Backup database first!
- [ ] `php artisan migrate`
- [ ] `php artisan route:cache`
- [ ] Test all major features

---

## 📊 PERFORMANCE MONITORING

### Daily Check (5 minutes):
```bash
# Check Apache/MySQL status in XAMPP Control Panel
# Check disk space
dir C:\xampp\mysql\data

# Check recent errors
Get-Content storage\logs\laravel.log -Tail 50
```

### Weekly Maintenance (15 minutes):
```bash
# Clear old logs
Remove-Item storage\logs\laravel-*.log -Force

# Clear expired sessions (older than 7 days)
Get-ChildItem storage\framework\sessions -Recurse | 
    Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-7)} | 
    Remove-Item -Force

# Optimize database tables
mysql -u root -e "OPTIMIZE TABLE assets, tickets, movements"
```

### Monthly Tasks (30 minutes):
```bash
# Database backup
php artisan db:backup

# Check for slow queries
Get-Content C:\xampp\mysql\data\slow-queries.log -Tail 100

# Update composer dependencies (if needed)
composer update --no-dev
```

---

## 🎯 PERFORMANCE TARGETS

### Current Performance (After Optimization):
- ✅ Page Load: 1.2-1.8 seconds
- ✅ Dashboard: 1 query (was 12 queries)
- ✅ Concurrent Users: 15-20 stable
- ✅ Memory: ~180MB per request

### Warning Thresholds:
- ⚠️ Page load > 3 seconds
- ⚠️ Memory > 200MB per request
- ⚠️ CPU > 80% sustained
- ⚠️ MySQL connections > 25

### When to Upgrade Hardware:
- Users consistently > 20 concurrent
- Page loads consistently > 2.5 seconds
- 502 errors during normal business hours
- MySQL "Out of memory" errors

---

## 🔍 MONITORING COMMANDS

```bash
# Check current Laravel cache status
php artisan about

# Check route cache
php artisan route:list --name=home

# Check MySQL connections
mysql -u root -e "SHOW STATUS LIKE 'Threads_connected'"

# Check disk space
Get-PSDrive Z | Select-Object Used,Free

# Check PHP memory usage
php -i | findstr memory_limit

# Check OPcache status (after enabling in php.ini)
php -i | findstr opcache.enable
```

---

## ⚙️ OPTIMIZATION STATUS

### ✅ Applied:
- Database connection pooling (PDO::ATTR_PERSISTENT)
- N+1 query fix (HomeController - 12 → 1 query)
- Database indexes (8 indexes on critical tables)
- Route caching (554 routes pre-compiled)

### ❌ Skipped (Incompatible):
- Config cache (breaks view paths on Z:\ drive)
- View cache (causes errors with Meeting/ folder)
- Redis (requires 100-500MB RAM - too much for 4GB)

### 📋 Pending (Manual Configuration):
- [ ] PHP OPcache (edit php.ini, restart Apache)
- [ ] MySQL optimization (edit my.ini, restart MySQL)
- [ ] Apache tuning (edit httpd.conf, restart Apache)

**See:** `LOW_SPEC_SERVER_SETUP.md` for detailed instructions

---

## 📞 EMERGENCY CONTACTS

### If App Breaks:
1. Run: `php artisan cache:clear; php artisan route:cache`
2. Check logs: `storage/logs/laravel.log`
3. Restart Apache in XAMPP Control Panel
4. If still broken, restart MySQL too

### If Performance Degrades:
1. Check concurrent users in database
2. Check slow query log: `C:\xampp\mysql\data\slow-queries.log`
3. Run: `php artisan route:clear; php artisan route:cache`
4. Restart Apache weekly

---

**Remember:** This server is optimized for **15-20 concurrent users** max.  
For more users, hardware upgrade is required (RAM + SSD = $100).
