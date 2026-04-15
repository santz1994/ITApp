@extends('layouts.app')

@section('title', 'Agent Performance')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-users"></i> Agent Performance Dashboard
                </h3>
            </div>
            <div class="box-body">
                <!-- Performance Summary Cards -->
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>{{ $totalAdmins ?? 0 }}</h3>
                                <p>Total Agents</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{ $activeAdmins ?? 0 }}</h3>
                                <p>Active Agents</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user-check"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3>{{ $resolvedTickets ?? 0 }}</h3>
                                <p>Tickets Resolved Today</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>{{ number_format($avgResponseTime ?? 0, 1) }}h</h3>
                                <p>Avg Response Time</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Performance Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Agent Performance Metrics</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-enhanced table-bordered table-striped" id="adminPerformanceTable">
                                        <thead>
                                            <tr>
                                                <th>Agent</th>
                                                <th>Role</th>
                                                <th>Tickets Assigned</th>
                                                <th>Tickets Resolved</th>
                                                <th>Resolution Rate</th>
                                                <th>Avg Response Time</th>
                                                <th>Last Activity</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($adminPerformance) && count($adminPerformance) > 0)
                                                @foreach($adminPerformance as $admin)
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
                                                    <td>{{ $admin->assigned_tickets ?? 0 }}</td>
                                                    <td>{{ $admin->resolved_tickets ?? 0 }}</td>
                                                    <td>
                                                        @php
                                                            $rate = ($admin->assigned_tickets > 0) ? 
                                                                round(($admin->resolved_tickets / $admin->assigned_tickets) * 100, 1) : 0;
                                                        @endphp
                                                        <span class="label @if($rate >= 80) label-success @elseif($rate >= 60) label-warning @else label-danger @endif">
                                                            {{ $rate }}%
                                                        </span>
                                                    </td>
                                                    <td>{{ number_format($admin->avg_response_time ?? 0, 1) }}h</td>
                                                    <td>{{ $admin->last_activity ? $admin->last_activity->diffForHumans() : 'Never' }}</td>
                                                    <td>
                                                        @if(isset($admin->is_online) && $admin->is_online)
                                                            <span class="label label-success">Online</span>
                                                        @else
                                                            <span class="label label-default">Offline</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="8" class="text-center">No admin performance data available</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Charts -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Weekly Ticket Resolution</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="weeklyResolutionChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header with-border">
                                <h3 class="box-title">Response Time Trends</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="responseTimeChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Recent Admin Activities</h3>
                            </div>
                            <div class="box-body">
                                <ul class="timeline">
                                    @if(isset($recentActivities) && count($recentActivities) > 0)
                                        @foreach($recentActivities as $activity)
                                        <li>
                                            <i class="fa fa-user bg-blue"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fa fa-clock-o"></i> {{ $activity->created_at->diffForHumans() }}</span>
                                                <h3 class="timeline-header">{{ $activity->user->name ?? 'Unknown' }}</h3>
                                                <div class="timeline-body">
                                                    {{ $activity->description ?? 'No description available' }}
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    @else
                                        <li>
                                            <i class="fa fa-info bg-gray"></i>
                                            <div class="timeline-item">
                                                <div class="timeline-body">
                                                    No recent activities found.
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('plugins/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with Export Buttons
    @if(isset($adminPerformance) && count($adminPerformance) > 0)
    var table = $('#adminPerformanceTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Admin Performance Report - {{ date("Y-m-d") }}',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Admin Performance Report - {{ date("Y-m-d") }}',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Admin Performance Report',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy',
                className: 'btn btn-default btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            }
        ],
        order: [[4, 'desc']], // Sort by resolution rate
        language: {
            lengthMenu: "Show _MENU_ admins per page",
            info: "Showing _START_ to _END_ of _TOTAL_ admins",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                last: '<i class="fa fa-angle-double-right"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                previous: '<i class="fa fa-angle-left"></i>'
            }
        }
    });
    
    // Move export buttons to header
    table.buttons().container().appendTo($('#adminPerformanceTable').closest('.box').find('.box-title').parent());
    @endif

    // Weekly Resolution Chart
    var weeklyCtx = document.getElementById('weeklyResolutionChart');
    if (weeklyCtx) {
        var weeklyChart = new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($weeklyLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
                datasets: [{
                    label: 'Tickets Resolved',
                    data: {!! json_encode($weeklyData ?? [5, 8, 12, 6, 15, 3, 2]) !!},
                    borderColor: '#00a65a',
                    backgroundColor: 'rgba(0, 166, 90, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Response Time Chart
    var responseCtx = document.getElementById('responseTimeChart');
    if (responseCtx) {
        var responseChart = new Chart(responseCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($responseLabels ?? ['< 1h', '1-4h', '4-8h', '8-24h', '> 24h']) !!},
                datasets: [{
                    label: 'Number of Tickets',
                    data: {!! json_encode($responseData ?? [15, 25, 10, 8, 3]) !!},
                    backgroundColor: [
                        '#00a65a',
                        '#f39c12',
                        '#3c8dbc',
                        '#dd4b39',
                        '#932ab6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables/dataTables.bootstrap.css') }}">
<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > li {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}

.timeline > li:before,
.timeline > li:after {
    content: "";
    display: table;
}

.timeline > li:after {
    clear: both;
}

.timeline > li > .timeline-item {
    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}

.timeline > li > .fa,
.timeline > li > .glyphicon,
.timeline > li > .ion {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #666;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}
</style>
@endsection
