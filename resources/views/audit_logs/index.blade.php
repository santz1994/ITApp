@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom-tables.css') }}">
@endpush

@section('main-content')
    @include('components.page-header', [
        'title' => 'Audit Logs',
        'subtitle' => 'System activity and change tracking',
        'icon' => 'fa-history',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'fa-dashboard'],
            ['label' => 'Audit Logs', 'active' => true]
        ],
        'actions' => '
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#filter-panel">
                    <i class="fa fa-filter"></i> <span class="hidden-xs">Filters</span>
                </button>
                <a href="' . route('audit-logs.export', request()->all()) . '" class="btn btn-success">
                    <i class="fa fa-download"></i> <span class="hidden-xs">Export</span>
                </a>
            </div>
        '
    ])

    @include('components.loading-overlay')

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-list"></i> Activity History
                            </h3>
                        </div>

                        <!-- Filter Panel -->
                <div id="filter-panel" class="collapse">
                    <div class="box-body">
                        <form method="GET" action="{{ route('audit-logs.index') }}" id="filter-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>User</label>
                                        <select name="user_id" class="form-control select2">
                                            <option value="">All Users</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Action</label>
                                        <select name="action" class="form-control">
                                            <option value="">All Actions</option>
                                            @foreach($actions as $action)
                                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                                    {{ ucfirst($action) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Model Type</label>
                                        <select name="model_type" class="form-control">
                                            <option value="">All Models</option>
                                            @foreach($modelTypes as $modelType)
                                                <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                                                    {{ $modelType }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Event Type</label>
                                        <select name="event_type" class="form-control">
                                            <option value="">All Types</option>
                                            @foreach($eventTypes as $eventType)
                                                <option value="{{ $eventType }}" {{ request('event_type') == $eventType ? 'selected' : '' }}>
                                                    {{ ucfirst($eventType) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Search</label>
                                        <input type="text" name="search" class="form-control" placeholder="Description or IP..." value="{{ request('search') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('audit-logs.index') }}" class="btn btn-default">
                                                <i class="fa fa-times"></i> Clear Filters
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Audit Logs Table -->
                <div class="box-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($auditLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th width="150">Date/Time</th>
                                        <th width="120">User</th>
                                        <th width="100">Action</th>
                                        <th width="120">Model</th>
                                        <th>Description</th>
                                        <th width="100">Event Type</th>
                                        <th width="120">IP Address</th>
                                        <th width="80">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLogs as $log)
                                        <tr>
                                            <td>{{ $log->id }}</td>
                                            <td>
                                                <small>{{ $log->created_at->format('Y-m-d') }}</small><br>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($log->user)
                                                    <a href="{{ route('audit-logs.index', ['user_id' => $log->user_id]) }}" title="View user's logs">
                                                        {{ $log->user->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $actionBadgeClass = [
                                                        'create' => 'success',
                                                        'update' => 'info',
                                                        'delete' => 'danger',
                                                        'login' => 'primary',
                                                        'logout' => 'default',
                                                        'failed_login' => 'warning',
                                                    ][$log->action] ?? 'default';
                                                @endphp
                                                <span class="label label-{{ $actionBadgeClass }}">
                                                    {{ $log->action_name }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->model_type)
                                                    <span class="badge bg-purple">{{ $log->model_name }}</span>
                                                    @if($log->model_id)
                                                        <small class="text-muted">#{{ $log->model_id }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ \Illuminate\Support\Str::limit($log->description, 80) }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $eventTypeBadgeClass = [
                                                        'model' => 'primary',
                                                        'auth' => 'success',
                                                        'system' => 'warning',
                                                    ][$log->event_type] ?? 'default';
                                                @endphp
                                                <span class="label label-{{ $eventTypeBadgeClass }}">
                                                    {{ ucfirst($log->event_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $log->ip_address }}</small>
                                            </td>
                                            <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('audit-logs.show', $log->id) }}" class="btn btn-sm btn-info" title="View details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="text-center">
                            {{ $auditLogs->appends(request()->all())->links() }}
                        </div>

                        <!-- Summary -->
                        @if(method_exists($auditLogs, 'total'))
                        <div class="text-center text-muted">
                            <small>
                                Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} of {{ $auditLogs->total() }} logs
                            </small>
                        </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No audit logs found matching your criteria.
                        </div>
                    @endif
                </div>

                <!-- Cleanup Section (Super Admin Only) -->
                @can('cleanup', App\AuditLog::class)
                    <div class="box-footer">
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cleanupModal">
                            <i class="fa fa-trash"></i> Cleanup Old Logs
                        </button>
                        <span class="text-muted">
                            <small>Remove audit logs older than specified days (admin only)</small>
                        </span>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('audit-logs.cleanup') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-trash"></i> Cleanup Old Audit Logs</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-warning"></i> This action will permanently delete old audit logs and cannot be undone.
                    </div>
                    <div class="form-group">
                        <label>Keep logs from the last:</label>
                        <select name="days_to_keep" class="form-control" required>
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90" selected>90 days (Recommended)</option>
                            <option value="120">120 days</option>
                            <option value="180">180 days (6 months)</option>
                            <option value="365">365 days (1 year)</option>
                        </select>
                        <small class="help-block">Logs older than this will be permanently deleted.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Delete Old Logs
                    </button>
                </div>
            </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Hide loading overlay when page is fully loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (typeof hideLoadingOverlay === 'function') {
                hideLoadingOverlay();
            }
        }, 300);
    });
    
    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select a user",
        allowClear: true
    });

    // Auto-open filter panel if filters are applied
    @if(request()->hasAny(['user_id', 'action', 'model_type', 'event_type', 'start_date', 'end_date', 'search']))
        $('#filter-panel').addClass('in');
    @endif
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush
