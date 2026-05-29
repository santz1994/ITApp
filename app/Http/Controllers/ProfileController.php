<?php

namespace App\Http\Controllers;

use App\User;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

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

        $this->profileService->updateProfile($user, $validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully!');
    }

    public function editPassword()
    {
        return view('profile.change-password');
    }

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

        $this->profileService->changePassword(Auth::user(), $validated['password']);

        return redirect()->route('profile.edit')
            ->with('success', 'Password changed successfully!');
    }

    public function editPicture()
    {
        $user = Auth::user();
        return view('profile.change-picture', compact('user'));
    }

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        $this->profileService->updatePicture(Auth::user(), $request->file('profile_picture'));

        return redirect()->route('profile.edit')
            ->with('success', 'Profile picture updated successfully!');
    }

    public function deletePicture()
    {
        $this->profileService->deletePicture(Auth::user());

        return redirect()->route('profile.edit-picture')
            ->with('success', 'Profile picture deleted successfully!');
    }

    public function editNotifications()
    {
        $user = Auth::user();
        return view('profile.notifications', compact('user'));
    }

    public function updateNotifications(Request $request)
    {
        $this->profileService->updateNotifications(Auth::user(), $request->all());

        return redirect()->route('profile.edit-notifications')
            ->with('success', 'Notification preferences updated successfully!');
    }
}
