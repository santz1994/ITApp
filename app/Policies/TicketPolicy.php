<?php

namespace App\Policies;

use App\Ticket;
use App\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * All authenticated users can view tickets (with role-based filtering in controller)
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view tickets
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Business Rules:
     * - Super Admin: Can view all tickets
     * - Admin/Management: Can view all tickets
     * - User: Can only view their own tickets or tickets assigned to them
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Super admin, admin, and management can view all tickets
        if ($user->hasAnyRole(['super-admin', 'admin', 'management'])) {
            return true;
        }

        // Users can view tickets they created or are assigned to
        return $ticket->user_id === $user->id || $ticket->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     * 
     * All authenticated users can create tickets
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create tickets
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Business Rules:
     * - Super Admin: Can update all tickets
     * - Admin/Management: Can update all tickets
     * - User: Can update only their own tickets (before admin assignment)
     * - Assigned tech: Can update status/resolution of assigned tickets
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Super admin, admin, and management can update all tickets
        if ($user->hasAnyRole(['super-admin', 'admin', 'management'])) {
            return true;
        }

        // Creator can update their own tickets
        if ($ticket->user_id === $user->id) {
            return true;
        }

        // Assigned technician can update the ticket
        if ($ticket->assigned_to === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Business Rules:
     * - Only Super Admin can delete tickets
     * - Tickets should typically be closed, not deleted (audit trail)
     */
    public function delete(User $user, ?Ticket $ticket = null): bool
    {
        // Only super-admin can delete tickets
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     * 
     * Only super-admin can restore soft-deleted tickets
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * 
     * Only super-admin can force delete tickets
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can assign tickets to technicians.
     * 
     * Business Rules:
     * - Super Admin: Can assign any ticket
     * - Admin/Management: Can assign any ticket
     * - Users: Can only assign to themselves if they created the ticket
     */
    public function assign(User $user, ?Ticket $ticket = null): bool
    {
        // Super admin, admin, and management can assign any ticket
        if ($user->hasAnyRole(['super-admin', 'admin', 'management'])) {
            return true;
        }

        // Users can assign tickets they created (to themselves)
        if ($ticket && $ticket->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can change ticket priority.
     * 
     * Business Rules:
     * - Super Admin: Can change any priority
     * - Admin/Management: Can change any priority
     * - Users: Can change priority on their own tickets
     */
    public function changePriority(User $user, Ticket $ticket): bool
    {
        // Super admin, admin, and management can change any priority
        if ($user->hasAnyRole(['super-admin', 'admin', 'management'])) {
            return true;
        }

        // Users can change priority on tickets they created or are assigned to
        return $ticket->user_id === $user->id || $ticket->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can resolve/close tickets.
     * 
     * Business Rules:
     * - Super Admin: Can resolve any ticket
     * - Admin/Management: Can resolve any ticket
     * - Assigned tech: Can resolve their assigned tickets
     * - Users: Cannot resolve tickets
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        // Super admin, admin, and management can resolve any ticket
        if ($user->hasAnyRole(['super-admin', 'admin', 'management'])) {
            return true;
        }

        // Assigned technician can resolve their assigned tickets
        return $ticket->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can reopen closed tickets.
     * 
     * Business Rules:
     * - Super Admin: Can reopen any ticket
     * - Admin/Management: Can reopen any ticket
     * - Creator: Can reopen their own tickets
     */
    public function reopen(User $user, Ticket $ticket): bool
    {
        // Super admin, admin, and management can reopen any ticket
        if ($user->hasAnyRole(['super-admin', 'admin', 'management'])) {
            return true;
        }

        // Creator can reopen their own tickets
        return $ticket->user_id === $user->id;
    }
}
