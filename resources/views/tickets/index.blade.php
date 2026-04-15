@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

@include('components.page-header', [
    'title' => 'Tickets',
    'subtitle' => 'Manage and track all support tickets',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Tickets']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('tickets.create').'" class="btn btn-primary">
                <i class="fa fa-plus"></i> <span class="hidden-xs">New Ticket</span>
            </a>
            <a href="'.route('tickets.export').'" class="btn btn-success">
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
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-aqua" onclick="filterByTab('all')">
        <div class="inner">
          <h3>{{ $tickets->total() ?? count($tickets) }}</h3>
          <p>Total Tickets</p>
        </div>
        <div class="icon">
          <i class="fa fa-ticket"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByTab('all')">
          View All <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-yellow" onclick="filterByStatus('open')">
        <div class="inner">
          <h3>{{ $openTickets ?? 0 }}</h3>
          <p>Open Tickets</p>
        </div>
        <div class="icon">
          <i class="fa fa-folder-open"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('open')">
          Filter Open <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-green" onclick="filterByStatus('resolved')">
        <div class="inner">
          <h3>{{ $resolvedTickets ?? 0 }}</h3>
          <p>Resolved Tickets</p>
        </div>
        <div class="icon">
          <i class="fa fa-check-circle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('resolved')">
          Filter Resolved <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-red" onclick="filterByStatus('overdue')">
        <div class="inner">
          <h3>{{ $overdueTickets ?? 0 }}</h3>
          <p>Overdue SLA</p>
        </div>
        <div class="icon">
          <i class="fa fa-exclamation-triangle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('overdue')">
          Filter Overdue <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- Quick Filter Tabs --}}
  <div class="row">
    <div class="col-md-12">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li class="{{ !request('tab') || request('tab') == 'all' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'all'] + request()->except('tab')) }}">
              <i class="fa fa-list"></i> All Tickets
            </a>
          </li>
          <li class="{{ request('tab') == 'my' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'my'] + request()->except('tab')) }}">
              <i class="fa fa-user"></i> My Tickets
            </a>
          </li>
          <li class="{{ request('tab') == 'unassigned' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'unassigned'] + request()->except('tab')) }}">
              <i class="fa fa-inbox"></i> Unassigned
            </a>
          </li>
          <li class="{{ request('tab') == 'sla-risk' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'sla-risk'] + request()->except('tab')) }}">
              <i class="fa fa-clock-o"></i> SLA At Risk
            </a>
          </li>
        </ul>
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
          <form id="filterForm" method="GET" action="{{ route('tickets.index') }}">
            {{-- Preserve tab parameter --}}
            @if(request('tab'))
              <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            
            <div class="row">
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="search"><i class="fa fa-search"></i> Search Tickets</label>
                  <input type="text" id="search" name="search" class="form-control" 
                         placeholder="Ticket #, Subject, Description..." 
                         value="{{ request('search') }}">
                  <small class="text-muted">Search by ticket number, subject, or description</small>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="status_filter"><i class="fa fa-info-circle"></i> Status</label>
                  <select id="status_filter" name="status" class="form-control">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                      <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                        {{ $status->status }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="priority_filter"><i class="fa fa-exclamation-circle"></i> Priority</label>
                  <select id="priority_filter" name="priority" class="form-control">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $priority)
                      <option value="{{ $priority->id }}" {{ request('priority') == $priority->id ? 'selected' : '' }}>
                        {{ $priority->priority }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="type_filter"><i class="fa fa-tag"></i> Ticket Type</label>
                  <select id="type_filter" name="type" class="form-control">
                    <option value="">All Types</option>
                    @if(isset($types))
                      @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                          {{ $type->type }}
                        </option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
              @can('assign', App\Ticket::class)
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="assigned_filter"><i class="fa fa-user"></i> Assigned To</label>
                  <select id="assigned_filter" name="assigned_to" class="form-control">
                    <option value="">All Admins</option>
                    <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                    @foreach($admins as $admin)
                      <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                        {{ $admin->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              @endcan
            </div>
            <div class="row">
              <div class="col-md-3 col-sm-6">
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
                  <label for="date_from"><i class="fa fa-calendar"></i> Date From</label>
                  <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="date_to"><i class="fa fa-calendar"></i> Date To</label>
                  <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="sla_filter"><i class="fa fa-clock-o"></i> SLA Status</label>
                  <select id="sla_filter" name="sla" class="form-control">
                    <option value="">All SLA Status</option>
                    <option value="overdue" {{ request('sla') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="at-risk" {{ request('sla') == 'at-risk' ? 'selected' : '' }}>At Risk (&lt; 4 hours)</option>
                    <option value="on-time" {{ request('sla') == 'on-time' ? 'selected' : '' }}>On Time</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-default">
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
    <div class="col-md-12">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-list"></i> Tickets List</h3>
          <div class="box-tools">
            <span class="label label-primary" id="ticketCount">{{ $tickets->total() ?? count($tickets) }} Tickets</span>
          </div>
        </div>
        <div class="box-body">

          <!-- Bulk Operations Toolbar -->
          <div id="bulk-actions-toolbar" style="display: none; margin-bottom: 20px; padding: 15px; border-radius: 5px;">
            <div class="row">
              <div class="col-md-12">
                <strong><i class="fa fa-check-square-o"></i> <span id="selected-count">0</span> ticket(s) selected</strong>
                <div class="btn-group" style="margin-left: 20px;">
                  <button type="button" class="btn btn-sm btn-primary" onclick="showBulkAssignModal()">
                    <i class="fa fa-user"></i> Assign
                  </button>
                  <button type="button" class="btn btn-sm btn-info" onclick="showBulkStatusModal()">
                    <i class="fa fa-flag"></i> Change Status
                  </button>
                  <button type="button" class="btn btn-sm btn-warning" onclick="showBulkPriorityModal()">
                    <i class="fa fa-exclamation-circle"></i> Change Priority
                  </button>
                  <button type="button" class="btn btn-sm btn-success" onclick="showBulkCategoryModal()">
                    <i class="fa fa-tags"></i> Change Category
                  </button>
                  @can('delete', App\Ticket::class)
                  <button type="button" class="btn btn-sm btn-danger" onclick="confirmBulkDelete()">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                  @endcan
                </div>
                <button type="button" class="btn btn-sm btn-default pull-right" onclick="clearSelection()">
                  <i class="fa fa-times"></i> Clear Selection
                </button>
              </div>
            </div>
          </div>
          
          <table id="table" class="table table-enhanced table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th width="30">
                  <input type="checkbox" id="select-all-tickets" onclick="toggleSelectAll(this)">
                </th>
                <th class="sortable" data-column="ticket_number">Ticket #</th>
                <th class="sortable" data-column="subject">Subject</th>
                <th class="sortable" data-column="priority">Priority</th>
                <th class="sortable" data-column="status">Status</th>
                <th class="sortable" data-column="sla">SLA</th>
                <th class="sortable" data-column="creator">Creator</th>
                <th class="sortable" data-column="location">Location</th>
                @can('assign', App\Ticket::class)
                  <th class="sortable" data-column="assigned_to">Assigned To</th>
                @endcan
                <th class="sortable" data-column="created_at">Created</th>
                <th class="actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tickets as $ticket)
                <?php
                  // Calculate SLA status
                  $slaStatus = 'on-time';
                  $slaText = 'On Time';
                  $slaClass = 'sla-on-time';
                  
                  if (isset($ticket->sla_due_date)) {
                    $now = now();
                    $dueDate = \Carbon\Carbon::parse($ticket->sla_due_date);
                    
                    if ($dueDate->isPast()) {
                      $slaStatus = 'overdue';
                      $slaText = 'Overdue';
                      $slaClass = 'sla-overdue';
                    } elseif ($dueDate->diffInHours($now) < 4) {
                      $slaStatus = 'at-risk';
                      $slaText = 'At Risk';
                      $slaClass = 'sla-at-risk';
                    }
                  }
                ?>
                <tr>
                  <div>
                    <td>
                      <input type="checkbox" class="ticket-checkbox" value="{{$ticket->id}}" onchange="updateBulkToolbar()">
                    </td>
                    <td>
                      <strong><div class="hover-pointer" id="ticketnum{{$ticket->id}}">{{$ticket->ticket_code}}</div></strong>
                    </td>
                    <td>
                      <div class="hover-pointer" id="subject{{$ticket->id}}">
                        {{$ticket->subject}}
                      </div>
                      @if($ticket->assets && $ticket->assets->count())
                        <small class="text-muted">
                          <i class="fa fa-laptop"></i>
                          @foreach($ticket->assets as $a)
                            @if(!$loop->first), @endif{{ $a->asset_tag }}
                          @endforeach
                        </small>
                      @endif
                    </td>
                    <td>
                      <div class="hover-pointer" id="priority{{$ticket->id}}">
                        @if($ticket->ticket_priority->priority == 'Low')
                          <span class="priority-low"><i class="fa fa-arrow-down"></i> Low</span>
                        @elseif($ticket->ticket_priority->priority == 'Medium')
                          <span class="priority-medium"><i class="fa fa-minus"></i> Medium</span>
                        @elseif($ticket->ticket_priority->priority == 'High')
                          <span class="priority-high"><i class="fa fa-arrow-up"></i> High</span>
                        @endif
                      </div>
                    </td>
                    <td>
                      <div class="hover-pointer" id="status{{$ticket->id}}">
                        @php
                          $statusName = strtolower($ticket->ticket_status->status ?? '');
                          $labelClass = 'label-default';
                          if(str_contains($statusName, 'pending')) {
                            $labelClass = 'label-danger'; // red
                          } elseif(str_contains($statusName, 'open')) {
                            $labelClass = 'label-warning'; // yellow
                          } elseif(str_contains($statusName, 'resolved') || str_contains($statusName, 'closed')) {
                            $labelClass = 'label-success'; // green
                          } elseif(str_contains($statusName, 'progress')) {
                            $labelClass = 'label-primary'; // blue
                          }
                        @endphp
                        <span class="label {{ $labelClass }}">
                          {{$ticket->ticket_status->status}}
                        </span>
                      </div>
                    </td>
                    <td>
                      @if(isset($ticket->sla_due_date))
                        <span class="{{ $slaClass }}">
                          <i class="fa fa-clock-o"></i> {{ $slaText }}
                        </span>
                      @else
                        <span class="text-muted">No SLA</span>
                      @endif
                    </td>
                    <td><div class="hover-pointer" id="agent{{$ticket->id}}">{{$ticket->user->name}}</div></td>
                    <td><div class="hover-pointer" id="location{{$ticket->id}}">{{$ticket->location->location_name}}</div></td>
                    @can('assign', App\Ticket::class)
                      <td>
                        <div class="hover-pointer" id="assigned{{$ticket->id}}">
                          @if($ticket->assignedTo)
                            <i class="fa fa-user"></i> {{ $ticket->assignedTo->name }}
                          @else
                            <span class="text-muted"><i class="fa fa-inbox"></i> Unassigned</span>
                          @endif
                        </div>
                      </td>
                    @endcan
                    <td>
                      <small>{{ $ticket->created_at->format('M d, Y') }}</small><br>
                      <small class="text-muted">{{ $ticket->created_at->format('h:i A') }}</small>
                    </td>
                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                      <div class="btn-group btn-group-sm" role="group">
                        <a href="/tickets/{{ $ticket->id }}" class="btn btn-sm btn-primary" title="View">
                          <i class="fa fa-eye"></i>
                        </a>
                      </div>
                    </td>
                  </div>
                </tr>
              @endforeach
            </tbody>
          </table>
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
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
              }
            },
            {
              extend: 'csv',
              text: '<i class="fa fa-file-text-o"></i> CSV',
              className: 'btn btn-info btn-sm',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
              }
            },
            {
              extend: 'pdf',
              text: '<i class="fa fa-file-pdf-o"></i> PDF',
              className: 'btn btn-danger btn-sm',
              orientation: 'landscape',
              pageSize: 'A4',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7]
              }
            },
            {
              extend: 'copy',
              text: '<i class="fa fa-copy"></i> Copy',
              className: 'btn btn-default btn-sm',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
              }
            }
          ],
          columnDefs: [ 
            { orderable: false, targets: [0, -1] }
          ],
          order: [[ 9, "desc" ]],
          language: {
            lengthMenu: "Show _MENU_ tickets per page",
            info: "Showing _START_ to _END_ of _TOTAL_ tickets",
            infoEmpty: "No tickets to show",
            infoFiltered: "(filtered from _MAX_ total tickets)",
            search: "Quick Search:",
            paginate: {
              first: '<i class="fa fa-angle-double-left"></i>',
              previous: '<i class="fa fa-angle-left"></i>',
              next: '<i class="fa fa-angle-right"></i>',
              last: '<i class="fa fa-angle-double-right"></i>'
            }
          },
          drawCallback: function() {
            var info = this.api().page.info();
            $('#ticketCount').text(info.recordsDisplay + ' Tickets');
          }
        } );

        // Export filtered results
        $('#exportFiltered').on('click', function() {
          table.button('.buttons-excel').trigger();
        });

        // Enhanced box collapse button text toggle
        $('.box').on('expanded.boxwidget', function() {
          $(this).find('.btn-box-tool i').removeClass('fa-plus').addClass('fa-minus');
          $(this).find('.btn-box-tool').contents().last()[0].textContent = ' Collapse Filters';
        });
        $('.box').on('collapsed.boxwidget', function() {
          $(this).find('.btn-box-tool i').removeClass('fa-minus').addClass('fa-plus');
          $(this).find('.btn-box-tool').contents().last()[0].textContent = ' Expand Filters';
        });
        // Get the agent, locatoin, status and priority columns' div IDs for each row.
        // If it is clicked on, then the datatable will filter that.
        @foreach($tickets as $ticket)
          // Agent
          var agent = (function() {
            var x = '#agent' + {{$ticket->id}};
            return x;
          });
          $(agent()).click(function () {
            table.search( "{{$ticket->user->name}}" ).draw();
          });

          // Location
          var location = (function() {
            var x = '#location' + {{$ticket->id}};
            return x;
          });
          $(location()).click(function () {
            table.search( "{{$ticket->location->location_name}}" ).draw();
          });

          // Asset
          var asset = (function() {
            var x = '#asset' + {{$ticket->id}};
            return x;
          });
          $(asset()).click(function () {
            @if($ticket->asset)
              table.search( "{{ $ticket->asset->asset_tag }}" ).draw();
            @endif
          });

          // Status
          var status = (function() {
            var x = '#status' + {{$ticket->id}};
            return x;
          });
          $(status()).click(function () {
            table.search( "{{$ticket->ticket_status->status}}" ).draw();
          });

          // Priority
          var priority = (function() {
            var x = '#priority' + {{$ticket->id}};
            return x;
          });
          $(priority()).click(function () {
            table.search( "{{$ticket->ticket_priority->priority}}" ).draw();
          });
            @can('assign', App\Ticket::class)
            // Assigned To
            var assigned = (function() {
              var x = '#assigned' + {{$ticket->id}};
              return x;
            });
            $(assigned()).click(function () {
              @if($ticket->assignedTo)
                table.search( "{{ $ticket->assignedTo->name }}" ).draw();
              @endif
            });
            @endcan
        @endforeach
      } );

      // Filter by status from stat cards
      window.filterByStatus = function(status) {
        var searchTerm = '';
        switch(status) {
          case 'open': searchTerm = 'Open'; break;
          case 'resolved': searchTerm = 'Resolved'; break;
          case 'overdue': searchTerm = 'Overdue'; break;
          default: searchTerm = '';
        }
        table.search(searchTerm).draw();
      };

      // Filter by tab
      window.filterByTab = function(tab) {
        table.search('').draw();
      };

    </script>

    <!-- Bulk Assign Modal -->
    <div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-user"></i> Bulk Assign Tickets</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-assign-user">Assign To:</label>
              <select id="bulk-assign-user" class="form-control">
                <option value="">Select User...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-assign-count">0</span> ticket(s) will be assigned</small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="executeBulkAssign()">
              <i class="fa fa-check"></i> Assign
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Status Modal -->
    <div class="modal fade" id="bulkStatusModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-flag"></i> Bulk Update Status</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-status">Change Status To:</label>
              <select id="bulk-status" class="form-control">
                <option value="">Select Status...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-status-count">0</span> ticket(s) will be updated</small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-info" onclick="executeBulkUpdateStatus()">
              <i class="fa fa-check"></i> Update Status
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Priority Modal -->
    <div class="modal fade" id="bulkPriorityModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-exclamation-circle"></i> Bulk Update Priority</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-priority">Change Priority To:</label>
              <select id="bulk-priority" class="form-control">
                <option value="">Select Priority...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-priority-count">0</span> ticket(s) will be updated</small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-warning" onclick="executeBulkUpdatePriority()">
              <i class="fa fa-check"></i> Update Priority
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Category Modal -->
    <div class="modal fade" id="bulkCategoryModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-tags"></i> Bulk Update Category</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-category">Change Category To:</label>
              <select id="bulk-category" class="form-control">
                <option value="">Select Category...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-category-count">0</span> ticket(s) will be updated</small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" onclick="executeBulkUpdateCategory()">
              <i class="fa fa-check"></i> Update Category
            </button>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Global variables
      var bulkOptions = {
        users: [],
        statuses: [],
        priorities: [],
        types: []
      };

      // Load bulk options on page load
      $(document).ready(function() {
        loadBulkOptions();
      });

      // Load options for dropdowns
      function loadBulkOptions() {
        $.ajax({
          url: '{{ route("tickets.bulk.options") }}',
          type: 'GET',
          success: function(response) {
            if (response.success) {
              bulkOptions = response.data;
              populateDropdowns();
            }
          },
          error: function(xhr) {
            console.error('Failed to load bulk options:', xhr);
          }
        });
      }

      // Populate all dropdowns
      function populateDropdowns() {
        // Users dropdown
        var usersSelect = $('#bulk-assign-user');
        usersSelect.empty().append('<option value="">Select User...</option>');
        bulkOptions.users.forEach(function(user) {
          usersSelect.append('<option value="' + user.id + '">' + user.name + ' (' + user.email + ')</option>');
        });

        // Statuses dropdown
        var statusesSelect = $('#bulk-status');
        statusesSelect.empty().append('<option value="">Select Status...</option>');
        bulkOptions.statuses.forEach(function(status) {
          statusesSelect.append('<option value="' + status.id + '">' + status.name + '</option>');
        });

        // Priorities dropdown
        var prioritiesSelect = $('#bulk-priority');
        prioritiesSelect.empty().append('<option value="">Select Priority...</option>');
        bulkOptions.priorities.forEach(function(priority) {
          prioritiesSelect.append('<option value="' + priority.id + '">' + priority.name + '</option>');
        });

        // Categories dropdown
        var typesSelect = $('#bulk-category');
        typesSelect.empty().append('<option value="">Select Category...</option>');
        bulkOptions.types.forEach(function(type) {
          typesSelect.append('<option value="' + type.id + '">' + type.name + '</option>');
        });
      }

      // Toggle select all
      function toggleSelectAll(checkbox) {
        $('.ticket-checkbox').prop('checked', checkbox.checked);
        updateBulkToolbar();
      }

      // Update bulk actions toolbar visibility
      function updateBulkToolbar() {
        var selectedCount = $('.ticket-checkbox:checked').length;
        $('#selected-count').text(selectedCount);
        
        if (selectedCount > 0) {
          $('#bulk-actions-toolbar').slideDown();
        } else {
          $('#bulk-actions-toolbar').slideUp();
        }

        // Update select all checkbox state
        var totalCheckboxes = $('.ticket-checkbox').length;
        $('#select-all-tickets').prop('checked', selectedCount === totalCheckboxes);
      }

      // Clear selection
      function clearSelection() {
        $('.ticket-checkbox').prop('checked', false);
        $('#select-all-tickets').prop('checked', false);
        updateBulkToolbar();
      }

      // Get selected ticket IDs
      function getSelectedTicketIds() {
        var ticketIds = [];
        $('.ticket-checkbox:checked').each(function() {
          ticketIds.push($(this).val());
        });
        return ticketIds;
      }

      // Show modals
      function showBulkAssignModal() {
        var selectedCount = getSelectedTicketIds().length;
        $('#bulk-assign-count').text(selectedCount);
        $('#bulkAssignModal').modal('show');
      }

      function showBulkStatusModal() {
        var selectedCount = getSelectedTicketIds().length;
        $('#bulk-status-count').text(selectedCount);
        $('#bulkStatusModal').modal('show');
      }

      function showBulkPriorityModal() {
        var selectedCount = getSelectedTicketIds().length;
        $('#bulk-priority-count').text(selectedCount);
        $('#bulkPriorityModal').modal('show');
      }

      function showBulkCategoryModal() {
        var selectedCount = getSelectedTicketIds().length;
        $('#bulk-category-count').text(selectedCount);
        $('#bulkCategoryModal').modal('show');
      }

      // Execute bulk operations
      function executeBulkAssign() {
        var ticketIds = getSelectedTicketIds();
        var assignedTo = $('#bulk-assign-user').val();

        if (!assignedTo) {
          alert('Please select a user to assign tickets to.');
          return;
        }

        performBulkOperation('{{ route("tickets.bulk.assign") }}', {
          ticket_ids: ticketIds,
          assigned_to: assignedTo
        }, '#bulkAssignModal');
      }

      function executeBulkUpdateStatus() {
        var ticketIds = getSelectedTicketIds();
        var statusId = $('#bulk-status').val();

        if (!statusId) {
          alert('Please select a status.');
          return;
        }

        performBulkOperation('{{ route("tickets.bulk.update-status") }}', {
          ticket_ids: ticketIds,
          status_id: statusId
        }, '#bulkStatusModal');
      }

      function executeBulkUpdatePriority() {
        var ticketIds = getSelectedTicketIds();
        var priorityId = $('#bulk-priority').val();

        if (!priorityId) {
          alert('Please select a priority.');
          return;
        }

        performBulkOperation('{{ route("tickets.bulk.update-priority") }}', {
          ticket_ids: ticketIds,
          priority_id: priorityId
        }, '#bulkPriorityModal');
      }

      function executeBulkUpdateCategory() {
        var ticketIds = getSelectedTicketIds();
        var typeId = $('#bulk-category').val();

        if (!typeId) {
          alert('Please select a category.');
          return;
        }

        performBulkOperation('{{ route("tickets.bulk.update-category") }}', {
          ticket_ids: ticketIds,
          type_id: typeId
        }, '#bulkCategoryModal');
      }

      function confirmBulkDelete() {
        var ticketIds = getSelectedTicketIds();
        
        if (confirm('Are you sure you want to delete ' + ticketIds.length + ' ticket(s)? This action cannot be undone.')) {
          performBulkOperation('{{ route("tickets.bulk.delete") }}', {
            ticket_ids: ticketIds
          }, null);
        }
      }

      // Generic function to perform bulk operations
      function performBulkOperation(url, data, modalId) {
        $.ajax({
          url: url,
          type: 'POST',
          data: data,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          beforeSend: function() {
            // Show loading overlay
            showLoading('Processing request...');
            // Disable buttons
            $('button').prop('disabled', true);
          },
          success: function(response) {
            if (modalId) {
              $(modalId).modal('hide');
            }
            
            alert(response.message);
            
            // Reload page to show updated data
            window.location.reload();
          },
          error: function(xhr) {
            var errorMessage = 'An error occurred';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            }
            alert('Error: ' + errorMessage);
          },
          complete: function() {
            // Hide loading overlay
            hideLoading();
            // Re-enable buttons
            $('button').prop('disabled', false);
          }
        });
      }
    </script>

@include('components.loading-overlay')

@endsection


