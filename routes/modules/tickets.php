<?php

/**
 * Ticket Management Routes
 * 
 * All ticket-related routes for admin and super-admin users
 * Controllers refactored into specialized classes:
 * - TicketController: Core CRUD operations
 * - TicketAssignmentController: Assignment operations
 * - TicketStatusController: Status management
 * - TicketTimerController: Time tracking
 * - UserTicketController: User portal (see user-portal.php)
 */

use Illuminate\Support\Facades\Route;

// ========================================
// ADMIN/SUPER-ADMIN ROUTES - Define FIRST
// IMPORTANT: Specific string routes BEFORE parameterized routes to avoid routing conflicts
// ========================================
Route::middleware(['web', 'auth', 'role:admin|super-admin'])->group(function () {
    
    // SPECIFIC STRING ROUTES - Must come BEFORE /tickets/{ticket}
    Route::get('/tickets/unassigned', [\App\Http\Controllers\TicketController::class, 'unassigned'])->name('tickets.unassigned');
    Route::get('/tickets/overdue', [\App\Http\Controllers\TicketController::class, 'overdue'])->name('tickets.overdue');
    Route::get('/tickets/export', [\App\Http\Controllers\TicketController::class, 'export'])->name('tickets.export');
    
    // PARAMETERIZED ROUTES - Comes after specific routes (only match numeric IDs)
    Route::delete('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'destroy'])->name('tickets.destroy')->where('ticket', '[0-9]+');
    Route::get('/tickets/{ticket}/print', [\App\Http\Controllers\TicketController::class, 'print'])->name('tickets.print')->where('ticket', '[0-9]+');
    
    // ASSIGNMENT ROUTES
    Route::post('/tickets/{ticket}/self-assign', [\App\Http\Controllers\Tickets\TicketAssignmentController::class, 'selfAssign'])->name('tickets.self-assign')->where('ticket', '[0-9]+');
    Route::post('/tickets/{ticket}/assign', [\App\Http\Controllers\Tickets\TicketAssignmentController::class, 'assign'])->name('tickets.assign')->where('ticket', '[0-9]+');
    Route::post('/tickets/{ticket}/force-assign', [\App\Http\Controllers\Tickets\TicketAssignmentController::class, 'forceAssign'])->name('tickets.force-assign')->where('ticket', '[0-9]+');
    
    // STATUS MANAGEMENT ROUTES
    Route::post('/tickets/{ticket}/complete', [\App\Http\Controllers\Tickets\TicketStatusController::class, 'complete'])->name('tickets.complete')->where('ticket', '[0-9]+');
    Route::post('/tickets/{ticket}/update-status', [\App\Http\Controllers\Tickets\TicketStatusController::class, 'updateStatus'])->name('tickets.update-status')->where('ticket', '[0-9]+');
    Route::post('/tickets/{ticket}/complete-with-resolution', [\App\Http\Controllers\Tickets\TicketStatusController::class, 'completeWithResolution'])->name('tickets.complete-with-resolution')->where('ticket', '[0-9]+');
    
    // RESOLVE/UNRESOLVE ROUTES (for assigned agents and super-admin)
    Route::patch('/tickets/{ticket}/resolve', [\App\Http\Controllers\TicketController::class, 'resolve'])->name('tickets.resolve')->where('ticket', '[0-9]+');
    Route::patch('/tickets/{ticket}/unresolve', [\App\Http\Controllers\TicketController::class, 'unresolve'])->name('tickets.unresolve')->where('ticket', '[0-9]+');
    
    // USER INTERACTION ROUTES
    Route::post('/tickets/{ticket}/add-response', [\App\Http\Controllers\Tickets\UserTicketController::class, 'addResponse'])->name('tickets.add-response')->where('ticket', '[0-9]+');
    
    // TIME TRACKING ROUTES
    Route::post('/tickets/{ticket}/start-timer', [\App\Http\Controllers\Tickets\TicketTimerController::class, 'startTimer'])->name('tickets.start-timer')->where('ticket', '[0-9]+');
    Route::post('/tickets/{ticket}/stop-timer', [\App\Http\Controllers\Tickets\TicketTimerController::class, 'stopTimer'])->name('tickets.stop-timer')->where('ticket', '[0-9]+');
    Route::get('/tickets/{ticket}/timer-status', [\App\Http\Controllers\Tickets\TicketTimerController::class, 'getTimerStatus'])->name('tickets.timer-status')->where('ticket', '[0-9]+');
    Route::get('/tickets/{ticket}/work-summary', [\App\Http\Controllers\Tickets\TicketTimerController::class, 'getWorkSummary'])->name('tickets.work-summary')->where('ticket', '[0-9]+');

    // BULK DELETE - Admin only
    Route::post('/tickets/bulk/delete', [\App\Http\Controllers\BulkOperationController::class, 'bulkDelete'])->name('tickets.bulk.delete');
    
    // SLA LEARNING SYSTEM DASHBOARD
    Route::get('/sla-learning', [\App\Http\Controllers\SLADashboardController::class, 'index'])->name('sla.learning.dashboard');
});

// ========================================
// AUTHENTICATED USER ROUTES
// ========================================
Route::middleware(['web', 'auth'])->group(function () {
    // Specific routes first
    Route::get('/tickets/create', [\App\Http\Controllers\TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/bulk/options', [\App\Http\Controllers\BulkOperationController::class, 'getBulkOptions'])->name('tickets.bulk.options');
    Route::post('/tickets', [\App\Http\Controllers\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'index'])->name('tickets.index');
    
    // BULK OPERATIONS - Available to authenticated users with controller-level authorization
    Route::post('/tickets/bulk/assign', [\App\Http\Controllers\BulkOperationController::class, 'bulkAssign'])->name('tickets.bulk.assign');
    Route::post('/tickets/bulk/update-status', [\App\Http\Controllers\BulkOperationController::class, 'bulkUpdateStatus'])->name('tickets.bulk.update-status');
    Route::post('/tickets/bulk/update-priority', [\App\Http\Controllers\BulkOperationController::class, 'bulkUpdatePriority'])->name('tickets.bulk.update-priority');
    Route::post('/tickets/bulk/update-category', [\App\Http\Controllers\BulkOperationController::class, 'bulkUpdateCategory'])->name('tickets.bulk.update-category');
    
    // Parameterized routes after specific ones (with constraint to exclude 'bulk', 'create', 'export', 'unassigned', 'overdue')
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show')->where('ticket', '^(?!bulk|create|export|unassigned|overdue)[0-9]+$');
    Route::get('/tickets/{ticket}/edit', [\App\Http\Controllers\TicketController::class, 'edit'])->name('tickets.edit')->where('ticket', '^(?!bulk)[0-9]+$');
    Route::put('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'update'])->name('tickets.update')->where('ticket', '^(?!bulk)[0-9]+$');
    Route::patch('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'update'])->where('ticket', '^(?!bulk)[0-9]+$');
});

// ========================================
// ENHANCED TICKET CREATION (Multi-role access)
// ========================================
Route::middleware(['web', 'auth', 'role:management|admin|super-admin'])->group(function () {
    Route::get('/tickets/create-with-asset', [\App\Http\Controllers\TicketController::class, 'createWithAsset'])->name('tickets.create-with-asset');
});
