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

        try {
            if (class_exists(Role::class) && method_exists($user, 'assignRole')) {
                Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
                $user->assignRole('user');
            }
        } catch (\Throwable $exception) {
            // Ignore role assignment issues and continue with auth-only assertion.
        }

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Main Portal Dashboard');
        $response->assertSee('IT Support Module');
        $response->assertSee('Profile');
        $response->assertSee('Quick Access');
        $response->assertSee('Portal Personalization');
        $response->assertSee('EN');
        $response->assertSee('ID');
    }
}
