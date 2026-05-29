@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-charts.css') }}">
@endpush

@section('main-content')

@role(['super-admin', 'admin'])

{{-- Page Header Component --}}
@component('components.page-header')
    @slot('icon') fa-dashboard @endslot
    @slot('title') Dashboard @endslot
    @slot('subtitle') Welcome back! Here's what's happening with your assets and tickets today. @endslot
@endcomponent

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<!-- Dashboard Section -->
<section class="dashboard-container">

    {{-- Quick Stats Row --}}
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua" style="cursor: default;">
                <div class="inner">
                        <h3>{{ isset($assetStats) ? $assetStats['total_assets'] : 0 }}</h3>
                        <p>Total Inventory Items</p>
                </div>
                <div class="icon">
                    <i class="fa fa-cube"></i>
                </div>
                    <a href="#" class="small-box-footer">
                        View Details <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow" onclick="window.location.href='{{ route('tickets.index') }}'" style="cursor: pointer;">
                <div class="inner">
                    @php
                        $closedStatusIds = \App\TicketsStatus::whereIn('status', ['Closed', 'Resolved'])->pluck('id');
                        $openTickets = \App\Ticket::whereNotIn('ticket_status_id', $closedStatusIds)->count();
                    @endphp
                    <h3>{{ $openTickets ?? 0 }}</h3>
                    <p>Open Tickets</p>
                </div>
                <div class="icon">
                    <i class="fa fa-ticket"></i>
                </div>
                <a href="{{ route('tickets.index') }}" class="small-box-footer">
                    Manage Tickets <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ isset($movements) ? $movements->count() : 0 }}</h3>
                    <p>Recent Movements</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exchange"></i>
                </div>
                <a href="#movements-section" class="small-box-footer">
                    View Activity <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red" onclick="window.location.href='{{ route('tickets.index') }}?filter=overdue'" style="cursor: pointer;">
                <div class="inner">
                    @php
                        $closedStatusIds = \App\TicketsStatus::whereIn('status', ['Closed', 'Resolved'])->pluck('id');
                        $overdue = \App\Ticket::where('sla_due', '<', now())
                            ->whereNotIn('ticket_status_id', $closedStatusIds)
                            ->count();
                    @endphp
                    <h3>{{ $overdue ?? 0 }}</h3>
                    <p>SLA Breaches</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('tickets.index') }}" class="small-box-footer">
                    View Urgent <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Section -->
    <h2 class="charts-section-title">
        <i class="fa fa-pie-chart"></i> Analytics & Insights
    </h2>

    <div class="charts-grid">
        <!-- Asset Distribution Chart -->
        <div class="chart-card">
                <div class="chart-header">
                <div>
                    <h3 class="chart-title">
                        <i class="fa fa-cube"></i> Asset Distribution by Type
                    </h3>
                    <p class="chart-subtitle">Breakdown of all tracked assets</p>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-placeholder">
                    <canvas id="assetTypeChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color legend-color-primary"></span>
                    <span>Computers</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-secondary"></span>
                    <span>Peripherals</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-success"></span>
                    <span>Furniture</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-warning"></span>
                    <span>Other</span>
                </div>
            </div>
        </div>

        <!-- Ticket Status Chart -->
        <div class="chart-card">
                <div class="chart-header">
                <div>
                    <h3 class="chart-title">
                        <i class="fa fa-tasks"></i> Ticket Status Overview
                    </h3>
                    <p class="chart-subtitle">Current state of all support tickets</p>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-placeholder">
                    <canvas id="ticketStatusChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color legend-color-primary"></span>
                    <span>Open</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-warning"></span>
                    <span>In Progress</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-success"></span>
                    <span>Resolved</span>
                </div>
            </div>
        </div>

        <!-- Monthly Ticket Trend Chart -->
        <div class="chart-card">
                <div class="chart-header">
                <div>
                    <h3 class="chart-title">
                        <i class="fa fa-line-chart"></i> Ticket Trend (6 Months)
                    </h3>
                    <p class="chart-subtitle">Ticket volume over time</p>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-placeholder">
                    <canvas id="ticketTrendChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color legend-color-primary"></span>
                    <span>Created</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-success"></span>
                    <span>Resolved</span>
                </div>
            </div>
        </div>

        <!-- Asset Lifecycle Status Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">
                        <i class="fa fa-heartbeat"></i> Asset Lifecycle Status
                    </h3>
                    <p class="chart-subtitle">Asset condition and depreciation</p>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-placeholder">
                    <canvas id="assetStatusChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color legend-color-success"></span>
                    <span>Active</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-warning"></span>
                    <span>Inactive</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color legend-color-danger"></span>
                    <span>Disposed</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row">
        {{-- Left Column: Recent Activity (8 columns) --}}
        <div class="col-md-8">
            
            {{-- Recent Activity Feed --}}
            <div class="box box-primary" id="movements-section">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-clock-o"></i> Recent Activity
                    </h3>
                    <div class="box-tools pull-right">
                        <span class="label label-primary">Last 5 actions</span>
                    </div>
                </div>
                <div class="box-body">
                    @if(isset($movements) && $movements->count() > 0)
                        <ul class="timeline timeline-inverse">
                            @foreach($movements as $movement)
                                    @php
                                    $createdDate = \Carbon\Carbon::parse($movement->created_at);
                                    $asset = $movement->asset ?? null;
                                @endphp
                                
                                <li>
                                    <i class="fa fa-exchange bg-blue"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fa fa-clock-o"></i> {{ $createdDate->diffForHumans() }}
                                        </span>
                                        <h3 class="timeline-header">
                                            <strong>{{ optional($movement->user)->name ?? 'System' }}</strong> performed an inventory movement
                                            <span>{{ $asset->asset_tag ?? 'N/A' }}</span>
                                        </h3>
                                        <div class="timeline-body">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <strong>Asset:</strong> {{ $asset->name ?? 'N/A' }}<br>
                                                    <strong>Model:</strong> {{ optional($asset->model)->asset_model ?? 'N/A' }}
                                                </div>
                                                <div class="col-sm-6">
                                                    <strong>Location:</strong> {{ optional($movement->location)->location_name ?? 'N/A' }}<br>
                                                    <strong>Status:</strong> 
                                                    @if($movement->status)
                                                        <span class="label" style="background-color: {{ $movement->status->color ?? '#999' }}">
                                                            {{ $movement->status->name }}
                                                        </span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            
                            <li>
                                <i class="fa fa-clock-o bg-gray"></i>
                            </li>
                        </ul>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-history fa-3x text-muted"></i>
                            <p class="lead">No Recent Activity</p>
                            <p class="text-muted">Asset movements and changes will appear here</p>
                        </div>
                    @endif
                </div>
                @if(isset($movements) && $movements->count() > 0)
                <div class="box-footer text-center">
                    <a href="#" class="btn btn-default">View All Activity</a>
                </div>
                @endif
            </div>

            {{-- Recent Assets --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-cube"></i> Recently Added Assets
                    </h3>
                    <div class="box-tools pull-right">
                        <span class="badge count-badge bg-green">{{ isset($recentAssets) ? $recentAssets->count() : 0 }}</span>
                    </div>
                </div>
                <div class="box-body no-padding">
                    @if(false && isset($recentAssets) && $recentAssets->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Asset Tag</th>
                                    <th>Model</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAssets as $asset)
                                <tr>
                                    <td><strong class="text-primary">{{ $asset->asset_tag }}</strong></td>
                                    <td>{{ optional($asset->model)->asset_model ?? 'N/A' }}</td>
                                    <td>
                                        @if($asset->location)
                                            <i class="fa fa-map-marker text-primary"></i> {{ $asset->location->location_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset->status)
                                            <span class="label" style="background-color: {{ $asset->status->color ?? '#999' }}">
                                                {{ $asset->status->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $asset->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state" style="padding: 30px;">
                            <i class="fa fa-cube fa-3x text-muted"></i>
                            <p class="text-muted">No recent assets</p>
                        </div>
                    @endif
                </div>
                {{-- Assets section removed (legacy) --}}
            </div>

        </div>

        {{-- Right Sidebar (4 columns) --}}
        <div class="col-md-4">
            
            {{-- Quick Actions --}}
            <div class="box box-solid box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    {{-- Add New Asset removed (legacy) --}}
                    <a href="{{ route('tickets.create') }}" class="btn btn-warning btn-block" style="margin-bottom: 10px;">
                        <i class="fa fa-ticket"></i> Create Ticket
                    </a>
                    {{-- Asset quick actions removed (legacy) --}}
                </div>
            </div>

            {{-- System Overview --}}
            <div class="box box-solid box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> System Overview</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-building"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Locations</span>
                            <span class="info-box-number">{{ isset($locationCount) ? $locationCount : \App\Location::count() }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-sitemap"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Divisions</span>
                            <span class="info-box-number">{{ isset($divisionCount) ? $divisionCount : \App\Division::count() }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Users</span>
                            <span class="info-box-number">{{ \App\User::count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Status --}}
            <div class="box box-solid box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-heartbeat"></i> System Status</h3>
                </div>
                <div class="box-body">
                    <ul class="list-unstyled" style="margin-bottom: 0;">
                        <li style="padding: 8px 0; border-bottom: 1px solid #f4f4f4;">
                            <i class="fa fa-check-circle text-green"></i> <strong>Database:</strong> 
                            <span class="pull-right text-muted">Connected</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid #f4f4f4;">
                            <i class="fa fa-check-circle text-green"></i> <strong>Storage:</strong> 
                            <span class="pull-right text-muted">Available</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid #f4f4f4;">
                            <i class="fa fa-clock-o text-blue"></i> <strong>Server Time:</strong> 
                            <span class="pull-right text-muted" id="server-time-sidebar">{{ now()->format('H:i:s') }}</span>
                        </li>
                        <li style="padding: 8px 0;">
                            <i class="fa fa-calendar text-blue"></i> <strong>Today:</strong> 
                            <span class="pull-right text-muted">{{ now()->format('d M Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Performance Summary --}}
            <div class="box box-solid box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-line-chart"></i> Performance Summary</h3>
                </div>
                <div class="box-body">
                    @php
                        $totalTickets = \App\Ticket::count();
                        $closedStatusIds = \App\TicketsStatus::whereIn('status', ['Closed', 'Resolved'])->pluck('id');
                        $closedTickets = \App\Ticket::whereIn('ticket_status_id', $closedStatusIds)->count();
                        $onTimePercentage = $totalTickets > 0 ? round(($closedTickets / $totalTickets) * 100) : 0;
                    @endphp
                    
                    <p><i class="fa fa-check text-success"></i> <strong>Completed Tickets:</strong></p>
                    <div class="progress" style="margin-bottom: 15px;">
                        <div class="progress-bar progress-bar-success" style="width: {{ $onTimePercentage }}%">
                            {{ $onTimePercentage }}%
                        </div>
                    </div>
                    
                    <p><i class="fa fa-star text-yellow"></i> <strong>Overall Rating:</strong></p>
                    <p class="text-center" style="font-size: 24px; margin: 10px 0;">
                        @if($onTimePercentage >= 80)
                            😊 Excellent
                        @elseif($onTimePercentage >= 60)
                            🙂 Good
                        @else
                            😐 Needs Improvement
                        @endif
                    </p>
                    
                    <hr>
                    
                    <p class="text-muted text-center" style="margin: 0;">
                        <small>Keep up the great work!</small>
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

@endrole

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
$(document).ready(function() {
    // Auto-update server time in sidebar
    const timeElement = document.getElementById('server-time-sidebar');
    if (timeElement) {
        setInterval(function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            timeElement.textContent = hours + ':' + minutes + ':' + seconds;
        }, 1000);
    }
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if(target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 70
            }, 1000);
        }
    });
    
    // Chart Color Palette
    const chartColors = {
            primary: '#3b82f6',
            secondary: '#8b5cf6',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4'
        };

        // Asset Type Distribution Pie Chart
        const assetTypeCtx = document.getElementById('assetTypeChart');
        if (assetTypeCtx) {
            @php
                $categories = $assetsByCategory ?? collect();
                $categoryLabels = $categories->keys()->map(fn($key) => "$key ({$categories[$key]})")->toArray();
                $categoryData = $categories->values()->toArray();
            @endphp
            
            new Chart(assetTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoryLabels) !!},
                    datasets: [{
                        data: {!! json_encode($categoryData) !!},
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.secondary,
                            chartColors.success,
                            chartColors.warning,
                            chartColors.info,
                            chartColors.danger
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12, weight: '500' },
                                usePointStyle: true,
                                color: '#475569'
                            }
                        }
                    }
                }
            });
        }

        // Ticket Status Overview Pie Chart
        const ticketStatusCtx = document.getElementById('ticketStatusChart');
        if (ticketStatusCtx) {
            @php
                $statuses = $ticketsByStatus ?? collect();
                $statusLabels = $statuses->keys()->map(fn($key) => "$key ({$statuses[$key]})")->toArray();
                $statusData = $statuses->values()->toArray();
            @endphp
            
            new Chart(ticketStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($statusLabels) !!},
                    datasets: [{
                        data: {!! json_encode($statusData) !!},
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.warning,
                            chartColors.success,
                            chartColors.danger,
                            chartColors.info
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12, weight: '500' },
                                usePointStyle: true,
                                color: '#475569'
                            }
                        }
                    }
                }
            });
        }

        // Ticket Trend Line Chart (6 Months)
        const ticketTrendCtx = document.getElementById('ticketTrendChart');
        if (ticketTrendCtx) {
            @php
                $months = [];
                for ($i = 5; $i >= 0; $i--) {
                    $months[] = \Carbon\Carbon::now()->subMonths($i)->format('M');
                }
                $ticketsCreated = $monthlyTickets ?? array_fill(0, 6, 0);
                $ticketsResolved = $monthlyTicketsResolved ?? array_fill(0, 6, 0);
            @endphp
            
            new Chart(ticketTrendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($months) !!},
                    datasets: [
                        {
                            label: 'Created',
                            data: {!! json_encode($ticketsCreated) !!},
                            borderColor: chartColors.primary,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.primary,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Resolved',
                            data: {!! json_encode($ticketsResolved) !!},
                            borderColor: chartColors.success,
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.success,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12, weight: '500' },
                                usePointStyle: true,
                                color: '#475569'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#64748b',
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(226, 232, 240, 0.5)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#64748b',
                                font: { size: 11 }
                            },
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            });
        }

        // Asset Lifecycle Status Bar Chart
        const assetStatusCtx = document.getElementById('assetStatusChart');
        if (assetStatusCtx) {
            @php
                $lifecycleStatuses = $assetsByStatus ?? collect();
                $lifecycleLabels = $lifecycleStatuses->keys()->toArray();
                $lifecycleData = $lifecycleStatuses->values()->toArray();
                $lifecycleColors = [];
                foreach ($lifecycleLabels as $label) {
                    if (stripos($label, 'active') !== false || stripos($label, 'in use') !== false) {
                        $lifecycleColors[] = '#10b981'; // success
                    } elseif (stripos($label, 'inactive') !== false || stripos($label, 'stock') !== false) {
                        $lifecycleColors[] = '#f59e0b'; // warning
                    } elseif (stripos($label, 'disposed') !== false || stripos($label, 'broken') !== false) {
                        $lifecycleColors[] = '#ef4444'; // danger
                    } else {
                        $lifecycleColors[] = '#06b6d4'; // info
                    }
                }
            @endphp
            
            new Chart(assetStatusCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($lifecycleLabels) !!},
                    datasets: [{
                        label: 'Number of Assets',
                        data: {!! json_encode($lifecycleData) !!},
                        backgroundColor: {!! json_encode($lifecycleColors) !!},
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: '#64748b',
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(226, 232, 240, 0.5)',
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                color: '#64748b',
                                font: { size: 11, weight: '500' }
                            },
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            });
        }
}); // End document.ready
</script>
@endpush

@endsection
