<?php

/**
 * API Routes for Modules
 * 
 * Vehicle Management, Inventory Management, Approval Workflow
 * All routes are prefixed with /api/v1 and require Sanctum auth
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VehicleApiController;
use App\Http\Controllers\API\InventoryApiController;
use App\Http\Controllers\API\ApprovalApiController;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // ========================================
    // VEHICLE MANAGEMENT API
    // ========================================
    Route::prefix('vehicles')->group(function () {
        // Vehicle CRUD
        Route::get('/', [VehicleApiController::class, 'index'])->middleware('permission:view_vehicles');
        Route::post('/', [VehicleApiController::class, 'store'])->middleware('permission:manage_vehicles');
        Route::get('/{id}', [VehicleApiController::class, 'show'])->middleware('permission:view_vehicles');
        Route::put('/{id}', [VehicleApiController::class, 'update'])->middleware('permission:manage_vehicles');
        Route::delete('/{id}', [VehicleApiController::class, 'destroy'])->middleware('permission:manage_vehicles');

        // Availability check
        Route::post('/check-availability', [VehicleApiController::class, 'checkAvailability']);

        // Booking management
        Route::get('/bookings/all', [VehicleApiController::class, 'bookings'])->middleware('permission:view_vehicle_reports');
        Route::get('/bookings/my', [VehicleApiController::class, 'bookings'])->middleware('permission:create_vehicle_booking');
        Route::post('/bookings', [VehicleApiController::class, 'storeBooking'])->middleware('permission:create_vehicle_booking');
        Route::get('/bookings/{id}', [VehicleApiController::class, 'showBooking']);
        Route::post('/bookings/{id}/approve', [VehicleApiController::class, 'approveBooking'])->middleware('permission:approve_vehicle_booking');
        Route::post('/bookings/{id}/reject', [VehicleApiController::class, 'rejectBooking'])->middleware('permission:approve_vehicle_booking');
        Route::post('/bookings/{id}/cancel', [VehicleApiController::class, 'cancelBooking']);
        Route::post('/bookings/{id}/start', [VehicleApiController::class, 'startTrip']);
        Route::post('/bookings/{id}/complete', [VehicleApiController::class, 'completeTrip']);

        // Maintenance
        Route::get('/{id}/maintenance', [VehicleApiController::class, 'maintenanceLogs']);
        Route::post('/{id}/maintenance', [VehicleApiController::class, 'addMaintenance'])->middleware('permission:manage_vehicle_maintenance');
    });

    // ========================================
    // INVENTORY MANAGEMENT API
    // ========================================
    Route::prefix('inventory')->group(function () {
        // Items CRUD
        Route::get('/', [InventoryApiController::class, 'index'])->middleware('permission:view_inventory');
        Route::post('/', [InventoryApiController::class, 'store'])->middleware('permission:manage_inventory');
        Route::get('/low-stock', [InventoryApiController::class, 'lowStock']);
        Route::get('/categories', [InventoryApiController::class, 'categories']);
        Route::get('/{id}', [InventoryApiController::class, 'show'])->middleware('permission:view_inventory');
        Route::put('/{id}', [InventoryApiController::class, 'update'])->middleware('permission:manage_inventory');
        Route::delete('/{id}', [InventoryApiController::class, 'destroy'])->middleware('permission:manage_inventory');

        // Stock management
        Route::post('/{id}/add-stock', [InventoryApiController::class, 'addStock'])->middleware('permission:manage_stock');
        Route::post('/{id}/reduce-stock', [InventoryApiController::class, 'reduceStock'])->middleware('permission:manage_stock');

        // Inventory requests
        Route::get('/requests/all', [InventoryApiController::class, 'requests']);
        Route::post('/requests', [InventoryApiController::class, 'storeRequest'])->middleware('permission:create_inventory_request');
        Route::get('/requests/{id}', [InventoryApiController::class, 'showRequest']);
        Route::post('/requests/{id}/approve', [InventoryApiController::class, 'approveRequest'])->middleware('permission:approve_inventory_request');
        Route::post('/requests/{id}/reject', [InventoryApiController::class, 'rejectRequest'])->middleware('permission:approve_inventory_request');
        Route::post('/requests/{id}/fulfill', [InventoryApiController::class, 'fulfillRequest'])->middleware('permission:fulfill_inventory_request');
        Route::post('/requests/{id}/cancel', [InventoryApiController::class, 'cancelRequest']);
    });

    // ========================================
    // APPROVAL WORKFLOW API
    // ========================================
    Route::prefix('approvals')->group(function () {
        Route::get('/pending', [ApprovalApiController::class, 'pendingApprovals']);
        Route::post('/{id}/approve', [ApprovalApiController::class, 'approve'])->middleware('permission:approve_requests');
        Route::post('/{id}/reject', [ApprovalApiController::class, 'reject'])->middleware('permission:approve_requests');
        Route::get('/{id}', [ApprovalApiController::class, 'show']);

        // Admin: Rule management
        Route::get('/rules/all', [ApprovalApiController::class, 'rules'])->middleware('permission:manage_approval_rules');
        Route::post('/rules', [ApprovalApiController::class, 'storeRule'])->middleware('permission:manage_approval_rules');
        Route::put('/rules/{id}', [ApprovalApiController::class, 'updateRule'])->middleware('permission:manage_approval_rules');
        Route::delete('/rules/{id}', [ApprovalApiController::class, 'destroyRule'])->middleware('permission:manage_approval_rules');
        Route::post('/rules/{id}/toggle', [ApprovalApiController::class, 'toggleRule'])->middleware('permission:manage_approval_rules');
    });
});