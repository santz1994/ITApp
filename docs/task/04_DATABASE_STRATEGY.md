# Database Strategy & Data Migration Plan

**Proyek:** ITQuty Microservices Migration  
**Fokus:** Database Architecture & Data Safety  
**Tanggal:** 18 Desember 2025  
**Prioritas:** 🔴 CRITICAL - Data Loss Prevention

---

## 🎯 Executive Summary

### Pertanyaan Utama: **APAKAH DATA AKAN HILANG?**

# ✅ JAWABAN: TIDAK AKAN ADA DATA YANG HILANG

Dengan strategi migrasi yang tepat, **100% data Anda akan aman**. Dokumen ini menjelaskan:
1. Bagaimana data tetap aman selama migrasi
2. Strategi backup & recovery
3. Zero-downtime migration approach
4. Rollback plan jika terjadi masalah

---

## 🛡️ Data Safety Guarantee

### 3 Lapis Perlindungan Data

```
Layer 1: BACKUP AUTOMATION
├── Daily full backup (setiap hari jam 2 pagi)
├── Hourly incremental backup
├── Point-in-time recovery capability
└── Retention: 30 days

Layer 2: REPLICATION
├── Master-Slave database replication
├── Slave database sebagai backup real-time
├── Auto-failover jika master down
└── Read queries dari slave (load balancing)

Layer 3: MIGRATION SAFETY
├── Parallel running (old + new system jalan bersamaan)
├── Data synchronization real-time
├── Validation checks sebelum cutover
└── Quick rollback capability (< 5 menit)
```

---

## 📊 Database Architecture Evolution

### Phase 0: Current State (Monolith)
```
┌────────────────────────────────────────┐
│         Laravel Application             │
│  (All logic dalam 1 aplikasi)           │
└──────────────┬─────────────────────────┘
               │
               ▼
┌────────────────────────────────────────┐
│        MySQL Single Database            │
│                                         │
│  Tables:                                │
│  ├── users                              │
│  ├── roles, permissions                 │
│  ├── assets, asset_models, asset_types  │
│  ├── tickets, ticket_comments           │
│  ├── locations, divisions, suppliers    │
│  ├── budgets, invoices                  │
│  ├── meeting_room_bookings              │
│  └── ... (40+ tables total)             │
│                                         │
│  Size: ~500MB - 5GB (tergantung data)   │
└─────────────────────────────────────────┘
```

### Phase 1: Shared Database with Service Views (Month 1-3)
```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│Auth Service │  │Asset Service│  │Ticket Svc   │
└──────┬──────┘  └──────┬──────┘  └──────┬──────┘
       │                │                │
       └────────────────┴────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────┐
│      MySQL Shared Database (READ/WRITE)      │
│                                               │
│  Database Views untuk isolasi:                │
│  ├── auth_view (users, roles, permissions)    │
│  ├── asset_view (assets, models, types)       │
│  ├── ticket_view (tickets, comments)          │
│  └── ...                                      │
│                                               │
│  ✓ Data tetap di 1 database                   │
│  ✓ Services akses via views                   │
│  ✓ ZERO data migration needed                 │
└───────────────────────────────────────────────┘

Keuntungan Phase 1:
✓ Tidak ada data migration (AMAN)
✓ Transactions masih work normal
✓ No data duplication
✓ Rollback sangat mudah
✓ Testing sambil production jalan
```

### Phase 2: Database per Service - Gradual Migration (Month 4-9)
```
┌──────────┐   ┌───────────┐   ┌───────────┐
│Auth Svc  │   │Asset Svc  │   │Ticket Svc │
└────┬─────┘   └─────┬─────┘   └─────┬─────┘
     │               │               │
     ▼               ▼               ▼
┌─────────┐   ┌──────────┐   ┌──────────┐
│ auth_db │   │asset_db  │   │ticket_db │
└────┬────┘   └─────┬────┘   └─────┬────┘
     │              │              │
     └──────────────┴──────────────┘
                    │
                    ▼
     ┌─────────────────────────────┐
     │   Master Database (Sync)     │
     │  - Still contains all data   │
     │  - Two-way sync active       │
     │  - Validation & reconciliation│
     └──────────────────────────────┘

Strategi Migrasi Bertahap:
1. Copy data ke database baru
2. Setup two-way sync (CDC - Change Data Capture)
3. Service baca dari DB baru, tulis ke kedua DB
4. Validasi 1-2 minggu
5. Switch fully ke DB baru
6. Matikan sync setelah confident
```

