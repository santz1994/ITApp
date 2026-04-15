@extends('layouts.app')

@section('main-content')
    {{-- Modern Page Header --}}
    @include('components.page-header', [
        'title' => 'System Logs',
        'subtitle' => 'View and manage application logs',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'System Management', 'url' => route('system.settings')],
            ['label' => 'Logs', 'url' => null]
        ],
        'actions' => '
            <a href="'.route('system.logs.download').'" class="btn btn-info">
                <i class="fa fa-download"></i> Download Logs
            </a>
            <button type="button" class="btn btn-danger" onclick="clearLogs()">
                <i class="fa fa-trash"></i> Clear Logs
            </button>
        '
    ])

    <section class="content container-fluid">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Quick Stats Dashboard --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $stats['total'] ?? 0 }}</h3>
                        <p>Total Log Entries</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-file-text"></i>
                    </div>
                    <a href="#log-entries" class="small-box-footer">
                        View Logs <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $stats['errors'] ?? 0 }}</h3>
                        <p>Error Entries</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-circle"></i>
                    </div>
                    <a href="{{ route('system.logs', ['level' => 'error']) }}" class="small-box-footer">
                        Filter Errors <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $stats['warnings'] ?? 0 }}</h3>
                        <p>Warning Entries</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('system.logs', ['level' => 'warning']) }}" class="small-box-footer">
                        Filter Warnings <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $stats['file_size'] ?? 'N/A' }}</h3>
                        <p>Log File Size</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hdd-o"></i>
                    </div>
                    <a href="#log-files" class="small-box-footer">
                        View Files <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Log Viewer --}}
            <div class="col-md-9" id="log-entries">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list-alt"></i> Log Entries
                            @if(isset($logs) && count($logs) > 0)
                            <span class="badge bg-light-blue">{{ count($logs) }}</span>
                            @endif
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(request('level') || request('date') || request('search'))
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-filter"></i> <strong>Active Filters:</strong>
                            @if(request('level'))
                                <span class="label label-primary">Level: {{ strtoupper(request('level')) }}</span>
                            @endif
                            @if(request('date'))
                                <span class="label label-primary">Date: {{ ucfirst(request('date')) }}</span>
                            @endif
                            @if(request('search'))
                                <span class="label label-primary">Search: "{{ request('search') }}"</span>
                            @endif
                            <a href="{{ route('system.logs') }}" class="btn btn-xs btn-default pull-right">
                                <i class="fa fa-times"></i> Clear Filters
                            </a>
                        </div>
                        @endif

                        @if(isset($logs) && count($logs) > 0)
                        <div class="table-responsive">
                            <table id="logs-table" class="table table-enhanced table-striped table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 180px;"><i class="fa fa-clock-o"></i> Timestamp</th>
                                        <th style="width: 100px;"><i class="fa fa-tag"></i> Level</th>
                                        <th><i class="fa fa-comment"></i> Message</th>
                                        <th style="width: 80px;"><i class="fa fa-info-circle"></i> Context</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                    @php
                                        $levelIcons = [
                                            'emergency' => 'fa-fire',
                                            'alert' => 'fa-bell',
                                            'critical' => 'fa-exclamation-circle',
                                            'error' => 'fa-times-circle',
                                            'warning' => 'fa-exclamation-triangle',
                                            'notice' => 'fa-info-circle',
                                            'info' => 'fa-info',
                                            'debug' => 'fa-bug'
                                        ];
                                        $levelColors = [
                                            'emergency' => 'danger',
                                            'alert' => 'danger',
                                            'critical' => 'danger',
                                            'error' => 'danger',
                                            'warning' => 'warning',
                                            'notice' => 'info',
                                            'info' => 'info',
                                            'debug' => 'default'
                                        ];
                                        $level = strtolower($log['level'] ?? 'unknown');
                                        $icon = $levelIcons[$level] ?? 'fa-question-circle';
                                        $color = $levelColors[$level] ?? 'default';
                                    @endphp
                                    <tr class="log-row-{{ $color }}">
                                        <td>
                                            <small><i class="fa fa-calendar"></i> {{ $log['timestamp'] ?? 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <span class="label label-{{ $color }}">
                                                <i class="fa {{ $icon }}"></i> {{ strtoupper($level) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="log-message">{{ $log['message'] ?? 'No message' }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($log['context']) && !empty($log['context']))
                                            <button class="btn btn-xs btn-info" onclick="showContext('{{ $log['id'] ?? 'unknown' }}')">
                                                <i class="fa fa-search"></i>
                                            </button>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="empty-state text-center" style="padding: 60px 20px;">
                            <i class="fa fa-file-text-o fa-5x text-muted" style="opacity: 0.3;"></i>
                            <h3>No Log Entries Found</h3>
                            @if(request('search') || request('level') || request('date'))
                            <p class="text-muted">No logs match your current filters.</p>
                            <a href="{{ route('system.logs') }}" class="btn btn-primary">
                                <i class="fa fa-times"></i> Clear Filters
                            </a>
                            @else
                            <p class="text-muted">The log file is empty or no logs have been generated yet.</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @if(isset($logs) && count($logs) > 0)
                    <div class="box-footer">
                        <i class="fa fa-info-circle text-muted"></i>
                        <small class="text-muted">Displaying {{ count($logs) }} log entries</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Log Controls --}}
            <div class="col-md-3">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-filter"></i> Filters & Controls
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label><i class="fa fa-tag"></i> Filter by Level:</label>
                            <form method="GET" action="{{ route('system.logs') }}" id="level-filter-form">
                                <select class="form-control select2" name="level" onchange="this.form.submit()">
                                    <option value="">🔵 All Levels</option>
                                    <option value="emergency" {{ request('level') === 'emergency' ? 'selected' : '' }}>🔴 Emergency</option>
                                    <option value="alert" {{ request('level') === 'alert' ? 'selected' : '' }}>🔴 Alert</option>
                                    <option value="critical" {{ request('level') === 'critical' ? 'selected' : '' }}>🔴 Critical</option>
                                    <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>🔴 Error</option>
                                    <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>🟡 Warning</option>
                                    <option value="notice" {{ request('level') === 'notice' ? 'selected' : '' }}>🔵 Notice</option>
                                    <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>🔵 Info</option>
                                    <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>⚪ Debug</option>
                                </select>
                                @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                            </form>
                            <small class="text-muted">Filter logs by severity level</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-calendar"></i> Date Range:</label>
                            <form method="GET" action="{{ route('system.logs') }}" id="date-filter-form">
                                <select class="form-control select2" name="date" onchange="this.form.submit()">
                                    <option value="">All Dates</option>
                                    <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>📅 Today</option>
                                    <option value="yesterday" {{ request('date') === 'yesterday' ? 'selected' : '' }}>📅 Yesterday</option>
                                    <option value="week" {{ request('date') === 'week' ? 'selected' : '' }}>📅 This Week</option>
                                    <option value="month" {{ request('date') === 'month' ? 'selected' : '' }}>📅 This Month</option>
                                </select>
                                @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                                @if(request('level'))
                                <input type="hidden" name="level" value="{{ request('level') }}">
                                @endif
                            </form>
                            <small class="text-muted">Filter logs by time period</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-search"></i> Search Logs:</label>
                            <form method="GET" action="{{ route('system.logs') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search in messages..." value="{{ request('search') }}">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                                @if(request('level'))
                                <input type="hidden" name="level" value="{{ request('level') }}">
                                @endif
                                @if(request('date'))
                                <input type="hidden" name="date" value="{{ request('date') }}">
                                @endif
                            </form>
                        </div>

                        <hr>

                        <div class="btn-group btn-group-justified" role="group">
                            <div class="btn-group">
                                <a href="{{ route('system.logs.download') }}" class="btn btn-info">
                                    <i class="fa fa-download"></i> Download
                                </a>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger" onclick="clearLogs()">
                                    <i class="fa fa-trash"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Log Statistics
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-file-text"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Entries</span>
                                        <span class="info-box-number">{{ number_format($stats['total'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-hover table-condensed">
                            <tbody>
                                <tr>
                                    <td><i class="fa fa-times-circle text-danger"></i> <strong>Errors:</strong></td>
                                    <td class="text-right">
                                        <span class="badge bg-red">{{ $stats['errors'] ?? 0 }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fa fa-exclamation-triangle text-warning"></i> <strong>Warnings:</strong></td>
                                    <td class="text-right">
                                        <span class="badge bg-yellow">{{ $stats['warnings'] ?? 0 }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fa fa-info-circle text-info"></i> <strong>Info:</strong></td>
                                    <td class="text-right">
                                        <span class="badge bg-blue">{{ $stats['info'] ?? 0 }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fa fa-hdd-o text-default"></i> <strong>File Size:</strong></td>
                                    <td class="text-right">
                                        <span class="label label-default">{{ $stats['file_size'] ?? 'Unknown' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fa fa-clock-o text-default"></i> <strong>Last Entry:</strong></td>
                                    <td class="text-right">
                                        <small class="text-muted">{{ $stats['last_entry'] ?? 'Never' }}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box-success" id="log-files">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-folder-open"></i> Available Log Files
                            @if(isset($log_files) && count($log_files) > 0)
                            <span class="badge bg-green">{{ count($log_files) }}</span>
                            @endif
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($log_files) && count($log_files) > 0)
                        <ul class="list-group list-group-unbordered">
                            @foreach($log_files as $file)
                            <li class="list-group-item {{ $file['name'] === request('file', 'laravel.log') ? 'active' : '' }}">
                                <a href="{{ route('system.logs', ['file' => $file['name']]) }}" 
                                   class="{{ $file['name'] === request('file', 'laravel.log') ? 'text-white' : '' }}">
                                    <i class="fa fa-file-text-o"></i> <strong>{{ $file['name'] }}</strong>
                                </a>
                                <br>
                                <small class="{{ $file['name'] === request('file', 'laravel.log') ? 'text-white' : 'text-muted' }}">
                                    <i class="fa fa-hdd-o"></i> {{ $file['size'] }}
                                    <br>
                                    <i class="fa fa-calendar"></i> {{ $file['modified'] }}
                                </small>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="text-center" style="padding: 20px;">
                            <i class="fa fa-folder-open-o fa-3x text-muted" style="opacity: 0.3;"></i>
                            <p class="text-muted">No log files found</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

<script>
// Show log context details
function showContext(logId) {
    // Implement context viewing in modal or alert
    alert('Log Context for ID: ' + logId + '\n\nThis feature displays additional context information for the log entry.\n\n(Full implementation pending)');
}

// Clear all logs with confirmation
function clearLogs() {
    if (confirm('⚠️ Are you sure you want to clear all logs?\n\nThis action cannot be undone and will permanently delete all log entries.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("system.logs.clear") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize DataTables and enhancements on document ready
$(document).ready(function() {
    // Initialize logs table with enhanced features
    if ($('#logs-table').length) {
        $('#logs-table').DataTable({
            "responsive": true,
            "pageLength": 50,
            "lengthMenu": [[25, 50, 100, 250, -1], [25, 50, 100, 250, "All"]],
            "order": [[0, "desc"]], // Order by timestamp descending (newest first)
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search in all columns...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ log entries",
                "infoEmpty": "No log entries available",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "emptyTable": "No log entries found in the system",
                "zeroRecords": "No matching log entries found"
            },
            "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                   '<"row"<"col-sm-12"tr>>' +
                   '<"row"<"col-sm-5"i><"col-sm-7"p>>',
            "columnDefs": [
                {
                    "targets": [0], // Timestamp column
                    "type": "date",
                    "className": "text-nowrap"
                },
                {
                    "targets": [1], // Level column
                    "orderable": true,
                    "className": "text-center"
                },
                {
                    "targets": [3], // Context column
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                }
            ],
            // Highlight error and warning rows
            "rowCallback": function(row, data, index) {
                if ($(row).hasClass('log-row-danger')) {
                    $(row).css('background-color', '#f2dede');
                } else if ($(row).hasClass('log-row-warning')) {
                    $(row).css('background-color', '#fcf8e3');
                }
            }
        });
    }

    // Initialize Select2 on filter dropdowns
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%',
        minimumResultsForSearch: -1 // Hide search box
    });

    // Auto-submit filter forms on change
    $('.select2').on('select2:select', function (e) {
        $(this).closest('form').submit();
    });
});
</script>

<style>
/* Log row styling */
.log-message {
    word-break: break-word;
    line-height: 1.4;
}

.log-row-danger {
    border-left: 3px solid #dd4b39;
}

.log-row-warning {
    border-left: 3px solid #f39c12;
}

.log-row-info {
    border-left: 3px solid #00c0ef;
}

/* Table enhancements */
#logs-table tbody tr {
    transition: all 0.2s ease;
}

#logs-table tbody tr:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Empty state styling */
.empty-state {
    padding: 60px 20px !important;
}

.empty-state i {
    margin-bottom: 20px;
}

/* Active log file styling */
.list-group-item.active {
    background-color: #00a65a !important;
    border-color: #00a65a !important;
}

.list-group-item.active a {
    color: white !important;
}

/* Filter controls */
.form-group label {
    font-weight: 600;
    color: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .small-box h3 {
        font-size: 28px;
    }
    
    #logs-table {
        font-size: 12px;
    }
}
</style>
@endsection
