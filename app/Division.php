<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
  use HasFactory;
  
  protected $fillable = ['name'];
  public $timestamps = false;
  
  public function asset()
  {
    return $this->hasMany(Asset::class);
  }

  public function invoice()
  {
    return $this->hasMany(Invoice::class);
  }

  public function budget()
  {
    return $this->hasMany(Budget::class);
  }
}
