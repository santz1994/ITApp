<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingRoomLcdSetting extends Model
{
    protected $fillable = [
        'rooms_per_slide',
        'slide_interval_seconds',
    ];

    protected $casts = [
        'rooms_per_slide' => 'integer',
        'slide_interval_seconds' => 'integer',
    ];
}
