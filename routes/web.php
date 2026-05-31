<?php

/**
 * Main Web Routes
 * 
 * Routes are organized into modular files:
 * - auth.php: Authentication routes
 * - api/web-api.php: AJAX endpoints
 * - modules/admin.php: Admin, system, user & role management
 * - modules/meeting-rooms.php: Meeting room booking
 * - modules/vehicles.php: Vehicle booking
 * - modules/inventory.php: Inventory management
 * - modules/approvals.php: Multi-tier approval workflow
 * - modules/profile.php: User profile
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home');
    }
    return redirect('/login');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/api/web-api.php';
require __DIR__ . '/modules/admin.php';
require __DIR__ . '/modules/meeting-rooms.php';
require __DIR__ . '/modules/vehicles.php';
require __DIR__ . '/modules/inventory.php';
require __DIR__ . '/modules/approvals.php';
require __DIR__ . '/modules/profile.php';

use Illuminate\Support\Str;

// React SPA fallback: serve built React app for non-API routes.
Route::get('/{any}', function () {
    $path = request()->path();
    if (Str::startsWith($path, 'api') || Str::startsWith($path, 'storage') || Str::startsWith($path, 'public')) {
        abort(404);
    }
    return file_get_contents(public_path('react/index.html'));
})->where('any', '.*');
