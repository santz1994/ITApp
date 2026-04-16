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

    public function test_authenticated_user_can_open_main_portal(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Main Portal Dashboard');
        $response->assertSee('IT Support Module');
        $response->assertSee('Profile');
        $response->assertSee('Quick Access');
        $response->assertSee('Portal Personalization');
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('data-role-badge-key="user"', false);
        $response->assertSee('data-role-badge-level="1"', false);
        $response->assertSee('data-role-badge-label-en="User / The Operator"', false);
        $response->assertSee('data-role-badge-label-id="Pengguna / The Operator"', false);
        $response->assertSee('data-role-set-badge-key="user"', false);
    }

    public function test_admin_user_sees_lv9_role_badge_visual_markers(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'admin');

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('data-role-badge-key="admin"', false);
        $response->assertSee('data-role-badge-level="9"', false);
        $response->assertSee('data-role-badge-effect="warning-glow"', false);
        $response->assertSee('role-badge-lv9', false);
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
