<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manufacturer extends Model
{
  use HasFactory;
  
  protected $fillable = ['name'];
  public $timestamps = false;

  public function asset_model()
  {
    return $this->belongsTo(AssetModel::class);
  }
}
