 @extends('layouts.app')

@section('title', 'Ticket Reports')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bar-chart"></i> Ticket Reports
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <!-- Total Tickets Card -->
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{ $totalTickets ?? 0 }}</h3>
                                <p>Total Tickets</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-ticket"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Open Tickets Card -->
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3>{{ $openTickets ?? 0 }}</h3>
                                <p>Open Tickets</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-folder-open"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Closed Tickets Card -->
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue">
                            <div class="inner">
                                <h3>{{ $closedTickets ?? 0 }}</h3>
                                <p>Closed Tickets</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check-circle"></i>
                            </div>
                        </div>
                    </div>

                    <!-- High Priority Card -->
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>{{ $highPriorityTickets ?? 0 }}</h3>
                                <p>High Priority</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets by Status Chart -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Tickets by Status</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="ticketStatusChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets by Priority Chart -->
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header with-border">
                                <h3 class="box-title">Tickets by Priority</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="ticketPriorityChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Recent Tickets</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="recent-tickets-table" class="table table-enhanced table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Assigned To</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($recentTickets) && count($recentTickets) > 0)
                                                @foreach($recentTickets as $ticket)
                                                <tr>
                                                    <td>{{ $ticket->id }}</td>
                                                    <td>{{ $ticket->title }}</td>
                                                    <td>
                                                        <span class="label label-info">{{ $ticket->status->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="label label-warning">{{ $ticket->priority->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>{{ $ticket->assignedTo->name ?? 'Unassigned' }}</td>
                                                    <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center">No tickets found</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
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
<script>
$(document).ready(function() {
    // Tickets by Status Chart
    var statusCtx = document.getElementById('ticketStatusChart');
    if (statusCtx) {
        var statusChart = new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($ticketsByStatus['labels'] ?? ['Open', 'Closed', 'In Progress']) !!},
                datasets: [{
                    data: {!! json_encode($ticketsByStatus['data'] ?? [10, 5, 3]) !!},
                    backgroundColor: [
                        '#f39c12',
                        '#00a65a',
                        '#3c8dbc'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Tickets by Priority Chart
    var priorityCtx = document.getElementById('ticketPriorityChart');
    if (priorityCtx) {
        var priorityChart = new Chart(priorityCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($ticketsByPriority['labels'] ?? ['Low', 'Medium', 'High', 'Critical']) !!},
                datasets: [{
                    label: 'Tickets',
                    data: {!! json_encode($ticketsByPriority['data'] ?? [5, 8, 3, 2]) !!},
                    backgroundColor: [
                        '#00a65a',
                        '#f39c12',
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
    
    // Enhanced Recent Tickets Table with Export Buttons
    @if(isset($recentTickets) && count($recentTickets) > 0)
    var ticketsTable = $('#recent-tickets-table').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Recent Tickets Report - {{ date("Y-m-d") }}',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Recent Tickets Report - {{ date("Y-m-d") }}',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Recent Tickets Report',
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
        order: [[5, 'desc']], // Sort by created date descending
        language: {
            lengthMenu: "Show _MENU_ tickets per page",
            info: "Showing _START_ to _END_ of _TOTAL_ tickets",
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                last: '<i class="fa fa-angle-double-right"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                previous: '<i class="fa fa-angle-left"></i>'
            }
        }
    });
    
    // Move export buttons to header
    ticketsTable.buttons().container().appendTo($('#recent-tickets-table').closest('.box').find('.box-title').parent());
    @endif
});
</script>
@endsection
