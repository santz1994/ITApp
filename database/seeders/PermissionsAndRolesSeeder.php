<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsAndRolesSeeder extends Seeder
{
    public function run()
    {
        // Define a conservative set of permissions used across tests
        $permissions = [
            'view_kpi_dashboard', 'view-kpi-dashboard', 'view-reports',

            // Assets
            'view_all_assets', 'view-assets', 'create-assets', 'create-asset', 'edit-assets', 'edit-asset', 'delete-assets', 'delete-asset', 'export-assets', 'import-assets', 'import-data', 'export-data',

            // Asset Requests (granular)
            'view-asset-requests', 'view_asset_requests', 'create-asset-requests', 'create_asset_requests', 'approve-asset-requests', 'approve_asset_requests', 'reject-asset-requests', 'reject_asset_requests', 'fulfill-asset-requests', 'fulfill_asset_requests',

            // Tickets
            'create_tickets', 'create-tickets', 'view-tickets', 'view_ticket_reports', 'view-ticket-reports', 'edit-tickets', 'delete-tickets', 'assign-tickets', 'export-tickets',

            // Daily activities
            'view_daily_activities', 'view-daily-activities', 'create-daily-activities', 'edit-daily-activities', 'delete-daily-activities',

            // Models & configuration
            'view-models', 'create-models', 'edit-models', 'delete-models',

            // Suppliers, locations, divisions
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-locations', 'create-locations', 'edit-locations', 'delete-locations',
            'view-divisions', 'create-divisions', 'edit-divisions', 'delete-divisions',

            // Invoice & budget
            'view-invoices', 'create-invoices', 'edit-invoices', 'delete-invoices',

            // User management
            'view-users', 'create-users', 'edit-users', 'delete-users', 'change-role',

            // Misc / backward-compat
            'view-management-dashboard', 'view_admin_performance', 'view_asset_reports',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Roles
    $super = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    // Some code checks for 'super_admin' (underscore) — create alias role to reduce 403s in tests
    $superUnderscore = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $management = Role::firstOrCreate(['name' => 'management', 'guard_name' => 'web']);
    $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Assign permissions
        $allPermissions = Permission::all();
        $super->syncPermissions($allPermissions);
        $superUnderscore->syncPermissions($allPermissions);

        $adminPermNames = [
            'view_all_assets', 'view-assets', 'create-assets', 'edit-assets', 'view-tickets', 'create-tickets', 'edit-tickets', 'assign-tickets',
            'view_ticket_reports', 'view_asset_reports', 'view_admin_performance', 'view_daily_activities', 'create-daily-activities',
            'export-data', 'import-data', 'export-tickets',
            // Asset request management
            'view-asset-requests', 'create-asset-requests', 'approve-asset-requests', 'reject-asset-requests', 'fulfill-asset-requests',
            'view-users', 'create-users', 'edit-users',
        ];
        $admin->syncPermissions(Permission::whereIn('name', $adminPermNames)->get());

        $managementPermNames = [
            'view_kpi_dashboard', 'view-kpi-dashboard', 'view_admin_performance', 'view_reports', 'view-management-dashboard',
            'view-assets', 'view-tickets', 'create-tickets', 'view-daily-activities'
        ];
        $management->syncPermissions(Permission::whereIn('name', $managementPermNames)->get());

        $user->syncPermissions([]);

        try {
            Artisan::call('permission:cache-reset');
        } catch (\Throwable $e) {
            // ignore cache reset failures (e.g., during early bootstrap)
        }
    }
}
// stray Artisan call removed (permission:cache-reset is already called inside run())
