<?php

namespace App\Policies;

use App\Asset;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * 
     * All authenticated users can view assets
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view assets
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Business Rules:
     * - Super Admin: Can view all assets
     * - Admin/Management: Can view all assets
     * - User: Can view all assets (read-only access to inventory)
     */
    public function view(User $user, Asset $asset): bool
    {
        return true; // All authenticated users can view individual assets
    }

    /**
     * Determine whether the user can create models.
     * 
     * Business Rules:
     * - Only Super Admin and Admin can create assets
     * - Management and Users cannot create assets
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Business Rules:
     * - Super Admin: Can update all assets
     * - Admin: Can update all assets
     * - Management: Cannot update assets
     * - Users: Cannot update assets
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Business Rules:
     * - Only Super Admin can delete assets
     * - Assets should be marked as decommissioned rather than deleted (audit trail)
     */
    public function delete(User $user, Asset $asset): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     * 
     * Only super-admin can restore soft-deleted assets
     */
    public function restore(User $user, Asset $asset): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * 
     * Only super-admin can force delete assets
     */
    public function forceDelete(User $user, Asset $asset): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can assign assets to users.
     * 
     * Business Rules:
     * - Super Admin: Can assign any asset
     * - Admin: Can assign any asset
     * - Management: Cannot assign assets
     * - Users: Cannot assign assets
     */
    public function assign(User $user, Asset $asset): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can move assets between locations.
     * 
     * Business Rules:
     * - Super Admin: Can move any asset
     * - Admin: Can move any asset
     * - Management: Cannot move assets
     * - Users: Cannot move assets
     */
    public function move(User $user, Asset $asset): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can perform maintenance on assets.
     * 
     * Business Rules:
     * - Super Admin: Can log maintenance for any asset
     * - Admin: Can log maintenance for any asset
     * - Management: Can log maintenance for assets in their division
     * - Users: Can request maintenance (via tickets)
     */
    public function performMaintenance(User $user, Asset $asset): bool
    {
        // Super admin and admin can perform maintenance on any asset
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        // Management can perform maintenance on assets in their division
        if ($user->hasRole('management') && $asset->division_id === $user->division_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can decommission assets.
     * 
     * Business Rules:
     * - Super Admin: Can decommission any asset
     * - Admin: Can decommission any asset
     * - Management: Cannot decommission assets
     * - Users: Cannot decommission assets
     */
    public function decommission(User $user, Asset $asset): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can export asset data.
     * 
     * Business Rules:
     * - Super Admin: Can export all asset data
     * - Admin: Can export all asset data
     * - Management: Can export assets from their division
     * - Users: Cannot export
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'management']);
    }

    /**
     * Determine whether the user can import asset data.
     * 
     * Business Rules:
     * - Only Super Admin and Admin can import assets
     * - Bulk operations require elevated permissions
     */
    public function import(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can view asset QR codes.
     * 
     * All users can view QR codes for asset lookup
     */
    public function viewQRCode(User $user, Asset $asset): bool
    {
        return true;
    }

    /**
     * Determine whether the user can generate QR codes for assets.
     * 
     * Business Rules:
     * - Super Admin: Can generate QR codes
     * - Admin: Can generate QR codes
     * - Management: Cannot generate QR codes
     * - Users: Cannot generate QR codes
     */
    public function generateQRCode(User $user, Asset $asset): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}