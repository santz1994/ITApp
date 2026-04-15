<?php

namespace App\Services;

use App\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class RoleService
{
    /**
     * Create a new role
     */
    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
        ]);
        
        // Attach permissions if provided
        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }
        
        // Clear cache
        Artisan::call('cache:clear');
        
        return $role;
    }
    
    /**
     * Update existing role
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? $role->description,
        ]);
        
        // Sync permissions
        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        } else {
            $role->permissions()->sync([]);
        }
        
        // Clear cache
        Artisan::call('cache:clear');
        
        return $role->fresh();
    }
    
    /**
     * Delete role
     */
    public function deleteRole(Role $role): array
    {
        // Check if role has users
        $userCount = $role->users()->count();
        
        if ($userCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete role \"{$role->display_name}\" because it has {$userCount} users assigned."
            ];
        }
        
        $roleName = $role->display_name;
        $role->delete();
        
        // Clear cache
        Artisan::call('cache:clear');
        
        return [
            'success' => true,
            'message' => "Role \"{$roleName}\" deleted successfully!"
        ];
    }
    
    /**
     * Get role with permissions
     */
    public function getRole(Role $role): Role
    {
        return $role->load('permissions');
    }
    
    /**
     * Get all roles with relationships
     */
    public function getAllRoles(): Collection
    {
        return Role::withCount(['users', 'permissions'])->get();
    }
}
