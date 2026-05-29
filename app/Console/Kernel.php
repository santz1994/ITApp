<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TestCriticalFixes::class,
        Commands\DetectDuplicateSerials::class,
        Commands\ExportDuplicateSerialRows::class,
        Commands\CleanupDuplicateSerials::class,
        Commands\DatabaseBackup::class,
        Commands\UpdateMeetingRoomStatuses::class,
        Commands\FixBlockingBookingEndTime::class,
        Commands\TestNotificationUpdate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Automatic database backup every Friday at 11:00 PM
        $schedule->command('db:backup')
                 ->weeklyOn(5, '23:00')
                 ->timezone('Asia/Jakarta');
        
        // Update meeting room statuses every 5 minutes
        // Changes 'approved' to 'finished' when end_datetime has passed
        $schedule->command('meetings:update-statuses')
                 ->everyFiveMinutes()
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping(); // Prevent multiple instances running at once
        
        // $schedule->command('migrate')->daily();
        // $schedule->command('db:seed')->daily();
    }
}
