<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location_id',
        'capacity',
        'status',
        'description',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function bookings()
    {
        return $this->hasMany(MeetingRoomBooking::class, 'room_id');
    }
}
