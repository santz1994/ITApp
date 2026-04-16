@extends('layouts.app')

@push('styles')
<style>
    .portal-welcome-card {
        border-radius: 12px;
        background: linear-gradient(135deg, #0f3b66 0%, #1b6ca8 100%);
        color: #fff;
        padding: 20px;
        margin-bottom: 20px;
    }

    .portal-welcome-card .meta {
        opacity: 0.9;
        font-size: 13px;
    }

    .portal-context-box,
    .portal-highlight-box {
        border-radius: 10px;
        border: 1px solid #e8eef5;
        min-height: 214px;
    }

    .portal-context-row {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px dashed #eef2f6;
        padding: 8px 0;
        font-size: 13px;
    }

    .portal-context-row:last-child {
        border-bottom: none;
    }

    .portal-highlight-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .portal-highlight {
        border-radius: 8px;
        padding: 10px;
        color: #fff;
        min-height: 76px;
    }

    .portal-highlight .value {
        font-size: 22px;
        font-weight: 700;
        line-height: 1;
    }

    .portal-highlight .label {
        font-size: 11px;
        margin-top: 6px;
        display: inline-block;
    }

    .portal-highlight.theme-aqua { background: #00a7d0; }
    .portal-highlight.theme-green { background: #00a65a; }
    .portal-highlight.theme-yellow { background: #f39c12; }
    .portal-highlight.theme-blue { background: #3c8dbc; }
    .portal-highlight.theme-orange { background: #dd6b20; }

    .portal-metric .small-box {
        border-radius: 10px;
        overflow: hidden;
        min-height: 132px;
    }

    .portal-module-card {
        border-radius: 10px;
        border: 1px solid #eef2f6;
        transition: transform .15s ease, box-shadow .15s ease;
        min-height: 220px;
    }

    .portal-module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(22, 42, 67, 0.12);
    }

    .portal-module-card .box-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .portal-module-card .module-subtitle {
        font-size: 12px;
        letter-spacing: .2px;
        color: #6b7a8b;
    }

    .portal-module-card .module-description {
        min-height: 60px;
        color: #4f5f73;
    }

    .portal-table .label {
        font-weight: 600;
    }

    .portal-request-status {
        text-transform: uppercase;
        letter-spacing: .2px;
    }

    .portal-widget-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 12px;
    }

    .portal-widget-item {
        border: 1px solid #e8edf3;
        border-radius: 8px;
        padding: 10px;
    }

    .portal-widget-item .label-text {
        color: #6c7a89;
        font-size: 12px;
        display: block;
    }

    .portal-widget-item .value-text {
        color: #1f2d3d;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
    }

    @media (max-width: 767px) {
        .portal-welcome-card {
            padding: 16px;
        }

        .portal-highlight-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .portal-widget-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('main-content')

@component('components.page-header')
    @slot('icon') fa-th-large @endslot
    @slot('title') Main Portal Dashboard @endslot
    @slot('subtitle') {{ $subtitle ?? 'Role-based navigation center for every module in one place.' }} @endslot
@endcomponent

<div class="portal-welcome-card">
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <h3 style="margin-top: 0; margin-bottom: 8px;">Welcome, {{ auth()->user()->name }}</h3>
            <div class="meta">
                <i class="fa fa-id-badge"></i> Role: {{ $primaryRoleLabel ?? 'User' }}
                &nbsp;|&nbsp;
                <i class="fa fa-envelope"></i> {{ auth()->user()->email }}
            </div>
            @if(!empty($userRoleNames))
                <div class="meta" style="margin-top: 6px;">
                    <i class="fa fa-users"></i> Role Set: {{ implode(', ', $userRoleNames) }}
                </div>
            @endif
            <div class="meta" style="margin-top: 6px;">
                Workspace: {{ $workspaceContext['division'] ?? '-' }} / {{ $workspaceContext['location'] ?? '-' }}
            </div>
        </div>
        <div class="col-md-4 col-sm-12 text-right" style="padding-top: 6px;">
            <div><strong>WIB Time</strong></div>
            <div style="font-size: 20px;">{{ ($jakartaNow ?? now('Asia/Jakarta'))->format('d M Y H:i') }}</div>
            <div class="meta">Asia/Jakarta (UTC+7)</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $metrics['open_tickets'] ?? 0 }}</h3>
                <p>Open Tickets</p>
            </div>
            <div class="icon"><i class="fa fa-ticket"></i></div>
            <a href="{{ $quickLinks['tickets'] ?? '#' }}" class="small-box-footer">IT Support <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $metrics['meetings_today'] ?? 0 }}</h3>
                <p>Meetings Today</p>
            </div>
            <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
            <a href="{{ $quickLinks['meeting_rooms'] ?? '#' }}" class="small-box-footer">Meeting Room <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $metrics['pending_requests'] ?? 0 }}</h3>
                <p>Pending Requests</p>
            </div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            <a href="{{ $quickLinks['purchase_requests'] ?? '#' }}" class="small-box-footer">Purchase Request <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $metrics['total_assets'] ?? 0 }}</h3>
                <p>{{ $assetMetricLabel ?? 'Total Assets' }}</p>
            </div>
            <div class="icon"><i class="fa fa-cubes"></i></div>
            <a href="{{ $quickLinks['assets'] ?? '#' }}" class="small-box-footer">Assets Management <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="box box-solid portal-context-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-user-circle"></i> My Workspace Context</h3>
            </div>
            <div class="box-body">
                <div class="portal-context-row">
                    <span class="text-muted">Division</span>
                    <strong>{{ $workspaceContext['division'] ?? '-' }}</strong>
                </div>
                <div class="portal-context-row">
                    <span class="text-muted">Location</span>
                    <strong>{{ $workspaceContext['location'] ?? '-' }}</strong>
                </div>
                <div class="portal-context-row">
                    <span class="text-muted">Building</span>
                    <strong>{{ $workspaceContext['building'] ?? '-' }}</strong>
                </div>
                <div class="portal-context-row">
                    <span class="text-muted">Last Login</span>
                    <strong>{{ $workspaceContext['last_login_human'] ?? 'Unknown' }}</strong>
                </div>
                <div class="portal-context-row">
                    <span class="text-muted">Account Status</span>
                    <span class="label {{ !empty($workspaceContext['is_active']) ? 'label-success' : 'label-default' }}">
                        {{ !empty($workspaceContext['is_active']) ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-solid portal-highlight-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bullseye"></i> Role Focus Snapshot</h3>
            </div>
            <div class="box-body">
                <div class="portal-highlight-grid">
                    @forelse($roleHighlights ?? [] as $highlight)
                        <div class="portal-highlight theme-{{ $highlight['theme'] ?? 'blue' }}">
                            <div><i class="fa {{ $highlight['icon'] ?? 'fa-circle' }}"></i></div>
                            <div class="value">{{ $highlight['value'] ?? 0 }}</div>
                            <span class="label">{{ $highlight['label'] ?? '-' }}</span>
                        </div>
                    @empty
                        <div class="alert alert-info" style="margin-bottom: 0;">
                            Role snapshot is not available for your account yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@if(($metrics['pending_meeting_approvals'] ?? 0) > 0 || ($metrics['active_users'] ?? 0) > 0)
    <div class="row">
        @if(($metrics['pending_meeting_approvals'] ?? 0) > 0)
            <div class="col-md-6">
                <div class="alert alert-warning" style="border-radius: 8px;">
                    <i class="fa fa-clock-o"></i>
                    <strong>{{ $metrics['pending_meeting_approvals'] }}</strong> meeting request(s) waiting for approval.
                </div>
            </div>
        @endif
        @if(($metrics['active_users'] ?? 0) > 0)
            <div class="col-md-6">
                <div class="alert alert-info" style="border-radius: 8px;">
                    <i class="fa fa-users"></i>
                    <strong>{{ $metrics['active_users'] }}</strong> active users currently registered in system.
                </div>
            </div>
        @endif
    </div>
@endif

<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-th"></i> Module Navigation / Navigasi Modul</h3>
    </div>
    <div class="box-body">
        <div class="row">
            @forelse($modules as $module)
                <div class="col-lg-4 col-md-6 col-sm-12" style="margin-bottom: 14px;">
                    <div class="box box-solid portal-module-card">
                        <div class="box-header with-border bg-{{ $module['theme'] }}">
                            <h3 class="box-title" style="color: #fff;">
                                <i class="fa {{ $module['icon'] }}"></i> {{ $module['title'] }}
                            </h3>
                        </div>
                        <div class="box-body">
                            <div class="module-subtitle">{{ $module['subtitle'] }}</div>
                            <p class="module-description" style="margin-top: 10px;">{{ $module['description'] }}</p>

                            @if(!is_null($module['stat']))
                                <p style="margin-bottom: 10px;">
                                    <span class="label label-{{ $module['theme'] }}">{{ $module['stat'] }}</span>
                                    <small class="text-muted"> {{ $module['stat_label'] }}</small>
                                </p>
                            @endif

                            <a href="{{ $module['url'] }}" class="btn btn-{{ $module['theme'] }} btn-sm">
                                Open Module
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-xs-12">
                    <div class="alert alert-info">
                        No modules are configured for your current role. Please contact administrator.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="box box-default portal-table">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Purchase Request Snapshot</h3>
        <div class="box-tools pull-right">
            <a href="{{ $quickLinks['purchase_requests'] ?? '#' }}" class="btn btn-default btn-sm">
                <i class="fa fa-external-link"></i> Full Purchase Requests
            </a>
        </div>
    </div>
    <div class="box-body no-padding">
        @if(isset($recentAssetRequests) && $recentAssetRequests->count() > 0)
            @php
                $statusClass = [
                    'pending' => 'label-warning',
                    'approved' => 'label-success',
                    'rejected' => 'label-danger',
                    'fulfilled' => 'label-primary',
                ];
                $priorityClass = [
                    'low' => 'label-default',
                    'medium' => 'label-info',
                    'high' => 'label-warning',
                    'urgent' => 'label-danger',
                ];
            @endphp
            <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>Request No</th>
                            <th>Requester</th>
                            <th>Asset Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created (WIB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAssetRequests as $purchaseRequest)
                            <tr>
                                <td>
                                    <a href="{{ route('asset-requests.show', $purchaseRequest->id) }}">
                                        <strong>{{ $purchaseRequest->request_number ?? ('AR-' . $purchaseRequest->id) }}</strong>
                                    </a>
                                </td>
                                <td>{{ optional($purchaseRequest->requestedBy)->name ?? 'N/A' }}</td>
                                <td>{{ optional($purchaseRequest->assetType)->type_name ?? 'N/A' }}</td>
                                <td>
                                    @php $priority = strtolower((string) ($purchaseRequest->priority ?? 'medium')); @endphp
                                    <span class="label {{ $priorityClass[$priority] ?? 'label-default' }}">
                                        {{ ucfirst($priority) }}
                                    </span>
                                </td>
                                <td>
                                    @php $status = strtolower((string) ($purchaseRequest->status ?? 'pending')); @endphp
                                    <span class="label portal-request-status {{ $statusClass[$status] ?? 'label-default' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>{{ optional($purchaseRequest->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 20px;" class="text-muted text-center">
                <i class="fa fa-inbox"></i> No recent purchase requests available.
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-default portal-table">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-life-ring"></i> IT Support Summary</h3>
                <div class="box-tools pull-right">
                    <a href="{{ $quickLinks['tickets'] ?? '#' }}" class="btn btn-default btn-sm">
                        <i class="fa fa-external-link"></i> Open Tickets
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="portal-widget-grid">
                    <div class="portal-widget-item">
                        <span class="label-text">Open Tickets</span>
                        <span class="value-text">{{ $ticketStatusBreakdown['open'] ?? 0 }}</span>
                    </div>
                    <div class="portal-widget-item">
                        <span class="label-text">Assigned to Me</span>
                        <span class="value-text">{{ $ticketStatusBreakdown['assigned_open'] ?? 0 }}</span>
                    </div>
                    <div class="portal-widget-item">
                        <span class="label-text">Urgent Open</span>
                        <span class="value-text">{{ $ticketStatusBreakdown['urgent_open'] ?? 0 }}</span>
                    </div>
                    <div class="portal-widget-item">
                        <span class="label-text">Resolved / Closed</span>
                        <span class="value-text">{{ ($ticketStatusBreakdown['resolved'] ?? 0) + ($ticketStatusBreakdown['closed'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="box-body no-padding">
                @if(isset($recentTickets) && $recentTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Created (WIB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTickets as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tickets.show', $ticket->id) }}">
                                                <strong>{{ $ticket->ticket_code }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 42) }}</td>
                                        <td>
                                            <span class="label label-default">
                                                {{ optional($ticket->ticket_status)->status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ optional($ticket->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding: 20px;" class="text-muted text-center">
                        <i class="fa fa-inbox"></i> No recent tickets available.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default portal-table">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> Meeting Room Summary</h3>
                <div class="box-tools pull-right">
                    <a href="{{ $quickLinks['meeting_rooms'] ?? '#' }}" class="btn btn-default btn-sm">
                        <i class="fa fa-external-link"></i> Open Bookings
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="portal-widget-grid">
                    <div class="portal-widget-item">
                        <span class="label-text">Pending</span>
                        <span class="value-text">{{ $meetingStatusBreakdown['pending'] ?? 0 }}</span>
                    </div>
                    <div class="portal-widget-item">
                        <span class="label-text">Approved</span>
                        <span class="value-text">{{ $meetingStatusBreakdown['approved'] ?? 0 }}</span>
                    </div>
                    <div class="portal-widget-item">
                        <span class="label-text">Finished</span>
                        <span class="value-text">{{ $meetingStatusBreakdown['finished'] ?? 0 }}</span>
                    </div>
                    <div class="portal-widget-item">
                        <span class="label-text">Cancelled / Rejected</span>
                        <span class="value-text">{{ ($meetingStatusBreakdown['cancelled'] ?? 0) + ($meetingStatusBreakdown['rejected'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="box-body no-padding">
                @if(isset($recentMeetingBookings) && $recentMeetingBookings->count() > 0)
                    @php
                        $meetingStatusClass = [
                            'pending' => 'label-warning',
                            'approved' => 'label-success',
                            'rejected' => 'label-danger',
                            'finished' => 'label-primary',
                            'cancelled' => 'label-default',
                        ];
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Requester</th>
                                    <th>Status</th>
                                    <th>Start (WIB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMeetingBookings as $booking)
                                    @php $meetingStatus = strtolower((string) ($booking->status ?? 'pending')); @endphp
                                    <tr>
                                        <td>
                                            @if(\Illuminate\Support\Facades\Route::has('meeting-room-bookings.show'))
                                                <a href="{{ route('meeting-room-bookings.show', $booking->id) }}">
                                                    <strong>{{ $booking->room_name ?? 'Meeting Room' }}</strong>
                                                </a>
                                            @else
                                                <strong>{{ $booking->room_name ?? 'Meeting Room' }}</strong>
                                            @endif
                                        </td>
                                        <td>{{ $booking->requester_name ?? optional($booking->user)->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="label {{ $meetingStatusClass[$meetingStatus] ?? 'label-default' }}">
                                                {{ ucfirst($meetingStatus) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($booking->start_datetime)->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding: 20px;" class="text-muted text-center">
                        <i class="fa fa-inbox"></i> No recent meeting bookings available.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
