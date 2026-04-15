<?php

namespace App;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
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
