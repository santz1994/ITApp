@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Maintenance Logs',
    'subtitle' => 'Asset maintenance and repair history',
    'icon' => 'fa-wrench',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Maintenance Logs']
    ]
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
            <div class="small-box bg-aqua" onclick="filterByStatus('all')">
                <div class="inner">
                    <h3>{{ $maintenanceLogs->total() }}</h3>
                    <p>Total Logs</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('all')">
                    View All <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow" onclick="filterByStatus('planned')">
                <div class="inner">
                    <h3>{{ $maintenanceLogs->where('status', 'planned')->count() }}</h3>
                    <p>Planned</p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-alt"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('planned')">
                    Filter Planned <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-orange" onclick="filterByStatus('in_progress')">
                <div class="inner">
                    <h3>{{ $maintenanceLogs->where('status', 'in_progress')->count() }}</h3>
                    <p>In Progress</p>
                </div>
                <div class="icon">
                    <i class="fa fa-cog fa-spin"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('in_progress')">
                    Filter In Progress <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green" onclick="filterByStatus('completed')">
                <div class="inner">
                    <h3>{{ $maintenanceLogs->where('status', 'completed')->count() }}</h3>
                    <p>Completed</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('completed')">
                    Filter Completed <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            {{-- Advanced Filters --}}
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
                    <form method="GET" action="{{ route('maintenance.index') }}" id="filter-form">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-laptop"></i> Asset</label>
                                    <select name="asset_id" class="form-control select2">
                                        <option value="">All Assets</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->asset_tag }} - {{ $asset->model_name ?? 'Unknown Model' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-info-circle"></i> Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-wrench"></i> Maintenance Type</label>
                                    <select name="maintenance_type" class="form-control">
                                        <option value="">All Types</option>
                                        @foreach($maintenanceTypes as $type)
                                            <option value="{{ $type }}" {{ request('maintenance_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fa fa-user"></i> Performed By</label>
                                    <input type="text" name="performed_by" class="form-control" value="{{ request('performed_by') }}" placeholder="Technician name...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('maintenance.index') }}" class="btn btn-default">
                                    <i class="fa fa-times"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-wrench"></i> All Maintenance Logs
                        <span class="count-badge">{{ $maintenanceLogs->total() }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('maintenance.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add Maintenance Log
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table id="table" class="table table-bordered table-striped table-hover table-enhanced">
                        <thead>
                            <tr>
                                <th><i class="fa fa-laptop"></i> Asset</th>
                                <th><i class="fa fa-wrench"></i> Type</th>
                                <th><i class="fa fa-file-text"></i> Description</th>
                                <th><i class="fa fa-info-circle"></i> Status</th>
                                <th><i class="fa fa-calendar"></i> Scheduled</th>
                                <th><i class="fa fa-user"></i> Performed By</th>
                                <th><i class="fa fa-money-bill"></i> Cost</th>
                                <th style="width: 150px;"><i class="fa fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenanceLogs as $log)
                                <tr>
                                    <td>
                                        <strong>{{ $log->asset->asset_tag ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $log->asset->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'preventive' => 'info',
                                                'corrective' => 'warning',
                                                'repair' => 'danger',
                                                'inspection' => 'primary'
                                            ];
                                        @endphp
                                        <span class="label label-{{ $typeColors[$log->maintenance_type] ?? 'default' }}">
                                            {{ ucfirst($log->maintenance_type) }}
                                        </span>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($log->description, 50) }}</td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'planned' => 'label-default',
                                                'in_progress' => 'label-warning', 
                                                'completed' => 'label-success',
                                                'cancelled' => 'label-danger'
                                            ];
                                        @endphp
                                        <span class="label {{ $statusClass[$log->status] ?? 'label-default' }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->scheduled_at)
                                            {{ $log->scheduled_at->format('M d, Y') }}<br>
                                            <small class="text-muted">{{ $log->scheduled_at->format('h:i A') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->performedBy->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($log->cost)
                                            <strong>Rp {{ number_format($log->cost, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('maintenance.show', $log->id) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('maintenance.edit', $log->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                                    onclick="deleteMaintenance({{ $log->id }})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-maintenance-{{ $log->id }}" method="POST" action="{{ route('maintenance.destroy', $log->id) }}" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center empty-state">
                                        <i class="fa fa-wrench fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                                        <p>No maintenance logs found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                @if($maintenanceLogs->hasPages())
                    <div class="box-footer clearfix">
                        {{ $maintenanceLogs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Delete maintenance log confirmation
window.deleteMaintenance = function(id) {
    if(confirm('Are you sure you want to delete this maintenance log?')) {
        document.getElementById('delete-maintenance-' + id).submit();
    }
};

$(document).ready(function() {
    @if($maintenanceLogs->count() > 0)
    // Enhanced DataTable with export buttons
    var table = $('#table').DataTable({
        responsive: true,
        dom: 'lfrtip', // Remove 'B' to prevent duplicate buttons
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6] 
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-csv"></i> CSV',
                className: 'btn btn-info btn-sm',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6] 
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6] 
                },
                orientation: 'landscape'
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy',
                className: 'btn btn-default btn-sm',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6] 
                }
            }
        ],
        columnDefs: [
            { orderable: false, targets: 7 }
        ],
        order: [[4, "desc"]], // Sort by scheduled date desc
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search maintenance logs...",
            lengthMenu: "Show _MENU_ logs",
            info: "Showing _START_ to _END_ of _TOTAL_ maintenance logs",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                last: '<i class="fa fa-angle-double-right"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                previous: '<i class="fa fa-angle-left"></i>'
            }
        }
    });

    // Move export buttons to header
    table.buttons().container().appendTo($('.box-header .box-title').parent());
    @endif

    // Stat card filtering
    window.filterByStatus = function(status) {
        if (status === 'all') {
            table.search('').draw();
        } else {
            var searchTerm = status.replace('_', ' ');
            table.search(searchTerm).draw();
        }
    };

    // Initialize Select2 for asset dropdown
    $('.select2').select2({
        placeholder: 'Select an asset...',
        allowClear: true
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush

@endsection