<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the subject (the model that was acted upon)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the causer (the user who performed the action)
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by log name
     */
    public function scopeInLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by event type
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by causer
     */
    public function scopeCausedBy($query, $causer)
    {
        return $query->where('causer_type', get_class($causer))
                     ->where('causer_id', $causer->id);
    }
}
