@extends('layouts.app')

@section('main-content')
    {{-- Modern Page Header --}}
    @include('components.page-header', [
        'title' => 'System Maintenance',
        'subtitle' => 'System maintenance and cleanup tools',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'System Management', 'url' => route('system.settings')],
            ['label' => 'Maintenance', 'url' => null]
        ],
        'actions' => '
            <a href="'.route('system.settings').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to System
            </a>
            <button type="button" class="btn btn-success" onclick="runHealthCheck()">
                <i class="fa fa-heartbeat"></i> Health Check
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
                        <h3>{{ isset($cache_size) ? $cache_size : '0 MB' }}</h3>
                        <p>Cache Size</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-database"></i>
                    </div>
                    <a href="#cache-management" class="small-box-footer">
                        Manage Cache <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ isset($disk_space) ? $disk_space : 'N/A' }}</h3>
                        <p>Free Disk Space</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hdd-o"></i>
                    </div>
                    <a href="#storage-cleanup" class="small-box-footer">
                        View Storage <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ isset($queue_jobs) ? $queue_jobs : '0' }}</h3>
                        <p>Queue Jobs</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tasks"></i>
                    </div>
                    <a href="#queue-management" class="small-box-footer">
                        Manage Queue <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ isset($db_size) ? $db_size : 'N/A' }}</h3>
                        <p>Database Size</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-database"></i>
                    </div>
                    <a href="#db-maintenance" class="small-box-footer">
                        Optimize DB <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Cache Management --}}
            <div class="col-md-6" id="cache-management">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-tachometer"></i> Cache Management
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Clear application cache to improve performance and apply new configurations.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-light-blue action-card" onclick="clearCacheType('all')">
                                    <span class="info-box-icon"><i class="fa fa-refresh"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Clear All Caches</span>
                                        <span class="info-box-number">Complete Flush</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-aqua action-card" onclick="clearCacheType('config')">
                                    <span class="info-box-icon"><i class="fa fa-cog"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Config Cache</span>
                                        <span class="info-box-number">Clear Config</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-green action-card" onclick="clearCacheType('route')">
                                    <span class="info-box-icon"><i class="fa fa-road"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Route Cache</span>
                                        <span class="info-box-number">Clear Routes</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-yellow action-card" onclick="clearCacheType('view')">
                                    <span class="info-box-icon"><i class="fa fa-eye"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">View Cache</span>
                                        <span class="info-box-number">Clear Views</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <i class="fa fa-lightbulb-o"></i> <strong>Tip:</strong> Clear cache after updating configurations or deploying new code.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Storage Cleanup --}}
            <div class="col-md-6" id="storage-cleanup">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-hdd-o"></i> Storage Cleanup
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Clean up temporary files and logs to free up disk space.
                        </p>

                        <div class="list-group">
                            <a href="javascript:void(0)" onclick="clearLogs()" class="list-group-item action-item">
                                <span class="badge bg-red"><i class="fa fa-trash"></i></span>
                                <h4 class="list-group-item-heading">
                                    <i class="fa fa-file-text text-danger"></i> Clear Log Files
                                </h4>
                                <p class="list-group-item-text text-muted">Remove old log files to free up storage space</p>
                            </a>
                            <a href="javascript:void(0)" onclick="clearTemp()" class="list-group-item action-item">
                                <span class="badge bg-orange"><i class="fa fa-trash"></i></span>
                                <h4 class="list-group-item-heading">
                                    <i class="fa fa-folder text-warning"></i> Clear Temp Files
                                </h4>
                                <p class="list-group-item-text text-muted">Delete temporary files and cached data</p>
                            </a>
                            <a href="javascript:void(0)" onclick="clearUploads()" class="list-group-item action-item">
                                <span class="badge bg-yellow"><i class="fa fa-trash"></i></span>
                                <h4 class="list-group-item-heading">
                                    <i class="fa fa-upload text-warning"></i> Clear Old Uploads
                                </h4>
                                <p class="list-group-item-text text-muted">Remove uploads older than 90 days</p>
                            </a>
                        </div>

                        <div class="callout callout-warning" style="margin-bottom: 0;">
                            <h4><i class="fa fa-warning"></i> Warning!</h4>
                            <p>Storage cleanup operations cannot be undone. Ensure you have backups before proceeding.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Database Maintenance --}}
        <div class="row">
            <div class="col-md-8" id="db-maintenance">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-database"></i> Database Maintenance
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="action-card-large">
                                    <div class="action-card-icon bg-aqua">
                                        <i class="fa fa-magic"></i>
                                    </div>
                                    <div class="action-card-content">
                                        <h4>Optimize Tables</h4>
                                        <p class="text-muted">
                                            Analyze and optimize database tables to improve query performance and reduce fragmentation.
                                        </p>
                                        <div class="stats-row">
                                            <span class="text-muted">
                                                <i class="fa fa-table"></i> Tables ready for optimization
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-info btn-block" onclick="optimizeDB()">
                                            <i class="fa fa-magic"></i> Optimize Now
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="action-card-large">
                                    <div class="action-card-icon bg-blue">
                                        <i class="fa fa-arrow-up"></i>
                                    </div>
                                    <div class="action-card-content">
                                        <h4>Migration Status</h4>
                                        <p class="text-muted">
                                            Run pending database migrations to keep your schema up to date with the latest changes.
                                        </p>
                                        <div class="stats-row">
                                            <span class="text-muted">
                                                <i class="fa fa-clock-o"></i> Last run: Recently
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-info btn-block" onclick="runMigrations()">
                                            <i class="fa fa-arrow-up"></i> Run Migrations
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" style="margin-top: 15px; margin-bottom: 0;">
                            <i class="fa fa-shield"></i> <strong>Safety:</strong> Database operations are logged and can be monitored in the activity log below.
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Status --}}
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-heartbeat"></i> System Health
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="padding: 10px;">
                        <div class="status-item">
                            <div class="status-icon bg-blue">
                                <i class="fa fa-code"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">PHP Version</span>
                                <span class="status-value">{{ PHP_VERSION }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon bg-red">
                                <i class="fa fa-laravel"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Laravel</span>
                                <span class="status-value">{{ app()->version() }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon {{ app()->environment('production') ? 'bg-red' : 'bg-green' }}">
                                <i class="fa fa-server"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Environment</span>
                                <span class="label label-{{ app()->environment('production') ? 'danger' : 'success' }}">
                                    {{ strtoupper(app()->environment()) }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon {{ config('app.debug') ? 'bg-yellow' : 'bg-green' }}">
                                <i class="fa fa-bug"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Debug Mode</span>
                                <span class="label label-{{ config('app.debug') ? 'warning' : 'success' }}">
                                    {{ config('app.debug') ? 'ON' : 'OFF' }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item" style="border-bottom: none;">
                            <div class="status-icon bg-green">
                                <i class="fa fa-hdd-o"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Free Space</span>
                                <span class="status-value">{{ isset($disk_space) ? $disk_space : 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Queue Management --}}
        <div class="row">
            <div class="col-md-12" id="queue-management">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-tasks"></i> Queue Management
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Manage background job queues and worker processes.
                        </p>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="queue-action-card" onclick="restartQueue()">
                                    <div class="queue-icon bg-blue">
                                        <i class="fa fa-refresh"></i>
                                    </div>
                                    <h4>Restart Queue</h4>
                                    <p class="text-muted">Restart all queue workers to apply new changes</p>
                                    <button type="button" class="btn btn-primary btn-sm btn-block">
                                        <i class="fa fa-refresh"></i> Restart
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="queue-action-card" onclick="clearQueue()">
                                    <div class="queue-icon bg-yellow">
                                        <i class="fa fa-trash-o"></i>
                                    </div>
                                    <h4>Clear Queue</h4>
                                    <p class="text-muted">Remove all pending jobs from the queue</p>
                                    <button type="button" class="btn btn-warning btn-sm btn-block">
                                        <i class="fa fa-trash"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="queue-action-card" onclick="clearFailed()">
                                    <div class="queue-icon bg-red">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                    <h4>Clear Failed</h4>
                                    <p class="text-muted">Remove all failed jobs from the database</p>
                                    <button type="button" class="btn btn-danger btn-sm btn-block">
                                        <i class="fa fa-times"></i> Clear Failed
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="queue-action-card" onclick="viewQueueStatus()">
                                    <div class="queue-icon bg-aqua">
                                        <i class="fa fa-bar-chart"></i>
                                    </div>
                                    <h4>Queue Status</h4>
                                    <p class="text-muted">View detailed queue statistics and metrics</p>
                                    <button type="button" class="btn btn-info btn-sm btn-block">
                                        <i class="fa fa-eye"></i> View Status
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="callout callout-info" style="margin-top: 15px; margin-bottom: 0;">
                            <h4><i class="fa fa-lightbulb-o"></i> Queue Workers</h4>
                            <p>Make sure your queue workers are running using: <code>php artisan queue:work</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activities Log --}}
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-history"></i> Recent Maintenance Activities
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($recent_activities) && count($recent_activities) > 0)
                            <div class="table-responsive">
                                <table id="activities-table" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fa fa-clock-o"></i> Date & Time</th>
                                            <th><i class="fa fa-cog"></i> Action</th>
                                            <th><i class="fa fa-user"></i> User</th>
                                            <th><i class="fa fa-info-circle"></i> Status</th>
                                            <th><i class="fa fa-file-text"></i> Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_activities as $activity)
                                        <tr>
                                            <td>
                                                <i class="fa fa-calendar text-muted"></i>
                                                {{ $activity->created_at->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td>
                                                <span class="activity-action">
                                                    @php
                                                        $icon = 'cog';
                                                        if (str_contains(strtolower($activity->action), 'cache')) $icon = 'refresh';
                                                        elseif (str_contains(strtolower($activity->action), 'database')) $icon = 'database';
                                                        elseif (str_contains(strtolower($activity->action), 'queue')) $icon = 'tasks';
                                                        elseif (str_contains(strtolower($activity->action), 'log')) $icon = 'file-text';
                                                    @endphp
                                                    <i class="fa fa-{{ $icon }} text-primary"></i>
                                                    {{ $activity->action }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fa fa-user-circle text-info"></i>
                                                {{ $activity->user->name ?? 'System' }}
                                            </td>
                                            <td>
                                                <span class="label label-{{ $activity->status == 'success' ? 'success' : 'danger' }}">
                                                    <i class="fa fa-{{ $activity->status == 'success' ? 'check' : 'times' }}"></i>
                                                    {{ ucfirst($activity->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $activity->details ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center" style="margin-bottom: 0;">
                                <i class="fa fa-info-circle fa-2x"></i>
                                <h4>No Activities Found</h4>
                                <p>No recent maintenance activities recorded. Activities will appear here after you perform maintenance operations.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
    /* Action Card Styles */
    .action-card {
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }
    
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .action-card .info-box-content {
        padding-left: 10px;
    }
    
    /* Large Action Cards */
    .action-card-large {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 15px;
        background: #fff;
        transition: all 0.3s ease;
    }
    
    .action-card-large:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        border-color: #3c8dbc;
    }
    
    .action-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 28px;
        color: white;
    }
    
    .action-card-content h4 {
        color: #333;
        font-weight: 600;
        margin-bottom: 10px;
        text-align: center;
    }
    
    .action-card-content p {
        font-size: 13px;
        line-height: 1.5;
        min-height: 40px;
    }
    
    .stats-row {
        margin: 10px 0;
        padding: 8px 0;
        border-top: 1px solid #eee;
        font-size: 12px;
    }
    
    /* Status Items */
    .status-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #f4f4f4;
        transition: background-color 0.2s;
    }
    
    .status-item:hover {
        background-color: #f9f9f9;
    }
    
    .status-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .status-content {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .status-label {
        font-weight: 500;
        color: #666;
    }
    
    .status-value {
        font-weight: 600;
        color: #333;
    }
    
    /* Queue Action Cards */
    .queue-action-card {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        text-align: center;
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }
    
    .queue-action-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
        border-color: #3c8dbc;
    }
    
    .queue-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
        color: white;
    }
    
    .queue-action-card h4 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .queue-action-card p {
        font-size: 12px;
        margin-bottom: 15px;
        min-height: 36px;
    }
    
    /* Action Items in Lists */
    .action-item {
        transition: all 0.2s ease;
    }
    
    .action-item:hover {
        background-color: #f5f5f5;
        transform: translateX(5px);
    }
    
    .action-item .badge {
        padding: 8px 10px;
    }
    
    /* Activity Table Enhancements */
    #activities-table thead th {
        background-color: #f4f4f4;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
    }
    
    .activity-action {
        font-weight: 500;
    }
    
    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
    }
    
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3c8dbc;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables for Activities
    @if(isset($recent_activities) && count($recent_activities) > 0)
    $('#activities-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                className: 'btn-sm'
            },
            {
                extend: 'csv',
                className: 'btn-sm'
            },
            {
                extend: 'excel',
                className: 'btn-sm'
            },
            {
                extend: 'pdf',
                className: 'btn-sm'
            },
            {
                extend: 'print',
                className: 'btn-sm'
            }
        ]
    });
    @endif
});

