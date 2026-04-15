<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketsPriority extends Model
{
  use HasFactory;
  
  protected $fillable = ['priority'];
  public $timestamps = false;

  public function ticket()
  {
    return $this->hasMany(Ticket::class);
  }

  // Accessor to provide consistent 'name' attribute
  public function getNameAttribute()
  {
      return $this->priority;
  }
}
