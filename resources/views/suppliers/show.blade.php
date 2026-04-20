@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Supplier Details',
    'subtitle' => $supplier->name,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
        ['label' => 'Details']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('suppliers.edit', $supplier->id).'" class="btn btn-warning">
                <i class="fa fa-edit"></i> <span class="hidden-xs">Edit</span>
            </a>
            <a href="'.route('suppliers.index').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
            </a>
        </div>
    '
])

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'supplierShowLanguageEnglish',
    'indonesianButtonId' => 'supplierShowLanguageIndonesian',
    'ariaLabel' => 'Supplier Show Language Toggle',
])

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            {{-- Supplier Information --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-building"></i> <span data-i18n="suppliers.show.section.info">Supplier Information</span></h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt data-i18n="suppliers.show.label.name">Supplier Name:</dt>
                        <dd><strong>{{ $supplier->name }}</strong></dd>

                        <dt data-i18n="suppliers.show.label.contact">Contact Person:</dt>
                        <dd>{{ $supplier->contact_person ?? '-' }}</dd>

                        <dt data-i18n="suppliers.show.label.email">Email:</dt>
                        <dd>{{ $supplier->email ?? '-' }}</dd>

                        <dt data-i18n="suppliers.show.label.phone">Phone:</dt>
                        <dd>{{ $supplier->phone ?? '-' }}</dd>

                        <dt data-i18n="suppliers.show.label.address">Address:</dt>
                        <dd>{{ $supplier->address ?? '-' }}</dd>

                        <dt data-i18n="suppliers.show.label.website">Website:</dt>
                        <dd>
                            @if($supplier->website)
                                <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                            @else
                                -
                            @endif
                        </dd>

                        <dt data-i18n="suppliers.show.label.created">Created:</dt>
                        <dd>{{ $supplier->created_at ? $supplier->created_at->format('d M Y H:i') : '-' }}</dd>

                        <dt data-i18n="suppliers.show.label.updated">Last Updated:</dt>
                        <dd>{{ $supplier->updated_at ? $supplier->updated_at->format('d M Y H:i') : '-' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bar-chart"></i> <span data-i18n="suppliers.show.section.stats">Statistics</span></h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-laptop"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-i18n="suppliers.show.stats.assets">Total Assets</span>
                            <span class="info-box-number">{{ $assets->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-file-text"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-i18n="suppliers.show.stats.invoices">Total Invoices</span>
                            <span class="info-box-number">{{ $invoices->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-i18n="suppliers.show.stats.value">Total Value</span>
                            <span class="info-box-number">R{{ number_format($assets->sum('purchase_cost'), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Assets from This Supplier --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> <span data-i18n="suppliers.show.section.assets">Assets from This Supplier</span></h3>
                    <div class="box-tools">
                        <span class="label label-primary">{{ $assets->count() }} <span data-i18n="suppliers.show.count.assets">Assets</span></span>
                    </div>
                </div>
                <div class="box-body">
                    @if($assets->count() > 0)
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th data-i18n="suppliers.show.table.asset_tag">Asset Tag</th>
                                    <th data-i18n="suppliers.show.table.model">Model</th>
                                    <th data-i18n="suppliers.show.table.serial">Serial Number</th>
                                    <th data-i18n="suppliers.show.table.status">Status</th>
                                    <th data-i18n="suppliers.show.table.purchase_cost">Purchase Cost</th>
                                    <th data-i18n="suppliers.show.table.actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                <tr>
                                    <td><strong>{{ $asset->asset_tag }}</strong></td>
                                    <td>{{ optional($asset->assetModel)->asset_model ?? '-' }}</td>
                                    <td>{{ $asset->serial_number ?? '-' }}</td>
                                    <td>
                                        <span class="label label-{{ $asset->status == 'deployed' ? 'success' : ($asset->status == 'ready' ? 'info' : ($asset->status == 'repairs' ? 'warning' : 'default')) }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                    <td>R{{ number_format($asset->purchase_cost ?? 0, 2) }}</td>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-xs btn-info" data-i18n-title="suppliers.show.action.view_title" title="View">
                                            <i class="fa fa-eye"></i> <span data-i18n="suppliers.show.action.view">View</span>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <span data-i18n="suppliers.show.empty.assets">No assets have been purchased from this supplier yet.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Invoices from This Supplier --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text"></i> <span data-i18n="suppliers.show.section.invoices">Invoices from This Supplier</span></h3>
                    <div class="box-tools">
                        <span class="label label-success">{{ $invoices->count() }} <span data-i18n="suppliers.show.count.invoices">Invoices</span></span>
                    </div>
                </div>
                <div class="box-body">
                    @if($invoices->count() > 0)
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th data-i18n="suppliers.show.table.invoice_number">Invoice Number</th>
                                    <th data-i18n="suppliers.show.table.date">Date</th>
                                    <th data-i18n="suppliers.show.table.amount">Amount</th>
                                    <th data-i18n="suppliers.show.table.invoice_status">Status</th>
                                    <th data-i18n="suppliers.show.table.actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td><strong>{{ $invoice->invoice_number ?? '-' }}</strong></td>
                                    <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '-' }}</td>
                                    <td>R{{ number_format($invoice->amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="label label-{{ $invoice->status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($invoice->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-xs btn-info" data-i18n-title="suppliers.show.action.view_title" title="View">
                                            <i class="fa fa-eye"></i> <span data-i18n="suppliers.show.action.view">View</span>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <span data-i18n="suppliers.show.empty.invoices">No invoices recorded for this supplier yet.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function() {
        var translations = {
            en: {
                'suppliers.show.section.info': 'Supplier Information',
                'suppliers.show.label.name': 'Supplier Name:',
                'suppliers.show.label.contact': 'Contact Person:',
                'suppliers.show.label.email': 'Email:',
                'suppliers.show.label.phone': 'Phone:',
                'suppliers.show.label.address': 'Address:',
                'suppliers.show.label.website': 'Website:',
                'suppliers.show.label.created': 'Created:',
                'suppliers.show.label.updated': 'Last Updated:',
                'suppliers.show.section.stats': 'Statistics',
                'suppliers.show.stats.assets': 'Total Assets',
                'suppliers.show.stats.invoices': 'Total Invoices',
                'suppliers.show.stats.value': 'Total Value',
                'suppliers.show.section.assets': 'Assets from This Supplier',
                'suppliers.show.count.assets': 'Assets',
                'suppliers.show.table.asset_tag': 'Asset Tag',
                'suppliers.show.table.model': 'Model',
                'suppliers.show.table.serial': 'Serial Number',
                'suppliers.show.table.status': 'Status',
                'suppliers.show.table.purchase_cost': 'Purchase Cost',
                'suppliers.show.table.actions': 'Actions',
                'suppliers.show.action.view_title': 'View',
                'suppliers.show.action.view': 'View',
                'suppliers.show.empty.assets': 'No assets have been purchased from this supplier yet.',
                'suppliers.show.section.invoices': 'Invoices from This Supplier',
                'suppliers.show.count.invoices': 'Invoices',
                'suppliers.show.table.invoice_number': 'Invoice Number',
                'suppliers.show.table.date': 'Date',
                'suppliers.show.table.amount': 'Amount',
                'suppliers.show.table.invoice_status': 'Status',
                'suppliers.show.empty.invoices': 'No invoices recorded for this supplier yet.'
            },
            id: {
                'suppliers.show.section.info': 'Informasi Supplier',
                'suppliers.show.label.name': 'Nama Supplier:',
                'suppliers.show.label.contact': 'Kontak Person:',
                'suppliers.show.label.email': 'Email:',
                'suppliers.show.label.phone': 'Telepon:',
                'suppliers.show.label.address': 'Alamat:',
                'suppliers.show.label.website': 'Website:',
                'suppliers.show.label.created': 'Dibuat:',
                'suppliers.show.label.updated': 'Terakhir Diperbarui:',
                'suppliers.show.section.stats': 'Statistik',
                'suppliers.show.stats.assets': 'Total Aset',
                'suppliers.show.stats.invoices': 'Total Faktur',
                'suppliers.show.stats.value': 'Total Nilai',
                'suppliers.show.section.assets': 'Aset dari Supplier Ini',
                'suppliers.show.count.assets': 'Aset',
                'suppliers.show.table.asset_tag': 'Tag Aset',
                'suppliers.show.table.model': 'Model',
                'suppliers.show.table.serial': 'Nomor Serial',
                'suppliers.show.table.status': 'Status',
                'suppliers.show.table.purchase_cost': 'Biaya Pembelian',
                'suppliers.show.table.actions': 'Aksi',
                'suppliers.show.action.view_title': 'Lihat',
                'suppliers.show.action.view': 'Lihat',
                'suppliers.show.empty.assets': 'Belum ada aset yang dibeli dari supplier ini.',
                'suppliers.show.section.invoices': 'Faktur dari Supplier Ini',
                'suppliers.show.count.invoices': 'Faktur',
                'suppliers.show.table.invoice_number': 'Nomor Faktur',
                'suppliers.show.table.date': 'Tanggal',
                'suppliers.show.table.amount': 'Jumlah',
                'suppliers.show.table.invoice_status': 'Status',
                'suppliers.show.empty.invoices': 'Belum ada faktur yang tercatat untuk supplier ini.'
            }
        };

        var currentLanguage = 'en';
        var userId = '{{ (int) auth()->id() }}';
        var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
        var englishButton = document.getElementById('supplierShowLanguageEnglish');
        var indonesianButton = document.getElementById('supplierShowLanguageIndonesian');

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

            Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-title]'), function(node) {
                var key = node.getAttribute('data-i18n-title');
                if (dictionary[key]) {
                    node.setAttribute('title', dictionary[key]);
                }
            });

            if (englishButton && indonesianButton) {
                englishButton.classList.toggle('active', currentLanguage === 'en');
                indonesianButton.classList.toggle('active', currentLanguage === 'id');
            }
        }

        window.supplierShowLabel = getLabel;

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

        $(document).ready(function() {
            applyLanguage(getLanguage());
        });
    })();
</script>
@endpush
