<?php

namespace App\Policies;

use App\AuditLog;
use App\User;
use Illuminate\Auth\Access\Response;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any audit logs.
     * 
     * Business Rules:
     * - Super Admin: Can view all audit logs
     * - Admin: Can view audit logs (filtered by controller)
     * - Management: Limited access via controller filtering
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'management']);
    }

    /**
     * Determine whether the user can view a specific audit log.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'management']);
    }

    /**
     * Determine whether the user can create audit logs.
     * Audit logs are system-generated, not manually created.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update audit logs.
     * Audit logs should be immutable for integrity.
     */
    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete audit logs.
     * Individual deletion not allowed - only bulk cleanup.
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore deleted audit logs.
     */
    public function restore(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete audit logs.
     */
    public function forceDelete(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can cleanup old audit logs.
     * 
     * Business Rules:
     * - Only Super Admin can perform bulk cleanup operations
     */
    public function cleanup(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can export audit logs.
     * 
     * Business Rules:
     * - Super Admin and Admin can export audit logs
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
