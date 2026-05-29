<?php

/**
 * Inventory Management Routes
 * 
 * Handles ATK & Sparepart inventory management, stock movements, and requests.
 * Uses permission-based middleware for database-driven RBAC.
 * 
 * Permission tags used:
 * - view_inventory: View inventory items (all authenticated)
 * - manage_inventory: CRUD operations on inventory items (admin)
 * - manage_stock: Add/reduce stock
 * - create_inventory_request: Create inventory requests
 * - approve_inventory_request: Approve/reject inventory requests
 * - fulfill_inventory_request: Issue/fulfill inventory requests
 * - view_inventory_reports: View inventory reports
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;

Route::middleware(['auth'])->group(function () {

    // ========================================
    // INVENTORY ITEMS (All authenticated users - read)
    // ========================================
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{item}', [InventoryController::class, 'show'])->name('inventory.show')
        ->where('item', '[0-9]+');

    // ========================================
    // LOW STOCK ALERT (All authenticated users)
    // ========================================
    Route::get('/inventory-alerts/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');

    // ========================================
    // MY REQUESTS (All authenticated users)
    // ========================================
    Route::get('/inventory-requests', [InventoryController::class, 'requests'])->name('inventory.requests');
    Route::get('/inventory-requests/{id}', [InventoryController::class, 'showRequest'])->name('inventory.request.show')
        ->where('id', '[0-9]+');

    // ========================================
    // CREATE REQUEST (All authenticated users with permission)
    // ========================================
    Route::middleware(['permission:create_inventory_request'])->group(function () {
        Route::get('/inventory-requests/create', [InventoryController::class, 'createRequest'])->name('inventory.request.create');
        Route::post('/inventory-requests', [InventoryController::class, 'storeRequest'])->name('inventory.request.store');
        Route::post('/inventory-requests/{id}/cancel', [InventoryController::class, 'cancelRequest'])->name('inventory.request.cancel');
    });

    // ========================================
    // APPROVE/REJECT REQUESTS (Admin/Manager)
    // ========================================
    Route::middleware(['permission:approve_inventory_request'])->group(function () {
        Route::post('/inventory-requests/{id}/approve', [InventoryController::class, 'approveRequest'])->name('inventory.request.approve');
        Route::post('/inventory-requests/{id}/reject', [InventoryController::class, 'rejectRequest'])->name('inventory.request.reject');
    });

    // ========================================
    // FULFILL REQUESTS (Admin/GA)
    // ========================================
    Route::middleware(['permission:fulfill_inventory_request'])->group(function () {
        Route::post('/inventory-requests/{id}/fulfill', [InventoryController::class, 'fulfillRequest'])->name('inventory.request.fulfill');
    });

    // ========================================
    // INVENTORY ITEMS CRUD (Admin only)
    // ========================================
    Route::middleware(['permission:manage_inventory'])->group(function () {
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit')
            ->where('item', '[0-9]+');
        Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update')
            ->where('item', '[0-9]+');
        Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy')
            ->where('item', '[0-9]+');
    });

    // ========================================
    // STOCK MANAGEMENT (Admin only)
    // ========================================
    Route::middleware(['permission:manage_stock'])->group(function () {
        Route::post('/inventory/{item}/add-stock', [InventoryController::class, 'addStock'])->name('inventory.add-stock')
            ->where('item', '[0-9]+');
        Route::post('/inventory/{item}/reduce-stock', [InventoryController::class, 'reduceStock'])->name('inventory.reduce-stock')
            ->where('item', '[0-9]+');
    });
});