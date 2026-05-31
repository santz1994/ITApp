<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ComprehensivePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates all permissions required by the application
     * and assigns them to appropriate roles.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = config('auth.defaults.guard', 'web');

        // Define all permissions with their display names and descriptions
        $permissions = [
            // ===== Meeting Room Booking Permissions =====
            ['name' => 'create_booking', 'display_name' => 'Create Booking', 'description' => 'Create meeting room bookings'],
            ['name' => 'view_bookings', 'display_name' => 'View Bookings', 'description' => 'View meeting room bookings'],
            ['name' => 'approve_booking', 'display_name' => 'Approve Booking', 'description' => 'Approve meeting room bookings'],
            ['name' => 'reject_booking', 'display_name' => 'Reject Booking', 'description' => 'Reject meeting room bookings'],
            ['name' => 'cancel_booking', 'display_name' => 'Cancel Booking', 'description' => 'Cancel meeting room bookings'],
            ['name' => 'finish_booking', 'display_name' => 'Finish Booking', 'description' => 'Finish/complete meeting room bookings'],
            ['name' => 'extend_booking', 'display_name' => 'Extend Booking', 'description' => 'Extend meeting room booking time'],
            ['name' => 'quick_edit_booking', 'display_name' => 'Quick Edit Booking', 'description' => 'Quick edit booking subject/time'],
            ['name' => 'view_meeting_room_lcd', 'display_name' => 'View LCD Dashboard', 'description' => 'View LCD dashboard (public)'],
            ['name' => 'manage_meeting_room_lcd_settings', 'display_name' => 'Manage LCD Settings', 'description' => 'Configure LCD display settings'],
            ['name' => 'print_booking', 'display_name' => 'Print Booking', 'description' => 'Print booking details'],
            ['name' => 'export_booking_report', 'display_name' => 'Export Booking Report', 'description' => 'Export booking reports'],
            ['name' => 'view_director_dashboard', 'display_name' => 'Director Dashboard', 'description' => 'View director dashboard for meeting rooms'],
            ['name' => 'view_receptionist_dashboard', 'display_name' => 'Receptionist Dashboard', 'description' => 'View receptionist dashboard'],
            ['name' => 'manage_room_availability', 'display_name' => 'Manage Room Availability', 'description' => 'Toggle room availability'],
            ['name' => 'quick_booking', 'display_name' => 'Quick Booking', 'description' => 'Create quick bookings from dashboard'],
            ['name' => 'update_booking_time', 'display_name' => 'Update Booking Time', 'description' => 'Drag & drop booking time'],
            ['name' => 'view_booking_calendar', 'display_name' => 'View Booking Calendar', 'description' => 'View booking calendar'],

            // ===== Vehicle Permissions =====
            ['name' => 'manage_vehicles', 'display_name' => 'Manage Vehicles', 'description' => 'CRUD operations on vehicles (admin)'],
            ['name' => 'view_vehicles', 'display_name' => 'View Vehicles', 'description' => 'View vehicle list'],
            ['name' => 'create_vehicle_booking', 'display_name' => 'Create Vehicle Booking', 'description' => 'Create vehicle bookings'],
            ['name' => 'approve_vehicle_booking', 'display_name' => 'Approve Vehicle Booking', 'description' => 'Approve/reject vehicle bookings'],
            ['name' => 'manage_vehicle_maintenance', 'display_name' => 'Manage Vehicle Maintenance', 'description' => 'Add maintenance logs'],
            ['name' => 'view_vehicle_reports', 'display_name' => 'View Vehicle Reports', 'description' => 'View vehicle usage reports'],

            // ===== Inventory Permissions =====
            ['name' => 'view_inventory', 'display_name' => 'View Inventory', 'description' => 'View inventory items'],
            ['name' => 'manage_inventory', 'display_name' => 'Manage Inventory', 'description' => 'CRUD operations on inventory items'],
            ['name' => 'manage_stock', 'display_name' => 'Manage Stock', 'description' => 'Add/reduce stock'],
            ['name' => 'create_inventory_request', 'display_name' => 'Create Inventory Request', 'description' => 'Create inventory requests'],
            ['name' => 'approve_inventory_request', 'display_name' => 'Approve Inventory Request', 'description' => 'Approve/reject inventory requests'],
            ['name' => 'fulfill_inventory_request', 'display_name' => 'Fulfill Inventory Request', 'description' => 'Issue/fulfill inventory requests'],
            ['name' => 'view_inventory_reports', 'display_name' => 'View Inventory Reports', 'description' => 'View inventory reports'],

            // ===== Approval Workflow Permissions =====
            ['name' => 'view_pending_approvals', 'display_name' => 'View Pending Approvals', 'description' => 'View approvals pending for current user'],
            ['name' => 'approve_requests', 'display_name' => 'Approve Requests', 'description' => 'Approve/reject requests'],
            ['name' => 'manage_approval_rules', 'display_name' => 'Manage Approval Rules', 'description' => 'CRUD approval rules (admin)'],

            // ===== Admin & System Permissions =====
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'description' => 'Full user CRUD'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'description' => 'Role management'],
            ['name' => 'view_admin_panel', 'display_name' => 'View Admin Panel', 'description' => 'Access admin configuration panel'],
            ['name' => 'manage_system_settings', 'display_name' => 'Manage System Settings', 'description' => 'System settings management'],
            ['name' => 'manage_menus', 'display_name' => 'Manage Menus', 'description' => 'Menu management'],
            ['name' => 'manage_audit_logs', 'display_name' => 'Manage Audit Logs', 'description' => 'View and manage audit logs'],
            ['name' => 'manage_notification_settings', 'display_name' => 'Manage Notification Settings', 'description' => 'Configure notification settings'],
            ['name' => 'update_activity', 'display_name' => 'Update Activity', 'description' => 'Update activity status'],

            // ===== Report Permissions =====
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'description' => 'View reports section'],
            ['name' => 'export_data', 'display_name' => 'Export Data', 'description' => 'Export data to Excel/CSV'],
        ];

        // Create all permissions
        echo "Creating permissions...\n";
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $guardName],
                [
                    'display_name' => $permission['display_name'],
                    'description' => $permission['description']
                ]
            );
            echo "  ✓ {$permission['name']}\n";
        }

        // Get canonical roles
        $developerRole = Role::where('name', 'developer')->first();
        $administratorRole = Role::where('name', 'administrator')->first();
        $directorRole = Role::where('name', 'director')->first();
        $humanResourcesRole = Role::where('name', 'human-resources')->first();
        $receptionistRole = Role::where('name', 'receptionist')->first();
        $userRole = Role::where('name', 'user')->first();
        $guestRole = Role::where('name', 'guest')->first();

        if (!$developerRole || !$administratorRole || !$directorRole || !$userRole) {
            echo "\n⚠️  ERROR: Canonical roles not found! Run RolesTableSeeder first.\n";
            return;
        }

        // Assign permissions to roles
        echo "\nAssigning permissions to roles...\n";

        // Developer - ALL PERMISSIONS (LV 10)
        $developerRole->syncPermissions(Permission::all());
        echo "  ✓ Developer: " . Permission::count() . " permissions\n";

        // Administrator - operational full access except developer-only areas
        $administratorPermissions = [
            // Meetings
            'create_booking', 'view_bookings', 'approve_booking', 'reject_booking', 'cancel_booking',
            'finish_booking', 'extend_booking', 'quick_edit_booking', 'view_booking_calendar',
            'print_booking', 'export_booking_report', 'view_receptionist_dashboard',
            'manage_room_availability', 'quick_booking', 'update_booking_time',
            // Vehicles
            'manage_vehicles', 'view_vehicles', 'create_vehicle_booking', 'approve_vehicle_booking',
            'manage_vehicle_maintenance', 'view_vehicle_reports',
            // Inventory
            'view_inventory', 'manage_inventory', 'manage_stock',
            'create_inventory_request', 'approve_inventory_request', 'fulfill_inventory_request', 'view_inventory_reports',
            // Approvals
            'view_pending_approvals', 'approve_requests',
            // Admin
            'manage_users', 'manage_roles', 'view_admin_panel', 'manage_menus',
            'manage_audit_logs', 'manage_notification_settings',
            // Reports
            'view_reports', 'export_data', 'update_activity',
        ];
        $administratorRole->syncPermissions($administratorPermissions);
        echo "  ✓ Administrator: " . count($administratorPermissions) . " permissions\n";

        // Director - Approvals, view and report permissions (LV 8)
        $directorPermissions = [
            // Meetings
            'create_booking', 'view_bookings', 'approve_booking', 'reject_booking',
            'view_director_dashboard', 'view_booking_calendar',
            // Vehicles
            'view_vehicles', 'view_vehicle_reports',
            // Inventory
            'view_inventory', 'view_inventory_reports',
            // Approvals
            'view_pending_approvals', 'approve_requests',
            // Reports
            'view_reports', 'update_activity',
        ];
        $directorRole->syncPermissions($directorPermissions);
        echo "  ✓ Director: " . count($directorPermissions) . " permissions\n";

        // Human Resources - user management (LV 3)
        if ($humanResourcesRole) {
            $humanResourcesPermissions = [
                'manage_users',
            ];
            $humanResourcesRole->syncPermissions($humanResourcesPermissions);
            echo "  ✓ Human Resources: " . count($humanResourcesPermissions) . " permissions\n";
        }

        // User - Limited permissions (LV 1)
        $userPermissions = [
            'create_booking', 'view_bookings',
            'view_vehicles', 'create_vehicle_booking',
            'view_inventory', 'create_inventory_request',
            'view_pending_approvals',
            'update_activity',
        ];
        $userRole->syncPermissions($userPermissions);
        echo "  ✓ User: " . count($userPermissions) . " permissions\n";

        // Guest - no authenticated permissions
        if ($guestRole) {
            $guestRole->syncPermissions([]);
            echo "  ✓ Guest: 0 permissions\n";
        }
        
        // Receptionist - Meeting room management (LV 2)
        if ($receptionistRole) {
            $receptionistPermissions = [
                'view_bookings', 'create_booking', 'cancel_booking',
                'view_receptionist_dashboard', 'manage_room_availability',
                'quick_booking', 'update_booking_time', 'view_booking_calendar',
            ];
            $receptionistRole->syncPermissions($receptionistPermissions);
            echo "  ✓ Receptionist: " . count($receptionistPermissions) . " permissions\n";
        }

        echo "\n✅ All permissions created and assigned successfully!\n";
        echo "Total Roles: 7 (guest, user, receptionist, human-resources, director, administrator, developer)\n";
        echo "Total Permissions: " . Permission::count() . "\n";
    }
}