// Show loading overlay
function showLoading(message = 'Processing...') {
    const overlay = $('<div class="loading-overlay">' +
        '<div class="loading-content">' +
        '<div class="loading-spinner"></div>' +
        '<h4>' + message + '</h4>' +
        '<p class="text-muted">Please wait while the operation completes</p>' +
        '</div>' +
        '</div>');
    $('body').append(overlay);
}

// Hide loading overlay
function hideLoading() {
    $('.loading-overlay').remove();
}

// Show toast notification
function showToast(message, type = 'success') {
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    const bgClass = type === 'success' ? 'bg-green' : 'bg-red';
    
    const toast = $('<div class="alert alert-' + type + ' alert-dismissible" style="position: fixed; top: 70px; right: 20px; z-index: 10000; min-width: 300px;">' +
        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
        '<i class="fa fa-' + icon + '"></i> ' + message +
        '</div>');
    
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(() => toast.remove()), 5000);
}

// Clear Cache by Type
function clearCacheType(type) {
    if (!confirm('Are you sure you want to clear the ' + type + ' cache?')) {
        return;
    }
    
    showLoading('Clearing ' + type + ' cache...');
    
    fetch('{{ route("system.cache.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: type })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Cache cleared successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to clear cache', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while clearing cache', 'error');
        console.error('Error:', error);
    });
}

