<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

class ManagementDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_management_dashboard()
    {
    $admin = User::factory()->create();
    // Create role and permission and assign role to user (spatie permission middleware checks role/permission)
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        Permission::firstOrCreate(['name' => 'view_kpi_dashboard']);
        $role->givePermissionTo('view_kpi_dashboard');
        // Also create a super-admin role and assign it to ensure role checks pass
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);
        $admin->assignRole('admin');
        $admin->assignRole('super-admin');
        // give user the permission directly as well, and clear permission cache
        $admin->givePermissionTo('view_kpi_dashboard');
        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Exception $e) {
            // ignore if class not available
        }

        $this->actingAs($admin);

        $response = $this->get('/management/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_admin_performance_ajax_returns_json()
    {
    $admin = User::factory()->create();
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        Permission::firstOrCreate(['name' => 'view_kpi_dashboard']);
        $role->givePermissionTo('view_kpi_dashboard');
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);
        $admin->assignRole('admin');
        $admin->assignRole('super-admin');
        $admin->givePermissionTo('view_kpi_dashboard');
        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Exception $e) {
            // ignore if class not available
        }

        $this->actingAs($admin);

    $response = $this->get('/management/admin-performance?period=month', ['X-Requested-With' => 'XMLHttpRequest']);
    $response->assertStatus(200);
    $response->assertJsonStructure(['adminPerformance', 'period']);
    }
}
