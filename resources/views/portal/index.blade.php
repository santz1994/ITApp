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

    .portal-top-controls {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }

    .portal-language-toggle .btn.active {
        background: #1b6ca8;
        border-color: #1b6ca8;
        color: #fff;
    }

    .portal-quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .portal-quick-link-btn {
        border-radius: 20px;
        border: 1px solid #d9e4ef;
        background: #f8fbfe;
        color: #1f2d3d;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        font-size: 12px;
    }

    .portal-quick-link-btn:hover {
        border-color: #7aa8cf;
        text-decoration: none;
        color: #0f3b66;
    }

    .portal-pref-module-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .portal-pref-module-item .order-tools {
        display: inline-flex;
        gap: 4px;
    }

    .portal-pref-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .portal-pref-checkbox {
        border: 1px solid #e8edf3;
        border-radius: 8px;
        padding: 8px;
        display: block;
        font-size: 12px;
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

    .approval-center-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .approval-center-item {
        border: 1px solid #e8edf3;
        border-left-width: 4px;
        border-radius: 8px;
        padding: 12px;
        background: #fff;
    }

    .approval-center-item.theme-aqua { border-left-color: #00a7d0; }
    .approval-center-item.theme-orange { border-left-color: #dd6b20; }
    .approval-center-item.theme-blue { border-left-color: #3c8dbc; }

    .approval-center-count {
        font-size: 24px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }

    .approval-center-label {
        font-weight: 600;
        color: #233447;
        margin-bottom: 5px;
    }

    .approval-center-desc {
        font-size: 12px;
        color: #6c7a89;
        min-height: 34px;
        margin-bottom: 10px;
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

        .approval-center-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .portal-pref-grid {
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
            <h3 style="margin-top: 0; margin-bottom: 8px;"><span data-i18n="portal.welcome">Welcome</span>, {{ auth()->user()->name }}</h3>
            <div class="meta">
                <i class="fa fa-id-badge"></i> <span data-i18n="portal.role">Role</span>: {{ $primaryRoleLabel ?? 'User' }}
                &nbsp;|&nbsp;
                <i class="fa fa-envelope"></i> {{ auth()->user()->email }}
            </div>
            @if(!empty($userRoleNames))
                <div class="meta" style="margin-top: 6px;">
                    <i class="fa fa-users"></i> <span data-i18n="portal.role_set">Role Set</span>: {{ implode(', ', $userRoleNames) }}
                </div>
            @endif
            <div class="meta" style="margin-top: 6px;">
                <span data-i18n="portal.workspace">Workspace</span>: {{ $workspaceContext['division'] ?? '-' }} / {{ $workspaceContext['location'] ?? '-' }}
            </div>
        </div>
        <div class="col-md-4 col-sm-12 text-right" style="padding-top: 6px;">
            <div class="portal-top-controls">
                <div class="btn-group btn-group-xs portal-language-toggle" role="group" aria-label="Language Toggle">
                    <button type="button" class="btn btn-default" id="portalLanguageEnglish" data-lang="en">EN</button>
                    <button type="button" class="btn btn-default" id="portalLanguageIndonesian" data-lang="id">ID</button>
                </div>
                <button type="button" class="btn btn-default btn-xs" id="openPortalPersonalization">
                    <i class="fa fa-sliders"></i> <span data-i18n="portal.personalize">Personalize</span>
                </button>
            </div>
            <div><strong data-i18n="portal.wib_time">WIB Time</strong></div>
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
                <p data-i18n="metric.open_tickets">Open Tickets</p>
            </div>
            <div class="icon"><i class="fa fa-ticket"></i></div>
            <a href="{{ $quickLinks['tickets'] ?? '#' }}" class="small-box-footer"><span data-i18n="metric.it_support">IT Support</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $metrics['meetings_today'] ?? 0 }}</h3>
                <p data-i18n="metric.meetings_today">Meetings Today</p>
            </div>
            <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
            <a href="{{ $quickLinks['meeting_rooms'] ?? '#' }}" class="small-box-footer"><span data-i18n="metric.meeting_room">Meeting Room</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $metrics['pending_requests'] ?? 0 }}</h3>
                <p data-i18n="metric.pending_requests">Pending Requests</p>
            </div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            <a href="{{ $quickLinks['purchase_requests'] ?? '#' }}" class="small-box-footer"><span data-i18n="metric.purchase_request">Purchase Request</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $metrics['total_assets'] ?? 0 }}</h3>
                <p>{{ $assetMetricLabel ?? 'Total Assets' }}</p>
            </div>
            <div class="icon"><i class="fa fa-cubes"></i></div>
            <a href="{{ $quickLinks['assets'] ?? '#' }}" class="small-box-footer"><span data-i18n="metric.assets">Assets Management</span> <i class="fa fa-arrow-circle-right"></i></a>
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

