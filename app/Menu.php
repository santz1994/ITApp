<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'label',
        'route',
        'url',
        'icon',
        'parent_id',
        'order_index',
        'is_active',
        'is_external',
        'target',
        'css_class',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_external' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Get the parent menu item
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get all child menu items (submenu)
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
                    ->orderBy('order_index');
    }

    /**
     * Get active children only
     */
    public function activeChildren()
    {
        return $this->hasMany(Menu::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('order_index');
    }

    /**
     * Get roles that can access this menu
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_role')
                    ->withPivot('can_view')
                    ->withTimestamps();
    }

    /**
     * Get users with specific access to this menu
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'menu_user')
                    ->withPivot('can_view')
                    ->withTimestamps();
    }

    /**
     * Scope: Get only top-level menus (no parent)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Get only active menus
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get menus ordered by order_index
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Get menu URL
     * Returns route URL if route exists, otherwise returns direct URL
     */
    public function getUrlAttribute()
    {
        if ($this->route && \Route::has($this->route)) {
            return route($this->route);
        }
        
        return $this->attributes['url'] ?? '#';
    }

    /**
     * Check if menu is accessible by a specific role
     */
    public function isAccessibleByRole($roleId)
    {
        return $this->roles()->where('role_id', $roleId)
                    ->wherePivot('can_view', true)
                    ->exists();
    }

    /**
     * Check if menu is accessible by a specific user
     */
    public function isAccessibleByUser(User $user)
    {
        // Check user-specific override first
        $userAccess = $this->users()
                          ->where('user_id', $user->id)
                          ->first();
        
        if ($userAccess) {
            return $userAccess->pivot->can_view;
        }

        // Check role-based access
        $userRoleIds = $user->roles->pluck('id')->toArray();
        
        return $this->roles()
                    ->whereIn('role_id', $userRoleIds)
                    ->wherePivot('can_view', true)
                    ->exists();
    }

    /**
     * Scope: Get menus accessible by user
     */
    public function scopeAccessibleByUser($query, User $user)
    {
        $userRoleIds = $user->roles->pluck('id')->toArray();
        
        return $query->where(function($q) use ($user, $userRoleIds) {
            // Has role permission
            $q->whereHas('roles', function($roleQuery) use ($userRoleIds) {
                $roleQuery->whereIn('role_id', $userRoleIds)
                         ->where('can_view', true);
            })
            // OR has user-specific permission
            ->orWhereHas('users', function($userQuery) use ($user) {
                $userQuery->where('user_id', $user->id)
                         ->where('can_view', true);
            });
        });
    }

    /**
     * Get breadcrumb trail for this menu
     */
    public function getBreadcrumbTrail()
    {
        $trail = collect([$this]);
        $current = $this;
        
        while ($current->parent) {
            $current = $current->parent;
            $trail->prepend($current);
        }
        
        return $trail;
    }
}
