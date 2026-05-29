<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
  use HasFactory;
  
  protected $fillable = ['name'];
  public $timestamps = false;
  
  // Legacy asset/invoice/budget relations removed.
}