// Clear Log Files
function clearLogs() {
    if (!confirm('Are you sure you want to clear all log files? This action cannot be undone.')) {
        return;
    }
    
    showLoading('Clearing log files...');
    
    fetch('{{ route("system.logs.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Log files cleared successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to clear log files', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while clearing logs', 'error');
        console.error('Error:', error);
    });
}

// Clear Temp Files
function clearTemp() {
    if (!confirm('Are you sure you want to clear temporary files?')) {
        return;
    }
    
    showLoading('Clearing temporary files...');
    
    fetch('{{ route("system.temp.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Temporary files cleared successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to clear temporary files', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while clearing temp files', 'error');
        console.error('Error:', error);
    });
}

// Clear Old Uploads
function clearUploads() {
    if (!confirm('Are you sure you want to remove uploads older than 90 days?')) {
        return;
    }
    
    showLoading('Clearing old uploads...');
    
    fetch('{{ route("system.uploads.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Old uploads cleared successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to clear old uploads', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while clearing uploads', 'error');
        console.error('Error:', error);
    });
}

// Optimize Database
function optimizeDB() {
    if (!confirm('Are you sure you want to optimize all database tables? This may take a few minutes.')) {
        return;
    }
    
    showLoading('Optimizing database tables...');
    
    fetch('{{ route("system.database.optimize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Database optimized successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to optimize database', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while optimizing database', 'error');
        console.error('Error:', error);
    });
}

