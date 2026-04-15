@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom-tables.css') }}">
@endpush

@section('main-content')
    @include('components.page-header', [
        'title' => 'Daily Activities',
        'subtitle' => 'Track and manage daily work activities with comprehensive logging',
        'icon' => 'fa-calendar-check-o',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'dashboard'],
            ['label' => 'Daily Activities']
        ],
        'actions' => '
            <div class="btn-group" role="group">
                <a href="' . route('daily-activities.create') . '" class="btn btn-primary">
                    <i class="fa fa-plus"></i> <span class="hidden-xs">New Activity</span>
                </a>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-download"></i> <span class="hidden-xs">Reports</span> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="' . route('daily-activities.daily-report') . '?date=' . request('date', today()->format('Y-m-d')) . '">
                        <i class="fa fa-file-text-o"></i> Daily Report
                    </a></li>
                    <li><a href="' . route('daily-activities.weekly-report') . '">
                        <i class="fa fa-calendar"></i> Weekly Report
                    </a></li>
                    <li><a href="' . route('daily-activities.export-pdf') . '?date=' . request('date', today()->format('Y-m-d')) . '">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </a></li>
                </ul>
            </div>
        '
    ])

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-ban"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-warning"></i> Validation Errors:</h4>
            <ul style="margin-bottom: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('components.loading-overlay')

    <section class="content">
        <div class="row">
            {{-- Main Content: 8 columns --}}
            <div class="col-md-8">
                {{-- Quick Stats Cards --}}
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua stat-card" onclick="filterByPeriod('today')">
                            <div class="inner">
                                <h3>{{ $stats['today'] ?? 0 }}</h3>
                                <p>Today's Activities</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar-o"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                Filter Today <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green stat-card" onclick="filterByPeriod('week')">
                            <div class="inner">
                                <h3>{{ $stats['week'] ?? 0 }}</h3>
                                <p>This Week</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                Filter Week <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow stat-card" onclick="filterByPeriod('month')">
                            <div class="inner">
                                <h3>{{ $stats['month'] ?? 0 }}</h3>
                                <p>This Month</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar-check-o"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                Filter Month <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red stat-card" onclick="filterByUser('me')">
                            <div class="inner">
                                <h3>{{ $stats['logged_by_me'] ?? 0 }}</h3>
                                <p>Logged by Me</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                My Activities <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Advanced Filters (Collapsible) --}}
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Advanced Filters</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body filter-bar">
                        <form method="GET" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-calendar"></i> Date Range:</label>
                                        <div class="input-group">
                                            <input type="date" name="date_from" class="form-control" 
                                                   value="{{ request('date_from') }}" placeholder="From">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="input-group">
                                            <input type="date" name="date_to" class="form-control" 
                                                   value="{{ request('date_to') }}" placeholder="To">
                                        </div>
                                    </div>
                                </div>
                                @role(['super-admin', 'admin'])
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-user"></i> User:</label>
                                        <select name="user_id" class="form-control">
                                            <option value="">All Users</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endrole
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-ticket"></i> Ticket:</label>
                                        <input type="text" name="ticket_id" class="form-control" 
                                               value="{{ request('ticket_id') }}" placeholder="Ticket ID">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fa fa-tag"></i> Type:</label>
                                        <select name="type" class="form-control">
                                            <option value="">All Types</option>
                                            <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                                            <option value="automated" {{ request('type') == 'automated' ? 'selected' : '' }}>Automated</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa fa-search"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('daily-activities.index') }}" class="btn btn-default">
                                        <i class="fa fa-refresh"></i> Reset
                                    </a>
                                    <button type="button" class="btn btn-success" id="exportFilteredBtn">
                                        <i class="fa fa-file-excel-o"></i> Export Filtered
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Activities List with DataTable --}}
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Activities List
                            <span class="badge bg-blue">{{ $activities->total() ?? 0 }}</span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if($activities->count() > 0)
                            <div class="table-responsive">
                                <table id="activitiesTable" class="table table-bordered table-striped table-enhanced">
                                    <thead>
                                        <tr>
                                            <th width="120">Date/Time</th>
                                            <th width="150">User</th>
                                            <th>Description</th>
                                            <th width="120">Type</th>
                                            <th width="120">Related Ticket</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activities as $activity)
                                        <tr>
                                            <td>
                                                <span class="label label-info">
                                                    @if($activity->activity_date)
                                                        {{ \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fa fa-clock-o"></i>
                                                    @if($activity->created_at)
                                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <strong><i class="fa fa-user"></i> {{ $activity->user->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $activity->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <div class="activity-description">
                                                    {{ \Illuminate\Support\Str::limit($activity->description, 150) }}
                                                    @if(strlen($activity->description) > 150)
                                                        <a href="#" class="show-full-description" data-description="{{ $activity->description }}">
                                                            <i class="fa fa-expand"></i> Show more
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($activity->type === 'manual')
                                                    <span class="label label-primary">
                                                        <i class="fa fa-edit"></i> Manual
                                                    </span>
                                                @else
                                                    <span class="label label-success">
                                                        <i class="fa fa-cogs"></i> Auto
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($activity->ticket)
                                                    <a href="{{ route('tickets.show', $activity->ticket->id) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fa fa-ticket"></i> {{ $activity->ticket->ticket_code ?? '#' . $activity->ticket->id }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('daily-activities.show', $activity->id) }}" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    @can('update', $activity)
                                                        <a href="{{ route('daily-activities.edit', $activity->id) }}" 
                                                           class="btn btn-sm btn-primary" title="Edit Activity">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $activity)
                                                        <button type="button" class="btn btn-sm btn-danger" title="Delete Activity"
                                                                onclick="deleteActivity({{ $activity->id }})">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                                @can('delete', $activity)
                                                <form id="delete-activity-{{ $activity->id }}" action="{{ route('daily-activities.destroy', $activity->id) }}" 
                                                      method="POST" style="display:none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6">
                                                Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} of {{ $activities->total() ?? 0 }} activities
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            <div class="text-center">
                                {{ $activities->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="alert alert-info text-center empty-state">
                                <h4><i class="fa fa-info-circle"></i> No Activities Found</h4>
                                <p>No daily activities found for the selected date and filters.</p>
                                <a href="{{ route('daily-activities.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Your First Activity
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar: 4 columns --}}
            <div class="col-md-4">
                {{-- Today's Summary --}}
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-calendar-o"></i> Today's Summary</h3>
                    </div>
                    <div class="box-body">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-tasks"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Activities</span>
                                <span class="info-box-number">{{ $stats['total_activities'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-edit"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Manual Entries</span>
                                <span class="info-box-number">{{ $stats['manual_activities'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-cogs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Auto Generated</span>
                                <span class="info-box-number">{{ $stats['auto_activities'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-ticket"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tickets Completed</span>
                                <span class="info-box-number">{{ $stats['tickets_completed'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Guidelines --}}
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Activity Guidelines</h3>
                    </div>
                    <div class="box-body">
                        <p style="font-size: 13px; line-height: 1.6;">
                            <strong>Best Practices for Activity Logging:</strong>
                        </p>
                        <ul style="font-size: 12px; line-height: 1.8;">
                            <li><strong>Be Specific:</strong> Describe what you did in detail</li>
                            <li><strong>Include Context:</strong> Mention who, what, where, and why</li>
                            <li><strong>Track Time:</strong> Log start and end times accurately</li>
                            <li><strong>Link Tickets:</strong> Associate activities with related tickets</li>
                            <li><strong>Log Daily:</strong> Don't wait - log activities as they happen</li>
                            <li><strong>Note Outcomes:</strong> Record results and lessons learned</li>
                        </ul>
                    </div>
                </div>

                {{-- Quick Templates --}}
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Quick Templates</h3>
                    </div>
                    <div class="box-body">
                        <p style="font-size: 12px; margin-bottom: 10px;"><strong>Common Activity Templates:</strong></p>
                        <div class="list-group">
                            <a href="#" class="list-group-item template-item" data-type="ticket_handling" data-template="Handled ticket #[TICKET] - [ISSUE_SUMMARY]. Resolution: [SOLUTION]. Time spent: [DURATION] minutes.">
                                <i class="fa fa-ticket text-blue"></i> Ticket Handling
                            </a>
                            <a href="#" class="list-group-item template-item" data-type="asset_management" data-template="Asset management activity for [ASSET_TAG]. Task: [TASK_DESCRIPTION]. Status: [STATUS].">
                                <i class="fa fa-cube text-green"></i> Asset Management
                            </a>
                            <a href="#" class="list-group-item template-item" data-type="user_support" data-template="Provided support to [USER_NAME] for [ISSUE]. Resolution: [SOLUTION].">
                                <i class="fa fa-life-ring text-yellow"></i> User Support
                            </a>
                            <a href="#" class="list-group-item template-item" data-type="system_maintenance" data-template="Performed system maintenance: [SYSTEM]. Tasks completed: [TASKS]. All systems operational.">
                                <i class="fa fa-cogs text-red"></i> System Maintenance
                            </a>
                            <a href="#" class="list-group-item template-item" data-type="meeting" data-template="Attended meeting: [MEETING_TOPIC]. Attendees: [ATTENDEES]. Key decisions: [DECISIONS].">
                                <i class="fa fa-users text-purple"></i> Meeting
                            </a>
                        </div>
                        <p style="font-size: 11px; margin-top: 10px; color: #777;">
                            <i class="fa fa-info-circle"></i> Click a template to copy it to clipboard
                        </p>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="box-body">
                        <a href="{{ route('daily-activities.create') }}" class="btn btn-primary btn-block">
                            <i class="fa fa-plus"></i> Add New Activity
                        </a>
                        <a href="{{ route('daily-activities.calendar') }}" class="btn btn-info btn-block">
                            <i class="fa fa-calendar"></i> View Calendar
                        </a>
                        <a href="{{ route('daily-activities.daily-report') }}?date={{ today()->format('Y-m-d') }}" class="btn btn-success btn-block">
                            <i class="fa fa-file-text-o"></i> Today's Report
                        </a>
                        <a href="{{ route('daily-activities.weekly-report') }}" class="btn btn-warning btn-block">
                            <i class="fa fa-calendar"></i> Weekly Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{-- Full Description Modal --}}
<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-text"></i> Activity Description</h4>
            </div>
            <div class="modal-body">
                <p id="fullDescription" style="white-space: pre-wrap;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Delete activity confirmation
window.deleteActivity = function(id) {
    if(confirm('Are you sure you want to delete this activity?')) {
        document.getElementById('delete-activity-' + id).submit();
    }
};

$(document).ready(function() {
    // Hide loading overlay when page is fully loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (typeof hideLoadingOverlay === 'function') {
                hideLoadingOverlay();
            }
        }, 300);
    });
    
    // Initialize DataTable with Excel/CSV/PDF export
    @if($activities->count() > 0)
    var table = $('#activitiesTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                titleAttr: 'Export to Excel',
                className: 'btn-success',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // Exclude Actions column
                }
            },
            {
                extend: 'csv',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                titleAttr: 'Export to CSV',
                className: 'btn-info',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                titleAttr: 'Export to PDF',
                className: 'btn-danger',
                orientation: 'landscape',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy',
                titleAttr: 'Copy to Clipboard',
                className: 'btn-default',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search activities...",
            lengthMenu: "_MENU_ activities per page",
            info: "Showing _START_ to _END_ of _TOTAL_ activities",
            infoEmpty: "No activities found",
            infoFiltered: "(filtered from _MAX_ total activities)",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                last: '<i class="fa fa-angle-double-right"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                previous: '<i class="fa fa-angle-left"></i>'
            }
        },
        order: [[0, 'desc']], // Sort by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
    });
    @endif
    
    // Show full description modal
    $('.show-full-description').on('click', function(e) {
        e.preventDefault();
        var description = $(this).data('description');
        $('#fullDescription').text(description);
        $('#descriptionModal').modal('show');
    });
    
    // Stat card click - filter by period
    window.filterByPeriod = function(period) {
        var today = new Date();
        var dateFrom, dateTo;
        
        if (period === 'today') {
            dateFrom = dateTo = today.toISOString().split('T')[0];
        } else if (period === 'week') {
            var weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            dateFrom = weekAgo.toISOString().split('T')[0];
            dateTo = today.toISOString().split('T')[0];
        } else if (period === 'month') {
            var monthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
            dateFrom = monthAgo.toISOString().split('T')[0];
            dateTo = today.toISOString().split('T')[0];
        }
        
        // Update form and submit
        $('input[name="date_from"]').val(dateFrom);
        $('input[name="date_to"]').val(dateTo);
        $('#filterForm').submit();
    };
    
    // Filter by current user
    window.filterByUser = function(type) {
        if (type === 'me') {
            $('select[name="user_id"]').val('{{ Auth::id() }}');
            $('#filterForm').submit();
        }
    };
    
    // Export filtered results
    $('#exportFilteredBtn').on('click', function() {
        @if($activities->count() > 0)
            table.button('.buttons-excel').trigger();
        @else
            alert('No data to export. Please adjust your filters.');
        @endif
    });
    
    // Template item click - copy to clipboard
    $('.template-item').on('click', function(e) {
        e.preventDefault();
        var template = $(this).data('template');
        var type = $(this).data('type');
        
        // Copy to clipboard
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(template).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Show feedback
        $(this).html('<i class="fa fa-check text-success"></i> Copied to clipboard!');
        var self = this;
        setTimeout(function() {
            $(self).html($(self).data('original-html'));
        }, 2000);
        
        // Store original HTML if not already stored
        if (!$(this).data('original-html')) {
            $(this).data('original-html', $(this).html());
        }
    });
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush

