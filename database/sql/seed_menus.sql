-- Menu System Data Seeding
-- Run this SQL directly in phpMyAdmin
USE itquty;
-- Clear existing data
TRUNCATE TABLE `menu_user`;
TRUNCATE TABLE `menu_role`;
TRUNCATE TABLE `menus`;
-- Insert Dashboard Menu
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        'Dashboard',
        'home',
        'fa fa-dashboard',
        1,
        1,
        NOW(),
        NOW()
    );
-- Insert Dashboard menu permissions for all roles
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT 1,
    id,
    1,
    NOW(),
    NOW()
FROM roles
WHERE name IN (
        'developer',
        'administrator',
        'director',
        'user',
        'receptionist',
        'human-resources'
    );
-- Insert Assets Parent Menu
INSERT INTO `menus` (
        `id`,
        `label`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (2, 'Assets', 'fa fa-desktop', 2, 1, NOW(), NOW());
-- Insert Assets submenu items
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `parent_id`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        3,
        'All Assets',
        'assets.index',
        'fa fa-list',
        2,
        1,
        1,
        NOW(),
        NOW()
    ),
    (
        4,
        'Add New Asset',
        'assets.create',
        'fa fa-plus',
        2,
        2,
        1,
        NOW(),
        NOW()
    ),
    (
        5,
        'Asset Models',
        'assetmodels.index',
        'fa fa-cubes',
        2,
        3,
        1,
        NOW(),
        NOW()
    ),
    (
        6,
        'Asset Maintenance',
        'asset-maintenance.index',
        'fa fa-wrench',
        2,
        4,
        1,
        NOW(),
        NOW()
    );
-- Insert Assets menu permissions (developer and administrator only)
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT m.id,
    r.id,
    1,
    NOW(),
    NOW()
FROM menus m
    CROSS JOIN roles r
WHERE m.id IN (2, 3, 4, 5, 6)
    AND r.name IN ('developer', 'administrator');
-- Insert Tickets Parent Menu
INSERT INTO `menus` (
        `id`,
        `label`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (7, 'Tickets', 'fa fa-ticket', 3, 1, NOW(), NOW());
-- Insert Tickets submenu items
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `parent_id`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        8,
        'All Tickets',
        'tickets.index',
        'fa fa-list',
        7,
        1,
        1,
        NOW(),
        NOW()
    ),
    (
        9,
        'Create Ticket',
        'tickets.create',
        'fa fa-plus',
        7,
        2,
        1,
        NOW(),
        NOW()
    ),
    (
        10,
        'My Tickets',
        'user.tickets.index',
        'fa fa-user',
        7,
        3,
        1,
        NOW(),
        NOW()
    );
-- Insert Tickets menu permissions
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT m.id,
    r.id,
    1,
    NOW(),
    NOW()
FROM menus m
    CROSS JOIN roles r
WHERE m.id IN (7, 8, 9)
    AND r.name IN (
        'developer',
        'administrator',
        'user',
        'director',
        'human-resources'
    );
-- My Tickets only for users
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT 10,
    id,
    1,
    NOW(),
    NOW()
FROM roles
WHERE name = 'user';
-- Insert Meeting Rooms Parent Menu
INSERT INTO `menus` (
        `id`,
        `label`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        11,
        'Meeting Rooms',
        'fa fa-calendar',
        4,
        1,
        NOW(),
        NOW()
    );
-- Insert Meeting Rooms submenu items
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `parent_id`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        12,
        'All Bookings',
        'meeting-room-bookings.index',
        'fa fa-list',
        11,
        1,
        1,
        NOW(),
        NOW()
    ),
    (
        13,
        'New Booking',
        'meeting-room-bookings.create',
        'fa fa-plus',
        11,
        2,
        1,
        NOW(),
        NOW()
    ),
    (
        14,
        'Calendar View',
        'meeting-room-bookings-calendar',
        'fa fa-calendar-o',
        11,
        3,
        1,
        NOW(),
        NOW()
    );
-- Insert Meeting Rooms menu permissions
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT m.id,
    r.id,
    1,
    NOW(),
    NOW()
FROM menus m
    CROSS JOIN roles r
WHERE m.id IN (11, 12, 14)
    AND r.name IN (
        'developer',
        'administrator',
        'director',
        'user',
        'receptionist',
        'human-resources'
    );
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT 13,
    id,
    1,
    NOW(),
    NOW()
FROM roles
WHERE name IN ('developer', 'administrator', 'user');
-- Insert Inventory Menu
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        15,
        'Inventory',
        'spares.index',
        'fa fa-archive',
        5,
        1,
        NOW(),
        NOW()
    );
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT 15,
    id,
    1,
    NOW(),
    NOW()
FROM roles
WHERE name IN ('developer', 'administrator');
-- Insert Reports Parent Menu
INSERT INTO `menus` (
        `id`,
        `label`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        16,
        'Reports',
        'fa fa-bar-chart',
        6,
        1,
        NOW(),
        NOW()
    );
-- Insert Reports submenu items
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `parent_id`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        17,
        'Asset Report',
        'reports.assets',
        'fa fa-file-pdf-o',
        16,
        1,
        1,
        NOW(),
        NOW()
    ),
    (
        18,
        'Ticket Report',
        'reports.tickets',
        'fa fa-line-chart',
        16,
        2,
        1,
        NOW(),
        NOW()
    );
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT m.id,
    r.id,
    1,
    NOW(),
    NOW()
FROM menus m
    CROSS JOIN roles r
WHERE m.id IN (16, 17, 18)
    AND r.name IN ('developer', 'administrator', 'director');
-- Insert Admin Parent Menu (Developer only)
INSERT INTO `menus` (
        `id`,
        `label`,
        `icon`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        19,
        'Administration',
        'fa fa-cogs',
        7,
        1,
        NOW(),
        NOW()
    );
-- Insert Admin submenu items
INSERT INTO `menus` (
        `id`,
        `label`,
        `route`,
        `icon`,
        `parent_id`,
        `order_index`,
        `is_active`,
        `created_at`,
        `updated_at`
    )
VALUES (
        20,
        'Users',
        'admin.users.index',
        'fa fa-users',
        19,
        1,
        1,
        NOW(),
        NOW()
    ),
    (
        21,
        'Roles & Permissions',
        'admin.roles.index',
        'fa fa-shield',
        19,
        2,
        1,
        NOW(),
        NOW()
    ),
    (
        22,
        'Menu Management',
        'admin.menus.index',
        'fa fa-bars',
        19,
        3,
        1,
        NOW(),
        NOW()
    ),
    (
        23,
        'System Settings',
        'admin.settings.index',
        'fa fa-gear',
        19,
        4,
        1,
        NOW(),
        NOW()
    );
INSERT INTO `menu_role` (
        `menu_id`,
        `role_id`,
        `can_view`,
        `created_at`,
        `updated_at`
    )
SELECT m.id,
    r.id,
    1,
    NOW(),
    NOW()
FROM menus m
    CROSS JOIN roles r
WHERE m.id IN (19, 20, 21, 22, 23)
    AND r.name = 'developer';
-- Show success message
SELECT CONCAT('Successfully seeded ', COUNT(*), ' menu items') AS result
FROM menus;
SELECT CONCAT(
        'Successfully created ',
        COUNT(*),
        ' menu-role permissions'
    ) AS result
FROM menu_role;