### Phase 3: Complete Separation (Month 10-12)
```
┌──────────┐ ┌───────────┐ ┌──────────┐ ┌──────────┐
│Auth Svc  │ │Asset Svc  │ │Ticket Svc│ │Master Svc│
└────┬─────┘ └─────┬─────┘ └────┬─────┘ └────┬─────┘
     │             │            │            │
     ▼             ▼            ▼            ▼
┌─────────┐  ┌──────────┐ ┌──────────┐ ┌──────────┐
│ auth_db │  │asset_db  │ │ticket_db │ │master_db │
│ MySQL   │  │ MySQL    │ │ MySQL    │ │  MySQL   │
└─────────┘  └──────────┘ └──────────┘ └──────────┘

Event-Driven Sync:
┌─────────────────────────────────┐
│     RabbitMQ Event Bus          │
│  Events:                        │
│  - UserUpdated                  │
│  - AssetAssigned                │
│  - TicketCreated                │
│  - LocationChanged              │
└─────────────────────────────────┘

Data Consistency: Eventual Consistency
- Critical data: Immediate sync via events
- Reference data: Batch sync every 5 minutes
- Analytics data: Batch sync daily
```

---

## 🔄 Data Migration Methodology

### 1. Zero-Downtime Migration Process

```
Step 1: PREPARATION (No impact ke production)
├── Create new database schemas
├── Setup replication
├── Test migration scripts
└── Prepare rollback scripts

Step 2: INITIAL DATA COPY (Offline/Online)
├── Copy existing data ke new databases
├── Time: 2-6 hours (tergantung size)
├── Method: mysqldump + mysql import
└── Validation: Row counts, checksums

Step 3: CHANGE DATA CAPTURE (CDC) Setup
├── Setup binary log replication
├── OR use Debezium untuk CDC
├── Track changes di old database
└── Apply changes ke new database

Step 4: PARALLEL RUNNING (2-4 minggu)
├── Old system tetap production
├── New services shadow mode
├── Compare results (validation)
└── Fix inconsistencies

Step 5: GRADUAL CUTOVER
├── Route 10% traffic ke new services
├── Monitor error rates
├── Gradually increase to 100%
└── Keep old system ready untuk rollback

Step 6: FINAL CUTOVER
├── Switch 100% ke new services
├── Keep sync active 1 minggu
├── Monitor closely
└── Decommission old system setelah confident
```

### 2. Data Validation Checkpoints

```sql
-- Validation Script 1: Row Counts
SELECT 
    'users' as table_name,
    COUNT(*) as old_db_count,
    (SELECT COUNT(*) FROM new_db.users) as new_db_count,
    COUNT(*) - (SELECT COUNT(*) FROM new_db.users) as diff
FROM old_db.users
UNION ALL
SELECT 
    'assets',
    COUNT(*),
    (SELECT COUNT(*) FROM asset_db.assets),
    COUNT(*) - (SELECT COUNT(*) FROM asset_db.assets)
FROM old_db.assets;

-- Validation Script 2: Data Checksums
SELECT 
    'users' as table_name,
    MD5(GROUP_CONCAT(id, email ORDER BY id)) as old_checksum,
    (SELECT MD5(GROUP_CONCAT(id, email ORDER BY id)) FROM new_db.users) as new_checksum
FROM old_db.users;

-- Validation Script 3: Latest Records
SELECT 
    'Latest 10 assets - Old DB' as source,
    id, asset_tag, updated_at
FROM old_db.assets
ORDER BY updated_at DESC
LIMIT 10
UNION ALL
SELECT 
    'Latest 10 assets - New DB',
    id, asset_tag, updated_at
FROM asset_db.assets
ORDER BY updated_at DESC
LIMIT 10;
```

---

## 💾 Backup Strategy

### 1. Automated Daily Backups

