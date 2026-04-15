<?php

namespace App\Events;

use App\MeetingRoomBooking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingRoomBookingStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     *
     * @param  MeetingRoomBooking  $booking
     * @param  string  $oldStatus
     * @param  string  $newStatus
     * @return void
     */
    public function __construct(MeetingRoomBooking $booking, string $oldStatus, string $newStatus)
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
