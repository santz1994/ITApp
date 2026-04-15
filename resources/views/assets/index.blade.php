@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

@include('components.page-header', [
    'title' => 'Assets',
    'subtitle' => 'Manage all IT assets and equipment',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Assets']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('assets.create').'" class="btn btn-primary">
                <i class="fa fa-plus"></i> <span class="hidden-xs">New Asset</span>
            </a>
            <a href="'.route('assets.import-form').'" class="btn btn-info">
                <i class="fa fa-upload"></i> <span class="hidden-xs">Import</span>
            </a>
            <a href="'.route('assets.export').'" class="btn btn-success">
                <i class="fa fa-download"></i> <span class="hidden-xs">Export</span>
            </a>
        </div>
    '
])

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
  @endif

  {{-- Quick Stats Cards --}}
  <div class="row">
    <div class="col-md-2 col-sm-6 col-xs-12">
      <div class="small-box bg-purple clickable-badge" onclick="filterByStatus('all')">
        <div class="inner">
          <h3>{{$totalAssets}}</h3>
          <p>Total Assets</p>
        </div>
        <div class="icon">
          <i class="fa fa-tags"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('all')">
          View All <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
      <div class="small-box bg-aqua clickable-badge" onclick="filterByStatus('deployed')">
        <div class="inner">
          <h3>{{$deployed}}</h3>
          <p>Deployed</p>
        </div>
        <div class="icon">
          <i class="fa fa-check-circle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('deployed')">
          Filter <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
      <div class="small-box bg-green clickable-badge" onclick="filterByStatus('ready')">
        <div class="inner">
          <h3>{{$readyToDeploy}}</h3>
          <p>Ready to Deploy</p>
        </div>
        <div class="icon">
          <i class="fa fa-plus-circle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('ready')">
          Filter <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
      <div class="small-box bg-yellow clickable-badge" onclick="filterByStatus('repairs')">
        <div class="inner">
          <h3>{{$repairs}}</h3>
          <p>In Repairs</p>
        </div>
        <div class="icon">
          <i class="fa fa-wrench"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('repairs')">
          Filter <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
      <div class="small-box bg-red clickable-badge" onclick="filterByStatus('written-off')">
        <div class="inner">
          <h3>{{$writtenOff}}</h3>
          <p>Written Off</p>
        </div>
        <div class="icon">
          <i class="fa fa-times-circle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('written-off')">
          Filter <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Assets by Status</h3>
        </div>
        <div class="box-body">
          @if(!empty($assetsByStatus) && count($assetsByStatus) > 0)
            <table class="table table-condensed">
              <thead>
                <tr><th>Status</th><th class="text-right">Count</th></tr>
              </thead>
              <tbody>
                @foreach($assetsByStatus as $s)
                  <tr>
                    <td>{{ $s->status_name }}</td>
                    <td class="text-right">{{ $s->count }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <p>No data available</p>
          @endif
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title">New Assets (Last 6 months)</h3>
        </div>
        <div class="box-body">
          @if(!empty($monthlyNewAssets) && count($monthlyNewAssets) > 0)
            <div class="table-responsive">
              <table class="table table-condensed">
                <thead>
                  <tr><th>Month</th><th class="text-right">New Assets</th></tr>
                </thead>
                <tbody>
                  @foreach($monthlyNewAssets as $m)
                    <tr>
                      <td>{{ $m['month'] ?? $m->month ?? 'N/A' }}</td>
                      <td class="text-right">{{ $m['count'] ?? $m->count ?? 0 }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p>No recent assets</p>
          @endif
        </div>
      </div>
    </div>
  </div>
  {{-- Enhanced Filter Bar --}}
  <div class="row">
    <div class="col-md-12">
      <div class="box box-default collapsed-box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-filter"></i> Advanced Filters</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
              <i class="fa fa-plus"></i> Expand Filters
            </button>
          </div>
        </div>
        <div class="box-body filter-bar">
          <form id="filterForm" method="get" action="{{ route('assets.index') }}">
            <div class="row">
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="search"><i class="fa fa-search"></i> Search Assets</label>
                  <input type="text" id="search" name="search" class="form-control" 
                         placeholder="Tag, Serial, Model..." 
                         value="{{ request('search') }}">
                  <small class="text-muted">Search by asset tag, serial number, or model</small>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="status_filter"><i class="fa fa-info-circle"></i> Status</label>
                  <select id="status_filter" name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="deployed" {{ request('status') == 'deployed' ? 'selected' : '' }}>Deployed</option>
                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready to Deploy</option>
                    <option value="repairs" {{ request('status') == 'repairs' ? 'selected' : '' }}>In Repairs</option>
                    <option value="written-off" {{ request('status') == 'written-off' ? 'selected' : '' }}>Written Off</option>
                  </select>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="asset_type_filter"><i class="fa fa-laptop"></i> Asset Type</label>
                  <select id="asset_type_filter" name="asset_type" class="form-control">
                    <option value="">All Types</option>
                    @if(isset($assetTypes))
                      @foreach($assetTypes as $type)
                        <option value="{{ $type->id }}" {{ request('asset_type') == $type->id ? 'selected' : '' }}>
                          {{ $type->type_name }}
                        </option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="location_filter"><i class="fa fa-map-marker"></i> Location</label>
                  <select id="location_filter" name="location" class="form-control">
                    <option value="">All Locations</option>
                    @if(isset($locations))
                      @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                          {{ $location->location_name }}
                        </option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="age_filter"><i class="fa fa-calendar"></i> Asset Age</label>
                  <select id="age_filter" name="age" class="form-control">
                    <option value="">All Ages</option>
                    <option value="new" {{ request('age') == 'new' ? 'selected' : '' }}>New (0-12 months)</option>
                    <option value="moderate" {{ request('age') == 'moderate' ? 'selected' : '' }}>Moderate (1-3 years)</option>
                    <option value="aging" {{ request('age') == 'aging' ? 'selected' : '' }}>Aging (3-5 years)</option>
                    <option value="old" {{ request('age') == 'old' ? 'selected' : '' }}>Old (5+ years)</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('assets.index') }}" class="btn btn-default">
                  <i class="fa fa-refresh"></i> Reset Filters
                </a>
                <button type="button" id="exportFiltered" class="btn btn-success pull-right">
                  <i class="fa fa-file-excel-o"></i> Export Filtered Results
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12 col-xs-12 col-lg-12">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-table"></i> Assets List</h3>
          <div class="box-tools">
            <span class="label label-primary" id="assetCount">{{ method_exists($assets, 'total') ? $assets->total() : count($assets) }} Assets</span>
          </div>
        </div>
        <div class="box-body">
          <table id="table" class="table table-enhanced table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="sortable" data-column="asset_tag">Tag</th>
                <th class="sortable" data-column="asset_type">Asset Type</th>
                <th class="sortable" data-column="serial_number">S/N</th>
                <th class="sortable" data-column="age">Age</th>
                <th class="sortable" data-column="model">Model</th>
                <th class="sortable" data-column="location">Location</th>
                <th class="sortable" data-column="division">Division</th>
                <th class="sortable" data-column="assigned_to">User/PIC</th>
                <th class="sortable" data-column="status">Status</th>
                <th class="actions">Actions</th>
                <th class="sortable" data-column="supplier">Supplier</th>
                <th class="sortable" data-column="purchase_date">Purchase Date</th>
                <th class="sortable" data-column="warranty_months">Warranty Months</th>
                <th class="sortable" data-column="warranty_type">Warranty Type</th>
              </tr>
            </thead>
            <tbody>
              <?php $now = new \Carbon\Carbon(); ?>
              @foreach($assets as $asset)
                @if($asset->purchase_date != '0000-00-00')
                  <?php $purchasedDate = \Carbon\Carbon::parse($asset->purchase_date);
                  $age = $purchasedDate->diffInMonths($now); ?>
                @endif
                <tr
                  @if(isset($age))
                    @if($age > 59)
                      class="danger"
                    @elseif($age > 47 && $age < 60)
                      class="warning"
                    @endif
                  @endif
                >
                  <div>
                    <td>{{$asset->asset_tag}}</td>
                    <td>{{ $asset->model && $asset->model->asset_type ? $asset->model->asset_type->type_name : 'N/A' }}</td>
                    <td>{{ $asset->serial_number ?? '' }}</td>
                    <td>
                      @if (isset($age))
                        <?php $years = $age / 12;
                              $months = $age % 12;
                               ?>
                        {{floor($years)}}Y, {{$months}}M
                      @endif
                    </td>
                    <td>
                      <div id="model{{$asset->id}}" class="hover-pointer">
                        {{ $asset->model ? (($asset->model->manufacturer ? $asset->model->manufacturer->name : 'N/A') . ' - ' . $asset->model->asset_model) : 'N/A' }}
                      </div>
                    </td>
                    <td>
                      <div id="location{{$asset->id}}" class="hover-pointer">
                        {{ $asset->movement ? ($asset->movement->location ? $asset->movement->location->location_name : 'N/A') : 'N/A' }}
                      </div>
                    </td>
                    <td>
                      <div id="division{{$asset->id}}" class="hover-pointer">
                        {{ $asset->division ? $asset->division->name : 'N/A' }}
                      </div>
                    </td>
                    <td>
                      <div id="user{{$asset->id}}" class="hover-pointer">
                        {{ $asset->assignedTo ? $asset->assignedTo->name : 'N/A' }}
                      </div>
                    </td>
                    <td>
                      <div id="status{{$asset->id}}" class="hover-pointer">
                        @if($asset->status)
                          @if($asset->status->id == 1)
                            <span class="label label-success">
                          @elseif($asset->status->id == 2)
                            <span class="label label-info">
                          @elseif($asset->status->id == 3 || $asset->status->id == 4)
                            <span class="label label-warning">
                          @elseif($asset->status->id == 5 || $asset->status->id == 6)
                            <span class="label label-danger">
                          @else
                            <span class="label label-default">
                          @endif
                          {{$asset->status->name}}</span>
                        @else
                          <span class="label label-default">No Status</span>
                        @endif
                      </div>
                    </td>
                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                      <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>
                        <a href="/assets/{{ $asset->id }}/move" class="btn btn-sm btn-primary" title="Move"><i class="fa fa-send"></i></a>
                        <a href="/assets/{{ $asset->id }}/history" class="btn btn-sm btn-primary" title="History"><i class="fa fa-calendar"></i></a>
                        <a href="{{ route('tickets.create', ['asset_id' => $asset->id]) }}" class="btn btn-sm btn-warning" title="Tickets"><i class="fa fa-ticket"></i></a>
                        <a href="/assets/{{ $asset->id }}/edit" class="btn btn-sm btn-primary" title="Edit"><i class="fa fa-pencil"></i></a>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAsset({{ $asset->id }})" title="Delete">
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>
                      <form id="delete-asset-{{ $asset->id }}" method="POST" action="{{ url('assets/' . $asset->id) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                      </form>
                    </td>
                    <td>{{ $asset->supplier ? $asset->supplier->name : 'N/A' }}</td>
                    <td>{{$asset->purchase_date}}</td>
                    <td>{{$asset->warranty_months}}</td>
                    <td>{{ $asset->warranty_type ? $asset->warranty_type->name : 'N/A' }}</td>
                  </div>
                </tr>
                <?php $age = null; ?>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script>
  $(document).ready(function() {
  var table = $('#table').DataTable( {
    responsive: true,
    dom: 'l<"clear">Bfrtip',
    pageLength: 25,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
    buttons: [
      {
        extend: 'excel',
        text: '<i class="fa fa-file-excel-o"></i> Excel',
        className: 'btn btn-success btn-sm',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12]
        }
      },
      {
        extend: 'csv',
        text: '<i class="fa fa-file-text-o"></i> CSV',
        className: 'btn btn-info btn-sm',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12, 13]
        }
      },
      {
        extend: 'pdf',
        text: '<i class="fa fa-file-pdf-o"></i> PDF',
        className: 'btn btn-danger btn-sm',
        orientation: 'landscape',
        pageSize: 'A4',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
        }
      },
      {
        extend: 'copy',
        text: '<i class="fa fa-copy"></i> Copy',
        className: 'btn btn-default btn-sm',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12, 13]
        }
      }
    ],
    columns: [
      null,
      null,  // Asset Type
      null,
      null,
      null,
      null,
      null,
      null,  // User/PIC
      null,
      null,  // Actions
      null,  // Supplier
      null,  // Purchase Date
      null,  // Warranty Months
      null   // Warranty Type
    ], 
    columnDefs: [{
      orderable: false, targets: 9
    }],
    language: {
      lengthMenu: "Show _MENU_ assets per page",
      info: "Showing _START_ to _END_ of _TOTAL_ assets",
      infoEmpty: "No assets to show",
      infoFiltered: "(filtered from _MAX_ total assets)",
      search: "Quick Search:",
      paginate: {
        first: '<i class="fa fa-angle-double-left"></i>',
        previous: '<i class="fa fa-angle-left"></i>',
        next: '<i class="fa fa-angle-right"></i>',
        last: '<i class="fa fa-angle-double-right"></i>'
      }
    },
    drawCallback: function() {
      // Update asset count badge
      // Use this.api() instead of table variable (which isn't assigned yet during initialization)
      var api = this.api();
      var info = api.page.info();
      $('#assetCount').text(info.recordsDisplay + ' Assets');
    }
  } );
    // Export filtered results button
    $('#exportFiltered').on('click', function() {
      table.button('.buttons-excel').trigger();
    });

    // Get the model, location, division and status columns' div IDs for each row.
    // If it is clicked on, then the datatable will filter that.
    @foreach($assets as $asset)
      // Model
      var model = (function() {
        var x = '#model' + {{$asset->id}};
        return x;
      });
      $(model()).click(function () {
        @if($asset->model && $asset->model->manufacturer)
        table.search( "{{$asset->model->manufacturer->name}} - {{$asset->model->asset_model}}" ).draw();
        @elseif($asset->model)
        table.search( "{{$asset->model->asset_model}}" ).draw();
        @else
        table.search( "N/A" ).draw();
        @endif
      });

      // Location
      var location = (function() {
        var x = '#location' + {{$asset->id}};
        return x;
      });
      $(location()).click(function () {
        @if($asset->movement && $asset->movement->location)
        table.search( "{{$asset->movement->location->location_name}}" ).draw();
        @else
        table.search( "N/A" ).draw();
        @endif
      });

      // Division
      var division = (function() {
        var x = '#division' + {{$asset->id}};
        return x;
      });
      $(division()).click(function () {
        @if($asset->division)
        table.search( "{{$asset->division->name}}" ).draw();
        @else
        table.search( "N/A" ).draw();
        @endif
      });

      // Status
      var status = (function() {
        var x = '#status' + {{$asset->id}};
        return x;
      });
      $(status()).click(function () {
        @if($asset->movement && $asset->movement->status)
        table.search( "{{$asset->movement->status->name}}" ).draw();
        @else
        table.search( "N/A" ).draw();
        @endif
      });
    @endforeach
  } );

  // Filter by status when clicking stat cards
  window.filterByStatus = function(status) {
    var searchTerm = '';
    switch(status) {
      case 'deployed': searchTerm = 'Deployed'; break;
      case 'ready': searchTerm = 'Ready'; break;
      case 'repairs': searchTerm = 'Repairs'; break;
      case 'written-off': searchTerm = 'Written Off'; break;
      case 'all': searchTerm = ''; break;
    }
    table.search(searchTerm).draw();
  };

  // Enhanced box collapse button text toggle
  $('.box').on('expanded.boxwidget', function() {
    $(this).find('.btn-box-tool i').removeClass('fa-plus').addClass('fa-minus');
    $(this).find('.btn-box-tool').contents().last()[0].textContent = ' Collapse Filters';
  });
  $('.box').on('collapsed.boxwidget', function() {
    $(this).find('.btn-box-tool i').removeClass('fa-minus').addClass('fa-plus');
    $(this).find('.btn-box-tool').contents().last()[0].textContent = ' Expand Filters';
  });

  // Delete asset confirmation
  window.deleteAsset = function(id) {
    if(confirm('Are you sure you want to delete this asset?')) {
      document.getElementById('delete-asset-' + id).submit();
    }
  };
  </script>
  @if(Session::has('status'))
    <script>
      $(document).ready(function() {
        Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
      });
    </script>
  @endif

@include('components.loading-overlay')

@endsection


