# Database Documentation — ITApp

> **Framework:** Laravel 10 · **Database:** MySQL 8.0 · **ORM:** Eloquent
> **Compliance:** ISO 27001 · GDPR · SOC 2
> **Language:** Indonesian & English (bilingual)

---

## Table of Contents

1. [Database Structure Overview](#1-database-structure-overview)
2. [ER Diagram (ASCII)](#2-er-diagram-ascii)
3. [Complete SQL Schema (CREATE TABLE)](#3-complete-sql-schema-create-table)
4. [Laravel 10 Migration Files](#4-laravel-10-migration-files)
5. [Eloquent Model Relationships](#5-eloquent-model-relationships)
6. [Query Examples for DataTable](#6-query-examples-for-datatable)
7. [Yajra DataTables Integration](#7-yajra-datatables-integration)
8. [Performance Indexes & Optimization](#8-performance-indexes--optimization)
9. [GDPR & Audit Logging (ISO 27001)](#9-gdpr--audit-logging-iso-27001)

---

## 1. Database Structure Overview

The database consists of **20+ tables** grouped into functional modules:

| # | Module | Tables |
|---|--------|--------|
| A | **Auth & Users** | `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` |
| B | **Tickets** | `tickets`, `ticket_history`, `ticket_comments`, `ticket_assets`, `tickets_statuses`, `tickets_types`, `tickets_priorities`, `sla_policies` |
| C | **Assets** | `assets`, `asset_models`, `asset_types`, `asset_categories`, `asset_maintenance_logs`, `asset_lifecycle_events`, `asset_requests`, `purchase_orders` |
| D | **Meeting Rooms** | `meeting_rooms`, `meeting_room_bookings`, `meeting_room_display_settings` |
| E | **Reference / Master** | `locations`, `divisions`, `departments`, `suppliers`, `manufacturers`, `statuses`, `warranty_types`, `invoices`, `movements` |
| F | **Audit / Compliance** | `audit_logs`, `activity_log`, `daily_activities` |
| G | **System** | `sessions`, `jobs`, `cache`, `menus`, `menu_user`, `notification_settings` |

---

## 2. ER Diagram (ASCII)

```
+------------------+         +------------------+
|      users       |         |      roles       |
|------------------|         |------------------|
| id (PK)          |<------->| id (PK)          |
| username         |  M:M    | name             |
| email            | (pivot  | guard_name       |
| name             | model_  | access_level     |
| role_id (FK)  ---|---> id  | description      |
| division_id (FK) |  has_   +------------------+
| location_id (FK) |  roles) |   permissions    |
| is_active        |         |------------------|
| last_login_at    |         | id (PK)          |
+------------------+         | name             |
        |                    | guard_name       |
        |  1:N               +------------------+
        v
+------------------+      +------------------+      +---------------------+
|     tickets      |      | ticket_history   |      |  ticket_comments    |
|------------------|      |------------------|      |---------------------|
| id (PK)          |----->| id (PK)          |      | id (PK)             |
| ticket_code      | 1:N  | ticket_id (FK)   |      | ticket_id (FK)      |
| user_id (FK)     |      | field_changed    |      | user_id (FK)        |
| assigned_to (FK) |      | old_value        |      | comment             |
| ticket_status_id |      | new_value        |      | is_internal         |
| ticket_type_id   |      | changed_by_user  |      +---------------------+
| ticket_priority_id|     | changed_at       |
| location_id (FK) |      +------------------+
| sla_due          |
| resolved_at      |           ticket_assets (pivot M:M)
+------------------+     +------------------------------+
        |                | ticket_id (FK) | asset_id(FK)|
        |                +------------------------------+
        v
+------------------+      +----------------------+
|     assets       |      | asset_maintenance_   |
|------------------|      | logs                 |
| id (PK)          |----->|----------------------|
| asset_tag        | 1:N  | id (PK)              |
| qr_code          |      | asset_id (FK)        |
| model_id (FK)    |      | status               |
| division_id (FK) |      | notes                |
| location_id (FK) |      | performed_by (FK)    |
| status_id (FK)   |      +----------------------+
| assigned_to (FK) |
| purchase_order_id|      +----------------------+
+------------------+      |   asset_requests     |
                          |----------------------|
                          | id (PK)              |
                          | requested_by (FK)    |
                          | asset_type_id (FK)   |
                          | approved_by (FK)     |
                          | fulfilled_asset_id   |
                          | status               |
                          +----------------------+

+------------------------+      +------------------+
|  meeting_room_bookings |      |  meeting_rooms   |
|------------------------|      |------------------|
| id (PK)                |      | id (PK)          |
| room_id (FK)           |----->| name             |
| user_id (FK)           |      | code             |
| requester_name         |      | location_id (FK) |
| start_datetime         |      | capacity         |
| end_datetime           |      | status           |
| status                 |      +------------------+
| approved_by (FK)       |
| manager_id (FK)        |
+------------------------+

+------------------+    +------------------+      +------------------+      +------------------+
|    factory       |    |    locations     |      |    divisions     |      |   departments    |
|------------------|    |------------------|      |------------------|      |------------------|
| id (PK)          |    | id (PK)          |      | id (PK)          |      | id (PK)          |
| name             |    | location_name    |      | name             |      | name             |
|                  |    | name             |      | location_id (FK) |      | code             |
+------------------+    | factory_id (FK)  |      +------------------+      | description      |
                        | building         |                                +------------------+
                        | office           |                        
                        +------------------+
+------------------+
|   audit_logs     |
|------------------|
| id (PK)          |
| user_id (FK)     |
| action           |
| model_type       |
| model_id         |
| old_values (JSON)|
| new_values (JSON)|
| ip_address       |
| created_at       |
+------------------+
```

---

## 3. Complete SQL Schema (CREATE TABLE)

### 3.1 Users & Auth Module

```sql
-- ============================================================
-- users
-- ============================================================
CREATE TABLE `users` (
    `id`                          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`                    VARCHAR(150) NULL UNIQUE,
    `first_name`                  VARCHAR(120) NULL,
    `last_name`                   VARCHAR(120) NULL,
    `name`                        VARCHAR(255) NOT NULL,
    `email`                       VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at`           TIMESTAMP NULL,
    `password`                    VARCHAR(255) NOT NULL,
    `api_token`                   VARCHAR(80) NULL UNIQUE,
    `role_id`                     INT UNSIGNED NULL,
    `division_id`                 INT UNSIGNED NULL,
    `location_id`                 INT UNSIGNED NULL,
    `phone`                       VARCHAR(30) NULL,
    `profile_picture`             VARCHAR(255) NULL,
    `portal_preferences`          JSON NULL COMMENT 'Bilingual toggle, role badge, theme prefs',
    `notify_email`                TINYINT(1) NOT NULL DEFAULT 1,
    `notify_ticket_created`       TINYINT(1) NOT NULL DEFAULT 1,
    `notify_ticket_assigned`      TINYINT(1) NOT NULL DEFAULT 1,
    `notify_ticket_updated`       TINYINT(1) NOT NULL DEFAULT 1,
    `notify_meeting_approved`     TINYINT(1) NOT NULL DEFAULT 1,
    `notify_meeting_rejected`     TINYINT(1) NOT NULL DEFAULT 1,
    `is_active`                   TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at`               TIMESTAMP NULL,
    `remember_token`              VARCHAR(100) NULL,
    `created_at`                  TIMESTAMP NULL,
    `updated_at`                  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_users_role_active_login` (`role_id`, `is_active`, `last_login_at`),
    KEY `idx_users_division` (`division_id`),
    KEY `idx_users_location` (`location_id`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_users_division` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_users_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- roles  (Spatie Permission compatible)
-- ============================================================
CREATE TABLE `roles` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(125) NOT NULL,              -- VARCHAR(125) matches Spatie Permission's default schema
    `guard_name`    VARCHAR(125) NOT NULL DEFAULT 'web',  -- VARCHAR(125) matches Spatie Permission's default schema
    `access_level`  TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=Guest,2=User,3=Staff,4=Admin,5=Super Admin',
    `description`   VARCHAR(255) NULL,                    -- Free-text, 255 is appropriate here
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_name_guard_unique` (`name`, `guard_name`),
    KEY `idx_roles_access_level` (`access_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- permissions  (Spatie Permission compatible)
-- ============================================================
CREATE TABLE `permissions` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(125) NOT NULL,
    `guard_name`    VARCHAR(125) NOT NULL DEFAULT 'web',
    `description`   VARCHAR(255) NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_name_guard_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- role_has_permissions  (pivot: role M:M permissions)
-- ============================================================
CREATE TABLE `role_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `role_id`       INT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `role_id`),
    KEY `idx_rhp_role` (`role_id`),
    CONSTRAINT `fk_rhp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rhp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- model_has_roles  (pivot: user/model M:M roles)
-- ============================================================
CREATE TABLE `model_has_roles` (
    `role_id`       INT UNSIGNED NOT NULL,
    `model_type`    VARCHAR(125) NOT NULL,
    `model_id`      BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `model_id`, `model_type`),
    KEY `idx_mhr_model` (`model_id`, `model_type`),
    CONSTRAINT `fk_mhr_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- model_has_permissions  (pivot: user/model M:M permissions)
-- ============================================================
CREATE TABLE `model_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `model_type`    VARCHAR(125) NOT NULL,
    `model_id`      BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
    KEY `idx_mhp_model` (`model_id`, `model_type`),
    CONSTRAINT `fk_mhp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.2 Tickets Module

```sql
-- ============================================================
-- tickets_statuses
-- ============================================================
CREATE TABLE `tickets_statuses` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `status`     VARCHAR(50) NOT NULL,
    `color`      VARCHAR(20) NULL COMMENT 'Bootstrap color class: success, warning, danger',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Seeds: Open, In Progress, Pending, Resolved, Closed

-- ============================================================
-- tickets_types
-- ============================================================
CREATE TABLE `tickets_types` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Seeds: Hardware, Software, Network, General

-- ============================================================
-- tickets_priorities
-- ============================================================
CREATE TABLE `tickets_priorities` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50) NOT NULL,
    `color`      VARCHAR(20) NULL,
    `sla_hours`  INT NOT NULL DEFAULT 72 COMMENT 'Target resolution in hours',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Seeds: 1=Urgent(4h), 2=High(24h), 3=Medium(72h), 4=Low(168h)

-- ============================================================
-- sla_policies
-- ============================================================
CREATE TABLE `sla_policies` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`                VARCHAR(255) NOT NULL,
    `description`         TEXT NULL,
    `response_time`       INT NOT NULL COMMENT 'Minutes to first response',
    `resolution_time`     INT NOT NULL COMMENT 'Minutes to resolution',
    `priority_id`         INT UNSIGNED NULL,
    `business_hours_only` TINYINT(1) NOT NULL DEFAULT 1,
    `escalation_time`     INT NULL COMMENT 'Minutes before escalation',
    `escalate_to_user_id` BIGINT UNSIGNED NULL,
    `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_sla_priority` (`priority_id`),
    KEY `idx_sla_active`   (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- tickets
-- ============================================================
CREATE TABLE `tickets` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_code`         VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g. TKT-20251015-001',
    `user_id`             INT UNSIGNED NOT NULL COMMENT 'Reporter',
    `location_id`         INT UNSIGNED NOT NULL,
    `ticket_status_id`    INT UNSIGNED NOT NULL,
    `ticket_type_id`      INT UNSIGNED NOT NULL,
    `ticket_priority_id`  INT UNSIGNED NOT NULL,
    `subject`             VARCHAR(255) NOT NULL,
    `description`         TEXT NOT NULL,
    `assigned_to`         INT UNSIGNED NULL COMMENT 'Assigned technician',
    `assigned_at`         TIMESTAMP NULL,
    `assignment_type`     ENUM('auto','manual','super_admin') NOT NULL DEFAULT 'auto',
    `asset_id`            INT UNSIGNED NULL COMMENT 'Related asset (optional)',
    `sla_due`             TIMESTAMP NULL COMMENT 'SLA deadline (calculated from priority)',
    `sla_due_date`        DATETIME NULL COMMENT 'Alias kept for backward compatibility',
    `first_response_at`   TIMESTAMP NULL,
    `resolved_at`         TIMESTAMP NULL,
    `closed`              DATETIME NULL,
    `resolution_notes`    TEXT NULL,
    `status_history`      JSON NULL COMMENT 'Legacy JSON audit trail',
    `title`               VARCHAR(255) NULL COMMENT 'Alias for subject; kept for legacy code',
    `status`              VARCHAR(50) NULL COMMENT 'Denormalized; canonical = ticket_status_id',
    `priority`            VARCHAR(50) NULL COMMENT 'Denormalized; canonical = ticket_priority_id',
    `category`            VARCHAR(100) NULL,
    `assigned_agent_id`   INT UNSIGNED NULL COMMENT 'Alias for assigned_to (legacy)',
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tickets_status_priority_sla`   (`ticket_status_id`, `ticket_priority_id`, `sla_due`),
    KEY `idx_tickets_user_status_created`   (`user_id`, `ticket_status_id`, `created_at`),
    KEY `idx_tickets_assigned_status`       (`assigned_to`, `ticket_status_id`),
    KEY `idx_tickets_location`              (`location_id`),
    KEY `idx_tickets_asset`                 (`asset_id`),
    CONSTRAINT `fk_tickets_user`     FOREIGN KEY (`user_id`)            REFERENCES `users` (`id`),
    CONSTRAINT `fk_tickets_location` FOREIGN KEY (`location_id`)        REFERENCES `locations` (`id`),
    CONSTRAINT `fk_tickets_status`   FOREIGN KEY (`ticket_status_id`)   REFERENCES `tickets_statuses` (`id`),
    CONSTRAINT `fk_tickets_type`     FOREIGN KEY (`ticket_type_id`)     REFERENCES `tickets_types` (`id`),
    CONSTRAINT `fk_tickets_priority` FOREIGN KEY (`ticket_priority_id`) REFERENCES `tickets_priorities` (`id`),
    CONSTRAINT `fk_tickets_assigned` FOREIGN KEY (`assigned_to`)        REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tickets_asset`    FOREIGN KEY (`asset_id`)           REFERENCES `assets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ticket_history  (immutable audit trail for ticket changes)
-- ============================================================
CREATE TABLE `ticket_history` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`          INT UNSIGNED NOT NULL,
    `user_id`            INT UNSIGNED NULL COMMENT 'Who triggered the event',
    `event_type`         VARCHAR(50) NOT NULL COMMENT 'field_change, comment, status_change',
    `field_changed`      VARCHAR(100) NULL,
    `old_value`          TEXT NULL,
    `new_value`          TEXT NULL,
    `changed_by_user_id` INT UNSIGNED NULL,
    `changed_at`         TIMESTAMP NULL,
    `change_type`        VARCHAR(50) NULL,
    `data`               JSON NULL COMMENT 'Extra payload for complex events',
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_th_ticket`  (`ticket_id`),
    KEY `idx_th_user`    (`user_id`),
    KEY `idx_th_event`   (`event_type`),
    KEY `idx_th_changed` (`changed_at`),
    CONSTRAINT `fk_th_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_th_user`   FOREIGN KEY (`user_id`)   REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ticket_comments
-- ============================================================
CREATE TABLE `ticket_comments` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`   INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL,
    `comment`     TEXT NOT NULL,
    `is_internal` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=public, 1=internal staff only',
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tc_ticket_internal` (`ticket_id`, `is_internal`),
    KEY `idx_tc_user_created`    (`user_id`, `created_at`),
    CONSTRAINT `fk_tc_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tc_user`   FOREIGN KEY (`user_id`)   REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ticket_assets  (pivot: ticket M:M assets)
-- ============================================================
CREATE TABLE `ticket_assets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`  INT UNSIGNED NOT NULL,
    `asset_id`   INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ticket_asset` (`ticket_id`, `asset_id`),
    CONSTRAINT `fk_ta_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ta_asset`  FOREIGN KEY (`asset_id`)  REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.3 Assets Module

```sql
-- ============================================================
-- manufacturers
-- ============================================================
CREATE TABLE `manufacturers` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- asset_types
-- ============================================================
CREATE TABLE `asset_types` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_name`   VARCHAR(100) NOT NULL,
    `name`        VARCHAR(100) NULL COMMENT 'Canonical alias for type_name',
    `spare`       TINYINT(1) NOT NULL DEFAULT 0,
    `description` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- asset_categories
-- ============================================================
CREATE TABLE `asset_categories` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `code`        VARCHAR(10)  NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `asset_categories_name_unique` (`name`),
    UNIQUE KEY `asset_categories_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Default seeds: AST=Asset, SPR=Sparepart, CNS=Consumable, TOL=Tools, LCE=License, VND=Vendor

-- ============================================================
-- asset_models
-- ============================================================
CREATE TABLE `asset_models` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_model`     VARCHAR(100) NOT NULL,
    `manufacturer_id` INT UNSIGNED NOT NULL,
    `asset_type_id`   INT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_am_type`         (`asset_type_id`),
    KEY `idx_am_manufacturer` (`manufacturer_id`),
    CONSTRAINT `fk_am_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`),
    CONSTRAINT `fk_am_type`         FOREIGN KEY (`asset_type_id`)   REFERENCES `asset_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- suppliers
-- ============================================================
CREATE TABLE `suppliers` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(100) NULL,
    `phone`      VARCHAR(30) NULL,
    `address`    TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- warranty_types
-- ============================================================
CREATE TABLE `warranty_types` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `warranty_type` VARCHAR(50) NOT NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- statuses  (asset status master data)
-- ============================================================
CREATE TABLE `statuses` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50) NOT NULL,
    `color`      VARCHAR(20) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Seeds: 1=In Use, 2=Available, 3=Under Maintenance, 4=Retired, 5=Lost

-- ============================================================
-- purchase_orders
-- ============================================================
CREATE TABLE `purchase_orders` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `po_number`   VARCHAR(100) NOT NULL UNIQUE,
    `supplier_id` INT UNSIGNED NULL,
    `order_date`  DATE NULL,
    `total_cost`  DECIMAL(15,2) NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_po_supplier` (`supplier_id`),
    CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- assets
-- ============================================================
CREATE TABLE `assets` (
    `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_tag`                VARCHAR(20) NOT NULL UNIQUE,
    `qr_code`                  VARCHAR(100) NULL UNIQUE COMMENT 'Auto-generated UUID: AST-<uniqid> (distinct from asset_tag which is a short sequential label)',
    `rfid_tag`                 VARCHAR(100) NULL UNIQUE,
    `serial_number`            VARCHAR(50) NULL,
    `name`                     VARCHAR(255) NULL COMMENT 'Denormalized from model for quick display',
    `model_id`                 INT UNSIGNED NOT NULL,
    `division_id`              INT UNSIGNED NOT NULL,
    `location_id`              INT UNSIGNED NULL,
    `supplier_id`              INT UNSIGNED NULL,
    `supplier_name`            VARCHAR(255) NULL COMMENT 'Denormalized for quick display',
    `invoice_id`               INT UNSIGNED NULL,
    `purchase_order_id`        INT UNSIGNED NULL,
    `movement_id`              INT UNSIGNED NULL,
    `purchase_date`            DATE NULL,
    `warranty_months`          INT NULL,
    `warranty_type_id`         INT UNSIGNED NULL,
    `warranty_expiration_date` DATETIME NULL,
    `cost`                     DECIMAL(15,2) NULL,
    `ip_address`               VARCHAR(45) NULL,
    `mac_address`              VARCHAR(17) NULL,
    `status_id`                INT UNSIGNED NOT NULL DEFAULT 1,
    `assigned_to`              INT UNSIGNED NULL COMMENT 'Canonical FK — use this for queries and FK constraints',
    `assigned_user_id`         INT UNSIGNED NULL COMMENT 'Legacy alias mirrored from assigned_to by AssetObserver; kept for backward compat only',
    `notes`                    TEXT NULL,
    `image`                    VARCHAR(255) NULL,
    -- Lifecycle JSON audit fields
    -- NOTE: These JSON columns are legacy denormalized caches carried over from the original schema.
    -- For new code, prefer the relational tables: ticket_assets, asset_maintenance_logs, movements.
    -- These columns are maintained for backward compatibility and fast read access in list views.
    `ticket_history`           JSON NULL,
    `maintenance_history`      JSON NULL,
    `disposal_history`         JSON NULL,
    `lending_history`          JSON NULL,
    `return_history`           JSON NULL,
    `location_history`         JSON NULL,
    `depreciation_schedule`    JSON NULL,
    -- Denormalized display fields
    `category`                 VARCHAR(100) NULL,
    `asset_type`               VARCHAR(100) NULL,
    `brand`                    VARCHAR(100) NULL,
    `factory`                  VARCHAR(100) NULL,
    `building`                 VARCHAR(100) NULL,
    `department`               VARCHAR(100) NULL,
    `location_name`            VARCHAR(255) NULL,
    `status_name`              VARCHAR(100) NULL,
    `code`                     VARCHAR(50) NULL,
    -- Maintenance lifecycle
    `maintenance_schedule`     VARCHAR(50) NULL,
    `maintenance_status`       ENUM('scheduled','in_progress','completed') NULL,
    `maintenance_notes`        TEXT NULL,
    -- Disposal lifecycle
    `disposal_status`          ENUM('pending','approved','completed') NULL,
    `disposal_notes`           TEXT NULL,
    -- Lending lifecycle
    `lending_status`           ENUM('pending','approved','completed') NULL,
    `lending_notes`            TEXT NULL,
    -- Return lifecycle
    `return_status`            ENUM('pending','approved','completed') NULL,
    `return_notes`             TEXT NULL,
    `deleted_at`               TIMESTAMP NULL COMMENT 'Soft delete support',
    `created_at`               TIMESTAMP NULL,
    `updated_at`               TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_assets_lookup_filters`       (`status_id`, `division_id`, `purchase_date`),
    KEY `idx_assets_assigned_maintenance` (`assigned_to`, `maintenance_status`),
    KEY `idx_assets_warranty`             (`warranty_expiration_date`),
    KEY `idx_assets_deleted`              (`deleted_at`),
    CONSTRAINT `fk_assets_model`         FOREIGN KEY (`model_id`)         REFERENCES `asset_models` (`id`),
    CONSTRAINT `fk_assets_division`      FOREIGN KEY (`division_id`)      REFERENCES `divisions` (`id`),
    CONSTRAINT `fk_assets_location`      FOREIGN KEY (`location_id`)      REFERENCES `locations` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_assets_supplier`      FOREIGN KEY (`supplier_id`)      REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_assets_status`        FOREIGN KEY (`status_id`)        REFERENCES `statuses` (`id`),
    CONSTRAINT `fk_assets_assigned`      FOREIGN KEY (`assigned_to`)      REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_assets_assigned_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- asset_requests  (Purchase Requests / Asset Procurement)
-- ============================================================
CREATE TABLE `asset_requests` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_number`     VARCHAR(20) NULL UNIQUE COMMENT 'e.g. AR-2025-0001',
    `requested_by`       INT UNSIGNED NOT NULL,
    `user_id`            INT UNSIGNED NULL COMMENT 'Alias kept in sync with requested_by',
    `asset_type_id`      INT UNSIGNED NULL,
    `item_name`          VARCHAR(255) NULL,
    `category`           VARCHAR(100) NULL,
    `quantity`           INT NOT NULL DEFAULT 1,
    `justification`      TEXT NOT NULL,
    `priority`           ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `status`             VARCHAR(50) NOT NULL DEFAULT 'pending',
    `vendor`             VARCHAR(255) NULL,
    `estimated_cost`     DECIMAL(15,2) NULL,
    `actual_cost`        DECIMAL(15,2) NULL,
    `delivery_date`      DATE NULL,
    `receipt_image`      VARCHAR(255) NULL,
    `purchase_notes`     TEXT NULL,
    `approval_history`   JSON NULL,
    `purchase_history`   JSON NULL,
    `approved_by`        INT UNSIGNED NULL,
    `approved_at`        TIMESTAMP NULL,
    `approval_notes`     TEXT NULL,
    `fulfilled_asset_id` INT UNSIGNED NULL,
    `fulfilled_at`       TIMESTAMP NULL,
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ar_requester_status` (`requested_by`, `status`, `created_at`),
    KEY `idx_ar_procurement`      (`status`, `approved_at`),
    CONSTRAINT `fk_ar_requester` FOREIGN KEY (`requested_by`)      REFERENCES `users` (`id`),
    CONSTRAINT `fk_ar_type`      FOREIGN KEY (`asset_type_id`)     REFERENCES `asset_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ar_approver`  FOREIGN KEY (`approved_by`)       REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ar_asset`     FOREIGN KEY (`fulfilled_asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- asset_maintenance_logs
-- ============================================================
CREATE TABLE `asset_maintenance_logs` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id`     INT UNSIGNED NOT NULL,
    `status`       ENUM('planned','in_progress','completed') NOT NULL DEFAULT 'planned',
    `notes`        TEXT NULL,
    `performed_by` INT UNSIGNED NULL,
    `performed_at` TIMESTAMP NULL,
    `cost`         DECIMAL(15,2) NULL,
    `created_at`   TIMESTAMP NULL,
    `updated_at`   TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_aml_asset`  (`asset_id`),
    KEY `idx_aml_status` (`status`),
    CONSTRAINT `fk_aml_asset` FOREIGN KEY (`asset_id`)     REFERENCES `assets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_aml_user`  FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.4 Meeting Rooms Module

```sql
-- ============================================================
-- meeting_rooms  (master data)
-- ============================================================
CREATE TABLE `meeting_rooms` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255) NOT NULL UNIQUE,
    `code`        VARCHAR(50) NULL UNIQUE COMMENT 'e.g. MR-001',
    `location_id` INT UNSIGNED NULL,
    `capacity`    SMALLINT UNSIGNED NULL,
    `status`      ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
    `description` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_mr_status`   (`status`),
    KEY `idx_mr_location` (`location_id`),
    CONSTRAINT `fk_mr_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- meeting_room_bookings
-- ============================================================
CREATE TABLE `meeting_room_bookings` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_name`           VARCHAR(255) NOT NULL COMMENT 'Denormalized for display',
    `room_id`             INT UNSIGNED NULL,
    `user_id`             INT UNSIGNED NOT NULL COMMENT 'Account that created the booking',
    `requester_name`      VARCHAR(255) NULL COMMENT 'Physical requester (may differ from user)',
    `department`          VARCHAR(100) NULL COMMENT 'Bagian/Departemen pemohon',
    `requester_position`  VARCHAR(100) NULL COMMENT 'Jabatan Pemohon',
    `start_datetime`      DATETIME NOT NULL,
    `start_time`          DATETIME NULL COMMENT 'Alias kept for backward compatibility',
    `end_datetime`        DATETIME NOT NULL,
    `end_time`            DATETIME NULL COMMENT 'Alias kept for backward compatibility',
    `purpose`             TEXT NOT NULL COMMENT 'Keperluan Rapat',
    `meeting_description` TEXT NULL COMMENT 'Deskripsi/Keterangan Rapat',
    `meeting_needs`       TEXT NULL COMMENT 'Fasilitas/Keperluan Tambahan',
    `attendees_count`     INT NOT NULL DEFAULT 1,
    `status`              ENUM('pending','approved','rejected','cancelled','finished') NOT NULL DEFAULT 'pending',
    `director_notes`      TEXT NULL,
    `approved_by`         INT UNSIGNED NULL,
    `approved_at`         TIMESTAMP NULL,
    `manager_id`          INT UNSIGNED NULL COMMENT 'Mengetahui (acknowledged by)',
    `manager_approved_at` TIMESTAMP NULL,
    `finished_at`         TIMESTAMP NULL,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_mrb_status`         (`status`),
    KEY `idx_mrb_start`          (`start_datetime`),
    KEY `idx_mrb_room_date`      (`room_name`, `start_datetime`),
    KEY `idx_mrb_booking_window` (`room_id`, `start_datetime`, `end_datetime`, `status`),
    CONSTRAINT `fk_mrb_user`     FOREIGN KEY (`user_id`)     REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mrb_room`     FOREIGN KEY (`room_id`)     REFERENCES `meeting_rooms` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_mrb_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_mrb_manager`  FOREIGN KEY (`manager_id`)  REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.5 Reference / Master Data

```sql
-- ============================================================
-- locations
-- ============================================================
CREATE TABLE `locations` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `location_name` VARCHAR(100) NOT NULL,
    `name`          VARCHAR(120) NULL COMMENT 'Canonical alias for location_name',
    `building`      VARCHAR(100) NULL,
    `office`        VARCHAR(100) NULL,
    `description`   VARCHAR(255) NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_loc_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- divisions
-- ============================================================
CREATE TABLE `divisions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `location_id` INT UNSIGNED NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_div_location` (`location_id`),
    CONSTRAINT `fk_div_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- departments
-- ============================================================
CREATE TABLE `departments` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL UNIQUE,
    `code`        VARCHAR(10) NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- factory
-- ============================================================
CREATE TABLE `factory` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL UNIQUE,
    `code`        VARCHAR(10) NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

-- ============================================================
-- movements  (asset movement / transfer log)
-- ============================================================
CREATE TABLE `movements` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id`    INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NULL,
    `location_id` INT UNSIGNED NULL,
    `status_id`   INT UNSIGNED NULL,
    `notes`       TEXT NULL,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_mov_asset_created` (`asset_id`, `created_at`),
    KEY `idx_mov_composite`     (`asset_id`, `user_id`, `created_at`),
    CONSTRAINT `fk_mov_asset`    FOREIGN KEY (`asset_id`)    REFERENCES `assets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mov_user`     FOREIGN KEY (`user_id`)     REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_mov_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_mov_status`   FOREIGN KEY (`status_id`)   REFERENCES `statuses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.6 Audit / Compliance

```sql
-- ============================================================
-- audit_logs  (ISO 27001 / GDPR compliant, immutable)
-- IMPORTANT: Do NOT grant DELETE privilege on this table!
-- ============================================================
CREATE TABLE `audit_logs` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NULL COMMENT 'Nullable: system actions may not have a user',
    `action`      VARCHAR(100) NOT NULL COMMENT 'create, update, delete, login, logout, export',
    `model_type`  VARCHAR(100) NULL COMMENT 'FQCN e.g. App\\Ticket',
    `model_id`    BIGINT UNSIGNED NULL,
    `old_values`  TEXT NULL COMMENT 'JSON of previous state',
    `new_values`  TEXT NULL COMMENT 'JSON of new state',
    `ip_address`  VARCHAR(45) NULL,
    `user_agent`  TEXT NULL,
    `description` TEXT NULL COMMENT 'Human-readable summary',
    `event_type`  VARCHAR(50) NOT NULL DEFAULT 'model' COMMENT 'model, auth, system',
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_audit_user`            (`user_id`),
    KEY `idx_audit_model_type`      (`model_type`),
    KEY `idx_audit_model_id`        (`model_id`),
    KEY `idx_audit_action`          (`action`),
    KEY `idx_audit_model_composite` (`model_type`, `model_id`),
    KEY `idx_audit_created`         (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Immutable audit log — do NOT allow DELETE on this table';
```

---

## 4. Laravel 10 Migration Files

> **Cara Penggunaan:** Salin file ke `database/migrations/` lalu jalankan `php artisan migrate`.
> Semua migrasi menggunakan `Schema::hasTable()` dan `Schema::hasColumn()` agar idempoten (aman dijalankan berulang).

### 4.1 Migrasi Users — Tambah Kolom

```php
<?php
// database/migrations/2025_01_01_000001_extend_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 150)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 120)->nullable()->after('username');
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 120)->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->unsignedInteger('role_id')->nullable()->index()->after('last_name');
            }
            if (! Schema::hasColumn('users', 'division_id')) {
                $table->unsignedInteger('division_id')->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'location_id')) {
                $table->unsignedInteger('location_id')->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
            if (! Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture', 255)->nullable();
            }
            if (! Schema::hasColumn('users', 'portal_preferences')) {
                $table->json('portal_preferences')->nullable()
                      ->comment('Bilingual toggle, role badge, theme preferences');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->index();
            }
            // Notification preferences
            $notifyColumns = [
                'notify_email', 'notify_ticket_created', 'notify_ticket_assigned',
                'notify_ticket_updated', 'notify_meeting_approved', 'notify_meeting_rejected',
            ];
            foreach ($notifyColumns as $col) {
                if (! Schema::hasColumn('users', $col)) {
                    $table->boolean($col)->default(true);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'first_name', 'last_name', 'role_id',
                'division_id', 'location_id', 'phone', 'profile_picture',
                'portal_preferences', 'is_active', 'last_login_at',
                'notify_email', 'notify_ticket_created', 'notify_ticket_assigned',
                'notify_ticket_updated', 'notify_meeting_approved', 'notify_meeting_rejected',
            ]);
        });
    }
};
```

### 4.2 Migrasi Tickets — Schema Lengkap

```php
<?php
// database/migrations/2025_01_01_000010_create_tickets_full_schema.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Tickets Statuses ---
        if (! Schema::hasTable('tickets_statuses')) {
            Schema::create('tickets_statuses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('status', 50);
                $table->string('color', 20)->nullable();
                $table->timestamps();
            });
            DB::table('tickets_statuses')->insert([
                ['status' => 'Open',        'color' => 'danger',   'created_at' => now(), 'updated_at' => now()],
                ['status' => 'In Progress', 'color' => 'warning',  'created_at' => now(), 'updated_at' => now()],
                ['status' => 'Pending',     'color' => 'info',     'created_at' => now(), 'updated_at' => now()],
                ['status' => 'Resolved',    'color' => 'success',  'created_at' => now(), 'updated_at' => now()],
                ['status' => 'Closed',      'color' => 'secondary','created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- Tickets Types ---
        if (! Schema::hasTable('tickets_types')) {
            Schema::create('tickets_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 50);
                $table->timestamps();
            });
            DB::table('tickets_types')->insert([
                ['type' => 'Hardware', 'created_at' => now(), 'updated_at' => now()],
                ['type' => 'Software', 'created_at' => now(), 'updated_at' => now()],
                ['type' => 'Network',  'created_at' => now(), 'updated_at' => now()],
                ['type' => 'General',  'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- Tickets Priorities ---
        if (! Schema::hasTable('tickets_priorities')) {
            Schema::create('tickets_priorities', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('color', 20)->nullable();
                $table->integer('sla_hours')->default(72);
                $table->timestamps();
            });
            DB::table('tickets_priorities')->insert([
                ['name' => 'Urgent', 'color' => 'danger',  'sla_hours' => 4,   'created_at' => now(), 'updated_at' => now()],
                ['name' => 'High',   'color' => 'warning', 'sla_hours' => 24,  'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Medium', 'color' => 'info',    'sla_hours' => 72,  'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Low',    'color' => 'success', 'sla_hours' => 168, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // --- Tickets ---
        if (! Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ticket_code', 50)->unique();
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('location_id');
                $table->unsignedInteger('ticket_status_id');
                $table->unsignedInteger('ticket_type_id');
                $table->unsignedInteger('ticket_priority_id');
                $table->string('subject', 255);
                $table->text('description');
                $table->unsignedInteger('assigned_to')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->enum('assignment_type', ['auto','manual','super_admin'])->default('auto');
                $table->unsignedInteger('asset_id')->nullable();
                $table->timestamp('sla_due')->nullable();
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->dateTime('closed')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->json('status_history')->nullable();
                $table->timestamps();

                $table->index(['ticket_status_id', 'ticket_priority_id', 'sla_due'],
                               'idx_tickets_status_priority_sla');
                $table->index(['user_id', 'ticket_status_id', 'created_at'],
                               'idx_tickets_user_status_created');
                $table->index(['assigned_to', 'ticket_status_id'],
                               'idx_tickets_assigned_status');

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('location_id')->references('id')->on('locations');
                $table->foreign('ticket_status_id')->references('id')->on('tickets_statuses');
                $table->foreign('ticket_type_id')->references('id')->on('tickets_types');
                $table->foreign('ticket_priority_id')->references('id')->on('tickets_priorities');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            });
        }

        // --- Ticket History ---
        if (! Schema::hasTable('ticket_history')) {
            Schema::create('ticket_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('ticket_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('event_type')->index();
                $table->string('field_changed', 100)->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->unsignedInteger('changed_by_user_id')->nullable();
                $table->timestamp('changed_at')->nullable()->index();
                $table->string('change_type', 50)->nullable();
                $table->json('data')->nullable();
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // --- Ticket Comments ---
        if (! Schema::hasTable('ticket_comments')) {
            Schema::create('ticket_comments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ticket_id')->index();
                $table->unsignedInteger('user_id')->index();
                $table->text('comment');
                $table->boolean('is_internal')->default(false);
                $table->timestamps();

                $table->index(['ticket_id', 'is_internal']);
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // --- Ticket Assets (M:M pivot) ---
        if (! Schema::hasTable('ticket_assets')) {
            Schema::create('ticket_assets', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ticket_id');
                $table->unsignedInteger('asset_id');
                $table->timestamps();

                $table->unique(['ticket_id', 'asset_id']);
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_assets');
        Schema::dropIfExists('ticket_comments');
        Schema::dropIfExists('ticket_history');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('tickets_priorities');
        Schema::dropIfExists('tickets_types');
        Schema::dropIfExists('tickets_statuses');
    }
};
```

### 4.3 Migrasi Meeting Rooms

```php
<?php
// database/migrations/2025_01_01_000020_create_meeting_rooms_full_schema.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('meeting_rooms')) {
            Schema::create('meeting_rooms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->unique();
                $table->string('code', 50)->nullable()->unique();
                $table->unsignedInteger('location_id')->nullable()->index();
                $table->unsignedSmallInteger('capacity')->nullable();
                $table->enum('status', ['active','inactive','maintenance'])->default('active')->index();
                $table->string('description', 255)->nullable();
                $table->timestamps();

                $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('meeting_room_bookings')) {
            Schema::create('meeting_room_bookings', function (Blueprint $table) {
                $table->id();
                $table->string('room_name');
                $table->unsignedInteger('room_id')->nullable();
                $table->unsignedInteger('user_id');
                $table->string('requester_name')->nullable();
                $table->string('department', 100)->nullable();
                $table->string('requester_position', 100)->nullable();
                $table->dateTime('start_datetime');
                $table->dateTime('end_datetime');
                $table->text('purpose');
                $table->text('meeting_description')->nullable();
                $table->text('meeting_needs')->nullable();
                $table->integer('attendees_count')->default(1);
                $table->enum('status', ['pending','approved','rejected','cancelled','finished'])
                      ->default('pending');
                $table->text('director_notes')->nullable();
                $table->unsignedInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedInteger('manager_id')->nullable();
                $table->timestamp('manager_approved_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('start_datetime');
                $table->index(['room_name', 'start_datetime']);
                $table->index(['room_id', 'start_datetime', 'end_datetime', 'status'],
                              'idx_mrb_booking_window');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('room_id')->references('id')->on('meeting_rooms')->onDelete('set null');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_room_bookings');
        Schema::dropIfExists('meeting_rooms');
    }
};
```

### 4.4 Migrasi Audit Logs

```php
<?php
// database/migrations/2025_01_01_000030_create_audit_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->comment('Who performed the action');
            $table->string('action', 100)->comment('create, update, delete, login, logout');
            $table->string('model_type', 100)->nullable()->comment('FQCN e.g. App\\Ticket');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('old_values')->nullable()->comment('JSON before change');
            $table->text('new_values')->nullable()->comment('JSON after change');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->string('event_type', 50)->default('model')
                  ->comment('model, auth, system');
            $table->timestamps();

            $table->index('user_id', 'idx_audit_user');
            $table->index('model_type', 'idx_audit_model_type');
            $table->index('model_id', 'idx_audit_model_id');
            $table->index('action', 'idx_audit_action');
            $table->index(['model_type', 'model_id'], 'idx_audit_model_composite');
            $table->index('created_at', 'idx_audit_created');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

---

## 5. Eloquent Model Relationships

### 5.1 User Model

```php
<?php
// app/User.php — Relationships & Scopes

class User extends Authenticatable
{
    use HasRoles; // Spatie — provides roles() and permissions() relations

    /** Tickets yang dilaporkan user ini */
    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /** Tickets yang di-assign ke teknisi ini */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /** Assets yang ditugaskan ke user ini */
    public function assignedAssets()
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    /** Booking meeting rooms oleh user ini */
    public function meetingBookings()
    {
        return $this->hasMany(MeetingRoomBooking::class, 'user_id');
    }

    /** Purchase requests oleh user ini */
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class, 'requested_by');
    }

    /** Division tempat user bekerja */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /** Lokasi fisik user */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /** Primary role reference (denormalized shortcut) */
    public function primaryRoleEntity()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // ===== SCOPES =====

    /** Hanya user yang aktif */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Filter berdasarkan role name.
     * NOTE: For large datasets add an index on model_has_roles(model_id, model_type)
     * or consider a join-based alternative for reporting queries.
     */
    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', $roleName));
    }

    /** User yang sedang online (aktif dalam 5 menit terakhir) */
    public function scopeOnline($query)
    {
        return $query->where('last_login_at', '>', now()->subMinutes(5));
    }
}
```

### 5.2 Ticket Model

```php
<?php
// app/Ticket.php — Relationships & Scopes

class Ticket extends Model
{
    /** User yang melaporkan ticket */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Teknisi yang menangani ticket */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Lokasi terkait ticket */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /** Status ticket */
    public function ticket_status()
    {
        return $this->belongsTo(TicketsStatus::class, 'ticket_status_id');
    }

    /** Prioritas ticket */
    public function ticket_priority()
    {
        return $this->belongsTo(TicketsPriority::class, 'ticket_priority_id');
    }

    /** Tipe ticket (Hardware, Software, dll) */
    public function ticket_type()
    {
        return $this->belongsTo(TicketsType::class, 'ticket_type_id');
    }

    /** Satu asset utama yang terkait (backward compat) */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /** Many-to-many ke assets via pivot ticket_assets */
    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'ticket_assets', 'ticket_id', 'asset_id')
                    ->withTimestamps();
    }

    /** Riwayat perubahan ticket (immutable audit trail) */
    public function history()
    {
        return $this->hasMany(TicketHistory::class)->orderBy('changed_at', 'desc');
    }

    /** Komentar pada ticket */
    public function comments()
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'asc');
    }

    // ===== SCOPES =====

    /** Tickets yang belum selesai */
    public function scopeOpen($query)
    {
        return $query->whereHas('ticket_status', fn($q) =>
            $q->whereNotIn('status', ['Resolved', 'Closed']));
    }

    /** Tickets yang melewati SLA deadline */
    public function scopeBreachedSla($query)
    {
        return $query->whereNotNull('sla_due')
                     ->where('sla_due', '<', now())
                     ->whereNull('resolved_at');
    }

    /** Filter berdasarkan technician yang di-assign */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /** Eager loading standar untuk DataTable */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'user:id,name,email',
            'assignedTo:id,name',
            'ticket_status:id,status,color',
            'ticket_priority:id,name,color',
            'location:id,location_name',
        ]);
    }
}
```

### 5.3 Asset Model

```php
<?php
// app/Asset.php — Relationships & Scopes

