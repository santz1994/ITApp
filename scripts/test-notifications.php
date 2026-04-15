<?php
/**
 * Notification System Test Script
 * Run: php test-notifications.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Ticket;
use App\Models\MeetingRoom;
use App\NotificationSetting;
use Illuminate\Support\Facades\DB;

echo "\n=== NOTIFICATION SYSTEM TEST ===\n\n";

// 1. Check System Settings
echo "1. System-wide Notification Settings:\n";
echo str_repeat("-", 50) . "\n";
$settings = NotificationSetting::all(['key', 'value']);
foreach ($settings as $setting) {
    $status = $setting->value ? '✓ Enabled' : '✗ Disabled';
    echo sprintf("   %-30s %s\n", $setting->key, $status);
}

// 2. Check User Preferences
echo "\n2. User Notification Preferences:\n";
echo str_repeat("-", 50) . "\n";
$users = User::select('id', 'name', 'email', 'notify_email', 'notify_ticket_created', 
                      'notify_ticket_assigned', 'notify_ticket_updated', 
                      'notify_meeting_approved', 'notify_meeting_rejected')
             ->limit(5)
             ->get();

foreach ($users as $user) {
    echo "   User: {$user->name} ({$user->email})\n";
    echo "     - Master Email: " . ($user->notify_email ? '✓' : '✗') . "\n";
    echo "     - Ticket Created: " . ($user->notify_ticket_created ? '✓' : '✗') . "\n";
    echo "     - Ticket Assigned: " . ($user->notify_ticket_assigned ? '✓' : '✗') . "\n";
    echo "     - Ticket Updated: " . ($user->notify_ticket_updated ? '✓' : '✗') . "\n";
    echo "     - Meeting Approved: " . ($user->notify_meeting_approved ? '✓' : '✗') . "\n";
    echo "     - Meeting Rejected: " . ($user->notify_meeting_rejected ? '✓' : '✗') . "\n";
    echo "\n";
}

// 3. Check Email Configuration
echo "3. Email Configuration:\n";
echo str_repeat("-", 50) . "\n";
echo "   MAIL_MAILER: " . config('mail.default') . "\n";
echo "   MAIL_HOST: " . config('mail.mailers.noverifysmtp.host') . "\n";
echo "   MAIL_PORT: " . config('mail.mailers.noverifysmtp.port') . "\n";
echo "   MAIL_USERNAME: " . config('mail.mailers.noverifysmtp.username') . "\n";
echo "   MAIL_ENCRYPTION: " . config('mail.mailers.noverifysmtp.encryption') . "\n";
echo "   MAIL_FROM: " . config('mail.from.address') . " (" . config('mail.from.name') . ")\n";

// 4. Check Recent Tickets
echo "\n4. Recent Tickets (for testing):\n";
echo str_repeat("-", 50) . "\n";
$tickets = Ticket::with(['user', 'ticket_status', 'ticket_priority'])
                 ->orderBy('created_at', 'desc')
                 ->limit(5)
                 ->get();

if ($tickets->isEmpty()) {
    echo "   No tickets found.\n";
} else {
    foreach ($tickets as $ticket) {
        echo sprintf("   #%d - %s (Status: %s)\n", 
                    $ticket->id, 
                    $ticket->title, 
                    $ticket->ticket_status->name ?? 'N/A');
        echo "     Created by: {$ticket->user->name}\n";
        echo "     Priority: " . ($ticket->ticket_priority->name ?? 'N/A') . "\n";
        echo "\n";
    }
}

// 5. Check Recent Meeting Room Bookings
echo "5. Recent Meeting Room Bookings:\n";
echo str_repeat("-", 50) . "\n";
$bookings = DB::table('meeting_room_bookings')
              ->join('users', 'meeting_room_bookings.user_id', '=', 'users.id')
              ->join('meeting_rooms', 'meeting_room_bookings.meeting_room_id', '=', 'meeting_rooms.id')
              ->select('meeting_room_bookings.*', 'users.name as user_name', 'meeting_rooms.name as room_name')
              ->orderBy('meeting_room_bookings.created_at', 'desc')
              ->limit(5)
              ->get();

if ($bookings->isEmpty()) {
    echo "   No bookings found.\n";
} else {
    foreach ($bookings as $booking) {
        $status = $booking->is_approved === null ? 'Pending' : ($booking->is_approved ? 'Approved' : 'Rejected');
        echo sprintf("   #%d - %s @ %s\n", $booking->id, $booking->room_name, $booking->booking_date);
        echo "     Booked by: {$booking->user_name}\n";
        echo "     Status: {$status}\n";
        echo "\n";
    }
}

echo "\n=== TEST SUMMARY ===\n";
echo "System is ready for notification testing.\n";
echo "\nTo test notifications:\n";
echo "1. Create a new ticket at: http://192.168.1.87/tickets/create\n";
echo "2. Book a meeting room at: http://192.168.1.87/meeting-rooms\n";
echo "3. Approve/reject booking at: http://192.168.1.87/admin/meeting-rooms/approval\n";
echo "4. Check email inbox for notifications\n";
echo "\nTo manage settings:\n";
echo "- System settings: http://192.168.1.87/admin/notification-settings\n";
echo "- User preferences: http://192.168.1.87/profile/notifications\n\n";
