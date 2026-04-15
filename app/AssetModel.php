<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetModel extends Model
{
    use HasFactory;
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'asset_models';

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'manufacturer_id',
        'asset_type_id',
        'pcspec_id',
        'asset_model',
        'part_number',
    ];

    /**
     * Provide a convenient `name` attribute used by views.
     */
    public function getNameAttribute()
    {
        return $this->asset_model;
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function asset_type()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function pcspec()
    {
        return $this->belongsTo(Pcspec::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'model_id');
    }

}