class Asset extends Model
{
    use SoftDeletes;

    /** Model/tipe hardware asset */
    public function model()
    {
        return $this->belongsTo(AssetModel::class, 'model_id');
    }

    /** Tipe asset via AssetModel (HasOneThrough) */
    public function assetType()
    {
        return $this->hasOneThrough(
            AssetType::class, AssetModel::class,
            'id', 'id', 'model_id', 'asset_type_id'
        );
    }

    /** Status asset (In Use, Available, dll) */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /** Divisi pemilik asset */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /** Lokasi fisik asset */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /** Supplier/vendor asal asset */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /** User yang menggunakan asset saat ini */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Purchase Order terkait */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /** Tickets yang terkait dengan asset ini (M:M) */
    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_assets', 'asset_id', 'ticket_id')
                    ->withTimestamps();
    }

    /** Log pemeliharaan asset */
    public function maintenanceLogs()
    {
        return $this->hasMany(AssetMaintenanceLog::class)->latest();
    }

    /** Riwayat perpindahan asset */
    public function movements()
    {
        return $this->hasMany(Movement::class)->latest();
    }

    // ===== SCOPES =====

    // NOTE: Status IDs match the seed data in statuses table (1=In Use, 2=Available).
    // For robustness in production, prefer a named constant or a lookup:
    //   $availableId = Status::where('name', 'Available')->value('id');
    public function scopeAvailable($query)
    {
        return $query->where('status_id', 2); // 2 = Available (seed data default)
    }

    public function scopeInUse($query)
    {
        return $query->where('status_id', 1); // 1 = In Use (seed data default)
    }

    public function scopeWarrantyExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('warranty_expiration_date', [now(), now()->addDays($days)]);
    }

    public function scopeByDivision($query, int $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    /** Eager loading standar untuk DataTable */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'model.assetType',
            'model.manufacturer',
            'status:id,name,color',
            'division:id,name',
            'location:id,location_name',
            'assignedTo:id,name',
        ]);
    }
}
```

### 5.4 MeetingRoomBooking Model

```php
<?php
// app/MeetingRoomBooking.php — Relationships & Scopes

