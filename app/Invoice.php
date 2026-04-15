<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
  protected $fillable = ['invoice_number', 'order_number', 'supplier_id', 'division_id', 'invoiced_date', 'total'];

  protected $dates = ['invoiced_date'];

  public function supplier()
  {
    return $this->belongsTo(Supplier::class);
  }

  public function division()
  {
    return $this->belongsTo(Division::class);
  }

  public function asset()
  {
    return $this->hasMany(Asset::class);
  }

  /**
   * Accessor for invoice_date (alias for invoiced_date)
   * Maintains backward compatibility with old code
   */
  public function getInvoiceDateAttribute()
  {
    return $this->invoiced_date;
  }

  /**
   * Mutator for invoice_date (alias for invoiced_date)
   * Allows setting via either name
   */
  public function setInvoiceDateAttribute($value)
  {
    $this->attributes['invoiced_date'] = $value;
  }
}
