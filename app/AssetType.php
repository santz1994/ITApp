<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetType extends Model
{
  use HasFactory;
  
  protected $fillable = ['type_name', 'abbreviation', 'spare'];
  public $timestamps = false;

  public function asset_model()
  {
    return $this->belongsTo(AssetModel::class);
  }

  // Relationship to assets through asset_models
  public function assets()
  {
    return $this->hasManyThrough(Asset::class, AssetModel::class, 'asset_type_id', 'model_id');
  }

  // Returns the count for assets of a specific asset type, for a specific division, that is in the 'Storeroom' with Status 'Ready to Deploy'
  public function sparesCount($assetType, $division)
  {
    return DB::table('assets')
                     ->join('asset_models', function ($join) {
                       $spare = DB::table('asset_types')->where('spare', 1)->pluck('id');
                       $join->on('assets.model_id', '=', 'asset_models.id')
                            ->whereIn('asset_models.asset_type_id', $spare);
                     })
                     ->join('movements', function ($join) {
                       $movement = DB::table('statuses')->where('name', 'Ready to Deploy')->pluck('id');
                       $location = DB::table('locations')->where('storeroom', 1)->pluck('id');
                       $join->on('assets.movement_id', '=', 'movements.id')
                            ->whereIn('movements.status_id', $movement)
                            ->whereIn('movements.location_id', $location);
                     })
                     ->where('division_id', $division)
                     ->where('asset_type_id', $assetType)
                     ->count();
  }

  // Accessor to provide consistent 'name' attribute
  public function getNameAttribute()
  {
      return $this->type_name;
  }
}