```bash
#!/bin/bash
# File: /scripts/daily_backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/mysql"
DATABASES=("auth_db" "asset_db" "ticket_db" "master_db")

# Full backup semua databases
for DB in "${DATABASES[@]}"; do
    echo "Backing up $DB..."
    
    # Dump database
    mysqldump \
        --user=root \
        --password=$MYSQL_ROOT_PASSWORD \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        $DB | gzip > $BACKUP_DIR/${DB}_${DATE}.sql.gz
    
    # Upload ke cloud storage (optional)
    aws s3 cp $BACKUP_DIR/${DB}_${DATE}.sql.gz \
        s3://itquty-backups/mysql/${DB}/
done

# Keep only last 30 days locally
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

# Verify backup
for DB in "${DATABASES[@]}"; do
    gunzip -t $BACKUP_DIR/${DB}_${DATE}.sql.gz
    if [ $? -eq 0 ]; then
        echo "✓ $DB backup verified"
    else
        echo "✗ $DB backup FAILED - ALERT!"
        # Send alert email/Slack
    fi
done
```

### 2. Point-in-Time Recovery Setup

```ini
# MySQL Configuration: /etc/mysql/my.cnf

[mysqld]
# Enable binary logging untuk PITR
log_bin = /var/log/mysql/mysql-bin.log
expire_logs_days = 7
max_binlog_size = 100M
binlog_format = ROW

# Backup settings
innodb_file_per_table = 1
innodb_flush_log_at_trx_commit = 1

# Replication
server-id = 1
binlog_do_db = auth_db
binlog_do_db = asset_db
binlog_do_db = ticket_db
```

### 3. Backup Retention Policy

```
┌─────────────────────────────────────────┐
│         BACKUP RETENTION POLICY          │
├─────────────────────────────────────────┤
│                                         │
│  Daily Full Backups:                    │
│  ├── Local: 30 days                     │
│  ├── Cloud: 90 days                     │
│  └── Archive: 1 year (monthly snapshot) │
│                                         │
│  Hourly Incremental:                    │
│  ├── Local: 7 days                      │
│  └── Cloud: 30 days                     │
│                                         │
│  Binary Logs (PITR):                    │
│  ├── Local: 7 days                      │
│  └── Cloud: 30 days                     │
│                                         │
│  Storage Estimate:                      │
│  - Daily backup: ~500MB - 2GB           │
│  - Monthly total: ~15GB - 60GB          │
│  - Yearly total: ~180GB - 700GB         │
│                                         │
└─────────────────────────────────────────┘
```

---

## 🔄 Data Synchronization Strategy

### 1. Real-Time Sync (CDC with Debezium)

```yaml
# Debezium Configuration
name: itquty-mysql-connector
config:
  connector.class: io.debezium.connector.mysql.MySqlConnector
  database.hostname: mysql-master
  database.port: 3306
  database.user: debezium
  database.password: ${DEBEZIUM_PASSWORD}
  database.server.id: 184054
  database.server.name: itquty
  database.whitelist: auth_db,asset_db,ticket_db
  database.history.kafka.bootstrap.servers: kafka:9092
  database.history.kafka.topic: schema-changes.itquty
  
  # Capture all changes
  include.schema.changes: true
  snapshot.mode: initial
  
  # Transform CDC events
  transforms: unwrap
  transforms.unwrap.type: io.debezium.transforms.ExtractNewRecordState
```

### 2. Event-Based Sync

```php
// File: app/Events/AssetAssigned.php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $asset;
    public $user;
    public $assignedAt;

    public function __construct($asset, $user)
    {
        $this->asset = $asset;
        $this->user = $user;
        $this->assignedAt = now();
    }

    /**
     * Publish to message queue for other services
     */
    public function broadcastOn()
    {
        return [
            new Channel('assets'),
            new Channel('notifications'),
            new Channel('reporting')
        ];
    }

    public function broadcastAs()
    {
        return 'asset.assigned';
    }

    public function broadcastWith()
    {
        return [
            'asset_id' => $this->asset->id,
            'asset_tag' => $this->asset->asset_tag,
            'user_id' => $this->user->id,
            'username' => $this->user->username,
            'assigned_at' => $this->assignedAt->toIso8601String(),
        ];
    }
}
```

### 3. Batch Sync for Reference Data

