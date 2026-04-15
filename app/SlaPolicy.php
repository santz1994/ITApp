<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'response_time',
        'resolution_time',
        'priority_id',
        'business_hours_only',
        'escalation_time',
        'escalate_to_user_id',
        'is_active',
    ];

    protected $casts = [
        'business_hours_only' => 'boolean',
        'is_active' => 'boolean',
        'response_time' => 'integer',
        'resolution_time' => 'integer',
        'escalation_time' => 'integer',
    ];

    /**
     * Get the priority associated with this SLA policy
     */
    public function priority()
    {
        return $this->belongsTo(TicketsPriority::class, 'priority_id');
    }

    /**
     * Get the user to escalate to
     */
    public function escalateToUser()
    {
        return $this->belongsTo(User::class, 'escalate_to_user_id');
    }

    /**
     * Scope to get only active policies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate deadline from a given start time
     */
    public function calculateDeadline($startTime, $type = 'response')
    {
        $minutes = $type === 'response' ? $this->response_time : $this->resolution_time;
        
        if ($this->business_hours_only) {
            // TODO: Implement business hours calculation
            // For now, just add minutes
            return $startTime->copy()->addMinutes($minutes);
        }
        
        return $startTime->copy()->addMinutes($minutes);
    }

    /**
     * Check if a ticket is within SLA
     */
    public function isWithinSla($startTime, $currentTime, $type = 'response')
    {
        $deadline = $this->calculateDeadline($startTime, $type);
        return $currentTime->lte($deadline);
    }
}
