<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Business Rules:
     * - Super Admin: Can view all users
     * - Admin: Can view all users
     * - Management: Can view users in their division
     * - Users: Cannot view user list
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'management']);
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Business Rules:
     * - Super Admin: Can view any user
     * - Admin: Can view any user
     * - Management: Can view users in their division
     * - Users: Can only view their own profile
     */
    public function view(User $user, User $model): bool
    {
        // Super admin and admin can view any user
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Management can view users in their division
        if ($user->hasRole('management') && $user->division_id === $model->division_id) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     * 
     * Business Rules:
     * - Only Super Admin and Admin can create users
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Business Rules:
     * - Super Admin: Can update any user
     * - Admin: Can update users (except super-admins)
     * - Management: Cannot update users
     * - Users: Can update their own profile (limited fields)
     */
    public function update(User $user, User $model): bool
    {
        // Super admin can update any user
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can update users (except super-admins)
        if ($user->hasRole('admin') && !$model->hasRole('super-admin')) {
            return true;
        }

        // Users can update their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Business Rules:
     * - Only Super Admin can delete users
     * - Cannot delete own account
     * - Users should be deactivated rather than deleted (audit trail)
     * 
     * Note: When $model is null (called as @can('delete', App\User::class)),
     * this checks if user has permission to delete users in general
     */
    public function delete(User $user, ?User $model = null): bool
    {
        // Only super-admin can delete users
        if (!$user->hasRole('super-admin')) {
            return false;
        }

        // If no specific model (checking permission in general), allow
        if ($model === null) {
            return true;
        }

        // Cannot delete own account
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     * 
     * Only super-admin can restore soft-deleted users
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * 
     * Only super-admin can force delete users
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only super-admin can force delete
        if (!$user->hasRole('super-admin')) {
            return false;
        }

        // Cannot force delete own account
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can assign roles to the model.
     * 
     * Business Rules:
     * - Super Admin: Can assign any role
     * - Admin: Can assign roles (except super-admin)
     * - Management: Cannot assign roles
     * - Users: Cannot assign roles
     */
    public function assignRole(User $user, User $model): bool
    {
        // Super admin can assign any role
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can assign roles (except super-admin role)
        return $user->hasRole('admin') && !$model->hasRole('super-admin');
    }

    /**
     * Determine whether the user can activate/deactivate the model.
     * 
     * Business Rules:
     * - Super Admin: Can activate/deactivate any user
     * - Admin: Can activate/deactivate users (except super-admins)
     * - Management: Cannot activate/deactivate users
     * - Cannot deactivate own account
     */
    public function toggleActive(User $user, User $model): bool
    {
        // Cannot deactivate own account
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can toggle any user
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can toggle users (except super-admins)
        return $user->hasRole('admin') && !$model->hasRole('super-admin');
    }

    /**
     * Determine whether the user can reset the model's password.
     * 
     * Business Rules:
     * - Super Admin: Can reset any password
     * - Admin: Can reset passwords (except super-admins)
     * - Users: Can reset their own password
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Super admin can reset any password
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can reset passwords (except super-admins)
        if ($user->hasRole('admin') && !$model->hasRole('super-admin')) {
            return true;
        }

        // Users can reset their own password
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can view the model's activity logs.
     * 
     * Business Rules:
     * - Super Admin: Can view any user's logs
     * - Admin: Can view any user's logs
     * - Management: Can view logs for users in their division
     * - Users: Can view their own activity logs
     */
    public function viewActivityLogs(User $user, User $model): bool
    {
        // Super admin and admin can view any logs
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Management can view logs for users in their division
        if ($user->hasRole('management') && $user->division_id === $model->division_id) {
            return true;
        }

        // Users can view their own logs
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can impersonate the model.
     * 
     * Business Rules:
     * - Only Super Admin can impersonate users
     * - Cannot impersonate other super-admins
     * - Cannot impersonate own account (redundant)
     */
    public function impersonate(User $user, User $model): bool
    {
        // Only super-admin can impersonate
        if (!$user->hasRole('super-admin')) {
            return false;
        }

        // Cannot impersonate another super-admin
        if ($model->hasRole('super-admin')) {
            return false;
        }

        // Cannot impersonate own account
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage roles and permissions system.
     * 
     * Business Rules:
     * - Only Super Admin can manage the role system (create/edit/delete roles and permissions)
     */
    public function manageRoles(User $user): bool
    {
        return $user->hasRole('super-admin');
    }
}
