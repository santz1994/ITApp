<?php
// Repro script to create a ticket without ticket_status_id to test the defensive default
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\TicketService;
use Illuminate\Support\Facades\Log;

$service = new TicketService();

$payload = [
    'subject' => 'idol test meeting',
    'description' => 'Meeting bersama',
    'ticket_priority_id' => 3,
    'ticket_type_id' => 1,
    'location_id' => 1,
    'user_id' => 1,
    // Intentionally omit 'ticket_status_id' to reproduce the failure
];

try {
    $ticket = $service->createTicket($payload);
    echo "Created ticket id={$ticket->id} code={$ticket->ticket_code} status_id={$ticket->ticket_status_id}\n";
} catch (Exception $e) {
    echo "Error creating ticket: " . $e->getMessage() . "\n";
}

// Also tail the laravel log to show any warning entries (optional)
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    echo "\n-- Last 30 lines of laravel.log --\n";
    $lines = array_slice(file($logPath), -30);
    foreach ($lines as $line) echo $line;
}
