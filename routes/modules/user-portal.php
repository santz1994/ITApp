<?php

/**
 * User Self-Service Portal Routes
 * 
 * Routes for regular users (role: user)
 * Allows users to:
 * - Create and view their own tickets
 * - View their assigned assets
 */

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:user'])->group(function () {
    
    // ========================================
    // USER TICKET ROUTES (UserTicketController)
    // ========================================
    Route::get('/tiket-saya', [\App\Http\Controllers\Tickets\UserTicketController::class, 'userTickets'])->name('tickets.user-index');
    Route::get('/tiket-saya/buat', [\App\Http\Controllers\Tickets\UserTicketController::class, 'userCreate'])->name('tickets.user-create');
    Route::post('/tiket-saya/buat', [\App\Http\Controllers\Tickets\UserTicketController::class, 'userStore'])->name('tickets.user-store');
    Route::get('/tiket-saya/{ticket}', [\App\Http\Controllers\Tickets\UserTicketController::class, 'userShow'])->name('tickets.user-show');
    
    // ========================================
    // USER ASSET ROUTES
    // ========================================
    Route::get('/aset-saya', [\App\Http\Controllers\AssetsController::class, 'userAssets'])->name('assets.user-index');
    Route::get('/aset-saya/{asset}', [\App\Http\Controllers\AssetsController::class, 'userShow'])->name('assets.user-show');
});
