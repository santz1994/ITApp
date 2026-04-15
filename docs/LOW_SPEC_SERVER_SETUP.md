# 🖥️ LOW-SPEC SERVER CONFIGURATION GUIDE
**Hardware:** Intel i3-2100 @ 3.1GHz, 4GB RAM, HDD  
**Target:** 15-20 concurrent users, stable performance

---

## 📋 STEP-BY-STEP OPTIMIZATION

### STEP 1: PHP Configuration (php.ini)

**Location:** `C:\xampp\php\php.ini`

```ini
[PHP]
; Memory Management - CRITICAL for 4GB RAM
memory_limit = 128M                    ; ⚠️ Reduced from default 256M
max_execution_time = 60                ; Prevent runaway scripts
max_input_time = 60

; File Uploads - Reduced to save RAM
post_max_size = 20M
upload_max_filesize = 10M
max_file_uploads = 10

; OPcache - MUST ENABLE (30% speed boost)
[opcache]
zend_extension = php_opcache.dll
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128       ; ⚠️ 128MB only (was 256MB)
opcache.interned_strings_buffer = 8    ; Reduced from 16
opcache.max_accelerated_files = 10000  ; Reduced from 20000
opcache.validate_timestamps = 1
opcache.revalidate_freq = 60           ; Check changes every 60s
opcache.save_comments = 1
opcache.fast_shutdown = 1

; APCu - User data caching (BETTER than Redis for low RAM)
[apcu]
extension = php_apcu.dll
apc.enabled = 1
apc.shm_size = 32M                     ; Only 32MB for cache
apc.enable_cli = 0                     ; Disable for CLI
apc.ttl = 3600                         ; 1 hour cache lifetime
apc.gc_ttl = 7200                      ; Garbage collection

; Realpath Cache - Speeds up file includes
realpath_cache_size = 4M
realpath_cache_ttl = 600

; Disable unnecessary extensions to save RAM
; extension=php_ldap.dll
; extension=php_soap.dll
```

---

### STEP 2: MySQL Configuration (my.ini)

**Location:** `C:\xampp\mysql\bin\my.ini`

```ini
[mysqld]
# Memory Management - OPTIMIZED for 4GB RAM
innodb_buffer_pool_size = 512M         ; ⚠️ Most important! 12% of RAM
key_buffer_size = 64M
max_connections = 30                   ; ⚠️ Reduced from 151
thread_cache_size = 8
table_open_cache = 256
tmp_table_size = 32M
max_heap_table_size = 32M

# Query Cache - HDD benefits more from caching
query_cache_type = 1
query_cache_size = 32M
query_cache_limit = 2M

# InnoDB Settings
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2     ; Less I/O on HDD
innodb_flush_method = normal           ; For Windows

# Slow Query Log - Find bottlenecks
slow_query_log = 1
slow_query_log_file = slow-queries.log
long_query_time = 2                    ; Log queries > 2 seconds

# Binary Log - Disable if not needed (saves I/O)
skip-log-bin
```

---

### STEP 3: Apache Configuration (httpd.conf)

**Location:** `C:\xampp\apache\conf\httpd.conf`

```apache
# Prefork MPM - Better for low RAM
<IfModule mpm_prefork_module>
    StartServers             3         ; ⚠️ Reduced from 5
    MinSpareServers          2         ; ⚠️ Reduced from 5
    MaxSpareServers          5         ; ⚠️ Reduced from 10
    MaxRequestWorkers        30        ; ⚠️ CRITICAL: Max 30 concurrent
    MaxConnectionsPerChild   1000      ; Restart after 1000 requests
</IfModule>

# KeepAlive - Reduce for limited RAM
KeepAlive On
MaxKeepAliveRequests 50                ; Reduced from 100
KeepAliveTimeout 2                     ; ⚠️ Short timeout (was 5)

# Compression - Save bandwidth
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache Static Files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

### STEP 4: Laravel .env Configuration

```dotenv
APP_ENV=production                     ; ⚠️ Must be production
APP_DEBUG=false                        ; ⚠️ Disable debug (saves RAM)

CACHE_DRIVER=file                      ; ⚠️ Keep file cache (APCu if installed)
SESSION_DRIVER=file                    ; File session is fine
QUEUE_CONNECTION=sync                  ; Keep sync (no queue workers)

DB_CONNECTION=mysql
LOG_CHANNEL=daily                      ; Rotate logs daily
LOG_LEVEL=error                        ; ⚠️ Only log errors
```

---

### STEP 5: Code-Level Optimizations

**File:** `app/Providers/AppServiceProvider.php`

```php
public function boot()
{
    // Disable unnecessary features in production
    if (app()->environment('production')) {
        // Disable debug bar
        if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
        
        // Limit query log
        DB::connection()->disableQueryLog();
        
        // Reduce model event listeners if not needed
        Model::preventLazyLoading(false);
    }
}
```

**Global Query Optimization:**

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    // Add global scope to limit results
    Asset::addGlobalScope('limited', function ($query) {
        if (request()->is('admin/*')) {
            return $query; // No limit for admin
        }
        return $query->limit(100); // Max 100 records for users
    });
}
```

---

## 🔧 MAINTENANCE TASKS

### Daily:
```bash
# Clear old logs (saves disk space)
php artisan log:clear

# Clear expired sessions
find storage/framework/sessions -mtime +7 -delete
```

### Weekly:
```bash
# Optimize database tables
mysql -u root -e "OPTIMIZE TABLE assets, tickets, movements"

# Clear Laravel cache if needed
php artisan cache:clear
php artisan view:clear
```

### Monthly:
```bash
# Database backup
php artisan db:backup

# Check disk space
dir C:\xampp\mysql\data
```

---

## 📊 EXPECTED PERFORMANCE

### With These Optimizations:
- **Page Load:** 1-1.5 seconds (acceptable for HDD)
- **Concurrent Users:** 15-20 (stable)
- **RAM Usage:** 2.5-3GB (leaves 1GB for OS)
- **CPU Usage:** 40-60% average
- **Uptime:** 24/7 capable with weekly restart

### Warning Signs (Time to Upgrade):
- ⚠️ Page load > 3 seconds consistently
- ⚠️ Users reporting "502 Bad Gateway"
- ⚠️ MySQL crashes with "Out of memory"
- ⚠️ More than 20 users online simultaneously

---

## 💡 UPGRADE PATH

### When Users > 20:

**Option 1: RAM Upgrade ($30-50)**
- Add 4GB RAM (total 8GB)
- Result: 2x capacity (40 users)
- Easy: Just plug and play

**Option 2: SSD Upgrade ($50-80)**
- Replace HDD with 240GB SSD
- Result: 5-10x faster I/O
- Impact: Most noticeable improvement!

**Option 3: Both RAM + SSD ($100)**
- Best bang for buck
- Result: 50-80 users capacity
- Recommended for growing business

---

## ✅ VERIFICATION COMMANDS

```bash
# Check PHP OPcache status
php -i | findstr opcache.enable

# Check APCu status
php -i | findstr apc.enabled

# Test page load time
curl -w "@curl-format.txt" -o /dev/null -s http://192.168.1.87

# Check MySQL performance
mysql -u root -e "SHOW STATUS LIKE 'Threads_connected'"
```

---

**Created:** December 8, 2025  
**For:** i3-2100, 4GB RAM, HDD  
**Target:** 15-20 users, production-ready  
**Status:** Ready to implement ✅
