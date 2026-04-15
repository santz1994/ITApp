<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="600"> {{-- Auto refresh every 10 minutes (600 seconds) --}}
    <title>Jadwal Ruang Meeting 2 - LCD Dashboard</title>
    
    {{-- Bootstrap 3 CSS --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            padding: 15px;
            overflow-x: hidden;
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .dashboard-header h1 {
            font-size: 40px;
            font-weight: bold;
            margin: 0 0 8px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .dashboard-header .subtitle {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .current-time {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .current-date {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .room-section {
            margin-bottom: 20px;
        }
        
        .room-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            color: #333;
            min-height: 380px;
        }
        
        .room-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        
        .room-name {
            font-size: 22px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
        }
        
        .room-status {
            font-size: 13px;
            margin-top: 4px;
        }
        
        .status-available {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-occupied {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .booking-item {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 6px;
            transition: transform 0.2s;
        }
        
        .booking-item:hover {
            transform: translateX(5px);
        }
        
        .booking-item.current {
            background: #fff3cd;
            border-left-color: #ffc107;
            opacity: 0.85;
            animation: pulse 2s infinite;
        }
        
        .booking-item.approved {
            background: #ccf1dc;
            border-left-color: #27ae60;
            animation: pulse 2s infinite;
        }
        
        .booking-item.pending {
            background: #f1d9b2;
            border-left-color: #f39c12;
        }
        
        .booking-item.blocked {
            background: #ffb3d9;
            border-left-color: #e91e63;
            opacity: 0.85;
            animation: pulse 2s infinite;
        }
        
        .booking-item.blocked .booking-time {
            color: #c2185b;
        }
        
        .booking-item.blocked .booking-title {
            color: #880e4f;
            font-style: italic;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            50% { box-shadow: 0 0 15px 8px rgba(255, 193, 7, 0); }
        }
        
        .booking-time {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .booking-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #2c3e50;
        }
        
        .booking-details {
            font-size: 15px;
            color: #7f8c8d;
            margin-bottom: 2px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 6px;
        }
        
        .badge-pending {
            background: #f39c12;
            color: #fff;
        }
        
        .badge-approved {
            background: #27ae60;
            color: #fff;
        }
        
        .badge-current {
            background: #e74c3c;
            color: #fff;
            animation: blink 1.5s infinite;
        }
        
        .badge-blocked {
            background: #e91e63;
            color: #fff;
            animation: blink 1.5s infinite;
        }
        
        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }
        
        .no-bookings {
            text-align: center;
            padding: 25px 15px;
            color: #95a5a6;
            font-size: 14px;
        }
        
        .no-bookings i {
            font-size: 40px;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        
        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 11px;
            backdrop-filter: blur(10px);
        }
        
        .auto-refresh-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
        }
        
        .auto-refresh-progress {
            height: 100%;
            background: #27ae60;
            width: 0%;
            animation: refresh-progress 600s linear infinite;
        }
        
        @keyframes refresh-progress {
            from { width: 0%; }
            to { width: 100%; }
        }
        
        /* Running Text Styles */
        .running-text-container {
            background: rgba(255, 255, 255, 0.15);
            padding: 12px 0;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .running-text {
            display: flex;
            align-items: center;
            white-space: nowrap;
            animation: scroll-left 40s linear infinite;
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .running-text i {
            margin: 0 12px;
            color: #ffd700;
            font-size: 24px;
        }
        
        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        /* 5 rooms layout - 2 rows: 3 top, 2 bottom */
        .row-top-3 .room-section {
            margin-bottom: 20px;
        }
        
        .row-bottom-2 .room-section {
            margin-bottom: 20px;
        }
        
        @media (min-width: 1200px) {
            .row-top-3 .room-section {
                width: 33.333%;
                float: left;
            }
            .row-bottom-2 {
                max-width: 66.666%;
                margin: 0 auto;
            }
            .row-bottom-2 .room-section {
                width: 50%;
                float: left;
            }
        }
        
        @media (max-width: 1199px) {
            .row-top-3 .room-section,
            .row-bottom-2 .room-section {
                width: 50%;
                float: left;
            }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="dashboard-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i>
            JADWAL RUANG MEETING
        </h1>
        <div style="margin-top: 12px;">
            <div class="current-time" id="currentTime"></div>
            <div class="current-date" id="currentDate"></div>
        </div>
    </div>

    {{-- Room Sections - Top Row: 3 Rooms --}}
    <div class="row row-top-3">
        @php
            $topRooms = ['Ruang Meeting 1', 'Ruang Meeting 2', 'Ruang Meeting 3'];
        @endphp
        
        @foreach($topRooms as $roomName)
        <div class="room-section">
            <div class="room-card">
                <div class="room-header">
                    <h2 class="room-name">
                        <i class="fa fa-door-open"></i> {{ $roomName }}
                    </h2>
                    @php
                        $roomBookings = $bookings->where('room_name', $roomName)
                            ->where('start_datetime', '<=', now()->addHours(12))
                            ->sortBy('start_datetime');
                        
                        $currentBooking = $roomBookings->first(function($b) {
                            return $b->start_datetime <= now() && $b->end_datetime >= now();
                        });
                    @endphp
                    <div class="room-status">
                        @if($currentBooking)
                            <i class="fa fa-circle status-occupied"></i>
                            <span class="status-occupied">SEDANG DIGUNAKAN</span>
                        @else
                            <i class="fa fa-circle status-available"></i>
                            <span class="status-available">TERSEDIA</span>
                        @endif
                    </div>
                </div>

                {{-- Today's Bookings --}}
                @if($roomBookings->count() > 0)
                    @foreach($roomBookings->take(3) as $booking)
                    @php
                        $isCurrent = $booking->start_datetime <= now() && $booking->end_datetime >= now();
                        $isPast = $booking->end_datetime < now();
                        $isBlocked = str_starts_with($booking->purpose, 'BLOCKED:');
                    @endphp
                    
                    @if(!$isPast)
                    <div class="booking-item {{ $isBlocked ? 'blocked' : ($isCurrent ? 'current' : ($booking->status == 'approved' ? 'approved' : 'pending')) }}">
                        <div class="booking-time">
                            <i class="fa fa-clock-o"></i>
                            {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                            @if($isBlocked)
                                <span class="status-badge badge-blocked">
                                    <i class="fa fa-ban"></i> BLOCKED - BERLANGSUNG
                                </span>
                            @elseif($isCurrent)
                                <span class="status-badge badge-current">
                                    <i class="fa fa-circle"></i> BERLANGSUNG
                                </span>
                            @elseif($booking->status == 'approved')
                                <span class="status-badge badge-approved">DISETUJUI - TERJADWAL</span>
                            @else
                                <span class="status-badge badge-pending">PENDING</span>
                            @endif
                        </div>
                        <div class="booking-title">
                            {{ \Illuminate\Support\Str::limit($booking->purpose, 35) }}
                        </div>
                        <div class="booking-details">
                            <i class="fa fa-user"></i> {{ $booking->user->name }} 
                            <span style="margin-left: 8px;">
                                <i class="fa fa-building"></i> {{ $booking->department }}
                            </span>
                        </div>
                        <div class="booking-details">
                            <i class="fa fa-users"></i> {{ $booking->attendees_count }} Peserta
                        </div>
                    </div>
                    @endif
                    @endforeach
                @else
                    <div class="no-bookings">
                        <div><i class="fa fa-calendar-times-o"></i></div>
                        <div>Tidak ada booking hari ini</div>
                        <div style="font-size: 13px; margin-top: 5px;">No bookings today</div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Room Sections - Bottom Row: 2 Rooms --}}
    <div class="row row-bottom-2">
        @php
            $bottomRooms = ['Ruang Meeting 4', 'Ruang Meeting 5'];
        @endphp
        
        @foreach($bottomRooms as $roomName)
        <div class="room-section">
            <div class="room-card">
                <div class="room-header">
                    <h2 class="room-name">
                        <i class="fa fa-door-open"></i> {{ $roomName }}
                    </h2>
                    @php
                        $roomBookings = $bookings->where('room_name', $roomName)
                            ->where('start_datetime', '<=', now()->addHours(12))
                            ->sortBy('start_datetime');
                        
                        $currentBooking = $roomBookings->first(function($b) {
                            return $b->start_datetime <= now() && $b->end_datetime >= now();
                        });
                    @endphp
                    <div class="room-status">
                        @if($currentBooking)
                            <i class="fa fa-circle status-occupied"></i>
                            <span class="status-occupied">SEDANG DIGUNAKAN</span>
                        @else
                            <i class="fa fa-circle status-available"></i>
                            <span class="status-available">TERSEDIA</span>
                        @endif
                    </div>
                </div>

                {{-- Today's Bookings --}}
                @if($roomBookings->count() > 0)
                    @foreach($roomBookings->take(3) as $booking)
                    @php
                        $isCurrent = $booking->start_datetime <= now() && $booking->end_datetime >= now();
                        $isPast = $booking->end_datetime < now();
                        $isBlocked = str_starts_with($booking->purpose, 'BLOCKED:');
                    @endphp
                    
                    @if(!$isPast)
                    <div class="booking-item {{ $isBlocked ? 'blocked' : ($isCurrent ? 'current' : ($booking->status == 'approved' ? 'approved' : 'pending')) }}">
                        <div class="booking-time">
                            <i class="fa fa-clock-o"></i>
                            {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                            @if($isBlocked)
                                <span class="status-badge badge-blocked">
                                    <i class="fa fa-ban"></i> BLOCKED
                                </span>
                            @elseif($isCurrent)
                                <span class="status-badge badge-current">
                                    <i class="fa fa-circle"></i> NOW
                                </span>
                            @elseif($booking->status == 'approved')
                                <span class="status-badge badge-approved">OK</span>
                            @else
                                <span class="status-badge badge-pending">PENDING</span>
                            @endif
                        </div>
                        <div class="booking-title">
                            {{ \Illuminate\Support\Str::limit($booking->purpose, 35) }}
                        </div>
                        <div class="booking-details">
                            <i class="fa fa-user"></i> {{ $booking->user->name }} 
                            <span style="margin-left: 8px;">
                                <i class="fa fa-building"></i> {{ $booking->department }}
                            </span>
                        </div>
                        <div class="booking-details">
                            <i class="fa fa-users"></i> {{ $booking->attendees_count }} Peserta
                        </div>
                    </div>
                    @endif
                    @endforeach
                @else
                    <div class="no-bookings">
                        <div><i class="fa fa-calendar-times-o"></i></div>
                        <div>Tidak ada booking hari ini</div>
                        <div style="font-size: 13px; margin-top: 5px;">No bookings today</div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

        {{-- Running Text Announcement --}}
    <div class="running-text-container">
        <div class="running-text">
            <i class="fa fa-exclamation-triangle"></i>
            <span>PERMOHONAN HARUS DIAJUKAN MINIMAL 15 MENIT SEBELUMNYA</span>
            <i class="fa fa-clock-o"></i>
            <span>REQUESTS MUST BE SUBMITTED AT LEAST 15 MINUTES IN ADVANCE</span>
            <i class="fa fa-exclamation-triangle"></i>
            <span>PERMOHONAN HARUS DIAJUKAN MINIMAL 15 MENIT SEBELUMNYA</span>
            <i class="fa fa-clock-o"></i>
            <span>REQUESTS MUST BE SUBMITTED AT LEAST 15 MINUTES IN ADVANCE</span>
        </div>
    </div>

    {{-- Refresh Indicator --}}
    <div class="refresh-indicator">
        <i class="fa fa-refresh fa-spin"></i> Live
    </div>

    {{-- Auto Refresh Progress Bar --}}
    <div class="auto-refresh-bar">
        <div class="auto-refresh-progress"></div>
    </div>

    {{-- Scripts --}}
    <script>
        // Update clock
        function updateClock() {
            const now = new Date();
            
            // Time - Format as HH:MM:SS
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('currentTime').innerHTML = `${hours}:${minutes}<span style="font-size: 0.7em;">:${seconds}</span>`;
            
            // Date
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', dateOptions);
        }
        
        // Update every second
        updateClock();
        setInterval(updateClock, 1000);
        
        // Manual refresh button (optional)
        document.addEventListener('keypress', function(e) {
            if(e.key === 'r' || e.key === 'R') {
                location.reload();
            }
        });
    </script>
</body>
</html>
