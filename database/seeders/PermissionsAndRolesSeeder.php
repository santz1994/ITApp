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

        // Roles (canonical only)
        $guard = config('auth.defaults.guard', 'web');
        $roleByName = [];
        foreach (\App\Role::canonicalNames() as $roleName) {
            $roleByName[$roleName] = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
        }

        // Assign permissions
        $allPermissions = Permission::all();

        if (isset($roleByName['developer'])) {
            $roleByName['developer']->syncPermissions($allPermissions);
        }

        if (isset($roleByName['administrator'])) {
            $administratorPermNames = [
                'view_all_assets', 'view-assets', 'create-assets', 'edit-assets', 'view-tickets', 'create-tickets', 'edit-tickets', 'assign-tickets',
                'view_ticket_reports', 'view_asset_reports', 'view_admin_performance', 'view_daily_activities', 'create-daily-activities',
                'export-data', 'import-data', 'export-tickets',
                // Asset request management
                'view-asset-requests', 'create-asset-requests', 'approve-asset-requests', 'reject-asset-requests', 'fulfill-asset-requests',
                'view-users', 'create-users', 'edit-users',
            ];

            $roleByName['administrator']->syncPermissions(Permission::whereIn('name', $administratorPermNames)->get());
        }

        if (isset($roleByName['director'])) {
            $directorPermNames = [
                'view_kpi_dashboard', 'view-kpi-dashboard', 'view_admin_performance', 'view_reports', 'view-management-dashboard',
                'view-assets', 'view-tickets', 'create-tickets', 'view-daily-activities',
            ];

            $roleByName['director']->syncPermissions(Permission::whereIn('name', $directorPermNames)->get());
        }

        if (isset($roleByName['receptionist'])) {
            $receptionistPermNames = [
                'view-tickets', 'create-tickets', 'view-daily-activities',
            ];

            $roleByName['receptionist']->syncPermissions(Permission::whereIn('name', $receptionistPermNames)->get());
        }

        if (isset($roleByName['human-resources'])) {
            $hrPermNames = [
                'view-users', 'create-users', 'edit-users',
                'view-tickets', 'create-tickets',
            ];

            $roleByName['human-resources']->syncPermissions(Permission::whereIn('name', $hrPermNames)->get());
        }

        if (isset($roleByName['user'])) {
            $roleByName['user']->syncPermissions([]);
        }

        if (isset($roleByName['guest'])) {
            $roleByName['guest']->syncPermissions([]);
        }

        try {
            Artisan::call('permission:cache-reset');
        } catch (\Throwable $e) {
            // ignore cache reset failures (e.g., during early bootstrap)
        }
    }
}
// stray Artisan call removed (permission:cache-reset is already called inside run())
