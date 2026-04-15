<?php

namespace App\Policies;

use App\AssetRequest;
use App\User;
use Illuminate\Auth\Access\Response;

class AssetRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Business Rules:
     * - Super Admin/Admin: Can view all requests
     * - Management: Can view requests from their division
     * - Regular Users: Can view only their own requests
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view asset requests (filtered by controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Business Rules:
     * - Super Admin/Admin: Can view any request
     * - Management: Can view requests from their division
     * - Users: Can view their own requests
     */
    public function view(User $user, AssetRequest $assetRequest): bool
    {
        // Super admin and admin can view any request
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Management can view requests from their division
        if ($user->hasRole('management') && $user->division_id === $assetRequest->division_id) {
            return true;
        }

        // Users can view their own requests
        return $user->id === $assetRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     * 
     * Business Rules:
     * - All authenticated users can create asset requests
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Business Rules:
     * - Users can update only their own pending requests
     * - Admins can update any request
     */
    public function update(User $user, AssetRequest $assetRequest): bool
    {
        // Super admin and admin can update any request
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Users can only update their own pending requests
        return $user->id === $assetRequest->user_id && $assetRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Business Rules:
     * - Users can delete only their own pending requests
     * - Admins can delete any request
     */
    public function delete(User $user, AssetRequest $assetRequest): bool
    {
        // Super admin and admin can delete any request
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Users can only delete their own pending requests
        return $user->id === $assetRequest->user_id && $assetRequest->status === 'pending';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AssetRequest $assetRequest): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AssetRequest $assetRequest): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can approve the asset request.
     * 
     * Business Rules:
     * - Only Super Admin and Admin can approve requests
     * - Request must be in pending status
     */
    public function approve(User $user, AssetRequest $assetRequest): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']) && $assetRequest->status === 'pending';
    }

    /**
     * Determine whether the user can reject the asset request.
     * 
     * Business Rules:
     * - Only Super Admin and Admin can reject requests
     * - Request must be in pending status
     */
    public function reject(User $user, AssetRequest $assetRequest): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']) && $assetRequest->status === 'pending';
    }
}
