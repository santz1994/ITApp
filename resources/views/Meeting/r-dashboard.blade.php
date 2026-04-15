<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Receptionist Dashboard - Meeting Rooms</title>
    
    {{-- Bootstrap 3 & jQuery --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }
        
        .dashboard-header {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-header h1 {
            font-size: 48px;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .dashboard-header .subtitle {
            font-size: 20px;
            opacity: 0.9;
            margin-top: 10px;
        }
        
        .current-time {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 15px;
        }
        
        .current-date {
            font-size: 20px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .header-actions {
            margin-top: 20px;
        }
        
        .btn-quick-booking {
            background: rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .btn-quick-booking:hover {
            background: rgba(255, 255, 255, 0.5);
            border-color: #fff;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .room-section {
            margin-bottom: 30px;
        }
        
        .room-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            min-height: 450px;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }
        
        .room-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .room-name {
            font-size: 26px;
            font-weight: bold;
            margin: 0 0 8px 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .room-status {
            font-size: 14px;
            margin-top: 8px;
        }
        
        .status-available {
            color: #2ecc71;
            text-shadow: 0 0 10px rgba(46, 204, 113, 0.5);
        }
        
        .status-occupied {
            color: #e74c3c;
            text-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }
        
        .status-unavailable {
            color: #f39c12;
            text-shadow: 0 0 10px rgba(243, 156, 18, 0.5);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .badge-available {
            background: rgba(46, 204, 113, 0.3);
            border: 2px solid #2ecc71;
            color: #fff;
        }
        
        .badge-occupied {
            background: rgba(231, 76, 60, 0.3);
            border: 2px solid #e74c3c;
            color: #fff;
        }
        
        .badge-unavailable {
            background: rgba(243, 156, 18, 0.3);
            border: 2px solid #f39c12;
            color: #fff;
        }
        
        .current-booking {
            background: rgba(231, 76, 60, 0.25);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #e74c3c;
        }
        
        .current-booking strong {
            font-size: 13px;
            display: block;
            margin-bottom: 8px;
        }
        
        .booking-time {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .booking-title {
            font-size: 14px;
            margin: 5px 0;
            opacity: 0.95;
        }
        
        .booking-details {
            font-size: 13px;
            opacity: 0.85;
            margin: 3px 0;
        }
        
        /* Toggle Switch */
        .toggle-container {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 28px;
            vertical-align: middle;
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
            background-color: #2ecc71;
            transition: .4s;
            border-radius: 34px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        input:checked + .toggle-slider {
            background-color: #e74c3c;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(32px);
        }
        
        .toggle-label {
            font-size: 13px;
            font-weight: 600;
            margin-left: 12px;
            vertical-align: middle;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 99999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-spinner {
            text-align: center;
            color: #fff;
        }
        
        .loading-spinner i {
            font-size: 64px;
            margin-bottom: 20px;
            animation: spin 1s linear infinite;
        }
        
        .loading-spinner h3 {
            font-size: 24px;
            font-weight: bold;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Alert Messages */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-size: 16px;
            backdrop-filter: blur(10px);
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.3);
            border: 2px solid #2ecc71;
            color: #fff;
        }
        
        .alert-danger {
            background: rgba(231, 76, 60, 0.3);
            border: 2px solid #e74c3c;
            color: #fff;
        }
        
        /* Modal Styling - Enhanced Modern Design */
        .modal-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            border: none;
            color: #fff;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding: 25px 30px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 20px 20px 0 0;
        }
        
        .modal-header .close {
            color: #fff;
            opacity: 0.8;
            text-shadow: none;
            font-size: 32px;
            transition: all 0.3s ease;
        }
        
        .modal-header .close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }
        
        .modal-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .modal-title i {
            margin-right: 10px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .modal-body {
            padding: 30px;
            max-height: calc(100vh - 250px);
            overflow-y: auto;
        }
        
        /* Custom scrollbar for modal */
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.4);
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.6);
        }
        
        /* Section Headers */
        .modal-body h4 {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .modal-body h4 i {
            margin-right: 8px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Form Group Styling */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.3px;
        }
        
        .form-group label i {
            margin-right: 6px;
            opacity: 0.9;
            width: 16px;
            text-align: center;
        }
        
        .form-group label .text-danger {
            color: #ffeb3b !important;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            opacity: 0.75;
            font-style: italic;
        }
        
        /* Form Controls - Enhanced Input Fields */
        .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            padding: 12px 15px;
            font-size: 14px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .form-control:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: #fff;
            color: #fff;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
            outline: none;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
        }
        
        .form-control:disabled {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        /* Select dropdown - Enhanced visibility with larger text */
        select.form-control {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #fff !important;
            font-weight: 600;
            font-size: 16px !important;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='%23ffffff' d='M8 11L3 6h10z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 15px center !important;
            padding: 14px 45px 14px 15px !important;
            height: auto !important;
            min-height: 48px;
            line-height: 1.5;
        }
        
        select.form-control:hover {
            background: rgba(255, 255, 255, 0.35) !important;
        }
        
        select.form-control:focus {
            background: rgba(255, 255, 255, 0.4) !important;
        }
        
        /* Dropdown options - larger and more visible */
        select.form-control option {
            background: #764ba2;
            color: #fff;
            padding: 12px 15px;
            font-weight: 600;
            font-size: 16px;
            line-height: 1.6;
        }
        
        select.form-control option:hover,
        select.form-control option:checked,
        select.form-control option:focus {
            background: #667eea;
            color: #fff;
        }
        
        /* Placeholder option (first option with empty value) */
        select.form-control option[value=""] {
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
        }
        
        /* Textarea specific */
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        /* Number input */
        input[type="number"].form-control {
            -moz-appearance: textfield;
        }
        
        input[type="number"].form-control::-webkit-inner-spin-button,
        input[type="number"].form-control::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        /* Modal Footer */
        .modal-footer {
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            padding: 20px 30px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 0 0 20px 20px;
        }
        
        /* Buttons - Enhanced with Icons and Transitions */
        .btn-primary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
            border: 2px solid rgba(255, 255, 255, 0.6);
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.4));
            border-color: #fff;
            color: #fff;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary i {
            margin-right: 8px;
        }
        
        .btn-default {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        
        .btn-default:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.6);
            color: #fff;
            transform: translateY(-2px);
        }
        
        .btn-default:active {
            transform: translateY(0);
        }
        
        /* Section Dividers */
        .section-divider {
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 15px;
            margin-bottom: 20px;
            margin-top: 25px;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
        }
        
        .section-divider:first-child {
            margin-top: 0;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-top: 30px;
            font-size: 14px;
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.3; }
        }
        
        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Modified Field Indicator */
        @keyframes glow {
            0%, 100% {
                box-shadow: 0 0 15px rgba(255, 235, 59, 0.3);
            }
            50% {
                box-shadow: 0 0 25px rgba(255, 235, 59, 0.6);
            }
        }
        
        .field-modified {
            animation: glow 2s ease-in-out infinite;
        }
        
        /* Help Badge */
        .help-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: normal;
            margin-left: 6px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Pending List */
        .pending-section {
            background: rgba(243, 156, 18, 0.2);
            border: 2px solid rgba(243, 156, 18, 0.5);
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
        }
        
        .pending-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            border-left: 3px solid #f39c12;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pending-item:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
        }
        
        /* Booking Item */
        .booking-item {
            transition: all 0.3s ease;
        }
        
        .booking-item:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            transform: translateX(3px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        /* Draggable */
        .draggable {
            cursor: move;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .draggable:hover {
            opacity: 0.95;
            transform: scale(1.02) translateX(3px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        
        .dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }
        
        .drag-over {
            border: 2px dashed #fff;
        }
        
        /* Drop Target Indicator */
        .drop-target {
            box-shadow: 0 0 30px rgba(46, 204, 113, 0.8), 
                        0 0 60px rgba(46, 204, 113, 0.5) !important;
            border: 3px solid #2ecc71 !important;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
        
        .drop-target .room-header {
            background: rgba(46, 204, 113, 0.3) !important;
        }
        
        /* Edit Modal Badge */
        .edit-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(52, 152, 219, 0.8);
            color: #fff;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="dashboard-header">
        <h1>
            <i class="fa fa-desktop"></i>
            RECEPTIONIST DASHBOARD
        </h1>
        <div class="subtitle">Meeting Room Control Center</div>
        <div style="margin-top: 15px;">
            <div class="current-time" id="currentTime"></div>
            <div class="current-date" id="currentDate"></div>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-quick-booking" data-toggle="modal" data-target="#quickBookingModal">
                <i class="fa fa-plus-circle"></i> Quick Booking
            </button>
            <button type="button" class="btn btn-quick-booking" data-toggle="modal" data-target="#monthlyReportModal" style="margin-left: 10px;">
                <i class="fa fa-file-excel-o"></i> Laporan Bulanan
            </button>
        </div>
    </div>

    {{-- Alert Container --}}
    <div id="alertContainer"></div>

    {{-- Room Cards --}}
    <div class="row">
        @foreach($rooms as $room)
        <div class="col-md-4 room-section">
            <div class="room-card">
                <div class="room-header">
                    <h2 class="room-name">
                        <i class="fa fa-door-open"></i> {{ $room['name'] }}
                    </h2>
                    <div class="room-status">
                        <i class="fa fa-circle status-{{ $room['status'] }}"></i>
                        <span class="status-{{ $room['status'] }}">
                            @if($room['status'] === 'available')
                                TERSEDIA
                            @elseif($room['status'] === 'occupied')
                                SEDANG DIGUNAKAN
                            @else
                                TIDAK TERSEDIA
                            @endif
                        </span>
                    </div>
                    <span class="status-badge badge-{{ $room['status'] }}">
                        @if($room['status'] === 'available')
                            <i class="fa fa-check-circle"></i> AVAILABLE
                        @elseif($room['status'] === 'occupied')
                            <i class="fa fa-users"></i> OCCUPIED
                        @else
                            <i class="fa fa-ban"></i> UNAVAILABLE
                        @endif
                    </span>
                </div>

                {{-- Current Booking Info --}}
                @if($room['current_booking'])
                <div class="current-booking pulse">
                    <strong><i class="fa fa-circle" style="color: #e74c3c; animation: blink 1s infinite;"></i> SEDANG BERLANGSUNG:</strong>
                    <div class="booking-time">
                        <i class="fa fa-clock-o"></i> 
                        {{ \Carbon\Carbon::parse($room['current_booking']->start_datetime)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($room['current_booking']->end_datetime)->format('H:i') }}
                    </div>
                    <div class="booking-title">
                        {{ \Illuminate\Support\Str::limit($room['current_booking']->purpose, 40) }}
                    </div>
                </div>
                @endif

                {{-- Today's Bookings List --}}
                @if($room['today_bookings']->count() > 0)
                <div style="margin-top: 15px;">
                    <strong style="font-size: 14px; display: block; margin-bottom: 10px;">
                        <i class="fa fa-calendar"></i> Jadwal Hari Ini ({{ $room['today_bookings']->count() }})
                    </strong>
                    <div style="max-height: 200px; overflow-y: auto;" class="bookings-list">
                        @foreach($room['today_bookings'] as $booking)
                        @php
                            $isCurrent = $booking->start_datetime <= now() && $booking->end_datetime >= now();
                            $isPast = $booking->end_datetime < now();
                        @endphp
                        <div class="booking-item {{ !$isPast ? 'draggable' : '' }}" 
                             data-booking-id="{{ $booking->id }}"
                             data-start="{{ $booking->start_datetime }}"
                             data-end="{{ $booking->end_datetime }}"
                             draggable="{{ !$isPast ? 'true' : 'false' }}"
                             onclick="editBooking({{ $booking->id }})"
                             style="background: rgba(255,255,255,{{ $isCurrent ? '0.3' : ($isPast ? '0.1' : '0.15') }}); 
                                    padding: 12px; 
                                    border-radius: 8px; 
                                    margin-bottom: 10px;
                                    position: relative;
                                    border-left: 4px solid {{ $isCurrent ? '#e74c3c' : ($isPast ? '#95a5a6' : '#3498db') }};
                                    cursor: pointer;
                                    transition: all 0.3s ease;">
                            @if(!$isPast)
                            <div class="edit-badge">
                                <i class="fa fa-edit"></i> CLICK TO EDIT
                            </div>
                            @endif
                            
                            {{-- Finish Button for Ongoing Meetings (Receptionist/Superadmin/Director/Management Only) --}}
                            @if($isCurrent && $booking->canBeFinished() && user_has_role(Auth::user(), ['receptionist', 'super-admin', 'director', 'management']))
                            <div style="position: absolute; top: 8px; right: 8px; z-index: 3;">
                                <button type="button" 
                                        class="btn btn-success btn-xs" 
                                        onclick="event.stopPropagation(); finishMeeting({{ $booking->id }})"
                                        style="padding: 4px 10px; font-weight: bold; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"
                                        title="Akhiri meeting lebih awal">
                                    <i class="fa fa-check"></i> FINISH
                                </button>
                            </div>
                            @endif
                            
                            {{-- Time and Status --}}
                            <div style="font-size: 15px; font-weight: bold; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <i class="fa fa-clock-o"></i> 
                                    {{ \Carbon\Carbon::parse($booking->start_datetime)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}
                                    <span style="font-size: 10px; opacity: 0.8; font-weight: normal;">
                                        ({{ number_format($booking->start_datetime->diffInMinutes($booking->end_datetime) / 60, 1) }} jam)
                                    </span>
                                </div>
                                <div>
                                    @if($isCurrent)
                                        <span style="color: #e74c3c; font-size: 11px;">● LIVE</span>
                                    @elseif($isPast)
                                        <span style="opacity: 0.6; font-size: 11px;">✓ SELESAI</span>
                                    @else
                                        <i class="fa fa-arrows" style="opacity: 0.5; font-size: 12px;" title="Drag to reschedule"></i>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Purpose --}}
                            <div style="font-size: 13px; font-weight: 600; margin-bottom: 6px; opacity: 0.95;">
                                <i class="fa fa-bullseye"></i> {{ \Illuminate\Support\Str::limit($booking->purpose, 40) }}
                            </div>
                            
                            {{-- Requester Info --}}
                            <div style="font-size: 11px; opacity: 0.85; margin-bottom: 4px;">
                                <i class="fa fa-user"></i> {{ $booking->user->name ?? 'N/A' }}
                                @if($booking->department)
                                    <span style="margin-left: 5px;">| {{ $booking->department }}</span>
                                @endif
                            </div>
                            
                            {{-- Attendees and Description --}}
                            <div style="font-size: 11px; opacity: 0.85; margin-bottom: 4px;">
                                <i class="fa fa-users"></i> {{ $booking->attendees_count }} peserta
                                @if($booking->requester_position)
                                    <span style="margin-left: 8px;">
                                        <i class="fa fa-briefcase"></i> {{ $booking->requester_position }}
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Description if available --}}
                            @if($booking->meeting_description)
                            <div style="font-size: 11px; opacity: 0.8; margin-top: 6px; padding: 5px 8px; background: rgba(0,0,0,0.1); border-radius: 4px; line-height: 1.4;">
                                <i class="fa fa-align-left"></i> {{ \Illuminate\Support\Str::limit($booking->meeting_description, 60) }}
                            </div>
                            @endif
                            
                            {{-- Booking ID --}}
                            <div style="font-size: 10px; opacity: 0.6; margin-top: 6px;">
                                ID: #{{ $booking->id }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="text-align: center; padding: 20px; opacity: 0.6;">
                    <i class="fa fa-calendar-times-o" style="font-size: 32px; margin-bottom: 10px;"></i>
                    <div style="font-size: 14px;">Tidak ada booking hari ini</div>
                </div>
                @endif

                {{-- Pending Bookings List --}}
                @if($room['pending_bookings']->count() > 0)
                <div class="pending-section">
                    <strong style="font-size: 13px; display: block; margin-bottom: 10px; color: #f39c12;">
                        <i class="fa fa-hourglass-half"></i> Menunggu Approval ({{ $room['pending_bookings']->count() }})
                    </strong>
                    <div style="max-height: 200px; overflow-y: auto;">
                        @foreach($room['pending_bookings'] as $pending)
                        <div class="pending-item" onclick="editBooking({{ $pending->id }})" 
                             style="cursor: pointer; transition: all 0.3s ease;">
                            {{-- Time and Duration --}}
                            <div style="font-size: 13px; font-weight: bold; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <i class="fa fa-clock-o"></i> 
                                    {{ \Carbon\Carbon::parse($pending->start_datetime)->format('d/m H:i') }} - 
                                    {{ \Carbon\Carbon::parse($pending->end_datetime)->format('H:i') }}
                                    <span style="font-size: 10px; opacity: 0.8; font-weight: normal;">
                                        ({{ number_format($pending->start_datetime->diffInMinutes($pending->end_datetime) / 60, 1) }} jam)
                                    </span>
                                </div>
                                <i class="fa fa-edit" style="opacity: 0.7; font-size: 12px;"></i>
                            </div>
                            
                            {{-- Purpose --}}
                            <div style="font-size: 12px; font-weight: 600; margin-bottom: 5px; opacity: 0.95;">
                                <i class="fa fa-bullseye"></i> {{ \Illuminate\Support\Str::limit($pending->purpose, 35) }}
                            </div>
                            
                            {{-- Requester Info --}}
                            <div style="font-size: 11px; opacity: 0.85; margin-bottom: 3px;">
                                <i class="fa fa-user"></i> {{ $pending->user->name ?? 'N/A' }}
                                @if($pending->department)
                                    | {{ $pending->department }}
                                @endif
                            </div>
                            
                            {{-- Attendees --}}
                            <div style="font-size: 11px; opacity: 0.85; margin-bottom: 3px;">
                                <i class="fa fa-users"></i> {{ $pending->attendees_count }} peserta
                                @if($pending->requester_position)
                                    <span style="margin-left: 5px;">| {{ $pending->requester_position }}</span>
                                @endif
                            </div>
                            
                            {{-- Description if available --}}
                            @if($pending->meeting_description)
                            <div style="font-size: 10px; opacity: 0.75; margin-top: 5px; padding: 4px 6px; background: rgba(0,0,0,0.1); border-radius: 3px; line-height: 1.3;">
                                <i class="fa fa-align-left"></i> {{ \Illuminate\Support\Str::limit($pending->meeting_description, 50) }}
                            </div>
                            @endif
                            
                            {{-- Booking ID --}}
                            <div style="font-size: 9px; opacity: 0.6; margin-top: 4px;">
                                ID: #{{ $pending->id }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Availability Toggle --}}
                <div class="toggle-container">
                    <label class="toggle-switch">
                        <input type="checkbox" 
                               class="room-toggle" 
                               data-room="{{ $room['name'] }}"
                               {{ $room['status'] === 'unavailable' ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">
                        @if($room['status'] === 'unavailable')
                            <i class="fa fa-check-circle"></i> Room Blocked (Click to Unblock)
                        @else
                            <i class="fa fa-lock"></i> Mark as Unavailable
                        @endif
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Footer --}}
    <div class="footer">
        <i class="fa fa-info-circle"></i> 
        Receptionist Dashboard - Real-time Meeting Room Control
        <br>
        <small style="opacity: 0.8;">
            Logged in as: <strong>{{ Auth::user()->name }}</strong> | 
            Last updated: <span id="lastUpdate"></span>
        </small>
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
                        <i class="fa fa-plus-circle"></i> Quick Booking - New Meeting Request
                    </h4>
                    <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">
                        <i class="fa fa-info-circle"></i> 
                        Booking request will be pending until approved by Director
                    </p>
                </div>
                <form id="quickBookingForm">
                    <div class="modal-body">
                        {{-- Section 1: Informasi Pemohon --}}
                        <div class="section-divider">
                            <h4 style="margin: 0;"><i class="fa fa-user"></i> Informasi Pemohon / Requester Information</h4>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        <i class="fa fa-user"></i> Nama Pemohon / Requester Name *
                                        <span style="font-size: 11px; font-weight: normal; opacity: 0.8; margin-left: 5px;">
                                            <i class="fa fa-info-circle"></i> Editable
                                        </span>
                                    </label>
                                    <input type="text" name="requester_name" id="requester_name" class="form-control" 
                                           value="{{ Auth::user()->name }}" 
                                           placeholder="Masukkan nama pemohon (e.g., John Doe, Direktur Utama)" required>
                                    <small style="opacity: 0.8;">
                                        <i class="fa fa-lightbulb-o"></i> 
                                        Dapat diubah untuk booking atas nama orang lain (VIP guest, tamu eksternal)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-id-badge"></i> Jabatan / Position *</label>
                                    <input type="text" name="requester_position" class="form-control" 
                                           placeholder="e.g., Staff IT, Manager" required>
                                    <small style="opacity: 0.8;">Masukkan jabatan Anda</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-building"></i> Bagian / Departemen *</label>
                                    <input type="text" name="department" class="form-control" 
                                           placeholder="e.g., IT Department" required>
                                    <small style="opacity: 0.8;">Bagian/departemen Anda</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Tanggal / Date</label>
                                    <input type="text" class="form-control" value="{{ date('d F Y') }}" disabled>
                                    <small style="opacity: 0.8;">Tanggal pembuatan form</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Detail Rapat --}}
                        <div class="section-divider">
                            <h4 style="margin: 0;"><i class="fa fa-calendar-check-o"></i> Detail Rapat / Meeting Details</h4>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label><i class="fa fa-door-open"></i> Ruang Rapat / Meeting Room *</label>
                                    <select name="room_name" class="form-control" required style="font-size: 16px; padding: 14px 40px 14px 15px; height: auto;">
                                        <option value="">-- Pilih Ruang Rapat --</option>
                                        @foreach(['Ruang Meeting 1', 'Ruang Meeting 2', 'Ruang Meeting 3'] as $roomName)
                                            <option value="{{ $roomName }}">{{ $roomName }}</option>
                                        @endforeach
                                    </select>
                                    <small style="opacity: 0.8;">Pilih ruang rapat yang diinginkan</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-users"></i> Estimasi Peserta *</label>
                                    <input type="number" name="attendees_count" class="form-control" 
                                           min="1" max="100" value="1" required>
                                    <small style="opacity: 0.8;">Jumlah peserta (1-100)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fa fa-bullseye"></i> Keperluan Rapat / Meeting Purpose *</label>
                            <input type="text" name="purpose" class="form-control" 
                                   placeholder="e.g., Review Proyek Q4, Training Karyawan" 
                                   minlength="10" required>
                            <small style="opacity: 0.8;">Tujuan/keperluan rapat (minimal 10 karakter)</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fa fa-align-left"></i> Deskripsi / Keterangan Rapat *</label>
                            <textarea name="meeting_description" class="form-control" rows="3" 
                                      placeholder="Jelaskan detail rapat, agenda, dan hal-hal penting lainnya..." 
                                      minlength="10" required></textarea>
                            <small style="opacity: 0.8;">Deskripsi lengkap rapat (minimal 10 karakter)</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fa fa-list"></i> Kebutuhan Fasilitas / Facility Needs <span style="opacity: 0.7;">(Optional)</span></label>
                            <textarea name="meeting_needs" class="form-control" rows="2" 
                                      placeholder="e.g., Proyektor, Whiteboard, Sound System, Snack & Coffee"></textarea>
                            <small style="opacity: 0.8;">Fasilitas tambahan yang dibutuhkan (opsional)</small>
                        </div>

                        {{-- Section 3: Waktu Rapat --}}
                        <div class="section-divider">
                            <h4 style="margin: 0;"><i class="fa fa-clock-o"></i> Diperlukan Pada / Required On</h4>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Tanggal Rapat *</label>
                                    <input type="date" name="meeting_date" id="modal_meeting_date" class="form-control" 
                                           min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    <small style="opacity: 0.8;">Pilih tanggal rapat</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-clock-o"></i> Waktu Mulai *</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                        <input type="text" name="start_time" id="modal_start_time" class="form-control modal-time-input" 
                                               value="09:00" 
                                               maxlength="5"
                                               pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                               placeholder="09:00"
                                               autocomplete="off"
                                               required>
                                    </div>
                                    <small style="opacity: 0.8;"><strong>Format 24 jam:</strong> 00:00 - 23:59</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-clock-o"></i> Waktu Selesai *</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                        <input type="text" name="end_time" id="modal_end_time" class="form-control modal-time-input" 
                                               value="11:00" 
                                               maxlength="5"
                                               pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                               placeholder="11:00"
                                               autocomplete="off"
                                               required>
                                    </div>
                                    <small style="opacity: 0.8;"><strong>Format 24 jam:</strong> 00:00 - 23:59</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Submit Request (Pending Approval)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div class="loading-overlay">
        <div class="loading-spinner">
            <i class="fa fa-spinner"></i>
            <h3>Processing...</h3>
            <p>Please wait</p>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Update Clock
            function updateClock() {
                const now = new Date();
                
                // Time - Format as HH:MM:SS
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                $('#currentTime').html(`${hours}:${minutes}<span style="font-size: 0.7em;">:${seconds}</span>`);
                
                // Date
                const dateOptions = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                };
                $('#currentDate').text(now.toLocaleDateString('id-ID', dateOptions));
                
                // Last update - Format 24 jam
                const lastUpdateHours = String(now.getHours()).padStart(2, '0');
                const lastUpdateMinutes = String(now.getMinutes()).padStart(2, '0');
                const lastUpdateSeconds = String(now.getSeconds()).padStart(2, '0');
                const lastUpdateDate = now.toLocaleDateString('id-ID');
                $('#lastUpdate').text(`${lastUpdateDate} ${lastUpdateHours}:${lastUpdateMinutes}:${lastUpdateSeconds}`);
            }
            
            updateClock();
            setInterval(updateClock, 1000);

            // Toggle Room Availability
            $('.room-toggle').on('change', function() {
                const $toggle = $(this);
                const roomName = $toggle.data('room');
                const newStatus = $toggle.is(':checked') ? 'unavailable' : 'available';
                const $label = $toggle.closest('.toggle-container').find('.toggle-label');
                
                let reason = '';
                if (newStatus === 'unavailable') {
                    reason = prompt('Alasan memblokir ruangan (opsional):');
                    if (reason === null) {
                        // User cancelled
                        $toggle.prop('checked', false);
                        return;
                    }
                } else {
                    if (!confirm('Apakah Anda yakin ingin membuka blokir ruangan ini?')) {
                        $toggle.prop('checked', true);
                        return;
                    }
                }
                
                showLoading();
                
                $.ajax({
                    url: '{{ route("meeting-room-bookings.toggle-availability") }}',
                    method: 'POST',
                    data: {
                        room_name: roomName,
                        status: newStatus,
                        reason: reason || 'Manual block by receptionist'
                    },
                    success: function(response) {
                        hideLoading();
                        
                        if (response.success) {
                            showAlert('success', response.message);
                            
                            // Update label
                            if (newStatus === 'unavailable') {
                                $label.html('<i class="fa fa-check-circle"></i> Room Blocked (Click to Unblock)');
                            } else {
                                $label.html('<i class="fa fa-lock"></i> Mark as Unavailable');
                            }
                            
                            // Reload after 2 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showAlert('danger', response.message || 'Terjadi kesalahan');
                            $toggle.prop('checked', !$toggle.is(':checked'));
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        
                        let errorMsg = 'Terjadi kesalahan saat mengubah status ruangan';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        showAlert('danger', errorMsg);
                        $toggle.prop('checked', !$toggle.is(':checked'));
                    }
                });
            });

            // Requester Name Field - Visual Feedback Enhancement
            const originalRequesterName = '{{ Auth::user()->name }}';
            let requesterNameChanged = false;
            
            $('#requester_name').on('input', function() {
                const currentValue = $(this).val().trim();
                const $field = $(this);
                const $label = $field.closest('.form-group').find('label');
                
                // Check if value differs from authenticated user
                if (currentValue !== originalRequesterName && currentValue !== '') {
                    // Visual feedback: Different person
                    $field.css({
                        'border-color': '#ffeb3b',
                        'background': 'rgba(255, 235, 59, 0.2)',
                        'box-shadow': '0 0 15px rgba(255, 235, 59, 0.3)'
                    });
                    
                    if (!requesterNameChanged) {
                        $label.append(' <span class="badge" style="background: #ffeb3b; color: #333; font-size: 10px; vertical-align: middle;">MODIFIED</span>');
                        requesterNameChanged = true;
                    }
                } else {
                    // Reset to default
                    $field.css({
                        'border-color': 'rgba(255, 255, 255, 0.3)',
                        'background': 'rgba(255, 255, 255, 0.15)',
                        'box-shadow': ''
                    });
                    
                    $label.find('.badge').remove();
                    requesterNameChanged = false;
                }
            });
            
            // Reset visual state when modal opens
            $('#quickBookingModal').on('show.bs.modal', function() {
                $('#requester_name').val(originalRequesterName).trigger('input');
                requesterNameChanged = false;
            });

            // ============================================
            // 24-HOUR TIME INPUT WITH AUTO-MASKING (Modal)
            // ============================================
            
            // Auto-format time input as user types (HH:MM masking)
            $('.modal-time-input').on('input', function(e) {
                var value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
                var formatted = '';
                
                if (value.length > 0) {
                    // First digit of hour (0-2)
                    formatted = value[0];
                    
                    if (value.length > 1) {
                        // Second digit of hour
                        var firstDigit = parseInt(value[0]);
                        var secondDigit = parseInt(value[1]);
                        
                        // Validate hour (max 23)
                        if (firstDigit === 2 && secondDigit > 3) {
                            secondDigit = 3; // Force max hour 23
                        }
                        formatted += secondDigit + ':';
                        
                        if (value.length > 2) {
                            // First digit of minute (0-5)
                            var minuteFirst = parseInt(value[2]);
                            if (minuteFirst > 5) {
                                minuteFirst = 5; // Force max minute 59
                            }
                            formatted += minuteFirst;
                            
                            if (value.length > 3) {
                                // Second digit of minute
                                formatted += value[3];
                            }
                        }
                    }
                }
                
                $(this).val(formatted);
            });
            
            // Validate and format on blur
            $('.modal-time-input').on('blur', function() {
                var value = $(this).val();
                
                if (value) {
                    // Check if format is correct HH:MM
                    var match = value.match(/^(\d{1,2}):?(\d{0,2})$/);
                    
                    if (match) {
                        var hours = parseInt(match[1], 10);
                        var minutes = match[2] ? parseInt(match[2], 10) : 0;
                        
                        // Validate ranges
                        if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
                            // Format with leading zeros
                            var formatted = 
                                (hours < 10 ? '0' + hours : hours) + ':' + 
                                (minutes < 10 ? '0' + minutes : minutes);
                            $(this).val(formatted);
                            $(this).removeClass('is-invalid');
                        } else {
                            // Invalid time
                            $(this).addClass('is-invalid');
                        }
                    } else {
                        // Invalid format
                        $(this).addClass('is-invalid');
                    }
                }
            });
            
            // Remove invalid class on focus
            $('.modal-time-input').on('focus', function() {
                $(this).removeClass('is-invalid');
            });

            // Quick Booking Form Submit
            $('#quickBookingForm').on('submit', function(e) {
                e.preventDefault();
                
                showLoading();
                
                $.ajax({
                    url: '{{ route("meeting-room-bookings.quick-booking") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        hideLoading();
                        
                        if (response.success) {
                            $('#quickBookingModal').modal('hide');
                            $('#quickBookingForm')[0].reset();
                            
                            // Enhanced success message showing requester name
                            const requesterName = $('#requester_name').val();
                            const isModified = requesterName !== originalRequesterName;
                            let successMsg = response.message;
                            
                            if (isModified) {
                                successMsg += `<br><strong><i class="fa fa-user"></i> Booking untuk: ${requesterName}</strong>`;
                            }
                            
                            showAlert('success', successMsg);
                            
                            // Reload after 2 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showAlert('danger', response.message || 'Gagal membuat booking');
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        
                        let errorMsg = 'Terjadi kesalahan saat membuat booking';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('<br>');
                        }
                        
                        showAlert('danger', errorMsg);
                    }
                });
            });

            // Drag and Drop Functionality
            let draggedElement = null;
            let draggedBookingId = null;
            let draggedFromRoom = null;

            $('.draggable').on('dragstart', function(e) {
                draggedElement = $(this);
                draggedBookingId = $(this).data('booking-id');
                // Find room name from parent room-card
                draggedFromRoom = $(this).closest('.room-card').find('.room-name').text().trim().replace(/\s+/g, ' ');
                $(this).addClass('dragging');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/html', $(this).html());
            });

            $('.draggable').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.bookings-list').removeClass('drag-over');
                $('.room-card').removeClass('drop-target');
            });

            // Allow drop on entire room card
            $('.room-card').on('dragover', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                $(this).addClass('drop-target');
            });

            $('.room-card').on('dragleave', function(e) {
                // Only remove if leaving the card completely
                if (!$(this).has(e.relatedTarget).length) {
                    $(this).removeClass('drop-target');
                }
            });

            $('.room-card').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drop-target');
                
                if (draggedElement) {
                    // Get target room name
                    const targetRoomFull = $(this).find('.room-name').text().trim();
                    const targetRoom = targetRoomFull.replace(/\s+/g, ' ');
                    
                    // Check if different room
                    const isDifferentRoom = draggedFromRoom !== targetRoom;
                    
                    // Prompt for new time
                    let promptMessage = 'Enter new time (HH:MM format, e.g., 14:30):';
                    if (isDifferentRoom) {
                        promptMessage = `Moving to ${targetRoom}\nEnter new time (HH:MM format, e.g., 14:30):`;
                    }
                    
                    const newTime = prompt(promptMessage);
                    if (newTime) {
                        const timeMatch = newTime.match(/^(\d{1,2}):(\d{2})$/);
                        if (timeMatch) {
                            const originalStart = new Date(draggedElement.data('start'));
                            const originalEnd = new Date(draggedElement.data('end'));
                            const duration = (originalEnd - originalStart) / 60000; // minutes
                            
                            const [, hours, minutes] = timeMatch;
                            const newStart = new Date(originalStart);
                            newStart.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                            
                            const newEnd = new Date(newStart);
                            newEnd.setMinutes(newEnd.getMinutes() + duration);
                            
                            // Pass target room to update function
                            updateBookingTime(draggedBookingId, newStart, newEnd, isDifferentRoom ? targetRoom : null);
                        } else {
                            alert('Invalid time format! Please use HH:MM (e.g., 14:30)');
                        }
                    }
                }
            });

            // Update booking time via AJAX
            function updateBookingTime(bookingId, startDatetime, endDatetime, targetRoom) {
                showLoading();
                
                // Format datetime as local time string (YYYY-MM-DD HH:MM:SS)
                function formatLocalDatetime(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const seconds = String(date.getSeconds()).padStart(2, '0');
                    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                }
                
                // Prepare data
                const requestData = {
                    start_datetime: formatLocalDatetime(startDatetime),
                    end_datetime: formatLocalDatetime(endDatetime)
                };
                
                // Add room_name if moving to different room
                if (targetRoom) {
                    requestData.room_name = targetRoom;
                }
                
                $.ajax({
                    url: `/meeting-room-bookings/${bookingId}/update-time`,
                    method: 'PUT',
                    data: requestData,
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            showAlert('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showAlert('danger', response.message || 'Failed to update booking time');
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMsg = 'Error updating booking time';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert('danger', errorMsg);
                    }
                });
            }

            // Finish meeting early
            window.finishMeeting = function(bookingId) {
                if (confirm('Akhiri meeting ini sekarang?\n\nMeeting akan ditandai sebagai SELESAI walaupun belum mencapai waktu berakhir yang dijadwalkan.\n\nFinish this meeting now?\n\nThe meeting will be marked as FINISHED even if it hasn\'t reached the scheduled end time.')) {
                    showLoading();
                    
                    $.ajax({
                        url: `/meeting-room-bookings/${bookingId}/finish`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            hideLoading();
                            showAlert('success', 'Meeting berhasil diakhiri! / Meeting finished successfully!');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            hideLoading();
                            let errorMsg = 'Error finishing meeting';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showAlert('danger', errorMsg);
                        }
                    });
                }
            };

            // Edit booking by clicking
            window.editBooking = function(bookingId) {
                window.location.href = `/meeting-room-bookings/${bookingId}/edit`;
            };

            // Helper Functions
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                        ${message}
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
                
                // Auto dismiss after 5 seconds
                setTimeout(function() {
                    $('#alertContainer').html('');
                }, 5000);
                
                // Scroll to top
                $('html, body').animate({ scrollTop: 0 }, 500);
            }

            function showLoading() {
                $('.loading-overlay').css('display', 'flex');
            }

            function hideLoading() {
                $('.loading-overlay').css('display', 'none');
            }
        });
    </script>

    {{-- Monthly Report Modal --}}
    <div class="modal fade" id="monthlyReportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="background: #fff; color: #333; border-radius: 8px;">
                <div class="modal-header" style="background: #00a65a; color: #fff; border-radius: 8px 8px 0 0; border: none;">
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 1; font-size: 28px;">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title" style="font-weight: bold;">
                        <i class="fa fa-file-excel-o"></i> Download Laporan Bulanan Excel
                    </h4>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <form id="monthlyReportForm" method="GET" action="{{ route('meeting-room-bookings.report.monthly-excel') }}">
                        <div class="form-group">
                            <label for="report_month" style="color: #333; font-weight: bold; font-size: 14px;">Pilih Bulan:</label>
                            <select class="form-control" id="report_month" name="month" required 
                                    style="height: 40px; font-size: 14px; color: #333; border: 2px solid #ddd; border-radius: 4px;">
                                <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>Januari</option>
                                <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>Februari</option>
                                <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>Maret</option>
                                <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>April</option>
                                <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>Mei</option>
                                <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>Juni</option>
                                <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>Juli</option>
                                <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>Agustus</option>
                                <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>September</option>
                                <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>November</option>
                                <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>Desember</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="report_year" style="color: #333; font-weight: bold; font-size: 14px;">Pilih Tahun:</label>
                            <select class="form-control" id="report_year" name="year" required
                                    style="height: 40px; font-size: 14px; color: #333; border: 2px solid #ddd; border-radius: 4px;">
                                @for($year = now()->year; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ now()->year == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="alert alert-info" style="margin-top: 15px; background: #d9edf7; border: 1px solid #bce8f1; color: #31708f;">
                            <i class="fa fa-info-circle"></i>
                            Laporan akan berisi data booking yang sudah <strong>Approved</strong> atau <strong>Finished</strong> pada bulan yang dipilih.
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #ddd; padding: 15px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" 
                            style="background: #f4f4f4; color: #333; border: 1px solid #ddd; padding: 8px 20px; font-size: 14px;">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success" id="btnDownloadReport"
                            style="background: #00a65a; color: #fff; border: none; padding: 8px 20px; font-size: 14px; font-weight: bold;">
                        <i class="fa fa-download"></i> Download Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Download report button
            $('#btnDownloadReport').click(function() {
                const month = $('#report_month').val();
                const year = $('#report_year').val();
                
                if (!month || !year) {
                    alert('Pilih bulan dan tahun terlebih dahulu!');
                    return;
                }
                
                // Submit form to download
                $('#monthlyReportForm').submit();
                
                // Close modal after a delay
                setTimeout(function() {
                    $('#monthlyReportModal').modal('hide');
                }, 500);
            });

            // Auto-open modal if triggered from sidebar
            @if(request('open_report_modal') == 'true')
                $('#monthlyReportModal').modal('show');
            @endif
        });
    </script>
</body>
</html>
