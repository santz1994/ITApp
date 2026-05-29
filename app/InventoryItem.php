<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'unit',
        'current_stock',
        'minimum_stock',
        'unit_price',
        'location',
        'photo',
        'qr_code',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(InventoryStockMovement::class, 'item_id');
    }

    public function requestItems()
    {
        return $this->hasMany(InventoryRequestItem::class, 'item_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // ========================================
    // ACCESSORS & HELPERS
    // ========================================

    public function isLowStock()
    {
        return $this->minimum_stock > 0 && $this->current_stock <= $this->minimum_stock;
    }

    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    public function getTotalValueAttribute()
    {
        return $this->current_stock * $this->unit_price;
    }
}