@if(!empty($quickLinkOptions))
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="quick_access.title">Quick Access</span></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-default btn-xs" id="openPortalPersonalizationTop">
                    <i class="fa fa-sliders"></i> <span data-i18n="quick_access.customize">Customize</span>
                </button>
            </div>
        </div>
        <div class="box-body">
            <p class="text-muted" style="margin-bottom: 10px;" data-i18n="quick_access.description">Pin the shortcuts you use most and keep them in your preferred order.</p>
            <div id="portalQuickLinksContainer" class="portal-quick-links"></div>
        </div>
    </div>
@endif

@if(!empty($approvalCenter['enabled']))
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-tasks"></i> <span data-i18n="approval_center.title">Approval Center</span></h3>
            <div class="box-tools pull-right">
                <span class="label label-primary">{{ $approvalCenter['total_pending'] ?? 0 }} <span data-i18n="approval_center.pending">Pending</span></span>
            </div>
        </div>
        <div class="box-body">
            <div class="approval-center-grid">
                @foreach(($approvalCenter['items'] ?? []) as $queueItem)
                    <div class="approval-center-item theme-{{ $queueItem['theme'] ?? 'blue' }}">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div class="approval-center-count">{{ $queueItem['pending_count'] ?? 0 }}</div>
                            <div><i class="fa {{ $queueItem['icon'] ?? 'fa-circle' }} text-muted"></i></div>
                        </div>
                        <div class="approval-center-label">{{ $queueItem['label'] ?? '-' }}</div>
                        <div class="approval-center-desc">{{ $queueItem['description'] ?? '' }}</div>
                        <a href="{{ $queueItem['url'] ?? '#' }}" class="btn btn-{{ $queueItem['theme'] ?? 'primary' }} btn-xs">
                            {{ $queueItem['action_label'] ?? 'Open Queue' }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-th"></i> <span data-i18n="modules.title">Module Navigation</span></h3>
    </div>
    <div class="box-body">
        <div class="row" id="portalModuleGrid">
            @forelse($modules as $module)
                <div class="col-lg-4 col-md-6 col-sm-12" style="margin-bottom: 14px;" data-portal-module-key="{{ $module['key'] ?? \Illuminate\Support\Str::slug($module['title'] ?? 'module') }}">
                    <div class="box box-solid portal-module-card">
                        <div class="box-header with-border bg-{{ $module['theme'] }}">
                            <h3 class="box-title" style="color: #fff;">
                                <i class="fa {{ $module['icon'] }}"></i>
                                <span class="portal-module-title" data-portal-module-title="{{ $module['key'] ?? '' }}">{{ $module['title'] }}</span>
                            </h3>
                        </div>
                        <div class="box-body">
                            <div class="module-subtitle" data-portal-module-subtitle="{{ $module['key'] ?? '' }}">{{ $module['subtitle'] }}</div>
                            <p class="module-description" style="margin-top: 10px;">{{ $module['description'] }}</p>

                            @if(!is_null($module['stat']))
                                <p style="margin-bottom: 10px;">
                                    <span class="label label-{{ $module['theme'] }}">{{ $module['stat'] }}</span>
                                    <small class="text-muted"> {{ $module['stat_label'] }}</small>
                                </p>
                            @endif

                            <a href="{{ $module['url'] }}" class="btn btn-{{ $module['theme'] }} btn-sm">
                                <span data-i18n="modules.open">Open Module</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-xs-12">
                    <div class="alert alert-info">
                        <span data-i18n="modules.empty">No modules are configured for your current role. Please contact administrator.</span>
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
                <h3 class="box-title"><i class="fa fa-life-ring"></i> <span data-i18n="summary.it_support">IT Support Summary</span></h3>
                <div class="box-tools pull-right">
                    <a href="{{ $quickLinks['tickets'] ?? '#' }}" class="btn btn-default btn-sm">
                        <i class="fa fa-external-link"></i> <span data-i18n="summary.open_tickets">Open Tickets</span>
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
                <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> <span data-i18n="summary.meeting_room">Meeting Room Summary</span></h3>
                <div class="box-tools pull-right">
                    <a href="{{ $quickLinks['meeting_rooms'] ?? '#' }}" class="btn btn-default btn-sm">
                        <i class="fa fa-external-link"></i> <span data-i18n="summary.open_bookings">Open Bookings</span>
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

<div class="modal fade" id="portalPersonalizationModal" tabindex="-1" role="dialog" aria-labelledby="portalPersonalizationLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="portalPersonalizationLabel"><i class="fa fa-sliders"></i> <span data-i18n="pref.title">Portal Personalization</span></h4>
                <p class="text-muted" style="margin-top: 6px; margin-bottom: 0;" data-i18n="pref.subtitle">Arrange module order and choose shortcuts that match your daily workflow.</p>
            </div>
            <div class="modal-body">
                <h5 style="margin-top: 0;"><strong data-i18n="pref.modules_section">Module Order</strong></h5>
                <div id="portalPreferenceModuleList" class="list-group">
                    @foreach($modules as $module)
                        <div class="list-group-item portal-pref-module-item" data-module-key="{{ $module['key'] ?? \Illuminate\Support\Str::slug($module['title'] ?? 'module') }}">
                            <div>
                                <label style="margin: 0; font-weight: 600;">
                                    <input type="checkbox" class="portal-pref-module-visible" checked>
                                    <i class="fa {{ $module['icon'] ?? 'fa-th' }}"></i>
                                    <span data-pref-module-label>{{ $module['title'] }}</span>
                                </label>
                            </div>
                            <div class="order-tools">
                                <button type="button" class="btn btn-default btn-xs portal-pref-up" title="Move up"><i class="fa fa-arrow-up"></i></button>
                                <button type="button" class="btn btn-default btn-xs portal-pref-down" title="Move down"><i class="fa fa-arrow-down"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h5><strong data-i18n="pref.quick_links_section">Quick Access Links</strong></h5>
                <p class="text-muted" style="margin-bottom: 8px;" data-i18n="pref.quick_links_hint">Select the shortcuts you want to pin in the Quick Access row.</p>
                <div class="portal-pref-grid">
                    @foreach(($quickLinkOptions ?? []) as $quickLinkOption)
                        <label class="portal-pref-checkbox">
                            <input type="checkbox" class="portal-pref-quick-link" data-link-key="{{ $quickLinkOption['key'] }}" checked>
                            <i class="fa {{ $quickLinkOption['icon'] ?? 'fa-link' }}"></i>
                            <span data-link-label-en="{{ $quickLinkOption['label'] ?? '' }}" data-link-label-id="{{ $quickLinkOption['label_id'] ?? ($quickLinkOption['label'] ?? '') }}">{{ $quickLinkOption['label'] ?? '' }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" id="resetPortalPersonalization"><i class="fa fa-repeat"></i> <span data-i18n="pref.reset">Reset</span></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><span data-i18n="pref.cancel">Cancel</span></button>
                <button type="button" class="btn btn-primary" id="savePortalPersonalization"><i class="fa fa-save"></i> <span data-i18n="pref.save">Save Changes</span></button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var quickLinkOptions = @json($quickLinkOptions ?? []);
    var moduleLabels = {
        it_support: { en: 'IT Support Module', id: 'Modul Dukungan TI' },
        meeting_room: { en: 'Meeting Room', id: 'Ruang Rapat' },
        assets_management: { en: 'Assets Management', id: 'Manajemen Aset' },
        purchase_request: { en: 'Purchase Request', id: 'Permintaan Pengadaan' },
        profile: { en: 'Profile', id: 'Profil' },
        user_management: { en: 'User Management', id: 'Manajemen Pengguna' },
        settings: { en: 'Settings', id: 'Pengaturan' },
        kpi_dashboard: { en: 'KPI Dashboard', id: 'Dashboard KPI' },
        lcd_screen: { en: 'LCD Screen', id: 'Layar LCD' }
    };
    var moduleSubtitles = {
        it_support: { en: 'Ticketing and User Support', id: 'Tiket dan Dukungan Pengguna' },
        meeting_room: { en: 'Booking and Schedule', id: 'Booking dan Jadwal' },
        assets_management: { en: 'Inventory and Maintenance', id: 'Inventaris dan Maintenance' },
        purchase_request: { en: 'Procurement Request', id: 'Permintaan Pengadaan' },
        profile: { en: 'Account and Preferences', id: 'Akun dan Preferensi' },
        user_management: { en: 'Role and Permission', id: 'Role dan Permission' },
        settings: { en: 'System Configuration', id: 'Konfigurasi Sistem' },
        kpi_dashboard: { en: 'Performance Monitoring', id: 'Monitoring Kinerja' },
        lcd_screen: { en: 'Live Meeting Display', id: 'Live Meeting Display' }
    };

    var i18n = {
        en: {
            'portal.welcome': 'Welcome',
            'portal.role': 'Role',
            'portal.role_set': 'Role Set',
            'portal.workspace': 'Workspace',
            'portal.personalize': 'Personalize',
            'portal.wib_time': 'WIB Time',
            'metric.open_tickets': 'Open Tickets',
            'metric.it_support': 'IT Support',
            'metric.meetings_today': 'Meetings Today',
            'metric.meeting_room': 'Meeting Room',
            'metric.pending_requests': 'Pending Requests',
            'metric.purchase_request': 'Purchase Request',
            'metric.assets': 'Assets Management',
            'quick_access.title': 'Quick Access',
            'quick_access.customize': 'Customize',
            'quick_access.description': 'Pin the shortcuts you use most and keep them in your preferred order.',
            'approval_center.title': 'Approval Center',
            'approval_center.pending': 'Pending',
            'modules.title': 'Module Navigation',
            'modules.open': 'Open Module',
            'modules.empty': 'No modules are configured for your current role. Please contact administrator.',
            'summary.it_support': 'IT Support Summary',
            'summary.open_tickets': 'Open Tickets',
            'summary.meeting_room': 'Meeting Room Summary',
            'summary.open_bookings': 'Open Bookings',
            'pref.title': 'Portal Personalization',
            'pref.subtitle': 'Arrange module order and choose shortcuts that match your daily workflow.',
            'pref.modules_section': 'Module Order',
            'pref.quick_links_section': 'Quick Access Links',
            'pref.quick_links_hint': 'Select the shortcuts you want to pin in the Quick Access row.',
            'pref.reset': 'Reset',
            'pref.cancel': 'Cancel',
            'pref.save': 'Save Changes',
            'pref.none_selected': 'No quick links selected yet. Open personalization to pin shortcuts.'
        },
        id: {
            'portal.welcome': 'Selamat Datang',
            'portal.role': 'Peran',
            'portal.role_set': 'Daftar Peran',
            'portal.workspace': 'Workspace',
            'portal.personalize': 'Personalisasi',
            'portal.wib_time': 'Waktu WIB',
            'metric.open_tickets': 'Tiket Terbuka',
            'metric.it_support': 'Dukungan TI',
            'metric.meetings_today': 'Rapat Hari Ini',
            'metric.meeting_room': 'Ruang Rapat',
            'metric.pending_requests': 'Permintaan Pending',
            'metric.purchase_request': 'Permintaan Pengadaan',
            'metric.assets': 'Manajemen Aset',
            'quick_access.title': 'Akses Cepat',
            'quick_access.customize': 'Atur',
            'quick_access.description': 'Sematkan pintasan yang paling sering digunakan sesuai urutan kerja Anda.',
            'approval_center.title': 'Pusat Persetujuan',
            'approval_center.pending': 'Pending',
            'modules.title': 'Navigasi Modul',
            'modules.open': 'Buka Modul',
            'modules.empty': 'Belum ada modul untuk peran Anda. Silakan hubungi administrator.',
            'summary.it_support': 'Ringkasan Dukungan TI',
            'summary.open_tickets': 'Tiket Terbuka',
            'summary.meeting_room': 'Ringkasan Ruang Rapat',
            'summary.open_bookings': 'Buka Booking',
            'pref.title': 'Personalisasi Portal',
            'pref.subtitle': 'Atur urutan modul dan pilih shortcut sesuai alur kerja Anda.',
            'pref.modules_section': 'Urutan Modul',
            'pref.quick_links_section': 'Link Akses Cepat',
            'pref.quick_links_hint': 'Pilih shortcut yang ingin ditampilkan pada baris Akses Cepat.',
            'pref.reset': 'Reset',
            'pref.cancel': 'Batal',
            'pref.save': 'Simpan Perubahan',
            'pref.none_selected': 'Belum ada quick link terpilih. Buka personalisasi untuk menambahkan shortcut.'
        }
    };

    var moduleGrid = document.getElementById('portalModuleGrid');
    var quickLinksContainer = document.getElementById('portalQuickLinksContainer');
    var modulePreferenceList = document.getElementById('portalPreferenceModuleList');
    var saveButton = document.getElementById('savePortalPersonalization');
    var resetButton = document.getElementById('resetPortalPersonalization');
    var openButtons = [
        document.getElementById('openPortalPersonalization'),
        document.getElementById('openPortalPersonalizationTop')
    ];
    var languageButtons = document.querySelectorAll('.portal-language-toggle [data-lang]');

    if (!moduleGrid) {
        return;
    }

    var userId = '{{ (int) auth()->id() }}';
    var storageKey = 'itapp.portal.preferences.v1.user.' + userId;

    function moduleKeysInDom() {
        return Array.prototype.map.call(moduleGrid.querySelectorAll('[data-portal-module-key]'), function (node) {
            return node.getAttribute('data-portal-module-key');
        });
    }

    function defaultPreferences() {
        return {
            language: 'en',
            moduleOrder: moduleKeysInDom(),
            hiddenModules: [],
            quickLinkKeys: quickLinkOptions.map(function (option) {
                return option.key;
            }).slice(0, 4)
        };
    }

    function uniqueList(values) {
        var seen = {};
        var result = [];

        values.forEach(function (value) {
            if (!value || seen[value]) {
                return;
            }

            seen[value] = true;
            result.push(value);
        });

        return result;
    }

    function loadPreferences() {
        var defaults = defaultPreferences();

        try {
            var raw = window.localStorage.getItem(storageKey);
            if (!raw) {
                return defaults;
            }

            var parsed = JSON.parse(raw);
            var moduleOrder = uniqueList((parsed.moduleOrder || []).concat(defaults.moduleOrder));
            var hiddenModules = uniqueList(parsed.hiddenModules || []).filter(function (key) {
                return moduleOrder.indexOf(key) !== -1;
            });
            var validQuickLinkKeys = quickLinkOptions.map(function (option) {
                return option.key;
            });
            var quickLinkKeys = uniqueList(parsed.quickLinkKeys || []).filter(function (key) {
                return validQuickLinkKeys.indexOf(key) !== -1;
            });

            if (quickLinkKeys.length === 0) {
                quickLinkKeys = defaults.quickLinkKeys;
            }

            return {
                language: parsed.language === 'id' ? 'id' : 'en',
                moduleOrder: moduleOrder,
                hiddenModules: hiddenModules,
                quickLinkKeys: quickLinkKeys
            };
        } catch (error) {
            return defaults;
        }
    }

    function savePreferences(preferences) {
        window.localStorage.setItem(storageKey, JSON.stringify(preferences));
    }

    function applyModuleOrder(preferences) {
        var nodes = Array.prototype.slice.call(moduleGrid.querySelectorAll('[data-portal-module-key]'));
        var byKey = {};

        nodes.forEach(function (node) {
            byKey[node.getAttribute('data-portal-module-key')] = node;
        });

        preferences.moduleOrder.forEach(function (key) {
            if (byKey[key]) {
                moduleGrid.appendChild(byKey[key]);
            }
        });

        nodes.forEach(function (node) {
            var key = node.getAttribute('data-portal-module-key');
            node.style.display = preferences.hiddenModules.indexOf(key) !== -1 ? 'none' : '';
        });
    }

    function quickLinkLabel(option, language) {
        if (language === 'id') {
            return option.label_id || option.label;
        }

        return option.label;
    }

    function renderQuickLinks(preferences) {
        if (!quickLinksContainer) {
            return;
        }

        quickLinksContainer.innerHTML = '';

        var optionMap = {};
        quickLinkOptions.forEach(function (option) {
            optionMap[option.key] = option;
        });

        var selectedOptions = preferences.quickLinkKeys
            .map(function (key) {
                return optionMap[key] || null;
            })
            .filter(function (option) {
                return !!option;
            });

        if (selectedOptions.length === 0) {
            var emptyState = document.createElement('span');
            emptyState.className = 'text-muted';
            emptyState.textContent = i18n[preferences.language]['pref.none_selected'];
            quickLinksContainer.appendChild(emptyState);
            return;
        }

        selectedOptions.forEach(function (option) {
            var anchor = document.createElement('a');
            anchor.href = option.url || '#';
            anchor.className = 'portal-quick-link-btn';
            anchor.innerHTML = '<i class="fa ' + (option.icon || 'fa-link') + '"></i><span>' + quickLinkLabel(option, preferences.language) + '</span>';
            quickLinksContainer.appendChild(anchor);
        });
    }

    function applyLanguage(preferences) {
        var dictionary = i18n[preferences.language] || i18n.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function (node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-portal-module-title]'), function (node) {
            var key = node.getAttribute('data-portal-module-title');
            if (!key || !moduleLabels[key]) {
                return;
            }

            node.textContent = moduleLabels[key][preferences.language] || moduleLabels[key].en;
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-portal-module-subtitle]'), function (node) {
            var key = node.getAttribute('data-portal-module-subtitle');
            if (!key || !moduleSubtitles[key]) {
                return;
            }

            node.textContent = moduleSubtitles[key][preferences.language] || moduleSubtitles[key].en;
        });

        Array.prototype.forEach.call(document.querySelectorAll('.portal-language-toggle [data-lang]'), function (button) {
            if (button.getAttribute('data-lang') === preferences.language) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('.portal-pref-checkbox [data-link-label-en]'), function (node) {
            var labelEn = node.getAttribute('data-link-label-en') || '';
            var labelId = node.getAttribute('data-link-label-id') || labelEn;
            node.textContent = preferences.language === 'id' ? labelId : labelEn;
        });
    }

    function syncPreferenceModal(preferences) {
        if (!modulePreferenceList) {
            return;
        }

        var rows = Array.prototype.slice.call(modulePreferenceList.querySelectorAll('.portal-pref-module-item'));
        var rowByKey = {};

        rows.forEach(function (row) {
            rowByKey[row.getAttribute('data-module-key')] = row;
        });

        modulePreferenceList.innerHTML = '';

        preferences.moduleOrder.forEach(function (key) {
            var row = rowByKey[key];
            if (!row) {
                return;
            }

            var visibleCheckbox = row.querySelector('.portal-pref-module-visible');
            if (visibleCheckbox) {
                visibleCheckbox.checked = preferences.hiddenModules.indexOf(key) === -1;
            }

            modulePreferenceList.appendChild(row);
        });

        Array.prototype.forEach.call(document.querySelectorAll('.portal-pref-quick-link'), function (checkbox) {
            var key = checkbox.getAttribute('data-link-key');
            checkbox.checked = preferences.quickLinkKeys.indexOf(key) !== -1;
        });
    }

    function collectPreferencesFromModal(previousPreferences) {
        var moduleOrder = [];
        var hiddenModules = [];

        Array.prototype.forEach.call(modulePreferenceList.querySelectorAll('.portal-pref-module-item'), function (row) {
            var key = row.getAttribute('data-module-key');
            var visibleCheckbox = row.querySelector('.portal-pref-module-visible');

            if (!key) {
                return;
            }

            moduleOrder.push(key);

            if (visibleCheckbox && !visibleCheckbox.checked) {
                hiddenModules.push(key);
            }
        });

        var quickLinkKeys = Array.prototype.map.call(document.querySelectorAll('.portal-pref-quick-link:checked'), function (checkbox) {
            return checkbox.getAttribute('data-link-key');
        });

        if (quickLinkKeys.length === 0 && quickLinkOptions.length > 0) {
            quickLinkKeys = [quickLinkOptions[0].key];
        }

        return {
            language: previousPreferences.language,
            moduleOrder: uniqueList(moduleOrder),
            hiddenModules: uniqueList(hiddenModules),
            quickLinkKeys: uniqueList(quickLinkKeys)
        };
    }

    function applyAll(preferences) {
        applyModuleOrder(preferences);
        applyLanguage(preferences);
        renderQuickLinks(preferences);
    }

    var preferences = loadPreferences();
    applyAll(preferences);
    syncPreferenceModal(preferences);

    if (modulePreferenceList) {
        modulePreferenceList.addEventListener('click', function (event) {
            var target = event.target;
            if (!target) {
                return;
            }

            var moveButton = target.closest('.portal-pref-up, .portal-pref-down');
            if (!moveButton) {
                return;
            }

            var item = moveButton.closest('.portal-pref-module-item');
            if (!item) {
                return;
            }

            if (moveButton.classList.contains('portal-pref-up') && item.previousElementSibling) {
                modulePreferenceList.insertBefore(item, item.previousElementSibling);
            }

            if (moveButton.classList.contains('portal-pref-down') && item.nextElementSibling) {
                modulePreferenceList.insertBefore(item.nextElementSibling, item);
            }
        });
    }

    Array.prototype.forEach.call(openButtons, function (button) {
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            syncPreferenceModal(preferences);
            if (window.jQuery) {
                window.jQuery('#portalPersonalizationModal').modal('show');
            }
        });
    });

    Array.prototype.forEach.call(languageButtons, function (button) {
        button.addEventListener('click', function () {
            preferences.language = button.getAttribute('data-lang') === 'id' ? 'id' : 'en';
            savePreferences(preferences);
            applyAll(preferences);
        });
    });

    if (saveButton) {
        saveButton.addEventListener('click', function () {
            preferences = collectPreferencesFromModal(preferences);
            savePreferences(preferences);
            applyAll(preferences);

            if (window.jQuery) {
                window.jQuery('#portalPersonalizationModal').modal('hide');
            }
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            preferences = defaultPreferences();
            savePreferences(preferences);
            applyAll(preferences);
            syncPreferenceModal(preferences);
        });
    }
})();
</script>
@endpush

@endsection
