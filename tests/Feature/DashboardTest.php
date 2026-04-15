<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }

    $response = $this->actingAs($user)->followingRedirects()->get('/dashboard');
    $response->assertStatus(200);
    // Allow either the new integrated dashboard or the legacy home view
    $viewName = null;
    if (isset($response->original) && method_exists($response->original, 'getName')) {
        $viewName = $response->original->getName();
    }
    $this->assertTrue(in_array($viewName, ['dashboard.integrated-dashboard', 'home']), "Unexpected view: $viewName");
    }
}