```php
// File: app/Console/Commands/SyncMasterData.php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncMasterData extends Command
{
    protected $signature = 'sync:master-data';
    protected $description = 'Sync master data across services';

    public function handle()
    {
        $this->info('Starting master data sync...');

        // Sync locations
        $this->syncLocations();
        
        // Sync divisions
        $this->syncDivisions();
        
        // Sync manufacturers
        $this->syncManufacturers();

        $this->info('Master data sync completed!');
    }

    private function syncLocations()
    {
        $locations = DB::connection('master_db')
            ->table('locations')
            ->where('updated_at', '>', now()->subMinutes(5))
            ->get();

        foreach ($locations as $location) {
            // Publish event untuk services lain
            event(new MasterDataUpdated('location', $location));
            
            // OR direct API call ke services
            Http::post('http://asset-service:8003/api/internal/locations', 
                $location->toArray()
            );
        }

        $this->info("Synced {$locations->count()} locations");
    }
}
```

---

## 🔐 Data Security During Migration

### 1. Encryption at Rest
```ini
# MySQL Encryption Configuration
[mysqld]
# Encrypt tablespaces
innodb_encrypt_tables = ON
innodb_encrypt_log = ON

# Keyring plugin
early-plugin-load = keyring_file.so
keyring_file_data = /var/lib/mysql-keyring/keyring
```

### 2. Encryption in Transit
```yaml
# All database connections use SSL/TLS
DB_CONNECTION: mysql
DB_SSL_CA: /ssl/ca-cert.pem
DB_SSL_CERT: /ssl/client-cert.pem
DB_SSL_KEY: /ssl/client-key.pem
DB_SSL_VERIFY_SERVER_CERT: true
```

### 3. Access Control
```sql
-- Create service-specific database users

-- Auth Service user (only access auth_db)
CREATE USER 'auth_service'@'%' IDENTIFIED BY 'secure_password_1';
GRANT SELECT, INSERT, UPDATE, DELETE ON auth_db.* TO 'auth_service'@'%';

-- Asset Service user (only access asset_db)
CREATE USER 'asset_service'@'%' IDENTIFIED BY 'secure_password_2';
GRANT SELECT, INSERT, UPDATE, DELETE ON asset_db.* TO 'asset_service'@'%';
GRANT SELECT ON master_db.locations TO 'asset_service'@'%';
GRANT SELECT ON master_db.divisions TO 'asset_service'@'%';

-- Ticket Service user
CREATE USER 'ticket_service'@'%' IDENTIFIED BY 'secure_password_3';
GRANT SELECT, INSERT, UPDATE, DELETE ON ticket_db.* TO 'ticket_service'@'%';

-- Read-only user untuk reporting
CREATE USER 'reporting_readonly'@'%' IDENTIFIED BY 'secure_password_4';
GRANT SELECT ON auth_db.* TO 'reporting_readonly'@'%';
GRANT SELECT ON asset_db.* TO 'reporting_readonly'@'%';
GRANT SELECT ON ticket_db.* TO 'reporting_readonly'@'%';

FLUSH PRIVILEGES;
```

---

## 🚨 Disaster Recovery Plan

### Recovery Time Objective (RTO) & Recovery Point Objective (RPO)

```
┌─────────────────────────────────────────┐
│          RECOVERY TARGETS                │
├─────────────────────────────────────────┤
│                                         │
│  RTO (Recovery Time):                   │
│  ├── Critical: 15 minutes               │
│  ├── High: 1 hour                       │
│  └── Normal: 4 hours                    │
│                                         │
│  RPO (Data Loss):                       │
│  ├── Critical: 0 seconds (sync replica) │
│  ├── High: 5 minutes (binary logs)      │
│  └── Normal: 1 hour (backup)            │
│                                         │
└─────────────────────────────────────────┘
```

### Disaster Scenarios & Recovery Steps

