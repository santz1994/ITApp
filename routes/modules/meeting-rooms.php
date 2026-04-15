<?php

/**
 * Meeting Room Booking Routes
 * 
 * Handles meeting room booking requests, approvals, and printing.
 * 
 * Features:
 * - Users can create booking requests
 * - Directors can approve/reject requests
 * - Receptionists can view and print bookings
 * - Conflict detection prevents double-booking
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeetingRoomBookingController;

// ========================================
// PUBLIC ROUTE - LCD DASHBOARD (No Auth)
// ========================================
// LCD Dashboard for displaying real-time booking schedule
// Can be accessed via specific IP or public URL for display purposes
Route::get('meeting-room-lcd-dashboard', [MeetingRoomBookingController::class, 'lcdDashboard'])
    ->name('meeting-room-bookings.lcd-dashboard');

// LCD Dashboard 2 - Display for 5 meeting rooms
Route::get('meeting-room-lcd-dashboard2', [MeetingRoomBookingController::class, 'lcdDashboard2'])
    ->name('meeting-room-bookings.lcd-dashboard2');

Route::middleware(['auth'])->group(function () {

    // ========================================
    // LCD DASHBOARD SETTINGS (Receptionist/Admin)
    // ========================================

    // LCD room list and display order settings page
    Route::get('meeting-room-lcd-settings', [MeetingRoomBookingController::class, 'lcdSettings'])
        ->name('meeting-room-bookings.lcd-settings')
        ->middleware('role:receptionist|admin|super-admin');

    // Save LCD room list and display order settings
    Route::post('meeting-room-lcd-settings', [MeetingRoomBookingController::class, 'saveLcdSettings'])
        ->name('meeting-room-bookings.lcd-settings.save')
        ->middleware('role:receptionist|admin|super-admin');
    
    // ========================================
    // MEETING ROOM BOOKING RESOURCE ROUTES
    // ========================================
    Route::resource('meeting-room-bookings', MeetingRoomBookingController::class)
        ->names([
            'index' => 'meeting-room-bookings.index',
            'create' => 'meeting-room-bookings.create',
            'store' => 'meeting-room-bookings.store',
            'show' => 'meeting-room-bookings.show',
            'edit' => 'meeting-room-bookings.edit',
            'update' => 'meeting-room-bookings.update',
            'destroy' => 'meeting-room-bookings.destroy',
        ]);
    
    // ========================================
    // CUSTOM ACTIONS
    // ========================================
    
    // Approve booking (Director/Admin only)
    Route::post('meeting-room-bookings/{id}/approve', [MeetingRoomBookingController::class, 'approve'])
        ->name('meeting-room-bookings.approve')
        ->middleware('role:director|admin|super-admin');
    
    // Reject booking (Director/Admin only)
    Route::post('meeting-room-bookings/{id}/reject', [MeetingRoomBookingController::class, 'reject'])
        ->name('meeting-room-bookings.reject')
        ->middleware('role:director|admin|super-admin');
    
    // Cancel booking (Receptionist/Admin only)
    Route::post('meeting-room-bookings/{id}/cancel', [MeetingRoomBookingController::class, 'cancel'])
        ->name('meeting-room-bookings.cancel')
        ->middleware('role:receptionist|admin|super-admin');
    
    // Finish booking (Receptionist/Admin only)
    Route::post('meeting-room-bookings/{id}/finish', [MeetingRoomBookingController::class, 'finish'])
        ->name('meeting-room-bookings.finish')
        ->middleware('role:receptionist|admin|super-admin');
    
    // Extend meeting time (User/Receptionist/Admin)
    Route::post('meeting-room-bookings/{id}/extend', [MeetingRoomBookingController::class, 'extendTime'])
        ->name('meeting-room-bookings.extend')
        ->middleware('auth');
    
    // Quick edit meeting subject (Receptionist/Admin only)
    Route::put('meeting-room-bookings/{id}/quick-edit-subject', [MeetingRoomBookingController::class, 'quickEditSubject'])
        ->name('meeting-room-bookings.quick-edit-subject')
        ->middleware('role:receptionist|admin|super-admin');
    
    // Quick edit meeting time (Receptionist/Admin only)
    Route::put('meeting-room-bookings/{id}/quick-edit-time', [MeetingRoomBookingController::class, 'quickEditTime'])
        ->name('meeting-room-bookings.quick-edit-time')
        ->middleware('role:receptionist|admin|super-admin');
    
    // ========================================
    // DIRECTOR DASHBOARD
    // ========================================
    
    // Director Dashboard (Director/Management/Admin only)
    Route::get('meeting-room-director-dashboard', [MeetingRoomBookingController::class, 'directorDashboard'])
        ->name('meeting-room-bookings.director-dashboard')
        ->middleware('role:director|management|admin|super-admin');
    
    // ========================================
    // RECEPTIONIST DASHBOARD
    // ========================================
    
    // Receptionist Dashboard (Receptionist/Admin only)
    Route::get('meeting-room-receptionist-dashboard', [MeetingRoomBookingController::class, 'receptionistDashboard'])
        ->name('meeting-room-bookings.receptionist-dashboard')
        ->middleware('role:receptionist|admin|super-admin');
    
    // Toggle Room Availability (AJAX)
    Route::post('meeting-room-bookings/toggle-availability', [MeetingRoomBookingController::class, 'toggleRoomAvailability'])
        ->name('meeting-room-bookings.toggle-availability')
        ->middleware('role:receptionist|admin|super-admin');
    
    // Quick Booking from Dashboard (AJAX)
    Route::post('meeting-room-bookings/quick-booking', [MeetingRoomBookingController::class, 'quickBooking'])
        ->name('meeting-room-bookings.quick-booking')
        ->middleware('role:receptionist|admin|super-admin');
    
    // Update Booking Time (Drag & Drop) (AJAX)
    Route::put('meeting-room-bookings/{id}/update-time', [MeetingRoomBookingController::class, 'updateBookingTime'])
        ->name('meeting-room-bookings.update-time')
        ->middleware('role:receptionist|admin|super-admin');
    
    // ========================================
    // CALENDAR & DISPLAY VIEWS
    // ========================================
    
    // Calendar view
    Route::get('meeting-room-bookings-calendar', [MeetingRoomBookingController::class, 'calendar'])
        ->name('meeting-room-bookings.calendar');
    
    // Calendar data (JSON for FullCalendar)
    Route::get('meeting-room-bookings-calendar/data', [MeetingRoomBookingController::class, 'calendarData'])
        ->name('meeting-room-bookings.calendar.data');
    
    // Print booking (Receptionist/Admin/Owner)
    Route::get('meeting-room-bookings/{id}/print', [MeetingRoomBookingController::class, 'print'])
        ->name('meeting-room-bookings.print');
    
    // ========================================
    // REPORTS
    // ========================================
    
    // Monthly Report - Excel Export (Receptionist/Admin only)
    Route::get('meeting-room-bookings/report/monthly-excel', [MeetingRoomBookingController::class, 'monthlyExcelReport'])
        ->name('meeting-room-bookings.report.monthly-excel')
        ->middleware('role:receptionist|admin|super-admin');
});
