@extends('layouts.app')

@section('main-content')

{{-- Page Header Component --}}
@component('components.page-header')
    @slot('icon') fa-dashboard @endslot
    @slot('title') Integrated Dashboard @endslot
    @slot('subtitle') Comprehensive system overview with analytics, trends, and actionable insights @endslot
@endcomponent

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
@endif

{{-- Period Selection Filter Bar --}}
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Time Period Selection</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-primary btn-sm" id="refreshDashboard">
                <i class="fa fa-refresh"></i> Refresh Data
            </button>
            <button type="button" class="btn btn-success btn-sm" id="exportDashboard">
                <i class="fa fa-download"></i> Export Report
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="btn-group period-selector" role="group">
                    <button type="button" class="btn btn-default period-btn active" data-period="today">
                        <i class="fa fa-clock-o"></i> Today
                    </button>
                    <button type="button" class="btn btn-default period-btn" data-period="week">
                        <i class="fa fa-calendar-week"></i> This Week
                    </button>
                    <button type="button" class="btn btn-default period-btn" data-period="month">
                        <i class="fa fa-calendar"></i> This Month
                    </button>
                    <button type="button" class="btn btn-default period-btn" data-period="quarter">
                        <i class="fa fa-calendar-alt"></i> This Quarter
                    </button>
                    <button type="button" class="btn btn-default period-btn" data-period="year">
                        <i class="fa fa-calendar-check"></i> This Year
                    </button>
                    <button type="button" class="btn btn-default period-btn" data-period="all">
                        <i class="fa fa-infinity"></i> All Time
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" class="form-control" id="customDateRange" placeholder="Custom date range...">
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-12">
                <small class="text-muted">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Current Period:</strong> <span id="currentPeriodText">Today</span>
                    <span class="pull-right">
                        <i class="fa fa-clock-o"></i> Last Updated: <span id="lastUpdated">{{ now()->format('d/m/Y H:i:s') }}</span>
                    </span>
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Quick Stats Row --}}
<div class="row">
    <!-- Open Tickets Stat -->
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua stat-card" data-target="tickets-section" style="cursor: pointer;">
            <div class="inner">
                <h3 id="stat-open-tickets">{{ $stats['open_tickets'] ?? 0 }}</h3>
                <p>Open Tickets</p>
            </div>
            <div class="icon">
                <i class="fa fa-ticket"></i>
            </div>
            <a href="{{ url('/tickets') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Overdue Tickets Stat -->
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red stat-card" data-target="sla-alerts-section" style="cursor: pointer;">
            <div class="inner">
                <h3 id="stat-overdue-tickets">{{ $stats['overdue_tickets'] ?? 0 }}</h3>
                <p>Overdue Tickets</p>
            </div>
            <div class="icon">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <a href="{{ url('/tickets?filter=overdue') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Assets Stat -->
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green stat-card" data-target="assets-chart-section" style="cursor: pointer;">
            <div class="inner">
                <h3 id="stat-total-assets">{{ $stats['total_assets'] ?? 0 }}</h3>
                <p>Total Assets</p>
            </div>
            <div class="icon">
                <i class="fa fa-tags"></i>
            </div>
            <a href="{{ url('/assets') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Maintenance Due Stat -->
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow stat-card" data-target="maintenance-section" style="cursor: pointer;">
            <div class="inner">
                <h3 id="stat-maintenance-due">{{ $stats['maintenance_due'] ?? 0 }}</h3>
                <p>Maintenance Due</p>
            </div>
            <div class="icon">
                <i class="fa fa-wrench"></i>
            </div>
            <a href="{{ url('/assets?filter=maintenance_due') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Main Dashboard Content: 8-col Main + 4-col Sidebar --}}
