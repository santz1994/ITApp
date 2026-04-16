<?php

namespace App;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'access_level',
    ];

    protected $casts = [
        'access_level' => 'integer',
    ];

    /**
     * Menus that are accessible by this role
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_role')
                    ->withPivot('can_view')
                    ->withTimestamps();
    }
}
