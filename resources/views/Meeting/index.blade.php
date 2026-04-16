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
                <i class="fa fa-plus"></i> <span class="hidden-xs" data-i18n="meeting.action.new_booking">New Booking</span>
            </a>
            <a href="'.route('meeting-room-bookings.calendar').'" class="btn btn-primary">
                <i class="fa fa-calendar"></i> <span class="hidden-xs" data-i18n="meeting.action.calendar">Calendar</span>
            </a>
        </div>
    '
])

<div class="pull-right" style="margin-top: -52px; margin-bottom: 16px; margin-right: 15px;">
    <div class="btn-group btn-group-xs" role="group" aria-label="Meeting Room Language Toggle">
        <button type="button" class="btn btn-default" id="meetingLanguageEnglish" data-lang="en">EN</button>
        <button type="button" class="btn btn-default" id="meetingLanguageIndonesian" data-lang="id">ID</button>
    </div>
</div>
<div class="clearfix"></div>

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
                    <p data-i18n="meeting.summary.total_bookings">Total Bookings</p>
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
                    <p data-i18n="meeting.summary.pending_approval">Pending Approval</p>
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
                    <p data-i18n="meeting.summary.approved">Approved</p>
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
                    <p data-i18n="meeting.summary.rejected">Rejected</p>
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
                    <p data-i18n="meeting.summary.finished">Finished</p>
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
                    <p data-i18n="meeting.summary.cancelled">Cancelled</p>
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
                    <p data-i18n="meeting.summary.today_bookings">Today's Bookings</p>
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
                        <span data-i18n="meeting.table.title">Booking Requests</span>
                        <span class="badge bg-blue" id="bookingCount">{{ $bookings->count() }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <div class="btn-group" role="group" style="margin-right: 5px;">
                            @role(['receptionist', 'admin', 'super-admin'])
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#monthlyReportModal">
                                <i class="fa fa-file-excel-o"></i> <span data-i18n="meeting.action.export">Export</span>
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
                                <i class="fa fa-list"></i> <span data-i18n="meeting.tabs.all_bookings">All Bookings</span>
                                <span class="badge bg-blue">{{ $stats['total'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="{{ request('tab') == 'my-bookings' ? 'active' : '' }}">
                            <a href="{{ route('meeting-room-bookings.index', ['tab' => 'my-bookings']) }}" data-tab="my-bookings">
                                <i class="fa fa-user"></i> <span data-i18n="meeting.tabs.my_bookings">My Bookings</span>
                            </a>
                        </li>
                        @if(user_has_role(Auth::user(), 'director') || user_has_role(Auth::user(), 'admin') || user_has_role(Auth::user(), 'super-admin'))
                        <li class="{{ request('tab') == 'pending' ? 'active' : '' }}">
                            <a href="{{ route('meeting-room-bookings.index', ['tab' => 'pending']) }}" data-tab="pending">
                                <i class="fa fa-clock-o"></i> <span data-i18n="meeting.tabs.pending_approval">Pending Approval</span>
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
                                    <th style="width: 60px;" data-i18n="meeting.table.id">ID</th>
                                    <th data-i18n="meeting.table.room">Room</th>
                                    <th data-i18n="meeting.table.date_time">Date & Time</th>
                                    <th data-i18n="meeting.table.requester">Requester</th>
                                    <th data-i18n="meeting.table.department">Department</th>
                                    <th data-i18n="meeting.table.duration">Duration</th>
                                    <th data-i18n="meeting.table.attendees">Attendees</th>
                                    <th data-i18n="meeting.table.status">Status</th>
                                    <th style="width: 200px;" data-i18n="meeting.table.actions">Actions</th>
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
                                            <h4 data-i18n="meeting.table.empty_title">No Bookings Found</h4>
                                            <p data-i18n="meeting.table.empty_subtitle">Start by creating your first booking request.</p>
                                            <a href="{{ route('meeting-room-bookings.create') }}" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> <span data-i18n="meeting.action.create_booking">Create Booking</span>
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
                        <i class="fa fa-check-circle"></i> <span data-i18n="meeting.modal.approve.title">Approve Booking Request</span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label data-i18n="meeting.modal.approve.notes_label">Notes (Optional)</label>
                        <textarea name="director_notes" class="form-control" rows="3" 
                                  placeholder="Add notes for approval..." data-i18n-placeholder="meeting.modal.approve.notes_placeholder"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="meeting.modal.cancel">Cancel</span></button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> <span data-i18n="meeting.modal.approve.action">Approve</span>
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
                        <i class="fa fa-times-circle"></i> <span data-i18n="meeting.modal.reject.title">Reject Booking Request</span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><span data-i18n="meeting.modal.reject.reason_label">Rejection Reason</span> <span class="text-danger">*</span></label>
                        <textarea name="director_notes" class="form-control" rows="3" 
                                  placeholder="Explain why this booking is rejected..." data-i18n-placeholder="meeting.modal.reject.reason_placeholder"
                                  required minlength="10"></textarea>
                        <small class="text-muted" data-i18n="meeting.modal.reject.min_chars">Minimum 10 characters required</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="meeting.modal.cancel">Cancel</span></button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times"></i> <span data-i18n="meeting.modal.reject.action">Reject</span>
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
                    <i class="fa fa-file-excel-o"></i> <span data-i18n="meeting.modal.report.title">Download Monthly Excel Report</span>
                </h4>
            </div>
            <div class="modal-body">
                <form id="monthlyReportForm" method="GET" action="{{ route('meeting-room-bookings.report.monthly-excel') }}">
                    <div class="form-group">
                        <label for="report_month" data-i18n="meeting.modal.report.month_label">Select Month:</label>
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
                        <label for="report_year" data-i18n="meeting.modal.report.year_label">Select Year:</label>
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
                        <span data-i18n="meeting.modal.report.info">The report includes booking data with status <strong>Approved</strong> or <strong>Finished</strong> for the selected month.</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="meeting.modal.cancel">Cancel</span></button>
                <button type="button" class="btn btn-success" id="btnDownloadReport">
                    <i class="fa fa-download"></i> <span data-i18n="meeting.modal.report.download">Download Excel</span>
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
(function() {
    var translations = {
        en: {
            'meeting.summary.total_bookings': 'Total Bookings',
            'meeting.summary.pending_approval': 'Pending Approval',
            'meeting.summary.approved': 'Approved',
            'meeting.summary.rejected': 'Rejected',
            'meeting.summary.finished': 'Finished',
            'meeting.summary.cancelled': 'Cancelled',
            'meeting.summary.today_bookings': "Today's Bookings",
            'meeting.table.title': 'Booking Requests',
            'meeting.table.count_suffix': 'Bookings',
            'meeting.table.id': 'ID',
            'meeting.table.room': 'Room',
            'meeting.table.date_time': 'Date & Time',
            'meeting.table.requester': 'Requester',
            'meeting.table.department': 'Department',
            'meeting.table.duration': 'Duration',
            'meeting.table.attendees': 'Attendees',
            'meeting.table.status': 'Status',
            'meeting.table.actions': 'Actions',
            'meeting.table.empty_title': 'No Bookings Found',
            'meeting.table.empty_subtitle': 'Start by creating your first booking request.',
            'meeting.tabs.all_bookings': 'All Bookings',
            'meeting.tabs.my_bookings': 'My Bookings',
            'meeting.tabs.pending_approval': 'Pending Approval',
            'meeting.action.new_booking': 'New Booking',
            'meeting.action.calendar': 'Calendar',
            'meeting.action.export': 'Export',
            'meeting.action.create_booking': 'Create Booking',
            'meeting.ui.loading': 'Loading...',
            'meeting.modal.cancel': 'Cancel',
            'meeting.modal.approve.title': 'Approve Booking Request',
            'meeting.modal.approve.notes_label': 'Notes (Optional)',
            'meeting.modal.approve.notes_placeholder': 'Add notes for approval...',
            'meeting.modal.approve.action': 'Approve',
            'meeting.modal.reject.title': 'Reject Booking Request',
            'meeting.modal.reject.reason_label': 'Rejection Reason',
            'meeting.modal.reject.reason_placeholder': 'Explain why this booking is rejected...',
            'meeting.modal.reject.min_chars': 'Minimum 10 characters required',
            'meeting.modal.reject.action': 'Reject',
            'meeting.modal.report.title': 'Download Monthly Excel Report',
            'meeting.modal.report.month_label': 'Select Month:',
            'meeting.modal.report.year_label': 'Select Year:',
            'meeting.modal.report.info': 'The report includes booking data with status Approved or Finished for the selected month.',
            'meeting.modal.report.download': 'Download Excel',
            'meeting.runtime.confirm.finish': 'Finish this meeting now?\n\nMeeting will be marked as FINISHED even if it has not reached the scheduled end time.',
            'meeting.runtime.confirm.cancel_booking': 'Are you sure you want to cancel this booking?',
            'meeting.runtime.confirm.delete_booking': 'Are you sure you want to delete this booking? This action cannot be undone.',
            'meeting.runtime.success.finish': 'Meeting finished successfully!',
            'meeting.runtime.error.finish_failed': 'Failed to finish meeting',
            'meeting.runtime.error_prefix': 'Error:',
            'meeting.runtime.validation.month_year': 'Please select month and year first!',
            'meeting.datatable.length_menu': 'Show _MENU_ bookings per page',
            'meeting.datatable.info': 'Showing _START_ to _END_ of _TOTAL_ bookings',
            'meeting.datatable.info_empty': 'No bookings to show',
            'meeting.datatable.info_filtered': '(filtered from _MAX_ total bookings)',
            'meeting.datatable.search': 'Quick Search:',
            'meeting.datatable.loading': 'Loading...',
            'meeting.datatable.processing': 'Processing...',
            'meeting.datatable.button.excel': 'Excel',
            'meeting.datatable.button.csv': 'CSV',
            'meeting.datatable.button.pdf': 'PDF',
            'meeting.datatable.button.copy': 'Copy',
            'meeting.datatable.button.columns': 'Columns'
        },
        id: {
            'meeting.summary.total_bookings': 'Total Booking',
            'meeting.summary.pending_approval': 'Menunggu Persetujuan',
            'meeting.summary.approved': 'Disetujui',
            'meeting.summary.rejected': 'Ditolak',
            'meeting.summary.finished': 'Selesai',
            'meeting.summary.cancelled': 'Dibatalkan',
            'meeting.summary.today_bookings': 'Booking Hari Ini',
            'meeting.table.title': 'Permintaan Booking',
            'meeting.table.count_suffix': 'Booking',
            'meeting.table.id': 'ID',
            'meeting.table.room': 'Ruangan',
            'meeting.table.date_time': 'Tanggal & Waktu',
            'meeting.table.requester': 'Pemohon',
            'meeting.table.department': 'Departemen',
            'meeting.table.duration': 'Durasi',
            'meeting.table.attendees': 'Peserta',
            'meeting.table.status': 'Status',
            'meeting.table.actions': 'Aksi',
            'meeting.table.empty_title': 'Belum Ada Booking',
            'meeting.table.empty_subtitle': 'Mulai dengan membuat permintaan booking pertama Anda.',
            'meeting.tabs.all_bookings': 'Semua Booking',
            'meeting.tabs.my_bookings': 'Booking Saya',
            'meeting.tabs.pending_approval': 'Antrian Persetujuan',
            'meeting.action.new_booking': 'Booking Baru',
            'meeting.action.calendar': 'Kalender',
            'meeting.action.export': 'Ekspor',
            'meeting.action.create_booking': 'Buat Booking',
            'meeting.ui.loading': 'Memuat...',
            'meeting.modal.cancel': 'Batal',
            'meeting.modal.approve.title': 'Setujui Permintaan Booking',
            'meeting.modal.approve.notes_label': 'Catatan (Opsional)',
            'meeting.modal.approve.notes_placeholder': 'Tambahkan catatan persetujuan...',
            'meeting.modal.approve.action': 'Setujui',
            'meeting.modal.reject.title': 'Tolak Permintaan Booking',
            'meeting.modal.reject.reason_label': 'Alasan Penolakan',
            'meeting.modal.reject.reason_placeholder': 'Jelaskan alasan penolakan booking ini...',
            'meeting.modal.reject.min_chars': 'Minimal 10 karakter',
            'meeting.modal.reject.action': 'Tolak',
            'meeting.modal.report.title': 'Unduh Laporan Bulanan Excel',
            'meeting.modal.report.month_label': 'Pilih Bulan:',
            'meeting.modal.report.year_label': 'Pilih Tahun:',
            'meeting.modal.report.info': 'Laporan berisi data booking dengan status Approved atau Finished pada bulan yang dipilih.',
            'meeting.modal.report.download': 'Unduh Excel',
            'meeting.runtime.confirm.finish': 'Selesaikan rapat ini sekarang?\n\nRapat akan ditandai FINISHED walaupun belum mencapai waktu selesai yang dijadwalkan.',
            'meeting.runtime.confirm.cancel_booking': 'Apakah Anda yakin ingin membatalkan booking ini?',
            'meeting.runtime.confirm.delete_booking': 'Apakah Anda yakin ingin menghapus booking ini? Tindakan ini tidak dapat dibatalkan.',
            'meeting.runtime.success.finish': 'Rapat berhasil diselesaikan!',
            'meeting.runtime.error.finish_failed': 'Gagal menyelesaikan rapat',
            'meeting.runtime.error_prefix': 'Kesalahan:',
            'meeting.runtime.validation.month_year': 'Pilih bulan dan tahun terlebih dahulu!',
            'meeting.datatable.length_menu': 'Tampilkan _MENU_ booking per halaman',
            'meeting.datatable.info': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ booking',
            'meeting.datatable.info_empty': 'Tidak ada booking untuk ditampilkan',
            'meeting.datatable.info_filtered': '(difilter dari total _MAX_ booking)',
            'meeting.datatable.search': 'Pencarian Cepat:',
            'meeting.datatable.loading': 'Memuat...',
            'meeting.datatable.processing': 'Memproses...',
            'meeting.datatable.button.excel': 'Excel',
            'meeting.datatable.button.csv': 'CSV',
            'meeting.datatable.button.pdf': 'PDF',
            'meeting.datatable.button.copy': 'Salin',
            'meeting.datatable.button.columns': 'Kolom'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('meetingLanguageEnglish');
    var indonesianButton = document.getElementById('meetingLanguageIndonesian');

    function getLanguage() {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            if (!raw) {
                return 'en';
            }

            var parsed = JSON.parse(raw);
            return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
            return 'en';
        }
    }

    function saveLanguage(language) {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            var parsed = raw ? JSON.parse(raw) : {};
            parsed.language = language === 'id' ? 'id' : 'en';
            window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
            // Keep silent if localStorage is unavailable.
        }
    }

    function getLabel(key) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || key;
    }

    function meetingLabel(key, fallback) {
        return getLabel(key) || fallback;
    }

    function refreshMeetingDataTableUiTranslations() {
        if (!window.jQuery) {
            return;
        }

        var $wrapper = window.jQuery('#bookingsTable_wrapper');
        if (!$wrapper.length) {
            return;
        }

        $wrapper.find('.buttons-excel').html('<i class="fa fa-file-excel-o"></i> ' + getLabel('meeting.datatable.button.excel'));
        $wrapper.find('.buttons-csv').html('<i class="fa fa-file-text-o"></i> ' + getLabel('meeting.datatable.button.csv'));
        $wrapper.find('.buttons-pdf').html('<i class="fa fa-file-pdf-o"></i> ' + getLabel('meeting.datatable.button.pdf'));
        $wrapper.find('.buttons-copy').html('<i class="fa fa-copy"></i> ' + getLabel('meeting.datatable.button.copy'));
        $wrapper.find('.buttons-colvis').html('<i class="fa fa-columns"></i> ' + getLabel('meeting.datatable.button.columns'));

        var $searchLabel = $wrapper.find('div.dataTables_filter label');
        if ($searchLabel.length) {
            $searchLabel.contents().filter(function() {
                return this.nodeType === 3;
            }).first().replaceWith(getLabel('meeting.datatable.search'));
        }
    }

    function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-placeholder]'), function(node) {
            var key = node.getAttribute('data-i18n-placeholder');
            if (dictionary[key]) {
                node.setAttribute('placeholder', dictionary[key]);
            }
        });

        if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }

        refreshMeetingDataTableUiTranslations();
    }

    window.getMeetingLabel = getLabel;
    window.initializeMeetingLanguage = function() {
        if (englishButton && indonesianButton) {
            englishButton.addEventListener('click', function() {
                saveLanguage('en');
                applyLanguage('en');
            });

            indonesianButton.addEventListener('click', function() {
                saveLanguage('id');
                applyLanguage('id');
            });
        }

        applyLanguage(getLanguage());
    };
})();

