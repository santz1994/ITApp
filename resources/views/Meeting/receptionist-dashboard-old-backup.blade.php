@extends('layouts.app')

@section('title', 'Receptionist Dashboard - Meeting Room Control')

@section('htmlheader')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('css')
<style>
    /* Dashboard Layout */
    .receptionist-dashboard {
        padding: 20px;
        background: #ecf0f5;
    }
    
    /* Room Cards */
    .room-card {
        background: white;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .room-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .room-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #ecf0f5;
    }
    
    .room-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }
    
    /* Status Badge */
    .status-badge {
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
    }
    
    .status-available {
        background: #00a65a;
        color: white;
    }
    
    .status-occupied {
        background: #dd4b39;
        color: white;
    }
    
    .status-unavailable {
        background: #f39c12;
        color: white;
    }
    
    /* Toggle Switch */
    .toggle-container {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 15px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 6px;
    }
    
    .toggle-label {
        font-weight: 600;
        color: #555;
        font-size: 16px;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 30px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #00a65a;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(30px);
    }
    
    /* Current Booking Info */
    .current-booking {
        margin-top: 15px;
        padding: 15px;
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        border-radius: 4px;
    }
    
    .current-booking h5 {
        margin: 0 0 10px 0;
        color: #856404;
        font-weight: bold;
    }
    
    .current-booking p {
        margin: 5px 0;
        color: #856404;
    }
    
    /* Quick Action Buttons */
    .quick-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }
    
    .btn-quick-book {
        background: #3c8dbc;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-quick-book:hover {
        background: #2e6da4;
        transform: scale(1.05);
    }
    
    /* Modal Styling */
    .modal-header {
        background: #3c8dbc;
        color: white;
    }
    
    .modal-header .close {
        color: white;
        opacity: 0.8;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
    
    /* Form Groups */
    .form-group label {
        font-weight: 600;
        color: #555;
    }
    
    .form-control:focus {
        border-color: #3c8dbc;
        box-shadow: 0 0 0 0.2rem rgba(60,141,188,.25);
    }
    
    /* Alert Animations */
    .alert {
        animation: slideDown 0.5s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Loading State */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none !important;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-overlay.show {
        display: flex !important;
    }
    
    .loading-spinner {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
    }
    
    .loading-spinner i {
        font-size: 48px;
        color: #3c8dbc;
    }
</style>
@endsection

@section('main-content')
<div class="receptionist-dashboard">
    
    {{-- Page Header --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-dashboard"></i> Receptionist Control Panel
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#quickBookingModal">
                            <i class="fa fa-plus-circle"></i> Quick Booking
                        </button>
                        <a href="{{ route('meeting-room-bookings.index') }}" class="btn btn-default">
                            <i class="fa fa-list"></i> View All Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Success/Error Messages --}}
    <div class="row">
        <div class="col-md-12">
            <div id="alertContainer"></div>
        </div>
    </div>
    
    {{-- Room Cards --}}
    <div class="row">
        @foreach($rooms as $room)
        <div class="col-md-4">
            <div class="room-card" data-room="{{ $room['name'] }}">
                
                {{-- Room Header --}}
                <div class="room-header">
                    <h4 class="room-title">{{ $room['name'] }}</h4>
                    <span class="status-badge status-{{ $room['status'] }}">
                        {{ ucfirst($room['status']) }}
                    </span>
                </div>
                
                {{-- Current Booking Info --}}
                @if($room['current_booking'])
                <div class="current-booking">
                    <h5><i class="fa fa-calendar-check-o"></i> Current Meeting</h5>
                    <p><strong>Purpose:</strong> {{ $room['current_booking']->purpose }}</p>
                    <p><strong>User:</strong> {{ $room['current_booking']->user->name ?? 'N/A' }}</p>
                    <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($room['current_booking']->start_datetime)->format('H:i') }} - {{ \Carbon\Carbon::parse($room['current_booking']->end_datetime)->format('H:i') }}</p>
                    <p><strong>Participants:</strong> {{ $room['current_booking']->attendees_count }} people</p>
                </div>
                @else
                <div class="current-booking" style="background: #d4edda; border-left-color: #28a745;">
                    <h5 style="color: #155724;"><i class="fa fa-check-circle"></i> Room Available</h5>
                    <p style="color: #155724;">No active meetings at this time</p>
                </div>
                @endif
                
                {{-- Availability Toggle --}}
                <div class="toggle-container">
                    <span class="toggle-label toggle-label-{{ $room['name'] }}">
                        @if($room['status'] === 'unavailable')
                            ✓ Room Blocked (Click to Unblock)
                        @else
                            Mark as Unavailable
                        @endif
                    </span>
                    <label class="toggle-switch">
                        <input type="checkbox" 
                               class="availability-toggle" 
                               data-room="{{ $room['name'] }}"
                               {{ $room['status'] === 'unavailable' ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="text-muted">(For VIP/Special Guests)</small>
                </div>
                
            </div>
        </div>
        @endforeach
    </div>
    
</div>

{{-- Quick Booking Modal --}}
<div class="modal fade" id="quickBookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plus-circle"></i> Quick Booking - Meeting Room
                </h4>
            </div>
            
            <form id="quickBookingForm">
                @csrf
                <div class="modal-body">
                    
                    <div class="row">
                        {{-- Room Selection --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="room_name">
                                    <i class="fa fa-door-closed"></i> Meeting Room <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="room_name" name="room_name" required>
                                    <option value="">-- Select Room --</option>
                                    @foreach($roomNames as $roomName)
                                    <option value="{{ $roomName }}">{{ $roomName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        {{-- Date --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">
                                    <i class="fa fa-calendar"></i> Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        {{-- Start Time --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">
                                    <i class="fa fa-clock-o"></i> Start Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        
                        {{-- End Time --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">
                                    <i class="fa fa-clock-o"></i> End Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Purpose --}}
                    <div class="form-group">
                        <label for="purpose">
                            <i class="fa fa-bullseye"></i> Purpose <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="2" 
                                  placeholder="Brief description of meeting purpose..." required></textarea>
                    </div>
                    
                    <div class="row">
                        {{-- Requester Name --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="requester_name">
                                    <i class="fa fa-user"></i> Requester Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="requester_name" name="requester_name" 
                                       placeholder="Full name" required>
                            </div>
                        </div>
                        
                        {{-- Department --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="requester_department">
                                    <i class="fa fa-building"></i> Department <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="requester_department" 
                                       name="requester_department" placeholder="Department" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        {{-- Phone --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="requester_phone">
                                    <i class="fa fa-phone"></i> Phone Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="requester_phone" 
                                       name="requester_phone" placeholder="0812-xxxx-xxxx" required>
                            </div>
                        </div>
                        
                        {{-- Participants Count --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="participants_count">
                                    <i class="fa fa-users"></i> Participants <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="participants_count" 
                                       name="participants_count" min="1" placeholder="Number of participants" required>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Booking
                    </button>
                </div>
            </form>
            
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-spinner">
        <i class="fa fa-spinner fa-spin"></i>
        <p style="margin-top: 15px; font-weight: bold;">Processing...</p>
    </div>
</div>

@endsection

@section('scripts')
<script>
console.log('Script block started');

// Ensure jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is NOT loaded!');
    alert('Error: jQuery not loaded!');
} else {
    console.log('jQuery loaded:', jQuery.fn.jquery);
}

jQuery(document).ready(function($) {
    console.log('=== Document Ready Started ===');
    
    // CSRF Token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
    console.log('Toggle switches found:', $('.availability-toggle').length);
    console.log('Quick booking form found:', $('#quickBookingForm').length);
    
    // Force hide loading on page load (multiple methods)
    $('#loadingOverlay').removeClass('show').hide().css('display', 'none');
    console.log('Loading overlay hidden on init');
    console.log('Loading overlay classes:', $('#loadingOverlay').attr('class'));
    console.log('Loading overlay display:', $('#loadingOverlay').css('display'));
    
    // ========================================
    // AVAILABILITY TOGGLE
    // ========================================
    console.log('Attaching toggle event handler...');
    $('.availability-toggle').on('change', function() {
        console.log('=== TOGGLE CLICKED ===');
        console.log('Event triggered');
        
        const checkbox = $(this);
        const roomName = checkbox.data('room');
        const isUnavailable = checkbox.is(':checked');
        const newStatus = isUnavailable ? 'unavailable' : 'available';
        
        console.log('Room:', roomName);
        console.log('New status:', newStatus);
        
        // Confirm action
        const confirmMessage = isUnavailable 
            ? `Mark ${roomName} as UNAVAILABLE?\n\nReason: VIP/Special guest arrival.\nRoom will be blocked for the rest of the day.`
            : `Mark ${roomName} as AVAILABLE again?\n\nThis will remove the blocking and allow bookings.`;
        
        console.log('Showing confirm dialog...');
        if (!confirm(confirmMessage)) {
            console.log('User cancelled');
            // Revert checkbox
            checkbox.prop('checked', !isUnavailable);
            return;
        }
        console.log('User confirmed');
        
        // Get reason if marking unavailable
        let reason = null;
        if (isUnavailable) {
            reason = prompt('Reason for blocking room:', 'VIP guest arrival');
            if (!reason) {
                checkbox.prop('checked', false);
                return;
            }
        }
        
        // Show loading
        showLoading();
        
        // Send AJAX request
        console.log('Sending toggle request:', {
            url: '{{ route('meeting-room-bookings.toggle-availability') }}',
            room_name: roomName,
            status: newStatus,
            reason: reason
        });
        
        $.ajax({
            url: '{{ route('meeting-room-bookings.toggle-availability') }}',
            method: 'POST',
            data: {
                room_name: roomName,
                status: newStatus,
                reason: reason
            },
            success: function(response) {
                console.log('Toggle response:', response);
                hideLoading();
                
                if (response.success) {
                    showAlert('success', response.message);
                    
                    // Update status badge
                    const statusBadge = $(`.room-card[data-room="${roomName}"] .status-badge`);
                    statusBadge.removeClass('status-available status-occupied status-unavailable');
                    statusBadge.addClass('status-' + response.status);
                    statusBadge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
                    
                    // Update toggle label
                    const toggleLabel = $(`.room-card[data-room="${roomName}"] .toggle-label`);
                    if (response.status === 'unavailable') {
                        toggleLabel.html('✓ Room Blocked (Click to Unblock)');
                    } else {
                        toggleLabel.html('Mark as Unavailable');
                    }
                    
                    // Reload page after 2 seconds to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', response.message || 'Failed to update room status');
                    checkbox.prop('checked', !isUnavailable);
                }
            },
            error: function(xhr, status, error) {
                console.error('Toggle error:', xhr, status, error);
                console.error('Response:', xhr.responseText);
                hideLoading();
                
                let errorMsg = 'Failed to update room availability';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseText) {
                    errorMsg = 'Server error: ' + xhr.status;
                }
                
                showAlert('danger', errorMsg);
                checkbox.prop('checked', !isUnavailable);
            }
        });
    });
    
    // ========================================
    // QUICK BOOKING FORM
    // ========================================
    $('#quickBookingForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = {
            room_name: $('#room_name').val(),
            date: $('#date').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            purpose: $('#purpose').val(),
            requester_name: $('#requester_name').val(),
            requester_department: $('#requester_department').val(),
            requester_phone: $('#requester_phone').val(),
            participants_count: $('#participants_count').val()
        };
        
        // Validate
        if (!formData.room_name || !formData.date || !formData.start_time || 
            !formData.end_time || !formData.purpose || !formData.requester_name || 
            !formData.requester_department || !formData.requester_phone || 
            !formData.participants_count) {
            showAlert('warning', 'Please fill in all required fields');
            return;
        }
        
        // Validate time range
        if (formData.start_time >= formData.end_time) {
            showAlert('warning', 'End time must be after start time');
            return;
        }
        
        // Show loading
        showLoading();
        
        // Send AJAX request
        console.log('Sending quick booking:', formData);
        
        $.ajax({
            url: '{{ route('meeting-room-bookings.quick-booking') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log('Quick booking response:', response);
                hideLoading();
                
                if (response.success) {
                    showAlert('success', response.message);
                    
                    // Reset form
                    $('#quickBookingForm')[0].reset();
                    
                    // Close modal
                    $('#quickBookingModal').modal('hide');
                    
                    // Reload page after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', response.message || 'Failed to create booking');
                }
            },
            error: function(xhr, status, error) {
                console.error('Quick booking error:', xhr, status, error);
                console.error('Response:', xhr.responseText);
                hideLoading();
                
                let errorMsg = 'Failed to create booking';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseText) {
                    errorMsg = 'Server error: ' + xhr.status;
                }
                
                showAlert('danger', errorMsg);
            }
        });
    });
    
    // ========================================
    // HELPER FUNCTIONS
    // ========================================
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-${type === 'success' ? 'check' : type === 'danger' ? 'ban' : 'warning'}"></i> ${type === 'success' ? 'Success!' : type === 'danger' ? 'Error!' : 'Warning!'}</h4>
                ${message}
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 500);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('#alertContainer .alert').fadeOut();
        }, 5000);
    }
    
    function showLoading() {
        $('#loadingOverlay').addClass('show');
        
        // Auto-hide after 30 seconds (safety timeout)
        setTimeout(function() {
            if ($('#loadingOverlay').hasClass('show')) {
                console.error('Loading timeout - forcing hide');
                hideLoading();
                showAlert('danger', 'Request timeout. Please try again or check your connection.');
            }
        }, 30000);
    }
    
    function hideLoading() {
        console.log('Hiding loading overlay');
        $('#loadingOverlay').removeClass('show');
    }
    
    // Set default date to today
    $('#date').val(new Date().toISOString().split('T')[0]);
    console.log('Default date set');
    
    console.log('=== Document Ready Complete ===');
    console.log('All event handlers attached');
});

console.log('Script block ended');
</script>
@endsection
