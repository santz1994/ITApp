@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Budget Management',
    'subtitle' => 'Financial Planning & Tracking',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Budgets']
    ]
])

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'budgetIndexLanguageEnglish',
    'indonesianButtonId' => 'budgetIndexLanguageIndonesian',
    'ariaLabel' => 'Budget Index Language Toggle',
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    @if(Session::has('status'))
        <div class="alert alert-{{ Session::get('status') == 'success' ? 'success' : 'danger' }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-{{ Session::get('status') == 'success' ? 'check-circle' : 'exclamation-triangle' }}"></i>
            <strong>{{ Session::get('title') }}</strong> - {{ Session::get('message') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-warning"></i> <span data-i18n="budgets.index.alert.validation">Validation Errors</span></h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.index.table.title">All Budgets</span>
                        <span class="count-badge">{{ $budgets->count() }}</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <table id="table" class="table table-bordered table-striped table-hover table-enhanced">
                        <thead>
                            <tr>
                                <th><i class="fa fa-sitemap"></i> <span data-i18n="budgets.index.table.division">Division</span></th>
                                <th><i class="fa fa-calendar"></i> <span data-i18n="budgets.index.table.fiscal_year">Fiscal Year</span></th>
                                <th><i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.index.table.amount">Budget Amount</span></th>
                                <th style="width: 150px;"><i class="fa fa-cog"></i> <span data-i18n="budgets.index.table.actions">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($budgets->count() > 0)
                                @foreach($budgets as $budget)
                                    <tr>
                                        <td><strong>{{ $budget->division->name }}</strong></td>
                                        <td>
                                            <span class="badge bg-blue">{{ $budget->year }}</span>
                                        </td>
                                        <td>
                                            <strong style="color: #28a745; font-size: 14px;">
                                                Rp {{ number_format($budget->total, 2) }}
                                            </strong>
                                        </td>
                                        <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ url('budgets/' . $budget->id) }}" class="btn btn-sm btn-info" title="View Budget" data-i18n-title="budgets.index.action.view_title">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ url('budgets/' . $budget->id . '/edit') }}" class="btn btn-sm btn-primary" title="Edit Budget" data-i18n-title="budgets.index.action.edit_title">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center empty-state" style="padding: 30px;">
                                        <i class="fa fa-money-bill-wave fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                                        <p data-i18n="budgets.index.empty.title">No budgets found.</p>
                                        <p class="text-muted" style="font-size: 12px;" data-i18n="budgets.index.empty.subtitle">Create your first budget using the form on the right.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-3">
            {{-- Create Budget Form --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-plus-circle"></i> <span data-i18n="budgets.index.form.title">Create Budget</span></h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ url('budgets') }}" id="createBudgetForm">
                        @csrf
                        
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-info-circle"></i></span>
                                <span data-i18n="budgets.index.form.section.details">Budget Details</span>
                            </legend>

                            <div class="form-group {{ hasErrorForClass($errors, 'division_id') }}">
                                <label for="division_id">
                                    <i class="fa fa-sitemap"></i> <span data-i18n="budgets.index.form.label.division">Division</span> <span class="text-danger">*</span>
                                </label>
                                <select class="form-control division_id" name="division_id" id="division_id" required>
                                    <option value="" data-i18n="budgets.index.form.option.select_division">-- Select Division --</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help-text" data-i18n="budgets.index.form.help.division">Select the division for this budget</small>
                                {{ hasErrorForField($errors, 'division_id') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'year') }}">
                                <label for="year">
                                    <i class="fa fa-calendar"></i> <span data-i18n="budgets.index.form.label.year">Fiscal Year</span> <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="year" 
                                       id="year" 
                                       class="form-control" 
                                       value="{{ old('year', date('Y')) }}"
                                       min="2020"
                                       max="2099"
                                       placeholder="e.g., {{ date('Y') }}"
                                       data-i18n-placeholder="budgets.index.form.placeholder.year"
                                       required>
                                <small class="help-text" data-i18n="budgets.index.form.help.year">Enter the fiscal year (e.g., {{ date('Y') }})</small>
                                {{ hasErrorForField($errors, 'year') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'total') }}">
                                <label for="total">
                                    <i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.index.form.label.total">Budget Amount</span> <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon">Rp</div>
                                    <input type="number" 
                                           name="total" 
                                           id="total" 
                                           class="form-control" 
                                           value="{{ old('total') }}"
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00"
                                           data-i18n-placeholder="budgets.index.form.placeholder.total"
                                           required>
                                </div>
                                <small class="help-text" data-i18n="budgets.index.form.help.total">Enter the total budget amount in Rupiah</small>
                                {{ hasErrorForField($errors, 'total') }}
                            </div>
                        </fieldset>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary btn-block btn-gradient" id="createBudgetSubmitButton">
                                <i class="fa fa-save"></i> <span data-i18n="budgets.index.action.submit">Create Budget</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Budget Guidelines --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> <span data-i18n="budgets.index.guidelines.title">Budget Guidelines</span></h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong data-i18n="budgets.index.guidelines.best_practices">Best Practices:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li data-i18n="budgets.index.guidelines.item_one">Create one budget per division per fiscal year</li>
                        <li data-i18n="budgets.index.guidelines.item_two">Use actual fiscal year dates (e.g., {{ date('Y') }})</li>
                        <li data-i18n="budgets.index.guidelines.item_three">Set realistic budget amounts based on historical data</li>
                        <li data-i18n="budgets.index.guidelines.item_four">Review and adjust budgets quarterly</li>
                    </ul>
                    <hr>
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong data-i18n="budgets.index.guidelines.planning_tips">Budget Planning Tips:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong data-i18n="budgets.index.guidelines.tip_asset">Asset Replacement:</strong> <span data-i18n="budgets.index.guidelines.tip_asset_desc">Plan for hardware lifecycle</span></li>
                        <li><strong data-i18n="budgets.index.guidelines.tip_software">Software Licenses:</strong> <span data-i18n="budgets.index.guidelines.tip_software_desc">Include annual subscriptions</span></li>
                        <li><strong data-i18n="budgets.index.guidelines.tip_maintenance">Maintenance:</strong> <span data-i18n="budgets.index.guidelines.tip_maintenance_desc">Budget for repairs and upgrades</span></li>
                        <li><strong data-i18n="budgets.index.guidelines.tip_contingency">Contingency:</strong> <span data-i18n="budgets.index.guidelines.tip_contingency_desc">Reserve 10-15% for emergencies</span></li>
                    </ul>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-chart-bar"></i> <span data-i18n="budgets.index.statistics.title">Budget Statistics</span></h3>
                </div>
                <div class="box-body">
                    @php
                        $totalBudget = $budgets->sum('total');
                        $currentYearBudgets = $budgets->where('year', date('Y'));
                        $currentYearTotal = $currentYearBudgets->sum('total');
                    @endphp

                    <div class="info-box bg-aqua" style="min-height: 80px; margin-bottom: 15px;">
                        <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-i18n="budgets.index.statistics.total_all">Total All Budgets</span>
                            <span class="info-box-number">Rp {{ number_format($totalBudget, 2) }}</span>
                            <span class="progress-description">
                                <span data-i18n="budgets.index.statistics.total_all_desc">All fiscal years</span>
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-green" style="min-height: 80px; margin-bottom: 0;">
                        <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ date('Y') }} Budgets</span>
                            <span class="info-box-number">Rp {{ number_format($currentYearTotal, 2) }}</span>
                            <span class="progress-description">
                                {{ $currentYearBudgets->count() }} division(s)
                            </span>
                        </div>
                    </div>
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
                'budgets.index.alert.validation': 'Validation Errors',
                'budgets.index.table.title': 'All Budgets',
                'budgets.index.table.division': 'Division',
                'budgets.index.table.fiscal_year': 'Fiscal Year',
                'budgets.index.table.amount': 'Budget Amount',
                'budgets.index.table.actions': 'Actions',
                'budgets.index.action.view_title': 'View Budget',
                'budgets.index.action.edit_title': 'Edit Budget',
                'budgets.index.empty.title': 'No budgets found.',
                'budgets.index.empty.subtitle': 'Create your first budget using the form on the right.',
                'budgets.index.form.title': 'Create Budget',
                'budgets.index.form.section.details': 'Budget Details',
                'budgets.index.form.label.division': 'Division',
                'budgets.index.form.option.select_division': '-- Select Division --',
                'budgets.index.form.help.division': 'Select the division for this budget',
                'budgets.index.form.label.year': 'Fiscal Year',
                'budgets.index.form.placeholder.year': 'e.g., {{ date('Y') }}',
                'budgets.index.form.help.year': 'Enter the fiscal year (e.g., {{ date('Y') }})',
                'budgets.index.form.label.total': 'Budget Amount',
                'budgets.index.form.placeholder.total': '0.00',
                'budgets.index.form.help.total': 'Enter the total budget amount in Rupiah',
                'budgets.index.action.submit': 'Create Budget',
                'budgets.index.action.submitting': 'Saving Budget...',
                'budgets.index.guidelines.title': 'Budget Guidelines',
                'budgets.index.guidelines.best_practices': 'Best Practices:',
                'budgets.index.guidelines.item_one': 'Create one budget per division per fiscal year',
                'budgets.index.guidelines.item_two': 'Use actual fiscal year dates (e.g., {{ date('Y') }})',
                'budgets.index.guidelines.item_three': 'Set realistic budget amounts based on historical data',
                'budgets.index.guidelines.item_four': 'Review and adjust budgets quarterly',
                'budgets.index.guidelines.planning_tips': 'Budget Planning Tips:',
                'budgets.index.guidelines.tip_asset': 'Asset Replacement:',
                'budgets.index.guidelines.tip_asset_desc': 'Plan for hardware lifecycle',
                'budgets.index.guidelines.tip_software': 'Software Licenses:',
                'budgets.index.guidelines.tip_software_desc': 'Include annual subscriptions',
                'budgets.index.guidelines.tip_maintenance': 'Maintenance:',
                'budgets.index.guidelines.tip_maintenance_desc': 'Budget for repairs and upgrades',
                'budgets.index.guidelines.tip_contingency': 'Contingency:',
                'budgets.index.guidelines.tip_contingency_desc': 'Reserve 10-15% for emergencies',
                'budgets.index.statistics.title': 'Budget Statistics',
                'budgets.index.statistics.total_all': 'Total All Budgets',
                'budgets.index.statistics.total_all_desc': 'All fiscal years',
                'budgets.index.runtime.required_fields': 'Please fill in all required fields.',
                'budgets.index.runtime.amount_invalid': 'Budget amount must be greater than 0.',
                'budgets.index.datatable.search': 'Search budgets:',
                'budgets.index.datatable.length_menu': 'Show _MENU_ budgets per page',
                'budgets.index.datatable.info': 'Showing _START_ to _END_ of _TOTAL_ budgets',
                'budgets.index.datatable.info_empty': 'No budgets available',
                'budgets.index.datatable.info_filtered': '(filtered from _MAX_ total budgets)',
                'budgets.index.datatable.zero_records': 'No matching budgets found',
                'budgets.index.datatable.empty_table': 'No budgets available',
                'budgets.index.datatable.excel': 'Excel',
                'budgets.index.datatable.csv': 'CSV',
                'budgets.index.datatable.pdf': 'PDF',
                'budgets.index.datatable.copy': 'Copy'
            },
            id: {
                'budgets.index.alert.validation': 'Kesalahan Validasi',
                'budgets.index.table.title': 'Semua Anggaran',
                'budgets.index.table.division': 'Divisi',
                'budgets.index.table.fiscal_year': 'Tahun Fiskal',
                'budgets.index.table.amount': 'Jumlah Anggaran',
                'budgets.index.table.actions': 'Aksi',
                'budgets.index.action.view_title': 'Lihat Anggaran',
                'budgets.index.action.edit_title': 'Ubah Anggaran',
                'budgets.index.empty.title': 'Belum ada anggaran.',
                'budgets.index.empty.subtitle': 'Buat anggaran pertama Anda menggunakan formulir di sebelah kanan.',
                'budgets.index.form.title': 'Buat Anggaran',
                'budgets.index.form.section.details': 'Detail Anggaran',
                'budgets.index.form.label.division': 'Divisi',
                'budgets.index.form.option.select_division': '-- Pilih Divisi --',
                'budgets.index.form.help.division': 'Pilih divisi untuk anggaran ini',
                'budgets.index.form.label.year': 'Tahun Fiskal',
                'budgets.index.form.placeholder.year': 'contoh, {{ date('Y') }}',
                'budgets.index.form.help.year': 'Masukkan tahun fiskal (contoh, {{ date('Y') }})',
                'budgets.index.form.label.total': 'Jumlah Anggaran',
                'budgets.index.form.placeholder.total': '0.00',
                'budgets.index.form.help.total': 'Masukkan total anggaran dalam Rupiah',
                'budgets.index.action.submit': 'Buat Anggaran',
                'budgets.index.action.submitting': 'Menyimpan Anggaran...',
                'budgets.index.guidelines.title': 'Panduan Anggaran',
                'budgets.index.guidelines.best_practices': 'Praktik Terbaik:',
                'budgets.index.guidelines.item_one': 'Buat satu anggaran per divisi per tahun fiskal',
                'budgets.index.guidelines.item_two': 'Gunakan tahun fiskal aktual (contoh, {{ date('Y') }})',
                'budgets.index.guidelines.item_three': 'Tetapkan jumlah anggaran realistis berdasarkan data historis',
                'budgets.index.guidelines.item_four': 'Tinjau dan sesuaikan anggaran setiap kuartal',
                'budgets.index.guidelines.planning_tips': 'Tips Perencanaan Anggaran:',
                'budgets.index.guidelines.tip_asset': 'Penggantian Aset:',
                'budgets.index.guidelines.tip_asset_desc': 'Rencanakan siklus hidup perangkat keras',
                'budgets.index.guidelines.tip_software': 'Lisensi Perangkat Lunak:',
                'budgets.index.guidelines.tip_software_desc': 'Sertakan langganan tahunan',
                'budgets.index.guidelines.tip_maintenance': 'Pemeliharaan:',
                'budgets.index.guidelines.tip_maintenance_desc': 'Anggarkan perbaikan dan peningkatan',
                'budgets.index.guidelines.tip_contingency': 'Kontinjensi:',
                'budgets.index.guidelines.tip_contingency_desc': 'Cadangkan 10-15% untuk keadaan darurat',
                'budgets.index.statistics.title': 'Statistik Anggaran',
                'budgets.index.statistics.total_all': 'Total Semua Anggaran',
                'budgets.index.statistics.total_all_desc': 'Semua tahun fiskal',
                'budgets.index.runtime.required_fields': 'Mohon isi semua bidang wajib.',
                'budgets.index.runtime.amount_invalid': 'Jumlah anggaran harus lebih besar dari 0.',
                'budgets.index.datatable.search': 'Cari anggaran:',
                'budgets.index.datatable.length_menu': 'Tampilkan _MENU_ anggaran per halaman',
                'budgets.index.datatable.info': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ anggaran',
                'budgets.index.datatable.info_empty': 'Tidak ada anggaran tersedia',
                'budgets.index.datatable.info_filtered': '(disaring dari total _MAX_ anggaran)',
                'budgets.index.datatable.zero_records': 'Tidak ada anggaran yang cocok',
                'budgets.index.datatable.empty_table': 'Tidak ada anggaran tersedia',
                'budgets.index.datatable.excel': 'Excel',
                'budgets.index.datatable.csv': 'CSV',
                'budgets.index.datatable.pdf': 'PDF',
                'budgets.index.datatable.copy': 'Salin'
            }
        };

        var currentLanguage = 'en';
        var userId = '{{ (int) auth()->id() }}';
        var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
        var englishButton = document.getElementById('budgetIndexLanguageEnglish');
        var indonesianButton = document.getElementById('budgetIndexLanguageIndonesian');
        var budgetsTable = null;

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

            if (typeof window.budgetIndexRefreshRuntimeText === 'function') {
                window.budgetIndexRefreshRuntimeText();
            }
        }

        window.budgetIndexLabel = getLabel;
        window.budgetIndexDataTableLanguage = function() {
            return {
                search: getLabel('budgets.index.datatable.search', 'Search budgets:'),
                lengthMenu: getLabel('budgets.index.datatable.length_menu', 'Show _MENU_ budgets per page'),
                info: getLabel('budgets.index.datatable.info', 'Showing _START_ to _END_ of _TOTAL_ budgets'),
                infoEmpty: getLabel('budgets.index.datatable.info_empty', 'No budgets available'),
                infoFiltered: getLabel('budgets.index.datatable.info_filtered', '(filtered from _MAX_ total budgets)'),
                zeroRecords: getLabel('budgets.index.datatable.zero_records', 'No matching budgets found'),
                emptyTable: getLabel('budgets.index.datatable.empty_table', 'No budgets available')
            };
        };

        window.budgetIndexRefreshRuntimeText = function() {
            if (window.jQuery && $.fn.select2 && $('#division_id').length) {
                $('#division_id').select2({
                    placeholder: getLabel('budgets.index.form.option.select_division', '-- Select Division --'),
                    allowClear: true
                });
            }

            if (budgetsTable) {
                budgetsTable.settings()[0].oLanguage = window.budgetIndexDataTableLanguage();
                budgetsTable.draw(false);
            }
        };

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
            // Initialize Select2
            $(".division_id").select2({
                placeholder: window.budgetIndexLabel('budgets.index.form.option.select_division', '-- Select Division --'),
                allowClear: true
            });

            // Initialize DataTable only if there are budgets
            @if($budgets->count() > 0)
            budgetsTable = $('#table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[1, "desc"]], // Sort by year descending
                columnDefs: [
                    { orderable: false, targets: 3 } // Actions column
                ],
                language: window.budgetIndexDataTableLanguage(),
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                     '<"row"<"col-sm-12"<"table-responsive"tr>>>' +
                     '<"row"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel"></i> ' + window.budgetIndexLabel('budgets.index.datatable.excel', 'Excel'),
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa fa-file-csv"></i> ' + window.budgetIndexLabel('budgets.index.datatable.csv', 'CSV'),
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf"></i> ' + window.budgetIndexLabel('budgets.index.datatable.pdf', 'PDF'),
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2]
                        }
                    },
                    {
                        extend: 'copy',
                        text: '<i class="fa fa-copy"></i> ' + window.budgetIndexLabel('budgets.index.datatable.copy', 'Copy'),
                        className: 'btn btn-default btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2]
                        }
                    }
                ]
            });
            @endif

            // Form validation
            $('#createBudgetForm').on('submit', function(e) {
                var division = $('#division_id').val();
                var year = $('#year').val();
                var total = $('#total').val();

                if (!division || !year || !total) {
                    e.preventDefault();
                    alert(window.budgetIndexLabel('budgets.index.runtime.required_fields', 'Please fill in all required fields.'));
                    return false;
                }

                if (parseFloat(total) <= 0) {
                    e.preventDefault();
                    alert(window.budgetIndexLabel('budgets.index.runtime.amount_invalid', 'Budget amount must be greater than 0.'));
                    return false;
                }

                var submitButton = document.getElementById('createBudgetSubmitButton');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + window.budgetIndexLabel('budgets.index.action.submitting', 'Saving Budget...');
                }
            });

            // Auto-dismiss alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            applyLanguage(getLanguage());
        });
    })();
</script>
@endpush


