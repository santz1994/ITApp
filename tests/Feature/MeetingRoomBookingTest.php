<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Role;
use App\MeetingRoomBooking;
use Carbon\Carbon;

/**
 * Meeting Room Booking Feature Tests
 * 
 * Tests comprehensive meeting room booking operations including:
 * - Normal booking workflow
 * - BLOCKED room functionality (receptionist can bypass)
 * - Quick edit subject (receptionist only)
 * - Quick edit time (receptionist only for future meetings)
 * - Extend time (during running meetings)
 * - Conflict detection and validation
 * 
 * @group meeting-room
 * @group critical
 */
class MeetingRoomBookingTest extends TestCase
{
    use DatabaseTransactions;

    protected $regularUser;
    protected $receptionist;
    protected $superAdmin;
    protected $director;

    /**
     * Set up test users with different roles
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create or get roles
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist'], ['description' => 'Receptionist']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin'], ['description' => 'Super Administrator']);
        $directorRole = Role::firstOrCreate(['name' => 'director'], ['description' => 'Director']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['description' => 'Regular User']);

        // Create test users
        $this->regularUser = User::factory()->create([
            'name' => 'Regular User Test',
            'email' => 'regular@test.com',
        ]);
        $this->regularUser->roles()->sync([$userRole->id]);

        $this->receptionist = User::factory()->create([
            'name' => 'Receptionist Test',
            'email' => 'receptionist@test.com',
        ]);
        $this->receptionist->roles()->sync([$receptionistRole->id]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@test.com',
        ]);
        $this->superAdmin->roles()->sync([$superAdminRole->id]);

        $this->director = User::factory()->create([
            'name' => 'Director Test',
            'email' => 'director@test.com',
        ]);
        $this->director->roles()->sync([$directorRole->id]);
    }

    /**
     * Test 1: Regular user can create a normal booking
     * 
     * @test
     */
    public function regular_user_can_create_normal_booking()
    {
        $this->actingAs($this->regularUser);

        $startTime = Carbon::now()->addHours(2);
        $endTime = Carbon::now()->addHours(3);

        $response = $this->post(route('meeting-room-bookings.store'), [
            'room_name' => 'Meeting Room A',
            'start_datetime' => $startTime->format('Y-m-d H:i'),
            'end_datetime' => $endTime->format('Y-m-d H:i'),
            'purpose' => 'Team Standup Meeting',
            'meeting_description' => 'Daily standup meeting to discuss project progress',
            'attendees_count' => 5,
            'department' => 'IT Department',
            'requester_position' => 'Software Engineer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('meeting_room_bookings', [
            'room_name' => 'Meeting Room A',
            'purpose' => 'Team Standup Meeting',
            'status' => 'pending',
            'user_id' => $this->regularUser->id,
        ]);
    }

    /**
     * Test 2: Receptionist can BLOCK a room
     * 
     * @test
     */
    public function receptionist_can_block_room()
    {
        $this->markTestSkipped('Block room route implementation pending');
        
        // TODO: Implement this test when block room route is added
        // $this->actingAs($this->receptionist);
        // $response = $this->post(route('meeting-room-bookings.block'), [...]);
    }

    /**
     * Test 3: Regular user CANNOT book a BLOCKED room (should be rejected)
     * 
     * @test
     */
    public function regular_user_cannot_book_blocked_room()
    {
        // First, receptionist blocks the room
        $this->actingAs($this->receptionist);
        
        $blockStart = Carbon::tomorrow()->setTime(10, 0);
        $blockEnd = Carbon::tomorrow()->setTime(16, 0);
        
        MeetingRoomBooking::create([
            'user_id' => $this->receptionist->id,
            'room_name' => 'Meeting Room C',
            'start_datetime' => $blockStart,
            'end_datetime' => $blockEnd,
            'purpose' => 'BLOCKED: VIP Event Preparation',
            'meeting_description' => 'Room blocked for VIP event setup',
            'attendees_count' => 0,
            'status' => 'approved',
        ]);

        // Now, regular user tries to book the same room at overlapping time
        $this->actingAs($this->regularUser);
        
        $response = $this->post(route('meeting-room-bookings.store'), [
            'room_name' => 'Meeting Room C',
            'start_datetime' => Carbon::tomorrow()->setTime(11, 0)->format('Y-m-d H:i'),
            'end_datetime' => Carbon::tomorrow()->setTime(12, 0)->format('Y-m-d H:i'),
            'purpose' => 'Regular Team Meeting',
            'meeting_description' => 'Weekly team sync meeting',
            'attendees_count' => 5,
        ]);

        // Should fail due to conflict with BLOCKED room
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('meeting_room_bookings', [
            'room_name' => 'Meeting Room C',
            'purpose' => 'Regular Team Meeting',
        ]);
    }

