@extends('layouts.app')

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Meeting Room Bookings',
    'subtitle' => 'Manage meeting room booking requests',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Meeting Room Bookings']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('meeting-room-bookings.create').'" class="btn btn-success">
                <i class="fa fa-plus"></i> <span class="hidden-xs">New Booking</span>
            </a>
            <a href="'.route('meeting-room-bookings.calendar').'" class="btn btn-primary">
                <i class="fa fa-calendar"></i> <span class="hidden-xs">Calendar</span>
            </a>
        </div>
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

    {{-- Quick Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua" onclick="filterByStatus('all')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow" onclick="filterByStatus('pending')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                    <p>Pending Approval</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green" onclick="filterByStatus('approved')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['approved'] ?? 0 }}</h3>
                    <p>Approved</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red" onclick="filterByStatus('rejected')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['rejected'] ?? 0 }}</h3>
                    <p>Rejected</p>
                </div>
                <div class="icon">
                    <i class="fa fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-xs-6">
            <div class="small-box bg-blue" onclick="filterByStatus('finished')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['finished'] ?? 0 }}</h3>
                    <p>Finished</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-xs-6">
            <div class="small-box bg-gray" onclick="filterByStatus('cancelled')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['cancelled'] ?? 0 }}</h3>
                    <p>Cancelled</p>
                </div>
                <div class="icon">
                    <i class="fa fa-ban"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-xs-6">
            <div class="small-box bg-purple" onclick="filterByStatus('today')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['today'] ?? 0 }}</h3>
                    <p>Today's Bookings</p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-o"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-calendar-check-o"></i> 
                        Booking Requests
                        <span class="badge bg-blue" id="bookingCount">{{ $bookings->count() }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <div class="btn-group" role="group" style="margin-right: 5px;">
                            @role(['receptionist', 'admin', 'super-admin'])
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#monthlyReportModal">
                                <i class="fa fa-file-excel-o"></i> Export
                            </button>
                            @endrole
                        </div>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="box-body">
                    {{-- Filter Tabs --}}
                    <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                        <li class="{{ (!request('tab') || request('tab') == 'all') ? 'active' : '' }}">
                            <a href="{{ route('meeting-room-bookings.index') }}" data-tab="all">
                                <i class="fa fa-list"></i> All Bookings
                                <span class="badge bg-blue">{{ $stats['total'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="{{ request('tab') == 'my-bookings' ? 'active' : '' }}">
                            <a href="{{ route('meeting-room-bookings.index', ['tab' => 'my-bookings']) }}" data-tab="my-bookings">
                                <i class="fa fa-user"></i> My Bookings
                            </a>
                        </li>
                        @if(user_has_role(Auth::user(), 'director') || user_has_role(Auth::user(), 'admin') || user_has_role(Auth::user(), 'super-admin'))
                        <li class="{{ request('tab') == 'pending' ? 'active' : '' }}">
                            <a href="{{ route('meeting-room-bookings.index', ['tab' => 'pending']) }}" data-tab="pending">
                                <i class="fa fa-clock-o"></i> Pending Approval
                                @if(isset($stats['pending']) && $stats['pending'] > 0)
                                    <span class="badge bg-yellow">{{ $stats['pending'] }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                    </ul>

                    {{-- DataTable --}}
                    <div class="table-responsive">
                        <table id="bookingsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Room</th>
                                    <th>Date & Time</th>
                                    <th>Requester</th>
                                    <th>Department</th>
                                    <th>Duration</th>
                                    <th>Attendees</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->id }}</td>
                                    <td>
                                        <strong>{{ $booking->room_name }}</strong>
                                    </td>
                                    <td>
                                        <i class="fa fa-calendar"></i> 
                                        {{ $booking->start_datetime->format('d M Y') }}
                                        <br>
                                        <i class="fa fa-clock-o"></i> 
                                        {{ $booking->start_datetime->format('H:i') }} - 
                                        {{ $booking->end_datetime->format('H:i') }}
                                    </td>
                                    <td>
                                        {{ $booking->user->name ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ $booking->requester_position ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $booking->department ?? 'N/A' }}</td>
                                    <td>{{ $booking->duration }}</td>
                                    <td>
                                        <span class="badge bg-blue">{{ $booking->attendees_count }}</span> persons
                                    </td>
                                    <td>
                                        <span class="label {{ $booking->statusBadge }}" 
                                              style="cursor: pointer; font-size: 11px;"
                                              onclick="filterByStatus('{{ $booking->status }}')">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- View Button --}}
                                            <a href="{{ route('meeting-room-bookings.show', $booking->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            {{-- Approve/Reject (Director Only, if Pending) --}}
                                            @if(user_has_any_role(Auth::user(), ['director', 'admin', 'super-admin']) && $booking->status == 'pending')
                                                <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="approveBooking({{ $booking->id }})"
                                                        title="Approve">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="rejectBooking({{ $booking->id }})"
                                                        title="Reject">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            @endif

                                            {{-- Edit (Owner if Pending & Future, OR Superadmin anytime) --}}
                                            @if(
                                                ($booking->user_id == Auth::id() && $booking->canBeEdited()) ||
                                                user_has_role(Auth::user(), 'super-admin')
                                            )
                                                <a href="{{ route('meeting-room-bookings.edit', $booking->id) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif

                                            {{-- Print (Receptionist/Admin/Owner) --}}
                                            @if(user_has_any_role(Auth::user(), ['receptionist', 'admin', 'super-admin']) || $booking->user_id == Auth::id())
                                                <a href="{{ route('meeting-room-bookings.print', $booking->id) }}" 
                                                   class="btn btn-sm btn-default" 
                                                   target="_blank"
                                                   title="Print">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                            @endif

                                            {{-- Finish Button (Receptionist/Admin only - for ongoing meetings) --}}
                                            @if(user_has_any_role(Auth::user(), ['receptionist', 'admin', 'super-admin']) 
                                                && $booking->status == 'approved' 
                                                && $booking->start_datetime <= now() 
                                                && $booking->end_datetime >= now())
                                                <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="finishBooking({{ $booking->id }})"
                                                        title="Finish Meeting">
                                                    <i class="fa fa-check-circle"></i>
                                                </button>
                                            @endif

                                            {{-- Cancel Button (Owner if can be edited) --}}
                                            @if($booking->user_id == Auth::id() && $booking->canBeEdited())
                                                <button type="button"
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="deleteBooking({{ $booking->id }}, 'cancel')"
                                                        title="Cancel">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif

                                            {{-- Delete Button (Super-admin only) --}}
                                            @if(user_has_role(Auth::user(), 'super-admin'))
                                                <button type="button"
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="deleteBooking({{ $booking->id }}, 'delete')"
                                                        title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- Hidden forms for delete actions --}}
                                        <form id="delete-form-{{ $booking->id }}" 
                                              action="{{ route('meeting-room-bookings.destroy', $booking->id) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="empty-state">
                                            <i class="fa fa-calendar fa-3x text-muted"></i>
                                            <h4>No Bookings Found</h4>
                                            <p>Start by creating your first booking request.</p>
                                            <a href="{{ route('meeting-room-bookings.create') }}" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Create Booking
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header bg-green">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-check-circle"></i> Approve Booking Request
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="director_notes" class="form-control" rows="3" 
                                  placeholder="Add notes for approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header bg-red">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-times-circle"></i> Reject Booking Request
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="director_notes" class="form-control" rows="3" 
                                  placeholder="Explain why this booking is rejected..." 
                                  required minlength="10"></textarea>
                        <small class="text-muted">Minimum 10 characters required</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Monthly Report Modal --}}
