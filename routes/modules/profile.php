<?php

/**
 * User Profile Routes
 * 
 * Handles user profile management:
 * - Edit profile information
 * - Change password
 * - Change profile picture
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::middleware(['auth'])->group(function () {
    
    // Profile Edit
    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    
    Route::put('profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    
    // Change Password
    Route::get('profile/change-password', [ProfileController::class, 'editPassword'])
        ->name('profile.edit-password');
    
    Route::put('profile/change-password', [ProfileController::class, 'updatePassword'])
        ->name('profile.update-password');
    
    // Change Profile Picture
    Route::get('profile/change-picture', [ProfileController::class, 'editPicture'])
        ->name('profile.edit-picture');
    
    Route::post('profile/change-picture', [ProfileController::class, 'updatePicture'])
        ->name('profile.update-picture');
    
    Route::delete('profile/delete-picture', [ProfileController::class, 'deletePicture'])
        ->name('profile.delete-picture');
    
    // Notification Preferences
    Route::get('profile/notifications', [ProfileController::class, 'editNotifications'])
        ->name('profile.edit-notifications');
    
    Route::put('profile/notifications', [ProfileController::class, 'updateNotifications'])
        ->name('profile.update-notifications');
});
