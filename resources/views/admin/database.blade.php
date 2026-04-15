@extends('layouts.app')

@section('main-content')
    {{-- Page Header --}}
    @include('components.page-header', [
        'title' => 'Database Administration',
        'subtitle' => 'Database management, optimization, and maintenance',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'dashboard'],
            ['label' => 'Admin Tools', 'url' => route('admin.dashboard'), 'icon' => 'cogs'],
            ['label' => 'Database']
        ],
        'actions' => '
            <a href="' . route('admin.dashboard') . '" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Admin
            </a>
            <button type="button" class="btn btn-success" onclick="backupDatabase()">
                <i class="fa fa-download"></i> Backup Database
            </button>
        '
    ])

    <section class="content">
        {{-- Quick Stats --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $db_stats['total_tables'] ?? 0 }}</h3>
                        <p>Database Tables</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-table"></i>
                    </div>
                    <a href="#tables-list" class="small-box-footer">
                        View Tables <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $db_stats['total_rows'] ?? '0' }}</h3>
                        <p>Total Rows</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-list"></i>
                    </div>
                    <a href="#tables-list" class="small-box-footer">
                        Table Data <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $db_stats['database_size'] ?? '0 MB' }}</h3>
                        <p>Database Size</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hdd-o"></i>
                    </div>
                    <a href="#db-actions" class="small-box-footer">
                        Optimize <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ isset($migrations) ? count($migrations) : 0 }}</h3>
                        <p>Migrations Run</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-code-fork"></i>
                    </div>
                    <a href="#migration-status" class="small-box-footer">
                        Migration History <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Database Information & Actions --}}
        <div class="row">
            {{-- Database Information --}}
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-info-circle"></i> Database Information
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="padding: 10px;">
                        <div class="status-item">
                            <div class="status-icon {{ isset($db_status['connected']) && $db_status['connected'] ? 'bg-green' : 'bg-red' }}">
                                <i class="fa fa-plug"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Connection Status</span>
                                <span class="label label-{{ isset($db_status['connected']) && $db_status['connected'] ? 'success' : 'danger' }}">
                                    <i class="fa fa-{{ isset($db_status['connected']) && $db_status['connected'] ? 'check' : 'times' }}"></i>
                                    {{ isset($db_status['connected']) && $db_status['connected'] ? 'Connected' : 'Disconnected' }}
                                </span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon bg-blue">
                                <i class="fa fa-server"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Driver</span>
                                <span class="status-value">{{ strtoupper($db_info['driver'] ?? 'Unknown') }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon bg-aqua">
                                <i class="fa fa-database"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Database Name</span>
                                <span class="status-value">{{ $db_info['database'] ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon bg-purple">
                                <i class="fa fa-globe"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Host</span>
                                <span class="status-value">{{ $db_info['host'] ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon bg-navy">
                                <i class="fa fa-plug"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Port</span>
                                <span class="status-value">{{ $db_info['port'] ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="status-icon bg-yellow">
                                <i class="fa fa-table"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Total Tables</span>
                                <span class="status-value">{{ $db_stats['total_tables'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="status-item" style="border-bottom: none;">
                            <div class="status-icon bg-green">
                                <i class="fa fa-hdd-o"></i>
                            </div>
                            <div class="status-content">
                                <span class="status-label">Database Size</span>
                                <span class="status-value">{{ $db_stats['database_size'] ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Database Actions --}}
            <div class="col-md-6">
                <div class="box box-success" id="db-actions">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-wrench"></i> Database Operations
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Click any card to perform the database operation.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="db-action-card" onclick="performAction('optimize')">
                                    <div class="db-icon bg-green">
                                        <i class="fa fa-magic"></i>
                                    </div>
                                    <h4>Optimize Tables</h4>
                                    <p class="text-muted">Optimize all database tables</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="db-action-card" onclick="performAction('repair')">
                                    <div class="db-icon bg-yellow">
                                        <i class="fa fa-wrench"></i>
                                    </div>
                                    <h4>Repair Tables</h4>
                                    <p class="text-muted">Repair corrupted tables</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="db-action-card" onclick="performAction('check')">
                                    <div class="db-icon bg-blue">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <h4>Check Tables</h4>
                                    <p class="text-muted">Verify table integrity</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="db-action-card" onclick="performAction('migrate')">
                                    <div class="db-icon bg-aqua">
                                        <i class="fa fa-arrow-up"></i>
                                    </div>
                                    <h4>Run Migrations</h4>
                                    <p class="text-muted">Execute pending migrations</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="db-action-card" onclick="performAction('seed')">
                                    <div class="db-icon bg-purple">
                                        <i class="fa fa-database"></i>
                                    </div>
                                    <h4>Run Seeders</h4>
                                    <p class="text-muted">Populate database with sample data</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-exclamation-triangle"></i> Danger Zone
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-danger">
                            <h4><i class="fa fa-ban"></i> Warning!</h4>
                            <p>These actions can cause <strong>permanent data loss</strong>. Use with extreme caution!</p>
                        </div>

                        <div class="list-group">
                            <a href="javascript:void(0)" onclick="performDangerousAction('reset')" class="list-group-item danger-item">
                                <span class="badge bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                                <h4 class="list-group-item-heading">
                                    <i class="fa fa-refresh text-danger"></i> Reset Database
                                </h4>
                                <p class="list-group-item-text text-muted">Drop all tables and recreate schema</p>
                            </a>
                            <a href="javascript:void(0)" onclick="performDangerousAction('fresh')" class="list-group-item danger-item">
                                <span class="badge bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                                <h4 class="list-group-item-heading">
                                    <i class="fa fa-bolt text-danger"></i> Fresh Migration
                                </h4>
                                <p class="list-group-item-text text-muted">Drop all tables and run fresh migrations</p>
                            </a>
                            <a href="javascript:void(0)" onclick="performDangerousAction('rollback')" class="list-group-item danger-item">
                                <span class="badge bg-orange"><i class="fa fa-undo"></i></span>
                                <h4 class="list-group-item-heading">
                                    <i class="fa fa-arrow-left text-warning"></i> Rollback Migration
                                </h4>
                                <p class="list-group-item-text text-muted">Rollback the last batch of migrations</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Database Tables List --}}
        <div class="row">
            <div class="col-md-12" id="tables-list">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Database Tables
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($tables) && count($tables) > 0)
                        <div class="table-responsive">
                            <table id="tables-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-table"></i> Table Name</th>
                                        <th><i class="fa fa-list-ol"></i> Rows</th>
                                        <th><i class="fa fa-database"></i> Size</th>
                                        <th><i class="fa fa-cog"></i> Engine</th>
                                        <th><i class="fa fa-calendar"></i> Created</th>
                                        <th><i class="fa fa-wrench"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tables as $table)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">
                                                <i class="fa fa-table text-info"></i>
                                                {{ $table['name'] }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-blue">
                                                {{ number_format($table['rows'] ?? 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-green">
                                                {{ $table['size'] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label label-info">
                                                {{ $table['engine'] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa fa-clock-o text-muted"></i>
                                            {{ $table['created'] ?? 'Unknown' }}
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" onclick="viewTable('{{ $table['name'] }}')">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="optimizeTable('{{ $table['name'] }}')">
                                                    <i class="fa fa-magic"></i> Optimize
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center" style="margin-bottom: 0;">
                            <i class="fa fa-info-circle fa-2x"></i>
                            <h4>No Tables Found</h4>
                            <p>Unable to retrieve table information from the database.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Migration Status --}}
        <div class="row">
            <div class="col-md-12" id="migration-status">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-code-fork"></i> Migration History
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if(isset($migrations) && count($migrations) > 0)
                        <div class="table-responsive">
                            <table id="migrations-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-file-code-o"></i> Migration</th>
                                        <th><i class="fa fa-layer-group"></i> Batch</th>
                                        <th><i class="fa fa-clock-o"></i> Executed At</th>
                                        <th><i class="fa fa-check-circle"></i> Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($migrations as $migration)
                                    <tr>
                                        <td>
                                            <code style="font-size: 12px;">{{ $migration['name'] }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple">
                                                Batch #{{ $migration['batch'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa fa-calendar text-muted"></i>
                                            {{ $migration['executed_at'] }}
                                        </td>
                                        <td>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> Completed
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center" style="margin-bottom: 0;">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <h4>No Migrations Found</h4>
                            <p>No migration records found in the database. Run migrations to populate this table.</p>
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
    
    /* Database Action Cards */
    .db-action-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 15px;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .db-action-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        transform: translateY(-5px);
        border-color: #3c8dbc;
    }
    
    .db-icon {
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
    
    .db-action-card h4 {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .db-action-card p {
        font-size: 12px;
        margin-bottom: 0;
        line-height: 1.4;
    }
    
    /* Danger Items */
    .danger-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .danger-item:hover {
        background-color: #fff5f5;
        border-left-color: #dd4b39;
        transform: translateX(5px);
    }
    
    .danger-item .badge {
        padding: 8px 10px;
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
    @if(isset($tables) && count($tables) > 0)
    $('#tables-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']], // Sort by rows descending
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'print']
    });
    @endif
    
    @if(isset($migrations) && count($migrations) > 0)
    $('#migrations-table').DataTable({
        responsive: true,
        order: [[1, 'desc']], // Sort by batch descending
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

// Perform Database Action
function performAction(action) {
    const actionNames = {
        'optimize': 'Optimize Tables',
        'repair': 'Repair Tables',
        'check': 'Check Tables',
        'migrate': 'Run Migrations',
        'seed': 'Run Seeders'
    };
    
    if (!confirm('Are you sure you want to ' + actionNames[action] + '?')) {
        return;
    }
    
    showLoading('Executing ' + actionNames[action] + '...');
    
    fetch('{{ route("admin.database.action") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ action: action })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Action completed successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Action failed', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while executing the action', 'error');
        console.error('Error:', error);
    });
}

// Perform Dangerous Action
function performDangerousAction(action) {
    const actionNames = {
        'reset': 'Reset Database',
        'fresh': 'Fresh Migration',
        'rollback': 'Rollback Migration'
    };
    
    const confirmMsg = 'WARNING: This action (' + actionNames[action] + ') can cause PERMANENT DATA LOSS!\n\n' +
                      'Are you ABSOLUTELY SURE you want to continue?\n\n' +
                      'Type "YES" to confirm:';
    
    const userInput = prompt(confirmMsg);
    if (userInput !== 'YES') {
        showToast('Action cancelled', 'info');
        return;
    }
    
    showLoading('Executing ' + actionNames[action] + '...');
    
    fetch('{{ route("admin.database.danger") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ danger_action: action })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message || 'Dangerous action completed!', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.message || 'Action failed', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while executing the dangerous action', 'error');
        console.error('Error:', error);
    });
}

// View Table
function viewTable(tableName) {
    window.location.href = '{{ route("admin.database.index") }}/' + tableName;
}

// Optimize Single Table
function optimizeTable(tableName) {
    if (!confirm('Optimize table "' + tableName + '"?')) {
        return;
    }
    
    showLoading('Optimizing table ' + tableName + '...');
    
    fetch('{{ route("admin.database.action") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            action: 'optimize-single',
            table: tableName
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast('Table "' + tableName + '" optimized successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to optimize table', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while optimizing the table', 'error');
        console.error('Error:', error);
    });
}

// Backup Database
function backupDatabase() {
    if (!confirm('Create a database backup?')) {
        return;
    }
    
    showLoading('Creating database backup...');
    
    fetch('{{ route("admin.database.backup") }}', {
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
            showToast(data.message || 'Database backup created successfully!', 'success');
            if (data.download_url) {
                window.location.href = data.download_url;
            }
        } else {
            showToast(data.message || 'Failed to create backup', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred while creating backup', 'error');
        console.error('Error:', error);
    });
}
</script>
@endpush
@endsection