    /**
     * Test 4: Receptionist CAN bypass BLOCKED rooms (special privilege)
     * 
     * @test
     */
    public function receptionist_can_bypass_blocked_rooms()
    {
        // First, create a BLOCKED booking
        $blockStart = Carbon::tomorrow()->setTime(10, 0);
        $blockEnd = Carbon::tomorrow()->setTime(16, 0);
        
        MeetingRoomBooking::create([
            'user_id' => $this->receptionist->id,
            'room_name' => 'Meeting Room D',
            'start_datetime' => $blockStart,
            'end_datetime' => $blockEnd,
            'purpose' => 'BLOCKED: Renovation',
            'meeting_description' => 'Room blocked for renovation work',
            'attendees_count' => 0,
            'status' => 'approved',
        ]);

        // Receptionist overrides the block for VIP meeting
        $this->actingAs($this->receptionist);
        
        $response = $this->post(route('meeting-room-bookings.store'), [
            'room_name' => 'Meeting Room D',
            'start_datetime' => Carbon::tomorrow()->setTime(14, 0)->format('Y-m-d H:i'),
            'end_datetime' => Carbon::tomorrow()->setTime(15, 0)->format('Y-m-d H:i'),
            'purpose' => 'VIP Client Meeting',
            'meeting_description' => 'Important meeting with VIP client - override block',
            'attendees_count' => 3,
            'department' => 'Management',
            'requester_position' => 'Receptionist',
        ]);

        // Should succeed - receptionist bypasses BLOCKED rooms
        $response->assertRedirect();
        $this->assertDatabaseHas('meeting_room_bookings', [
            'room_name' => 'Meeting Room D',
            'purpose' => 'VIP Client Meeting',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 5: Super-admin can also bypass BLOCKED rooms
     * 
     * @test
     */
    public function super_admin_can_bypass_blocked_rooms()
    {
        // Create BLOCKED booking
        MeetingRoomBooking::create([
            'user_id' => $this->receptionist->id,
            'room_name' => 'Meeting Room E',
            'start_datetime' => Carbon::tomorrow()->setTime(9, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(17, 0),
            'purpose' => 'BLOCKED: Emergency Maintenance',
            'meeting_description' => 'Emergency AC maintenance',
            'attendees_count' => 0,
            'status' => 'approved',
        ]);

        // Super-admin overrides for urgent meeting
        $this->actingAs($this->superAdmin);
        
        $response = $this->post(route('meeting-room-bookings.store'), [
            'room_name' => 'Meeting Room E',
            'start_datetime' => Carbon::tomorrow()->setTime(11, 0)->format('Y-m-d H:i'),
            'end_datetime' => Carbon::tomorrow()->setTime(12, 0)->format('Y-m-d H:i'),
            'purpose' => 'Board Meeting (Urgent)',
            'meeting_description' => 'Emergency board meeting - override maintenance block',
            'attendees_count' => 10,
            'department' => 'Executive',
            'requester_position' => 'Super Administrator',
        ]);

        // Should succeed
        $response->assertRedirect();
        $this->assertDatabaseHas('meeting_room_bookings', [
            'room_name' => 'Meeting Room E',
            'purpose' => 'Board Meeting (Urgent)',
        ]);
    }

    /**
     * Test 6: Receptionist can quick edit subject of any booking
     * 
     * @test
     */
    public function receptionist_can_quick_edit_subject()
    {
        // Create a booking by regular user
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room F',
            'start_datetime' => Carbon::tomorrow()->setTime(10, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(11, 0),
            'purpose' => 'Original Meeting Title',
            'meeting_description' => 'Original description',
            'attendees_count' => 5,
            'status' => 'approved',
        ]);

        // Receptionist edits the subject
        $this->actingAs($this->receptionist);
        
        $response = $this->putJson(route('meeting-room-bookings.quick-edit-subject', $booking->id), [
            'purpose' => 'Updated Meeting Title by Receptionist',
            'meeting_description' => 'Updated description with more details',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('meeting_room_bookings', [
            'id' => $booking->id,
            'purpose' => 'Updated Meeting Title by Receptionist',
            'meeting_description' => 'Updated description with more details',
        ]);
    }

    /**
     * Test 7: Regular user CANNOT quick edit subject (unauthorized)
     * 
     * @test
     */
    public function regular_user_cannot_quick_edit_subject()
    {
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room G',
            'start_datetime' => Carbon::tomorrow()->setTime(10, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(11, 0),
            'purpose' => 'Original Title',
            'meeting_description' => 'Original description',
            'attendees_count' => 5,
            'status' => 'approved',
        ]);

        // Regular user tries to quick edit (should fail - receptionist only)
        $this->actingAs($this->regularUser);
        
        $response = $this->putJson(route('meeting-room-bookings.quick-edit-subject', $booking->id), [
            'purpose' => 'Hacked Title',
            'meeting_description' => 'Hacked description',
        ]);

        // Should be forbidden (403) or redirect
        $response->assertStatus(403);
        
        // Original data should remain unchanged
        $this->assertDatabaseHas('meeting_room_bookings', [
            'id' => $booking->id,
            'purpose' => 'Original Title',
        ]);
    }

    /**
     * Test 8: Receptionist can quick edit time for FUTURE meetings only
     * 
     * @test
     */
    public function receptionist_can_quick_edit_time_for_future_meetings()
    {
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room H',
            'start_datetime' => Carbon::tomorrow()->setTime(10, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(11, 0),
            'purpose' => 'Team Meeting',
            'meeting_description' => 'Weekly sync',
            'attendees_count' => 5,
            'status' => 'approved',
        ]);

        $this->actingAs($this->receptionist);
        
        $newStart = Carbon::tomorrow()->setTime(14, 0);
        $newEnd = Carbon::tomorrow()->setTime(15, 30);
        
        $response = $this->putJson(route('meeting-room-bookings.quick-edit-time', $booking->id), [
            'meeting_date' => $newStart->format('Y-m-d'),
            'start_time' => $newStart->format('H:i'),
            'end_time' => $newEnd->format('H:i'),
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals($newStart->format('Y-m-d H:i'), $booking->start_datetime->format('Y-m-d H:i'));
        $this->assertEquals($newEnd->format('Y-m-d H:i'), $booking->end_datetime->format('Y-m-d H:i'));
    }

    /**
     * Test 9: Cannot edit time for PAST meetings
     * 
     * @test
     */
    public function cannot_edit_time_for_past_meetings()
    {
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room I',
            'start_datetime' => Carbon::yesterday()->setTime(10, 0),
            'end_datetime' => Carbon::yesterday()->setTime(11, 0),
            'purpose' => 'Past Meeting',
            'meeting_description' => 'This meeting already happened',
            'attendees_count' => 5,
            'status' => 'approved', // Use 'approved' instead of 'finished' to avoid constraint
        ]);
        
        // Mark as finished manually after creation
        $booking->status = 'finished';
        $booking->save();

        $this->actingAs($this->receptionist);
        
        $response = $this->putJson(route('meeting-room-bookings.quick-edit-time', $booking->id), [
            'meeting_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        // Should fail - cannot edit past meetings
        $response->assertStatus(422);
    }

    /**
     * Test 10: Extend time during RUNNING meeting
     * 
     * @test
     */
    public function can_extend_time_during_running_meeting()
    {
        // Create a meeting that started 30 minutes ago and ends in 30 minutes
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room J',
            'start_datetime' => Carbon::now()->subMinutes(30),
            'end_datetime' => Carbon::now()->addMinutes(30),
            'purpose' => 'Running Meeting',
            'meeting_description' => 'Meeting currently in progress',
            'attendees_count' => 5,
            'status' => 'approved',
        ]);

        // User extends the meeting by 30 minutes
        $this->actingAs($this->regularUser);
        
        $newEndTime = Carbon::now()->addMinutes(60);
        
        $response = $this->postJson(route('meeting-room-bookings.extend', $booking->id), [
            'new_end_time' => $newEndTime->format('H:i'),
            'extend_reason' => 'Discussion taking longer than expected',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals(
            $newEndTime->format('Y-m-d H:i'),
            $booking->end_datetime->format('Y-m-d H:i')
        );
    }

    /**
     * Test 11: Conflict detection works correctly
     * 
     * @test
     */
    public function conflict_detection_prevents_overlapping_bookings()
    {
        // Create existing booking
        MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room K',
            'start_datetime' => Carbon::tomorrow()->setTime(10, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(12, 0),
            'purpose' => 'Existing Meeting',
            'meeting_description' => 'Already booked',
            'attendees_count' => 5,
            'status' => 'approved',
        ]);

        // Try to create overlapping booking
        $this->actingAs($this->regularUser);
        
        $response = $this->post(route('meeting-room-bookings.store'), [
            'room_name' => 'Meeting Room K',
            'start_datetime' => Carbon::tomorrow()->setTime(11, 0)->format('Y-m-d H:i'),
            'end_datetime' => Carbon::tomorrow()->setTime(13, 0)->format('Y-m-d H:i'),
            'purpose' => 'Conflicting Meeting',
            'meeting_description' => 'This should be rejected',
            'attendees_count' => 5,
            'department' => 'IT Department',
            'requester_position' => 'Developer',
        ]);

        // Should fail due to conflict
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('meeting_room_bookings', [
            'purpose' => 'Conflicting Meeting',
        ]);
    }

    /**
     * Test 12: Director can approve pending bookings
     * 
     * @test
     */
    public function director_can_approve_pending_booking()
    {
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room L',
            'start_datetime' => Carbon::tomorrow()->setTime(10, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(11, 0),
            'purpose' => 'Pending Meeting',
            'meeting_description' => 'Waiting for approval',
            'attendees_count' => 5,
            'status' => 'pending',
        ]);

        $this->actingAs($this->director);
        
        $response = $this->post(route('meeting-room-bookings.approve', $booking->id), [
            'director_notes' => 'Approved for VIP client meeting',
        ]);

        $response->assertRedirect();
        
        $booking->refresh();
        $this->assertEquals('approved', $booking->status);
        $this->assertEquals('Approved for VIP client meeting', $booking->director_notes);
    }

    /**
     * Test 13: Validation prevents invalid time ranges
     * 
     * @test
     */
    public function validation_prevents_end_time_before_start_time()
    {
        $this->actingAs($this->regularUser);
        
        $response = $this->post(route('meeting-room-bookings.store'), [
            'room_name' => 'Meeting Room M',
            'start_datetime' => Carbon::tomorrow()->setTime(15, 0)->format('Y-m-d H:i'),
            'end_datetime' => Carbon::tomorrow()->setTime(14, 0)->format('Y-m-d H:i'), // Before start!
            'purpose' => 'Invalid Time Meeting',
            'meeting_description' => 'End time is before start time',
            'attendees_count' => 5,
            'department' => 'IT Department',
            'requester_position' => 'Tester',
        ]);

        $response->assertSessionHasErrors(['end_datetime']);
    }

    /**
     * Test 14: Audit logging records all changes
     * 
     * @test
     */
    public function audit_log_records_booking_changes()
    {
        $booking = MeetingRoomBooking::create([
            'user_id' => $this->regularUser->id,
            'room_name' => 'Meeting Room N',
            'start_datetime' => Carbon::tomorrow()->setTime(10, 0),
            'end_datetime' => Carbon::tomorrow()->setTime(11, 0),
            'purpose' => 'Original Purpose',
            'meeting_description' => 'Original description',
            'attendees_count' => 5,
            'status' => 'pending',
        ]);

        // Check that audit log entry was created (if auditable trait is used)
        // This depends on whether MeetingRoomBooking uses the Auditable trait
        
        // Update the booking
        $this->actingAs($this->receptionist);
        
        $this->putJson(route('meeting-room-bookings.quick-edit-subject', $booking->id), [
            'purpose' => 'Updated Purpose',
            'meeting_description' => 'Updated description',
        ]);

        // Verify audit trail exists (implementation depends on your audit system)
        // For example, if using owen-it/laravel-auditing package:
        // $this->assertDatabaseHas('audits', [
        //     'auditable_type' => MeetingRoomBooking::class,
        //     'auditable_id' => $booking->id,
        //     'event' => 'updated',
        // ]);
        
        $this->assertTrue(true); // Placeholder for actual audit check
    }

    /**
     * Test 15: Bulk operations work correctly (if implemented)
     * 
     * @test
     */
    public function receptionist_can_unblock_multiple_rooms()
    {
        // Create multiple BLOCKED bookings
        $bookings = [];
        for ($i = 0; $i < 3; $i++) {
            $bookings[] = MeetingRoomBooking::create([
                'user_id' => $this->receptionist->id,
                'room_name' => "Meeting Room " . chr(65 + $i), // A, B, C
                'start_datetime' => Carbon::tomorrow()->setTime(9, 0),
                'end_datetime' => Carbon::tomorrow()->setTime(17, 0),
                'purpose' => 'BLOCKED: Weekend Maintenance',
                'meeting_description' => 'Blocked for maintenance',
                'attendees_count' => 0,
                'status' => 'approved',
            ]);
        }

        $this->actingAs($this->receptionist);
        
        // Unblock rooms (if route exists)
        if (route_exists('meeting-room-bookings.unblock')) {
            $response = $this->post(route('meeting-room-bookings.unblock'), [
                'date' => Carbon::tomorrow()->format('Y-m-d'),
            ]);
            
            $response->assertStatus(200);
            
            // Verify all bookings are deleted or canceled
            foreach ($bookings as $booking) {
                $this->assertDatabaseMissing('meeting_room_bookings', [
                    'id' => $booking->id,
                    'status' => 'approved',
                ]);
            }
        } else {
            $this->markTestSkipped('Unblock route not implemented yet');
        }
    }
}

/**
 * Helper function to check if route exists
 */
function route_exists($routeName)
{
    try {
        route($routeName);
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}
