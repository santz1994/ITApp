<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryStockMovement extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    // ========================================
    // HELPERS
    // ========================================

    public function isIncoming()
    {
        return $this->type === 'in';
    }

    public function isOutgoing()
    {
        return $this->type === 'out';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'adjustment' => 'Penyesuaian',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}