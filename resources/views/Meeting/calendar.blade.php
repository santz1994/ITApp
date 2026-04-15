@extends('layouts.app')

@section('title', 'Jadwal Ruang Meeting | Meeting Room Schedule')

@section('head')
{{-- FullCalendar CSS --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    /* Enhanced FullCalendar Styling for Better Readability */
    .fc {
        font-size: 1em;
    }
    
    .fc-toolbar-title {
        font-size: 2em !important;
        font-weight: bold;
        color: #333;
    }
    
    /* Larger, clearer toolbar buttons */
    .fc-button {
        font-size: 16px !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
    }
    
    /* Better event styling with clear borders and spacing */
    .fc-event {
        cursor: pointer;
        font-size: 0.95em;
        padding: 4px 8px;
        border-radius: 6px;
        border: 2px solid rgba(0, 0, 0, 0.2) !important;
        margin: 2px 0 !important;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .fc-event:hover {
        opacity: 0.9;
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 100;
    }
    
    /* Day grid cells with better spacing */
    .fc-daygrid-day {
        min-height: 120px !important;
    }
    
    .fc-daygrid-day-number {
        font-size: 18px !important;
        font-weight: bold;
        padding: 8px !important;
    }
    
    /* Today's date highlighting */
    .fc-day-today {
        background-color: #fff8dc !important;
        border: 2px solid #ffa500 !important;
    }
    
    .fc-day-today .fc-daygrid-day-number {
        background: #ffa500;
        color: white;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Time grid view improvements */
    .fc-timegrid-slot {
        height: 50px !important;
    }
    
    .fc-timegrid-slot-label {
        font-size: 16px !important;
        font-weight: 700;
        color: #333;
    }
    
    .fc-timegrid-event {
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 15px;
        font-weight: 600;
        border: 2px solid rgba(0, 0, 0, 0.3) !important;
    }
    
    .fc-timegrid-event-harness {
        margin: 2px 4px !important;
    }
    
    /* Room column styling in timegrid */
    .fc-col-header-cell {
        font-size: 18px !important;
        font-weight: bold !important;
        padding: 15px 8px !important;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border: 2px solid #3c8dbc !important;
    }
    
    /* Separate room columns with distinct colors */
    .fc-timegrid-col:nth-child(2) {
        background: rgba(25, 118, 210, 0.05) !important;
    }
    
    .fc-timegrid-col:nth-child(3) {
        background: rgba(123, 31, 162, 0.05) !important;
    }
    
    .fc-timegrid-col:nth-child(4) {
        background: rgba(245, 124, 0, 0.05) !important;
    }
    
    /* Event content with larger text */
    .fc-event-title {
        font-weight: 700 !important;
        font-size: 15px !important;
        line-height: 1.4 !important;
    }
    
    .fc-event-time {
        font-weight: 700 !important;
        font-size: 14px !important;
    }
    
    /* List view improvements */
    .fc-list-event {
        font-size: 15px;
    }
    
    .fc-list-event:hover {
        background-color: #f0f0f0 !important;
    }
    
    .fc-list-event-time {
        font-weight: bold;
        font-size: 16px;
    }
    
    .fc-list-event-title {
        font-weight: 600;
    }
    
    /* Enhanced Legend Box */
    .legend-box {
        margin-top: 20px;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border: 2px solid #3c8dbc;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .legend-box strong {
        font-size: 18px;
        color: #333;
    }
    
    .legend-item {
        display: inline-block;
        margin-right: 25px;
        margin-bottom: 10px;
        margin-top: 10px;
        padding: 8px 15px;
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .legend-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .legend-color {
        display: inline-block;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        margin-right: 8px;
        vertical-align: middle;
        border: 2px solid rgba(0, 0, 0, 0.2);
    }
    
    .legend-item span:not(.legend-color) {
        font-size: 15px;
        font-weight: 600;
        vertical-align: middle;
    }
    
    /* Enhanced Room Filter */
    .room-filter {
        margin-bottom: 20px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .room-filter label {
        font-size: 17px;
        font-weight: bold;
        margin-right: 15px;
        color: #333;
    }
    
    .room-filter .btn-group .btn {
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: 15px;
        padding: 10px 20px;
        font-weight: 600;
        border: 2px solid #ddd;
        transition: all 0.3s ease;
    }
    
    .room-filter .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .room-filter .btn.active {
        background-color: #3c8dbc;
        color: white;
        border-color: #2c6a92;
        box-shadow: 0 4px 12px rgba(60, 141, 188, 0.4);
        transform: translateY(-2px);
    }
    
    /* Event colors by status - More distinct with better contrast */
    .fc-event.status-pending {
        background-color: #fff3cd !important;
        border-color: #f39c12 !important;
        color: #856404 !important;
    }
    
    .fc-event.status-approved {
        background-color: #d4edda !important;
        border-color: #00a65a !important;
        color: #155724 !important;
    }
    
    .fc-event.status-finished {
        background-color: #d1ecf1 !important;
        border-color: #3c8dbc !important;
        color: #0c5460 !important;
    }
    
    .fc-event.status-rejected {
        background-color: #f8d7da !important;
        border-color: #dd4b39 !important;
        color: #721c24 !important;
    }
    
    .fc-event.status-cancelled {
        background-color: #e2e3e5 !important;
        border-color: #999999 !important;
        color: #383d41 !important;
    }
    
    /* Enhanced room identification in events */
    .fc-event-title {
        font-weight: 700 !important;
        font-size: 15px !important;
        line-height: 1.5 !important;
        padding: 2px 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .fc-event-title:before {
        content: '📍';
        font-size: 14px;
    }
    
    /* Highlight current time in timegrid */
    .fc-timegrid-now-indicator-line {
        border-color: #e74c3c !important;
        border-width: 3px !important;
    }
    
    .fc-timegrid-now-indicator-arrow {
        border-color: #e74c3c !important;
        border-width: 8px !important;
    }
    
    /* Modal enhancements */
    .modal-header {
        background: linear-gradient(135deg, #3c8dbc 0%, #2c6a92 100%);
        color: white;
        border-radius: 6px 6px 0 0;
    }
    
    .modal-header .modal-title {
        font-size: 22px;
        font-weight: bold;
    }
    
    .modal-header .close {
        color: white;
        opacity: 0.9;
        font-size: 32px;
    }
    
    .modal-body {
        font-size: 16px;
        line-height: 1.8;
    }
    
    .modal-body .dl-horizontal dt {
        font-size: 16px;
        font-weight: 600;
        width: 180px;
    }
    
    .modal-body .dl-horizontal dd {
        font-size: 16px;
        margin-left: 200px;
    }
    
    .modal-body .label {
        font-size: 14px;
        padding: 6px 12px;
    }
</style>
@endsection

@section('main-content')

{{-- Page Header with Breadcrumbs (consistent with index page) --}}
@include('components.page-header', [
    'title' => 'Meeting Room Calendar',
    'subtitle' => 'Jadwal Ruang Meeting / Meeting Room Schedule',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Meeting Room Bookings', 'url' => route('meeting-room-bookings.index')],
        ['label' => 'Calendar']
    ],
    'actions' => '
        <a href="'.route('meeting-room-bookings.index').'" class="btn btn-default">
            <i class="fa fa-list"></i> List View
        </a>
        <a href="'.route('meeting-room-bookings.create').'" class="btn btn-success">
            <i class="fa fa-plus"></i> New Booking
        </a>
    '
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Quick Stats Cards (consistent with index page using small-box) --}}
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 id="todayCount">-</h3>
                    <p>Booking Hari Ini<br><small>Today's Bookings</small></p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 id="weekCount">-</h3>
                    <p>Booking Minggu Ini<br><small>This Week</small></p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 id="monthCount">-</h3>
                    <p>Booking Bulan Ini<br><small>This Month</small></p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-o"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ count($rooms) }}</h3>
                    <p>Total Ruangan<br><small>Total Rooms</small></p>
                </div>
                <div class="icon">
                    <i class="fa fa-building-o"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-calendar"></i> Kalender Booking / Booking Calendar
                    </h3>
                </div>
                <div class="box-body">
                    {{-- Room Filter --}}
                    <div class="room-filter">
                        <label style="font-weight: bold; margin-right: 10px;">
                            <i class="fa fa-filter"></i> Filter Ruangan / Filter Room:
                        </label>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default btn-sm active" data-room="all">
                                <i class="fa fa-building"></i> Semua / All Rooms
                            </button>
                            @foreach($rooms as $room)
                            <button type="button" class="btn btn-default btn-sm" data-room="{{ $room }}">
                                {{ $room }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Calendar Container --}}
                    <div id="calendar" style="margin-top: 20px;"></div>

                    {{-- Legend --}}
                    <div class="legend-box">
                        <strong><i class="fa fa-info-circle"></i> Keterangan Status / Status Legend:</strong>
                        <br><br>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #f39c12;"></span>
                            <span>Pending Approval</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #00a65a;"></span>
                            <span>Approved / Disetujui</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #3c8dbc;"></span>
                            <span>Finished / Selesai</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #dd4b39;"></span>
                            <span>Rejected / Ditolak</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #999999;"></span>
                            <span>Cancelled / Dibatalkan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Event Details Modal --}}
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-info-circle"></i> Detail Booking
                    </h4>
                </div>
                <div class="modal-body" id="eventDetails">
                    {{-- Content loaded via JavaScript --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    <a href="#" id="viewBookingBtn" class="btn btn-primary" target="_blank">
                        <i class="fa fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- FullCalendar JS with Scheduler for Resource Timeline --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var currentRoom = 'all';
    
    // Define room colors
    var roomColors = {
        'Ruang Meeting 1': { bg: '#e3f2fd', border: '#1976d2', text: '#0d47a1' },
        'Ruang Meeting 2': { bg: '#f3e5f5', border: '#7b1fa2', text: '#4a148c' },
        'Ruang Meeting 3': { bg: '#fff3e0', border: '#f57c00', text: '#e65100' }
    };
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'List'
        },
        locale: 'id',
        firstDay: 1, // Monday
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
        slotDuration: '00:30:00',
        slotLabelInterval: '01:00:00',
        allDaySlot: false,
        height: 'auto',
        
        // Enhanced display settings
        displayEventTime: true,
        displayEventEnd: true,
        eventDisplay: 'block',
        
        // Configure views
        views: {
            timeGridWeek: {
                titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            },
            timeGridDay: {
                titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            }
        },
        
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        events: function(info, successCallback, failureCallback) {
            fetch('{{ route("meeting-room-bookings.calendar.data") }}?' + 
                  'start=' + info.startStr + 
                  '&end=' + info.endStr +
                  '&room=' + currentRoom)
                .then(response => response.json())
                .then(data => {
                    // Add status classes and styling based on room
                    data.forEach(event => {
                        event.classNames = ['status-' + event.extendedProps.status];
                        
                        // Add room-specific styling
                        var room = event.extendedProps.room;
                        if (roomColors[room]) {
                            event.backgroundColor = roomColors[room].bg;
                            event.borderColor = roomColors[room].border;
                            event.textColor = roomColors[room].text;
                        }
                        
                        // Add room badge to title for clarity
                        var roomNumber = room.replace('Ruang Meeting ', '');
                        event.title = '🏢 R' + roomNumber + ' | ' + event.title;
                    });
                    successCallback(data);
                    updateStats(data);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            var event = info.event;
            var props = event.extendedProps;
            
            var statusClass = {
                'pending': 'warning',
                'approved': 'success',
                'rejected': 'danger',
                'cancelled': 'default',
                'finished': 'info'
            }[props.status] || 'default';
            
            var statusText = {
                'pending': 'Pending Approval',
                'approved': 'Approved / Disetujui',
                'rejected': 'Rejected / Ditolak',
                'cancelled': 'Cancelled / Dibatalkan',
                'finished': 'Finished / Selesai'
            }[props.status] || props.status;
            
            var html = `
                <dl class="dl-horizontal">
                    <dt>Status:</dt>
                    <dd><span class="label label-${statusClass}">${statusText}</span></dd>
                    
                    <dt>Ruangan / Room:</dt>
                    <dd><strong>${props.room}</strong></dd>
                    
                    <dt>Pemohon / Requester:</dt>
                    <dd>${props.requester}</dd>
                    
                    <dt>Bagian / Department:</dt>
                    <dd>${props.department}</dd>
                    
                    <dt>Waktu / Time:</dt>
                    <dd>
                        ${event.start.toLocaleString('id-ID', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                        <br>
                        s/d ${event.end.toLocaleString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </dd>
                    
                    <dt>Peserta / Attendees:</dt>
                    <dd>${props.attendees} orang</dd>
                    
                    <dt>Keperluan / Purpose:</dt>
                    <dd>${props.purpose}</dd>
                </dl>
            `;
            
            $('#eventDetails').html(html);
            $('#viewBookingBtn').attr('href', '{{ url("meeting-room-bookings") }}/' + props.booking_id);
            $('#eventModal').modal('show');
        }
    });
    
    calendar.render();
    
    // Room filter buttons
    $('.room-filter .btn').click(function() {
        $('.room-filter .btn').removeClass('active');
        $(this).addClass('active');
        currentRoom = $(this).data('room');
        calendar.refetchEvents();
    });
    
    // Update stats based on visible events
    function updateStats(events) {
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        
        var todayEnd = new Date(today);
        todayEnd.setDate(todayEnd.getDate() + 1);
        
        var weekStart = new Date(today);
        weekStart.setDate(today.getDate() - today.getDay() + 1);
        var weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 7);
        
        var monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        var monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        var todayCount = 0;
        var weekCount = 0;
        var monthCount = 0;
        
        events.forEach(function(event) {
            var eventStart = new Date(event.start);
            
            if (eventStart >= today && eventStart < todayEnd) {
                todayCount++;
            }
            if (eventStart >= weekStart && eventStart < weekEnd) {
                weekCount++;
            }
            if (eventStart >= monthStart && eventStart <= monthEnd) {
                monthCount++;
            }
        });
        
        $('#todayCount').text(todayCount);
        $('#weekCount').text(weekCount);
        $('#monthCount').text(monthCount);
    }
});
</script>
@endsection
