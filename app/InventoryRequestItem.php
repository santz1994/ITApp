<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'item_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_fulfilled',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_approved' => 'integer',
        'quantity_fulfilled' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function request()
    {
        return $this->belongsTo(InventoryRequest::class, 'request_id');
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    // ========================================
    // HELPERS
    // ========================================

    public function isFullyFulfilled()
    {
        $approved = $this->quantity_approved ?? $this->quantity_requested;
        return $this->quantity_fulfilled >= $approved;
    }

    public function getRemainingQuantityAttribute()
    {
        $approved = $this->quantity_approved ?? $this->quantity_requested;
        return max(0, $approved - $this->quantity_fulfilled);
    }
}