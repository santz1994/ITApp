<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketsStatus extends Model
{
  use HasFactory;
  
  protected $fillable = ['status'];
  public $timestamps = false;

  public function ticket()
  {
    return $this->hasMany(Ticket::class);
  }

  // Accessor to provide consistent 'name' attribute
  public function getNameAttribute()
  {
      return $this->status;
  }
}