<div class="modal fade" id="monthlyReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #00a65a; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-file-excel-o"></i> Download Laporan Bulanan Excel
                </h4>
            </div>
            <div class="modal-body">
                <form id="monthlyReportForm" method="GET" action="{{ route('meeting-room-bookings.report.monthly-excel') }}">
                    <div class="form-group">
                        <label for="report_month">Pilih Bulan:</label>
                        <select class="form-control" id="report_month" name="month" required>
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
                        <label for="report_year">Pilih Tahun:</label>
                        <select class="form-control" id="report_year" name="year" required>
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" {{ now()->year == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        Laporan akan berisi data booking dengan status <strong>Approved</strong> atau <strong>Finished</strong> pada bulan yang dipilih.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnDownloadReport">
                    <i class="fa fa-download"></i> Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.nav-tabs > li > a {
    transition: all 0.3s ease;
}

.nav-tabs > li.active > a,
.nav-tabs > li.active > a:hover,
.nav-tabs > li.active > a:focus {
    background-color: #3c8dbc;
    color: white;
    border-color: #3c8dbc;
    font-weight: bold;
}

.nav-tabs > li > a:hover {
    background-color: #f4f4f4;
}

.nav-tabs > li > a .badge {
    margin-left: 5px;
}

.small-box {
    transition: all 0.3s ease;
}

