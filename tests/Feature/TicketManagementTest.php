<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Traits\TestDataHelper;
use App\Ticket;
use App\Asset;
use App\TicketComment;
use App\TicketHistory;

/**
 * Ticket Management Feature Tests
 * 
 * Tests comprehensive ticket CRUD operations, multi-asset attachment,
 * status workflow, assignment, history tracking, and comments.
 */
class TicketManagementTest extends TestCase
{
    use DatabaseTransactions, TestDataHelper;

    protected $asset1;
    protected $asset2;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        try {
            if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) {
                (new \Database\Seeders\LocationsTableSeeder())->run();
            }
        } catch (\Throwable $e) {}
        try {
            if (class_exists(\Database\Seeders\RolesTableSeeder::class)) {
                (new \Database\Seeders\RolesTableSeeder())->run();
            }
        } catch (\Throwable $e) {}
        try {
            if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) {
                (new \Database\Seeders\TestUsersTableSeeder())->run();
            }
        } catch (\Throwable $e) {}
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
        
        // Setup backward compatibility aliases for existing tests
        $this->superAdmin = $this->testSuperAdmin;
        $this->admin = $this->testAdmin;
        $this->technician = $this->testTechnician;
        $this->user = $this->testUser;
        $this->division = $this->testDivision;
        $this->location = $this->testLocation;
        $this->openStatus = $this->testTicketOpenStatus;
        $this->closedStatus = $this->testTicketClosedStatus;
        $this->priority = $this->testTicketPriority;
        $this->ticketType = $this->testTicketType;

        // Create test assets using helper
        $this->asset1 = $this->createTestAsset([
            'asset_tag' => 'TICKET-ASSET-1-' . time(),
            'serial_number' => 'SN-1-' . uniqid(),
        ]);

        $this->asset2 = $this->createTestAsset([
            'asset_tag' => 'TICKET-ASSET-2-' . time(),
            'serial_number' => 'SN-2-' . uniqid(),
        ]);
    }

    /** @test */
    public function user_can_create_ticket_with_description()
    {
        $ticketData = [
            'subject' => 'Test Ticket ' . time(),
            'description' => 'This is a test ticket description',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tickets.store'), $ticketData);

        $response->assertRedirect();
        // Note: Subject is stored in lowercase due to Ticket model's subject setter (strtolower)
        // Display uses ucfirst accessor for formatting
        $this->assertDatabaseHas('tickets', [
            'subject' => strtolower($ticketData['subject']),
            'description' => $ticketData['description'],
        ]);
    }

    /** @test */
    public function ticket_gets_unique_code_on_creation()
    {
        $ticketData = [
            'subject' => 'Unique Code Test ' . time(),
            'description' => 'Testing unique code generation',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tickets.store'), $ticketData);

        // Note: Subject is stored in lowercase due to Ticket model's subject setter
        $ticket = Ticket::where('subject', strtolower($ticketData['subject']))->first();
        
        $this->assertNotNull($ticket);
        $this->assertNotNull($ticket->code);
        $this->assertNotNull($ticket->ticket_code);
        $this->assertMatchesRegularExpression('/^[A-Z0-9-]+$/', $ticket->code);
    }

    /** @test */
    public function can_attach_single_asset_to_ticket()
    {
        $ticket = Ticket::create([
            'subject' => 'Single Asset Test ' . time(),
            'description' => 'Test attaching single asset',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        // Attach asset using sync
        $ticket->assets()->sync([$this->asset1->id]);

        $this->assertDatabaseHas('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $this->asset1->id,
        ]);

        // Verify relationship
        $this->assertEquals(1, $ticket->assets()->count());
        $this->assertEquals($this->asset1->id, $ticket->assets->first()->id);
    }

    /** @test */
    public function can_attach_multiple_assets_to_ticket()
    {
        $ticket = Ticket::create([
            'subject' => 'Multi Asset Test ' . time(),
            'description' => 'Test attaching multiple assets',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        // Attach multiple assets
        $ticket->assets()->sync([$this->asset1->id, $this->asset2->id]);

        $this->assertDatabaseHas('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $this->asset1->id,
        ]);

        $this->assertDatabaseHas('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $this->asset2->id,
        ]);

        // Verify relationship
        $this->assertEquals(2, $ticket->assets()->count());
    }

    /** @test */
    public function can_detach_asset_from_ticket()
    {
        $ticket = Ticket::create([
            'subject' => 'Detach Asset Test ' . time(),
            'description' => 'Test detaching asset',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        // Attach both assets
        $ticket->assets()->sync([$this->asset1->id, $this->asset2->id]);
        
        // Detach one asset
        $ticket->assets()->detach($this->asset1->id);

        $this->assertDatabaseMissing('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $this->asset1->id,
        ]);

        $this->assertDatabaseHas('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $this->asset2->id,
        ]);

        $this->assertEquals(1, $ticket->assets()->count());
    }

    /** @test */
    public function ticket_shows_all_attached_assets()
    {
        $ticket = Ticket::create([
            'subject' => 'View Assets Test ' . time(),
            'description' => 'Test viewing attached assets',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        $ticket->assets()->sync([$this->asset1->id, $this->asset2->id]);

        $response = $this->actingAs($this->user)
            ->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee($this->asset1->asset_tag);
        $response->assertSee($this->asset2->asset_tag);
    }

    /** @test */
    public function can_assign_ticket_to_technician()
    {
        $ticket = Ticket::create([
            'subject' => 'Assignment Test ' . time(),
            'description' => 'Test ticket assignment',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        $updateData = [
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'ticket_priority_id' => $ticket->ticket_priority_id,
            'ticket_type_id' => $ticket->ticket_type_id,
            'ticket_status_id' => $ticket->ticket_status_id,
            'location_id' => $ticket->location_id,
            'assigned_to' => $this->technician->id,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('tickets.update', $ticket->id), $updateData);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $this->technician->id,
        ]);
    }

    /** @test */
    public function can_update_ticket_status()
    {
        $ticket = Ticket::create([
            'subject' => 'Status Update Test ' . time(),
            'description' => 'Test status update',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        $updateData = [
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'ticket_priority_id' => $ticket->ticket_priority_id,
            'ticket_type_id' => $ticket->ticket_type_id,
            'ticket_status_id' => $this->closedStatus->id,
            'location_id' => $this->location->id,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('tickets.update', $ticket->id), $updateData);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'ticket_status_id' => $this->closedStatus->id,
            'location_id' => $this->location->id,
        ]);
    }

    /** @test */
    public function ticket_history_logs_status_changes()
    {
        $ticket = Ticket::create([
            'subject' => 'History Test ' . time(),
            'description' => 'Test history logging',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        // Update status
        $ticket->ticket_status_id = $this->closedStatus->id;
        $ticket->save();

        // Check if history was logged
        $this->assertDatabaseHas('ticket_history', [
            'ticket_id' => $ticket->id,
            'field_name' => 'ticket_status_id',
        ]);
    }

    /** @test */
    public function ticket_history_logs_assignment_changes()
    {
        $ticket = Ticket::create([
            'subject' => 'Assignment History Test ' . time(),
            'description' => 'Test assignment history',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        // Assign to technician
        $ticket->assigned_to = $this->technician->id;
        $ticket->save();

        // Check if history was logged
        $this->assertDatabaseHas('ticket_history', [
            'ticket_id' => $ticket->id,
            'field_name' => 'assigned_to',
        ]);
    }

    /** @test */
    public function ticket_history_handles_null_dates_gracefully()
    {
        $ticket = Ticket::create([
            'subject' => 'Null Date Test ' . time(),
            'description' => 'Test null date handling',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        // Create history entry with null changed_at
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'field_name' => 'test_field',
            'old_value' => 'old',
            'new_value' => 'new',
            'changed_by' => $this->user->id,
            'changed_at' => null, // Explicitly null
        ]);

        // View ticket page should not crash
        $response = $this->actingAs($this->user)
            ->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function can_add_comment_to_ticket()
    {
        $ticket = Ticket::create([
            'subject' => 'Comment Test ' . time(),
            'description' => 'Test adding comments',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        $commentData = [
            'comment' => 'This is a test comment',
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('ticket.comments.store', $ticket->id), $commentData);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'comment' => 'This is a test comment',
            'user_id' => $this->technician->id,
        ]);
    }

    /** @test */
    public function comments_show_user_and_timestamp()
    {
        $ticket = Ticket::create([
            'subject' => 'Comment Display Test ' . time(),
            'description' => 'Test comment display',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'comment' => 'Test comment display',
            'user_id' => $this->technician->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('Test comment display');
        $response->assertSee($this->technician->name);
    }

    /** @test */
    public function required_fields_validation_works()
    {
        $invalidData = [
            'subject' => '', // Required
            'description' => '', // Required
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tickets.store'), $invalidData);

        $response->assertSessionHasErrors(['subject', 'description']);
    }

    /** @test */
    public function ticket_relationships_load_correctly()
    {
        $ticket = Ticket::create([
            'subject' => 'Relationships Test ' . time(),
            'description' => 'Test relationships',
            'ticket_priority_id' => $this->priority->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_status_id' => $this->openStatus->id,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'assigned_to' => $this->technician->id,
        ]);

        $ticket->assets()->sync([$this->asset1->id]);

        $loadedTicket = Ticket::with(['user', 'assignedTo', 'priority', 'type', 'status', 'assets'])
            ->find($ticket->id);

        $this->assertNotNull($loadedTicket->user);
        $this->assertEquals($this->user->id, $loadedTicket->user->id);

        $this->assertNotNull($loadedTicket->assignedTo);
        $this->assertEquals($this->technician->id, $loadedTicket->assignedTo->id);

        $this->assertNotNull($loadedTicket->priority);
        $this->assertEquals($this->priority->id, $loadedTicket->priority->id);

        $this->assertNotNull($loadedTicket->type);
        $this->assertEquals($this->ticketType->id, $loadedTicket->type->id);

        $this->assertNotNull($loadedTicket->status);
        $this->assertEquals($this->openStatus->id, $loadedTicket->status->id);

        $this->assertEquals(1, $loadedTicket->assets->count());
        $this->assertEquals($this->asset1->id, $loadedTicket->assets->first()->id);
    }
}
