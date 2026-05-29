<?php

namespace App\Services;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileService
{
    /**
     * Update profile information.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
        ]);

        return $user;
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Upload and replace profile picture.
     */
    public function updatePicture(User $user, UploadedFile $file): User
    {
        // Delete old picture
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $file->store('profile-pictures', 'public');
        $user->update(['profile_picture' => $path]);

        return $user;
    }

    /**
     * Delete profile picture.
     */
    public function deletePicture(User $user): User
    {
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->update(['profile_picture' => null]);

        return $user;
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(User $user, array $data): User
    {
        $user->update([
            'notify_email' => ($data['notify_email'] ?? '0') === '1',
            'notify_ticket_created' => isset($data['notify_ticket_created']),
            'notify_ticket_assigned' => isset($data['notify_ticket_assigned']),
            'notify_ticket_updated' => isset($data['notify_ticket_updated']),
            'notify_meeting_approved' => isset($data['notify_meeting_approved']),
            'notify_meeting_rejected' => isset($data['notify_meeting_rejected']),
        ]);

        return $user;
    }
}
