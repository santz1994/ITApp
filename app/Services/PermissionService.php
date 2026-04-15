<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Create a new permission
     */
    public function createPermission(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
            'description' => $data['description'] ?? null
        ]);
    }
    
    /**
     * Update existing permission
     */
    public function updatePermission(int $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        
        $permission->update([
            'name' => $data['name'] ?? $permission->name,
            'description' => $data['description'] ?? $permission->description
        ]);
        
        return $permission->fresh();
    }
    
    /**
     * Delete permission
     */
    public function deletePermission(int $id): bool
    {
        $permission = Permission::findOrFail($id);
        
        // Detach from all roles first
        $permission->roles()->detach();
        
        return $permission->delete();
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(int $roleId, int $permissionId): bool
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);
        
        $role->givePermissionTo($permission);
        
        return true;
    }
    
    /**
     * Remove permission from role
     */
    public function removePermissionFromRole(int $roleId, int $permissionId): bool
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);
        
        $role->revokePermissionTo($permission);
        
        return true;
    }
    
    /**
     * Get single permission
     */
    public function getPermission(int $id): Permission
    {
        return Permission::with('roles')->findOrFail($id);
    }
    
    /**
     * Get all permissions with roles
     */
    public function getAllPermissions(): Collection
    {
        return Permission::with('roles')->get();
    }
}
