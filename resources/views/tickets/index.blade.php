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
                <i class="fa fa-plus"></i> <span class="hidden-xs" data-i18n="tickets.action.new_ticket">New Ticket</span>
            </a>
            <a href="'.route('tickets.export').'" class="btn btn-success">
                <i class="fa fa-download"></i> <span class="hidden-xs" data-i18n="tickets.action.export">Export</span>
            </a>
        </div>
    '
])

  <div class="pull-right" style="margin-top: -52px; margin-bottom: 16px;">
    <div class="btn-group btn-group-xs" role="group" aria-label="Ticket Language Toggle">
      <button type="button" class="btn btn-default" id="ticketLanguageEnglish" data-lang="en">EN</button>
      <button type="button" class="btn btn-default" id="ticketLanguageIndonesian" data-lang="id">ID</button>
    </div>
  </div>
  <div class="clearfix"></div>

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
          <p data-i18n="tickets.summary.total_tickets">Total Tickets</p>
        </div>
        <div class="icon">
          <i class="fa fa-ticket"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByTab('all')">
          <span data-i18n="tickets.summary.view_all">View All</span> <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-yellow" onclick="filterByStatus('open')">
        <div class="inner">
          <h3>{{ $openTickets ?? 0 }}</h3>
          <p data-i18n="tickets.summary.open_tickets">Open Tickets</p>
        </div>
        <div class="icon">
          <i class="fa fa-folder-open"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('open')">
          <span data-i18n="tickets.summary.filter_open">Filter Open</span> <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-green" onclick="filterByStatus('resolved')">
        <div class="inner">
          <h3>{{ $resolvedTickets ?? 0 }}</h3>
          <p data-i18n="tickets.summary.resolved_tickets">Resolved Tickets</p>
        </div>
        <div class="icon">
          <i class="fa fa-check-circle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('resolved')">
          <span data-i18n="tickets.summary.filter_resolved">Filter Resolved</span> <i class="fa fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-red" onclick="filterByStatus('overdue')">
        <div class="inner">
          <h3>{{ $overdueTickets ?? 0 }}</h3>
          <p data-i18n="tickets.summary.overdue_sla">Overdue SLA</p>
        </div>
        <div class="icon">
          <i class="fa fa-exclamation-triangle"></i>
        </div>
        <a href="#" class="small-box-footer" onclick="event.preventDefault(); filterByStatus('overdue')">
          <span data-i18n="tickets.summary.filter_overdue">Filter Overdue</span> <i class="fa fa-arrow-circle-right"></i>
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
              <i class="fa fa-list"></i> <span data-i18n="tickets.tabs.all_tickets">All Tickets</span>
            </a>
          </li>
          <li class="{{ request('tab') == 'my' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'my'] + request()->except('tab')) }}">
              <i class="fa fa-user"></i> <span data-i18n="tickets.tabs.my_tickets">My Tickets</span>
            </a>
          </li>
          <li class="{{ request('tab') == 'unassigned' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'unassigned'] + request()->except('tab')) }}">
              <i class="fa fa-inbox"></i> <span data-i18n="tickets.tabs.unassigned">Unassigned</span>
            </a>
          </li>
          <li class="{{ request('tab') == 'sla-risk' ? 'active' : '' }}">
            <a href="{{ route('tickets.index', ['tab' => 'sla-risk'] + request()->except('tab')) }}">
              <i class="fa fa-clock-o"></i> <span data-i18n="tickets.tabs.sla_at_risk">SLA At Risk</span>
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
          <h3 class="box-title"><i class="fa fa-filter"></i> <span data-i18n="tickets.filters.title">Advanced Filters</span></h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
              <i class="fa fa-plus"></i> <span data-i18n="tickets.filters.expand">Expand Filters</span>
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
                  <label for="search"><i class="fa fa-search"></i> <span data-i18n="tickets.filters.search_label">Search Tickets</span></label>
                  <input type="text" id="search" name="search" class="form-control" 
                         placeholder="Ticket #, Subject, Description..." data-i18n-placeholder="tickets.filters.search_placeholder"
                         value="{{ request('search') }}">
                  <small class="text-muted" data-i18n="tickets.filters.search_hint">Search by ticket number, subject, or description</small>
                </div>
              </div>
              <div class="col-md-2 col-sm-6">
                <div class="form-group">
                  <label for="status_filter"><i class="fa fa-info-circle"></i> <span data-i18n="tickets.filters.status">Status</span></label>
                  <select id="status_filter" name="status" class="form-control">
                    <option value="" data-i18n="tickets.filters.all_statuses">All Statuses</option>
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
                  <label for="priority_filter"><i class="fa fa-exclamation-circle"></i> <span data-i18n="tickets.filters.priority">Priority</span></label>
                  <select id="priority_filter" name="priority" class="form-control">
                    <option value="" data-i18n="tickets.filters.all_priorities">All Priorities</option>
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
                  <label for="type_filter"><i class="fa fa-tag"></i> <span data-i18n="tickets.filters.ticket_type">Ticket Type</span></label>
                  <select id="type_filter" name="type" class="form-control">
                    <option value="" data-i18n="tickets.filters.all_types">All Types</option>
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
                  <label for="assigned_filter"><i class="fa fa-user"></i> <span data-i18n="tickets.filters.assigned_to">Assigned To</span></label>
                  <select id="assigned_filter" name="assigned_to" class="form-control">
                    <option value="" data-i18n="tickets.filters.all_admins">All Admins</option>
                    <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }} data-i18n="tickets.filters.unassigned">Unassigned</option>
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
                  <label for="location_filter"><i class="fa fa-map-marker"></i> <span data-i18n="tickets.filters.location">Location</span></label>
                  <select id="location_filter" name="location" class="form-control">
                    <option value="" data-i18n="tickets.filters.all_locations">All Locations</option>
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
                  <label for="date_from"><i class="fa fa-calendar"></i> <span data-i18n="tickets.filters.date_from">Date From</span></label>
                  <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="date_to"><i class="fa fa-calendar"></i> <span data-i18n="tickets.filters.date_to">Date To</span></label>
                  <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label for="sla_filter"><i class="fa fa-clock-o"></i> <span data-i18n="tickets.filters.sla_status">SLA Status</span></label>
                  <select id="sla_filter" name="sla" class="form-control">
                    <option value="" data-i18n="tickets.filters.all_sla_status">All SLA Status</option>
                    <option value="overdue" {{ request('sla') == 'overdue' ? 'selected' : '' }} data-i18n="tickets.filters.overdue">Overdue</option>
                    <option value="at-risk" {{ request('sla') == 'at-risk' ? 'selected' : '' }} data-i18n="tickets.filters.at_risk">At Risk (&lt; 4 hours)</option>
                    <option value="on-time" {{ request('sla') == 'on-time' ? 'selected' : '' }} data-i18n="tickets.filters.on_time">On Time</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-filter"></i> <span data-i18n="tickets.filters.apply">Apply Filters</span>
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-default">
                  <i class="fa fa-refresh"></i> <span data-i18n="tickets.filters.reset">Reset Filters</span>
                </a>
                <button type="button" id="exportFiltered" class="btn btn-success pull-right">
                  <i class="fa fa-file-excel-o"></i> <span data-i18n="tickets.filters.export_filtered">Export Filtered Results</span>
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
          <h3 class="box-title"><i class="fa fa-list"></i> <span data-i18n="tickets.table.title">Tickets List</span></h3>
          <div class="box-tools">
            <span class="label label-primary" id="ticketCount">{{ $tickets->total() ?? count($tickets) }} <span data-i18n="tickets.table.count_suffix">Tickets</span></span>
          </div>
        </div>
        <div class="box-body">

          <!-- Bulk Operations Toolbar -->
          <div id="bulk-actions-toolbar" style="display: none; margin-bottom: 20px; padding: 15px; border-radius: 5px;">
            <div class="row">
              <div class="col-md-12">
                <strong><i class="fa fa-check-square-o"></i> <span id="selected-count">0</span> <span data-i18n="tickets.bulk.selected">ticket(s) selected</span></strong>
                <div class="btn-group" style="margin-left: 20px;">
                  <button type="button" class="btn btn-sm btn-primary" onclick="showBulkAssignModal()">
                    <i class="fa fa-user"></i> <span data-i18n="tickets.bulk.assign">Assign</span>
                  </button>
                  <button type="button" class="btn btn-sm btn-info" onclick="showBulkStatusModal()">
                    <i class="fa fa-flag"></i> <span data-i18n="tickets.bulk.change_status">Change Status</span>
                  </button>
                  <button type="button" class="btn btn-sm btn-warning" onclick="showBulkPriorityModal()">
                    <i class="fa fa-exclamation-circle"></i> <span data-i18n="tickets.bulk.change_priority">Change Priority</span>
                  </button>
                  <button type="button" class="btn btn-sm btn-success" onclick="showBulkCategoryModal()">
                    <i class="fa fa-tags"></i> <span data-i18n="tickets.bulk.change_category">Change Category</span>
                  </button>
                  @can('delete', App\Ticket::class)
                  <button type="button" class="btn btn-sm btn-danger" onclick="confirmBulkDelete()">
                    <i class="fa fa-trash"></i> <span data-i18n="tickets.bulk.delete">Delete</span>
                  </button>
                  @endcan
                </div>
                <button type="button" class="btn btn-sm btn-default pull-right" onclick="clearSelection()">
                  <i class="fa fa-times"></i> <span data-i18n="tickets.bulk.clear_selection">Clear Selection</span>
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
                <th class="sortable" data-column="ticket_number" data-i18n="tickets.table.ticket_number">Ticket #</th>
                <th class="sortable" data-column="subject" data-i18n="tickets.table.subject">Subject</th>
                <th class="sortable" data-column="priority" data-i18n="tickets.table.priority">Priority</th>
                <th class="sortable" data-column="status" data-i18n="tickets.table.status">Status</th>
                <th class="sortable" data-column="sla" data-i18n="tickets.table.sla">SLA</th>
                <th class="sortable" data-column="creator" data-i18n="tickets.table.creator">Creator</th>
                <th class="sortable" data-column="location" data-i18n="tickets.table.location">Location</th>
                @can('assign', App\Ticket::class)
                  <th class="sortable" data-column="assigned_to" data-i18n="tickets.table.assigned_to">Assigned To</th>
                @endcan
                <th class="sortable" data-column="created_at" data-i18n="tickets.table.created">Created</th>
                <th class="actions" data-i18n="tickets.table.actions">Actions</th>
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
                        <span class="text-muted" data-i18n="tickets.table.no_sla">No SLA</span>
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
                            <span class="text-muted"><i class="fa fa-inbox"></i> <span data-i18n="tickets.table.unassigned">Unassigned</span></span>
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
      (function() {
        var translations = {
          en: {
            'tickets.action.new_ticket': 'New Ticket',
            'tickets.action.export': 'Export',
            'tickets.summary.total_tickets': 'Total Tickets',
            'tickets.summary.view_all': 'View All',
            'tickets.summary.open_tickets': 'Open Tickets',
            'tickets.summary.filter_open': 'Filter Open',
            'tickets.summary.resolved_tickets': 'Resolved Tickets',
            'tickets.summary.filter_resolved': 'Filter Resolved',
            'tickets.summary.overdue_sla': 'Overdue SLA',
            'tickets.summary.filter_overdue': 'Filter Overdue',
            'tickets.tabs.all_tickets': 'All Tickets',
            'tickets.tabs.my_tickets': 'My Tickets',
            'tickets.tabs.unassigned': 'Unassigned',
            'tickets.tabs.sla_at_risk': 'SLA At Risk',
            'tickets.filters.title': 'Advanced Filters',
            'tickets.filters.expand': 'Expand Filters',
            'tickets.filters.collapse': 'Collapse Filters',
            'tickets.filters.search_label': 'Search Tickets',
            'tickets.filters.search_placeholder': 'Ticket #, Subject, Description...',
            'tickets.filters.search_hint': 'Search by ticket number, subject, or description',
            'tickets.filters.status': 'Status',
            'tickets.filters.all_statuses': 'All Statuses',
            'tickets.filters.priority': 'Priority',
            'tickets.filters.all_priorities': 'All Priorities',
            'tickets.filters.ticket_type': 'Ticket Type',
            'tickets.filters.all_types': 'All Types',
            'tickets.filters.assigned_to': 'Assigned To',
            'tickets.filters.all_admins': 'All Admins',
            'tickets.filters.unassigned': 'Unassigned',
            'tickets.filters.location': 'Location',
            'tickets.filters.all_locations': 'All Locations',
            'tickets.filters.date_from': 'Date From',
            'tickets.filters.date_to': 'Date To',
            'tickets.filters.sla_status': 'SLA Status',
            'tickets.filters.all_sla_status': 'All SLA Status',
            'tickets.filters.overdue': 'Overdue',
            'tickets.filters.at_risk': 'At Risk (< 4 hours)',
            'tickets.filters.on_time': 'On Time',
            'tickets.filters.apply': 'Apply Filters',
            'tickets.filters.reset': 'Reset Filters',
            'tickets.filters.export_filtered': 'Export Filtered Results',
            'tickets.table.title': 'Tickets List',
            'tickets.table.count_suffix': 'Tickets',
            'tickets.table.ticket_number': 'Ticket #',
            'tickets.table.subject': 'Subject',
            'tickets.table.priority': 'Priority',
            'tickets.table.status': 'Status',
            'tickets.table.sla': 'SLA',
            'tickets.table.creator': 'Creator',
            'tickets.table.location': 'Location',
            'tickets.table.assigned_to': 'Assigned To',
            'tickets.table.created': 'Created',
            'tickets.table.actions': 'Actions',
            'tickets.table.no_sla': 'No SLA',
            'tickets.table.unassigned': 'Unassigned',
            'tickets.bulk.selected': 'ticket(s) selected',
            'tickets.bulk.assign': 'Assign',
            'tickets.bulk.change_status': 'Change Status',
            'tickets.bulk.change_priority': 'Change Priority',
            'tickets.bulk.change_category': 'Change Category',
            'tickets.bulk.delete': 'Delete',
            'tickets.bulk.clear_selection': 'Clear Selection',
            'tickets.modal.assign.title': 'Bulk Assign Tickets',
            'tickets.modal.assign.label': 'Assign To:',
            'tickets.modal.select_user': 'Select User...',
            'tickets.modal.assign.count_suffix': 'ticket(s) will be assigned',
            'tickets.modal.assign.action': 'Assign',
            'tickets.modal.status.title': 'Bulk Update Status',
            'tickets.modal.status.label': 'Change Status To:',
            'tickets.modal.select_status': 'Select Status...',
            'tickets.modal.status.count_suffix': 'ticket(s) will be updated',
            'tickets.modal.status.action': 'Update Status',
            'tickets.modal.priority.title': 'Bulk Update Priority',
            'tickets.modal.priority.label': 'Change Priority To:',
            'tickets.modal.select_priority': 'Select Priority...',
            'tickets.modal.priority.count_suffix': 'ticket(s) will be updated',
            'tickets.modal.priority.action': 'Update Priority',
            'tickets.modal.category.title': 'Bulk Update Category',
            'tickets.modal.category.label': 'Change Category To:',
            'tickets.modal.select_category': 'Select Category...',
            'tickets.modal.category.count_suffix': 'ticket(s) will be updated',
            'tickets.modal.category.action': 'Update Category',
            'tickets.modal.cancel': 'Cancel',
            'tickets.runtime.validation.assign_user': 'Please select a user to assign tickets to.',
            'tickets.runtime.validation.select_status': 'Please select a status.',
            'tickets.runtime.validation.select_priority': 'Please select a priority.',
            'tickets.runtime.validation.select_category': 'Please select a category.',
            'tickets.runtime.confirm.bulk_delete': 'Are you sure you want to delete {count} ticket(s)? This action cannot be undone.',
            'tickets.runtime.loading_processing': 'Processing request...',
            'tickets.runtime.error_generic': 'An error occurred',
            'tickets.runtime.error_prefix': 'Error:',
            'tickets.datatable.button.excel': 'Excel',
            'tickets.datatable.button.csv': 'CSV',
            'tickets.datatable.button.pdf': 'PDF',
            'tickets.datatable.button.copy': 'Copy',
            'tickets.datatable.length_menu': 'Show _MENU_ tickets per page',
            'tickets.datatable.info': 'Showing _START_ to _END_ of _TOTAL_ tickets',
            'tickets.datatable.info_empty': 'No tickets to show',
            'tickets.datatable.info_filtered': '(filtered from _MAX_ total tickets)',
            'tickets.datatable.search': 'Quick Search:'
          },
          id: {
            'tickets.action.new_ticket': 'Tiket Baru',
            'tickets.action.export': 'Ekspor',
            'tickets.summary.total_tickets': 'Total Tiket',
            'tickets.summary.view_all': 'Lihat Semua',
            'tickets.summary.open_tickets': 'Tiket Terbuka',
            'tickets.summary.filter_open': 'Filter Terbuka',
            'tickets.summary.resolved_tickets': 'Tiket Selesai',
            'tickets.summary.filter_resolved': 'Filter Selesai',
            'tickets.summary.overdue_sla': 'SLA Terlewati',
            'tickets.summary.filter_overdue': 'Filter Terlewati',
            'tickets.tabs.all_tickets': 'Semua Tiket',
            'tickets.tabs.my_tickets': 'Tiket Saya',
            'tickets.tabs.unassigned': 'Belum Ditugaskan',
            'tickets.tabs.sla_at_risk': 'SLA Berisiko',
            'tickets.filters.title': 'Filter Lanjutan',
            'tickets.filters.expand': 'Tampilkan Filter',
            'tickets.filters.collapse': 'Sembunyikan Filter',
            'tickets.filters.search_label': 'Cari Tiket',
            'tickets.filters.search_placeholder': 'No. Tiket, Subjek, Deskripsi...',
            'tickets.filters.search_hint': 'Cari berdasarkan nomor tiket, subjek, atau deskripsi',
            'tickets.filters.status': 'Status',
            'tickets.filters.all_statuses': 'Semua Status',
            'tickets.filters.priority': 'Prioritas',
            'tickets.filters.all_priorities': 'Semua Prioritas',
            'tickets.filters.ticket_type': 'Jenis Tiket',
            'tickets.filters.all_types': 'Semua Jenis',
            'tickets.filters.assigned_to': 'Ditugaskan Ke',
            'tickets.filters.all_admins': 'Semua Admin',
            'tickets.filters.unassigned': 'Belum Ditugaskan',
            'tickets.filters.location': 'Lokasi',
            'tickets.filters.all_locations': 'Semua Lokasi',
            'tickets.filters.date_from': 'Tanggal Mulai',
            'tickets.filters.date_to': 'Tanggal Akhir',
            'tickets.filters.sla_status': 'Status SLA',
            'tickets.filters.all_sla_status': 'Semua Status SLA',
            'tickets.filters.overdue': 'Terlambat',
            'tickets.filters.at_risk': 'Berisiko (< 4 jam)',
            'tickets.filters.on_time': 'Tepat Waktu',
            'tickets.filters.apply': 'Terapkan Filter',
            'tickets.filters.reset': 'Reset Filter',
            'tickets.filters.export_filtered': 'Ekspor Hasil Filter',
            'tickets.table.title': 'Daftar Tiket',
            'tickets.table.count_suffix': 'Tiket',
            'tickets.table.ticket_number': 'No. Tiket',
            'tickets.table.subject': 'Subjek',
            'tickets.table.priority': 'Prioritas',
            'tickets.table.status': 'Status',
            'tickets.table.sla': 'SLA',
            'tickets.table.creator': 'Pembuat',
            'tickets.table.location': 'Lokasi',
            'tickets.table.assigned_to': 'Ditugaskan Ke',
            'tickets.table.created': 'Dibuat',
            'tickets.table.actions': 'Aksi',
            'tickets.table.no_sla': 'Tanpa SLA',
            'tickets.table.unassigned': 'Belum Ditugaskan',
            'tickets.bulk.selected': 'tiket dipilih',
            'tickets.bulk.assign': 'Tugaskan',
            'tickets.bulk.change_status': 'Ubah Status',
            'tickets.bulk.change_priority': 'Ubah Prioritas',
            'tickets.bulk.change_category': 'Ubah Kategori',
            'tickets.bulk.delete': 'Hapus',
            'tickets.bulk.clear_selection': 'Bersihkan Pilihan',
            'tickets.modal.assign.title': 'Penugasan Massal Tiket',
            'tickets.modal.assign.label': 'Tugaskan Ke:',
            'tickets.modal.select_user': 'Pilih Pengguna...',
            'tickets.modal.assign.count_suffix': 'tiket akan ditugaskan',
            'tickets.modal.assign.action': 'Tugaskan',
            'tickets.modal.status.title': 'Update Status Massal',
            'tickets.modal.status.label': 'Ubah Status Menjadi:',
            'tickets.modal.select_status': 'Pilih Status...',
            'tickets.modal.status.count_suffix': 'tiket akan diperbarui',
            'tickets.modal.status.action': 'Update Status',
            'tickets.modal.priority.title': 'Update Prioritas Massal',
            'tickets.modal.priority.label': 'Ubah Prioritas Menjadi:',
            'tickets.modal.select_priority': 'Pilih Prioritas...',
            'tickets.modal.priority.count_suffix': 'tiket akan diperbarui',
            'tickets.modal.priority.action': 'Update Prioritas',
            'tickets.modal.category.title': 'Update Kategori Massal',
            'tickets.modal.category.label': 'Ubah Kategori Menjadi:',
            'tickets.modal.select_category': 'Pilih Kategori...',
            'tickets.modal.category.count_suffix': 'tiket akan diperbarui',
            'tickets.modal.category.action': 'Update Kategori',
            'tickets.modal.cancel': 'Batal',
            'tickets.runtime.validation.assign_user': 'Silakan pilih pengguna untuk menerima penugasan tiket.',
            'tickets.runtime.validation.select_status': 'Silakan pilih status.',
            'tickets.runtime.validation.select_priority': 'Silakan pilih prioritas.',
            'tickets.runtime.validation.select_category': 'Silakan pilih kategori.',
            'tickets.runtime.confirm.bulk_delete': 'Yakin ingin menghapus {count} tiket? Tindakan ini tidak dapat dibatalkan.',
            'tickets.runtime.loading_processing': 'Sedang memproses permintaan...',
            'tickets.runtime.error_generic': 'Terjadi kesalahan',
            'tickets.runtime.error_prefix': 'Kesalahan:',
            'tickets.datatable.button.excel': 'Excel',
            'tickets.datatable.button.csv': 'CSV',
            'tickets.datatable.button.pdf': 'PDF',
            'tickets.datatable.button.copy': 'Salin',
            'tickets.datatable.length_menu': 'Tampilkan _MENU_ tiket per halaman',
            'tickets.datatable.info': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ tiket',
            'tickets.datatable.info_empty': 'Tidak ada tiket untuk ditampilkan',
            'tickets.datatable.info_filtered': '(difilter dari total _MAX_ tiket)',
            'tickets.datatable.search': 'Pencarian Cepat:'
          }
        };

        var currentLanguage = 'en';
        var userId = '{{ (int) auth()->id() }}';
        var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
        var englishButton = document.getElementById('ticketLanguageEnglish');
        var indonesianButton = document.getElementById('ticketLanguageIndonesian');

        function getLanguage() {
          try {
            var raw = window.localStorage.getItem(languageStorageKey);
            if (!raw) {
              return 'en';
            }

            var parsed = JSON.parse(raw);
            return parsed && parsed.language === 'id' ? 'id' : 'en';
          } catch (error) {
            return 'en';
          }
        }

        function saveLanguage(language) {
          try {
            var raw = window.localStorage.getItem(languageStorageKey);
            var parsed = raw ? JSON.parse(raw) : {};
            parsed.language = language === 'id' ? 'id' : 'en';
            window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
          } catch (error) {
            // Keep silent if localStorage is unavailable.
          }
        }

        function getLabel(key) {
          var dictionary = translations[currentLanguage] || translations.en;
          return dictionary[key] || key;
        }

        function updateTicketCountLabel() {
          var ticketCount = document.getElementById('ticketCount');
          if (!ticketCount) {
            return;
          }

          var countMatch = (ticketCount.textContent || '').match(/\d+/);
          var countValue = countMatch ? countMatch[0] : '{{ $tickets->total() ?? count($tickets) }}';
          ticketCount.textContent = countValue + ' ' + getLabel('tickets.table.count_suffix');
        }

        function refreshDataTableUiTranslations() {
          if (!window.jQuery) {
            return;
          }

          var $wrapper = window.jQuery('#table_wrapper');
          if (!$wrapper.length) {
            return;
          }

          $wrapper.find('.buttons-excel').html('<i class="fa fa-file-excel-o"></i> ' + getLabel('tickets.datatable.button.excel'));
          $wrapper.find('.buttons-csv').html('<i class="fa fa-file-text-o"></i> ' + getLabel('tickets.datatable.button.csv'));
          $wrapper.find('.buttons-pdf').html('<i class="fa fa-file-pdf-o"></i> ' + getLabel('tickets.datatable.button.pdf'));
          $wrapper.find('.buttons-copy').html('<i class="fa fa-copy"></i> ' + getLabel('tickets.datatable.button.copy'));

          var $searchLabel = $wrapper.find('div.dataTables_filter label');
          if ($searchLabel.length) {
            $searchLabel.contents().filter(function() {
              return this.nodeType === 3;
            }).first().replaceWith(getLabel('tickets.datatable.search'));
          }
        }

        function applyLanguage(language) {
          currentLanguage = language === 'id' ? 'id' : 'en';
          var dictionary = translations[currentLanguage] || translations.en;

          Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
              node.textContent = dictionary[key];
            }
          });

          Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-placeholder]'), function(node) {
            var key = node.getAttribute('data-i18n-placeholder');
            if (dictionary[key]) {
              node.setAttribute('placeholder', dictionary[key]);
            }
          });

          if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
          }

          updateTicketCountLabel();
          refreshDataTableUiTranslations();
        }

        window.getTicketLabel = getLabel;

        if (englishButton && indonesianButton) {
          englishButton.addEventListener('click', function() {
            saveLanguage('en');
            applyLanguage('en');
          });

          indonesianButton.addEventListener('click', function() {
            saveLanguage('id');
            applyLanguage('id');
          });
        }

        applyLanguage(getLanguage());
      })();
    </script>
    <script>
      var table;

      $(document).ready(function() {
        table = $('#table').DataTable( {
          responsive: true,
          dom: 'l<"clear">Bfrtip',
          pageLength: 25,
          lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
          buttons: [
            {
              extend: 'excel',
              text: '<i class="fa fa-file-excel-o"></i> ' + (typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.button.excel') : 'Excel'),
              className: 'btn btn-success btn-sm',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
              }
            },
            {
              extend: 'csv',
              text: '<i class="fa fa-file-text-o"></i> ' + (typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.button.csv') : 'CSV'),
              className: 'btn btn-info btn-sm',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
              }
            },
            {
              extend: 'pdf',
              text: '<i class="fa fa-file-pdf-o"></i> ' + (typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.button.pdf') : 'PDF'),
              className: 'btn btn-danger btn-sm',
              orientation: 'landscape',
              pageSize: 'A4',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7]
              }
            },
            {
              extend: 'copy',
              text: '<i class="fa fa-copy"></i> ' + (typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.button.copy') : 'Copy'),
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
            lengthMenu: typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.length_menu') : "Show _MENU_ tickets per page",
            info: typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.info') : "Showing _START_ to _END_ of _TOTAL_ tickets",
            infoEmpty: typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.info_empty') : "No tickets to show",
            infoFiltered: typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.info_filtered') : "(filtered from _MAX_ total tickets)",
            search: typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.datatable.search') : "Quick Search:",
            paginate: {
              first: '<i class="fa fa-angle-double-left"></i>',
              previous: '<i class="fa fa-angle-left"></i>',
              next: '<i class="fa fa-angle-right"></i>',
              last: '<i class="fa fa-angle-double-right"></i>'
            }
          },
          drawCallback: function() {
            var info = this.api().page.info();
            var countSuffix = typeof window.getTicketLabel === 'function'
              ? window.getTicketLabel('tickets.table.count_suffix')
              : 'Tickets';
            $('#ticketCount').text(info.recordsDisplay + ' ' + countSuffix);
          }
        } );

        // Export filtered results
        $('#exportFiltered').on('click', function() {
          table.button('.buttons-excel').trigger();
        });

        // Enhanced box collapse button text toggle
        $('.box').on('expanded.boxwidget', function() {
          $(this).find('.btn-box-tool i').removeClass('fa-plus').addClass('fa-minus');
          $(this).find('.btn-box-tool').contents().last()[0].textContent = ' ' + (typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.filters.collapse') : 'Collapse Filters');
        });
        $('.box').on('collapsed.boxwidget', function() {
          $(this).find('.btn-box-tool i').removeClass('fa-minus').addClass('fa-plus');
          $(this).find('.btn-box-tool').contents().last()[0].textContent = ' ' + (typeof window.getTicketLabel === 'function' ? window.getTicketLabel('tickets.filters.expand') : 'Expand Filters');
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
            <h4 class="modal-title"><i class="fa fa-user"></i> <span data-i18n="tickets.modal.assign.title">Bulk Assign Tickets</span></h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-assign-user" data-i18n="tickets.modal.assign.label">Assign To:</label>
              <select id="bulk-assign-user" class="form-control">
                <option value="" data-i18n="tickets.modal.select_user">Select User...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-assign-count">0</span> <span data-i18n="tickets.modal.assign.count_suffix">ticket(s) will be assigned</span></small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="tickets.modal.cancel">Cancel</span></button>
            <button type="button" class="btn btn-primary" onclick="executeBulkAssign()">
              <i class="fa fa-check"></i> <span data-i18n="tickets.modal.assign.action">Assign</span>
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
            <h4 class="modal-title"><i class="fa fa-flag"></i> <span data-i18n="tickets.modal.status.title">Bulk Update Status</span></h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-status" data-i18n="tickets.modal.status.label">Change Status To:</label>
              <select id="bulk-status" class="form-control">
                <option value="" data-i18n="tickets.modal.select_status">Select Status...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-status-count">0</span> <span data-i18n="tickets.modal.status.count_suffix">ticket(s) will be updated</span></small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="tickets.modal.cancel">Cancel</span></button>
            <button type="button" class="btn btn-info" onclick="executeBulkUpdateStatus()">
              <i class="fa fa-check"></i> <span data-i18n="tickets.modal.status.action">Update Status</span>
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
            <h4 class="modal-title"><i class="fa fa-exclamation-circle"></i> <span data-i18n="tickets.modal.priority.title">Bulk Update Priority</span></h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-priority" data-i18n="tickets.modal.priority.label">Change Priority To:</label>
              <select id="bulk-priority" class="form-control">
                <option value="" data-i18n="tickets.modal.select_priority">Select Priority...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-priority-count">0</span> <span data-i18n="tickets.modal.priority.count_suffix">ticket(s) will be updated</span></small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="tickets.modal.cancel">Cancel</span></button>
            <button type="button" class="btn btn-warning" onclick="executeBulkUpdatePriority()">
              <i class="fa fa-check"></i> <span data-i18n="tickets.modal.priority.action">Update Priority</span>
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
            <h4 class="modal-title"><i class="fa fa-tags"></i> <span data-i18n="tickets.modal.category.title">Bulk Update Category</span></h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="bulk-category" data-i18n="tickets.modal.category.label">Change Category To:</label>
              <select id="bulk-category" class="form-control">
                <option value="" data-i18n="tickets.modal.select_category">Select Category...</option>
              </select>
            </div>
            <p class="text-muted">
              <small><span id="bulk-category-count">0</span> <span data-i18n="tickets.modal.category.count_suffix">ticket(s) will be updated</span></small>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="tickets.modal.cancel">Cancel</span></button>
            <button type="button" class="btn btn-success" onclick="executeBulkUpdateCategory()">
              <i class="fa fa-check"></i> <span data-i18n="tickets.modal.category.action">Update Category</span>
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

      function ticketLabel(key, fallback) {
        if (typeof window.getTicketLabel === 'function') {
          return window.getTicketLabel(key);
        }

        return fallback;
      }

      function ticketLabelWithCount(key, fallback, count) {
        return ticketLabel(key, fallback).replace('{count}', String(count));
      }

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
        usersSelect.empty().append('<option value="">' + ticketLabel('tickets.modal.select_user', 'Select User...') + '</option>');
        bulkOptions.users.forEach(function(user) {
          usersSelect.append('<option value="' + user.id + '">' + user.name + ' (' + user.email + ')</option>');
        });

        // Statuses dropdown
        var statusesSelect = $('#bulk-status');
        statusesSelect.empty().append('<option value="">' + ticketLabel('tickets.modal.select_status', 'Select Status...') + '</option>');
        bulkOptions.statuses.forEach(function(status) {
          statusesSelect.append('<option value="' + status.id + '">' + status.name + '</option>');
        });

        // Priorities dropdown
        var prioritiesSelect = $('#bulk-priority');
        prioritiesSelect.empty().append('<option value="">' + ticketLabel('tickets.modal.select_priority', 'Select Priority...') + '</option>');
        bulkOptions.priorities.forEach(function(priority) {
          prioritiesSelect.append('<option value="' + priority.id + '">' + priority.name + '</option>');
        });

        // Categories dropdown
        var typesSelect = $('#bulk-category');
        typesSelect.empty().append('<option value="">' + ticketLabel('tickets.modal.select_category', 'Select Category...') + '</option>');
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
          alert(ticketLabel('tickets.runtime.validation.assign_user', 'Please select a user to assign tickets to.'));
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
          alert(ticketLabel('tickets.runtime.validation.select_status', 'Please select a status.'));
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
          alert(ticketLabel('tickets.runtime.validation.select_priority', 'Please select a priority.'));
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
          alert(ticketLabel('tickets.runtime.validation.select_category', 'Please select a category.'));
          return;
        }

        performBulkOperation('{{ route("tickets.bulk.update-category") }}', {
          ticket_ids: ticketIds,
          type_id: typeId
        }, '#bulkCategoryModal');
      }

      function confirmBulkDelete() {
        var ticketIds = getSelectedTicketIds();
        
        if (confirm(ticketLabelWithCount('tickets.runtime.confirm.bulk_delete', 'Are you sure you want to delete {count} ticket(s)? This action cannot be undone.', ticketIds.length))) {
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
            showLoading(ticketLabel('tickets.runtime.loading_processing', 'Processing request...'));
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
            var errorMessage = ticketLabel('tickets.runtime.error_generic', 'An error occurred');
            if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            }
            alert(ticketLabel('tickets.runtime.error_prefix', 'Error:') + ' ' + errorMessage);
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


