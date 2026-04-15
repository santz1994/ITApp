<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class TestNotificationUpdate extends Command
{
    protected $signature = 'test:notification-update {user_id}';
    protected $description = 'Test notification preference update';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User ID {$userId} not found!");
            return 1;
        }

        $this->info("Current user: {$user->name} ({$user->email})");
        $this->info("Current notify_email: " . ($user->notify_email ? 'ENABLED (1)' : 'DISABLED (0)'));
        
        // Try to update
        $newValue = !$user->notify_email;
        $this->info("\nAttempting to update notify_email to: " . ($newValue ? 'ENABLED (1)' : 'DISABLED (0)'));
        
        $result = $user->update([
            'notify_email' => $newValue,
        ]);

        $this->info("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));

        // Refresh and check
        $user = $user->fresh();
        $this->info("After refresh notify_email: " . ($user->notify_email ? 'ENABLED (1)' : 'DISABLED (0)'));

        return 0;
    }
}