class MeetingRoomBooking extends Model
{
    /** User yang membuat booking */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Master data ruang meeting */
    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }

    /** Direktur/approver yang menyetujui */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Manager yang mengetahui (Mengetahui) */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // ===== SCOPES =====

    public function scopePending($query)   { return $query->where('status', 'pending'); }
    public function scopeApproved($query)  { return $query->where('status', 'approved'); }
    public function scopeUpcoming($query)  { return $query->where('start_datetime', '>=', now()); }
    public function scopeByRoom($query, int $roomId) { return $query->where('room_id', $roomId); }

    /**
     * Cek apakah waktu booking bertabrakan dengan booking lain untuk ruangan yang sama.
     * Digunakan sebelum menyimpan booking baru.
     */
    public static function hasConflict(int $roomId, string $start, string $end, int $excludeId = 0): bool
    {
        return static::where('room_id', $roomId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('id', '!=', $excludeId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                  ->orWhereBetween('end_datetime', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_datetime', '<=', $start)
                         ->where('end_datetime', '>=', $end);
                  });
            })
            ->exists();
    }
}
```

---

## 6. Query Examples for DataTable

### 6.1 Tickets DataTable (Server-Side Query)

```php
<?php
use Illuminate\Support\Facades\DB;

public function getTicketsQuery(array $filters = [])
{
    $query = DB::table('tickets as t')
        ->select([
            't.id',
            't.ticket_code',
            't.subject',
            'ts.status as status_label',
            'ts.color as status_color',
            'tp.name as priority_label',
            'tp.color as priority_color',
            'tt.type as type_label',
            'reporter.name as reporter_name',
            'reporter.email as reporter_email',
            'tech.name as assigned_name',
            'loc.location_name as location',
            't.sla_due',
            DB::raw('CASE WHEN t.sla_due < NOW() AND t.resolved_at IS NULL THEN 1 ELSE 0 END as sla_breached'),
            't.created_at',
            't.resolved_at',
        ])
        ->leftJoin('tickets_statuses as ts', 'ts.id', '=', 't.ticket_status_id')
        ->leftJoin('tickets_priorities as tp', 'tp.id', '=', 't.ticket_priority_id')
        ->leftJoin('tickets_types as tt', 'tt.id', '=', 't.ticket_type_id')
        ->leftJoin('users as reporter', 'reporter.id', '=', 't.user_id')
        ->leftJoin('users as tech', 'tech.id', '=', 't.assigned_to')
        ->leftJoin('locations as loc', 'loc.id', '=', 't.location_id');

    if (! empty($filters['status_id'])) {
        $query->where('t.ticket_status_id', $filters['status_id']);
    }
    if (! empty($filters['priority_id'])) {
        $query->where('t.ticket_priority_id', $filters['priority_id']);
    }
    if (! empty($filters['assigned_to'])) {
        $query->where('t.assigned_to', $filters['assigned_to']);
    }
    if (! empty($filters['date_from'])) {
        $query->where('t.created_at', '>=', $filters['date_from']);
    }
    if (! empty($filters['date_to'])) {
        $query->where('t.created_at', '<=', $filters['date_to'] . ' 23:59:59');
    }
    if (! empty($filters['sla_breached'])) {
        $query->where('t.sla_due', '<', now())->whereNull('t.resolved_at');
    }

    return $query;
}
```

### 6.2 Assets DataTable

```php
<?php
public function getAssetsQuery(array $filters = [])
{
    $query = DB::table('assets as a')
        ->select([
            'a.id', 'a.asset_tag', 'a.qr_code', 'a.serial_number',
            'am.asset_model as model_name',
            'at.type_name as asset_type',
            'mfr.name as manufacturer',
            'st.name as status', 'st.color as status_color',
            'div.name as division',
            'loc.location_name as location',
            'u.name as assigned_to_name',
            'a.purchase_date', 'a.warranty_expiration_date', 'a.cost',
            DB::raw('CASE WHEN a.warranty_expiration_date BETWEEN NOW()
                          AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END as warranty_expiring_soon'),
        ])
        ->join('asset_models as am', 'am.id', '=', 'a.model_id')
        ->join('asset_types as at', 'at.id', '=', 'am.asset_type_id')
        ->join('manufacturers as mfr', 'mfr.id', '=', 'am.manufacturer_id')
        ->join('statuses as st', 'st.id', '=', 'a.status_id')
        ->join('divisions as div', 'div.id', '=', 'a.division_id')
        ->leftJoin('locations as loc', 'loc.id', '=', 'a.location_id')
        ->leftJoin('users as u', 'u.id', '=', 'a.assigned_to')
        ->whereNull('a.deleted_at');

    if (! empty($filters['status_id'])) {
        $query->where('a.status_id', $filters['status_id']);
    }
    if (! empty($filters['division_id'])) {
        $query->where('a.division_id', $filters['division_id']);
    }
    if (! empty($filters['warranty_expiring'])) {
        $query->whereBetween('a.warranty_expiration_date', [now(), now()->addDays(30)]);
    }

    return $query;
}
```

### 6.3 Meeting Room Bookings DataTable

```php
<?php
public function getMeetingBookingsQuery(array $filters = [])
{
    $query = DB::table('meeting_room_bookings as mrb')
        ->select([
            'mrb.id', 'mrb.room_name', 'mrb.requester_name', 'mrb.department',
            'mrb.start_datetime', 'mrb.end_datetime',
            DB::raw('TIMESTAMPDIFF(MINUTE, mrb.start_datetime, mrb.end_datetime) as duration_minutes'),
            'mrb.purpose', 'mrb.attendees_count', 'mrb.status',
            'u.name as booked_by', 'u.email as booked_by_email',
            'approver.name as approved_by_name',
            'mrb.approved_at', 'mrb.director_notes',
            'mr.capacity as room_capacity',
            'loc.location_name as room_location',
        ])
        ->join('users as u', 'u.id', '=', 'mrb.user_id')
        ->leftJoin('users as approver', 'approver.id', '=', 'mrb.approved_by')
        ->leftJoin('meeting_rooms as mr', 'mr.id', '=', 'mrb.room_id')
        ->leftJoin('locations as loc', 'loc.id', '=', 'mr.location_id');

    if (! empty($filters['status'])) {
        $query->where('mrb.status', $filters['status']);
    }
    if (! empty($filters['room_id'])) {
        $query->where('mrb.room_id', $filters['room_id']);
    }
    if (! empty($filters['date'])) {
        $query->whereDate('mrb.start_datetime', $filters['date']);
    }

    return $query;
}
```

### 6.4 Asset Requests DataTable

```php
<?php
public function getAssetRequestsQuery(array $filters = [])
{
    $query = DB::table('asset_requests as ar')
        ->select([
            'ar.id', 'ar.request_number', 'ar.item_name', 'ar.category',
            'ar.quantity', 'ar.priority', 'ar.status',
            'ar.estimated_cost', 'ar.actual_cost', 'ar.created_at', 'ar.approved_at',
            'requester.name as requester_name',
            'requester.email as requester_email',
            'div.name as requester_division',
            'approver.name as approver_name',
            'at.type_name as asset_type',
            'asset.asset_tag as fulfilled_asset_tag',
        ])
        ->join('users as requester', 'requester.id', '=', 'ar.requested_by')
        ->leftJoin('users as approver', 'approver.id', '=', 'ar.approved_by')
        ->leftJoin('asset_types as at', 'at.id', '=', 'ar.asset_type_id')
        ->leftJoin('assets as asset', 'asset.id', '=', 'ar.fulfilled_asset_id')
        ->leftJoin('divisions as div', 'div.id', '=', 'requester.division_id');

    if (! empty($filters['status'])) {
        $query->where('ar.status', $filters['status']);
    }
    if (! empty($filters['priority'])) {
        $query->where('ar.priority', $filters['priority']);
    }

    return $query;
}
```

### 6.5 Dashboard Summary (WITH / CTE)

```sql
-- Query ringkasan dashboard menggunakan CTE (MySQL 8.0+)
WITH ticket_summary AS (
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN ts.status = 'Open' THEN 1 ELSE 0 END)        AS open_count,
        SUM(CASE WHEN ts.status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress_count,
        SUM(CASE WHEN t.sla_due < NOW() AND t.resolved_at IS NULL THEN 1 ELSE 0 END) AS sla_breached,
        AVG(CASE WHEN t.resolved_at IS NOT NULL
                 THEN TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) END) AS avg_resolution_hours
    FROM tickets t
    LEFT JOIN tickets_statuses ts ON ts.id = t.ticket_status_id
    WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
),
asset_summary AS (
    SELECT
        COUNT(*)                                                                        AS total,
        SUM(CASE WHEN st.name = 'In Use' THEN 1 ELSE 0 END)                            AS in_use,
        SUM(CASE WHEN st.name = 'Available' THEN 1 ELSE 0 END)                         AS available,
        SUM(CASE WHEN a.warranty_expiration_date BETWEEN NOW()
                      AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END)          AS warranty_expiring
    FROM assets a
    LEFT JOIN statuses st ON st.id = a.status_id
    WHERE a.deleted_at IS NULL
),
booking_summary AS (
    SELECT
        COUNT(*)                                                   AS today_total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END)       AS pending_approvals
    FROM meeting_room_bookings
    WHERE DATE(start_datetime) = CURDATE()
)
SELECT
    ts.total AS tickets_total,
    ts.open_count,
    ts.in_progress_count,
    ts.sla_breached,
    ROUND(ts.avg_resolution_hours, 1) AS avg_resolution_hours,
    ast.total AS assets_total,
    ast.in_use AS assets_in_use,
    ast.available AS assets_available,
    ast.warranty_expiring AS assets_warranty_expiring,
    bs.today_total AS bookings_today,
    bs.pending_approvals AS bookings_pending_approval
