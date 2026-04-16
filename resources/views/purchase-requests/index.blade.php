@extends('layouts.app')

@push('styles')
<style>
    .pr-summary-card .small-box {
        border-radius: 10px;
        min-height: 126px;
    }

    .pr-panel {
        border-radius: 10px;
        border: 1px solid #e7edf4;
    }

    .pr-breakdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px dashed #edf2f7;
        padding: 10px 0;
    }

    .pr-breakdown-item:last-child {
        border-bottom: none;
    }

    .pr-table .label {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .25px;
    }
</style>
@endpush

@section('main-content')

@component('components.page-header')
    @slot('icon') fa-shopping-cart @endslot
    @slot('title') Purchase Request Module @endslot
    @slot('subtitle') {{ $subtitle ?? 'Dedicated procurement workspace for request tracking and approvals.' }} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $summary['total'] ?? 0 }}</h3>
                <p>Total Requests</p>
            </div>
            <div class="icon"><i class="fa fa-list"></i></div>
            <a href="{{ route('asset-requests.index') }}" class="small-box-footer">Open List <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $summary['pending'] ?? 0 }}</h3>
                <p>Pending</p>
            </div>
            <div class="icon"><i class="fa fa-clock-o"></i></div>
            <a href="{{ route('asset-requests.index', ['status' => 'pending']) }}" class="small-box-footer">Pending Queue <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $summary['approved_month'] ?? 0 }}</h3>
                <p>Approved (This Month)</p>
            </div>
            <div class="icon"><i class="fa fa-check"></i></div>
            <a href="{{ route('asset-requests.index', ['status' => 'approved']) }}" class="small-box-footer">Approved List <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $summary['fulfilled_month'] ?? 0 }}</h3>
                <p>Fulfilled (This Month)</p>
            </div>
            <div class="icon"><i class="fa fa-truck"></i></div>
            <a href="{{ route('asset-requests.index', ['status' => 'fulfilled']) }}" class="small-box-footer">Fulfilled List <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="box box-solid pr-panel">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> Status Breakdown</h3>
            </div>
            <div class="box-body">
                <div class="pr-breakdown-item">
                    <span>Pending</span>
                    <span class="label label-warning">{{ $statusBreakdown['pending'] ?? 0 }}</span>
                </div>
                <div class="pr-breakdown-item">
                    <span>Approved</span>
                    <span class="label label-success">{{ $statusBreakdown['approved'] ?? 0 }}</span>
                </div>
                <div class="pr-breakdown-item">
                    <span>Rejected</span>
                    <span class="label label-danger">{{ $statusBreakdown['rejected'] ?? 0 }}</span>
                </div>
                <div class="pr-breakdown-item">
                    <span>Fulfilled</span>
                    <span class="label label-primary">{{ $statusBreakdown['fulfilled'] ?? 0 }}</span>
                </div>
                <div style="margin-top: 12px;" class="text-muted">
                    <i class="fa fa-clock-o"></i> {{ ($jakartaNow ?? now('Asia/Jakarta'))->format('d M Y H:i') }} WIB
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-solid pr-panel">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="box-body">
                <a href="{{ route('asset-requests.create') }}" class="btn btn-success">
                    <i class="fa fa-plus"></i> Create New Request
                </a>
                <a href="{{ route('asset-requests.index') }}" class="btn btn-default">
                    <i class="fa fa-list"></i> Open Request List
                </a>
                @if(!empty($canApprove))
                    <a href="{{ route('asset-requests.index', ['status' => 'pending']) }}" class="btn btn-warning">
                        <i class="fa fa-check-square-o"></i> Review Pending Approvals
                    </a>
                @endif
                <div style="margin-top: 14px;" class="text-muted">
                    Procurement records in this module are powered by existing asset request flow for backward compatibility.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box box-default pr-table">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> Recent Purchase Requests</h3>
    </div>
    <div class="box-body no-padding">
        @if(isset($recentRequests) && $recentRequests->count() > 0)
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
                            <th>Request Number</th>
                            <th>Requester</th>
                            <th>Asset Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created (WIB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRequests as $requestItem)
                            @php
                                $priority = strtolower((string) ($requestItem->priority ?? 'medium'));
                                $status = strtolower((string) ($requestItem->status ?? 'pending'));
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('asset-requests.show', $requestItem->id) }}">
                                        <strong>{{ $requestItem->request_number ?? ('AR-' . $requestItem->id) }}</strong>
                                    </a>
                                </td>
                                <td>{{ optional($requestItem->requestedBy)->name ?? 'N/A' }}</td>
                                <td>{{ optional($requestItem->assetType)->type_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="label {{ $priorityClass[$priority] ?? 'label-default' }}">{{ ucfirst($priority) }}</span>
                                </td>
                                <td>
                                    <span class="label {{ $statusClass[$status] ?? 'label-default' }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ optional($requestItem->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 20px;" class="text-muted text-center">
                <i class="fa fa-inbox"></i> No purchase request records available.
            </div>
        @endif
    </div>
</div>

@endsection
