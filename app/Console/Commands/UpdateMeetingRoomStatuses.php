<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MeetingRoomBooking;
use App\Events\MeetingRoomBookingStatusChanged;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateMeetingRoomStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update meeting room booking statuses based on current time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info('Starting meeting room status update at ' . $now->format('Y-m-d H:i:s'));

        // Find all approved bookings where end_datetime has passed
        $expiredBookings = MeetingRoomBooking::where('status', 'approved')
            ->where('end_datetime', '<', $now)
            ->get();

        $updatedCount = 0;

        foreach ($expiredBookings as $booking) {
            $oldStatus = $booking->status;
            
            // Use end_datetime as finished_at since it reflects the actual end time
            // (e.g., if receptionist unblocked early, end_datetime is already updated to that time)
            $booking->update([
                'status' => 'finished',
                'finished_at' => $booking->end_datetime, // Use the actual end_datetime from database
            ]);

            // Fire event for status change
            event(new MeetingRoomBookingStatusChanged($booking, $oldStatus, 'finished'));

            $this->info("Booking ID #{$booking->id} - {$booking->room_name} marked as finished (ended at {$booking->end_datetime->format('Y-m-d H:i')})");
            
            $updatedCount++;
        }

        if ($updatedCount > 0) {
            $this->info("✓ Successfully updated {$updatedCount} booking(s) to 'finished' status.");
            
            Log::info('Meeting room statuses auto-updated', [
                'updated_count' => $updatedCount,
                'timestamp' => $now->format('Y-m-d H:i:s'),
            ]);
        } else {
            $this->info('No bookings to update at this time.');
        }

        return 0;
    }
}