FROM ticket_summary ts, asset_summary ast, booking_summary bs;
```

---

## 7. Yajra DataTables Integration

### 7.1 Installation

```bash
composer require yajra/laravel-datatables-oracle:"^10.0"
php artisan vendor:publish --provider="Yajra\DataTables\DataTablesServiceProvider"
```

### 7.2 TicketsDataTable Class

```php
<?php
// app/DataTables/TicketsDataTable.php

namespace App\DataTables;

use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;

class TicketsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('status_badge', function ($row) {
                return '<span class="badge bg-' . ($row->status_color ?? 'secondary') . '">'
                     . ($row->status_label ?? '—') . '</span>';
            })
            ->addColumn('priority_badge', function ($row) {
                return '<span class="badge bg-' . ($row->priority_color ?? 'secondary') . '">'
                     . ($row->priority_label ?? '—') . '</span>';
            })
            ->addColumn('sla_status', function ($row) {
                if ($row->sla_breached) {
                    return '<span class="badge bg-danger">Breached</span>';
                }
                if ($row->sla_due) {
                    $due = \Carbon\Carbon::parse($row->sla_due);
                    $color = now()->diffInHours($due, false) < 4 ? 'warning' : 'success';
                    return '<span class="badge bg-' . $color . '">' . $due->diffForHumans() . '</span>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function ($row) {
                $view = '<a href="' . route('tickets.show', $row->id) . '"
                            class="btn btn-xs btn-info" title="Lihat">
                            <i class="fas fa-eye"></i></a> ';
                $edit = auth()->user()->can('edit-tickets')
                    ? '<a href="' . route('tickets.edit', $row->id) . '"
                          class="btn btn-xs btn-warning" title="Edit">
                          <i class="fas fa-edit"></i></a> '
                    : '';
                return $view . $edit;
            })
            ->rawColumns(['status_badge', 'priority_badge', 'sla_status', 'actions'])
            ->filterColumn('subject', fn($q, $k) => $q->whereRaw('t.subject LIKE ?', ["%{$k}%"]))
            ->filterColumn('reporter_name', fn($q, $k) => $q->whereRaw('reporter.name LIKE ?', ["%{$k}%"]))
            ->orderColumn('status_badge', 'ts.status $1')
            ->orderColumn('priority_badge', 'tp.name $1');
    }

    public function query()
    {
        return DB::table('tickets as t')
            ->select([
                't.id', 't.ticket_code', 't.subject',
                'ts.status as status_label', 'ts.color as status_color',
                'tp.name as priority_label', 'tp.color as priority_color',
                'reporter.name as reporter_name',
                'tech.name as assigned_name',
                't.sla_due',
                DB::raw('CASE WHEN t.sla_due < NOW() AND t.resolved_at IS NULL THEN 1 ELSE 0 END as sla_breached'),
                't.created_at', 't.resolved_at',
            ])
            ->leftJoin('tickets_statuses as ts', 'ts.id', '=', 't.ticket_status_id')
            ->leftJoin('tickets_priorities as tp', 'tp.id', '=', 't.ticket_priority_id')
            ->leftJoin('users as reporter', 'reporter.id', '=', 't.user_id')
            ->leftJoin('users as tech', 'tech.id', '=', 't.assigned_to');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('tickets-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->parameters([
                'pageLength' => 25,
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                'responsive' => true,
                'autoWidth'  => false,
                'language'   => ['url' => '/vendor/datatables/id.json'],
            ]);
    }

    protected function getColumns(): array
    {
        return [
            Column::make('ticket_code')->title('# Kode')->width(120),
            Column::make('subject')->title('Subjek'),
            Column::computed('status_badge')->title('Status')->orderable(true),
            Column::computed('priority_badge')->title('Prioritas')->orderable(true),
            Column::make('reporter_name')->title('Pelapor'),
            Column::make('assigned_name')->title('Teknisi')->defaultContent('—'),
            Column::computed('sla_status')->title('SLA')->orderable(false),
            Column::make('created_at')->title('Dibuat')->width(120),
            Column::computed('actions')->exportable(false)->printable(false)
                  ->width(80)->addClass('text-center')->title('Aksi'),
        ];
    }
}
```

### 7.3 Controller Usage

```php
<?php
// app/Http/Controllers/TicketController.php
use App\DataTables\TicketsDataTable;

