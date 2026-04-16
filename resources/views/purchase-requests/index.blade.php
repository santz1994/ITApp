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

        .pr-top-controls {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .pr-language-toggle .btn.active {
            background: #1b6ca8;
            border-color: #1b6ca8;
            color: #fff;
        }
</style>
@endpush

@section('main-content')

@component('components.page-header')
    @slot('icon') fa-shopping-cart @endslot
    @slot('title') <span data-i18n="pr.title">Purchase Request Module</span> @endslot
    @slot('subtitle') {{ $subtitle ?? 'Dedicated procurement workspace for request tracking and approvals.' }} @endslot
@endcomponent

<div class="pr-top-controls pull-right" style="margin-top: -52px; margin-bottom: 16px;">
    <div class="btn-group btn-group-xs pr-language-toggle" role="group" aria-label="Purchase Request Language Toggle">
        <button type="button" class="btn btn-default" id="prLanguageEnglish" data-lang="en">EN</button>
        <button type="button" class="btn btn-default" id="prLanguageIndonesian" data-lang="id">ID</button>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $summary['total'] ?? 0 }}</h3>
                <p data-i18n="pr.summary.total">Total Requests</p>
            </div>
            <div class="icon"><i class="fa fa-list"></i></div>
            <a href="{{ route('asset-requests.index') }}" class="small-box-footer"><span data-i18n="pr.action.open_list">Open List</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $summary['pending'] ?? 0 }}</h3>
                <p data-i18n="pr.summary.pending">Pending</p>
            </div>
            <div class="icon"><i class="fa fa-clock-o"></i></div>
            <a href="{{ route('asset-requests.index', ['status' => 'pending']) }}" class="small-box-footer"><span data-i18n="pr.action.pending_queue">Pending Queue</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $summary['approved_month'] ?? 0 }}</h3>
                <p data-i18n="pr.summary.approved_month">Approved (This Month)</p>
            </div>
            <div class="icon"><i class="fa fa-check"></i></div>
            <a href="{{ route('asset-requests.index', ['status' => 'approved']) }}" class="small-box-footer"><span data-i18n="pr.action.approved_list">Approved List</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 pr-summary-card">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $summary['fulfilled_month'] ?? 0 }}</h3>
                <p data-i18n="pr.summary.fulfilled_month">Fulfilled (This Month)</p>
            </div>
            <div class="icon"><i class="fa fa-truck"></i></div>
            <a href="{{ route('asset-requests.index', ['status' => 'fulfilled']) }}" class="small-box-footer"><span data-i18n="pr.action.fulfilled_list">Fulfilled List</span> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="box box-solid pr-panel">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> <span data-i18n="pr.breakdown.title">Status Breakdown</span></h3>
            </div>
            <div class="box-body">
                <div class="pr-breakdown-item">
                    <span data-i18n="pr.breakdown.pending">Pending</span>
                    <span class="label label-warning">{{ $statusBreakdown['pending'] ?? 0 }}</span>
                </div>
                <div class="pr-breakdown-item">
                    <span data-i18n="pr.breakdown.approved">Approved</span>
                    <span class="label label-success">{{ $statusBreakdown['approved'] ?? 0 }}</span>
                </div>
                <div class="pr-breakdown-item">
                    <span data-i18n="pr.breakdown.rejected">Rejected</span>
                    <span class="label label-danger">{{ $statusBreakdown['rejected'] ?? 0 }}</span>
                </div>
                <div class="pr-breakdown-item">
                    <span data-i18n="pr.breakdown.fulfilled">Fulfilled</span>
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
                <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="pr.quick_actions.title">Quick Actions</span></h3>
            </div>
            <div class="box-body">
                <a href="{{ route('asset-requests.create') }}" class="btn btn-success">
                    <i class="fa fa-plus"></i> <span data-i18n="pr.quick_actions.create">Create New Request</span>
                </a>
                <a href="{{ route('asset-requests.index') }}" class="btn btn-default">
                    <i class="fa fa-list"></i> <span data-i18n="pr.quick_actions.open_list">Open Request List</span>
                </a>
                @if(!empty($canApprove))
                    <a href="{{ route('asset-requests.index', ['status' => 'pending']) }}" class="btn btn-warning">
                        <i class="fa fa-check-square-o"></i> <span data-i18n="pr.quick_actions.review_approvals">Review Pending Approvals</span>
                    </a>
                @endif
                <div style="margin-top: 14px;" class="text-muted" data-i18n="pr.quick_actions.hint">
                    Procurement records in this module are powered by existing asset request flow for backward compatibility.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box box-default pr-table">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> <span data-i18n="pr.table.title">Recent Purchase Requests</span></h3>
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
                            <th data-i18n="pr.table.request_number">Request Number</th>
                            <th data-i18n="pr.table.requester">Requester</th>
                            <th data-i18n="pr.table.asset_type">Asset Type</th>
                            <th data-i18n="pr.table.priority">Priority</th>
                            <th data-i18n="pr.table.status">Status</th>
                            <th data-i18n="pr.table.created_wib">Created (WIB)</th>
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
                <i class="fa fa-inbox"></i> <span data-i18n="pr.table.empty">No purchase request records available.</span>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    var translations = {
        en: {
            'pr.title': 'Purchase Request Module',
            'pr.summary.total': 'Total Requests',
            'pr.summary.pending': 'Pending',
            'pr.summary.approved_month': 'Approved (This Month)',
            'pr.summary.fulfilled_month': 'Fulfilled (This Month)',
            'pr.action.open_list': 'Open List',
            'pr.action.pending_queue': 'Pending Queue',
            'pr.action.approved_list': 'Approved List',
            'pr.action.fulfilled_list': 'Fulfilled List',
            'pr.breakdown.title': 'Status Breakdown',
            'pr.breakdown.pending': 'Pending',
            'pr.breakdown.approved': 'Approved',
            'pr.breakdown.rejected': 'Rejected',
            'pr.breakdown.fulfilled': 'Fulfilled',
            'pr.quick_actions.title': 'Quick Actions',
            'pr.quick_actions.create': 'Create New Request',
            'pr.quick_actions.open_list': 'Open Request List',
            'pr.quick_actions.review_approvals': ['Review Pending', 'Approvals'].join(' '),
            'pr.quick_actions.hint': 'Procurement records in this module are powered by existing asset request flow for backward compatibility.',
            'pr.table.title': 'Recent Purchase Requests',
            'pr.table.request_number': 'Request Number',
            'pr.table.requester': 'Requester',
            'pr.table.asset_type': 'Asset Type',
            'pr.table.priority': 'Priority',
            'pr.table.status': 'Status',
            'pr.table.created_wib': 'Created (WIB)',
            'pr.table.empty': 'No purchase request records available.'
        },
        id: {
            'pr.title': 'Modul Permintaan Pengadaan',
            'pr.summary.total': 'Total Permintaan',
            'pr.summary.pending': 'Menunggu',
            'pr.summary.approved_month': 'Disetujui (Bulan Ini)',
            'pr.summary.fulfilled_month': 'Dipenuhi (Bulan Ini)',
            'pr.action.open_list': 'Buka Daftar',
            'pr.action.pending_queue': 'Antrian Pending',
            'pr.action.approved_list': 'Daftar Disetujui',
            'pr.action.fulfilled_list': 'Daftar Terpenuhi',
            'pr.breakdown.title': 'Ringkasan Status',
            'pr.breakdown.pending': 'Menunggu',
            'pr.breakdown.approved': 'Disetujui',
            'pr.breakdown.rejected': 'Ditolak',
            'pr.breakdown.fulfilled': 'Dipenuhi',
            'pr.quick_actions.title': 'Aksi Cepat',
            'pr.quick_actions.create': 'Buat Permintaan Baru',
            'pr.quick_actions.open_list': 'Buka Daftar Permintaan',
            'pr.quick_actions.review_approvals': 'Tinjau Persetujuan Pending',
            'pr.quick_actions.hint': 'Data pengadaan pada modul ini masih menggunakan alur asset request untuk menjaga kompatibilitas sebelumnya.',
            'pr.table.title': 'Permintaan Pengadaan Terbaru',
            'pr.table.request_number': 'Nomor Permintaan',
            'pr.table.requester': 'Pemohon',
            'pr.table.asset_type': 'Tipe Aset',
            'pr.table.priority': 'Prioritas',
            'pr.table.status': 'Status',
            'pr.table.created_wib': 'Dibuat (WIB)',
            'pr.table.empty': 'Belum ada data permintaan pengadaan.'
        }
    };

    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('prLanguageEnglish');
    var indonesianButton = document.getElementById('prLanguageIndonesian');

    if (!englishButton || !indonesianButton) {
        return;
    }

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

    function applyLanguage(language) {
        var locale = language === 'id' ? 'id' : 'en';
        var dictionary = translations[locale];

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function (node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        englishButton.classList.toggle('active', locale === 'en');
        indonesianButton.classList.toggle('active', locale === 'id');
    }

    englishButton.addEventListener('click', function () {
        saveLanguage('en');
        applyLanguage('en');
    });

    indonesianButton.addEventListener('click', function () {
        saveLanguage('id');
        applyLanguage('id');
    });

    applyLanguage(getLanguage());
})();
</script>
@endpush

@endsection
