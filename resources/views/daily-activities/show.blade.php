@extends('layouts.app')

@section('main-content')

@php $pageTitle = $pageTitle ?? ('Activity #' . $dailyActivity->id); @endphp

@include('components.page-header', [
    'title' => 'Daily Activity Details',
    'subtitle' => $dailyActivity->title ?? 'Activity Information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Daily Activities', 'url' => route('daily-activities.index')],
        ['label' => 'Activity #' . $dailyActivity->id]
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
    <div class="col-md-9">
      {{-- Activity Information Card --}}
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">
            <i class="fa fa-calendar-check-o"></i> {{ $pageTitle }}
            <span class="label label-info" style="margin-left: 10px;">
              {{ ucwords(str_replace('_', ' ', $dailyActivity->activity_type)) }}
            </span>
          </h3>
        </div>

        <div class="box-body">
          {{-- Nav tabs --}}
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
              <a href="#activity-info" aria-controls="activity-info" role="tab" data-toggle="tab">
                <i class="fa fa-info-circle"></i> Activity Info
              </a>
            </li>
            <li role="presentation">
              <a href="#description" aria-controls="description" role="tab" data-toggle="tab">
                <i class="fa fa-file-text-o"></i> Description
              </a>
            </li>
            @if($dailyActivity->start_time || $dailyActivity->end_time || $dailyActivity->location)
            <li role="presentation">
              <a href="#time-tracking" aria-controls="time-tracking" role="tab" data-toggle="tab">
                <i class="fa fa-clock-o"></i> Time Tracking
              </a>
            </li>
            @endif
            @if($dailyActivity->ticket_id || ($dailyActivity->notes && strlen($dailyActivity->notes) > 0))
            <li role="presentation">
              <a href="#related-info" aria-controls="related-info" role="tab" data-toggle="tab">
                <i class="fa fa-link"></i> Related Info
                @if($dailyActivity->ticket_id)
                  <span class="badge bg-blue">1</span>
                @endif
              </a>
            </li>
            @endif
            <li role="presentation">
              <a href="#metadata" aria-controls="metadata" role="tab" data-toggle="tab">
                <i class="fa fa-database"></i> Metadata
              </a>
            </li>
          </ul>

          {{-- Tab panes --}}
          <div class="tab-content" style="padding-top: 20px;">
            {{-- Activity Info Tab --}}
            <div role="tabpanel" class="tab-pane active" id="activity-info">
              <div class="row">
                <div class="col-md-6">
                  <h4><i class="fa fa-info-circle text-primary"></i> Activity Information</h4>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tr>
                        <th style="width: 150px;">Activity ID:</th>
                        <td><code>#{{ $dailyActivity->id }}</code></td>
                      </tr>
                      @if($dailyActivity->title)
                      <tr>
                        <th>Title:</th>
                        <td><strong>{{ $dailyActivity->title }}</strong></td>
                      </tr>
                      @endif
                      <tr>
                        <th>Activity Type:</th>
                        <td>
                          <span class="label label-info">
                            {{ ucwords(str_replace('_', ' ', $dailyActivity->activity_type)) }}
                          </span>
                        </td>
                      </tr>
                      <tr>
                        <th>Activity Date:</th>
                        <td>
                          {{ $dailyActivity->activity_date->format('d F Y') }}
                          <br><small class="text-muted">{{ $dailyActivity->activity_date->format('l') }}</small>
                        </td>
                      </tr>
                      <tr>
                        <th>Duration:</th>
                        <td>
                          @if($dailyActivity->duration_minutes)
                            <strong>{{ $dailyActivity->duration_minutes }} minutes</strong>
                            <small class="text-muted">(≈ {{ number_format($dailyActivity->duration_minutes / 60, 1) }} hours)</small>
                          @else
                            <span class="text-muted">N/A</span>
                          @endif
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>

                <div class="col-md-6">
                  <h4><i class="fa fa-user text-info"></i> User Information</h4>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tr>
                        <th style="width: 150px;">Logged By:</th>
                        <td>
                          <i class="fa fa-user"></i> {{ $dailyActivity->user->name ?? 'Unknown' }}
                          @if(isset($dailyActivity->user))
                            <br><small class="text-muted">{{ $dailyActivity->user->email }}</small>
                          @endif
                        </td>
                      </tr>
                      <tr>
                        <th>Created At:</th>
                        <td>
                          {{ $dailyActivity->created_at->format('d F Y - H:i') }}
                          <br><small class="text-muted">{{ $dailyActivity->created_at->diffForHumans() }}</small>
                        </td>
                      </tr>
                      <tr>
                        <th>Last Updated:</th>
                        <td>
                          {{ $dailyActivity->updated_at->format('d F Y - H:i') }}
                          <br><small class="text-muted">{{ $dailyActivity->updated_at->diffForHumans() }}</small>
                        </td>
                      </tr>
                      <tr>
                        <th>Days Ago:</th>
                        <td>
                          <span class="label label-success">
                            {{ $dailyActivity->activity_date->diffInDays(now()) }} days
                          </span>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            {{-- Description Tab --}}
            <div role="tabpanel" class="tab-pane" id="description">
              <div class="well" style="min-height: 150px; background: #f9f9f9;">
                <p style="margin: 0; line-height: 1.8; white-space: pre-wrap; font-size: 14px;">
                  {{ $dailyActivity->description ?? 'No description provided.' }}
                </p>
              </div>
            </div>

            {{-- Time Tracking Tab --}}
            @if($dailyActivity->start_time || $dailyActivity->end_time || $dailyActivity->location)
            <div role="tabpanel" class="tab-pane" id="time-tracking">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <tbody>
                    @if($dailyActivity->start_time)
                    <tr>
                      <th style="width: 200px;"><i class="fa fa-play-circle text-success"></i> Start Time</th>
                      <td><strong>{{ $dailyActivity->start_time->format('h:i A') }}</strong></td>
                    </tr>
                    @endif
                    @if($dailyActivity->end_time)
                    <tr>
                      <th><i class="fa fa-stop-circle text-danger"></i> End Time</th>
                      <td><strong>{{ $dailyActivity->end_time->format('h:i A') }}</strong></td>
                    </tr>
                    @endif
                    @if($dailyActivity->start_time && $dailyActivity->end_time)
                    <tr>
                      <th><i class="fa fa-hourglass-half text-warning"></i> Calculated Duration</th>
                      <td>
                        @php
                          $duration = $dailyActivity->start_time->diff($dailyActivity->end_time);
                          $hours = $duration->h;
                          $minutes = $duration->i;
                        @endphp
                        <strong class="text-primary">{{ $hours }} hours {{ $minutes }} minutes</strong>
                      </td>
                    </tr>
                    @endif
                    @if($dailyActivity->location)
                    <tr>
                      <th><i class="fa fa-map-marker text-info"></i> Work Location</th>
                      <td>
                        <span class="label label-info">
                          {{ ucwords(str_replace('_', ' ', $dailyActivity->location)) }}
                        </span>
                      </td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @endif

            {{-- Related Info Tab --}}
            @if($dailyActivity->ticket_id || ($dailyActivity->notes && strlen($dailyActivity->notes) > 0))
            <div role="tabpanel" class="tab-pane" id="related-info">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <tbody>
                    @if($dailyActivity->ticket_id)
                    <tr>
                      <th style="width: 200px;"><i class="fa fa-ticket text-primary"></i> Related Ticket</th>
                      <td>
                        @if(isset($dailyActivity->ticket))
                          <a href="{{ route('tickets.show', $dailyActivity->ticket_id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-external-link"></i> View Ticket #{{ $dailyActivity->ticket_id }}
                          </a>
                          @if($dailyActivity->ticket->subject)
                            <br><br>
                            <strong>Subject:</strong> {{ \Illuminate\Support\Str::limit($dailyActivity->ticket->subject, 100) }}
                          @endif
                        @else
                          Ticket #{{ $dailyActivity->ticket_id }}
                        @endif
                      </td>
                    </tr>
                    @endif
                    @if($dailyActivity->notes && strlen($dailyActivity->notes) > 0)
                    <tr>
                      <th><i class="fa fa-sticky-note text-warning"></i> Additional Notes</th>
                      <td style="white-space: pre-wrap;">{{ $dailyActivity->notes }}</td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @endif

            {{-- Metadata Tab --}}
            <div role="tabpanel" class="tab-pane" id="metadata">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <tbody>
                    <tr>
                      <th style="width: 200px;"><i class="fa fa-hashtag"></i> Activity ID</th>
                      <td><code>#{{ $dailyActivity->id }}</code></td>
                    </tr>
                    <tr>
                      <th><i class="fa fa-database"></i> Database Table</th>
                      <td><code>daily_activities</code></td>
                    </tr>
                    <tr>
                      <th><i class="fa fa-plus-circle"></i> Created At</th>
                      <td>
                        {{ $dailyActivity->created_at->format('d F Y - H:i:s') }}
                        <br><small class="text-muted">{{ $dailyActivity->created_at->diffForHumans() }}</small>
                      </td>
                    </tr>
                    <tr>
                      <th><i class="fa fa-edit"></i> Last Updated</th>
                      <td>
                        {{ $dailyActivity->updated_at->format('d F Y - H:i:s') }}
                        <br><small class="text-muted">{{ $dailyActivity->updated_at->diffForHumans() }}</small>
                      </td>
                    </tr>
                    @if(isset($dailyActivity->user))
                    <tr>
                      <th><i class="fa fa-user"></i> Logged By</th>
                      <td>
                        <strong>{{ $dailyActivity->user->name }}</strong>
                        <br><small>{{ $dailyActivity->user->email }}</small>
                      </td>
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

    {{-- Sidebar: 3 columns --}}
    <div class="col-md-3">
      {{-- Quick Actions --}}
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('daily-activities.edit', $dailyActivity->id) }}" class="btn btn-primary btn-block">
            <i class="fa fa-edit"></i> Edit Activity
          </a>
          <button onclick="window.print()" class="btn btn-default btn-block">
            <i class="fa fa-print"></i> Print
          </button>
          <a href="{{ route('daily-activities.index') }}" class="btn btn-default btn-block">
            <i class="fa fa-arrow-left"></i> Back to List
          </a>
          <a href="{{ route('daily-activities.create') }}" class="btn btn-success btn-block">
            <i class="fa fa-plus"></i> Add New Activity
          </a>
          <hr>
          <form action="{{ route('daily-activities.destroy', $dailyActivity->id) }}" 
                method="POST" 
                onsubmit="return confirm('Are you sure you want to delete this activity? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-block">
              <i class="fa fa-trash"></i> Delete Activity
            </button>
          </form>
        </div>
      </div>

      {{-- Activity Stats --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bar-chart"></i> Quick Stats</h3>
        </div>
        <div class="box-body">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Time Spent</span>
              <span class="info-box-number">
                {{ $dailyActivity->duration_minutes ?? 0 }} min
              </span>
            </div>
          </div>

          <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Days Ago</span>
              <span class="info-box-number">
                {{ $dailyActivity->activity_date->diffInDays(now()) }}
              </span>
            </div>
          </div>

          @if($dailyActivity->ticket_id)
          <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-ticket"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Linked Ticket</span>
              <span class="info-box-number" style="font-size: 16px;">
                #{{ $dailyActivity->ticket_id }}
              </span>
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Activity Timeline --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-history"></i> Activity Timeline</h3>
        </div>
        <div class="box-body">
          <ul class="timeline timeline-inverse">
            <li class="time-label">
              <span class="bg-red">
                {{ $dailyActivity->activity_date->format('M d, Y') }}
              </span>
            </li>
            @if($dailyActivity->start_time)
            <li>
              <i class="fa fa-play-circle bg-blue"></i>
              <div class="timeline-item">
                <h3 class="timeline-header">Activity Started</h3>
                <div class="timeline-body">
                  Started at {{ $dailyActivity->start_time->format('h:i A') }}
                </div>
              </div>
            </li>
            @endif
            @if($dailyActivity->end_time)
            <li>
              <i class="fa fa-stop-circle bg-green"></i>
              <div class="timeline-item">
                <h3 class="timeline-header">Activity Completed</h3>
                <div class="timeline-body">
                  Finished at {{ $dailyActivity->end_time->format('h:i A') }}
                </div>
              </div>
            </li>
            @endif
            <li>
              <i class="fa fa-save bg-gray"></i>
              <div class="timeline-item">
                <h3 class="timeline-header">Activity Logged</h3>
                <div class="timeline-body">
                  Logged by {{ $dailyActivity->user->name ?? 'System' }} at 
                  {{ $dailyActivity->created_at->format('h:i A') }}
                </div>
              </div>
            </li>
            <li>
              <i class="fa fa-clock-o bg-gray"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bar-chart"></i> Activity Statistics</h3>
        </div>
        <div class="box-body">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Time Spent</span>
              <span class="info-box-number">
                {{ $dailyActivity->duration_minutes ?? 0 }} min
              </span>
            </div>
          </div>

          <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Days Ago</span>
              <span class="info-box-number">
                {{ $dailyActivity->activity_date->diffInDays(now()) }}
              </span>
            </div>
          </div>

          @if($dailyActivity->ticket_id)
          <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-ticket"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Linked to Ticket</span>
              <span class="info-box-number">
                #{{ $dailyActivity->ticket_id }}
              </span>
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Activity Timeline --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-history"></i> Activity Timeline</h3>
        </div>
        <div class="box-body">
          <ul class="timeline timeline-inverse">
            <li class="time-label">
              <span class="bg-red">
                {{ $dailyActivity->activity_date->format('M d, Y') }}
              </span>
            </li>
            @if($dailyActivity->start_time)
            <li>
              <i class="fa fa-play-circle bg-blue"></i>
              <div class="timeline-item">
                <h3 class="timeline-header">Activity Started</h3>
                <div class="timeline-body">
                  Started at {{ $dailyActivity->start_time->format('h:i A') }}
                </div>
              </div>
            </li>
            @endif
            @if($dailyActivity->end_time)
            <li>
              <i class="fa fa-stop-circle bg-green"></i>
              <div class="timeline-item">
                <h3 class="timeline-header">Activity Completed</h3>
                <div class="timeline-body">
                  Finished at {{ $dailyActivity->end_time->format('h:i A') }}
                </div>
              </div>
            </li>
            @endif
            <li>
              <i class="fa fa-save bg-gray"></i>
              <div class="timeline-item">
                <h3 class="timeline-header">Activity Logged</h3>
                <div class="timeline-body">
                  Logged by {{ $dailyActivity->user->name ?? 'System' }} at 
                  {{ $dailyActivity->created_at->format('h:i A') }}
                </div>
              </div>
            </li>
            <li>
              <i class="fa fa-clock-o bg-gray"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Export functionality
    window.exportActivity = function(format) {
        const activityId = {{ $dailyActivity->id }};
        const exportUrl = `/daily-activities/${activityId}/export/${format}`;
        
        // Show loading message
        $('body').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
        
        // Simulate export (replace with actual endpoint)
        setTimeout(function() {
            $('.overlay').remove();
            alert('Export as ' + format.toUpperCase() + ' is being prepared. This feature will download the file.');
            // In production, redirect to: window.location.href = exportUrl;
        }, 1000);
    };
    
    // Print styles
    const printStyles = `
        @media print {
            .box-tools, .box-header .btn, .sidebar, .btn, form, .overlay, .no-print {
                display: none !important;
            }
            .col-md-8 {
                width: 100% !important;
            }
            .box {
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }
        }
    `;
    
    if (!$('#print-styles').length) {
        $('<style id="print-styles">' + printStyles + '</style>').appendTo('head');
    }
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush

@push('styles')
<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > li {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}

.timeline > li > .timeline-item {
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 10px;
    position: relative;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.timeline > li > .fa,
.timeline > li > .glyphicon,
.timeline > li > .ion {
    width: 30px;
    height: 30px;
    font-size: 14px;
    line-height: 30px;
    position: absolute;
    color: #666;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}

.timeline > .time-label > span {
    font-weight: 600;
    padding: 5px;
    display: inline-block;
    background-color: #fff;
    border-radius: 4px;
}

.timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding-bottom: 5px;
    font-size: 14px;
    line-height: 1.1;
}

.timeline-body {
    padding-top: 10px;
    font-size: 13px;
}

.metadata-alert {
    margin-bottom: 20px;
}
</style>
@endpush
@endsection
