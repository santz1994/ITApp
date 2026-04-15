# 🚀 ITQuty Deployment Guide

**Version:** 2.0  
**Date:** November 11, 2025  
**Status:** Production Ready

Complete deployment guide for ITQuty Asset & Ticket Management System from development to production.

---

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#-pre-deployment-checklist)
2. [Environment Setup](#-environment-setup)
3. [Step-by-Step Deployment](#-step-by-step-deployment)
4. [Web Server Configuration](#-web-server-configuration)
5. [Queue & Scheduled Tasks](#-queue--scheduled-tasks)
6. [SSL/HTTPS Setup](#-sslhttps-setup)
7. [Post-Deployment Verification](#-post-deployment-verification)
8. [Rollback Procedure](#-rollback-procedure)
9. [Monitoring & Maintenance](#-monitoring--maintenance)
10. [Troubleshooting](#-troubleshooting)

---

## ✅ Pre-Deployment Checklist

### Required Before Deployment

- [ ] **Backup created** (database + files)
- [ ] **Server requirements met** (PHP 8.1+, MySQL 8.0+, etc.)
- [ ] **Environment file prepared** (.env with production values)
- [ ] **Database credentials confirmed** (host, user, password, database name)
- [ ] **Git repository access** (if deploying from Git)
- [ ] **Composer installed** on server
- [ ] **Node.js/NPM installed** on server (for asset compilation)
- [ ] **Domain configured** (DNS pointing to server)
- [ ] **SSL certificate ready** (Let's Encrypt or purchased)
- [ ] **Mail server configured** (SMTP credentials)

### Pre-Deployment Tasks

```bash
# 1. Test locally first
php artisan test

# 2. Check for errors
php artisan route:list
php artisan config:show

# 3. Verify migrations
php artisan migrate:status

# 4. Export database structure (for reference)
mysqldump -u root -p --no-data itquty_db > schema_backup.sql
```

---

## 🖥️ Environment Setup

### Server Requirements

**Minimum Specifications:**
- **CPU:** 2 cores
- **RAM:** 4GB
- **Storage:** 20GB SSD
- **OS:** Ubuntu 20.04 LTS / 22.04 LTS (recommended)

**Software Requirements:**
```bash
# PHP 8.1+ with extensions
php -v
php -m | grep -E 'pdo|mysql|mbstring|xml|curl|zip|gd|openssl'

# MySQL 8.0+
mysql --version

# Composer
composer --version

# Node.js 18+
node --version
npm --version

# Nginx or Apache
nginx -v
# or
apache2 -v
```

### Install Required Software (Ubuntu)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-mbstring \
    php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath \
    php8.1-intl php8.1-redis php8.1-soap

# Install MySQL 8.0
sudo apt install -y mysql-server mysql-client

# Secure MySQL installation
sudo mysql_secure_installation

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js 18 LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install Redis (optional, for caching)
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Install Supervisor (for queue workers)
sudo apt install -y supervisor
```

---

## 📦 Step-by-Step Deployment

### 1. Prepare Server Directory

```bash
# Create application directory
sudo mkdir -p /var/www/itquty
sudo chown -R $USER:$USER /var/www/itquty

# Navigate to directory
cd /var/www/itquty
```

### 2. Clone or Upload Application

**Option A: From Git Repository**
```bash
git clone https://github.com/santz1994/itquty2.git .
git checkout master  # or specific tag/branch
```

**Option B: Manual Upload**
```bash
# Upload files via SCP, SFTP, or FTP to /var/www/itquty
# From local machine:
scp -r /path/to/local/project/* user@server:/var/www/itquty/
```

### 3. Install Dependencies

```bash
# Install PHP dependencies (production)
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
npm ci --production

# Compile assets for production
npm run production
```

### 4. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

**Production .env Configuration:**
```ini
# Application
APP_NAME="ITQuty Asset Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://itquty.yourdomain.com
APP_KEY=  # Will be generated

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=itquty_production
DB_USERNAME=itquty_user
DB_PASSWORD=secure_production_password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security
SANCTUM_STATEFUL_DOMAINS=itquty.yourdomain.com
SESSION_SECURE_COOKIE=true
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Setup Database

```bash
# Create database and user
sudo mysql -u root -p
```

```sql
-- In MySQL prompt
CREATE DATABASE itquty_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'itquty_user'@'localhost' IDENTIFIED BY 'secure_production_password';
GRANT ALL PRIVILEGES ON itquty_production.* TO 'itquty_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Run migrations
php artisan migrate --force --no-interaction

# Seed essential data (roles, permissions, locations)
php artisan db:seed --class=PermissionsAndRolesSeeder --force
php artisan db:seed --class=LocationsTableSeeder --force
php artisan db:seed --class=DivisionsTableSeeder --force

# DO NOT run TestUsersTableSeeder in production!
```

### 7. Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/itquty

# Set directory permissions
sudo find /var/www/itquty -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/itquty -type f -exec chmod 644 {} \;

# Writable directories
sudo chmod -R 775 /var/www/itquty/storage
sudo chmod -R 775 /var/www/itquty/bootstrap/cache

# Laravel artisan executable
sudo chmod +x /var/www/itquty/artisan
```

### 8. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative

# Run general optimize command
php artisan optimize
```

---

## 🌐 Web Server Configuration

### Nginx Configuration

Create Nginx config file:
```bash
sudo nano /etc/nginx/sites-available/itquty
```

**Basic HTTP Configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name itquty.yourdomain.com;
    root /var/www/itquty/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    # Logs
    access_log /var/log/nginx/itquty_access.log;
    error_log /var/log/nginx/itquty_error.log;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Favicon and robots
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Deny access to .htaccess
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(env|git|svn) {
        deny all;
        return 404;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security: Deny PHP in uploads
    location ~ ^/storage/.*\.php$ {
        deny all;
    }

    # Client max body size (for file uploads)
    client_max_body_size 100M;
}
```

**Enable site:**
```bash
# Test configuration
sudo nginx -t

# Create symlink
sudo ln -s /etc/nginx/sites-available/itquty /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### Apache Configuration (Alternative)

```apache
<VirtualHost *:80>
    ServerName itquty.yourdomain.com
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/itquty/public

    <Directory /var/www/itquty/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/itquty_error.log
    CustomLog ${APACHE_LOG_DIR}/itquty_access.log combined

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

```bash
# Enable modules
sudo a2enmod rewrite headers

# Enable site
sudo a2ensite itquty.conf

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

## ⚙️ Queue & Scheduled Tasks

### Supervisor Configuration (Queue Worker)

Create supervisor config:
```bash
sudo nano /etc/supervisor/conf.d/itquty-worker.conf
```

```ini
[program:itquty-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/itquty/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/itquty/storage/logs/worker.log
stopwaitsecs=3600
startsecs=0
```

**Start supervisor:**
```bash
# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start itquty-worker:*

# Check status
sudo supervisorctl status

# Enable on boot
sudo systemctl enable supervisor
```

### Laravel Scheduler (Cron)

Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add this line:
```cron
* * * * * cd /var/www/itquty && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔒 SSL/HTTPS Setup

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate (Nginx)
sudo certbot --nginx -d itquty.yourdomain.com -d www.itquty.yourdomain.com

# Follow prompts:
# - Enter email address
# - Agree to terms
# - Choose whether to redirect HTTP to HTTPS (recommended: Yes)

# Test auto-renewal
sudo certbot renew --dry-run

# Certificate auto-renews via cron/systemd timer
```

**Updated Nginx Config (After Certbot):**
Certbot automatically updates your Nginx config to include SSL. Verify:

```bash
sudo nano /etc/nginx/sites-available/itquty
```

Should now have:
```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name itquty.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/itquty.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/itquty.yourdomain.com/privkey.pem;
    ssl_trusted_certificate /etc/letsencrypt/live/itquty.yourdomain.com/chain.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256...';
    ssl_prefer_server_ciphers off;

    # ... rest of configuration
}

# HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name itquty.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

**Update .env:**
```ini
APP_URL=https://itquty.yourdomain.com
SESSION_SECURE_COOKIE=true
```

```bash
# Clear config cache
php artisan config:clear
php artisan config:cache

# Restart Nginx
sudo systemctl restart nginx
```

---

## ✔️ Post-Deployment Verification

### Health Checks

```bash
# 1. Check web server status
sudo systemctl status nginx
# or
sudo systemctl status apache2

# 2. Check PHP-FPM
sudo systemctl status php8.1-fpm

# 3. Check supervisor (queue workers)
sudo supervisorctl status

# 4. Check logs for errors
tail -f /var/www/itquty/storage/logs/laravel.log
tail -f /var/log/nginx/itquty_error.log

# 5. Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### Manual Testing Checklist

- [ ] **Homepage loads** (`https://itquty.yourdomain.com`)
- [ ] **Login works** (test with admin credentials)
- [ ] **Dashboard displays correctly**
- [ ] **Assets page loads**
- [ ] **Tickets page loads**
- [ ] **User list loads** (admin only)
- [ ] **Spares management works** (create/edit/delete)
- [ ] **Meeting room booking works**
- [ ] **Director dashboard accessible** (for director/management roles)
- [ ] **Loading spinner shows for slow requests**
- [ ] **DataTables search/sort works**
- [ ] **File uploads work** (test asset image upload)
- [ ] **Email sending works** (test password reset)
- [ ] **Queue jobs process** (check supervisor logs)
- [ ] **Scheduled tasks run** (check cron logs)

### Performance Tests

```bash
# Test page load speed
curl -o /dev/null -s -w "Time Total: %{time_total}s\n" https://itquty.yourdomain.com

# Check PHP opcache status
php -r "var_dump(opcache_get_status());"

# Check Redis connection
redis-cli ping
```

### Security Audit

```bash
# Check file permissions
ls -la /var/www/itquty/storage
ls -la /var/www/itquty/.env

# Verify .env is not accessible via web
curl -I https://itquty.yourdomain.com/.env
# Should return 404 or 403

# Check SSL certificate
openssl s_client -connect itquty.yourdomain.com:443 -servername itquty.yourdomain.com

# SSL Labs test (from browser)
# https://www.ssllabs.com/ssltest/analyze.html?d=itquty.yourdomain.com
```

---

## 🔄 Rollback Procedure

If issues occur after deployment, follow this rollback procedure:

### 1. Immediate Rollback (Code)

```bash
# Option A: Git rollback
cd /var/www/itquty
git log --oneline  # Find previous commit hash
git reset --hard <previous-commit-hash>

# Option B: Restore from backup
sudo rm -rf /var/www/itquty
sudo cp -r /backup/itquty_20251111 /var/www/itquty
sudo chown -R www-data:www-data /var/www/itquty
```

### 2. Database Rollback

```bash
# Restore database backup
mysql -u root -p itquty_production < /backup/itquty_production_20251111.sql
```

### 3. Clear Caches

```bash
cd /var/www/itquty
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Restart web server
sudo systemctl restart nginx
# or
sudo systemctl restart apache2

# Restart queue workers
sudo supervisorctl restart itquty-worker:*

# Restart Redis (if applicable)
sudo systemctl restart redis-server
```

### 5. Verify Rollback

- Check homepage loads
- Test login
- Verify critical features work
- Monitor error logs

---

## 📊 Monitoring & Maintenance

### Log Monitoring

```bash
# Laravel application logs
tail -f /var/www/itquty/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/itquty_access.log

# Nginx error logs
tail -f /var/log/nginx/itquty_error.log

# PHP-FPM logs
tail -f /var/log/php8.1-fpm.log

# Queue worker logs
tail -f /var/www/itquty/storage/logs/worker.log

# MySQL slow query log
sudo tail -f /var/log/mysql/mysql-slow.log
```

### Regular Maintenance Tasks

**Daily:**
```bash
# Check disk space
df -h

# Check error logs
tail -100 /var/www/itquty/storage/logs/laravel.log | grep ERROR

# Check queue worker status
sudo supervisorctl status
```

**Weekly:**
```bash
# Backup database
mysqldump -u root -p itquty_production > /backup/itquty_$(date +%Y%m%d).sql

# Backup files
tar -czf /backup/itquty_files_$(date +%Y%m%d).tar.gz /var/www/itquty

# Clean old logs (older than 30 days)
find /var/www/itquty/storage/logs -name "*.log" -mtime +30 -delete

# Update system packages
sudo apt update && sudo apt upgrade -y
```

**Monthly:**
```bash
# Check SSL certificate expiry
sudo certbot certificates

# Review user accounts (remove inactive)
php artisan tinker
>>> User::where('last_login_at', '<', now()->subMonths(3))->get();

# Optimize database tables
php artisan db:optimize

# Clear old sessions
php artisan session:gc
```

### Performance Monitoring

**Install monitoring tools:**
```bash
# htop (process monitoring)
sudo apt install -y htop

# iotop (disk I/O monitoring)
sudo apt install -y iotop

# nethogs (network monitoring)
sudo apt install -y nethogs
```

**Monitor key metrics:**
```bash
# CPU and memory
htop

# Disk I/O
sudo iotop

# Network usage
sudo nethogs

# MySQL performance
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G" | less
```

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error

**Symptoms:** White page with "500 Internal Server Error"

**Solutions:**
```bash
# 1. Check Laravel logs
tail -50 /var/www/itquty/storage/logs/laravel.log

# 2. Check web server error log
sudo tail -50 /var/log/nginx/itquty_error.log

# 3. Verify file permissions
sudo chown -R www-data:www-data /var/www/itquty/storage
sudo chmod -R 775 /var/www/itquty/storage

# 4. Clear all caches
php artisan optimize:clear

# 5. Verify .env file exists and has APP_KEY
cat /var/www/itquty/.env | grep APP_KEY
```

---

### Issue: Database Connection Error

**Symptoms:** "SQLSTATE[HY000] [2002] Connection refused"

**Solutions:**
```bash
# 1. Verify MySQL is running
sudo systemctl status mysql

# 2. Check database credentials in .env
cat /var/www/itquty/.env | grep DB_

# 3. Test connection manually
mysql -u itquty_user -p itquty_production

# 4. Verify user privileges
mysql -u root -p
SHOW GRANTS FOR 'itquty_user'@'localhost';

# 5. Check MySQL error log
sudo tail -50 /var/log/mysql/error.log
```

---

### Issue: Queue Jobs Not Processing

**Symptoms:** Jobs stuck in queue, emails not sending

**Solutions:**
```bash
# 1. Check supervisor status
sudo supervisorctl status itquty-worker:*

# 2. Restart queue workers
sudo supervisorctl restart itquty-worker:*

# 3. Check worker logs
tail -50 /var/www/itquty/storage/logs/worker.log

# 4. Manually process queue
php artisan queue:work --once

# 5. Check Redis connection
redis-cli ping

# 6. Clear failed jobs
php artisan queue:flush
php artisan queue:restart
```

---

### Issue: SSL Certificate Error

**Symptoms:** "Your connection is not private" warning

**Solutions:**
```bash
# 1. Check certificate status
sudo certbot certificates

# 2. Renew certificate
sudo certbot renew --force-renewal

# 3. Verify Nginx SSL config
sudo nginx -t

# 4. Restart Nginx
sudo systemctl restart nginx

# 5. Test SSL
openssl s_client -connect itquty.yourdomain.com:443
```

---

### Issue: High Server Load

**Symptoms:** Slow page loads, timeouts

**Solutions:**
```bash
# 1. Check current load
htop

# 2. Identify heavy processes
top -c

# 3. Check slow queries
mysql -u root -p
SELECT * FROM information_schema.processlist WHERE time > 10;

# 4. Enable query caching
# Edit config/database.php, add 'cache' settings

# 5. Optimize assets
php artisan optimize
composer dump-autoload --optimize

# 6. Enable OPcache
# Edit /etc/php/8.1/fpm/php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

---

### Issue: File Upload Errors

**Symptoms:** "413 Request Entity Too Large" or upload fails

**Solutions:**
```bash
# 1. Increase Nginx upload limit
sudo nano /etc/nginx/sites-available/itquty
# Add: client_max_body_size 100M;

# 2. Increase PHP upload limits
sudo nano /etc/php/8.1/fpm/php.ini
# Set:
upload_max_filesize = 100M
post_max_size = 100M

# 3. Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

# 4. Verify storage permissions
sudo chmod -R 775 /var/www/itquty/storage/app/public
```

---

## 📞 Support & Resources

### Getting Help

- **Documentation:** `/var/www/itquty/docs/`
- **Laravel Docs:** https://laravel.com/docs/10.x
- **Server Admin:** contact your system administrator
- **Emergency:** Check rollback procedure above

### Useful Commands Reference

```bash
# Application
php artisan about                    # App info
php artisan optimize                 # Cache everything
php artisan optimize:clear           # Clear all caches

# Queue
php artisan queue:work               # Start worker
php artisan queue:restart            # Restart workers
php artisan queue:failed             # Show failed jobs
php artisan queue:retry all          # Retry failed jobs

# Database
php artisan migrate --status         # Check migrations
php artisan db:show                  # Show DB info

# Logs
tail -f storage/logs/laravel.log     # Watch app logs
php artisan log:clear                # Clear logs (custom)

# Cache
php artisan cache:clear              # Clear app cache
php artisan config:clear             # Clear config cache
php artisan route:clear              # Clear route cache
php artisan view:clear               # Clear view cache
php artisan permission:cache-reset   # Clear permission cache
```

---

## ✅ Deployment Completed

After completing all steps above:

1. ✅ Application deployed to production
2. ✅ Web server configured and running
3. ✅ SSL certificate installed
4. ✅ Queue workers running via Supervisor
5. ✅ Scheduled tasks configured via Cron
6. ✅ All caches optimized
7. ✅ Post-deployment verification passed
8. ✅ Monitoring and backups configured

**Your ITQuty system is now live and ready for use!** 🎉

---

**Document Version:** 1.0  
**Last Updated:** November 11, 2025  
**Maintained By:** D-Riz

For detailed feature documentation, see [FINAL_SUMMARY_NOV_11_2025.md](docs/FINAL_SUMMARY_NOV_11_2025.md)
