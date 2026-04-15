<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin|super-admin'])->group(function () {
    // Conflict resolution
    Route::prefix('imports/{importId}')->name('imports.conflicts.')->group(function () {
        // Display conflicts
        Route::get('conflicts', [\App\Http\Controllers\ConflictResolutionController::class, 'index'])
            ->name('index');
        
        // Show specific conflict detail
        Route::get('conflicts/{conflictId}', [\App\Http\Controllers\ConflictResolutionController::class, 'show'])
            ->name('show');
        
        // Resolve single conflict
        Route::post('conflicts/{conflictId}/resolve', [\App\Http\Controllers\ConflictResolutionController::class, 'resolve'])
            ->name('resolve');
        
        // Bulk resolve conflicts
        Route::post('conflicts/bulk-resolve', [\App\Http\Controllers\ConflictResolutionController::class, 'bulkResolve'])
            ->name('bulk-resolve');
        
        // Auto-resolve conflicts
        Route::post('conflicts/auto-resolve', [\App\Http\Controllers\ConflictResolutionController::class, 'autoResolve'])
            ->name('auto-resolve');
        
        // Resolution history
        Route::get('conflicts/history', [\App\Http\Controllers\ConflictResolutionController::class, 'history'])
            ->name('history');
        
        // Export conflict report
        Route::get('conflicts/export', [\App\Http\Controllers\ConflictResolutionController::class, 'exportReport'])
            ->name('export-report');
        
        // Rollback resolutions
        Route::post('conflicts/rollback', [\App\Http\Controllers\ConflictResolutionController::class, 'rollback'])
            ->name('rollback');
    });
});
