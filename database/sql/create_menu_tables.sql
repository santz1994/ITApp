-- Menu System Tables Creation
-- Run this SQL directly in phpMyAdmin or MySQL client

USE itquty;

-- Create menus table
CREATE TABLE IF NOT EXISTS `menus` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `route` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_external` tinyint(1) NOT NULL DEFAULT 0,
  `target` varchar(50) NOT NULL DEFAULT '_self',
  `css_class` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_parent_id_index` (`parent_id`),
  KEY `menus_is_active_order_index_index` (`is_active`, `order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create menu_role pivot table
CREATE TABLE IF NOT EXISTS `menu_role` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_role_menu_id_role_id_unique` (`menu_id`, `role_id`),
  KEY `menu_role_menu_id_index` (`menu_id`),
  KEY `menu_role_role_id_index` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create menu_user pivot table
CREATE TABLE IF NOT EXISTS `menu_user` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_user_menu_id_user_id_unique` (`menu_id`, `user_id`),
  KEY `menu_user_menu_id_index` (`menu_id`),
  KEY `menu_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add to migrations table to mark as migrated
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2025_11_11_000001_create_menu_system_tables', 99)
ON DUPLICATE KEY UPDATE `migration` = `migration`;

SELECT 'Menu tables created successfully!' as status;