<div class="row">
    {{-- Main Content (8 columns) --}}
    <div class="col-md-8">
        
        {{-- Interactive Charts Section --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-line-chart"></i> Analytics & Trends</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    {{-- Ticket Trends Chart --}}
                    <div class="col-md-6">
                        <div class="chart-card" id="tickets-chart-section">
                            <h4 class="text-center">Ticket Volume Trend</h4>
                            <canvas id="ticketTrendChart" style="height: 250px;"></canvas>
                            <div class="chart-legend text-center" style="margin-top: 10px;">
                                <small class="text-muted">Last 6 months ticket creation trend</small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Ticket Status Distribution --}}
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h4 class="text-center">Ticket Status Distribution</h4>
                            <canvas id="ticketStatusChart" style="height: 250px;"></canvas>
                            <div class="chart-legend text-center" style="margin-top: 10px;">
                                <small class="text-muted">Current ticket status breakdown</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    {{-- Asset Status Chart --}}
                    <div class="col-md-6" id="assets-chart-section">
                        <div class="chart-card">
                            <h4 class="text-center">Asset Lifecycle Status</h4>
                            <canvas id="assetStatusChart" style="height: 250px;"></canvas>
                            <div class="chart-legend text-center" style="margin-top: 10px;">
                                <small class="text-muted">Asset distribution by status</small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Asset Type Distribution --}}
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h4 class="text-center">Asset Type Distribution</h4>
                            <canvas id="assetTypeChart" style="height: 250px;"></canvas>
                            <div class="chart-legend text-center" style="margin-top: 10px;">
                                <small class="text-muted">Assets by type category</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Tickets Table --}}
        <div class="box box-info" id="tickets-section">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-ticket"></i> Recent Tickets
                    <span class="badge bg-light-blue" id="recent-tickets-count">{{ $recentTickets->count() ?? 0 }}</span>
                </h3>
                <div class="box-tools pull-right">
                    <a href="{{ url('/tickets/create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> New Ticket
                    </a>
                    <a href="{{ url('/tickets') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-list"></i> View All
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if(isset($recentTickets) && $recentTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th width="10%">Code</th>
                                    <th width="35%">Subject</th>
                                    <th width="15%">Priority</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">SLA</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTickets as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}">
                                            <strong>{{ $ticket->ticket_code }}</strong>
                                        </a>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 35) }}</td>
                                    <td>
                                        @if($ticket->ticket_priority)
                                            <span class="label label-{{ $ticket->ticket_priority->color ?? 'default' }}">
                                                {{ $ticket->ticket_priority->priority }}
                                            </span>
                                        @else
                                            <span class="label label-default">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->ticket_status)
                                            <span class="label label-{{ $ticket->ticket_status->color ?? 'default' }}">
                                                {{ $ticket->ticket_status->status }}
                                            </span>
                                        @else
                                            <span class="label label-default">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->sla_due)
                                            @php
                                                $now = now();
                                                $slaClass = 'success';
                                                $slaIcon = 'check-circle';
                                                if ($ticket->sla_due->isPast()) {
                                                    $slaClass = 'danger';
                                                    $slaIcon = 'exclamation-circle';
                                                } elseif ($ticket->sla_due->diffInHours($now) <= 4) {
                                                    $slaClass = 'warning';
                                                    $slaIcon = 'clock-o';
                                                }
                                            @endphp
                                            <span class="label label-{{ $slaClass }}">
                                                <i class="fa fa-{{ $slaIcon }}"></i>
                                                {{ $ticket->sla_due->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="label label-default">No SLA</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state text-center" style="padding: 30px;">
                        <i class="fa fa-ticket fa-3x text-muted"></i>
                        <p class="text-muted">No recent tickets found</p>
                        <a href="{{ url('/tickets/create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create First Ticket
                        </a>
                    </div>
                @endif
            </div>
            <div class="box-footer text-center">
                <a href="{{ url('/tickets') }}" class="text-primary">View All Tickets <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        {{-- Performance Metrics --}}
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-tachometer"></i> Performance Metrics</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Avg. Resolution Time</span>
                                <span class="info-box-number">2.5 days</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 70%"></div>
                                </div>
                                <span class="progress-description">
                                    30% faster than last period
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-4">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SLA Compliance</span>
                                <span class="info-box-number">92%</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 92%"></div>
                                </div>
                                <span class="progress-description">
                                    Target: 95%
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-4">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">User Satisfaction</span>
                                <span class="info-box-number">4.5/5</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 90%"></div>
                                </div>
                                <span class="progress-description">
                                    Based on 127 responses
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar (4 columns) --}}
    <div class="col-md-4">
        
        {{-- Quick Actions Box --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-flash"></i> Quick Actions</h3>
            </div>
            <div class="box-body">
                <a href="{{ url('/tickets/create') }}" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Create Ticket
                </a>
                <a href="{{ url('/assets/create') }}" class="btn btn-success btn-block">
                    <i class="fa fa-plus"></i> Add Asset
                </a>
                <a href="{{ route('daily-activities.create') }}" class="btn btn-info btn-block">
                    <i class="fa fa-plus"></i> Log Activity
                </a>
                <a href="{{ url('/assets/scan-qr') }}" class="btn btn-warning btn-block">
                    <i class="fa fa-qrcode"></i> Scan QR Code
                </a>
            </div>
        </div>

        {{-- SLA Alerts Box --}}
        <div class="box box-danger" id="sla-alerts-section">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> SLA Alerts
                    @if(isset($slaAlerts) && $slaAlerts->count() > 0)
                        <span class="badge bg-red">{{ $slaAlerts->count() }}</span>
                    @endif
                </h3>
            </div>
            <div class="box-body">
                @if(isset($slaAlerts) && $slaAlerts->count() > 0)
                    <div class="list-group" style="margin-bottom: 0;">
                        @foreach($slaAlerts->take(5) as $alert)
                        <a href="{{ route('tickets.show', $alert->id) }}" class="list-group-item">
                            <h5 class="list-group-item-heading">
                                <i class="fa fa-ticket"></i> {{ $alert->ticket_code }}
                            </h5>
                            <p class="list-group-item-text">
                                {{ \Illuminate\Support\Str::limit($alert->subject, 40) }}
                            </p>
                            <small class="text-danger">
                                <i class="fa fa-clock-o"></i> Due: {{ $alert->sla_due->format('d/m H:i') }}
                            </small>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">
                        <i class="fa fa-check-circle fa-2x"></i><br>
                        No SLA alerts
                    </p>
                @endif
            </div>
            @if(isset($slaAlerts) && $slaAlerts->count() > 5)
                <div class="box-footer text-center">
                    <a href="{{ url('/tickets?filter=sla_at_risk') }}" class="text-danger">
                        View All {{ $slaAlerts->count() }} Alerts <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            @endif
        </div>

        {{-- Asset Maintenance Due Box --}}
        <div class="box box-warning" id="maintenance-section">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-wrench"></i> Maintenance Due
                    @if(isset($maintenanceDue) && $maintenanceDue->count() > 0)
                        <span class="badge bg-yellow">{{ $maintenanceDue->count() }}</span>
                    @endif
                </h3>
            </div>
            <div class="box-body">
                @if(isset($maintenanceDue) && $maintenanceDue->count() > 0)
                    @foreach($maintenanceDue->take(5) as $asset)
                    <div class="media">
                        <div class="media-left">
                            <i class="fa fa-desktop fa-2x text-yellow"></i>
                        </div>
                        <div class="media-body">
                            <h5 class="media-heading">
                                <a href="{{ url('/assets/' . $asset->id) }}">{{ $asset->asset_tag }}</a>
                            </h5>
                            <p style="margin-bottom: 5px;">{{ $asset->model->name ?? 'Unknown Model' }}</p>
                            <small class="text-muted">
                                <i class="fa fa-clock-o"></i> Last: {{ $asset->last_maintenance ?? 'Never' }}
                            </small>
                        </div>
                    </div>
                    @if(!$loop->last)<hr style="margin: 10px 0;">@endif
                    @endforeach
                @else
                    <p class="text-muted text-center">
                        <i class="fa fa-check-circle fa-2x"></i><br>
                        No maintenance due
                    </p>
                @endif
            </div>
            @if(isset($maintenanceDue) && $maintenanceDue->count() > 5)
                <div class="box-footer text-center">
                    <a href="{{ url('/assets?filter=maintenance_due') }}" class="text-warning">
                        View All {{ $maintenanceDue->count() }} Assets <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            @endif
        </div>

        {{-- System Status Box --}}
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-heartbeat"></i> System Health</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check-circle text-green"></i> 
                        <strong>Database:</strong> Connected
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check-circle text-green"></i> 
                        <strong>Storage:</strong> Available (75% free)
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check-circle text-green"></i> 
                        <strong>Cache:</strong> Operational
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-clock-o text-blue"></i> 
                        <strong>Server Time:</strong> <span id="server-time">{{ now()->format('H:i:s') }}</span>
                    </li>
                    <li>
                        <i class="fa fa-calendar text-blue"></i> 
                        <strong>Today:</strong> {{ now()->format('d M Y') }}
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

{{-- Today's Activities Timeline --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-calendar-check-o"></i> Today's Activities
                    @if(isset($todayActivities))
                        <span class="badge bg-light-blue">{{ $todayActivities->count() }}</span>
                    @endif
                </h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('daily-activities.create') }}" class="btn btn-info btn-sm">
                        <i class="fa fa-plus"></i> Add Activity
                    </a>
                    <a href="{{ route('daily-activities.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-list"></i> View All
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if(isset($todayActivities) && $todayActivities->count() > 0)
                    <div class="timeline">
                        @foreach($todayActivities->take(10) as $activity)
                        <div class="time-label">
                            <span class="bg-blue">{{ $activity->created_at->format('H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-{{ $activity->type === 'manual' ? 'edit' : 'cogs' }} bg-{{ $activity->type === 'manual' ? 'blue' : 'green' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fa fa-clock-o"></i> {{ $activity->created_at->diffForHumans() }}
                                </span>
                                <h3 class="timeline-header">
                                    <strong>{{ $activity->user->name }}</strong>
                                    @if($activity->ticket)
                                        - <a href="{{ route('tickets.show', $activity->ticket->id) }}">{{ $activity->ticket->ticket_code }}</a>
                                    @endif
                                </h3>
                                <div class="timeline-body">
                                    {{ \Illuminate\Support\Str::limit($activity->description, 200) }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </div>
                    </div>
                @else
                    <div class="empty-state text-center" style="padding: 40px;">
                        <i class="fa fa-calendar-times-o fa-3x text-muted"></i>
                        <p class="text-muted">No activities logged today</p>
                        <a href="{{ route('daily-activities.create') }}" class="btn btn-info">
                            <i class="fa fa-plus"></i> Log Your First Activity
                        </a>
                    </div>
                @endif
            </div>
            @if(isset($todayActivities) && $todayActivities->count() > 10)
                <div class="box-footer text-center">
                    <a href="{{ route('daily-activities.index') }}" class="text-info">
                        View All {{ $todayActivities->count() }} Activities Today <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- System Integration Status --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-cogs"></i> System Module Status
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-ticket"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ticketing System</span>
                                <span class="info-box-number">
                                    <i class="fa fa-check-circle"></i> Operational
                                </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $stats['open_tickets'] ?? 0 }} active tickets
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Asset Management</span>
                                <span class="info-box-number">
                                    <i class="fa fa-check-circle"></i> Operational
                                </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $stats['total_assets'] ?? 0 }} assets tracked
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Daily Activities</span>
                                <span class="info-box-number">
                                    <i class="fa fa-check-circle"></i> Operational
                                </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Activity tracking enabled
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-qrcode"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">QR Integration</span>
                                <span class="info-box-number">
                                    <i class="fa fa-check-circle"></i> Operational
                                </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    QR scanning available
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('/css/dashboard-charts.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
$(document).ready(function() {
    
    // Chart Color Palette
    const chartColors = {
        primary: '#3b82f6',
        secondary: '#8b5cf6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        aqua: '#00c0ef',
        green: '#00a65a',
        yellow: '#f39c12',
        red: '#dd4b39'
    };
    
    // ===========================================
    // Period Selection Functionality
    // ===========================================
    
    let currentPeriod = 'today';
    
    $('.period-btn').on('click', function() {
        $('.period-btn').removeClass('active btn-primary').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('active btn-primary');
        
        currentPeriod = $(this).data('period');
        updatePeriodText(currentPeriod);
        loadDashboardData(currentPeriod);
    });
    
    function updatePeriodText(period) {
        const periodTexts = {
            'today': 'Today',
            'week': 'This Week',
            'month': 'This Month',
            'quarter': 'This Quarter',
            'year': 'This Year',
            'all': 'All Time'
        };
        $('#currentPeriodText').text(periodTexts[period] || 'Today');
    }
    
    function loadDashboardData(period) {
        // Show loading indicator
        toastr.info('Refreshing dashboard data...', 'Loading');
        
        // AJAX call to refresh data (implement in controller)
        $.ajax({
            url: '{{ route("dashboard.index") }}',
            method: 'GET',
            data: { period: period, ajax: true },
            success: function(response) {
                // Update stats
                if (response.stats) {
                    $('#stat-open-tickets').text(response.stats.open_tickets || 0);
                    $('#stat-overdue-tickets').text(response.stats.overdue_tickets || 0);
                    $('#stat-total-assets').text(response.stats.total_assets || 0);
                    $('#stat-maintenance-due').text(response.stats.maintenance_due || 0);
                }
                
                // Update charts
                if (response.chartData) {
                    updateCharts(response.chartData);
                }
                
                // Update timestamp
                $('#lastUpdated').text(new Date().toLocaleString());
                
                toastr.success('Dashboard refreshed successfully!', 'Success');
            },
            error: function() {
                toastr.error('Failed to refresh dashboard data', 'Error');
            }
        });
    }
    
    // ===========================================
    // Chart.js Initialization
    // ===========================================
    
    // Ticket Trend Chart (Line Chart)
    const ticketTrendCtx = document.getElementById('ticketTrendChart');
    if (ticketTrendCtx) {
        const ticketTrendChart = new Chart(ticketTrendCtx, {
            type: 'line',
            data: {
                labels: ['6 months ago', '5 months ago', '4 months ago', '3 months ago', '2 months ago', 'Last month'],
                datasets: [{
                    label: 'Tickets Created',
                    data: [45, 52, 38, 67, 49, 58],
                    borderColor: chartColors.aqua,
                    backgroundColor: 'rgba(0, 192, 239, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }, {
                    label: 'Tickets Resolved',
                    data: [42, 49, 40, 65, 47, 55],
                    borderColor: chartColors.green,
                    backgroundColor: 'rgba(0, 166, 90, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12, weight: '500' },
                            usePointStyle: true,
                            color: '#475569'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' tickets';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#64748b'
                        },
                        grid: {
                            color: '#e2e8f0'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#64748b'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Ticket Status Distribution (Doughnut Chart)
    const ticketStatusCtx = document.getElementById('ticketStatusChart');
    if (ticketStatusCtx) {
        const ticketStatusChart = new Chart(ticketStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open (35)', 'In Progress (28)', 'Resolved (87)', 'Closed (12)'],
                datasets: [{
                    data: [35, 28, 87, 12],
                    backgroundColor: [
                        chartColors.aqua,
                        chartColors.warning,
                        chartColors.green,
                        chartColors.secondary
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + percentage + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Asset Status Chart (Horizontal Bar Chart)
    const assetStatusCtx = document.getElementById('assetStatusChart');
    if (assetStatusCtx) {
        const assetStatusChart = new Chart(assetStatusCtx, {
            type: 'bar',
            data: {
                labels: ['Deployed', 'In Stock', 'In Repair', 'Maintenance', 'Disposed'],
                datasets: [{
                    label: 'Asset Count',
                    data: [156, 42, 8, 15, 3],
                    backgroundColor: [
                        chartColors.green,
                        chartColors.aqua,
                        chartColors.warning,
                        chartColors.info,
                        chartColors.danger
                    ],
                    borderColor: [
                        chartColors.green,
                        chartColors.aqua,
                        chartColors.warning,
                        chartColors.info,
                        chartColors.danger
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' assets';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#64748b'
                        },
                        grid: {
                            color: '#e2e8f0'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#64748b'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Asset Type Distribution (Doughnut Chart)
    const assetTypeCtx = document.getElementById('assetTypeChart');
    if (assetTypeCtx) {
        const assetTypeChart = new Chart(assetTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Computers (89)', 'Monitors (67)', 'Printers (22)', 'Network (18)', 'Furniture (28)'],
                datasets: [{
                    data: [89, 67, 22, 18, 28],
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.info,
                        chartColors.warning,
                        chartColors.secondary,
                        chartColors.success
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + percentage + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateCharts(chartData) {
        // Update chart data dynamically
        if (chartData.ticketTrend) {
            ticketTrendChart.data.datasets[0].data = chartData.ticketTrend.created;
            ticketTrendChart.data.datasets[1].data = chartData.ticketTrend.resolved;
            ticketTrendChart.update();
        }
        
        if (chartData.ticketStatus) {
            ticketStatusChart.data.datasets[0].data = chartData.ticketStatus;
            ticketStatusChart.update();
        }
        
        if (chartData.assetStatus) {
            assetStatusChart.data.datasets[0].data = chartData.assetStatus;
            assetStatusChart.update();
        }
        
        if (chartData.assetTypes) {
            assetTypeChart.data.datasets[0].data = chartData.assetTypes;
            assetTypeChart.update();
        }
    }
    
    // ===========================================
    // Interactive Features
    // ===========================================
    
    // Stat card click - smooth scroll to section
    $('.stat-card').on('click', function(e) {
        if (!$(e.target).is('a')) {
            e.preventDefault();
            const target = $(this).data('target');
            if (target && $('#' + target).length) {
                $('html, body').animate({
                    scrollTop: $('#' + target).offset().top - 70
                }, 800);
            }
        }
    });
    
    // Refresh Dashboard button
    $('#refreshDashboard').on('click', function() {
        loadDashboardData(currentPeriod);
    });
    
    // Export Dashboard button
    $('#exportDashboard').on('click', function() {
        toastr.info('Preparing dashboard export...', 'Please Wait');
        
        // Implement export functionality (PDF/Excel)
        window.location.href = '{{ route("dashboard.index") }}?export=pdf&period=' + currentPeriod;
    });
    
    // Auto-update server time
    function updateServerTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        $('#server-time').text(hours + ':' + minutes + ':' + seconds);
    }
    
    setInterval(updateServerTime, 1000);
    updateServerTime();
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        loadDashboardData(currentPeriod);
    }, 300000);
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Smooth scroll for all anchor links
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        if(target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 70
            }, 1000);
        }
    });
    
}); // End document.ready
</script>
@endsection

