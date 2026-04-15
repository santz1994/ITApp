<?php
/**
 * Create New User Script
 * Run: php create-user.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;
use App\Division;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "\n=== CREATE NEW USER ===\n\n";

// Find Business Development division
$division = Division::where('name', 'Business Development')->first();

if (!$division) {
    echo "❌ Division 'Business Development' not found!\n";
    echo "Available divisions:\n";
    Division::all()->each(function($div) {
        echo "   - {$div->name} (ID: {$div->id})\n";
    });
    exit(1);
}

// Check if email already exists
$existing = User::where('email', 'angga@quty.co.id')->first();
if ($existing) {
    echo "❌ User with email 'angga@quty.co.id' already exists!\n";
    echo "   User ID: {$existing->id}\n";
    echo "   Name: {$existing->name}\n";
    exit(1);
}

// Create new user
$user = new User();
$user->name = 'Angga';
$user->email = 'angga@quty.co.id';
$user->division_id = $division->id;
$user->password = Hash::make('123456');
$user->api_token = Str::random(80); // Generate unique API token
$user->notify_email = true;
$user->notify_ticket_created = true;
$user->notify_ticket_assigned = true;
$user->notify_ticket_updated = true;
$user->notify_meeting_approved = true;
$user->notify_meeting_rejected = true;
$user->save();

// Assign User role
$user->assignRole('User');

echo "✓ User created successfully!\n\n";
echo "Details:\n";
echo str_repeat("-", 50) . "\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Division: {$division->name}\n";
echo "   Role: " . $user->getRoleNames()->first() . "\n";
echo "   Password: 123456\n";
echo "\n";
echo "Notification Preferences (All Enabled):\n";
echo "   ✓ Master Email Notification\n";
echo "   ✓ Ticket Created Notification\n";
echo "   ✓ Ticket Assigned Notification\n";
echo "   ✓ Ticket Updated Notification\n";
echo "   ✓ Meeting Approved Notification\n";
echo "   ✓ Meeting Rejected Notification\n";
echo "\n";
echo "Login URL: http://192.168.1.87/login\n";
echo str_repeat("=", 50) . "\n\n";
