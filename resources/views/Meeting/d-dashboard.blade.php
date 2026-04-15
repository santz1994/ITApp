<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Director Dashboard - Meeting Room Requests</title>
    
    {{-- Bootstrap 3 & jQuery --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    {{-- Notification System CSS --}}
    <link href="{{ asset('/css/notification-ui.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/notifications.css') }}" rel="stylesheet" type="text/css" />
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <style>
        body {
            background-color: #ecf0f5;
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 18px; /* Increased from default 14px */
        }
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px; /* Increased padding */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .main-header h1 {
            margin: 0;
            font-size: 36px; /* Increased from 28px */
            font-weight: 600;
        }
        .main-header small {
            display: block;
            margin-top: 5px;
            opacity: 0.9;
            font-size: 20px; /* Increased from 14px */
        }
        .info-box {
            display: block;
            min-height: 110px; /* Increased from 90px */
            background: #fff;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 2px;
            margin-bottom: 15px;
        }
        .info-box-icon {
            border-top-left-radius: 2px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 2px;
            display: block;
            float: left;
            height: 110px; /* Increased from 90px */
            width: 110px; /* Increased from 90px */
            text-align: center;
            font-size: 55px; /* Increased from 45px */
            line-height: 110px; /* Increased from 90px */
            background: rgba(0,0,0,0.2);
        }
        .info-box-content {
            padding: 8px 15px; /* Increased padding */
            margin-left: 110px; /* Increased from 90px */
        }
        .info-box-text {
            display: block;
            font-size: 18px; /* Increased from 14px */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-box-number {
            display: block;
            font-weight: bold;
            font-size: 28px; /* Increased from 18px */
        }
        .bg-yellow {
            background-color: #f39c12 !important;
            color: #fff !important;
        }
        .bg-green {
            background-color: #00a65a !important;
            color: #fff !important;
        }
        .bg-red {
            background-color: #dd4b39 !important;
            color: #fff !important;
        }
        .bg-blue {
            background-color: #0073b7 !important;
            color: #fff !important;
        }
        .box {
            position: relative;
            border-radius: 3px;
            background: #ffffff;
            border-top: 3px solid #d2d6de;
            margin-bottom: 20px;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        .box-header {
            color: #444;
            display: block;
            padding: 15px; /* Increased from 10px */
            position: relative;
        }
        .box-header.with-border {
            border-bottom: 1px solid #f4f4f4;
        }
        .box-title {
            display: inline-block;
            font-size: 24px; /* Increased from 18px */
            margin: 0;
            line-height: 1;
            font-weight: 600; /* Added bold */
        }
        .box-body {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 3px;
            border-bottom-left-radius: 3px;
            padding: 15px; /* Increased from 10px */
        }
        .box-primary {
            border-top-color: #3c8dbc;
        }
        .box-success {
            border-top-color: #00a65a;
        }
        .box-warning {
            border-top-color: #f39c12;
        }
        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            font-size: 16px; /* Added explicit size for table */
        }
        .table > thead > tr > th,
        .table > tbody > tr > td {
            padding: 12px; /* Increased from default 8px */
            font-size: 16px; /* Explicit size for table cells */
        }
        .table > thead > tr > th {
            font-size: 18px; /* Larger for headers */
            font-weight: 600;
        }
        .btn {
            font-size: 16px; /* Increased button text */
            padding: 8px 16px; /* Increased button padding */
        }
        .btn-lg {
            font-size: 20px; /* Larger buttons */
            padding: 12px 20px;
        }
        .btn-back {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .modal-header.bg-green,
        .modal-header.bg-red,
        .modal-header.bg-blue {
            color: #fff;
        }
        .modal-header .close {
            color: #fff;
            opacity: 0.8;
            font-size: 32px; /* Increased close button size */
        }
        .modal-header .close:hover {
            opacity: 1;
        }
        .modal-lg {
            width: 900px;
        }
        .modal-title {
            font-size: 24px; /* Increased modal title */
            font-weight: 600;
        }
        .modal-body {
            font-size: 18px; /* Increased modal body text */
            line-height: 1.6;
        }
        .modal-body p {
            font-size: 18px;
            margin-bottom: 12px;
        }
        .modal-body strong {
            font-size: 19px;
        }
        .form-control {
            font-size: 16px; /* Increased form input text */
            padding: 10px 12px;
            height: auto;
        }
        textarea.form-control {
            font-size: 16px;
            min-height: 120px;
        }
        label {
            font-size: 17px; /* Increased label text */
            font-weight: 600;
            margin-bottom: 8px;
        }
        .bg-aqua {
            background-color: #00c0ef !important;
            color: #fff !important;
        }
        /* Badge dan notification */
        .label {
            font-size: 14px;
            padding: 4px 8px;
        }
        .badge {
            font-size: 14px;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="main-header">
        <div class="container-fluid">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h1 style="margin: 0; line-height: 1.2;">
                        <i class="fa fa-check-circle"></i> Director Dashboard
                        <small style="display: block; margin-top: 5px;">Approve or Decline Meeting Room Requests</small>
                    </h1>
                </div>
                
                {{-- Notification Bell --}}
                <div>
                    <a href="#" id="notification-bell" class="notification-bell" style="color: white; font-size: 32px; position: relative; display: inline-block; text-decoration: none;">
                        <i class="fa fa-bell-o"></i>
                        <span id="notification-badge" class="notification-badge" style="display: none; position: absolute; top: -8px; right: -8px; background: #f39c12; color: white; border-radius: 12px; padding: 4px 8px; font-size: 14px; font-weight: bold; min-width: 22px; text-align: center;">0</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
    {{-- Statistics Cards --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Requests</span>
                    <span class="info-box-number">{{ $stats['pending'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Approved Today</span>
                    <span class="info-box-number">{{ $stats['approved_today'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rejected Today</span>
                    <span class="info-box-number">{{ $stats['rejected_today'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total This Month</span>
                    <span class="info-box-number">{{ $stats['month_total'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Requests Table --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Pending Approval Requests</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if($pendingRequests->isEmpty())
                        <div class="alert alert-info" style="font-size: 18px; padding: 15px;">
                            <i class="fa fa-info-circle"></i> No pending requests at the moment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Request Date</th>
                                        <th width="10%">Room</th>
                                        <th width="15%">Requester</th>
                                        <th width="12%">Department</th>
                                        <th width="15%">Meeting Time</th>
                                        <th width="15%">Purpose</th>
                                        <th width="8%">Participants</th>
                                        <th width="8%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $index => $booking)
                                        <tr>
                                            <td style="font-size: 16px;">{{ $index + 1 }}</td>
                                            <td>
                                                <small style="font-size: 15px;">
                                                    {{ $booking->created_at->format('d M Y') }}<br>
                                                    {{ $booking->created_at->format('H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong class="text-primary" style="font-size: 17px;">{{ $booking->room_name }}</strong>
                                            </td>
                                            <td>
                                                <strong style="font-size: 17px;">{{ $booking->requester_name ?? $booking->user->name }}</strong><br>
                                                <small style="font-size: 15px;">{{ $booking->requester_position }}</small>
                                            </td>
                                            <td style="font-size: 16px;">{{ $booking->department }}</td>
                                            <td>
                                                <strong style="font-size: 17px;">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('d M Y') }}</strong><br>
                                                <span class="text-info" style="font-size: 16px;">
                                                    {{ \Carbon\Carbon::parse($booking->start_datetime)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}
                                                </span>
                                                <br>
                                                <small style="font-size: 14px;">
                                                    ({{ \Carbon\Carbon::parse($booking->start_datetime)->diffInMinutes($booking->end_datetime) }} minutes)
                                                </small>
                                            </td>
                                            <td>
                                                <small style="font-size: 15px;">{{ \Illuminate\Support\Str::limit($booking->purpose, 50) }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-blue" style="font-size: 16px; padding: 6px 10px;">{{ $booking->attendees_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group-vertical" role="group">
                                                    {{-- View Button --}}
                                                    <button type="button" 
                                                            class="btn btn-sm btn-info btn-view"
                                                            style="font-size: 15px; padding: 6px 12px; margin-bottom: 3px;"
                                                            data-id="{{ $booking->id }}"
                                                            data-room="{{ $booking->room_name }}"
                                                            data-requester="{{ $booking->requester_name ?? $booking->user->name }}"
                                                            data-position="{{ $booking->requester_position }}"
                                                            data-department="{{ $booking->department }}"
                                                            data-start="{{ \Carbon\Carbon::parse($booking->start_datetime)->format('d M Y, H:i') }}"
                                                            data-end="{{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}"
                                                            data-duration="{{ \Carbon\Carbon::parse($booking->start_datetime)->diffInMinutes($booking->end_datetime) }}"
                                                            data-purpose="{{ $booking->purpose }}"
                                                            data-attendees="{{ $booking->attendees_count }}"
                                                            data-description="{{ $booking->meeting_description }}"
                                                            data-needs="{{ $booking->meeting_needs }}"
                                                            data-created="{{ $booking->created_at->format('d M Y, H:i') }}"
                                                            data-toggle="tooltip" 
                                                            title="View Details">
                                                        <i class="fa fa-eye"></i> View
                                                    </button>
                                                    
                                                    {{-- Approve Button --}}
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success btn-approve"
                                                            style="font-size: 15px; padding: 6px 12px; margin-bottom: 3px;"
                                                            data-id="{{ $booking->id }}"
                                                            data-room="{{ $booking->room_name }}"
                                                            data-requester="{{ $booking->requester_name ?? $booking->user->name }}"
                                                            data-toggle="tooltip" 
                                                            title="Approve Request">
                                                        <i class="fa fa-check"></i> Approve
                                                    </button>
                                                    
                                                    {{-- Reject Button --}}
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger btn-reject"
                                                            style="font-size: 15px; padding: 6px 12px;"
                                                            data-id="{{ $booking->id }}"
                                                            data-room="{{ $booking->room_name }}"
                                                            data-requester="{{ $booking->requester_name ?? $booking->user->name }}"
                                                            data-toggle="tooltip" 
                                                            title="Reject Request">
                                                        <i class="fa fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity (Approved/Rejected Today) --}}
    <div class="row">
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check-circle"></i> Recently Approved</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if($recentApproved->isEmpty())
                        <p class="text-muted">No approved requests today.</p>
                    @else
                        <ul class="list-unstyled">
                            @foreach($recentApproved as $booking)
                                <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                    <strong class="text-success">{{ $booking->room_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $booking->requester_name ?? $booking->user->name }} - 
                                        {{ \Carbon\Carbon::parse($booking->start_datetime)->format('d M, H:i') }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-times-circle"></i> Recently Rejected</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if($recentRejected->isEmpty())
                        <p class="text-muted">No rejected requests today.</p>
                    @else
                        <ul class="list-unstyled">
                            @foreach($recentRejected as $booking)
                                <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                    <strong class="text-danger">{{ $booking->room_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $booking->requester_name ?? $booking->user->name }} - 
                                        {{ \Carbon\Carbon::parse($booking->start_datetime)->format('d M, H:i') }}
                                    </small>
                                    @if($booking->rejection_reason)
                                        <br><small class="text-danger">Reason: {{ $booking->rejection_reason }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

        {{-- Today's Meeting Timeline --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> Today's Meeting Schedule</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-primary">{{ \Carbon\Carbon::today()->format('l, F j, Y') }}</span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="timeline-container" style="position: relative; min-height: 800px; overflow-y: auto; background: #f9f9f9; border-radius: 6px; padding: 15px;">
                        @php
                            $todayBookings = \App\MeetingRoomBooking::whereDate('start_datetime', \Carbon\Carbon::today())
                                ->whereIn('status', ['pending', 'approved'])
                                ->orderBy('start_datetime')
                                ->get();
                            
                            // Group bookings by room to prevent overlap
                            $bookingsByRoom = $todayBookings->groupBy('room_name');
                            $rooms = !empty($roomNames ?? []) ? array_values($roomNames) : ['Ruang Meeting 1', 'Ruang Meeting 2', 'Ruang Meeting 3'];
                            $roomCount = max(count($rooms), 1);
                            $roomPalette = [
                                ['bg' => '#e3f2fd', 'border' => '#1976d2', 'text' => '#0d47a1'],
                                ['bg' => '#f3e5f5', 'border' => '#7b1fa2', 'text' => '#4a148c'],
                                ['bg' => '#fff3e0', 'border' => '#f57c00', 'text' => '#e65100'],
                                ['bg' => '#e8f5e9', 'border' => '#2e7d32', 'text' => '#1b5e20'],
                                ['bg' => '#fce4ec', 'border' => '#c2185b', 'text' => '#880e4f'],
                                ['bg' => '#e0f7fa', 'border' => '#0097a7', 'text' => '#006064'],
                                ['bg' => '#ede7f6', 'border' => '#5e35b1', 'text' => '#311b92'],
                                ['bg' => '#fffde7', 'border' => '#f9a825', 'text' => '#f57f17'],
                            ];
                            $roomColors = [
                                'Ruang Meeting 1' => ['bg' => '#e3f2fd', 'border' => '#1976d2', 'text' => '#0d47a1'],
                                'Ruang Meeting 2' => ['bg' => '#f3e5f5', 'border' => '#7b1fa2', 'text' => '#4a148c'],
                                'Ruang Meeting 3' => ['bg' => '#fff3e0', 'border' => '#f57c00', 'text' => '#e65100'],
                            ];

                            foreach ($rooms as $index => $roomName) {
                                if (!isset($roomColors[$roomName])) {
                                    $roomColors[$roomName] = $roomPalette[$index % count($roomPalette)];
                                }
                            }
                            
                            $hours = range(7, 18); // 7 AM to 6 PM
                        @endphp
                        
                        {{-- Timeline Grid with Room Columns --}}
                        <div style="position: relative;">
                            {{-- Header Row with Room Names --}}
                            <div style="display: flex; margin-bottom: 15px; position: sticky; top: 0; background: #fff; z-index: 100; padding: 10px 0; border-bottom: 3px solid #3c8dbc;">
                                <div style="width: 100px; flex-shrink: 0; font-weight: bold; font-size: 18px; color: #333; padding-left: 10px;">
                                    <i class="fa fa-clock-o"></i> Time
                                </div>
                                @foreach($rooms as $room)
                                    <div style="flex: 1; text-align: center; font-weight: bold; font-size: 18px; color: {{ $roomColors[$room]['text'] ?? '#333' }}; padding: 8px; background: {{ $roomColors[$room]['bg'] ?? '#f5f5f5' }}; border-radius: 6px; margin: 0 5px; border: 2px solid {{ $roomColors[$room]['border'] ?? '#ccc' }};">
                                        <i class="fa fa-door-open"></i> {{ $room }}
                                    </div>
                                @endforeach
                            </div>
                            
                            {{-- Hour Rows --}}
                            @foreach($hours as $hour)
                            <div class="timeline-row" style="display: flex; min-height: 80px; border-bottom: 2px solid #e0e0e0; position: relative;">
                                {{-- Time Label --}}
                                <div style="width: 100px; flex-shrink: 0; padding: 10px; font-weight: bold; font-size: 20px; color: #555; background: #fff; border-right: 3px solid #3c8dbc;">
                                    {{ sprintf('%02d:00', $hour) }}
                                </div>
                                
                                {{-- Room Columns --}}
                                @foreach($rooms as $roomIndex => $room)
                                    <div style="flex: 1; position: relative; border-right: 1px solid #e0e0e0; background: {{ $roomColors[$room]['bg'] ?? '#fff' }}; margin: 0 2px;">
                                        {{-- Bookings for this hour and room will be positioned absolutely --}}
                                    </div>
                                @endforeach
                            </div>
                            @endforeach
                            
                            {{-- Bookings Overlay - Positioned per Room Column --}}
                            @foreach($rooms as $roomIndex => $room)
                                @php
                                    $roomBookings = $bookingsByRoom->get($room, collect());
                                @endphp
                                
                                @foreach($roomBookings as $booking)
                                    @php
                                        $start = \Carbon\Carbon::parse($booking->start_datetime);
                                        $end = \Carbon\Carbon::parse($booking->end_datetime);
                                        $startHour = $start->hour + ($start->minute / 60);
                                        $endHour = $end->hour + ($end->minute / 60);
                                        $topPosition = (($startHour - 7) * 80) + 70; // 80px per hour + 70px header offset
                                        $height = ($endHour - $startHour) * 80; // 80px per hour
                                        
                                        $statusColors = [
                                            'pending' => ['bg' => '#fff3cd', 'border' => '#f39c12', 'text' => '#856404', 'badge' => '#f39c12'],
                                            'approved' => ['bg' => '#d4edda', 'border' => '#00a65a', 'text' => '#155724', 'badge' => '#00a65a'],
                                        ];
                                        $colors = $statusColors[$booking->status] ?? ['bg' => '#d1ecf1', 'border' => '#3c8dbc', 'text' => '#0c5460', 'badge' => '#3c8dbc'];
                                        
                                        $now = \Carbon\Carbon::now();
                                        $isOngoing = $now->between($start, $end);
                                    @endphp
                                    
                                    @if($topPosition >= 70 && $topPosition < 1000)
                                    <div class="timeline-booking" 
                                         style="position: absolute; 
                                               left: calc(100px + (100% - 100px) / {{ $roomCount }} * {{ $roomIndex }} + 8px); 
                                               width: calc((100% - 100px) / {{ $roomCount }} - 20px);
                                                top: {{ $topPosition }}px; 
                                                height: {{ max($height, 40) }}px;
                                                background: {{ $colors['bg'] }};
                                                border: 3px solid {{ $colors['border'] }};
                                                border-radius: 8px;
                                                padding: 10px;
                                                color: {{ $colors['text'] }};
                                                font-size: 14px;
                                                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                                overflow: hidden;
                                                cursor: pointer;
                                                transition: all 0.3s ease;
                                                {{ $isOngoing ? 'animation: pulse-booking 2s ease-in-out infinite; box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);' : '' }}"
                                         onmouseover="this.style.transform='scale(1.02)'; this.style.zIndex='50'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.3)';"
                                         onmouseout="this.style.transform='scale(1)'; this.style.zIndex='10'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                                         onclick="window.location='{{ route('meeting-room-bookings.show', $booking->id) }}'">
                                        
                                        {{-- Status Badge --}}
                                        <div style="position: absolute; top: 6px; right: 6px; background: {{ $colors['badge'] }}; color: white; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            @if($isOngoing)
                                                <i class="fa fa-circle" style="animation: blink 1s infinite;"></i> LIVE
                                            @else
                                                {{ ucfirst($booking->status) }}
                                            @endif
                                        </div>
                                        
                                        {{-- Booking Content --}}
                                        <div style="padding-right: 70px;">
                                            <div style="font-weight: bold; font-size: 16px; margin-bottom: 6px; line-height: 1.3; color: {{ $colors['text'] }};">
                                                <i class="fa fa-bullseye"></i> 
                                                {{ \Illuminate\Support\Str::limit($booking->purpose, 35) }}
                                            </div>
                                            
                                            <div style="font-size: 15px; margin-bottom: 4px; font-weight: 600;">
                                                <i class="fa fa-clock-o"></i> 
                                                {{ $start->format('H:i') }} - {{ $end->format('H:i') }}
                                                <span style="font-size: 12px; opacity: 0.8; font-weight: normal;">
                                                    ({{ $start->diffInMinutes($end) }} min)
                                                </span>
                                            </div>
                                            
                                            <div style="font-size: 14px; margin-bottom: 4px;">
                                                <i class="fa fa-user"></i> 
                                                {{ \Illuminate\Support\Str::limit($booking->requester_name ?? $booking->user->name ?? 'N/A', 25) }}
                                            </div>
                                            
                                            <div style="font-size: 13px; opacity: 0.9;">
                                                <i class="fa fa-users"></i> 
                                                {{ $booking->attendees_count }} peserta
                                                @if($booking->department)
                                                    | {{ \Illuminate\Support\Str::limit($booking->department, 15) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                        
                        <style>
                            @keyframes pulse-booking {
                                0%, 100% { 
                                    border-color: #e74c3c;
                                    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
                                }
                                50% { 
                                    border-color: #c0392b;
                                    box-shadow: 0 8px 25px rgba(231, 76, 60, 0.6);
                                }
                            }
                            
                            @keyframes blink {
                                0%, 50%, 100% { opacity: 1; }
                                25%, 75% { opacity: 0.3; }
                            }
                        </style>
                        
                        @if($todayBookings->isEmpty())
                        <div class="text-center" style="padding: 40px; color: #999;">
                            <i class="fa fa-calendar-times-o fa-3x"></i>
                            <p style="margin-top: 15px; font-size: 16px;">No meetings scheduled for today</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- View Details Modal --}}
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-eye"></i> Meeting Room Booking Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-primary"><i class="fa fa-info-circle"></i> Booking Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Room:</th>
                                    <td><strong id="view-room"></strong></td>
                                </tr>
                                <tr>
                                    <th>Meeting Date:</th>
                                    <td id="view-start"></td>
                                </tr>
                                <tr>
                                    <th>End Time:</th>
                                    <td id="view-end"></td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td><span id="view-duration"></span> minutes</td>
                                </tr>
                                <tr>
                                    <th>Total Attendees:</th>
                                    <td><span class="badge bg-blue" id="view-attendees"></span> persons</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4 class="text-success"><i class="fa fa-user"></i> Requester Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Name:</th>
                                    <td><strong id="view-requester"></strong></td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td id="view-position"></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td id="view-department"></td>
                                </tr>
                                <tr>
                                    <th>Request Submitted:</th>
                                    <td><small class="text-muted" id="view-created"></small></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-warning"><i class="fa fa-file-text"></i> Meeting Details</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="20%">Purpose:</th>
                                    <td id="view-purpose"></td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td id="view-description"></td>
                                </tr>
                                <tr>
                                    <th>Facility Needs:</th>
                                    <td id="view-needs"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-approve-from-view">
                        <i class="fa fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger btn-reject-from-view">
                        <i class="fa fa-times"></i> Reject
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf
                    <input type="hidden" name="from" value="director-dashboard">
                    <div class="modal-header bg-green">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-check-circle"></i> Approve Meeting Request</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <strong class="text-success">APPROVE</strong> this meeting room request?</p>
                        <div class="alert alert-info">
                            <strong>Room:</strong> <span id="approve-room"></span><br>
                            <strong>Requester:</strong> <span id="approve-requester"></span>
                        </div>
                        <div class="form-group">
                            <label>Approval Notes (Optional)</label>
                            <textarea name="approval_notes" class="form-control" rows="3" placeholder="Add any notes or special instructions..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Yes, Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <input type="hidden" name="from" value="director-dashboard">
                    <div class="modal-header bg-red">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-times-circle"></i> Reject Meeting Request</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <strong class="text-danger">REJECT</strong> this meeting room request?</p>
                        <div class="alert alert-warning">
                            <strong>Room:</strong> <span id="reject-room"></span><br>
                            <strong>Requester:</strong> <span id="reject-requester"></span>
                        </div>
                        <div class="form-group">
                            <label>Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                            <span class="help-block">This reason will be shown to the requester.</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-times"></i> Yes, Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>    
    </div> {{-- End container-fluid --}}

<script>
    $(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Store current booking ID for approve/reject from view modal
        var currentBookingId = null;
        var currentBookingRoom = null;
        var currentBookingRequester = null;

        // View button click
        $('.btn-view').click(function() {
            currentBookingId = $(this).data('id');
            currentBookingRoom = $(this).data('room');
            currentBookingRequester = $(this).data('requester');
            
            // Populate modal with booking details
            $('#view-room').text($(this).data('room'));
            $('#view-requester').text($(this).data('requester'));
            $('#view-position').text($(this).data('position'));
            $('#view-department').text($(this).data('department'));
            $('#view-start').text($(this).data('start'));
            $('#view-end').text($(this).data('end'));
            $('#view-duration').text($(this).data('duration'));
            $('#view-purpose').text($(this).data('purpose'));
            $('#view-attendees').text($(this).data('attendees'));
            $('#view-description').text($(this).data('description') || '-');
            $('#view-needs').text($(this).data('needs') || '-');
            $('#view-created').text($(this).data('created'));
            
            $('#viewModal').modal('show');
        });

        // Approve from view modal
        $('.btn-approve-from-view').click(function() {
            $('#viewModal').modal('hide');
            setTimeout(function() {
                $('#approve-room').text(currentBookingRoom);
                $('#approve-requester').text(currentBookingRequester);
                $('#approveForm').attr('action', '/meeting-room-bookings/' + currentBookingId + '/approve');
                $('#approveModal').modal('show');
            }, 300);
        });

        // Reject from view modal
        $('.btn-reject-from-view').click(function() {
            $('#viewModal').modal('hide');
            setTimeout(function() {
                $('#reject-room').text(currentBookingRoom);
                $('#reject-requester').text(currentBookingRequester);
                $('#rejectForm').attr('action', '/meeting-room-bookings/' + currentBookingId + '/reject');
                $('#rejectModal').modal('show');
            }, 300);
        });

        // Approve button click
        $('.btn-approve').click(function() {
            var id = $(this).data('id');
            var room = $(this).data('room');
            var requester = $(this).data('requester');
            
            $('#approve-room').text(room);
            $('#approve-requester').text(requester);
            $('#approveForm').attr('action', '/meeting-room-bookings/' + id + '/approve');
            $('#approveModal').modal('show');
        });

        // Reject button click
        $('.btn-reject').click(function() {
            var id = $(this).data('id');
            var room = $(this).data('room');
            var requester = $(this).data('requester');
            
            $('#reject-room').text(room);
            $('#reject-requester').text(requester);
            $('#rejectForm').attr('action', '/meeting-room-bookings/' + id + '/reject');
            $('#rejectModal').modal('show');
        });

        // Auto-refresh every 60 seconds
        setInterval(function() {
            location.reload();
        }, 60000);
    });
</script>

</body>
</html>
