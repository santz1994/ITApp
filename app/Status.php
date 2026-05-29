<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];
    public $timestamps = false;

    public function movement()
    {
        return $this->hasMany(Movement::class);
    }

    public function assets()
    {
        // Asset relations removed
        return collect();
    }
}
