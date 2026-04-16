@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

@php $pageTitle = $pageTitle ?? ('Asset Details - ' . ($asset->asset_tag ?? '')); @endphp

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Asset Details',
    'subtitle' => $asset->asset_tag ?? 'Asset Information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Assets', 'url' => route('assets.index')],
        ['label' => $asset->asset_tag ?? 'Details']
    ]
])

<div class="pull-right" style="margin-top: -52px; margin-bottom: 16px; margin-right: 15px;">
    <div class="btn-group btn-group-xs" role="group" aria-label="Asset Show Language Toggle">
        <button type="button" class="btn btn-default" id="assetShowLanguageEnglish" data-lang="en">EN</button>
        <button type="button" class="btn btn-default" id="assetShowLanguageIndonesian" data-lang="id">ID</button>
    </div>
</div>
<div class="clearfix"></div>

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
            {{-- Asset Information Card --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-laptop"></i> {{ $pageTitle }}
                        @if($asset->status)
                            @php
                                $statusName = strtolower($asset->status->status ?? '');
                                $badgeColor = '#6c757d';
                                if(str_contains($statusName, 'pending') || str_contains($statusName, 'rusak') || str_contains($statusName, 'broken')) {
                                    $badgeColor = '#d9534f'; // red
                                } elseif(str_contains($statusName, 'ready') || str_contains($statusName, 'available') || str_contains($statusName, 'siap')) {
                                    $badgeColor = '#5cb85c'; // green
                                } elseif(str_contains($statusName, 'in use') || str_contains($statusName, 'deployed') || str_contains($statusName, 'terpakai')) {
                                    $badgeColor = '#5bc0de'; // blue
                                } elseif(str_contains($statusName, 'maintenance') || str_contains($statusName, 'repair')) {
                                    $badgeColor = '#f0ad4e'; // yellow
                                }
                            @endphp
                            <span class="label" style="background-color: {{ $badgeColor }}; margin-left: 10px;">
                                {{ $asset->status->status }}
                            </span>
                        @endif
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('assets.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> <span data-i18n="assets.show.action.back">Back</span>
                        </a>
                        <a href="{{ url('assets/' . $asset->id . '/print') }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="fa fa-print"></i> <span data-i18n="assets.show.action.print">Print</span>
                        </a>
                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-edit"></i> <span data-i18n="assets.show.action.edit">Edit</span>
                        </a>
                    </div>
                </div>
            
                <div class="box-body">
                    {{-- Nav tabs --}}
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#basic-info" aria-controls="basic-info" role="tab" data-toggle="tab">
                                <i class="fa fa-info-circle"></i> <span data-i18n="assets.show.tab.basic">Basic Info</span>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#specifications" aria-controls="specifications" role="tab" data-toggle="tab">
                                <i class="fa fa-cogs"></i> <span data-i18n="assets.show.tab.specifications">Specifications</span>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#network-info" aria-controls="network-info" role="tab" data-toggle="tab">
                                <i class="fa fa-network-wired"></i> <span data-i18n="assets.show.tab.network">Network</span>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tickets" aria-controls="tickets" role="tab" data-toggle="tab">
                                <i class="fa fa-ticket"></i> <span data-i18n="assets.show.tab.tickets">Tickets</span>
                                @if($recentIssues->count() > 0)
                                    <span class="badge bg-red">{{ $recentIssues->count() }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    {{-- Tab panes --}}
                    <div class="tab-content" style="padding-top: 20px;">
                        {{-- Basic Information Tab --}}
                        <div role="tabpanel" class="tab-pane active" id="basic-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4><i class="fa fa-info-circle text-primary"></i> <span data-i18n="assets.show.section.basic">Basic Information</span></h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <tr>
                                                <th style="width: 150px;">Asset Tag:</th>
                                                <td><strong>{{ $asset->asset_tag }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Model:</th>
                                                <td>{{ optional($asset->model)->asset_model ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Serial Number:</th>
                                                <td>{{ $asset->serial_number ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    @if($asset->status)
                                                        @php
                                                            $statusName = strtolower($asset->status->status ?? '');
                                                            $badgeColor = '#6c757d';
                                                            if(str_contains($statusName, 'pending') || str_contains($statusName, 'rusak') || str_contains($statusName, 'broken')) {
                                                                $badgeColor = '#d9534f';
                                                            } elseif(str_contains($statusName, 'ready') || str_contains($statusName, 'available') || str_contains($statusName, 'siap')) {
                                                                $badgeColor = '#5cb85c';
                                                            } elseif(str_contains($statusName, 'in use') || str_contains($statusName, 'deployed') || str_contains($statusName, 'terpakai')) {
                                                                $badgeColor = '#5bc0de';
                                                            } elseif(str_contains($statusName, 'maintenance') || str_contains($statusName, 'repair')) {
                                                                $badgeColor = '#f0ad4e';
                                                            }
                                                        @endphp
                                                        <span class="label" style="background-color: {{ $badgeColor }}">
                                                            {{ $asset->status->status }}
                                                        </span>
                                                    @else
                                                        <span class="label label-default">Unknown</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Location:</th>
                                                <td>
                                                    <i class="fa fa-map-marker"></i> {{ $asset->movement && $asset->movement->location ? $asset->movement->location->location_name : 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Division:</th>
                                                <td>
                                                    <i class="fa fa-building"></i> {{ $asset->division ? ($asset->division->name ?? $asset->division->division_name) : 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Assigned To:</th>
                                                <td>
                                                    <i class="fa fa-user"></i> {{ $asset->assignedTo ? $asset->assignedTo->name : 'Not Assigned' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h4><i class="fa fa-calendar text-info"></i> <span data-i18n="assets.show.section.purchase">Purchase & Warranty</span></h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            @if($asset->purchase_date)
                                                <tr>
                                                    <th style="width: 150px;">Purchase Date:</th>
                                                    <td>{{ $asset->purchase_date->format('d F Y') }}</td>
                                                </tr>
                                            @endif
                                            
                                            @if(optional($asset->supplier)->name)
                                                <tr>
                                                    <th>Supplier:</th>
                                                    <td>{{ $asset->supplier->name }}</td>
                                                </tr>
                                            @endif
                                            
                                            @if($asset->warranty_months)
                                                <tr>
                                                    <th>Warranty Months:</th>
                                                    <td>{{ $asset->warranty_months }} months</td>
                                                </tr>
                                                @php
                                                    $warrantyEnd = $asset->purchase_date ? $asset->purchase_date->addMonths($asset->warranty_months) : null;
                                                    $isWarrantyActive = $warrantyEnd && $warrantyEnd->isFuture();
                                                @endphp
                                                @if($warrantyEnd)
                                                    <tr>
                                                        <th>Warranty Expires:</th>
                                                        <td>
                                                            {{ $warrantyEnd->format('d F Y') }}
                                                            @if($isWarrantyActive)
                                                                <span class="label label-success">Active</span>
                                                            @else
                                                                <span class="label label-danger">Expired</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if($asset->notes)
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4><i class="fa fa-sticky-note text-warning"></i> Notes</h4>
                                        <div class="well well-sm">
                                            {!! nl2br(e($asset->notes)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Specifications Tab --}}
                        <div role="tabpanel" class="tab-pane" id="specifications">
                            @if($asset->pcspec)
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fa fa-microchip text-primary"></i> Hardware Specifications</h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <tr>
                                                    <th style="width: 150px;">Processor:</th>
                                                    <td>{{ $asset->pcspec->processor ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>RAM:</th>
                                                    <td>{{ $asset->pcspec->ram ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Storage:</th>
                                                    <td>{{ $asset->pcspec->storage ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Graphics:</th>
                                                    <td>{{ $asset->pcspec->graphics ?? 'N/A' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h4><i class="fa fa-desktop text-info"></i> Display & Software</h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <tr>
                                                    <th style="width: 150px;">Display:</th>
                                                    <td>{{ $asset->pcspec->display ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Operating System:</th>
                                                    <td>{{ $asset->pcspec->os ?? 'N/A' }}</td>
                                                </tr>
                                                @if($asset->pcspec->additional_specs)
                                                    <tr>
                                                        <th>Additional:</th>
                                                        <td>{{ $asset->pcspec->additional_specs }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No specifications available for this asset.
                                    <a href="{{ route('assets.edit', $asset->id) }}" class="alert-link">Add specifications</a>
                                </div>
                            @endif
                        </div>

                        {{-- Network Information Tab --}}
                        <div role="tabpanel" class="tab-pane" id="network-info">
                            @if($asset->ip_address || $asset->mac_address)
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fa fa-network-wired text-success"></i> Network Information</h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                @if($asset->ip_address)
                                                    <tr>
                                                        <th style="width: 150px;">IP Address:</th>
                                                        <td><code>{{ $asset->ip_address }}</code></td>
                                                    </tr>
                                                @endif
                                                @if($asset->mac_address)
                                                    <tr>
                                                        <th>MAC Address:</th>
                                                        <td><code>{{ $asset->mac_address }}</code></td>
                                                    </tr>
                                                @endif
                                                @if($asset->computer_name)
                                                    <tr>
                                                        <th>Computer Name:</th>
                                                        <td><code>{{ $asset->computer_name }}</code></td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No network information available for this asset.
                                </div>
                            @endif
                        </div>

                        {{-- Tickets Tab --}}
                        <div role="tabpanel" class="tab-pane" id="tickets">
                            @if($ticketHistory->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr class="bg-light-blue">
                                                <th>Ticket Code</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ticketHistory->sortByDesc('created_at') as $ticket)
                                                <tr>
                                                    <td><strong>{{ $ticket->ticket_code }}</strong></td>
                                                    <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 50) }}</td>
                                                    <td>
                                                        @if($ticket->ticket_status)
                                                            @php
                                                                $statusName = strtolower($ticket->ticket_status->name ?? '');
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
                                                    <td>
                                                        <span class="label label-{{ optional($ticket->ticket_priority)->color ?? 'default' }}">
                                                            {{ optional($ticket->ticket_priority)->priority ?? 'N/A' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fa fa-check-circle"></i> No tickets found for this asset. Great job!
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-3">
        {{-- Quick Actions --}}
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="assets.show.quick_actions.title">Quick Actions</span></h3>
            </div>
            <div class="box-body">
                <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-primary btn-block margin-bottom">
                    <i class="fa fa-edit"></i> <span data-i18n="assets.show.action.edit_asset">Edit Asset</span>
                </a>
                <a href="{{ route('tickets.create', ['asset_id' => $asset->id]) }}" class="btn btn-success btn-block margin-bottom">
                    <i class="fa fa-plus"></i> <span data-i18n="assets.show.action.create_ticket">Create Ticket</span>
                </a>
                <a href="{{ url('assets/' . $asset->id . '/print') }}" class="btn btn-info btn-block margin-bottom" target="_blank">
                    <i class="fa fa-print"></i> <span data-i18n="assets.show.action.print_label">Print Label</span>
                </a>
                @if($asset->qr_code)
                    <button class="btn btn-default btn-block margin-bottom" onclick="showQRCode()">
                        <i class="fa fa-qrcode"></i> <span data-i18n="assets.show.action.view_qr">View QR Code</span>
                    </button>
                @endif
                <a href="{{ route('assets.index') }}" class="btn btn-default btn-block">
                    <i class="fa fa-arrow-left"></i> <span data-i18n="assets.show.action.back_to_assets">Back to Assets</span>
                </a>
            </div>
        </div>

        {{-- Asset Statistics --}}
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistics</h3>
            </div>
            <div class="box-body">
                {{-- Total Tickets --}}
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-ticket"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Tickets</span>
                        <span class="info-box-number">{{ $ticketHistory->count() }}</span>
                    </div>
                </div>

                {{-- Recent Issues --}}
                <div class="info-box bg-{{ $recentIssues->count() > 0 ? 'red' : 'green' }}">
                    <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Recent Issues (30 days)</span>
                        <span class="info-box-number">{{ $recentIssues->count() }}</span>
                    </div>
                </div>

                {{-- Asset Age --}}
                @if($asset->purchase_date)
                    @php
                        $assetAge = $asset->purchase_date->diff(now());
                        $years = $assetAge->y;
                        $months = $assetAge->m;
                    @endphp
                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Asset Age</span>
                            <span class="info-box-number">{{ $years }}y {{ $months }}m</span>
                        </div>
                    </div>
                @endif

                {{-- Warranty Status --}}
                @if($asset->warranty_months && $asset->purchase_date)
                    @php
                        $warrantyEnd = $asset->purchase_date->copy()->addMonths($asset->warranty_months);
                        $isWarrantyActive = $warrantyEnd->isFuture();
                    @endphp
                    <div class="info-box bg-{{ $isWarrantyActive ? 'green' : 'red' }}">
                        <span class="info-box-icon"><i class="fa fa-shield"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Warranty</span>
                            <span class="info-box-number">{{ $isWarrantyActive ? 'Active' : 'Expired' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Related Links --}}
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-link"></i> Related Links</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    @if($asset->model)
                        <li class="margin-bottom">
                            <i class="fa fa-laptop text-blue"></i>
                            <a href="{{ route('models.show', $asset->model->id) }}">View Model Details</a>
                        </li>
                    @endif
                    @if($asset->location)
                        <li class="margin-bottom">
                            <i class="fa fa-map-marker text-green"></i>
                            <a href="{{ route('locations.show', $asset->location->id) }}">View Location</a>
                        </li>
                    @endif
                    @if($asset->division)
                        <li class="margin-bottom">
                            <i class="fa fa-building text-orange"></i>
                            <a href="{{ route('divisions.show', $asset->division->id) }}">View Division</a>
                        </li>
                    @endif
                    @if($asset->supplier)
                        <li class="margin-bottom">
                            <i class="fa fa-truck text-purple"></i>
                            <a href="{{ route('suppliers.show', $asset->supplier->id) }}">View Supplier</a>
                        </li>
                    @endif
                    <li class="margin-bottom">
                        <i class="fa fa-history text-gray"></i>
                        <a href="{{ route('assets.history', $asset->id) }}">View Asset History</a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Information Box --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
            </div>
            <div class="box-body">
                <p><strong>About This Asset:</strong></p>
                <ul class="list-unstyled">
                    <li><i class="fa fa-check text-green"></i> Asset details and specifications</li>
                    <li><i class="fa fa-check text-green"></i> Maintenance and ticket history</li>
                    <li><i class="fa fa-check text-green"></i> Network information</li>
                    <li><i class="fa fa-check text-green"></i> Warranty tracking</li>
                </ul>
                <hr>
                <p class="text-muted small">
                    <i class="fa fa-lightbulb-o"></i> <strong>Tip:</strong> Use the tabs above to navigate between different asset information sections.
                </p>
            </div>
        </div>
    </div>{{-- End row --}}
</div>{{-- End container-fluid --}}

{{-- QR Code Modal --}}
@if($asset->qr_code)
    <div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title">QR Code - {{ $asset->asset_tag }}</h4>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode"></div>
                    <p class="text-muted">{{ $asset->qr_code }}</p>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<script>
(function() {
    var translations = {
        en: {
            'assets.show.action.back': 'Back',
            'assets.show.action.print': 'Print',
            'assets.show.action.edit': 'Edit',
            'assets.show.tab.basic': 'Basic Info',
            'assets.show.tab.specifications': 'Specifications',
            'assets.show.tab.network': 'Network',
            'assets.show.tab.tickets': 'Tickets',
            'assets.show.section.basic': 'Basic Information',
            'assets.show.section.purchase': 'Purchase & Warranty',
            'assets.show.quick_actions.title': 'Quick Actions',
            'assets.show.action.edit_asset': 'Edit Asset',
            'assets.show.action.create_ticket': 'Create Ticket',
            'assets.show.action.print_label': 'Print Label',
            'assets.show.action.view_qr': 'View QR Code',
            'assets.show.action.back_to_assets': 'Back to Assets'
        },
        id: {
            'assets.show.action.back': 'Kembali',
            'assets.show.action.print': 'Cetak',
            'assets.show.action.edit': 'Ubah',
            'assets.show.tab.basic': 'Info Dasar',
            'assets.show.tab.specifications': 'Spesifikasi',
            'assets.show.tab.network': 'Jaringan',
            'assets.show.tab.tickets': 'Tiket',
            'assets.show.section.basic': 'Informasi Dasar',
            'assets.show.section.purchase': 'Pembelian & Garansi',
            'assets.show.quick_actions.title': 'Aksi Cepat',
            'assets.show.action.edit_asset': 'Ubah Aset',
            'assets.show.action.create_ticket': 'Buat Tiket',
            'assets.show.action.print_label': 'Cetak Label',
            'assets.show.action.view_qr': 'Lihat QR Code',
            'assets.show.action.back_to_assets': 'Kembali ke Aset'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('assetShowLanguageEnglish');
    var indonesianButton = document.getElementById('assetShowLanguageIndonesian');

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

        if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }
    }

    window.assetShowLabel = getLabel;

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

@if($asset->qr_code)
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        function showQRCode() {
            $('#qrCodeModal').modal('show');
            const qrCodeDiv = document.getElementById('qrcode');
            qrCodeDiv.innerHTML = '';
            // Generate QR code with asset view URL
            const assetUrl = '{{ route('assets.show', $asset->id) }}';
            QRCode.toCanvas(qrCodeDiv, assetUrl, { width: 200, height: 200 }, function (error) { if (error) console.error(error); });
        }
    </script>
@endif
@endsection
