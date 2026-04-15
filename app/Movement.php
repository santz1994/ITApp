<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
  protected $fillable = ['location_id', 'status_id'];

  public function asset()
  {
    return $this->belongsTo(Asset::class, 'asset_id');
  }

  public function location()
  {
    return $this->belongsTo(Location::class);
  }

  public function status()
  {
    return $this->belongsTo(Status::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
