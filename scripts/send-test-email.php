<?php
/**
 * Test Email Notification Script
 * Send test notification email to all users
 * Run: php send-test-email.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;
use App\NotificationSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "\n=== TEST EMAIL NOTIFICATION ===\n\n";

// Check email configuration
echo "1. Email Configuration:\n";
echo str_repeat("-", 60) . "\n";
echo "   MAIL_MAILER: " . config('mail.default') . "\n";
echo "   MAIL_HOST: " . config('mail.mailers.noverifysmtp.host') . "\n";
echo "   MAIL_PORT: " . config('mail.mailers.noverifysmtp.port') . "\n";
echo "   MAIL_FROM: " . config('mail.from.address') . "\n";
echo "   MAIL_ENCRYPTION: " . config('mail.mailers.noverifysmtp.encryption') . "\n\n";

// Check system settings
echo "2. System Notification Settings:\n";
echo str_repeat("-", 60) . "\n";
$emailEnabled = NotificationSetting::isEnabled('email_enabled');
echo "   Email System: " . ($emailEnabled ? "✓ Enabled" : "✗ Disabled") . "\n\n";

if (!$emailEnabled) {
    echo "❌ Email notification is disabled in system settings!\n";
    echo "   Enable it at: http://192.168.1.87/admin/notification-settings\n\n";
    exit(1);
}

// Get all users
echo "3. Getting Users:\n";
echo str_repeat("-", 60) . "\n";
$users = User::select('id', 'name', 'email', 'notify_email')->get();
echo "   Found " . $users->count() . " users\n\n";

if ($users->isEmpty()) {
    echo "❌ No users found!\n\n";
    exit(1);
}

// Send test email to each user
echo "4. Sending Test Emails:\n";
echo str_repeat("-", 60) . "\n";

$sent = 0;
$skipped = 0;
$failed = 0;

foreach ($users as $user) {
    echo sprintf("   %-30s (%-30s) ", $user->name, $user->email);
    
    // Check if user has email notifications enabled
    if (!$user->notify_email) {
        echo "⊘ SKIPPED (notifications disabled)\n";
        $skipped++;
        continue;
    }
    
    try {
        Mail::raw("This is a test notification email from IT Quty System.\n\nHi {$user->name},\n\nThis email confirms that your notification system is working correctly.\n\nTimestamp: " . now()->format('Y-m-d H:i:s') . "\n\nIf you received this email, your notification preferences are active.\n\nBest regards,\nIT Quty Team", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('[TEST] IT Quty Notification System');
        });
        
        echo "✓ SENT\n";
        $sent++;
        
        // Small delay to avoid overwhelming the mail server
        usleep(500000); // 0.5 second delay
        
    } catch (\Exception $e) {
        echo "✗ FAILED: " . $e->getMessage() . "\n";
        $failed++;
        Log::error('Test email failed', [
            'user' => $user->email,
            'error' => $e->getMessage()
        ]);
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo "   ✓ Sent:    {$sent}\n";
echo "   ⊘ Skipped: {$skipped} (notifications disabled by user)\n";
echo "   ✗ Failed:  {$failed}\n";
echo str_repeat("=", 60) . "\n\n";

if ($sent > 0) {
    echo "✓ Test emails sent successfully!\n";
    echo "  Check your inbox for test notifications.\n\n";
} else {
    echo "⚠ No emails were sent.\n";
    if ($skipped > 0) {
        echo "  All users have notifications disabled.\n";
        echo "  Enable at: http://192.168.1.87/profile/notifications\n\n";
    }
}
