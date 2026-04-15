<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        // Create Management role if it doesn't exist
        $managementRole = Role::firstOrCreate(['name' => 'management']);
        
        // Create specific permissions for management
        $managementPermissions = [
            'view_kpi_dashboard',
            'view_all_assets',
            'create_tickets',
            'view_ticket_reports',
            'view_asset_reports',
            'view_admin_performance'
        ];

        foreach ($managementPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to management role
        $managementRole->givePermissionTo($managementPermissions);

        // Update existing permissions for other roles if needed
        $adminPermissions = [
            'view_daily_activities',
            'create_daily_activities', 
            'manage_assigned_tickets',
            'self_assign_tickets'
        ];

        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($adminPermissions);
        }

        $superAdminRole = Role::where('name', 'superadmin')->first();
        if ($superAdminRole) {
            // SuperAdmin gets all permissions
            $superAdminRole->givePermissionTo(Permission::all());
        }
    }

    public function down()
    {
        // Delete management permissions
        Permission::whereIn('name', [
            'view_kpi_dashboard',
            'view_all_assets', 
            'create_tickets',
            'view_ticket_reports',
            'view_asset_reports',
            'view_admin_performance'
        ])->delete();

        // Delete management role
        Role::where('name', 'management')->delete();
    }
};