class TicketController extends Controller
{
    public function index(TicketsDataTable $dataTable)
    {
        $statuses   = \App\TicketsStatus::all();
        $priorities = \App\TicketsPriority::all();
        return $dataTable->render('tickets.index', compact('statuses', 'priorities'));
    }
}
```

### 7.4 Blade View Template

```blade
{{-- resources/views/tickets/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-ticket-alt mr-1"></i>
            {{ __('Daftar Tiket') }} / Ticket List
        </h3>
        @can('create-tickets')
        <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Buat Tiket Baru
        </a>
        @endcan
    </div>
    <div class="card-body">
        {{-- Filter Panel --}}
        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">Semua Status / All Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}">{{ $s->status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter-priority" class="form-select form-select-sm">
                    <option value="">Semua Prioritas / All Priority</option>
                    @foreach($priorities as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" id="filter-date-from" class="form-control form-control-sm"
                       placeholder="Dari tanggal">
            </div>
            <div class="col-md-2">
                <input type="date" id="filter-date-to" class="form-control form-control-sm"
                       placeholder="Sampai tanggal">
            </div>
            <div class="col-md-2">
                <button id="btn-filter" class="btn btn-secondary btn-sm w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>

        {{ $dataTable->table(['class' => 'table table-bordered table-hover table-sm']) }}
    </div>
</div>
@endsection

@push('scripts')
{{ $dataTable->scripts() }}
<script>
var table = window.LaravelDataTables['tickets-table'];

// Apply filters on button click
$('#btn-filter').on('click', function() {
    table.ajax.url(
        '{{ route("tickets.index") }}' +
        '?status_id='   + $('#filter-status').val() +
        '&priority_id=' + $('#filter-priority').val() +
        '&date_from='   + $('#filter-date-from').val() +
        '&date_to='     + $('#filter-date-to').val()
    ).load();
});

// Delete confirmation
$(document).on('click', '.btn-delete', function() {
    if (confirm('Yakin ingin menghapus tiket ini?')) {
        var id = $(this).data('id');
        $.ajax({
            url: '/tickets/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function() { table.ajax.reload(); }
        });
    }
});
</script>
@endpush
```

---

## 8. Performance Indexes & Optimization

### 8.1 Critical Indexes

```sql
-- Tickets: paling sering di-query (status + SLA + assignment)
ALTER TABLE tickets
    ADD INDEX idx_tickets_status_priority_sla  (ticket_status_id, ticket_priority_id, sla_due),
    ADD INDEX idx_tickets_user_status_created  (user_id, ticket_status_id, created_at),
    ADD INDEX idx_tickets_assigned_status      (assigned_to, ticket_status_id),
    ADD INDEX idx_tickets_sla_breach           (sla_due, resolved_at);

-- Assets: filter umum (status + divisi + tanggal beli)
ALTER TABLE assets
    ADD INDEX idx_assets_lookup_filters        (status_id, division_id, purchase_date),
    ADD INDEX idx_assets_assigned_maintenance  (assigned_to, maintenance_status),
    ADD INDEX idx_assets_warranty              (warranty_expiration_date);

-- Meeting Rooms: cek konflik booking
ALTER TABLE meeting_room_bookings
    ADD INDEX idx_mrb_booking_window  (room_id, start_datetime, end_datetime, status);

-- Asset Requests: workflow approval
ALTER TABLE asset_requests
    ADD INDEX idx_ar_requester_status (requested_by, status, created_at),
    ADD INDEX idx_ar_procurement      (status, approved_at);

-- Audit Logs: GDPR lookup
ALTER TABLE audit_logs
    ADD INDEX idx_audit_model_composite (model_type, model_id),
    ADD INDEX idx_audit_user_action     (user_id, action, created_at);
```

### 8.2 Eloquent Optimization Tips

```php
// ✅ BAIK: Eager loading — cegah N+1
$tickets = Ticket::with(['user:id,name', 'ticket_status:id,status,color',
                         'ticket_priority:id,name,color', 'assignedTo:id,name'])
                 ->latest()->paginate(25);

// ✅ BAIK: Select hanya kolom yang dibutuhkan
$tickets = Ticket::select(['id', 'ticket_code', 'subject', 'ticket_status_id', 'created_at'])
                 ->with(['ticket_status:id,status,color'])
                 ->paginate(25);

// ✅ BAIK: Hitung aggregate di database, bukan di PHP
$stats = Ticket::selectRaw('
    COUNT(*) as total,
    SUM(CASE WHEN ticket_status_id = 1 THEN 1 ELSE 0 END) as open_count,
    SUM(CASE WHEN resolved_at IS NOT NULL THEN 1 ELSE 0 END) as resolved_count
')->first();

// ✅ BAIK: Chunk untuk proses data besar (export, batch update)
Ticket::with(['user:id,name'])->chunk(200, function ($tickets) use (&$rows) {
    foreach ($tickets as $ticket) {
        $rows[] = [$ticket->ticket_code, $ticket->subject, $ticket->user->name ?? ''];
    }
});

// ✅ BAIK: Cache query yang jarang berubah
$statuses = Cache::remember('ticket_statuses', 3600, fn() => TicketsStatus::all());

// ❌ HINDARI: SELECT * tanpa LIMIT pada tabel besar
$all = Ticket::all(); // loads ALL records into memory!

// ❌ HINDARI: Lazy loading dalam loop
foreach (Ticket::all() as $t) {
    echo $t->user->name; // N+1 query!
}
```

---

## 9. GDPR & Audit Logging (ISO 27001)

### 9.1 Auditable Trait

```php
<?php
// app/Traits/Auditable.php
// Tambahkan trait ini ke model: User, Ticket, Asset, AssetRequest, MeetingRoomBooking

namespace App\Traits;

use App\AuditLog;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::writeAuditLog('create', $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            $changes  = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);
            unset($changes['updated_at'], $original['updated_at']);

            if (! empty($changes)) {
                static::writeAuditLog('update', $model, $original, $changes);
            }
        });

        static::deleted(function ($model) {
            static::writeAuditLog('delete', $model, $model->toArray(), null);
        });
    }

    protected static function writeAuditLog(string $action, $model, $old, $new): void
    {
        try {
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => $action,
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'event_type' => 'model',
            ]);
        } catch (\Throwable $e) {
            // Log to file but do NOT block the main operation
            \Log::error('AuditLog write failed: ' . $e->getMessage());
        }
    }
}
```

### 9.2 GDPR Data Export (Hak Akses — Pasal 15 GDPR)

```php
<?php
// Contoh controller action untuk export data user (GDPR Article 15)