// Run Migrations
function runMigrations() {
    if (!confirm('Are you sure you want to run pending migrations?')) {
        return;
    }
    
    showLoading('Running database migrations...');
    
    fetch('{{ route("system.database.migrate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Migrations completed successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to run migrations', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while running migrations', 'error');
        console.error('Error:', error);
    });
}

// Restart Queue
function restartQueue() {
    if (!confirm('Are you sure you want to restart queue workers?')) {
        return;
    }
    
    showLoading('Restarting queue workers...');
    
    fetch('{{ route("system.queue.restart") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Queue workers restarted successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to restart queue workers', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while restarting queue', 'error');
        console.error('Error:', error);
    });
}

// Clear Queue
function clearQueue() {
    if (!confirm('Are you sure you want to clear all queued jobs? This action cannot be undone.')) {
        return;
    }
    
    showLoading('Clearing queue...');
    
    fetch('{{ route("system.queue.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Queue cleared successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to clear queue', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while clearing queue', 'error');
        console.error('Error:', error);
    });
}

// Clear Failed Jobs
function clearFailed() {
    if (!confirm('Are you sure you want to clear all failed jobs?')) {
        return;
    }
    
    showLoading('Clearing failed jobs...');
    
    fetch('{{ route("system.queue.clear-failed") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Failed jobs cleared successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to clear failed jobs', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while clearing failed jobs', 'error');
        console.error('Error:', error);
    });
}

// View Queue Status
function viewQueueStatus() {
    showLoading('Loading queue status...');
    
    fetch('{{ route("system.queue.status") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            // Display queue status in a modal or alert
            let statusHtml = '<div style="text-align: left;">';
            statusHtml += '<strong>Pending Jobs:</strong> ' + (data.pending || 0) + '<br>';
            statusHtml += '<strong>Failed Jobs:</strong> ' + (data.failed || 0) + '<br>';
            statusHtml += '<strong>Active Workers:</strong> ' + (data.workers || 0) + '<br>';
            statusHtml += '</div>';
            
            showToast('Queue Status Retrieved', 'success');
            // You can implement a proper modal here
            alert('Queue Status:\n\n' + 
                  'Pending Jobs: ' + (data.pending || 0) + '\n' +
                  'Failed Jobs: ' + (data.failed || 0) + '\n' +
                  'Active Workers: ' + (data.workers || 0));
        } else {
            showToast(data.message || 'Failed to load queue status', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while loading queue status', 'error');
        console.error('Error:', error);
    });
}

// Run Health Check
function runHealthCheck() {
    showLoading('Running health check...');
    
    fetch('{{ route("system.health-check") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Health check completed successfully!', 'success');
            // You can display detailed health check results here
        } else {
            showToast(data.message || 'Health check failed', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred during health check', 'error');
        console.error('Error:', error);
    });
}
</script>
@endpush
@endsection