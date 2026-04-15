<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the user profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show change password form
     */
    public function editPassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Show change profile picture form
     */
    public function editPicture()
    {
        $user = Auth::user();
        return view('profile.change-picture', compact('user'));
    }

    /**
     * Update profile picture
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'], // 2MB max
        ]);

        $user = Auth::user();

        // Delete old profile picture if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store('profile-pictures', 'public');

        $user->update([
            'profile_picture' => $path,
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Delete profile picture
     */
    public function deletePicture()
    {
        $user = Auth::user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->update([
            'profile_picture' => null,
        ]);

        return redirect()->route('profile.edit-picture')
            ->with('success', 'Profile picture deleted successfully!');
    }

    /**
     * Show notification preferences form
     */
    public function editNotifications()
    {
        $user = Auth::user();
        return view('profile.notifications', compact('user'));
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $user->update([
            'notify_email' => $request->input('notify_email') === '1',
            'notify_ticket_created' => $request->has('notify_ticket_created'),
            'notify_ticket_assigned' => $request->has('notify_ticket_assigned'),
            'notify_ticket_updated' => $request->has('notify_ticket_updated'),
            'notify_meeting_approved' => $request->has('notify_meeting_approved'),
            'notify_meeting_rejected' => $request->has('notify_meeting_rejected'),
        ]);

        return redirect()->route('profile.edit-notifications')
            ->with('success', 'Notification preferences updated successfully!');
    }
}
