<?php

/**
 * Vehicle Management Routes
 * 
 * Handles vehicle CRUD, booking requests, approvals, and maintenance.
 * Uses permission-based middleware for database-driven RBAC.
 * 
 * Permission tags used:
 * - manage_vehicles: CRUD operations on vehicles (admin)
 * - view_vehicles: View vehicle list (all authenticated)
 * - create_vehicle_booking: Create vehicle bookings
 * - approve_vehicle_booking: Approve/reject vehicle bookings
 * - manage_vehicle_maintenance: Add maintenance logs
 * - view_vehicle_reports: View vehicle usage reports
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;

Route::middleware(['auth'])->group(function () {

    // ========================================
    // VEHICLE LISTING (All authenticated users)
    // ========================================
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show')
        ->where('vehicle', '[0-9]+');

    // ========================================
    // MY BOOKINGS (All authenticated users)
    // ========================================
    Route::get('/vehicle-bookings/my', [VehicleController::class, 'myBookings'])->name('vehicles.my-bookings');

    // ========================================
    // VEHICLE BOOKING (All authenticated users)
    // ========================================
    Route::middleware(['permission:create_vehicle_booking'])->group(function () {
        Route::get('/vehicle-bookings/create', [VehicleController::class, 'createBooking'])->name('vehicles.booking.create');
        Route::post('/vehicle-bookings', [VehicleController::class, 'storeBooking'])->name('vehicles.booking.store');
    });

    // ========================================
    // BOOKING DETAIL (All authenticated users)
    // ========================================
    Route::get('/vehicle-bookings/{id}', [VehicleController::class, 'showBooking'])->name('vehicles.booking.show')
        ->where('id', '[0-9]+');

    // ========================================
    // BOOKING ACTIONS
    // ========================================
    Route::middleware(['permission:approve_vehicle_booking'])->group(function () {
        Route::post('/vehicle-bookings/{id}/approve', [VehicleController::class, 'approveBooking'])->name('vehicles.booking.approve');
        Route::post('/vehicle-bookings/{id}/reject', [VehicleController::class, 'rejectBooking'])->name('vehicles.booking.reject');
    });

    Route::post('/vehicle-bookings/{id}/cancel', [VehicleController::class, 'cancelBooking'])->name('vehicles.booking.cancel');
    Route::post('/vehicle-bookings/{id}/start', [VehicleController::class, 'startTrip'])->name('vehicles.booking.start');
    Route::post('/vehicle-bookings/{id}/complete', [VehicleController::class, 'completeTrip'])->name('vehicles.booking.complete');

    // ========================================
    // VEHICLE MANAGEMENT (Admin only)
    // ========================================
    Route::middleware(['permission:manage_vehicles'])->group(function () {
        Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit')
            ->where('vehicle', '[0-9]+');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update')
            ->where('vehicle', '[0-9]+');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy')
            ->where('vehicle', '[0-9]+');

        // Maintenance
        Route::post('/vehicles/{vehicleId}/maintenance', [VehicleController::class, 'addMaintenance'])->name('vehicles.maintenance.add');
    });

    // ========================================
    // ALL BOOKINGS LISTING (Admin/Manager)
    // ========================================
    Route::middleware(['permission:view_vehicle_reports'])->group(function () {
        Route::get('/vehicle-bookings', [VehicleController::class, 'bookings'])->name('vehicles.bookings');
    });

    // ========================================
    // AJAX ENDPOINTS
    // ========================================
    Route::post('/api/vehicles/check-availability', [VehicleController::class, 'checkAvailability'])->name('vehicles.check-availability');
});