public function exportUserData(int $userId): array
{
    $user = User::with([
        'createdTickets:id,ticket_code,subject,ticket_status_id,created_at',
        'meetingBookings:id,room_name,start_datetime,status,created_at',
        'assetRequests:id,request_number,item_name,status,created_at',
        'assignedAssets:id,asset_tag,status_id',
    ])->findOrFail($userId);

    return [
        'exported_at' => now()->toISOString(),
        'user' => $user->only(['id', 'username', 'email', 'name', 'created_at']),
        'tickets' => $user->createdTickets->map(fn($t) => [
            'code'    => $t->ticket_code,
            'subject' => $t->subject,
            'created' => $t->created_at->toDateString(),
        ]),
        'meeting_bookings' => $user->meetingBookings->map(fn($b) => [
            'room'   => $b->room_name,
            'start'  => $b->start_datetime,
            'status' => $b->status,
        ]),
        'purchase_requests' => $user->assetRequests->map(fn($r) => [
            'number' => $r->request_number,
            'item'   => $r->item_name,
            'status' => $r->status,
        ]),
    ];
}
```

### 9.3 Compliance Matrix

| Requirement | Implementation in ITApp |
|---|---|
| **ISO 27001** — Access Control | Spatie `role_has_permissions`; `users.is_active` flag blocks login |
| **ISO 27001** — Audit Trail | `audit_logs` table, immutable (REVOKE DELETE on this table) |
| **ISO 27001** — Change Management | `ticket_history` records every field change with timestamps |
| **GDPR** — Right to Access (Art. 15) | `exportUserData()` returns all PII records |
| **GDPR** — Right to Erasure (Art. 17) | `assets.deleted_at` SoftDelete; anonymize user PII fields |
| **GDPR** — Data Minimization | `portal_preferences` JSON; only store fields that are needed |
| **GDPR** — Consent Tracking | `notify_*` columns allow granular notification opt-out |
| **SOC 2** — Availability | Indexes + mandatory pagination; no unbounded `SELECT *` |
| **SOC 2** — Confidentiality | Passwords auto-hashed; `api_token` unique; all mutations audited |
| **SOC 2** — Processing Integrity | Foreign keys + enums prevent invalid state transitions |

---

> **Last Updated:** 2026-04-16
> **Maintainer:** ITApp Development Team
> **Stack:** Laravel 10 · PHP 8.1+ · MySQL 8.0 · Yajra DataTables 10
