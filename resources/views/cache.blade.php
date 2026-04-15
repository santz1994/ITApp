@extends('layouts.app')

@section('main-content')
    {{-- Page Header --}}
    @include('components.page-header', [
        'title' => 'Cache Management',
        'subtitle' => 'Application cache control and optimization',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'dashboard'],
            ['label' => 'Admin Tools', 'url' => route('admin.dashboard'), 'icon' => 'cogs'],
            ['label' => 'Cache Management']
        ],
        'actions' => '
            <a href="' . route('admin.dashboard') . '" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Admin
            </a>
            <button type="button" class="btn btn-success" onclick="optimizeCache()">
                <i class="fa fa-rocket"></i> Optimize All
            </button>
        '
    ])

    <section class="content">
        {{-- Quick Stats --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $cache_stats['total_files'] ?? 0 }}</h3>
                        <p>Cache Files</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-files-o"></i>
                    </div>
                    <a href="#cache-files" class="small-box-footer">
                        View Details <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $cache_stats['total_size'] ?? '0 MB' }}</h3>
                        <p>Cache Size</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hdd-o"></i>
                    </div>
                    <a href="#cache-actions" class="small-box-footer">
                        Clear Cache <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $cache_stats['hit_rate'] ?? '0%' }}</h3>
                        <p>Hit Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bullseye"></i>
                    </div>
                    <a href="#cache-stats" class="small-box-footer">
                        Statistics <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ isset($recent_cache_activity) ? count($recent_cache_activity) : 0 }}</h3>
                        <p>Recent Operations</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-history"></i>
                    </div>
                    <a href="#recent-activity" class="small-box-footer">
                        View Activity <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
        {{-- Cache Status & Quick Actions --}}
        <div class="row">
            {{-- Cache Status --}}
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-heartbeat"></i> Cache System Status
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="padding: 10px;">
                        <div class="status-item">
                            <div class="status-icon {{ isset($cache_status['working']) && $cache_status['working'] ? 'bg-green' : 'bg-red' }}">
                                <i class="fa fa-database"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Cache Driver</span>
                                <span class="status-value">{{ strtoupper($cache_info['driver'] ?? 'file') }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon {{ isset($cache_status['working']) && $cache_status['working'] ? 'bg-green' : 'bg-red' }}">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">System Status</span>
                                <span class="label label-{{ isset($cache_status['working']) && $cache_status['working'] ? 'success' : 'danger' }}">
                                    {{ isset($cache_status['working']) && $cache_status['working'] ? 'Working' : 'Not Working' }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon {{ isset($cache_status['application']) && $cache_status['application'] ? 'bg-green' : 'bg-yellow' }}">
                                <i class="fa fa-cubes"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Application Cache</span>
                                <span class="label label-{{ isset($cache_status['application']) && $cache_status['application'] ? 'success' : 'warning' }}">
                                    {{ isset($cache_status['application']) && $cache_status['application'] ? 'Cached' : 'Not Cached' }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon {{ isset($cache_status['routes']) && $cache_status['routes'] ? 'bg-green' : 'bg-yellow' }}">
                                <i class="fa fa-road"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Route Cache</span>
                                <span class="label label-{{ isset($cache_status['routes']) && $cache_status['routes'] ? 'success' : 'warning' }}">
                                    {{ isset($cache_status['routes']) && $cache_status['routes'] ? 'Cached' : 'Not Cached' }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon {{ isset($cache_status['config']) && $cache_status['config'] ? 'bg-green' : 'bg-yellow' }}">
                                <i class="fa fa-cogs"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Config Cache</span>
                                <span class="label label-{{ isset($cache_status['config']) && $cache_status['config'] ? 'success' : 'warning' }}">
                                    {{ isset($cache_status['config']) && $cache_status['config'] ? 'Cached' : 'Not Cached' }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item" style="border-bottom: none;">
                            <div class="status-icon {{ isset($cache_status['views']) && $cache_status['views'] ? 'bg-green' : 'bg-yellow' }}">
                                <i class="fa fa-eye"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">View Cache</span>
                                <span class="label label-{{ isset($cache_status['views']) && $cache_status['views'] ? 'success' : 'warning' }}">
                                    {{ isset($cache_status['views']) && $cache_status['views'] ? 'Cached' : 'Not Cached' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Cache Actions --}}
            <div class="col-md-6" id="cache-actions">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bolt"></i> Quick Cache Actions
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Click any card to perform the cache operation.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="cache-action-card" onclick="clearCacheType('application')">
                                    <div class="cache-icon bg-yellow">
                                        <i class="fa fa-database"></i>
                                    </div>
                                    <h4>Application Cache</h4>
                                    <p class="text-muted">Clear application data cache</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="cache-action-card" onclick="clearCacheType('config')">
                                    <div class="cache-icon bg-aqua">
                                        <i class="fa fa-cogs"></i>
                                    </div>
                                    <h4>Config Cache</h4>
                                    <p class="text-muted">Clear configuration cache</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="cache-action-card" onclick="clearCacheType('route')">
                                    <div class="cache-icon bg-green">
                                        <i class="fa fa-road"></i>
                                    </div>
                                    <h4>Route Cache</h4>
                                    <p class="text-muted">Clear route definitions cache</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="cache-action-card" onclick="clearCacheType('view')">
                                    <div class="cache-icon bg-blue">
                                        <i class="fa fa-eye"></i>
                                    </div>
                                    <h4>View Cache</h4>
                                    <p class="text-muted">Clear compiled view templates</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="cache-action-card danger" onclick="clearCacheType('all')">
                                    <div class="cache-icon bg-red">
                                        <i class="fa fa-bomb"></i>
                                    </div>
                                    <h4>Clear All</h4>
                                    <p class="text-muted">Clear ALL cache types at once</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="cache-action-card success" onclick="optimizeCache()">
                                    <div class="cache-icon bg-green">
                                        <i class="fa fa-rocket"></i>
                                    </div>
                                    <h4>Optimize</h4>
                                    <p class="text-muted">Optimize cache performance</p>
                                </div>
                            </div>
                        </div>

                        <div class="callout callout-warning" style="margin-bottom: 0; margin-top: 15px;">
                            <h4><i class="fa fa-exclamation-triangle"></i> Important!</h4>
                            <p>Clearing cache may temporarily slow down your application until caches are rebuilt.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache Statistics -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Cache Statistics
                        </h3>
                    </div>
                    <div class="box-body">
                        @if(isset($cache_stats) && count($cache_stats) > 0)
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua"><i class="fa fa-files-o"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cache Files</span>
                                        <span class="info-box-number">{{ $cache_stats['total_files'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-hdd-o"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cache Size</span>
                                        <span class="info-box-number">{{ $cache_stats['total_size'] ?? '0 MB' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Last Cleared</span>
                                        <span class="info-box-number">{{ $cache_stats['last_cleared'] ?? 'Never' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-red"><i class="fa fa-tachometer"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Hit Rate</span>
                                        <span class="info-box-number">{{ $cache_stats['hit_rate'] ?? '0%' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Cache Files Detail --}}
        <div class="row">
            <div class="col-md-12" id="cache-files">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-files-o"></i> Cache Files Detail
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($cache_files) && count($cache_files) > 0)
                        <div class="table-responsive">
                            <table id="cache-files-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-tag"></i> Type</th>
                                        <th><i class="fa fa-folder"></i> Location</th>
                                        <th><i class="fa fa-database"></i> Size</th>
                                        <th><i class="fa fa-clock-o"></i> Modified</th>
                                        <th><i class="fa fa-info-circle"></i> Status</th>
                                        <th><i class="fa fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cache_files as $file)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">
                                                <i class="fa fa-{{ $file['type'] == 'view' ? 'eye' : ($file['type'] == 'route' ? 'road' : 'cog') }}"></i>
                                                {{ ucfirst($file['type']) }}
                                            </strong>
                                        </td>
                                        <td><code style="font-size: 11px;">{{ $file['path'] }}</code></td>
                                        <td><span class="badge bg-blue">{{ $file['size'] }}</span></td>
                                        <td><i class="fa fa-calendar text-muted"></i> {{ $file['modified'] }}</td>
                                        <td>
                                            <span class="label label-{{ $file['exists'] ? 'success' : 'danger' }}">
                                                <i class="fa fa-{{ $file['exists'] ? 'check' : 'times' }}"></i>
                                                {{ $file['exists'] ? 'Exists' : 'Missing' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($file['exists'])
                                            <button type="button" class="btn btn-sm btn-warning" onclick="clearCacheType('{{ $file['type'] }}')">
                                                <i class="fa fa-trash"></i> Clear
                                            </button>
                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center" style="margin-bottom: 0;">
                            <i class="fa fa-info-circle fa-2x"></i>
                            <h4>No Cache Files Found</h4>
                            <p>Unable to retrieve cache file information at this time.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Cache Activity --}}
        <div class="row">
            <div class="col-md-12" id="recent-activity">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-history"></i> Recent Cache Activity
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($recent_cache_activity) && count($recent_cache_activity) > 0)
                        <div class="table-responsive">
                            <table id="activity-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-clock-o"></i> Time</th>
                                        <th><i class="fa fa-bolt"></i> Action</th>
                                        <th><i class="fa fa-tag"></i> Cache Type</th>
                                        <th><i class="fa fa-user"></i> User</th>
                                        <th><i class="fa fa-check-circle"></i> Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_cache_activity as $activity)
                                    <tr>
                                        <td>
                                            <i class="fa fa-calendar text-muted"></i>
                                            {{ $activity['time'] }}
                                        </td>
                                        <td>
                                            <span class="label label-{{ $activity['action_type'] ?? 'default' }}">
                                                <i class="fa fa-flash"></i>
                                                {{ $activity['action'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-primary">
                                                <strong>{{ ucfirst($activity['cache_type']) }}</strong>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa fa-user-circle text-info"></i>
                                            {{ $activity['user'] }}
                                        </td>
                                        <td>
                                            <span class="label label-{{ $activity['success'] ? 'success' : 'danger' }}">
                                                <i class="fa fa-{{ $activity['success'] ? 'check' : 'times' }}"></i>
                                                {{ $activity['success'] ? 'Success' : 'Failed' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center" style="margin-bottom: 0;">
                            <i class="fa fa-history fa-2x"></i>
                            <h4>No Recent Activity</h4>
                            <p>No cache operations have been performed recently.</p>
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
    
    /* Cache Action Cards */
    .cache-action-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 15px;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .cache-action-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        transform: translateY(-5px);
        border-color: #3c8dbc;
    }
    
    .cache-action-card.danger:hover {
        border-color: #dd4b39;
    }
    
    .cache-action-card.success:hover {
        border-color: #00a65a;
    }
    
    .cache-icon {
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
    
    .cache-action-card h4 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .cache-action-card p {
        font-size: 12px;
        margin-bottom: 0;
        line-height: 1.4;
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
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    @if(isset($cache_files) && count($cache_files) > 0)
    $('#cache-files-table').DataTable({
        responsive: true,
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'print']
    });
    @endif
    
    @if(isset($recent_cache_activity) && count($recent_cache_activity) > 0)
    $('#activity-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'print']
    });
    @endif
});

// Show loading overlay
function showLoading(message = 'Processing...') {
    const overlay = $('<div class="loading-overlay">' +
        '<div class="loading-content">' +
        '<div class="loading-spinner"></div>' +
        '<h4>' + message + '</h4>' +
        '<p class="text-muted">Please wait...</p>' +
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
    
    const payload = { cache_type: type };
    console.log('Sending request to clear cache:', payload);
    
    fetch('{{ route("admin.cache.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('Server returned error: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        console.log('Success response:', data);
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
        console.error('Cache clear error:', error);
    });
}

// Optimize Cache
function optimizeCache() {
    if (!confirm('Optimize cache for better performance?')) {
        return;
    }
    
    showLoading('Optimizing cache...');
    
    fetch('{{ route("admin.cache.optimize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('Server returned error: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        console.log('Success response:', data);
        if (data.success) {
            showToast(data.message || 'Cache optimized successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to optimize cache', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while optimizing cache', 'error');
        console.error('Cache optimize error:', error);
    });
}
</script>
@endpush
@endsection
