<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLifecycleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'event_type',
        'description',
        'metadata',
        'user_id',
        'event_date',
        'ticket_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'event_date' => 'datetime',
    ];

    /**
     * Event type constants
     */
    const EVENT_ACQUISITION = 'acquisition';
    const EVENT_DEPLOYMENT = 'deployment';
    const EVENT_TRANSFER = 'transfer';
    const EVENT_MAINTENANCE = 'maintenance';
    const EVENT_REPAIR = 'repair';
    const EVENT_UPGRADE = 'upgrade';
    const EVENT_AUDIT = 'audit';
    const EVENT_WARRANTY_EXPIRY = 'warranty_expiry';
    const EVENT_DEPRECIATION = 'depreciation';
    const EVENT_RETIREMENT = 'retirement';
    const EVENT_DISPOSAL = 'disposal';
    const EVENT_STOLEN = 'stolen';
    const EVENT_LOST = 'lost';
    const EVENT_FOUND = 'found';
    const EVENT_DAMAGE = 'damage';
    const EVENT_OTHER = 'other';

    /**
     * Get the asset that this event belongs to
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who triggered the event
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related ticket (if any)
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Scope to filter by event type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope to filter by asset
     */
    public function scopeForAsset($query, $assetId)
    {
        return $query->where('asset_id', $assetId);
    }

    /**
     * Scope to get events within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Get a human-readable event type label
     */
    public function getEventTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->event_type));
    }

    /**
     * Get event icon class (for UI)
     */
    public function getEventIconAttribute()
    {
        $icons = [
            self::EVENT_ACQUISITION => 'fa-shopping-cart',
            self::EVENT_DEPLOYMENT => 'fa-rocket',
            self::EVENT_TRANSFER => 'fa-exchange',
            self::EVENT_MAINTENANCE => 'fa-wrench',
            self::EVENT_REPAIR => 'fa-tools',
            self::EVENT_UPGRADE => 'fa-arrow-up',
            self::EVENT_AUDIT => 'fa-check-square',
            self::EVENT_WARRANTY_EXPIRY => 'fa-clock-o',
            self::EVENT_DEPRECIATION => 'fa-line-chart',
            self::EVENT_RETIREMENT => 'fa-power-off',
            self::EVENT_DISPOSAL => 'fa-trash',
            self::EVENT_STOLEN => 'fa-user-secret',
            self::EVENT_LOST => 'fa-question-circle',
            self::EVENT_FOUND => 'fa-check',
            self::EVENT_DAMAGE => 'fa-exclamation-triangle',
            self::EVENT_OTHER => 'fa-info-circle',
        ];

        return $icons[$this->event_type] ?? 'fa-circle';
    }

    /**
     * Get event color class (for UI badges)
     */
    public function getEventColorAttribute()
    {
        $colors = [
            self::EVENT_ACQUISITION => 'success',
            self::EVENT_DEPLOYMENT => 'info',
            self::EVENT_TRANSFER => 'warning',
            self::EVENT_MAINTENANCE => 'primary',
            self::EVENT_REPAIR => 'warning',
            self::EVENT_UPGRADE => 'success',
            self::EVENT_AUDIT => 'info',
            self::EVENT_WARRANTY_EXPIRY => 'warning',
            self::EVENT_DEPRECIATION => 'default',
            self::EVENT_RETIREMENT => 'default',
            self::EVENT_DISPOSAL => 'danger',
            self::EVENT_STOLEN => 'danger',
            self::EVENT_LOST => 'danger',
            self::EVENT_FOUND => 'success',
            self::EVENT_DAMAGE => 'warning',
            self::EVENT_OTHER => 'default',
        ];

        return $colors[$this->event_type] ?? 'default';
    }
}
