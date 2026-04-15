<?php

namespace App\Policies;

use App\User;
use App\DailyActivity;

class DailyActivityPolicy
{
    /**
     * Determine whether the user can update the daily activity.
     * 
     * Business Rules:
     * - Only manual activities can be updated (not automated ones)
     * - Owners can update their own manual activities
     * - Super-admin and Admin can update any manual activity
     */
    public function update(User $user, DailyActivity $dailyActivity)
    {
        // Only manual activities can be updated
        if ($dailyActivity->type !== 'manual') {
            return false;
        }

        // Admins can update any manual activity
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return true;
        }

        // Users can update their own manual activities
        return $dailyActivity->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the daily activity.
     * 
     * Business Rules:
     * - Only manual activities can be deleted (not automated ones)
     * - Owners can delete their own manual activities
     * - Super-admin and Admin can delete any manual activity
     */
    public function delete(User $user, DailyActivity $dailyActivity)
    {
        // Only manual activities can be deleted
        if ($dailyActivity->type !== 'manual') {
            return false;
        }

        // Admins can delete any manual activity
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return true;
        }

        // Users can delete their own manual activities
        return $dailyActivity->user_id === $user->id;
    }
}
