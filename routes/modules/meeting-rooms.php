<?php

/**
 * Meeting Room Booking Routes
 * 
 * Handles meeting room booking requests, approvals, and printing.
 * Uses permission-based middleware for database-driven RBAC.
 * 
 * Permission tags used:
 * - view_meeting_room_lcd: View LCD dashboard (public)
 * - manage_meeting_room_lcd_settings: Configure LCD display settings
 * - create_booking: Create meeting room bookings
 * - view_bookings: View meeting room bookings
 * - approve_booking: Approve/reject meeting room bookings
 * - cancel_booking: Cancel meeting room bookings
 * - finish_booking: Finish/complete meeting room bookings
 * - extend_booking: Extend meeting room booking time
 * - quick_edit_booking: Quick edit booking subject/time
 * - view_director_dashboard: View director dashboard for meeting rooms
 * - view_receptionist_dashboard: View receptionist dashboard
 * - manage_room_availability: Toggle room availability
 * - quick_booking: Create quick bookings from dashboard
 * - update_booking_time: Drag & drop booking time
 * - view_booking_calendar: View booking calendar
 * - print_booking: Print booking details
 * - export_booking_report: Export booking reports
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeetingRoomBookingController;

// ========================================
// PUBLIC ROUTE - LCD DASHBOARD (No Auth)
// ========================================
Route::get('meeting-room-lcd-dashboard', [MeetingRoomBookingController::class, 'lcdDashboard'])
    ->name('meeting-room-bookings.lcd-dashboard');

Route::get('meeting-room-lcd-dashboard2', [MeetingRoomBookingController::class, 'lcdDashboard2'])
    ->name('meeting-room-bookings.lcd-dashboard2');

Route::middleware(['auth'])->group(function () {

    // ========================================
    // LCD DASHBOARD SETTINGS
    // ========================================
    Route::middleware(['permission:manage_meeting_room_lcd_settings'])->group(function () {
        Route::get('meeting-room-lcd-settings', [MeetingRoomBookingController::class, 'lcdSettings'])
            ->name('meeting-room-bookings.lcd-settings');
        Route::post('meeting-room-lcd-settings', [MeetingRoomBookingController::class, 'saveLcdSettings'])
            ->name('meeting-room-bookings.lcd-settings.save');
    });
    
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
    // APPROVAL ACTIONS
    // ========================================
    Route::middleware(['permission:approve_booking'])->group(function () {
        Route::post('meeting-room-bookings/{id}/approve', [MeetingRoomBookingController::class, 'approve'])
            ->name('meeting-room-bookings.approve');
        Route::post('meeting-room-bookings/{id}/reject', [MeetingRoomBookingController::class, 'reject'])
            ->name('meeting-room-bookings.reject');
    });
    
    // ========================================
    // CANCEL & FINISH ACTIONS
    // ========================================
    Route::middleware(['permission:cancel_booking'])->group(function () {
        Route::post('meeting-room-bookings/{id}/cancel', [MeetingRoomBookingController::class, 'cancel'])
            ->name('meeting-room-bookings.cancel');
    });
    
    Route::middleware(['permission:finish_booking'])->group(function () {
        Route::post('meeting-room-bookings/{id}/finish', [MeetingRoomBookingController::class, 'finish'])
            ->name('meeting-room-bookings.finish');
    });
    
    // ========================================
    // EXTEND & QUICK EDIT ACTIONS
    // ========================================
    Route::middleware(['permission:extend_booking'])->group(function () {
        Route::post('meeting-room-bookings/{id}/extend', [MeetingRoomBookingController::class, 'extendTime'])
            ->name('meeting-room-bookings.extend');
    });
    
    Route::middleware(['permission:quick_edit_booking'])->group(function () {
        Route::put('meeting-room-bookings/{id}/quick-edit-subject', [MeetingRoomBookingController::class, 'quickEditSubject'])
            ->name('meeting-room-bookings.quick-edit-subject');
        Route::put('meeting-room-bookings/{id}/quick-edit-time', [MeetingRoomBookingController::class, 'quickEditTime'])
            ->name('meeting-room-bookings.quick-edit-time');
    });
    
    // ========================================
    // DIRECTOR DASHBOARD
    // ========================================
    Route::middleware(['permission:view_director_dashboard'])->group(function () {
        Route::get('meeting-room-director-dashboard', [MeetingRoomBookingController::class, 'directorDashboard'])
            ->name('meeting-room-bookings.director-dashboard');
    });
    
    // ========================================
    // RECEPTIONIST DASHBOARD & ACTIONS
    // ========================================
    Route::middleware(['permission:view_receptionist_dashboard'])->group(function () {
        Route::get('meeting-room-receptionist-dashboard', [MeetingRoomBookingController::class, 'receptionistDashboard'])
            ->name('meeting-room-bookings.receptionist-dashboard');
        Route::post('meeting-room-bookings/toggle-availability', [MeetingRoomBookingController::class, 'toggleRoomAvailability'])
            ->name('meeting-room-bookings.toggle-availability');
        Route::post('meeting-room-bookings/quick-booking', [MeetingRoomBookingController::class, 'quickBooking'])
            ->name('meeting-room-bookings.quick-booking');
        Route::put('meeting-room-bookings/{id}/update-time', [MeetingRoomBookingController::class, 'updateBookingTime'])
            ->name('meeting-room-bookings.update-time');
    });
    
    // ========================================
    // CALENDAR & DISPLAY VIEWS
    // ========================================
    Route::middleware(['permission:view_booking_calendar'])->group(function () {
        Route::get('meeting-room-bookings-calendar', [MeetingRoomBookingController::class, 'calendar'])
            ->name('meeting-room-bookings.calendar');
        Route::get('meeting-room-bookings-calendar/data', [MeetingRoomBookingController::class, 'calendarData'])
            ->name('meeting-room-bookings.calendar.data');
    });
    
    // ========================================
    // PRINT & REPORTS
    // ========================================
    Route::middleware(['permission:print_booking'])->group(function () {
        Route::get('meeting-room-bookings/{id}/print', [MeetingRoomBookingController::class, 'print'])
            ->name('meeting-room-bookings.print');
    });
    
    Route::middleware(['permission:export_booking_report'])->group(function () {
        Route::get('meeting-room-bookings/report/monthly-excel', [MeetingRoomBookingController::class, 'monthlyExcelReport'])
            ->name('meeting-room-bookings.report.monthly-excel');
    });
});