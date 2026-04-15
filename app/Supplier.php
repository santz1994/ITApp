<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
  use HasFactory;
  
  protected $fillable = ['name'];
  public $timestamps = false;

  public function invoice()
  {
    return $this->hasMany(Invoice::class);
  }

  public function asset()
  {
    return $this->hasMany(Asset::class);
  }
}
