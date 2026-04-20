<?php

namespace App\Policies;

use App\Role;
use App\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any roles.
     * 
     * Business Rules:
     * - Super Admin: Can view all roles
     * - Admin: Can view all roles
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can view a specific role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can create roles.
     * 
     * Business Rules:
     * - Only Super Admin can create new roles
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can update roles.
     * 
     * Business Rules:
     * - Only Super Admin can edit roles
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can delete roles.
     * 
     * Business Rules:
     * - Only Super Admin can delete roles
     * - Cannot delete canonical system roles
     */
    public function delete(User $user, Role $role): bool
    {
        if (!$user->hasRole('super-admin')) {
            return false;
        }

        // Protect system roles
        $systemRoles = Role::canonicalNames();
        return !in_array($role->name, $systemRoles);
    }

    /**
     * Determine whether the user can restore deleted roles.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete roles.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can manage role permissions.
     * 
     * Business Rules:
     * - Only Super Admin can assign/remove permissions from roles
     */
    public function managePermissions(User $user, Role $role): bool
    {
        return $user->hasRole('super-admin');
    }
}