#### Scenario 1: Database Corruption
```bash
# Detection
- Automated health checks detect corruption
- Alert sent to team

# Recovery Steps
1. Stop application (maintenance mode)
   php artisan down

2. Identify corrupted tables
   mysqlcheck -u root -p --all-databases

3. Restore dari latest backup
   gunzip < /backups/mysql/asset_db_20251218.sql.gz | mysql -u root -p asset_db

4. Apply binary logs untuk PITR
   mysqlbinlog --start-datetime="2025-12-18 02:00:00" \
               --stop-datetime="2025-12-18 14:30:00" \
               /var/log/mysql/mysql-bin.000123 | mysql -u root -p

5. Verify data integrity
   ./scripts/validate_data.sh

6. Bring application online
   php artisan up

# Expected Time: 15-30 minutes
```

#### Scenario 2: Accidental Data Deletion
```bash
# Example: User accidentally deleted 100 assets

# Recovery Steps
1. Identify deletion time dari audit logs
   SELECT * FROM audit_logs 
   WHERE table_name = 'assets' 
   AND action = 'delete' 
   AND created_at > '2025-12-18 14:00:00';

2. Extract deleted records dari backup
   mysqldump -u root -p asset_db assets \
       --where="id IN (1,2,3,...)" > deleted_assets.sql

3. Restore deleted records
   mysql -u root -p asset_db < deleted_assets.sql

4. Verify restoration
   SELECT COUNT(*) FROM assets WHERE id IN (1,2,3,...);

# Expected Time: 5-15 minutes
```

#### Scenario 3: Complete Database Loss
```bash
# Worst case: Server crash, data completely lost

# Recovery Steps
1. Setup new database server
   docker run -d --name mysql-new mysql:8.0

2. Restore dari latest daily backup
   for DB in auth_db asset_db ticket_db master_db; do
       gunzip < /backups/mysql/${DB}_latest.sql.gz | \
           mysql -h new-db-host -u root -p $DB
   done

3. Apply binary logs dari backup time sampai sekarang
   # This recovers changes after backup
   mysqlbinlog --start-position=XXXXX /backups/binlogs/*.log | \
       mysql -h new-db-host -u root -p

4. Update service configurations
   # Point all services ke new database

5. Verify all data
   ./scripts/full_validation.sh

6. Resume operations
   # Remove maintenance mode

# Expected Time: 1-2 hours
```

---

## 📋 Migration Checklist

### Pre-Migration (Week before)
```
□ Full backup taken & verified
□ Backup restoration tested
□ Migration scripts tested di staging
□ Rollback plan documented
□ Team trained on new architecture
□ Communication plan ready
□ Monitoring dashboard setup
□ Alert rules configured
```

### During Migration
```
□ Enable maintenance mode (optional)
□ Final backup taken
□ Database schemas created
□ Initial data copied
□ Data validation passed
□ CDC sync started
□ Services deployed
□ Health checks passing
□ Smoke tests passed
```

### Post-Migration (First week)
```
□ Monitor error rates (target: < 0.1%)
□ Check response times (target: same or better)
□ Verify data consistency daily
□ Review logs for anomalies
□ User feedback collected
□ Performance metrics compared
□ Backup & recovery tested
□ Documentation updated
```

---

## 📊 Database Schema Comparison

### Current Monolith Schema
```sql
-- Total tables: 40+
-- Total size: ~500MB - 5GB
-- Indexes: 150+
-- Foreign keys: 80+

Key tables:
- users (5,000 - 50,000 rows)
- assets (10,000 - 100,000 rows)
- tickets (20,000 - 200,000 rows)
- ticket_comments (50,000 - 500,000 rows)
- asset_maintenance_logs (30,000 - 300,000 rows)
```

### Target Microservices Schemas

```sql
-- auth_db (300MB - 1GB)
Tables: 5
- users
- jwt_blacklist
- login_history
- password_resets
- sessions

-- asset_db (400MB - 3GB)
Tables: 12
- assets
- asset_models
- asset_types
- asset_requests
- asset_maintenance_logs
- asset_lifecycle_events
- movements
- pcspecs
- (plus related tables)

-- ticket_db (500MB - 2GB)
Tables: 10
- tickets
- ticket_comments
- ticket_history
- tickets_priorities
- tickets_statuses
- tickets_types
- tickets_canned_fields
- sla_policies
- (plus related tables)

-- master_db (100MB - 500MB)
Tables: 10+
- locations
- divisions
- manufacturers
- suppliers
- statuses
- warranty_types
- (plus other master data)
```

---

## 🎯 Success Criteria

