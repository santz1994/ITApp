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

    @media (max-width: 767px) {
        .portal-welcome-card {
            padding: 16px;
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
            <div class="meta" style="margin-top: 6px;">
                Bahasa Indonesia + English navigation enabled for core modules.
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
            <a href="{{ route('tickets.index') }}" class="small-box-footer">IT Support <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $metrics['meetings_today'] ?? 0 }}</h3>
                <p>Meetings Today</p>
            </div>
            <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
            <a href="{{ route('meeting-room-bookings.index') }}" class="small-box-footer">Meeting Room <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $metrics['pending_requests'] ?? 0 }}</h3>
                <p>Pending Requests</p>
            </div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            <a href="{{ route('asset-requests.index') }}" class="small-box-footer">Purchase Request <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 portal-metric">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $metrics['total_assets'] ?? 0 }}</h3>
                <p>{{ user_has_role(auth()->user(), 'user') ? 'My Assets' : 'Total Assets' }}</p>
            </div>
            <div class="icon"><i class="fa fa-cubes"></i></div>
            <a href="{{ route('assets.index') }}" class="small-box-footer">Assets Management <i class="fa fa-arrow-circle-right"></i></a>
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
        <h3 class="box-title"><i class="fa fa-list"></i> Recent Tickets Snapshot</h3>
        <div class="box-tools pull-right">
            <a href="{{ route('tickets.index') }}" class="btn btn-default btn-sm">
                <i class="fa fa-external-link"></i> Full Tickets
            </a>
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
                            <th>Priority</th>
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
                                <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 50) }}</td>
                                <td>
                                    <span class="label label-default">
                                        {{ optional($ticket->ticket_status)->status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="label label-info">
                                        {{ optional($ticket->ticket_priority)->priority ?? optional($ticket->ticket_priority)->name ?? 'N/A' }}
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

@endsection
