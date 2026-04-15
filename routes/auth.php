<?php

/**
 * Authentication Routes
 * 
 * This file contains all authentication-related routes:
 * - Login/Logout
 * - Password Reset
 * - Session Management
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Login Routes - Rate limited to prevent brute force attacks
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    });
});

// Logout Route
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Password Reset Routes - Rate limited to prevent abuse
Route::middleware(['throttle:20,1'])->group(function () {
    Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

    // Redirect GET requests to /password/email back to the forgot password form (prevents 405 error)
    Route::get('/password/email', function() {
        return redirect()->route('password.request')->with('info', 'Please use the form below to request a password reset link.');
    });

    Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

// Session Extension Route for AJAX requests
Route::post('/extend-session', function (\Illuminate\Http\Request $request) {
    if ($request->ajax() && Auth::check()) {
        $request->session()->put('last_activity', time());
        return response()->json(['status' => 'success', 'message' => 'Session extended']);
    }
    return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
})->middleware('auth')->name('extend-session');

// Force re-login route to clear session (Local env only for debugging)
if (app()->environment('local')) {
    Route::get('/force-relogin', function() {
        Auth::logout();
        session()->flush();
        session()->regenerate();
        return redirect('/login')->with('message', '<strong>Session cleared!</strong> Please login again to refresh your roles.');
    });
}

// Registration routes (web) - Rate limited to prevent spam
Route::middleware(['throttle:20,1'])->group(function () {
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = new \App\User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password']; // User model hashes password via mutator
        $user->is_active = true;
        $user->save();

        // Assign default role if Spatie roles exist
        try {
            if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
                $user->assignRole('user');
            }
        } catch (\Exception $e) {
            // ignore if permission tables not available
        }

        Auth::login($user);

        return redirect()->intended('/home');
    });
});
