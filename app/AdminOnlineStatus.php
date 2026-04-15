<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminOnlineStatus extends Model
{
    protected $table = 'admin_online_status';
    
    protected $fillable = [
        'user_id', 'last_activity', 'is_available_for_assignment'
    ];

    protected $dates = ['last_activity'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeOnline($query, $minutes = 10)
    {
        return $query->where('last_activity', '>=', now()->subMinutes($minutes));
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available_for_assignment', true);
    }

    // Static methods
    public static function updateActivity($userId)
    {
        return self::updateOrCreate(
            ['user_id' => $userId],
            ['last_activity' => now()]
        );
    }

    public static function getOnlineAdmins($minutes = 10)
    {
        return self::online($minutes)
                   ->available()
                   ->with('user')
                   ->get();
    }

    // Accessors
    public function getIsOnlineAttribute()
    {
        return $this->last_activity && $this->last_activity->diffInMinutes(now()) <= 10;
    }
}