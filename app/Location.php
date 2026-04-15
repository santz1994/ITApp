<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;
    
    protected $fillable = ['building', 'office', 'location_name', 'storeroom'];
    public $timestamps = false;

    public function movement()
    {
      return $this->hasMany(Movement::class);
    }

    public function ticket()
    {
      return $this->hasMany(Ticket::class);
    }

    // Accessor to provide consistent 'name' attribute
    public function getNameAttribute()
    {
        return $this->location_name;
    }
}
