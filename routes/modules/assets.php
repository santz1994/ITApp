<?php

/**
 * Asset Management Routes
 * 
 * All asset-related routes for administrator and developer users
 * Includes: Assets, Maintenance, QR Codes, Spares
 */

use Illuminate\Support\Facades\Route;

// QR Code Routes (Public access for mobile scanning)
Route::get('/assets/qr/{qrCode}', [\App\Http\Controllers\QRCodeController::class, 'showAssetByQR'])->name('assets.qr');

// ========================================
// ASSET READ-ONLY ROUTES (All authenticated users)
// IMPORTANT: Specific string routes BEFORE parameterized routes to avoid routing conflicts
// ========================================
Route::middleware(['auth'])->group(function () {
    // Asset viewing routes - all authenticated users can view assets (read-only)
    Route::get('/assets', [\App\Http\Controllers\AssetsController::class, 'index'])->name('assets.index');
    
    // Asset categories and requests - all users can view (must be before {asset} wildcard)
    Route::get('/assets/categories', [\App\Http\Controllers\InventoryController::class, 'categories'])->name('assets.categories');
    Route::get('/assets/requests', [\App\Http\Controllers\InventoryController::class, 'requests'])->name('assets.requests');
    
    // My Assets - all users can view their assigned assets (must be before {asset} wildcard)
    Route::get('/assets/my-assets', [\App\Http\Controllers\AssetsController::class, 'myAssets'])->name('assets.my-assets');
    
    // QR Scanning - all users can scan QR codes (must be before {asset} wildcard)
    Route::get('/assets/scan-qr', [\App\Http\Controllers\AssetsController::class, 'scanQR'])->name('assets.scan-qr');
    Route::post('/assets/search-by-qr', [\App\Http\Controllers\AssetsController::class, 'searchByQR'])->name('assets.search-by-qr');
    Route::post('/assets/process-scan', [\App\Http\Controllers\AssetsController::class, 'processScan'])->name('assets.process-scan');
    
    // Asset detail routes with wildcard parameter (with constraint to exclude specific keywords)
    Route::get('/assets/{asset}', [\App\Http\Controllers\AssetsController::class, 'show'])->name('assets.show')->where('asset', '^(?!create|export|import-form|import-errors-download|download-template|bulk-qr-codes)[0-9]+$');
    Route::get('/assets/{asset}/history', [\App\Http\Controllers\AssetsController::class, 'history'])->name('assets.history')->where('asset', '[0-9]+');
    Route::get('/assets/{asset}/ticket-history', [\App\Http\Controllers\AssetsController::class, 'show'])->name('assets.ticket-history')->where('asset', '[0-9]+');
    Route::get('/assets/{asset}/movements', [\App\Http\Controllers\AssetsController::class, 'movements'])->name('assets.movements')->where('asset', '[0-9]+');
    
    // QR Code viewing - all authenticated users can view QR codes
    Route::get('/assets/{asset}/qr-code', [\App\Http\Controllers\AssetsController::class, 'generateQR'])->name('assets.qr-code')->where('asset', '[0-9]+');
    Route::get('/assets/{asset}/qr-download', [\App\Http\Controllers\AssetsController::class, 'downloadQR'])->name('assets.qr-download')->where('asset', '[0-9]+');
});

