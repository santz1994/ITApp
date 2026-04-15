<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MeetingRoomBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FixBlockingBookingEndTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:fix-blocking-end-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix blocking bookings that are finished but still show 23:59 end time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fixing blocking bookings with 23:59 end times...');

        // Find finished blocking bookings where:
        // 1. Purpose starts with "BLOCKED:"
        // 2. Status is "finished"
        // 3. end_datetime is 23:59:59
        // 4. finished_at is set (this should be the actual end time)
        
        $bookings = MeetingRoomBooking::where('purpose', 'LIKE', 'BLOCKED:%')
            ->where('status', 'finished')
            ->whereNotNull('finished_at')
            ->get();

        $fixedCount = 0;

        foreach ($bookings as $booking) {
            // Check if end_datetime is 23:59:59
            if ($booking->end_datetime->format('H:i:s') === '23:59:59') {
                // Update end_datetime to match finished_at
                $booking->update([
                    'end_datetime' => $booking->finished_at,
                ]);

                $this->info("✓ Booking ID #{$booking->id} - {$booking->room_name}");
                $this->info("  Old end: {$booking->end_datetime->format('Y-m-d H:i:s')}");
                $this->info("  New end: {$booking->finished_at->format('Y-m-d H:i:s')}");
                
                $fixedCount++;
            }
        }

        if ($fixedCount > 0) {
            $this->info("\n✓ Successfully fixed {$fixedCount} booking(s).");
            
            Log::info('Fixed blocking bookings end times', [
                'fixed_count' => $fixedCount,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        } else {
            $this->info('No bookings need fixing.');
        }

        return 0;
    }
}
