<?php

/**
 * Web API Routes
 * 
 * AJAX endpoints for search, validation, and SLA checks
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
    // VALIDATION API (AJAX form validation)
    // ========================================
    Route::get('/api/validate/asset-tag', [\App\Http\Controllers\ValidationController::class, 'validateAssetTag'])->name('api.validate.asset-tag');
    Route::get('/api/validate/serial-number', [\App\Http\Controllers\ValidationController::class, 'validateSerialNumber'])->name('api.validate.serial-number');
    Route::get('/api/validate/email', [\App\Http\Controllers\ValidationController::class, 'validateEmail'])->name('api.validate.email');
    Route::get('/api/validate/ip-address', [\App\Http\Controllers\ValidationController::class, 'validateIpAddress'])->name('api.validate.ip-address');
    Route::get('/api/validate/mac-address', [\App\Http\Controllers\ValidationController::class, 'validateMacAddress'])->name('api.validate.mac-address');
    Route::post('/api/validate/batch', [\App\Http\Controllers\ValidationController::class, 'validateBatch'])->name('api.validate.batch');
    
    // ========================================
    // SLA API (AJAX SLA status checks)
    // ========================================
    Route::get('/api/sla/ticket/{ticket}/status', [\App\Http\Controllers\SlaController::class, 'getTicketSlaStatus'])->name('api.sla.ticket.status');
    Route::get('/api/sla/ticket/{ticket}/breach', [\App\Http\Controllers\SlaController::class, 'checkBreach'])->name('api.sla.ticket.breach');
    Route::get('/api/sla/metrics', [\App\Http\Controllers\SlaController::class, 'getMetrics'])->name('api.sla.metrics');
    
    // ========================================
    // AUDIT LOGS API
    // ========================================
    Route::get('/api/audit-logs/model', [\App\Http\Controllers\AuditLogController::class, 'getModelLogs'])->name('api.audit-logs.model');
    Route::get('/api/audit-logs/my-logs', [\App\Http\Controllers\AuditLogController::class, 'getMyLogs'])->name('api.audit-logs.my-logs');
    Route::get('/api/audit-logs/statistics', [\App\Http\Controllers\AuditLogController::class, 'getStatistics'])->name('api.audit-logs.statistics');
});