$(document).ready(function() {
    if (typeof window.initializeMeetingLanguage === 'function') {
        window.initializeMeetingLanguage();
    }

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
            countBadgeText: ' ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.table.count_suffix') : 'Bookings'),
            lengthMenuText: typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.length_menu') : 'Show _MENU_ bookings per page',
            infoText: typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.info') : 'Showing _START_ to _END_ of _TOTAL_ bookings',
            infoEmptyText: typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.info_empty') : 'No bookings to show',
            infoFilteredText: typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.info_filtered') : '(filtered from _MAX_ total bookings)',
            emptyTableText: '<div class="empty-state"><i class="fa fa-inbox fa-3x text-muted"></i><h4>'
                + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.table.empty_title') : 'No Bookings Found')
                + '</h4><p class="text-muted">'
                + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.table.empty_subtitle') : 'There are no meeting room bookings yet. Click "New Booking" to create one.')
                + '</p></div>',
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
            search: { smart: false }, // Disable smart search for faster filtering
            datatableOptions: {
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel-o"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.button.excel') : 'Excel'),
                        className: 'btn btn-success btn-sm',
                        title: 'Meeting_Room_Bookings',
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fa fa-file-text-o"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.button.csv') : 'CSV'),
                        className: 'btn btn-info btn-sm',
                        title: 'Meeting_Room_Bookings',
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fa fa-file-pdf-o"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.button.pdf') : 'PDF'),
                        className: 'btn btn-danger btn-sm',
                        title: 'Meeting_Room_Bookings',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        }
                    },
                    {
                        extend: 'copy',
                        text: '<i class="fa fa-copy"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.button.copy') : 'Copy'),
                        className: 'btn btn-default btn-sm',
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.button.columns') : 'Columns'),
                        className: 'btn btn-default btn-sm'
                    }
                ],
                language: {
                    search: typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.search') : 'Quick Search:',
                    loadingRecords: '<i class="fa fa-spinner fa-spin"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.loading') : 'Loading...'),
                    processing: '<i class="fa fa-spinner fa-spin"></i> ' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.datatable.processing') : 'Processing...')
                }
            }
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
        $('body').append('<div class="tab-loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);"><i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i><span style="font-size: 16px;">' + (typeof window.getMeetingLabel === 'function' ? window.getMeetingLabel('meeting.ui.loading') : 'Loading...') + '</span></div></div>');
        
        // Navigate to the URL
        window.location.href = href;
    });
    
    // Finish booking function
    window.finishBooking = function(id) {
        if (confirm(meetingLabel('meeting.runtime.confirm.finish', 'Finish this meeting now?\n\nMeeting will be marked as FINISHED even if it has not reached the scheduled end time.'))) {
            $.ajax({
                url: '/meeting-room-bookings/' + id + '/finish',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert(meetingLabel('meeting.runtime.success.finish', 'Meeting finished successfully!'));
                        location.reload();
                    } else {
                        alert(meetingLabel('meeting.runtime.error_prefix', 'Error:') + ' ' + (response.message || meetingLabel('meeting.runtime.error.finish_failed', 'Failed to finish meeting')));
                    }
                },
                error: function(xhr) {
                    alert(meetingLabel('meeting.runtime.error_prefix', 'Error:') + ' ' + (xhr.responseJSON?.message || meetingLabel('meeting.runtime.error.finish_failed', 'Failed to finish meeting')));
                }
            });
        }
    };

    // Delete booking function
    window.deleteBooking = function(id, type) {
        const message = type === 'cancel' 
            ? meetingLabel('meeting.runtime.confirm.cancel_booking', 'Are you sure you want to cancel this booking?')
            : meetingLabel('meeting.runtime.confirm.delete_booking', 'Are you sure you want to delete this booking? This action cannot be undone.');
        
        if (confirm(message)) {
            document.getElementById('delete-form-' + id).submit();
        }
    };
    
    // Download monthly report
    $('#btnDownloadReport').click(function() {
        const month = $('#report_month').val();
        const year = $('#report_year').val();
        
        if (!month || !year) {
            alert(meetingLabel('meeting.runtime.validation.month_year', 'Please select month and year first!'));
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
