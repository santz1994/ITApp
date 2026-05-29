<?php

/**
 * Main Web Routes
 * 
 * This file serves as the entry point for all web routes.
 * Routes are organized into modular files for better maintainability:
 * 
 * - auth.php: Authentication routes (login, logout, password reset)
 * - api/web-api.php: AJAX endpoints (search, validation, SLA)
 * - modules/tickets.php: Ticket management routes
 * - modules/assets.php: Asset management routes
 * - modules/admin.php: Admin & super-admin routes
 * - modules/user-portal.php: User self-service portal
 * - debug.php: Debug/test routes (local environment only)
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ========================================
// LEGACY ROUTE LOADING
// ========================================
// Bridge for legacy apps: if the old app/Http/routes.php exists, load it
$legacy = base_path('app/Http/routes.php');
if (file_exists($legacy)) {
    require $legacy;
} else {
    // Fallback: Define a minimal home route for tests
    Route::get('/', function () {
        if (Auth::check()) {
            // Always redirect authenticated users to the main portal.
            return redirect('/home');
        }
        return redirect('/login');
    });
    
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index')->middleware('auth');
}

// ========================================
// AUTHENTICATION ROUTES
// ========================================
require __DIR__ . '/auth.php';

// ========================================
// WEB API ROUTES (AJAX endpoints)
// ========================================
require __DIR__ . '/api/web-api.php';

// ========================================
// MODULE ROUTES
// ========================================
require __DIR__ . '/modules/admin.php';
require __DIR__ . '/modules/meeting-rooms.php';
require __DIR__ . '/modules/profile.php';

// ========================================
// DEBUG/TEST ROUTES (Local environment only)
// ========================================
if (app()->environment('local')) {
    require __DIR__ . '/debug.php';
    require __DIR__ . '/debug-assets.php';
}