### Data Integrity
```
✓ 100% row count match across all tables
✓ Checksums match for critical tables
✓ Foreign key relationships preserved
✓ No orphaned records
✓ All indexes functioning
✓ All constraints enforced
```

### Performance
```
✓ Query performance same or better
✓ No N+1 query issues
✓ Response time < baseline
✓ No database connection issues
✓ Replication lag < 1 second
```

### Safety
```
✓ Zero data loss
✓ Backups working automatically
✓ Point-in-time recovery tested
✓ Rollback capability verified
✓ Disaster recovery plan tested
```

---

## 💡 Best Practices

### 1. Always Backup First
```bash
# Before any migration step
./scripts/backup_all.sh
./scripts/verify_backups.sh
```

### 2. Test in Staging First
```bash
# Never migrate production first
1. Test di development
2. Test di staging dengan production data copy
3. Baru migrate production
```

### 3. Keep Old System Running
```bash
# During migration period (2-4 weeks)
- Old system tetap jalan
- New system parallel
- Can switch back anytime
- Decommission setelah confident
```

### 4. Monitor Everything
```yaml
Metrics to monitor:
- Database replication lag
- Query response time
- Error rates
- Data consistency checks
- Backup success/failure
- Disk space usage
```

### 5. Communicate Changes
```markdown
## Communication Plan

Before Migration:
- Email ke all users (1 week before)
- Announcement di aplikasi
- Training untuk admin users

During Migration:
- Status page update real-time
- Slack channel untuk updates
- Hotline untuk critical issues

After Migration:
- Success announcement
- Known issues list
- Support channel active 24/7 (first week)
```

---

## 📚 Scripts & Tools

### Database Migration Scripts
```bash
# Directory structure
/scripts/
├── migration/
│   ├── 01_create_schemas.sh
│   ├── 02_migrate_auth_data.sh
│   ├── 03_migrate_asset_data.sh
│   ├── 04_migrate_ticket_data.sh
│   ├── 05_setup_replication.sh
│   ├── 06_validate_data.sh
│   └── 07_cutover.sh
├── backup/
│   ├── daily_backup.sh
│   ├── restore_backup.sh
│   └── verify_backup.sh
└── monitoring/
    ├── check_replication.sh
    ├── check_consistency.sh
    └── alert_on_issues.sh
```

### Validation Tools
```php
// Laravel Command untuk validasi
php artisan db:validate-migration

// Check specific table
php artisan db:validate-table assets

// Full consistency check
php artisan db:consistency-check --full
```

---

## 🎓 Training Materials

### For Developers
```
Topics:
1. New database architecture
2. How to query data across services
3. Event-driven data sync
4. Troubleshooting common issues
5. Rollback procedures

Duration: 4 hours workshop
Materials: Slides + hands-on lab
```

### For Database Administrators
```
Topics:
1. Replication setup & monitoring
2. Backup & restore procedures
3. Performance tuning per service
4. Disaster recovery drills
5. Security best practices

Duration: 8 hours training
Materials: Documentation + practice environments
```

---

## ✅ Conclusion

**Data Safety Summary:**
1. ✅ **ZERO data loss** dengan backup & replication strategy
2. ✅ **Parallel running** ensures no disruption
3. ✅ **Point-in-time recovery** untuk accidental deletions
4. ✅ **Quick rollback** jika ada masalah (< 5 menit)
5. ✅ **Gradual migration** reduces risk
6. ✅ **24/7 monitoring** untuk detect issues early

**Timeline untuk Database Migration:**
- Phase 1 (Shared DB): 0 risk - IMMEDIATE
- Phase 2 (Gradual split): Low risk - 6 months
- Phase 3 (Complete separation): Controlled risk - 12 months

**Recommendation:**
Start dengan Phase 1 (Shared Database) untuk 3-6 bulan. Ini memberikan semua benefit microservices TANPA risk database migration. Setelah confident, baru lanjut ke Phase 2 & 3.

---

**Next Document:** [05_LOCAL_DEPLOYMENT_GUIDE.md](./05_LOCAL_DEPLOYMENT_GUIDE.md)  
**Related:** [03_MIGRATION_ROADMAP.md](./03_MIGRATION_ROADMAP.md)