// ========================================
// ASSET WRITE ROUTES (Admin and Super-Admin only)
// ========================================
Route::middleware(['auth', 'role:administrator|developer'])->group(function () {
    
    // ========================================
    // EXPORT/IMPORT/PRINT ROUTES (must be before {asset} wildcard)
    // ========================================
    Route::get('/assets/export', [\App\Http\Controllers\AssetsController::class, 'export'])->name('assets.export');
    Route::get('/assets/import-form', [\App\Http\Controllers\AssetsController::class, 'importForm'])->name('assets.import-form');
    Route::post('/assets/import', [\App\Http\Controllers\AssetsController::class, 'import'])->name('assets.import');
    Route::get('/assets/import-errors-download', [\App\Http\Controllers\AssetsController::class, 'downloadImportErrors'])->name('assets.import-errors-download');
    Route::get('/assets/download-template', [\App\Http\Controllers\AssetsController::class, 'downloadTemplate'])->name('assets.download-template');
    
    // ========================================
    // ASSET CRUD ROUTES (Write operations)
    // ========================================
    Route::get('/assets/create', [\App\Http\Controllers\AssetsController::class, 'create'])->name('assets.create');
    Route::post('/assets', [\App\Http\Controllers\AssetsController::class, 'store'])->name('assets.store');
    
    // Bulk operations (must be before {asset} wildcard)
    Route::post('/assets/bulk-qr-codes', [\App\Http\Controllers\QRCodeController::class, 'bulkGenerateQRCodes'])->name('assets.bulk-qr-codes');
    
    // Asset-specific routes with {asset} parameter (only match numeric IDs)
    Route::get('/assets/{asset}/edit', [\App\Http\Controllers\AssetsController::class, 'edit'])->name('assets.edit')->where('asset', '[0-9]+');
    Route::put('/assets/{asset}', [\App\Http\Controllers\AssetsController::class, 'update'])->name('assets.update')->where('asset', '[0-9]+');
    Route::patch('/assets/{asset}', [\App\Http\Controllers\AssetsController::class, 'update'])->where('asset', '[0-9]+');
    Route::delete('/assets/{asset}', [\App\Http\Controllers\AssetsController::class, 'destroy'])->name('assets.destroy')->where('asset', '[0-9]+');
    Route::get('/assets/{asset}/print', [\App\Http\Controllers\AssetsController::class, 'print'])->name('assets.print')->where('asset', '[0-9]+');
    
    // Asset assignment and movement
    Route::get('/assets/{asset}/move', [\App\Http\Controllers\AssetsController::class, 'movements'])->name('assets.move')->where('asset', '[0-9]+');
    Route::post('/assets/{asset}/assign', [\App\Http\Controllers\AssetsController::class, 'assign'])->name('assets.assign')->where('asset', '[0-9]+');
    Route::post('/assets/{asset}/unassign', [\App\Http\Controllers\AssetsController::class, 'unassign'])->name('assets.unassign')->where('asset', '[0-9]+');
    Route::post('/assets/{asset}/update-condition', [\App\Http\Controllers\AssetsController::class, 'updateCondition'])->name('assets.update-condition')->where('asset', '[0-9]+');
    Route::post('/assets/my-assets/update-all-conditions', [\App\Http\Controllers\AssetsController::class, 'updateAllConditions'])->name('assets.update-all-conditions');
    Route::post('/assets/{asset}/change-status', [\App\Http\Controllers\InventoryController::class, 'changeStatus'])->name('assets.change-status')->where('asset', '[0-9]+');


    // ========================================
    // ASSET MAINTENANCE LOGS
    // ========================================
    Route::resource('maintenance', \App\Http\Controllers\AssetMaintenanceLogController::class)->names([
        'index' => 'maintenance.index',
        'create' => 'maintenance.create',
        'store' => 'maintenance.store',
        'show' => 'maintenance.show',
        'edit' => 'maintenance.edit',
        'update' => 'maintenance.update',
        'destroy' => 'maintenance.destroy'
    ]);
    Route::get('/maintenance/asset/{asset}', [\App\Http\Controllers\AssetMaintenanceLogController::class, 'getByAsset'])->name('maintenance.by-asset');
    
    // ========================================
    // ASSET MAINTENANCE (Legacy)
    // ========================================
    Route::get('/asset-maintenance', [\App\Http\Controllers\AssetMaintenanceController::class, 'index'])->name('asset-maintenance.index');
    Route::get('/asset-maintenance/analytics', [\App\Http\Controllers\AssetMaintenanceController::class, 'analytics'])->name('asset-maintenance.analytics');
    Route::get('/asset-maintenance/{asset}', [\App\Http\Controllers\AssetMaintenanceController::class, 'show'])->name('asset-maintenance.show');

    // ========================================
    // SPARES MANAGEMENT
    // ========================================
    Route::get('/spares', [\App\Http\Controllers\SparesController::class, 'index'])->name('spares.index');
    Route::get('/spares/create', [\App\Http\Controllers\SparesController::class, 'create'])->name('spares.create');
    Route::post('/spares', [\App\Http\Controllers\SparesController::class, 'store'])->name('spares.store');
    Route::get('/spares/{spare}', [\App\Http\Controllers\SparesController::class, 'show'])->name('spares.show');
    Route::get('/spares/{spare}/edit', [\App\Http\Controllers\SparesController::class, 'edit'])->name('spares.edit');
    Route::put('/spares/{spare}', [\App\Http\Controllers\SparesController::class, 'update'])->name('spares.update');
    Route::delete('/spares/{spare}', [\App\Http\Controllers\SparesController::class, 'destroy'])->name('spares.destroy');
    
    // ========================================
    // FILE ATTACHMENTS
    // ========================================
    Route::post('/attachments/upload', [\App\Http\Controllers\AttachmentController::class, 'upload'])->name('attachments.upload');
    Route::post('/attachments/bulk-upload', [\App\Http\Controllers\AttachmentController::class, 'bulkUpload'])->name('attachments.bulk-upload');
    Route::get('/attachments', [\App\Http\Controllers\AttachmentController::class, 'index'])->name('attachments.index');
    Route::get('/attachments/{id}/download', [\App\Http\Controllers\AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{id}', [\App\Http\Controllers\AttachmentController::class, 'destroy'])->name('attachments.destroy');
});
