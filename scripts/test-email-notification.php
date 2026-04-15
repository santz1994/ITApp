<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\NotificationSetting;
use App\User;
use App\Notifications\TicketCreated;
use App\Ticket;

echo "=== Testing Email Notification System ===\n\n";

// 1. Check notification settings
echo "1. Checking notification settings...\n";
$emailEnabled = NotificationSetting::isEnabled('email_enabled');
$ticketCreatedEnabled = NotificationSetting::isEnabled('email_ticket_created');

echo "   - Email enabled: " . ($emailEnabled ? 'YES' : 'NO') . "\n";
echo "   - Ticket created notification: " . ($ticketCreatedEnabled ? 'YES' : 'NO') . "\n\n";

// 2. Check SMTP configuration
echo "2. SMTP Configuration:\n";
echo "   - Host: " . config('mail.mailers.noverifysmtp.host') . "\n";
echo "   - Port: " . config('mail.mailers.noverifysmtp.port') . "\n";
echo "   - Username: " . config('mail.mailers.noverifysmtp.username') . "\n";
echo "   - From: " . config('mail.from.address') . "\n\n";

// 3. Get a test user
echo "3. Finding test user...\n";
$user = User::whereHas('roles', function($q) {
    $q->where('name', 'user');
})->first();

if (!$user) {
    echo "   ERROR: No user found!\n";
    exit(1);
}

echo "   - User: {$user->name} ({$user->email})\n\n";

// 4. Get a sample ticket for testing
echo "4. Finding sample ticket...\n";
$ticket = Ticket::where('user_id', $user->id)
                ->with(['ticket_status', 'ticket_priority'])
                ->first();

if (!$ticket) {
    echo "   - No existing ticket found, will need to create one manually\n\n";
    echo "   Please create a ticket through the web interface to test email notification.\n";
    exit(0);
}

echo "   - Ticket: {$ticket->ticket_code} - {$ticket->subject}\n";
echo "   - Status: {$ticket->ticket_status->status}\n";
echo "   - Priority: {$ticket->ticket_priority->priority}\n\n";

// 5. Test sending notification
echo "5. Testing email notification...\n";
try {
    $user->notify(new TicketCreated($ticket));
    echo "   ✓ Email notification sent successfully!\n";
    echo "   Check inbox: {$user->email}\n\n";
} catch (\Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
}

echo "=== Test Completed ===\n";
