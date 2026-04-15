<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingRoomDisplaySetting extends Model
{
    protected $fillable = [
        'room_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
