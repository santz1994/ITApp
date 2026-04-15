<?php

namespace Tests\Feature;

use App\Ticket;
use App\TicketHistory;
use App\TicketsStatus;
use App\TicketsPriority;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test for ticket audit logging
 * Verifies that all ticket changes are properly recorded in ticket_history
 */
class TicketAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test data
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary records (use firstOrCreate to avoid duplicates)
        TicketsStatus::firstOrCreate(['id' => 1], ['status' => 'Open']);
        TicketsStatus::firstOrCreate(['id' => 2], ['status' => 'In Progress']);
        TicketsStatus::firstOrCreate(['id' => 3], ['status' => 'Closed']);
        
        TicketsPriority::firstOrCreate(['id' => 1], ['priority' => 'Urgent']);
        TicketsPriority::firstOrCreate(['id' => 2], ['priority' => 'High']);
        TicketsPriority::firstOrCreate(['id' => 3], ['priority' => 'Medium']);
        
        User::firstOrCreate(['id' => 1], ['name' => 'System User', 'email' => 'system@test.com', 'password' => bcrypt('password'), 'is_active' => 1]);
        User::firstOrCreate(['id' => 2], ['name' => 'Tech Support', 'email' => 'tech@test.com', 'password' => bcrypt('password'), 'is_active' => 1]);
        User::firstOrCreate(['id' => 3], ['name' => 'Manager', 'email' => 'manager@test.com', 'password' => bcrypt('password'), 'is_active' => 1]);
    }

    /**
     * Test that ticket status change is logged to ticket_history via direct model update
     */
    public function test_ticket_status_change_is_logged()
    {
        // Create a ticket with known initial values
        $ticket = Ticket::factory()->create([
            'ticket_status_id' => 1, // Open
            'ticket_priority_id' => 1, // Urgent
        ]);
        
        // Count history before update
        $historyCountBefore = $ticket->history()->count();
        \Log::info('Direct update test - History count before', ['count' => $historyCountBefore, 'ticket_id' => $ticket->id]);
        
        // Get the authenticated user
        $user = User::findOrFail(2);
        
        // Act as user for audit logging
        $this->actingAs($user);
        
        // Update ticket status directly via Eloquent (not HTTP to isolate the issue)
        $ticket->update([
            'ticket_status_id' => 2, // In Progress
        ]);
        
        // Reload ticket from database to ensure fresh data
        $ticket = Ticket::find($ticket->id);
        $historyCountAfter = $ticket->history()->count();
        \Log::info('Direct update test - History count after', ['count' => $historyCountAfter, 'ticket_id' => $ticket->id]);
        
        // Get all history records for debugging
        $allHistory = $ticket->history()->get();
        \Log::info('Direct update test - All history records', ['records' => $allHistory->map(fn($h) => [
            'id' => $h->id,
            'field_changed' => $h->field_changed,
            'old_value' => $h->old_value,
            'new_value' => $h->new_value,
            'change_type' => $h->change_type,
            'event_type' => $h->event_type,
            'changed_by_user_id' => $h->changed_by_user_id,
        ])->toArray()]);
        
        // Verify history was created (count increased)
        $this->assertGreaterThan($historyCountBefore, $historyCountAfter, 
            "History count should increase after direct model update. Before: {$historyCountBefore}, After: {$historyCountAfter}");
        
        // Verify the specific status change history
        $history = $ticket->history()->where('field_changed', 'ticket_status_id')->first();
        $this->assertNotNull($history, 'Status change history record should exist');
        $this->assertEquals('1', $history->old_value);
        $this->assertEquals('2', $history->new_value);
        $this->assertEquals('field_change', $history->change_type);
    }

    /**
     * Test that ticket status change via HTTP PATCH is logged
     */
    public function test_ticket_status_change_via_http_is_logged()
    {
        // Create a ticket with known initial values
        $ticket = Ticket::factory()->create([
            'ticket_status_id' => 1, // Open
            'ticket_priority_id' => 1, // Urgent
        ]);
        
        $historyCountBefore = $ticket->history()->count();
        
        // Get the authenticated user
        $user = User::findOrFail(2);
        
        // Update status via HTTP
        $response = $this->actingAs($user)
             ->patch(route('tickets.update', $ticket), [
                 'subject' => $ticket->subject,
                 'description' => $ticket->description,
                 'ticket_status_id' => 2, // In Progress
                 'ticket_priority_id' => $ticket->ticket_priority_id,
                 'ticket_type_id' => $ticket->ticket_type_id,
                 'location_id' => $ticket->location_id,
                 'user_id' => $ticket->user_id,
             ]);
        
        // Verify the response was successful (redirect or ok)
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]), 'Update should return 200 or 302');
        
        // Reload ticket from database
        $ticket = Ticket::find($ticket->id);
        $historyCountAfter = $ticket->history()->count();
        
        // Verify history was created (count increased)
        $this->assertGreaterThan($historyCountBefore, $historyCountAfter, 
            "History count should increase after HTTP update. Before: {$historyCountBefore}, After: {$historyCountAfter}");
        
        // Verify the specific status change history
        $history = $ticket->history()->where('field_changed', 'ticket_status_id')->first();
        $this->assertNotNull($history, 'Status change history record should exist');
        $this->assertEquals('1', $history->old_value);
        $this->assertEquals('2', $history->new_value);
        $this->assertEquals('field_change', $history->change_type);
    }

    /**
     * Test that ticket priority change is logged
     */
    public function test_ticket_priority_change_is_logged()
    {
        $ticket = Ticket::factory()->create([
            'ticket_priority_id' => 1, // Urgent
        ]);
        
        // Update priority
        $ticket->update(['ticket_priority_id' => 2]); // High
        
        // Verify history
        $history = $ticket->history()->where('field_changed', 'ticket_priority_id')->first();
        $this->assertNotNull($history);
        $this->assertEquals('1', $history->old_value);
        $this->assertEquals('2', $history->new_value);
    }

    /**
     * Test that ticket assignment change is logged
     */
    public function test_ticket_assignment_change_is_logged()
    {
        $ticket = Ticket::factory()->create([
            'assigned_to' => null,
        ]);
        
        // Assign ticket
        $ticket->update(['assigned_to' => 2]);
        
        // Verify history
        $history = $ticket->history()->where('field_changed', 'assigned_to')->first();
        $this->assertNotNull($history);
        $this->assertNull($history->old_value); // Was null
        $this->assertEquals('2', $history->new_value); // Now assigned to user 2
    }

    /**
     * Test that multiple changes are independently logged
     */
    public function test_multiple_ticket_changes_are_logged_independently()
    {
        $ticket = Ticket::factory()->create([
            'ticket_status_id' => 1,
            'ticket_priority_id' => 1,
            'assigned_to' => null,
        ]);
        
        // Get initial history count (there may be some from creation)
        $initialHistoryCount = $ticket->history()->count();
        
        // Change multiple fields
        $ticket->update([
            'ticket_status_id' => 2,
            'ticket_priority_id' => 3,
            'assigned_to' => 2,
        ]);
        
        // Refresh and verify
        $ticket->refresh();
        
        // Should have at least 3 NEW history records
        $this->assertGreaterThanOrEqual($initialHistoryCount + 3, $ticket->history()->count());
        
        // Verify each field was logged
        $statusHistory = $ticket->history()->where('field_changed', 'ticket_status_id')->first();
        $priorityHistory = $ticket->history()->where('field_changed', 'ticket_priority_id')->first();
        $assignmentHistory = $ticket->history()->where('field_changed', 'assigned_to')->first();
        
        $this->assertNotNull($statusHistory);
        $this->assertNotNull($priorityHistory);
        $this->assertNotNull($assignmentHistory);
    }

    /**
     * Test that ticket_history is immutable (cannot update)
     */
    public function test_ticket_history_is_immutable()
    {
        $ticket = Ticket::factory()->create();
        $ticket->update(['ticket_status_id' => 2]);
        
        $history = $ticket->history()->first();
        
        // Attempting to update should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('immutable');
        
        $history->update(['old_value' => 'modified']);
    }

    /**
     * Test ticket history filtering scopes
     */
    public function test_ticket_history_filtering_scopes()
    {
        $ticket1 = Ticket::factory()->create();
        $ticket2 = Ticket::factory()->create();
        
        // Create changes
        $ticket1->update(['ticket_status_id' => 2]);
        $ticket2->update(['ticket_priority_id' => 2]);
        
        // Test forField scope
        $statusChanges = TicketHistory::forField('ticket_status_id')->get();
        $this->assertGreaterThan(0, $statusChanges->count());
        $this->assertTrue($statusChanges->every(fn($h) => $h->field_changed === 'ticket_status_id'));
        
        // Test ticket-specific history
        $ticket1->refresh();
        $ticket1History = $ticket1->history;
        $this->assertTrue($ticket1History->every(fn($h) => $h->ticket_id === $ticket1->id));
    }
}
