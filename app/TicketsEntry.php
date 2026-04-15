<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchServiceTrait;

class TicketsEntry extends Model
{
  use SearchServiceTrait;

  /**
   * FULLTEXT searchable columns
   * @var array
   */
  protected $searchColumns = ['description'];
  public function ticket()
  {
    return $this->hasOne(Ticket::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
