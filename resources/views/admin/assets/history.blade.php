@extends('layouts.app')

@section('title')
Asset History - {{ $asset->asset_tag }}
@endsection

@section('main-content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">
          <i class="fa fa-history"></i> Asset History: {{ $asset->asset_tag }}
        </h3>
        <div class="box-tools pull-right">
          <a href="{{ route('assets.index') }}" class="btn btn-sm btn-default">
            <i class="fa fa-arrow-left"></i> Back to Assets
          </a>
        </div>
      </div>
      
      <div class="box-body">
        <!-- Asset Summary -->
        <div class="row">
          <div class="col-md-4">
            <div class="info-box">
              <span class="info-box-icon bg-blue"><i class="fa fa-desktop"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Asset Tag</span>
                <span class="info-box-number">{{ $asset->asset_tag }}</span>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="info-box">
              <span class="info-box-icon bg-green"><i class="fa fa-ticket"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Tickets</span>
                <span class="info-box-number">{{ $ticketHistory->count() }}</span>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="info-box">
              <span class="info-box-icon bg-blue">
                <i class="fa fa-info-circle"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Asset Status</span>
                <span class="info-box-number">Active</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Issues -->
        @if($recentIssues->count() > 0)
        <div class="box box-warning">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Recent Issues (Last 30 Days)</h3>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Ticket Code</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentIssues as $ticket)
                  <tr>
                    <td>{{ $ticket->ticket_code }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td>
                      <span class="label label-{{ $ticket->ticket_priority->id == 1 ? 'danger' : ($ticket->ticket_priority->id == 2 ? 'warning' : 'info') }}">
                        {{ $ticket->ticket_priority->name }}
                      </span>
                    </td>
                    <td>
                      <span class="label label-primary">{{ $ticket->ticket_status->name }}</span>
                    </td>
                    <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                    <td>
                      <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-eye"></i> View
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        @endif

        <!-- Full Ticket History -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-history"></i> Complete Ticket History</h3>
          </div>
          <div class="box-body">
            @if($ticketHistory->count() > 0)
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Ticket Code</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Resolution Time</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($ticketHistory as $ticket)
                  <tr>
                    <td><strong>{{ $ticket->ticket_code }}</strong></td>
                    <td>{{ $ticket->ticket_type->name ?? 'N/A' }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td>
                      <span class="label label-{{ $ticket->ticket_priority->id == 1 ? 'danger' : ($ticket->ticket_priority->id == 2 ? 'warning' : 'info') }}">
                        {{ $ticket->ticket_priority->name }}
                      </span>
                    </td>
                    <td>
                      <span class="label label-primary">{{ $ticket->ticket_status->name }}</span>
                    </td>
                    <td>{{ $ticket->user->name ?? 'Unassigned' }}</td>
                    <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                    <td>
                      @if($ticket->resolved_at)
                        {{ $ticket->created_at->diffForHumans($ticket->resolved_at, true) }}
                      @else
                        <span class="text-muted">Not resolved</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-eye"></i> View
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="alert alert-info">
              <i class="fa fa-info-circle"></i> No ticket history found for this asset.
            </div>
            @endif
          </div>
        </div>

        <!-- Maintenance Statistics -->
        <div class="row">
          <div class="col-md-6">
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-wrench"></i> Maintenance Statistics</h3>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <tr>
                      <td><strong>Total Maintenance Tickets:</strong></td>
                      <td>{{ $asset->getMaintenanceTicketsCount() }}</td>
                    </tr>
                    <tr>
                      <td><strong>Average Resolution Time:</strong></td>
                      <td>
                        @if($asset->getAverageResolutionTime())
                          {{ round($asset->getAverageResolutionTime() / 60, 1) }} hours
                        @else
                          N/A
                        @endif
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Asset Age:</strong></td>
                      <td>{{ $asset->purchase_date ? $asset->purchase_date->diffForHumans() : 'Unknown' }}</td>
                    </tr>
                    <tr>
                      <td><strong>Warranty Status:</strong></td>
                      <td>
                        <span class="label label-{{ $asset->warranty_status == 'Active' ? 'success' : ($asset->warranty_status == 'Expiring soon' ? 'warning' : 'danger') }}">
                          {{ $asset->warranty_status }}
                        </span>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Asset Details</h3>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <tr>
                      <td><strong>Serial Number:</strong></td>
                      <td>{{ $asset->serial_number }}</td>
                    </tr>
                    <tr>
                      <td><strong>Model:</strong></td>
                      <td>{{ $asset->assetModel->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td><strong>Division:</strong></td>
                      <td>{{ $asset->division->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td><strong>Assigned To:</strong></td>
                      <td>{{ $asset->assignedUser->name ?? 'Unassigned' }}</td>
                    </tr>
                    <tr>
                      <td><strong>Status:</strong></td>
                      <td>{{ $asset->status->name ?? 'N/A' }}</td>
                    </tr>
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

@section('javascript')
<script>
$(document).ready(function() {
    // Initialize DataTables if needed
    if ($.fn.DataTable) {
        $('.table').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 25
        });
    }
});
</script>
@endsection
