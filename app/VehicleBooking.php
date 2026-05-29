<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'requested_by',
        'purpose',
        'destination',
        'estimated_distance',
        'start_datetime',
        'end_datetime',
        'passengers',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'actual_distance',
        'actual_fuel_cost',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'approved_at' => 'datetime',
        'estimated_distance' => 'decimal:2',
        'actual_distance' => 'decimal:2',
        'actual_fuel_cost' => 'decimal:2',
        'passengers' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_datetime', $date);
    }

    public function scopeOverlapping($query, $vehicleId, $startDatetime, $endDatetime)
    {
        return $query->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                $q->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    ->orWhere(function ($q2) use ($startDatetime, $endDatetime) {
                        $q2->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            });
    }

    // ========================================
    // STATUS HELPERS
    // ========================================

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'in_progress' => 'info',
            'completed' => 'primary',
            'cancelled' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}