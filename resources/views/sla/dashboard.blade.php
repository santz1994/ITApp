@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <!-- Header -->
        @include('components.page-header', [
            'title' => 'SLA Dashboard',
            'subtitle' => 'Monitor and track Service Level Agreement performance',
            'icon' => 'fa-tachometer-alt',
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => url('/home'), 'icon' => 'fa-dashboard'],
                ['label' => 'SLA Dashboard', 'active' => true]
            ],
            'actions' => '<a href="'.route('sla.index').'" class="btn btn-primary">
                <i class="fa fa-cog"></i> <span class="hidden-xs">Manage Policies</span>
            </a>'
        ])

        <!-- Filters -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-filter"></i> Filters
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <form method="GET" action="{{ route('sla.dashboard') }}" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date"><i class="fa fa-calendar"></i> Start Date</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ $startDate ?? request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date"><i class="fa fa-calendar"></i> End Date</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="{{ $endDate ?? request('end_date', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="priority_id"><i class="fa fa-flag"></i> Priority</label>
                                <select class="form-control" id="priority_id" name="priority_id">
                                    <option value="">All Priorities</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" 
                                                {{ ($priorityId ?? request('priority_id')) == $priority->id ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="assigned_to"><i class="fa fa-user"></i> Assigned To</label>
                                <select class="form-control" id="assigned_to" name="assigned_to">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ ($assignedTo ?? request('assigned_to')) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('sla.dashboard') }}" class="btn btn-default">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $metrics['total_tickets'] ?? 0 }}</h3>
                        <p>Total Tickets</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-ticket"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $metrics['sla_met'] ?? 0 }}</h3>
                        <p>SLA Met</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        {{ $metrics['sla_compliance_rate'] ?? 0 }}% compliance <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $metrics['sla_breached'] ?? 0 }}</h3>
                        <p>SLA Breached</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $metrics['at_risk'] ?? 0 }}</h3>
                        <p>At Risk</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-fire"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breached SLA Tickets -->
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Breached SLA Tickets
                    <span class="badge bg-red">{{ $breachedTickets->count() }}</span>
                </h3>
            </div>
            <div class="box-body">
                @if($breachedTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Subject</th>
                                    <th width="120">Priority</th>
                                    <th width="150">Assigned To</th>
                                    <th width="140">Created</th>
                                    <th width="140">SLA Due</th>
                                    <th width="100">Status</th>
                                    <th width="100" style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($breachedTickets as $ticket)
                                    <tr>
                                        <td><strong class="text-primary">#{{ $ticket->id }}</strong></td>
                                        <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 50) }}</td>
                                        <td>
                                            <span class="label label-{{ $ticket->ticket_priority->color ?? 'default' }}">
                                                <i class="fa fa-flag"></i> {{ $ticket->ticket_priority->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa fa-user-circle"></i> {{ $ticket->assignedTo->name ?? 'Unassigned' }}
                                        </td>
                                        <td>
                                            <i class="fa fa-calendar"></i> {{ $ticket->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td>
                                            @if($ticket->sla_due)
                                                <span class="text-danger">
                                                    <i class="fa fa-clock-o"></i>
                                                    {{ \Carbon\Carbon::parse($ticket->sla_due)->format('Y-m-d H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted">No SLA</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label label-danger">
                                                <i class="fa fa-times-circle"></i> Breached
                                            </span>
                                        </td>
                                        <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('tickets.show', $ticket->id) }}" 
                                                   class="btn btn-sm btn-primary"
                                                   target="_blank"
                                                   title="View Ticket">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center" style="padding: 40px;">
                        <i class="fa fa-check-circle fa-4x text-success"></i>
                        <h4>No Breached SLA Tickets</h4>
                        <p class="text-muted">Excellent! All tickets are within SLA compliance.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Critical Tickets (At Risk) -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-fire"></i> Critical Tickets (At Risk of SLA Breach)
                    <span class="badge bg-yellow">{{ $criticalTickets->count() }}</span>
                </h3>
            </div>
            <div class="box-body">
                @if($criticalTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th>SLA Due</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criticalTickets as $ticket)
                                    <tr>
                                        <td><strong>#{{ $ticket->id }}</strong></td>
                                        <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 50) }}</td>
                                        <td>
                                            <span class="label label-{{ $ticket->ticket_priority->color ?? 'default' }}">
                                                {{ $ticket->ticket_priority->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $ticket->assignedTo->name ?? 'Unassigned' }}
                                        </td>
                                        <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($ticket->sla_due)
                                                <span class="text-warning">
                                                    <i class="fa fa-clock-o"></i>
                                                    {{ \Carbon\Carbon::parse($ticket->sla_due)->format('Y-m-d H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted">No SLA</span>
                                            @endif
                                        </td>
                                        <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('tickets.show', $ticket->id) }}" 
                                                   class="btn btn-sm btn-primary"
                                                   target="_blank"
                                                   title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center" style="padding: 40px;">
                        <i class="fa fa-thumbs-up fa-3x text-success"></i>
                        <p>No critical tickets at risk! Keep up the good work!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active SLA Policies -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-clipboard"></i> Active SLA Policies
                </h3>
            </div>
            <div class="box-body">
                @if($activePolicies->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Response Time</th>
                                    <th>Resolution Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activePolicies as $policy)
                                    <tr>
                                        <td>
                                            <span class="label label-{{ $policy->priority->color ?? 'default' }}">
                                                {{ $policy->priority->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $policy->response_time }} min</td>
                                        <td>{{ $policy->resolution_time }} min</td>
                                        <td>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> Active
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center" style="padding: 40px;">
                        <i class="fa fa-info-circle fa-3x text-muted"></i>
                        <p class="text-muted">No active SLA policies found.</p>
                        <a href="{{ route('sla.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create Policy
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change (optional)
    $('#priority_id, #assigned_to').on('change', function() {
        // Optional: auto-submit when selection changes
        // $('#filterForm').submit();
    });
});
</script>
@endpush
