<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="600"> {{-- Auto refresh every 10 minutes (600 seconds) --}}
    <title>Jadwal Ruang Meeting - LCD Dashboard</title>
    
    {{-- Bootstrap 3 CSS --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            padding: 20px;
            overflow-x: hidden;
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .dashboard-header h1 {
            font-size: 48px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .dashboard-header .subtitle {
            font-size: 24px;
            opacity: 0.9;
        }
        
        .current-time {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .current-date {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .room-section {
            margin-bottom: 30px;
        }

        .room-carousel {
            margin-bottom: 30px;
        }

        .carousel-meta {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 15px;
            font-weight: 600;
            color: #fefefe;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.35);
        }

        .carousel-slide {
            display: none;
            animation: carouselFade 0.5s ease;
        }

        .carousel-slide.active {
            display: block;
        }

        .carousel-slide-grid {
            display: flex;
            gap: 16px;
        }

        .carousel-slide-grid .room-section {
            margin-bottom: 0;
            padding: 0;
        }

        .carousel-dots {
            margin-top: 16px;
            text-align: center;
        }

        .carousel-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 6px;
            background: rgba(255, 255, 255, 0.35);
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
        }

        .carousel-dot.active {
            background: #ffd700;
            transform: scale(1.2);
        }

        @keyframes carouselFade {
            from { opacity: 0.45; }
            to { opacity: 1; }
        }
        
        .room-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: #333;
            min-height: 400px;
        }
        
        .room-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .room-name {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
        }
        
        .room-status {
            font-size: 18px;
            margin-top: 5px;
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
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
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
            50% { box-shadow: 0 0 20px 10px rgba(255, 193, 7, 0); }
        }
        
        .booking-time {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .booking-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .booking-details {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 3px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
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
            padding: 40px;
            color: #95a5a6;
            font-size: 18px;
        }
        
        .no-bookings i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            font-size: 14px;
        }
        
        .refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 12px;
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
            padding: 15px 0;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .running-text {
            display: flex;
            align-items: center;
            white-space: nowrap;
            animation: scroll-left 40s linear infinite;
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .running-text i {
            margin: 0 15px;
            color: #ffd700;
            font-size: 28px;
        }
        
        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        .maintenance-fallback {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999;
            background: radial-gradient(circle at top right, #2f3c7e 0%, #10142c 60%, #080b1a 100%);
            color: #fff;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
        }

        .maintenance-fallback .title {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .maintenance-fallback .subtitle {
            font-size: 22px;
            margin-bottom: 12px;
        }

        .maintenance-fallback .meta {
            font-size: 16px;
            opacity: 0.85;
        }

        @media (max-width: 991px) {
            .carousel-slide-grid {
                flex-direction: column;
            }

            .carousel-slide-grid .room-section {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div id="maintenanceFallback" class="maintenance-fallback">
        <div>
            <div style="font-size: 90px; margin-bottom: 12px;"><i class="fa fa-wrench"></i></div>
            <div class="title">UNDER MAINTENANCE</div>
            <div class="subtitle">LCD Dashboard sedang dipulihkan karena terjadi error.</div>
            <div class="meta">Auto reload setiap 60 detik.</div>
        </div>
    </div>

    {{-- Header --}}
    <div class="dashboard-header">
        <h1>
            JADWAL RUANG MEETING
        </h1>
        <div style="margin-top: 12px;">
            <div class="current-time" id="currentTime"></div>
            <div class="current-date" id="currentDate"></div>
        </div>
    </div>

    {{-- Room Carousel Sections --}}
    @php
        $roomsPerSlide = max((int) ($lcdGlobalSettings['rooms_per_slide'] ?? 2), 1);
        $slideIntervalSeconds = max((int) ($lcdGlobalSettings['slide_interval_seconds'] ?? 10), 5);
        $roomSlides = array_chunk($rooms, $roomsPerSlide);
        if (empty($roomSlides)) {
            $roomSlides = [[]];
        }
    @endphp

    <div class="room-carousel" id="roomCarousel" data-interval="{{ $slideIntervalSeconds }}">
        @foreach($roomSlides as $slideIndex => $slideRooms)
            @php
                $roomsInSlide = max(count($slideRooms), 1);
            @endphp
            <div class="carousel-slide {{ $slideIndex === 0 ? 'active' : '' }}" data-slide-index="{{ $slideIndex }}">
                <div class="carousel-slide-grid">
                    @forelse($slideRooms as $roomName)
                        <div class="room-section" style="width: calc(100% / {{ $roomsInSlide }});">
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
                                    @foreach($roomBookings->take(5) as $booking)
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
                                                <span class="status-badge badge-approved"> DISETUJUI - TERJADWAL</span>
                                            @else
                                                <span class="status-badge badge-pending"> PENDING</span>
                                            @endif
                                        </div>
                                        <div class="booking-title">
                                            {{ \Illuminate\Support\Str::limit($booking->purpose, 50) }}
                                        </div>
                                        <div class="booking-details">
                                            <i class="fa fa-user"></i> {{ $booking->user->name }}
                                            <span style="margin-left: 10px;">
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
                                        <div style="font-size: 14px; margin-top: 5px;">No bookings today</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="room-section" style="width: 100%;">
                            <div class="room-card">
                                <div class="no-bookings">
                                    <div><i class="fa fa-exclamation-triangle"></i></div>
                                    <div>Belum ada ruang meeting aktif</div>
                                    <div style="font-size: 14px; margin-top: 5px;">Silakan atur di menu LCD Settings</div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <div class="carousel-dots" id="carouselDots">
            @for($dotIndex = 0; $dotIndex < count($roomSlides); $dotIndex++)
                <span class="carousel-dot {{ $dotIndex === 0 ? 'active' : '' }}" data-slide-to="{{ $dotIndex }}"></span>
            @endfor
        </div>
    </div>

    {{-- Running Text Announcement --}}
    <div class="running-text-container">
        <div class="running-text">
            <i class="fa fa-exclamation-triangle"></i>
            <span>PERMOHONAN WAJIB DIAJUKAN MINIMAL 15 MENIT SEBELUMNYA</span>
            <i class="fa fa-clock-o"></i>
            <span>REQUESTS MUST BE SUBMITTED AT LEAST 15 MINUTES IN ADVANCE</span>
            <i class="fa fa-exclamation-triangle"></i>
            <span>PERMOHONAN WAJIB DIAJUKAN MINIMAL 15 MENIT SEBELUMNYA</span>
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
        let maintenanceShown = false;

        function showMaintenanceFallback() {
            if (maintenanceShown) {
                return;
            }

            maintenanceShown = true;
            var fallback = document.getElementById('maintenanceFallback');
            if (fallback) {
                fallback.style.display = 'flex';
            }
            setTimeout(function() {
                window.location.reload();
            }, 60000);
        }

        function initRoomCarousel() {
            try {
                var carousel = document.getElementById('roomCarousel');
                if (!carousel) {
                    return;
                }

                var slides = Array.prototype.slice.call(carousel.querySelectorAll('.carousel-slide'));
                if (slides.length === 0) {
                    return;
                }

                var dots = Array.prototype.slice.call(document.querySelectorAll('.carousel-dot'));
                var pageInfo = document.getElementById('carouselPageInfo');
                var intervalSeconds = parseInt(carousel.getAttribute('data-interval'), 10) || 10;
                var currentIndex = 0;
                var timerId = null;

                function renderSlide(index) {
                    slides.forEach(function (slide, slideIndex) {
                        slide.classList.toggle('active', slideIndex === index);
                    });

                    dots.forEach(function (dot, dotIndex) {
                        dot.classList.toggle('active', dotIndex === index);
                    });

                    if (pageInfo) {
                        pageInfo.innerHTML = '<i class="fa fa-play-circle"></i> Slide ' + (index + 1) + ' / ' + slides.length;
                    }

                    currentIndex = index;
                }

                function moveNext() {
                    if (slides.length <= 1) {
                        return;
                    }
                    var nextIndex = (currentIndex + 1) % slides.length;
                    renderSlide(nextIndex);
                }

                function resetTimer() {
                    if (timerId) {
                        clearInterval(timerId);
                        timerId = null;
                    }

                    if (slides.length > 1) {
                        timerId = setInterval(moveNext, intervalSeconds * 1000);
                    }
                }

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        var target = parseInt(dot.getAttribute('data-slide-to'), 10);
                        if (Number.isNaN(target)) {
                            return;
                        }
                        renderSlide(target);
                        resetTimer();
                    });
                });

                renderSlide(0);
                resetTimer();
            } catch (error) {
                showMaintenanceFallback();
            }
        }

        // Update clock
        function updateClock() {
            try {
                const now = new Date();

                // Time - Format as HH:MM:SS
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                const currentTimeEl = document.getElementById('currentTime');
                const currentDateEl = document.getElementById('currentDate');

                if (!currentTimeEl || !currentDateEl) {
                    throw new Error('Clock elements not found');
                }

                currentTimeEl.innerHTML = `${hours}:${minutes}<span style="font-size: 0.7em;">:${seconds}</span>`;

                // Date
                const dateOptions = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                currentDateEl.textContent = now.toLocaleDateString('id-ID', dateOptions);
            } catch (error) {
                showMaintenanceFallback();
            }
        }

        window.addEventListener('error', function () {
            showMaintenanceFallback();
        });

        window.addEventListener('unhandledrejection', function () {
            showMaintenanceFallback();
        });

        initRoomCarousel();

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
