@extends('layouts.app')

@section('title')
Enhanced Inventory Management
@endsection

@section('main-content')
<div class="row">
  <!-- Summary Statistics -->
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-aqua">
      <div class="inner">
        <h3>{{ $stats['total_assets'] }}</h3>
        <p>Total Assets</p>
      </div>
      <div class="icon">
        <i class="fa fa-desktop"></i>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-green">
      <div class="inner">
        <h3>{{ $stats['active_assets'] }}</h3>
        <p>Active Assets</p>
      </div>
      <div class="icon">
        <i class="fa fa-check-circle"></i>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-yellow">
      <div class="inner">
        <h3>{{ $stats['maintenance_assets'] }}</h3>
        <p>In Maintenance</p>
      </div>
      <div class="icon">
        <i class="fa fa-wrench"></i>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-red">
      <div class="inner">
        <h3>{{ $stats['pending_requests'] }}</h3>
        <p>Pending Requests</p>
      </div>
      <div class="icon">
        <i class="fa fa-clock-o"></i>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">
          <i class="fa fa-list"></i> Inventory Management
        </h3>
        <div class="box-tools pull-right">
          <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Add New Asset
          </a>
          <a href="{{ route('asset-requests.create') }}" class="btn btn-success btn-sm">
            <i class="fa fa-paper-plane"></i> Request Asset
          </a>
        </div>
      </div>

      <!-- Filters -->
      <div class="box-body">
        <form method="GET" action="{{ route('assets.index') }}">
          <div class="row">
            <div class="col-md-2">
              <div class="form-group">
                <label>Category</label>
                <select name="category" class="form-control">
                  <option value="">All Categories</option>
                  @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                      {{ $category->type_name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                  <option value="">All Statuses</option>
                  @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                      {{ $status->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Location</label>
                <select name="location" class="form-control">
                  <option value="">All Locations</option>
                  @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                      {{ $location->location_name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Division</label>
                <select name="division" class="form-control">
                  <option value="">All Divisions</option>
                  @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                      {{ $division->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" class="form-control" placeholder="Asset Tag, Serial..." value="{{ request('search') }}">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>&nbsp;</label>
                <div>
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-search"></i> Filter
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Assets List -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title">Assets Inventory</h3>
        <div class="box-tools pull-right">
          <span class="label label-info">{{ method_exists($assets, 'total') ? $assets->total() : count($assets) }} Total Assets</span>
        </div>
      </div>
      
      <div class="box-body">
        @if($assets->count() > 0)
        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="assetsTable">
            <thead>
              <tr>
                <th>Asset Tag</th>
                <th>Category</th>
                <th>Model</th>
                <th>Serial Number</th>
                <th>Status</th>
                <th>Location</th>
                <th>Division</th>
                <th>Assigned To</th>
                <th>Purchase Date</th>
                <th>Warranty</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($assets as $asset)
              <tr class="{{ $asset->status->name == 'In Repair' ? 'warning' : ($asset->status->name == 'Retired' ? 'danger' : '') }}">
                <td>
                  <strong>{{ $asset->asset_tag }}</strong>
                  @if($asset->qr_code)
                    <br><small class="text-muted"><i class="fa fa-qrcode"></i> {{ $asset->qr_code }}</small>
                  @endif
                </td>
                <td>
                  <span class="badge bg-blue">{{ $asset->assetModel->assetType->type_name ?? 'N/A' }}</span>
                </td>
                <td>{{ $asset->assetModel->name ?? 'N/A' }}</td>
                <td>{{ $asset->serial_number }}</td>
                <td>
                  @php
                    $statusColor = 'default';
                    switch($asset->status->name ?? 'Unknown') {
                      case 'Active': $statusColor = 'success'; break;
                      case 'In Use': $statusColor = 'primary'; break;
                      case 'Available': $statusColor = 'info'; break;
                      case 'In Repair': $statusColor = 'warning'; break;
                      case 'Retired': $statusColor = 'danger'; break;
                    }
                  @endphp
                  <span class="label label-{{ $statusColor }}">{{ $asset->status->name ?? 'Unknown' }}</span>
                </td>
                <td>{{ $asset->movement->location->location_name ?? 'N/A' }}</td>
                <td>{{ $asset->division->name ?? 'N/A' }}</td>
                <td>{{ $asset->assignedUser->name ?? 'Unassigned' }}</td>
                <td>{{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : 'N/A' }}</td>
                <td>
                  @if($asset->purchase_date && $asset->warranty_months)
                    @php
                      $warrantyStatus = $asset->getWarrantyStatus();
                    @endphp
                    <span class="label label-{{ $warrantyStatus == 'Active' ? 'success' : ($warrantyStatus == 'Expiring soon' ? 'warning' : 'danger') }}">
                      {{ $warrantyStatus }}
                    </span>
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-sm btn-info" title="View Details">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-sm btn-primary" title="Edit">
                      <i class="fa fa-edit"></i>
                    </a>
                    <a href="{{ route('assets.ticket-history', $asset->id) }}" class="btn btn-sm btn-warning" title="Ticket History">
                      <i class="fa fa-history"></i>
                    </a>
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-cog"></i> <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a href="#" onclick="changeAssetStatus({{ $asset->id }}, 'active')"><i class="fa fa-check"></i> Mark Active</a></li>
                        <li><a href="#" onclick="changeAssetStatus({{ $asset->id }}, 'maintenance')"><i class="fa fa-wrench"></i> Send to Maintenance</a></li>
                        <li><a href="#" onclick="changeAssetStatus({{ $asset->id }}, 'retired')"><i class="fa fa-times"></i> Retire Asset</a></li>
                        <li class="divider"></li>
                        <li><a href="{{ route('tickets.create-with-asset', ['asset_id' => $asset->id]) }}"><i class="fa fa-ticket"></i> Create Ticket</a></li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="text-center">
          {{ $assets->appends(request()->query())->links() }}
        </div>

        @else
        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i> No assets found with the selected filters.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Asset Categories Overview -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title">Asset Categories Overview</h3>
      </div>
      
      <div class="box-body">
        <div class="row">
          @foreach($categoryStats as $category)
          <div class="col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-aqua">
                <i class="fa fa-{{ $category['icon'] }}"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">{{ $category['name'] }}</span>
                <span class="info-box-number">{{ $category['count'] }}</span>
                <div class="progress">
                  <div class="progress-bar" style="width: {{ $category['percentage'] }}%"></div>
                </div>
                <span class="progress-description">{{ $category['percentage'] }}% of total assets</span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Initialize DataTables
    if ($.fn.DataTable) {
        $('#assetsTable').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false, // We have custom search
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 25
        });
    }
});

function changeAssetStatus(assetId, status) {
    swal({
        title: 'Change Asset Status?',
        text: 'Are you sure you want to change this asset status to ' + status + '?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Change Status',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.value) {
            // Create a form and submit
            var form = $('<form method="POST" action="/assets/' + assetId + '/change-status">')
                .append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">')
                .append('<input type="hidden" name="status" value="' + status + '">');
            
            $('body').append(form);
            form.submit();
        }
    });
}
</script>
@endsection
