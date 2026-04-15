@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Asset Maintenance Management',
    'subtitle' => 'Track, Schedule & Monitor Asset Maintenance',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Maintenance']
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

    {{-- Statistics Overview --}}
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $stats['assets_needing_maintenance'] ?? 0 }}</h3>
                    <p>Need Maintenance</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="small-box-footer">
                    Assets requiring attention
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{ $stats['scheduled_maintenance'] ?? 0 }}</h3>
                    <p>Scheduled</p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar"></i>
                </div>
                <div class="small-box-footer">
                    Upcoming maintenance tasks
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $stats['high_priority'] ?? 0 }}</h3>
                    <p>High Priority</p>
                </div>
                <div class="icon">
                    <i class="fa fa-fire"></i>
                </div>
                <div class="small-box-footer">
                    Urgent maintenance required
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>{{ $stats['overdue_maintenance'] ?? 0 }}</h3>
                    <p>Overdue</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock"></i>
                </div>
                <div class="small-box-footer">
                    Past due maintenance
                </div>
            </div>
        </div>
    </div>

    {{-- Assets Requiring Maintenance --}}
    <div class="row">
        <div class="col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-exclamation-triangle"></i> Assets Requiring Maintenance
                        <span class="badge badge-warning">{{ $assetsRequiringMaintenance->count() ?? 0 }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('asset-maintenance.analytics') ?? '#' }}" class="btn btn-info btn-sm">
                            <i class="fa fa-chart-bar"></i> Analytics
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    @if(isset($assetsRequiringMaintenance) && $assetsRequiringMaintenance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead style="background-color: #f7f7f7;">
                                    <tr>
                                        <th><i class="fa fa-barcode"></i> Asset Tag</th>
                                        <th><i class="fa fa-box"></i> Model</th>
                                        <th><i class="fa fa-map-marker-alt"></i> Location</th>
                                        <th><i class="fa fa-info-circle"></i> Status</th>
                                        <th><i class="fa fa-ticket-alt"></i> Recent Issues</th>
                                        <th><i class="fa fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assetsRequiringMaintenance as $asset)
                                    <tr>
                                        <td>
                                            <strong>{{ $asset->asset_tag }}</strong>
                                            @if($asset->is_lemon_asset ?? false)
                                                <span class="label label-danger">
                                                    <i class="fa fa-exclamation-triangle"></i> Problematic
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $asset->model->name ?? 'Unknown Model' }}</td>
                                        <td>{{ $asset->location->location_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="label label-{{ $asset->status->color ?? 'default' }}">
                                                {{ $asset->status->status ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $recentTickets = $asset->tickets()->where('tickets.created_at', '>=', now()->subMonths(1))->count();
                                            @endphp
                                            <span class="badge bg-{{ $recentTickets >= 3 ? 'red' : ($recentTickets >= 2 ? 'yellow' : 'green') }}">
                                                <i class="fa fa-ticket-alt"></i> {{ $recentTickets }} ticket(s)
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-xs">
                                                <a href="{{ route('asset-maintenance.show', $asset->id) }}" 
                                                   class="btn btn-info" 
                                                   title="View Maintenance History">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-warning" 
                                                        onclick="createMaintenanceTicket({{ $asset->id }})" 
                                                        title="Create Maintenance Ticket">
                                                    <i class="fa fa-wrench"></i>
                                                </button>
                                                @if($asset->is_lemon_asset ?? false)
                                                    <button type="button" 
                                                            class="btn btn-danger" 
                                                            onclick="generateReplacementRequest({{ $asset->id }})" 
                                                            title="Request Replacement">
                                                        <i class="fa fa-exchange-alt"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-check-circle"></i>
                            <h4>All Assets in Good Condition</h4>
                            <p>No assets currently require maintenance attention.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Maintenance Schedule --}}
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-calendar"></i> Maintenance Schedule
                    </h3>
                </div>
                <div class="box-body" style="max-height: 600px; overflow-y: auto;">
                    @if(isset($maintenanceSchedule) && $maintenanceSchedule->count() > 0)
                        @foreach($maintenanceSchedule->take(10) as $schedule)
                        <div class="media maintenance-item" style="margin-bottom: 15px;">
                            <div class="media-left">
                                <i class="fa fa-{{ $schedule['priority'] === 'high' ? 'fire text-red' : 'wrench text-warning' }} fa-2x"></i>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading" style="margin-top: 0;">
                                    <strong>{{ $schedule['asset']->asset_tag }}</strong>
                                    <span class="label label-{{ $schedule['priority'] === 'high' ? 'danger' : 'warning' }}">
                                        {{ ucfirst($schedule['priority']) }}
                                    </span>
                                </h5>
                                <p class="text-muted" style="margin: 5px 0;">{{ $schedule['asset']->model->name ?? 'Unknown Model' }}</p>
                                <small class="text-info">
                                    <i class="fa fa-calendar"></i> Due: {{ $schedule['recommended_date']->format('M d, Y') }}
                                </small>
                                <br>
                                <small class="text-muted">{{ $schedule['reason'] }}</small>
                            </div>
                        </div>
                        <hr style="margin: 10px 0;">
                        @endforeach
                        
                        @if($maintenanceSchedule->count() > 10)
                            <div class="text-center" style="margin-top: 10px;">
                                <small class="text-muted">
                                    <i class="fa fa-ellipsis-h"></i> And {{ $maintenanceSchedule->count() - 10 }} more items...
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="text-center" style="padding: 20px;">
                            <i class="fa fa-calendar-check" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                            <p class="text-muted">No scheduled maintenance at this time.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ route('asset-maintenance.analytics') ?? '#' }}" class="btn btn-info btn-block">
                        <i class="fa fa-chart-line"></i> View Analytics
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-default btn-block">
                        <i class="fa fa-laptop"></i> View All Assets
                    </a>
                    <button type="button" class="btn btn-warning btn-block" onclick="window.print()">
                        <i class="fa fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            {{-- Maintenance Tips --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> Maintenance Tips</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 12px; margin-bottom: 10px;"><strong>Best Practices:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px; line-height: 1.8;">
                        <li>Schedule regular preventive maintenance</li>
                        <li>Document all maintenance activities</li>
                        <li>Track problematic assets ("Lemon Assets")</li>
                        <li>Monitor recurring issues for patterns</li>
                        <li>Keep maintenance costs updated</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Maintenance Ticket Modal -->
