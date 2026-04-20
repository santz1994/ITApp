<?php

namespace App\Services;

use App\Menu;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Get menu tree for a specific user (with permission check)
     * 
     * @param User $user
     * @param bool $useCache
     * @return Collection
     */
    public function getMenusForUser(User $user, bool $useCache = true): Collection
    {
        $cacheKey = 'user_menus_' . $user->id;
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Get all active menus accessible by user
        $menus = Menu::active()
                    ->accessibleByUser($user)
                    ->with(['activeChildren' => function($query) use ($user) {
                        $query->accessibleByUser($user);
                    }])
                    ->topLevel()
                    ->ordered()
                    ->get();
        
        // Build hierarchical tree
        $menuTree = $this->buildMenuTree($menus);
        
        // Cache for 1 hour
        if ($useCache) {
            Cache::put($cacheKey, $menuTree, 3600);
        }
        
        return $menuTree;
    }

    /**
     * Build hierarchical menu tree
     * 
     * @param Collection $menus
     * @return Collection
     */
    protected function buildMenuTree(Collection $menus): Collection
    {
        return $menus->map(function($menu) {
            // Recursively load children
            if ($menu->activeChildren->count() > 0) {
                $menu->setRelation('activeChildren', $this->buildMenuTree($menu->activeChildren));
            }
            
            return $menu;
        });
    }

    /**
     * Check if user can access a specific menu
     * 
     * @param User $user
     * @param int $menuId
     * @return bool
     */
    public function canAccess(User $user, int $menuId): bool
    {
        $menu = Menu::find($menuId);
        
        if (!$menu || !$menu->is_active) {
            return false;
        }
        
        return $menu->isAccessibleByUser($user);
    }

    /**
     * Clear menu cache for a specific user
     * 
     * @param User $user
     * @return void
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget('user_menus_' . $user->id);
    }

    /**
     * Clear all menu caches
     * 
     * @return void
     */
    public function clearAllCache(): void
    {
        // Clear pattern-based cache keys (if using Redis/Memcached)
        // For file cache, you might need different approach
        Cache::flush(); // Use with caution - clears ALL cache
        
        // Better approach: Keep track of user IDs and clear individually
        // Or use cache tags (Redis/Memcached only)
    }

    /**
     * Sync menu permissions for a role
     * 
     * @param int $roleId
     * @param array $menuIds Array of menu IDs with permission
     * @return void
     */
    public function syncRolePermissions(int $roleId, array $menuIds): void
    {
        $role = \App\Role::findOrFail($roleId);
        
        // Prepare sync data with pivot values
        $syncData = [];
        foreach ($menuIds as $menuId) {
            $syncData[$menuId] = ['can_view' => true];
        }
        
        $role->menus()->sync($syncData);
        
        // Clear cache for all users with this role
        $this->clearCacheForRole($roleId);
    }

    /**
     * Clear cache for all users with a specific role
     * 
     * @param int $roleId
     * @return void
     */
    protected function clearCacheForRole(int $roleId): void
    {
        $users = User::whereHas('roles', function($query) use ($roleId) {
            $query->where('id', $roleId);
        })->get();
        
        foreach ($users as $user) {
            $this->clearUserCache($user);
        }
    }

    /**
     * Grant menu access to a specific user (override role permissions)
     * 
     * @param int $userId
     * @param int $menuId
     * @param bool $canView
     * @return void
     */
    public function grantUserAccess(int $userId, int $menuId, bool $canView = true): void
    {
        $user = User::findOrFail($userId);
        $menu = Menu::findOrFail($menuId);
        
        $user->menus()->syncWithoutDetaching([
            $menuId => ['can_view' => $canView]
        ]);
        
        $this->clearUserCache($user);
    }

    /**
     * Revoke user-specific menu access
     * 
     * @param int $userId
     * @param int $menuId
     * @return void
     */
    public function revokeUserAccess(int $userId, int $menuId): void
    {
        $user = User::findOrFail($userId);
        
        $user->menus()->detach($menuId);
        
        $this->clearUserCache($user);
    }

    /**
     * Get all menus as flat list (for management interface)
     * 
     * @return Collection
     */
    public function getAllMenus(): Collection
    {
        return Menu::with(['parent', 'roles', 'users'])
                   ->ordered()
                   ->get();
    }

    /**
     * Get menu hierarchy as nested array (for drag-drop interface)
     * 
     * @return array
     */
    public function getMenuHierarchy(): array
    {
        $menus = Menu::with('activeChildren')
                    ->topLevel()
                    ->active()
                    ->ordered()
                    ->get();
        
        return $this->buildNestedArray($menus);
    }

    /**
     * Build nested array for menu hierarchy
     * 
     * @param Collection $menus
     * @return array
     */
    protected function buildNestedArray(Collection $menus): array
    {
        $result = [];
        
        foreach ($menus as $menu) {
            $item = [
                'id' => $menu->id,
                'label' => $menu->label,
                'icon' => $menu->icon,
                'route' => $menu->route,
                'url' => $menu->url,
                'order' => $menu->order_index,
                'is_active' => $menu->is_active,
            ];
            
            if ($menu->activeChildren->count() > 0) {
                $item['children'] = $this->buildNestedArray($menu->activeChildren);
            }
            
            $result[] = $item;
        }
        
        return $result;
    }

    /**
     * Update menu order (for drag-drop reordering)
     * 
     * @param array $orderedMenuIds Array of menu IDs in new order
     * @param int|null $parentId Parent menu ID (null for top level)
     * @return void
     */
    public function updateMenuOrder(array $orderedMenuIds, ?int $parentId = null): void
    {
        foreach ($orderedMenuIds as $index => $menuId) {
            Menu::where('id', $menuId)->update([
                'order_index' => $index,
                'parent_id' => $parentId,
            ]);
        }
        
        $this->clearAllCache();
    }

    /**
     * Create a new menu item
     * 
     * @param array $data
     * @return Menu
     */
    public function createMenu(array $data): Menu
    {
        $menu = Menu::create($data);
        
        $this->clearAllCache();
        
        return $menu;
    }

    /**
     * Update a menu item
     * 
     * @param int $menuId
     * @param array $data
     * @return Menu
     */
    public function updateMenu(int $menuId, array $data): Menu
    {
        $menu = Menu::findOrFail($menuId);
        $menu->update($data);
        
        $this->clearAllCache();
        
        return $menu;
    }

    /**
     * Delete a menu item (and all its children)
     * 
     * @param int $menuId
     * @return void
     */
    public function deleteMenu(int $menuId): void
    {
        $menu = Menu::findOrFail($menuId);
        $menu->delete(); // Cascade delete children via foreign key
        
        $this->clearAllCache();
    }

    /**
     * Get permission matrix for a menu (which roles can access)
     * 
     * @param int $menuId
     * @return array
     */
    public function getMenuPermissionMatrix(int $menuId): array
    {
        $menu = Menu::with('roles')->findOrFail($menuId);
        $allRoles = \App\Role::query()->canonical()->get();
        
        $matrix = [];
        foreach ($allRoles as $role) {
            $hasAccess = $menu->roles->contains('id', $role->id);
            
            $matrix[] = [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'can_view' => $hasAccess,
            ];
        }
        
        return $matrix;
    }
}
