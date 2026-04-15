<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number', 'supplier_id', 'order_date', 'total_cost'
    ];

    protected $dates = ['order_date'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'purchase_order_id');
    }
}