.small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/datatable-enhancements.js') }}?v={{ time() }}"></script>
<script>
$(document).ready(function() {
    var table;
    
    // Phase 1: Quick DOM cleanup (non-blocking)
    var $table = $('#bookingsTable');
    var $emptyRows = $table.find('tbody tr').filter(function() {
        return $(this).find('td[colspan]').length > 0;
    });
    if ($emptyRows.length > 0) {
        $emptyRows.remove();
    }
    
    // Phase 2: Initialize DataTable with minimal config (fast)
    setTimeout(function() {
        table = initEnhancedDataTable('#bookingsTable', {
            pageLength: 25,
            exportFileName: 'Meeting_Room_Bookings',
            countBadgeSelector: '#bookingCount',
            countBadgeText: ' Bookings',
            emptyTableText: '<div class="empty-state"><i class="fa fa-inbox fa-3x text-muted"></i><h4>No Bookings Found</h4><p class="text-muted">There are no meeting room bookings yet. Click "New Booking" to create one.</p></div>',
            columnDefs: [
                { orderable: false, targets: [8] }, // Actions column
                { className: 'text-center', targets: [0, 6, 7, 8] }
            ],
            order: [[0, 'desc']], // Sort by ID descending
            deferRender: true,
            processing: false,
            responsive: false,
            autoWidth: false,
            stateSave: false, // Disable state saving for better performance
            search: { smart: false } // Disable smart search for faster filtering
        });
        
        // Set up global functions after table is initialized
        window.filterByStatus = function(status) {
            if (table) {
                if (status === 'all') {
                    table.search('').draw();
                } else {
                    table.search(status).draw();
                }
            }
        };
    }, 0); // Defer to next tick

    // Set up modal functions immediately (don't wait for DataTable)
    window.approveBooking = function(id) {
        $('#approveForm').attr('action', '/meeting-room-bookings/' + id + '/approve');
        $('#approveModal').modal('show');
    };

    window.rejectBooking = function(id) {
        $('#rejectForm').attr('action', '/meeting-room-bookings/' + id + '/reject');
        $('#rejectModal').modal('show');
    };

    // Tab navigation with loading indicator
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        var $link = $(this);
        var href = $link.attr('href');
        
        // Show loading overlay
        $('body').append('<div class="tab-loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);"><i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i><span style="font-size: 16px;">Loading...</span></div></div>');
        
        // Navigate to the URL
        window.location.href = href;
    });
    
    // Finish booking function
    window.finishBooking = function(id) {
        if (confirm('Finish this meeting now?\n\nMeeting will be marked as FINISHED even if it hasn\'t reached the scheduled end time.\n\nApakah Anda yakin?')) {
            $.ajax({
                url: '/meeting-room-bookings/' + id + '/finish',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Meeting finished successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to finish meeting'));
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Failed to finish meeting'));
                }
            });
        }
    };

    // Delete booking function
    window.deleteBooking = function(id, type) {
        const message = type === 'cancel' 
            ? 'Are you sure you want to cancel this booking?' 
            : 'Are you sure you want to delete this booking? This action cannot be undone.';
        
        if (confirm(message)) {
            document.getElementById('delete-form-' + id).submit();
        }
    };
    
    // Download monthly report
    $('#btnDownloadReport').click(function() {
        const month = $('#report_month').val();
        const year = $('#report_year').val();
        
        if (!month || !year) {
            alert('Pilih bulan dan tahun terlebih dahulu!');
            return;
        }
        
        $('#monthlyReportForm').submit();
        setTimeout(function() {
            $('#monthlyReportModal').modal('hide');
        }, 500);
    });
});
</script>
@endpush