<div class="modal fade" id="maintenanceTicketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Maintenance Ticket</h4>
            </div>
            <form id="maintenanceTicketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Asset</label>
                        <input type="text" id="assetDisplay" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority_id" class="form-control" required>
                            <option value="">Select Priority</option>
                            @foreach($priorities ?? [] as $priority)
                                <option value="{{ $priority->id }}">{{ $priority->priority }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type_id" class="form-control" required>
                            <option value="">Select Type</option>
                            @foreach($ticketTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" required
                                  placeholder="Describe the maintenance issue or requirement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Replacement Request Modal -->
<div class="modal fade" id="replacementRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">  
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Generate Replacement Request</h4>
            </div>
            <form id="replacementRequestForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Asset</label>
                        <input type="text" id="replacementAssetDisplay" class="form-control" readonly>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h5><i class="fa fa-exclamation-triangle"></i> Replacement Justification</h5>
                        <p>This asset has been identified as problematic based on maintenance history. 
                           Please provide justification for replacement request.</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason for Replacement</label>
                        <textarea name="reason" class="form-control" rows="4" required
                                  placeholder="Provide detailed justification for asset replacement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Generate Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer')
<script>
function createMaintenanceTicket(assetId) {
    // Get asset details via AJAX (you would implement this endpoint)
    $.get('/api/assets/' + assetId, function(asset) {
        $('#assetDisplay').val(asset.asset_tag + ' - ' + asset.model.name);
        $('#maintenanceTicketForm').attr('action', '/asset-maintenance/' + assetId + '/create-ticket');
        $('input[name="subject"]').val('Maintenance required for ' + asset.asset_tag);
        $('#maintenanceTicketModal').modal('show');
    });
}

function generateReplacementRequest(assetId) {
    // Get asset details via AJAX
    $.get('/api/assets/' + assetId, function(asset) {
        $('#replacementAssetDisplay').val(asset.asset_tag + ' - ' + asset.model.name);
        $('#replacementRequestForm').attr('action', '/asset-maintenance/' + assetId + '/replacement-request');
        $('#replacementRequestModal').modal('show');
    });
}

$(document).ready(function() {
    // Auto-refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
});
</script>
@endsection

