<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AssetMaintenanceLog extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'asset_id',
        'ticket_id',
        'performed_by',
        'maintenance_type',
        'description',
        'part_name',
        'parts_used',
        'cost',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'parts_used' => 'array',
        'cost' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('before_photos')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        
        $this->addMediaCollection('after_photos')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        
        $this->addMediaCollection('receipts')
             ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }

    /**
     * Relasi ke Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Relasi ke Ticket (optional)
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relasi ke User yang melakukan maintenance
     */
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope untuk filter berdasarkan tipe maintenance
     */
    public function scopeByType($query, $type)
    {
        return $query->where('maintenance_type', $type);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
