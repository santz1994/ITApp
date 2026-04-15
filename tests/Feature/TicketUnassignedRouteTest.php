<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Ticket;
use App\TicketsStatus;
use App\TicketsPriority;
use App\TicketsType;
use App\Location;
use Spatie\Permission\Models\Role;

/**
 * Test Suite for Unassigned Tickets Route
 * 
 * Verifies that the /tickets/unassigned route is accessible
 * and returns the correct view with proper data
 */
class TicketUnassignedRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles (use firstOrCreate to avoid duplicates)
        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);
        
        // Create test data
        $this->createTestData();
    }

    protected function createTestData()
    {
        // Create location
        Location::create([
            'location_name' => 'Test Location',
            'building' => 'Test Building',
            'office' => 'Test Office',
            'storeroom' => false
        ]);

        // Create ticket statuses
        TicketsStatus::create(['status' => 'Open']);
        TicketsStatus::create(['status' => 'In Progress']);
        TicketsStatus::create(['status' => 'Closed']);

        // Create ticket priority
        TicketsPriority::create(['priority' => 'High']);

        // Create ticket type
        TicketsType::create(['type' => 'Technical Support']);
    }

    /** @test */
    public function guest_cannot_access_unassigned_tickets_page()
    {
        $response = $this->get('/tickets/unassigned');

        // Should redirect to login
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function regular_user_cannot_access_unassigned_tickets_page()
    {
        $user = User::factory()->create(['is_active' => 1]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->get('/tickets/unassigned');

        // Should get 403 Forbidden
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_unassigned_tickets_page()
    {
        $admin = User::factory()->create(['is_active' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/tickets/unassigned');

        $response->assertStatus(200);
        $response->assertViewIs('tickets.unassigned');
        $response->assertViewHas('tickets');
    }

    /** @test */
    public function super_admin_can_access_unassigned_tickets_page()
    {
        $superAdmin = User::factory()->create(['is_active' => 1]);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)
            ->get('/tickets/unassigned');

        $response->assertStatus(200);
        $response->assertViewIs('tickets.unassigned');
        $response->assertViewHas('tickets');
    }

    /** @test */
    public function unassigned_tickets_page_shows_only_unassigned_tickets()
    {
        $admin = User::factory()->create(['is_active' => 1]);
        $admin->assignRole('admin');

        $user = User::factory()->create(['is_active' => 1]);
        $technician = User::factory()->create(['is_active' => 1]);

        // Create unassigned ticket
        $unassignedTicket = Ticket::create([
            'subject' => 'Unassigned Ticket',
            'description' => 'This ticket needs assignment',
            'user_id' => $user->id,
            'location_id' => 1,
            'ticket_status_id' => 1,
            'ticket_priority_id' => 1,
            'ticket_type_id' => 1,
            'assigned_to' => null, // No assignment
            'ticket_code' => 'TIK-' . now()->format('Ymd') . '-001'
        ]);

        // Create assigned ticket
        $assignedTicket = Ticket::create([
            'subject' => 'Assigned Ticket',
            'description' => 'This ticket is assigned',
            'user_id' => $user->id,
            'location_id' => 1,
            'ticket_status_id' => 1,
            'ticket_priority_id' => 1,
            'ticket_type_id' => 1,
            'assigned_to' => $technician->id, // Has assignment
            'ticket_code' => 'TIK-' . now()->format('Ymd') . '-002'
        ]);

        $response = $this->actingAs($admin)
            ->get('/tickets/unassigned');

        $response->assertStatus(200);
        $response->assertSee('Unassigned Ticket');
        $response->assertDontSee('Assigned Ticket');
    }

    /** @test */
    public function route_can_be_accessed_via_named_route()
    {
        $admin = User::factory()->create(['is_active' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get(route('tickets.unassigned'));

        $response->assertStatus(200);
        $response->assertViewIs('tickets.unassigned');
    }

    /** @test */
    public function unassigned_tickets_page_displays_correct_elements()
    {
        $admin = User::factory()->create(['is_active' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/tickets/unassigned');

        $response->assertStatus(200);
        
        // Check for key page elements
        $response->assertSee('Tiket Belum Ditangani');
        $response->assertSee('Kode Tiket');
        $response->assertSee('Prioritas');
        $response->assertSee('Aksi');
    }

    /** @test */
    public function unassigned_tickets_page_shows_empty_state_when_no_tickets()
    {
        $admin = User::factory()->create(['is_active' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/tickets/unassigned');

        $response->assertStatus(200);
        $response->assertSee('Tidak ada tiket yang belum ditangani');
    }
}
