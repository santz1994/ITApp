<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MainPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_home_portal(): void
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_main_portal_with_module_navigation_only(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Main Portal');
        $response->assertSee('Module Navigation');
        $response->assertSee('IT Support Module');
        $response->assertSee('Profile');
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="portal-utility-bar"', false);
        $response->assertSee('id="portal-logout-action"', false);
        $response->assertDontSee('Quick Access');
        $response->assertDontSee('Portal Personalization');
        $response->assertDontSee('Approval Center');
        $response->assertDontSee('data-role-badge-key=', false);
    }

    public function test_admin_user_still_sees_module_navigation_without_dashboard_widgets(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'admin');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Module Navigation');
        $response->assertSee('User Management');
        $response->assertSee('Settings');
        $response->assertSee('id="portal-utility-bar"', false);
        $response->assertDontSee('Quick Access');
        $response->assertDontSee('Approval Center');
        $response->assertDontSee('data-role-badge-key=', false);
    }

    public function test_main_portal_uses_hub_layout_without_global_sidebar(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('portal-hub-wrapper', false);
        $response->assertSee('portal-hub-layout', false);
        $response->assertDontSee('<aside class="main-sidebar">', false);
        $response->assertDontSee('<header class="main-header">', false);
        $response->assertDontSee('<section class="content-header">', false);
        $response->assertDontSee('control-sidebar', false);
    }

    public function test_main_portal_module_links_include_workspace_spoke_context(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('workspace=it_support', false);
        $response->assertSee('workspace=meeting_room', false);
        $response->assertSee('workspace=assets_management', false);
        $response->assertSee('workspace=purchase_request', false);
        $response->assertSee('workspace=profile', false);
    }

    public function test_main_portal_includes_dynamic_viewport_hooks_for_responsive_layout(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('setupDynamicViewport();', false);
        $response->assertSee('data-portal-screen', false);
        $response->assertSee('ResizeObserver', false);
        $response->assertSee('--portal-grid-min-card', false);
    }

    private function assignRoleSafely(User $user, string $roleName): void
    {
        try {
            if (!class_exists(Role::class) || !method_exists($user, 'assignRole')) {
                return;
            }

            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($roleName);
        } catch (\Throwable $exception) {
            // Keep tests resilient if role tables are unavailable.
        }
    }
}
