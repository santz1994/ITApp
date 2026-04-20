@extends('layouts.app')

@section('main-content')

@php $pageTitle = $pageTitle ?? ('Ticket #' . $ticket->ticket_code); @endphp

@include('components.page-header', [
    'title' => 'Ticket Details',
    'subtitle' => $ticket->subject ?? 'Ticket Information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Tickets', 'url' => route('tickets.index')],
        ['label' => '#' . $ticket->ticket_code]
    ]
])

  @include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'ticketShowLanguageEnglish',
    'indonesianButtonId' => 'ticketShowLanguageIndonesian',
    'ariaLabel' => 'Ticket Show Language Toggle',
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
    <div class="col-md-9">
      {{-- Ticket Information Card --}}
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">
            <i class="fa fa-ticket"></i> {{ $pageTitle }}
            @if($ticket->ticket_status)
              @php
                $statusName = strtolower($ticket->ticket_status->name);
                $badgeColor = '#6c757d'; // default gray
                if(str_contains($statusName, 'pending')) {
                  $badgeColor = '#d9534f'; // red
                } elseif(str_contains($statusName, 'open')) {
                  $badgeColor = '#f0ad4e'; // yellow/orange
                } elseif(str_contains($statusName, 'resolved') || str_contains($statusName, 'closed')) {
                  $badgeColor = '#5cb85c'; // green
                } elseif(str_contains($statusName, 'progress')) {
                  $badgeColor = '#5bc0de'; // blue
                }
              @endphp
              <span class="label" style="background-color: {{ $badgeColor }}; margin-left: 10px;">
                {{ $ticket->ticket_status->name }}
              </span>
            @endif
          </h3>
        </div>

        <div class="box-body">
          {{-- Nav tabs --}}
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
              <a href="#ticket-info" aria-controls="ticket-info" role="tab" data-toggle="tab">
                <i class="fa fa-info-circle"></i> <span data-i18n="tickets.show.tab.info">Ticket Info</span>
              </a>
            </li>
            <li role="presentation">
              <a href="#description" aria-controls="description" role="tab" data-toggle="tab">
                <i class="fa fa-file-text-o"></i> <span data-i18n="tickets.show.tab.description">Description</span>
              </a>
            </li>
            <li role="presentation">
              <a href="#related-assets" aria-controls="related-assets" role="tab" data-toggle="tab">
                <i class="fa fa-laptop"></i> <span data-i18n="tickets.show.tab.assets">Related Assets</span>
                @if($ticket->assets && $ticket->assets->count() > 0)
                  <span class="badge bg-green">{{ $ticket->assets->count() }}</span>
                @endif
              </a>
            </li>
            <li role="presentation">
              <a href="#notes" aria-controls="notes" role="tab" data-toggle="tab">
                <i class="fa fa-comments"></i> <span data-i18n="tickets.show.tab.notes">Notes</span>
                @if($ticketEntries && $ticketEntries->count() > 0)
                  <span class="badge bg-blue">{{ $ticketEntries->count() }}</span>
                @endif
              </a>
            </li>
            @if($ticket->history && $ticket->history->count() > 0)
            <li role="presentation">
              <a href="#history" aria-controls="history" role="tab" data-toggle="tab">
                <i class="fa fa-history"></i> <span data-i18n="tickets.show.tab.history">History</span>
                <span class="badge bg-blue">{{ $ticket->history->count() }}</span>
              </a>
            </li>
            @endif
            <li role="presentation">
              <a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
                <i class="fa fa-paperclip"></i> <span data-i18n="tickets.show.tab.attachments">Attachments</span>
                <span class="badge bg-green">{{ $ticket->getMedia('attachments')->count() ?? 0 }}</span>
              </a>
            </li>
          </ul>

          {{-- Tab panes --}}
          <div class="tab-content" style="padding-top: 20px;">
            {{-- Ticket Information Tab --}}
            <div role="tabpanel" class="tab-pane active" id="ticket-info">
              <div class="row">
                <div class="col-md-6">
                  <h4><i class="fa fa-info-circle text-primary"></i> <span data-i18n="tickets.show.section.basic">Basic Information</span></h4>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tr>
                        <th style="width: 150px;">Ticket Code:</th>
                        <td><strong>{{ $ticket->ticket_code }}</strong></td>
                      </tr>
                      <tr>
                        <th>Subject:</th>
                        <td><strong>{{ $ticket->subject }}</strong></td>
                      </tr>
                      <tr>
                        <th>Status:</th>
                        <td>
                          @if($ticket->ticket_status)
                            @php
                              $statusName = strtolower($ticket->ticket_status->name);
                              $badgeColor = '#6c757d';
                              if(str_contains($statusName, 'pending')) {
                                $badgeColor = '#d9534f';
                              } elseif(str_contains($statusName, 'open')) {
                                $badgeColor = '#f0ad4e';
                              } elseif(str_contains($statusName, 'resolved') || str_contains($statusName, 'closed')) {
                                $badgeColor = '#5cb85c';
                              } elseif(str_contains($statusName, 'progress')) {
                                $badgeColor = '#5bc0de';
                              }
                            @endphp
                            <span class="label" style="background-color: {{ $badgeColor }}">
                              {{ $ticket->ticket_status->name }}
                            </span>
                          @else
                            <span class="label label-default">N/A</span>
                          @endif
                        </td>
                      </tr>
                      <tr>
                        <th>Priority:</th>
                        <td>
                          <span class="label" style="background-color: {{ $ticket->ticket_priority->color ?? '#6c757d' }}">
                            {{ $ticket->ticket_priority->name ?? 'N/A' }}
                          </span>
                        </td>
                      </tr>
                      <tr>
                        <th>Type:</th>
                        <td>{{ $ticket->ticket_type->name ?? 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Reported By:</th>
                        <td>
                          <i class="fa fa-user"></i> {{ $ticket->user->name ?? 'N/A' }}
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>

                <div class="col-md-6">
                  <h4><i class="fa fa-calendar text-info"></i> <span data-i18n="tickets.show.section.timeline">Timeline & Assignment</span></h4>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tr>
                        <th style="width: 150px;">Created Date:</th>
                        <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d F Y - H:i') }}</td>
                      </tr>
                      <tr>
                        <th>Last Updated:</th>
                        <td>{{ \Carbon\Carbon::parse($ticket->updated_at)->format('d F Y - H:i') }}</td>
                      </tr>
                      <tr>
                        <th>Assigned To:</th>
                        <td>
                          @if($ticket->assignedTo)
                            <i class="fa fa-user"></i> {{ $ticket->assignedTo->name }}
                            @if($ticket->assigned_at)
                              <br><small class="text-muted">Assigned on {{ \Carbon\Carbon::parse($ticket->assigned_at)->format('d M Y, H:i') }}</small>
                            @endif
                          @else
                            <span class="label label-warning">Unassigned</span>
                          @endif
                        </td>
                      </tr>
                      @if($ticket->location)
                      <tr>
                        <th>Location:</th>
                        <td>
                          <i class="fa fa-map-marker"></i> {{ $ticket->location->name ?? 'N/A' }}
                        </td>
                      </tr>
                      @endif
                    </table>
                  </div>
                </div>
              </div>

              {{-- SLA Information --}}
              <div class="row">
                <div class="col-md-12">
                  <h4><i class="fa fa-clock-o text-warning"></i> <span data-i18n="tickets.show.section.sla">SLA Information</span></h4>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tr>
                        <th style="width: 150px;">Current Status:</th>
                        <td>
                          @if($ticket->ticket_status)
                            @php
                              $statusName = strtolower($ticket->ticket_status->name);
                              $badgeColor = '#6c757d';
                              if(str_contains($statusName, 'pending')) {
                                $badgeColor = '#d9534f';
                              } elseif(str_contains($statusName, 'open')) {
                                $badgeColor = '#f0ad4e';
                              } elseif(str_contains($statusName, 'resolved') || str_contains($statusName, 'closed')) {
                                $badgeColor = '#5cb85c';
                              } elseif(str_contains($statusName, 'progress')) {
                                $badgeColor = '#5bc0de';
                              }
                            @endphp
                            <span class="label" style="background-color: {{ $badgeColor }}">
                              {{ $ticket->ticket_status->name }}
                            </span>
                          @else
                            <span class="label label-default">N/A</span>
                          @endif
                        </td>
                        <th style="width: 150px;">SLA Due:</th>
                        <td>
                          @if($ticket->sla_due)
                            <span class="label
                              @if(\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due)))
                                label-danger
                              @elseif(\Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($ticket->sla_due)) < 2)
                                label-warning
                              @else
                                label-success
                              @endif
                            ">
                              {{ \Carbon\Carbon::parse($ticket->sla_due)->format('d M Y - H:i') }}
                            </span>
                            <small class="text-muted"> - {{ \Carbon\Carbon::parse($ticket->sla_due)->diffForHumans() }}</small>
                          @else
                            <span class="text-muted">Not set</span>
                          @endif
                        </td>
                        <th style="width: 150px;">Resolved:</th>
                        <td>
                          @if($ticket->resolved_at)
                            <i class="fa fa-check text-success"></i> {{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y - H:i') }}
                          @else
                            <span class="label label-default">Not resolved</span>
                          @endif
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            {{-- Description Tab --}}
            <div role="tabpanel" class="tab-pane" id="description">
              <h4><i class="fa fa-file-text text-primary"></i> Ticket Description</h4>
              <div class="well">
                {!! nl2br(e($ticket->description)) !!}
              </div>
            </div>

            {{-- Related Assets Tab --}}
            <div role="tabpanel" class="tab-pane" id="related-assets">
              <h4><i class="fa fa-laptop text-primary"></i> Related Assets
                @if($ticket->assets && $ticket->assets->count() > 0)
                  <span class="badge bg-green">{{ $ticket->assets->count() }}</span>
                @endif
              </h4>
              @if(isset($ticket->assets) && $ticket->assets->count() > 0)
                <div class="row">
                  @foreach($ticket->assets as $asset)
                    <div class="col-md-6">
                      <div class="box box-widget" style="margin-bottom: 15px;">
                        <div class="box-header with-border">
                          <h4 class="box-title">
                            <i class="fa fa-laptop"></i> 
                            <strong>{{ $asset->asset_tag ?? 'N/A' }}</strong>
                          </h4>
                          <div class="box-tools">
                            <a href="{{ route('assets.show', $asset->id) }}" target="_blank" class="btn btn-info btn-xs">
                              <i class="fa fa-external-link"></i> View
                            </a>
                          </div>
                        </div>
                        <div class="box-body">
                          <table class="table table-condensed table-striped">
                            @if(isset($asset->name) && $asset->name)
                            <tr>
                              <td style="width: 40%;"><strong>Name:</strong></td>
                              <td>{{ $asset->name }}</td>
                            </tr>
                            @endif
                            @if(isset($asset->model) && $asset->model)
                            <tr>
                              <td><strong>Model:</strong></td>
                              <td>{{ $asset->model->asset_model ?? 'N/A' }}</td>
                            </tr>
                            @endif
                            @if(isset($asset->serial_number) && $asset->serial_number)
                            <tr>
                              <td><strong>Serial Number:</strong></td>
                              <td><code>{{ $asset->serial_number }}</code></td>
                            </tr>
                            @endif
                            @if(isset($asset->status) && $asset->status)
                            <tr>
                              <td><strong>Status:</strong></td>
                              <td>
                                <span class="label" style="background-color: {{ $asset->status->color ?? '#6c757d' }}">
                                  {{ $asset->status->status ?? 'N/A' }}
                                </span>
                              </td>
                            </tr>
                            @endif
                            @if((isset($asset->location) && $asset->location) || (isset($asset->movement) && $asset->movement && isset($asset->movement->location)))
                            <tr>
                              <td><strong>Location:</strong></td>
                              <td>
                                <i class="fa fa-map-marker text-muted"></i> 
                                {{ $asset->movement && $asset->movement->location ? $asset->movement->location->location_name : ($asset->location->name ?? 'N/A') }}
                              </td>
                            </tr>
                            @endif
                            @if(isset($asset->assignedTo) && $asset->assignedTo)
                            <tr>
                              <td><strong>Assigned To:</strong></td>
                              <td>
                                <i class="fa fa-user text-muted"></i> {{ $asset->assignedTo->name }}
                              </td>
                            </tr>
                            @endif
                          </table>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="alert alert-info">
                  <i class="fa fa-info-circle"></i> No assets linked to this ticket.
                </div>
              @endif
            </div>

            {{-- Notes Tab --}}
            <div role="tabpanel" class="tab-pane" id="notes">
              @if($ticketEntries && $ticketEntries->count() > 0)
                <h4><i class="fa fa-comments text-primary"></i> Activity Notes 
                  <span class="badge bg-blue">{{ $ticketEntries->count() }}</span>
                </h4>
                <ul class="timeline">
                  @foreach($ticketEntries as $ticketEntry)
                    <?php $createdDate = \Carbon\Carbon::parse($ticketEntry->created_at); ?>
                    <li>
                      <i class="fa fa-comment bg-blue"></i>
                      <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> {{$createdDate->format('l, j F Y, H:i')}}</span>
                        <h3 class="timeline-header">
                          <i class="fa fa-user"></i> {{$ticketEntry->user->name}}
                        </h3>
                        <div class="timeline-body">
                          <div class="well well-sm">
                            {{$ticketEntry->note}}
                          </div>
                        </div>
                      </div>
                    </li>
                  @endforeach
                  <li>
                    <i class="fa fa-clock-o bg-gray"></i>
                  </li>
                </ul>
              @else
                <div class="alert alert-info">
                  <i class="fa fa-info-circle"></i> No notes added yet.
                </div>
              @endif
              
              <hr>
              <h4><i class="fa fa-plus text-success"></i> <span data-i18n="tickets.show.section.add_note">Add New Note</span></h4>
              <form method="POST" action="/tickets/{{$ticket->id}}">
                {{csrf_field()}}
                <div class="form-group">
                  <label for="note"><i class="fa fa-comment"></i> <span data-i18n="tickets.show.field.note">Note</span></label>
                  <textarea name="note" class="form-control" rows="5" data-i18n-placeholder="tickets.show.placeholder.note" placeholder="Enter your note here..." required>{{old('note')}}</textarea>
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <span data-i18n="tickets.show.action.save_note">Save Note</span>
                  </button>
                </div>
              </form>
            </div>

            {{-- History Tab --}}
            @if($ticket->history && $ticket->history->count() > 0)
            <div role="tabpanel" class="tab-pane" id="history">
              <h4><i class="fa fa-history text-primary"></i> Audit Trail 
                <span class="badge bg-blue">{{ $ticket->history->count() }}</span>
              </h4>
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr class="bg-light-blue">
                      <th><i class="fa fa-clock-o"></i> Date/Time</th>
                      <th><i class="fa fa-tag"></i> Field Changed</th>
                      <th><i class="fa fa-arrow-left"></i> Old Value</th>
                      <th><i class="fa fa-arrow-right"></i> New Value</th>
                      <th><i class="fa fa-user"></i> Changed By</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($ticket->history()->orderBy('changed_at', 'desc')->get() as $history)
                    <tr>
                      <td><small>{{ $history->changed_at ? $history->changed_at->format('Y-m-d H:i:s') : '-' }}</small></td>
                      <td><span class="label label-info">{{ ucwords(str_replace('_', ' ', $history->field_changed)) }}</span></td>
                      <td><span class="text-muted">{{ $history->old_value ?? '-' }}</span></td>
                      <td><strong>{{ $history->new_value ?? '-' }}</strong></td>
                      <td>{{ $history->changedByUser->name ?? 'System' }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            @endif

            {{-- Attachments Tab --}}
            <div role="tabpanel" class="tab-pane" id="attachments">
              <h4><i class="fa fa-paperclip text-primary"></i> File Attachments</h4>
              @include('partials.file-uploader', [
                'model_type' => 'ticket',
                'model_id' => $ticket->id,
                'collection' => 'attachments'
              ])
            </div>
          </div>
        </div>
      </div>
    </div>{{-- End col-md-9 --}}
    
    {{-- Sidebar --}}
    <div class="col-md-3">
      {{-- Quick Actions --}}
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="tickets.show.quick_actions.title">Quick Actions</span></h3>
        </div>
        <div class="box-body">
          @can('update', $ticket)
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary btn-block margin-bottom">
              <i class="fa fa-edit"></i> <span data-i18n="tickets.show.action.edit">Edit Ticket</span>
            </a>
          @endcan
          
          @php
            $canResolve = false;
            // Agent yang di-assign dapat resolve
            if(auth()->user() && $ticket->assigned_to == auth()->user()->id && !$ticket->resolved_at) {
              $canResolve = true;
            }
            // Super-admin dapat override resolve/unresolve
            if(auth()->user() && auth()->user()->hasRole('super-admin')) {
              $canResolve = true;
            }
          @endphp
          
          @if($canResolve && !$ticket->resolved_at)
            <form action="{{ route('tickets.resolve', $ticket) }}" method="POST" style="margin-bottom: 10px;">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn-success btn-block" onclick="return window.ticketShowConfirm('tickets.show.runtime.confirm.resolve', 'Mark this ticket as resolved?')">
                <i class="fa fa-check-circle"></i> <span data-i18n="tickets.show.action.resolve">Mark as Resolved</span>
              </button>
            </form>
          @elseif($canResolve && $ticket->resolved_at && auth()->user()->hasRole('super-admin'))
            <form action="{{ route('tickets.unresolve', $ticket) }}" method="POST" style="margin-bottom: 10px;">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn-warning btn-block" onclick="return window.ticketShowConfirm('tickets.show.runtime.confirm.reopen', 'Reopen this ticket?')">
                <i class="fa fa-undo"></i> <span data-i18n="tickets.show.action.reopen">Reopen Ticket</span>
              </button>
            </form>
          @endif
          
          <a href="{{ route('tickets.print', $ticket) }}" class="btn btn-info btn-block margin-bottom" target="_blank">
            <i class="fa fa-print"></i> <span data-i18n="tickets.show.action.print">Print Ticket</span>
          </a>
          <a href="{{ route('tickets.index') }}" class="btn btn-default btn-block">
            <i class="fa fa-arrow-left"></i> <span data-i18n="tickets.show.action.back">Back to Tickets</span>
          </a>
        </div>
      </div>

      {{-- Ticket Statistics --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistics</h3>
        </div>
        <div class="box-body">
          {{-- Total Notes --}}
          <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-comments"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Notes</span>
              <span class="info-box-number">{{ $ticket->ticket_entries->count() ?? 0 }}</span>
            </div>
          </div>

          {{-- Attachments --}}
          <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-paperclip"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Attachments</span>
              <span class="info-box-number">{{ $ticket->getMedia('attachments')->count() ?? 0 }}</span>
            </div>
          </div>

          {{-- Days Open --}}
          <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Days Open</span>
              <span class="info-box-number">{{ \Carbon\Carbon::parse($ticket->created_at)->diffInDays(\Carbon\Carbon::now()) }}</span>
            </div>
          </div>

          {{-- Days to Resolve --}}
          @if($ticket->resolved_at)
            <div class="info-box bg-aqua">
              <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Days to Resolve</span>
                <span class="info-box-number">{{ \Carbon\Carbon::parse($ticket->created_at)->diffInDays(\Carbon\Carbon::parse($ticket->resolved_at)) }}</span>
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- Related Links --}}
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-link"></i> Related Links</h3>
        </div>
        <div class="box-body">
          <ul class="list-unstyled">
            @if($ticket->asset)
              <li class="margin-bottom">
                <i class="fa fa-laptop text-blue"></i>
                <a href="{{ route('assets.show', $ticket->asset->id) }}">View Asset Details</a>
              </li>
            @endif
            @if($ticket->location)
              <li class="margin-bottom">
                <i class="fa fa-map-marker text-green"></i>
                <a href="{{ route('locations.show', $ticket->location->id) }}">View Location</a>
              </li>
            @endif
            @if($ticket->assignedTo)
              <li class="margin-bottom">
                <i class="fa fa-user text-purple"></i>
                <a href="{{ route('users.show', $ticket->assignedTo->id) }}">View Assigned User</a>
              </li>
            @endif
          </ul>
        </div>
      </div>

      {{-- Information Box --}}
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
        </div>
        <div class="box-body">
          <p><strong>About This Ticket:</strong></p>
          <ul class="list-unstyled">
            <li><i class="fa fa-check text-green"></i> Ticket details and timeline</li>
            <li><i class="fa fa-check text-green"></i> Activity notes and updates</li>
            <li><i class="fa fa-check text-green"></i> Audit history tracking</li>
            <li><i class="fa fa-check text-green"></i> File attachments</li>
          </ul>
          <hr>
          <p class="text-muted small">
            <i class="fa fa-lightbulb-o"></i> <strong>Tip:</strong> Use the tabs above to navigate between different ticket information sections.
          </p>
        </div>
      </div>
    </div>{{-- End col-md-3 --}}
  </div>{{-- End row --}}
  
  @if(count($errors))
    <div class="alert alert-danger">
      <ul>
        @foreach($errors->all() as $error)
          <li>{{$error}}</li>
        @endforeach
      </ul>
    </div>
  @endif
  
</div>{{-- End container-fluid --}}

  @if(Session::has('status'))
    <script>
      $(document).ready(function() {
        Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
      });
    </script>
  @endif

    @push('scripts')
    <script>
    (function() {
      var translations = {
        en: {
          'tickets.show.tab.info': 'Ticket Info',
          'tickets.show.tab.description': 'Description',
          'tickets.show.tab.assets': 'Related Assets',
          'tickets.show.tab.notes': 'Notes',
          'tickets.show.tab.history': 'History',
          'tickets.show.tab.attachments': 'Attachments',
          'tickets.show.section.basic': 'Basic Information',
          'tickets.show.section.timeline': 'Timeline & Assignment',
          'tickets.show.section.sla': 'SLA Information',
          'tickets.show.section.add_note': 'Add New Note',
          'tickets.show.field.note': 'Note',
          'tickets.show.placeholder.note': 'Enter your note here...',
          'tickets.show.action.save_note': 'Save Note',
          'tickets.show.quick_actions.title': 'Quick Actions',
          'tickets.show.action.edit': 'Edit Ticket',
          'tickets.show.action.resolve': 'Mark as Resolved',
          'tickets.show.action.reopen': 'Reopen Ticket',
          'tickets.show.action.print': 'Print Ticket',
          'tickets.show.action.back': 'Back to Tickets',
          'tickets.show.runtime.confirm.resolve': 'Mark this ticket as resolved?',
          'tickets.show.runtime.confirm.reopen': 'Reopen this ticket?'
        },
        id: {
          'tickets.show.tab.info': 'Info Tiket',
          'tickets.show.tab.description': 'Deskripsi',
          'tickets.show.tab.assets': 'Aset Terkait',
          'tickets.show.tab.notes': 'Catatan',
          'tickets.show.tab.history': 'Riwayat',
          'tickets.show.tab.attachments': 'Lampiran',
          'tickets.show.section.basic': 'Informasi Dasar',
          'tickets.show.section.timeline': 'Linimasa & Penugasan',
          'tickets.show.section.sla': 'Informasi SLA',
          'tickets.show.section.add_note': 'Tambah Catatan Baru',
          'tickets.show.field.note': 'Catatan',
          'tickets.show.placeholder.note': 'Masukkan catatan Anda di sini...',
          'tickets.show.action.save_note': 'Simpan Catatan',
          'tickets.show.quick_actions.title': 'Aksi Cepat',
          'tickets.show.action.edit': 'Ubah Tiket',
          'tickets.show.action.resolve': 'Tandai Selesai',
          'tickets.show.action.reopen': 'Buka Kembali Tiket',
          'tickets.show.action.print': 'Cetak Tiket',
          'tickets.show.action.back': 'Kembali ke Daftar Tiket',
          'tickets.show.runtime.confirm.resolve': 'Tandai tiket ini sebagai selesai?',
          'tickets.show.runtime.confirm.reopen': 'Buka kembali tiket ini?'
        }
      };

      var currentLanguage = 'en';
      var userId = '{{ (int) auth()->id() }}';
      var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
      var englishButton = document.getElementById('ticketShowLanguageEnglish');
      var indonesianButton = document.getElementById('ticketShowLanguageIndonesian');

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

      function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
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
      }

      function ticketShowConfirm(key, fallback) {
        return window.confirm(getLabel(key, fallback));
      }

      window.ticketShowLabel = getLabel;
      window.ticketShowConfirm = ticketShowConfirm;

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
    @endpush
@endsection


