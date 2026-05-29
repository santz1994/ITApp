<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'plate_number',
        'brand',
        'model',
        'year',
        'color',
        'capacity',
        'status',
        'fuel_type',
        'insurance_expiry',
        'stnk_expiry',
        'notes',
        'photo',
        'current_mileage',
    ];

    protected $casts = [
        'year' => 'integer',
        'capacity' => 'integer',
        'current_mileage' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function bookings()
    {
        return $this->hasMany(VehicleBooking::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(VehicleMaintenanceLog::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    // ========================================
    // ACCESSORS & HELPERS
    // ========================================

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function getFullNameAttribute()
    {
        return "{$this->brand} {$this->model} ({$this->plate_number})";
    }

    public function isStnkExpired()
    {
        return $this->stnk_expiry && now()->gt($this->stnk_expiry);
    }

    public function isInsuranceExpired()
    {
        return $this->insurance_expiry && now()->gt($this->insurance_expiry);
    }
}