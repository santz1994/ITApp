<?php

/**
 * Web API Routes
 * 
 * AJAX endpoints for search, validation, and audit logs
 * All routes require authentication
 */

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    
    // ========================================
    // GLOBAL SEARCH API
    // ========================================
    Route::get('/api/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('api.search');
    Route::get('/api/quick-search', [\App\Http\Controllers\SearchController::class, 'quickSearch'])->name('api.quick-search');
    
    // ========================================
    // AUDIT LOGS API
    // ========================================
    Route::get('/api/audit-logs/model', [\App\Http\Controllers\AuditLogController::class, 'getModelLogs'])->name('api.audit-logs.model');
    Route::get('/api/audit-logs/my-logs', [\App\Http\Controllers\AuditLogController::class, 'getMyLogs'])->name('api.audit-logs.my-logs');
    Route::get('/api/audit-logs/statistics', [\App\Http\Controllers\AuditLogController::class, 'getStatistics'])->name('api.audit-logs.statistics');

    // ========================================
    // PORTAL PREFERENCES API
    // ========================================
    Route::prefix('/api/portal-preferences')->name('api.portal-preferences.')->group(function () {
        Route::get('/', [\App\Http\Controllers\API\PortalPreferenceController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\API\PortalPreferenceController::class, 'store'])->name('store');
        Route::put('/{key}', [\App\Http\Controllers\API\PortalPreferenceController::class, 'update'])->name('update');
        Route::get('/{key}', [\App\Http\Controllers\API\PortalPreferenceController::class, 'show'])->name('show');
        Route::delete('/', [\App\Http\Controllers\API\PortalPreferenceController::class, 'destroy'])->name('destroy');
    });
});