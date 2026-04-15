@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-widgets.css') }}">
@endpush

@section('main-content')
    @include('components.page-header', [
        'title' => 'Management Dashboard',
        'subtitle' => 'Strategic overview and KPI metrics',
        'icon' => 'fa-briefcase',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'fa-dashboard'],
            ['label' => 'Management Dashboard', 'active' => true]
        ]
    ])

    @include('components.loading-overlay')

    <section class="content">
        <!-- Modern KPI Cards - Row 1: Tickets Overview -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('tickets.index', ['filter' => 'today']) }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-primary">
                            <i class="fa fa-ticket"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $overview['total_tickets_today'] ?? 0 }}</h3>
                            <p class="kpi-label">Today's Tickets</p>
                            <span class="kpi-trend neutral">
                                <i class="fa fa-calendar-o"></i> Real-time count
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('tickets.index', ['filter' => 'month']) }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-info">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $overview['total_tickets_month'] ?? 0 }}</h3>
                            <p class="kpi-label">This Month</p>
                            @if(isset($overview['month_growth']) && $overview['month_growth'] > 0)
                            <span class="kpi-trend positive">
                                <i class="fa fa-arrow-up"></i> {{ $overview['month_growth'] }}% from last month
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('tickets.index', ['status' => 'overdue']) }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-danger">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $overview['overdue_tickets'] ?? 0 }}</h3>
                            <p class="kpi-label">Overdue Tickets</p>
                            <span class="kpi-trend negative">
                                <i class="fa fa-warning"></i> Requires attention
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('tickets.index', ['status' => 'unassigned']) }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-warning">
                            <i class="fa fa-question-circle"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $overview['unassigned_tickets'] ?? 0 }}</h3>
                            <p class="kpi-label">Unassigned</p>
                            <span class="kpi-trend neutral">
                                <i class="fa fa-user-plus"></i> Needs assignment
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Overdue Tickets Widget Row -->
        <div class="row">
            @include('management.widgets.overdue-tickets')
        </div>

        <!-- Modern KPI Cards - Row 2: System Overview -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('assets.index') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-success">
                            <i class="fa fa-desktop"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $overview['total_assets'] ?? 0 }}</h3>
                            <p class="kpi-label">Total Assets</p>
                            @if(isset($overview['assets_growth']) && $overview['assets_growth'] > 0)
                            <span class="kpi-trend positive">
                                <i class="fa fa-arrow-up"></i> {{ $overview['assets_growth'] }}% growth
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('users.index') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-purple">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $overview['active_admins'] ?? 0 }}</h3>
                            <p class="kpi-label">Active Admins</p>
                            <span class="kpi-trend positive">
                                <i class="fa fa-circle"></i> Online now
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('sla.dashboard') }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon {{ ($sla_compliance['compliance_rate'] ?? 0) >= 90 ? 'bg-success' : 'bg-warning' }}">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $sla_compliance['compliance_rate'] ?? 0 }}%</h3>
                            <p class="kpi-label">SLA Compliance</p>
                            @if(($sla_compliance['compliance_rate'] ?? 0) >= 90)
                            <span class="kpi-trend positive">
                                <i class="fa fa-check-circle"></i> Excellent performance
                            </span>
                            @else
                            <span class="kpi-trend negative">
                                <i class="fa fa-warning"></i> Needs improvement
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="{{ route('assets.index', ['status' => 'in_use']) }}" style="text-decoration: none; color: inherit;">
                    <div class="kpi-card">
                        <div class="kpi-icon bg-teal">
                            <i class="fa fa-cogs"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value">{{ $asset_overview['in_use'] ?? 0 }}</h3>
                            <p class="kpi-label">Assets In Use</p>
                            @if(isset($asset_overview['utilization_rate']))
                            <span class="kpi-trend neutral">
                                <i class="fa fa-percent"></i> {{ $asset_overview['utilization_rate'] }}% utilization
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Charts and Performance -->
        <div class="row">
            <!-- Ticket Trends Chart -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Ticket Trends (Last 30 Days)</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="ticketTrendsChart" width="400" height="150"></canvas>
                    </div>
                </div>
            </div>

            <!-- Asset Overview -->
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Asset Status Distribution</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="assetStatusChart" width="200" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent Performance Summary -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Top Performing Agents</h3>
                        <div class="box-tools pull-right">
                            <a href="{{ route('management.admin-performance') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="admin-performance-table" class="table table-enhanced table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Agent</th>
                                        <th>Role</th>
                                        <th>Tickets Assigned</th>
                                        <th>Tickets Resolved</th>
                                        <th>Resolution Rate</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($admin_performance) && count($admin_performance) > 0)
                                        @foreach(array_slice($admin_performance, 0, 5) as $performanceData)
                                            @php
                                                $admin = $performanceData['admin'];
                                                $metrics = $performanceData['metrics'] ?? [];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <img src="{{ asset('img/avatar.png') }}" class="img-circle" style="width: 30px; height: 30px;">
                                                    {{ $admin->name }}
                                                </td>
                                                <td>
                                                    @if($admin->roles->count() > 0)
                                                        @foreach($admin->roles as $role)
                                                            <span class="label label-primary">{{ $role->name }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="label label-default">No Role</span>
                                                    @endif
                                                </td>
                                                <td>{{ $metrics['total_assigned'] ?? 0 }}</td>
                                                <td>{{ $metrics['total_completed'] ?? 0 }}</td>
                                                <td>
                                                    @php
                                                        $rate = $metrics['completion_rate'] ?? 0;
                                                    @endphp
                                                    <span class="label @if($rate >= 80) label-success @elseif($rate >= 60) label-warning @else label-danger @endif">
                                                        {{ number_format($rate, 1) }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($admin->adminOnlineStatus && $admin->adminOnlineStatus->is_online)
                                                        <span class="label label-success">Online</span>
                                                    @else
                                                        <span class="label label-default">Offline</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No agent performance data available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Quick Actions</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('management.admin-performance') }}" class="btn btn-app">
                                    <i class="fa fa-users"></i> Admin Performance
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('management.ticket-reports') }}" class="btn btn-app">
                                    <i class="fa fa-ticket"></i> Ticket Reports
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('management.asset-reports') }}" class="btn btn-app">
                                    <i class="fa fa-desktop"></i> Asset Reports
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ url('/system/logs') }}" class="btn btn-app">
                                    <i class="fa fa-file-text"></i> System Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script src="{{ asset('plugins/chartjs/Chart.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Ticket Trends Chart
    var trendsCtx = document.getElementById('ticketTrendsChart');
    if (trendsCtx) {
        var ticketTrendsChart = new Chart(trendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($ticket_trends->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('M d'); })) !!},
                datasets: [{
                    label: 'Created',
                    data: {!! json_encode($ticket_trends->pluck('created')) !!},
                    borderColor: 'rgb(60, 141, 188)',
                    backgroundColor: 'rgba(60, 141, 188, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Resolved',
                    data: {!! json_encode($ticket_trends->pluck('resolved')) !!},
                    borderColor: 'rgb(0, 166, 90)',
                    backgroundColor: 'rgba(0, 166, 90, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Asset Status Chart
    var assetCtx = document.getElementById('assetStatusChart');
    if (assetCtx) {
        var assetChart = new Chart(assetCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['In Use', 'In Stock', 'In Repair', 'Disposed'],
                datasets: [{
                    data: [
                        {{ $asset_overview['in_use'] ?? 0 }},
                        {{ $asset_overview['in_stock'] ?? 0 }},
                        {{ $asset_overview['in_repair'] ?? 0 }},
                        {{ $asset_overview['disposed'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#00a65a',
                        '#3c8dbc',
                        '#f39c12',
                        '#dd4b39'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Enhanced Admin Performance Table with Export Buttons
    @if(isset($admin_performance) && count($admin_performance) > 0)
    var adminTable = $('#admin-performance-table').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 5,
        lengthChange: false,
        searching: false,
        ordering: true,
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Admin Performance - {{ date("Y-m-d") }}',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Admin Performance - {{ date("Y-m-d") }}',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Admin Performance Report',
                orientation: 'landscape',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy',
                className: 'btn btn-default btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            }
        ],
        order: [[4, 'desc']], // Sort by resolution rate descending
        language: {
            info: "Showing _START_ to _END_ of _TOTAL_ agents",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                last: '<i class="fa fa-angle-double-right"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                previous: '<i class="fa fa-angle-left"></i>'
            }
        }
    });
    
    // Move export buttons to header
    adminTable.buttons().container().appendTo($('#admin-performance-table').closest('.box').find('.box-title').parent());
    @endif
    
    // Hide loading overlay when charts are loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (typeof hideLoadingOverlay === 'function') {
                hideLoadingOverlay();
            }
        }, 500);
    });
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection