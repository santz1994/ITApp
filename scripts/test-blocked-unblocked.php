<?php
/**
 * Test Script for Blocked/Unblocked Meeting Room Feature
 * 
 * Purpose: Test the blocking and unblocking functionality
 * and verify that end_datetime is updated correctly when unblocked
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\MeetingRoomBooking;
use App\User;
use Carbon\Carbon;

echo "===========================================\n";
echo "BLOCKED/UNBLOCKED FEATURE TEST\n";
echo "===========================================\n\n";

// Get first available user
$testUser = User::first();
if (!$testUser) {
    die("ERROR: No users found in database. Please create a user first.\n");
}

// Test Room
$testRoom = 'Ruang Meeting 2';

echo "Test Room: {$testRoom}\n";
echo "Test User: {$testUser->name} (ID: {$testUser->id})\n";
echo "Test Time: " . now()->format('Y-m-d H:i:s') . "\n\n";

// ============================================
// STEP 1: Check existing blocked bookings
// ============================================
echo "STEP 1: Check existing blocked bookings\n";
echo "-------------------------------------------\n";

$existingBlocked = MeetingRoomBooking::where('room_name', $testRoom)
    ->where('purpose', 'LIKE', 'BLOCKED:%')
    ->where('status', 'approved')
    ->get();

if ($existingBlocked->count() > 0) {
    echo "Found {$existingBlocked->count()} existing blocked booking(s):\n";
    foreach ($existingBlocked as $blocked) {
        echo "  ID: {$blocked->id}\n";
        echo "  Purpose: {$blocked->purpose}\n";
        echo "  Start: {$blocked->start_datetime->format('Y-m-d H:i:s')}\n";
        echo "  End: {$blocked->end_datetime->format('Y-m-d H:i:s')}\n";
        echo "  Duration: {$blocked->duration}\n";
        echo "\n";
    }
} else {
    echo "No existing blocked bookings found.\n";
}
echo "\n";

// ============================================
// STEP 2: Create a test blocked booking
// ============================================
echo "STEP 2: Create test blocked booking\n";
echo "-------------------------------------------\n";

$startTime = now();
$endTime = now()->endOfDay(); // Will be set to 23:59:59

$newBlocked = MeetingRoomBooking::create([
    'room_name' => $testRoom,
    'user_id' => $testUser->id,
    'start_datetime' => $startTime,
    'end_datetime' => $endTime,
    'purpose' => 'BLOCKED: Test blocking feature',
    'attendees_count' => 0,
    'status' => 'approved',
    'approved_by' => $testUser->id,
    'approved_at' => now(),
    'director_notes' => 'Test blocking feature',
    'department' => 'IT Department',
    'requester_position' => 'System Tester',
]);

echo "✓ Created blocked booking:\n";
echo "  ID: {$newBlocked->id}\n";
echo "  Purpose: {$newBlocked->purpose}\n";
echo "  Start: {$newBlocked->start_datetime->format('Y-m-d H:i:s')}\n";
echo "  End (BEFORE UNBLOCK): {$newBlocked->end_datetime->format('Y-m-d H:i:s')}\n";
echo "  Expected: 23:59:59 today\n";
echo "\n";

// Wait a moment to simulate time passing
echo "Waiting 2 seconds to simulate time passing...\n";
sleep(2);
echo "\n";

// ============================================
// STEP 3: Simulate unblocking (update end_datetime)
// ============================================
echo "STEP 3: Simulate unblocking\n";
echo "-------------------------------------------\n";

$unblockTime = now();
echo "Unblock time: {$unblockTime->format('Y-m-d H:i:s')}\n\n";

// Using the UPDATED query logic from the fix
$updated = MeetingRoomBooking::where('room_name', $testRoom)
    ->where('purpose', 'LIKE', 'BLOCKED:%')
    ->where('status', 'approved')
    ->where('start_datetime', '<=', $unblockTime)
    ->where('end_datetime', '>', $unblockTime)
    ->update([
        'end_datetime' => $unblockTime
    ]);

echo "Updated {$updated} blocked booking(s)\n\n";

// ============================================
// STEP 4: Verify the changes
// ============================================
echo "STEP 4: Verify changes after unblock\n";
echo "-------------------------------------------\n";

$refreshedBooking = MeetingRoomBooking::find($newBlocked->id);

echo "Blocked booking after unblock:\n";
echo "  ID: {$refreshedBooking->id}\n";
echo "  Purpose: {$refreshedBooking->purpose}\n";
echo "  Start: {$refreshedBooking->start_datetime->format('Y-m-d H:i:s')}\n";
echo "  End (AFTER UNBLOCK): {$refreshedBooking->end_datetime->format('Y-m-d H:i:s')}\n";
echo "  Duration: {$refreshedBooking->duration}\n";
echo "\n";

// Compare times
$endBefore = $endTime->format('H:i:s');
$endAfter = $refreshedBooking->end_datetime->format('H:i:s');

if ($endBefore !== $endAfter) {
    echo "✓ SUCCESS: End time changed from {$endBefore} to {$endAfter}\n";
    echo "✓ End time is now the actual unblock time (not 23:59:59)\n";
} else {
    echo "✗ FAILED: End time did not change (still {$endAfter})\n";
}
echo "\n";

// ============================================
// STEP 5: Test Excel export format
// ============================================
echo "STEP 5: Test Excel export format\n";
echo "-------------------------------------------\n";

// Simulate how it appears in Excel export
$startDate = Carbon::parse($refreshedBooking->start_datetime);
$endDate = Carbon::parse($refreshedBooking->end_datetime);

$tanggal = $startDate->format('d/m/Y');
$waktu = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');

// Keterangan formatting
$keterangan = $refreshedBooking->purpose;
if (str_starts_with($keterangan, 'BLOCKED: ')) {
    $keterangan = 'Blocked : ' . substr($keterangan, 9);
}

echo "Excel Export Format:\n";
echo "  TANGGAL: {$tanggal}\n";
echo "  WAKTU: {$waktu}\n";
echo "  MEETING ROOM: {$refreshedBooking->room_name}\n";
echo "  DEPARTEMEN: {$refreshedBooking->department}\n";
echo "  KETERANGAN: {$keterangan}\n";
echo "\n";

// ============================================
// STEP 6: Cleanup (optional)
// ============================================
echo "STEP 6: Cleanup\n";
echo "-------------------------------------------\n";
echo "Do you want to delete the test booking? (y/n): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) == 'y') {
    $refreshedBooking->delete();
    echo "✓ Test booking deleted (ID: {$newBlocked->id})\n";
} else {
    echo "✗ Test booking kept in database (ID: {$newBlocked->id})\n";
    echo "  You can manually delete it later if needed.\n";
}

echo "\n";
echo "===========================================\n";
echo "TEST COMPLETED\n";
echo "===========================================\n";
