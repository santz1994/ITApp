@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Asset Maintenance History',
    'subtitle' => $asset->asset_tag ?? 'Asset Details',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Maintenance', 'url' => route('asset-maintenance.index')],
        ['label' => 'History']
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

    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-9">
            {{-- Asset Information --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-laptop"></i> Asset Information</h3>
                    @if($asset->is_lemon_asset ?? false)
                        <span class="label label-danger pull-right" style="margin-top: 5px;">
                            <i class="fa fa-exclamation-triangle"></i> Problematic Asset
                        </span>
                    @endif
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-barcode"></i> Asset Tag:</span>
                                <span class="detail-value"><strong>{{ $asset->asset_tag }}</strong></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-box"></i> Model:</span>
                                <span class="detail-value">{{ $asset->model->name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-hashtag"></i> Serial Number:</span>
                                <span class="detail-value">{{ $asset->serial_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-info-circle"></i> Status:</span>
                                <span class="detail-value">
                                    <span class="label label-{{ $asset->status->color ?? 'default' }}">
                                        {{ $asset->status->status ?? 'Unknown' }}
                                    </span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-map-marker-alt"></i> Location:</span>
                                <span class="detail-value">{{ $asset->location->location_name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fa fa-user"></i> Assigned To:</span>
                                <span class="detail-value">{{ $asset->user->name ?? 'Unassigned' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Maintenance History Timeline --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Maintenance History</h3>
                    <span class="badge badge-warning">{{ $history->count() ?? 0 }}</span>
                </div>
                <div class="box-body">
                    @if(isset($history) && $history->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="maintenanceHistoryTable">
                                <thead style="background-color: #f7f7f7;">
                                    <tr>
                                        <th><i class="fa fa-calendar"></i> Date</th>
                                        <th><i class="fa fa-ticket-alt"></i> Ticket</th>
                                        <th><i class="fa fa-align-left"></i> Description</th>
                                        <th><i class="fa fa-info-circle"></i> Status</th>
                                        <th><i class="fa fa-exclamation-circle"></i> Priority</th>
                                        <th><i class="fa fa-user"></i> Assigned To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $record)
                                    <tr>
                                        <td>{{ $record->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('tickets.show', $record->id) }}">
                                                <strong>{{ $record->ticket_code }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($record->description, 60) }}</td>
                                        <td>
                                            <span class="label label-{{ $record->ticket_status->color ?? 'default' }}">
                                                {{ $record->ticket_status->name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label label-{{ $record->ticket_priority->color ?? 'default' }}">
                                                {{ $record->ticket_priority->name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>{{ $record->assigned_to_user->name ?? 'Unassigned' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-inbox"></i>
                            <h4>No Maintenance Records</h4>
                            <p>This asset has no maintenance history yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Tickets Summary --}}
            @php
                $recentTickets = $asset->tickets()->where('created_at', '>=', now()->subMonths(3))->get();
                $totalCost = 0; // You would calculate from maintenance logs if available
            @endphp

            @if($recentTickets->count() > 0)
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-chart-bar"></i> Recent Activity (Last 3 Months)</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-ticket-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Tickets</span>
                                        <span class="info-box-number">{{ $recentTickets->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-yellow">
                                    <span class="info-box-icon"><i class="fa fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Open Tickets</span>
                                        <span class="info-box-number">
                                            {{ $recentTickets->where('ticket_status_id', '!=', 3)->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-green">
                                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Resolved</span>
                                        <span class="info-box-number">
                                            {{ $recentTickets->where('ticket_status_id', 3)->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-md-3">
            {{-- Quick Actions --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ route('asset-maintenance.index') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="{{ url('assets/' . $asset->id . '/edit') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-edit"></i> Edit Asset
                    </a>
                    <button type="button" class="btn btn-warning btn-block" onclick="createMaintenanceTicket()">
                        <i class="fa fa-wrench"></i> New Maintenance
                    </button>
                    <hr>
                    <button type="button" class="btn btn-info btn-block" onclick="window.print()">
                        <i class="fa fa-print"></i> Print History
                    </button>
                </div>
            </div>

            {{-- Maintenance Statistics --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-pie"></i> Statistics</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-history"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Records</span>
                            <span class="info-box-number">{{ $history->count() ?? 0 }}</span>
                        </div>
                    </div>

                    @php
                        $monthlyTickets = $asset->tickets()->where('created_at', '>=', now()->subMonth())->count();
                    @endphp

                    <div class="info-box bg-{{ $monthlyTickets >= 3 ? 'red' : ($monthlyTickets >= 2 ? 'yellow' : 'green') }}">
                        <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">This Month</span>
                            <span class="info-box-number">{{ $monthlyTickets }}</span>
                        </div>
                    </div>

                    @if($asset->is_lemon_asset ?? false)
                        <div class="alert alert-danger" style="margin-top: 10px;">
                            <i class="fa fa-exclamation-triangle"></i> 
                            <strong>Problematic Asset</strong>
                            <p style="font-size: 12px; margin-top: 5px;">
                                This asset has recurring issues. Consider replacement.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Asset Health --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-heartbeat"></i> Asset Health</h3>
                </div>
                <div class="box-body">
                    @php
                        $totalTickets = $asset->tickets()->count();
                        $healthScore = $totalTickets == 0 ? 100 : max(0, 100 - ($totalTickets * 10));
                        $healthColor = $healthScore >= 70 ? 'success' : ($healthScore >= 40 ? 'warning' : 'danger');
                    @endphp

                    <p style="font-size: 12px; margin-bottom: 10px;">
                        <strong>Health Score:</strong>
                    </p>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-{{ $healthColor }}" 
                             role="progressbar" 
                             style="width: {{ $healthScore }}%;">
                            {{ $healthScore }}%
                        </div>
                    </div>

                    <ul style="margin-top: 15px; font-size: 12px; line-height: 1.8;">
                        <li><strong>Total Issues:</strong> {{ $totalTickets }}</li>
                        <li><strong>Status:</strong> {{ $asset->status->status ?? 'Unknown' }}</li>
                        <li><strong>Age:</strong> {{ $asset->created_at->diffInMonths(now()) }} months</li>
                    </ul>
                </div>
            </div>

            {{-- Information --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 12px; line-height: 1.6;">
                        This page shows the complete maintenance history for this asset, including all service tickets, 
                        repairs, and preventive maintenance activities.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#maintenanceHistoryTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                search: "Search history:",
                lengthMenu: "Show _MENU_ records",
                info: "Showing _START_ to _END_ of _TOTAL_ records"
            }
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    function createMaintenanceTicket() {
        window.location.href = '/tickets/create?asset_id={{ $asset->id }}';
    }
</script>
@endpush
