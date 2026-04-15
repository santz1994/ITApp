<?php

namespace App\Events;

use App\MeetingRoomBooking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingRoomBookingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;

    /**
     * Create a new event instance.
     *
     * @param  MeetingRoomBooking  $booking
     * @return void
     */
    public function __construct(MeetingRoomBooking $booking)
    {
        $this->booking = $booking;
    }
}
