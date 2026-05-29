<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'description',
        'cost',
        'maintenance_date',
        'next_maintenance_date',
        'mileage_at_service',
        'service_provider',
        'recorded_by',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'mileage_at_service' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeUpcoming($query)
    {
        return $query->where('next_maintenance_date', '>=', now())
            ->orderBy('next_maintenance_date', 'asc');
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_maintenance_date', '<', now());
    }
}