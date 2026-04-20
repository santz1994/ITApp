<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarWorkspaceContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_support_workspace_shows_focused_sidebar_groups(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/tickets?workspace=it_support');

        $response->assertStatus(200);
        $response->assertSee('Workspace');
        $response->assertSee('IT Support Module');
        $response->assertSee('data-i18n="sidebar.workspace.it_support"', false);
        $response->assertSee('Tickets');
        $response->assertSee('href="' . route('tickets.index') . '"', false);
        $response->assertDontSee('href="' . route('meeting-room-bookings.index') . '"', false);
        $response->assertDontSee('href="' . route('asset-requests.index') . '"', false);
    }

    public function test_meeting_room_workspace_shows_focused_sidebar_groups(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/meeting-room-bookings?workspace=meeting_room');

        $response->assertStatus(200);
        $response->assertSee('Workspace');
        $response->assertSee('Meeting Room');
        $response->assertSee('data-i18n="sidebar.workspace.meeting_room"', false);
        $response->assertSee('Meeting Room Booking');
        $response->assertSee('href="' . route('meeting-room-bookings.index') . '"', false);
        $response->assertDontSee('href="' . route('tickets.index') . '"', false);
        $response->assertDontSee('href="' . route('asset-requests.index') . '"', false);
    }

    public function test_sidebar_workspace_header_uses_portal_preference_language(): void
    {
        $user = User::factory()->create([
            'portal_preferences' => [
                'language' => 'id',
            ],
        ]);
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/tickets?workspace=it_support');

        $response->assertStatus(200);
        $response->assertSee('Navigasi');
        $response->assertSee('Ruang Kerja');
        $response->assertSee('Modul Dukungan TI');
        $response->assertSee('Portal Utama');
        $response->assertSee('data-i18n="sidebar.navigation"', false);
        $response->assertSee('data-i18n="sidebar.workspace"', false);
        $response->assertSee('data-i18n="sidebar.main_portal"', false);
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
