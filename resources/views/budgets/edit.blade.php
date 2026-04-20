@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit Budget',
    'subtitle' => $budget->division->name . ' - ' . $budget->year,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Budgets', 'url' => url('budgets')],
        ['label' => 'Edit']
    ]
])

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'budgetEditLanguageEnglish',
    'indonesianButtonId' => 'budgetEditLanguageIndonesian',
    'ariaLabel' => 'Budget Edit Language Toggle',
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

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-warning"></i> <span data-i18n="budgets.edit.alert.validation">Validation Errors</span></h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Budget Metadata --}}
    <div class="alert alert-info metadata-alert">
        <div class="row">
            <div class="col-md-4">
                <strong><i class="fa fa-hashtag"></i> <span data-i18n="budgets.edit.meta.id">Budget ID:</span></strong> #{{ $budget->id }}
            </div>
            <div class="col-md-4">
                <strong><i class="fa fa-calendar"></i> <span data-i18n="budgets.edit.meta.created">Created:</span></strong> 
                {{ $budget->created_at ? $budget->created_at->format('M d, Y') : 'N/A' }}
            </div>
            <div class="col-md-4">
                <strong><i class="fa fa-clock"></i> <span data-i18n="budgets.edit.meta.updated">Last Updated:</span></strong> 
                {{ $budget->updated_at ? $budget->updated_at->format('M d, Y') : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Form --}}
        <div class="col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> <span data-i18n="budgets.edit.form.title">Edit Budget Details</span></h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ url('budgets/' . $budget->id) }}" id="editBudgetForm">
                        @method('PATCH')
                        @csrf

                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-money-bill-wave"></i></span>
                                <span data-i18n="budgets.edit.section.info">Budget Information</span>
                            </legend>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'division_id') }}">
                                        <label for="division_id">
                                            <i class="fa fa-sitemap"></i> <span data-i18n="budgets.edit.label.division">Division</span> <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control division_id" name="division_id" id="division_id" required>
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->id }}" {{ $budget->division_id == $division->id ? 'selected' : '' }}>
                                                    {{ $division->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text" data-i18n="budgets.edit.help.division">Select the division for this budget</small>
                                        {{ hasErrorForField($errors, 'division_id') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'year') }}">
                                        <label for="year">
                                            <i class="fa fa-calendar"></i> <span data-i18n="budgets.edit.label.year">Fiscal Year</span> <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               name="year" 
                                               id="year" 
                                               class="form-control" 
                                               value="{{ $budget->year }}"
                                               min="2020"
                                               max="2099"
                                               placeholder="e.g., {{ date('Y') }}"
                                                 data-i18n-placeholder="budgets.edit.placeholder.year"
                                               required>
                                             <small class="help-text" data-i18n="budgets.edit.help.year">Enter the fiscal year (e.g., {{ date('Y') }})</small>
                                        {{ hasErrorForField($errors, 'year') }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {{ hasErrorForClass($errors, 'total') }}">
                                        <label for="total">
                                            <i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.edit.label.total">Budget Amount</span> <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-addon">Rp</div>
                                            <input type="number" 
                                                   name="total" 
                                                   id="total" 
                                                   class="form-control" 
                                                   value="{{ $budget->total }}"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   data-i18n-placeholder="budgets.edit.placeholder.total"
                                                   required>
                                        </div>
                                        <small class="help-text" data-i18n="budgets.edit.help.total">
                                            Enter the total budget amount in Rupiah. Current:
                                        </small>
                                        <strong class="help-text">Rp {{ number_format($budget->total, 2) }}</strong>
                                        {{ hasErrorForField($errors, 'total') }}
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-warning btn-lg btn-gradient" id="editBudgetSubmitButton">
                                <i class="fa fa-save"></i> <span data-i18n="budgets.edit.action.submit">Update Budget</span>
                            </button>
                            <a href="{{ url('budgets') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> <span data-i18n="budgets.edit.action.cancel">Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Edit Tips --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> <span data-i18n="budgets.edit.tips.title">Edit Tips</span></h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong><i class="fa fa-exclamation-triangle text-warning"></i> <span data-i18n="budgets.edit.tips.important">Important Notes:</span></strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong data-i18n="budgets.edit.tips.item_division">Division Change:</strong> <span data-i18n="budgets.edit.tips.item_division_desc">Changing the division may affect financial reports</span></li>
                        <li><strong data-i18n="budgets.edit.tips.item_year">Fiscal Year:</strong> <span data-i18n="budgets.edit.tips.item_year_desc">Ensure the year matches your accounting period</span></li>
                        <li><strong data-i18n="budgets.edit.tips.item_amount">Budget Amount:</strong> <span data-i18n="budgets.edit.tips.item_amount_desc">Changes will be reflected in spending tracking</span></li>
                        <li><strong data-i18n="budgets.edit.tips.item_history">Historical Data:</strong> <span data-i18n="budgets.edit.tips.item_history_desc">Past allocations and expenses remain unchanged</span></li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="budgets.edit.quick_actions.title">Quick Actions</span></h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('budgets') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> <span data-i18n="budgets.edit.quick_actions.back">Back to Budgets</span>
                    </a>
                    @if(url()->previous() != url()->current())
                        <a href="{{ url('budgets/' . $budget->id) }}" class="btn btn-info btn-block">
                            <i class="fa fa-eye"></i> <span data-i18n="budgets.edit.quick_actions.view">View Budget Details</span>
                        </a>
                    @endif
                    <hr>
                    <form method="POST" action="{{ url('budgets/' . $budget->id) }}" id="budgetEditDeleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> <span data-i18n="budgets.edit.quick_actions.delete">Delete Budget</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Best Practices --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check-circle"></i> <span data-i18n="budgets.edit.best_practices.title">Best Practices</span></h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong data-i18n="budgets.edit.best_practices.management">Budget Management:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong data-i18n="budgets.edit.best_practices.review">Regular Reviews:</strong> <span data-i18n="budgets.edit.best_practices.review_desc">Quarterly budget vs. actual comparisons</span></li>
                        <li><strong data-i18n="budgets.edit.best_practices.variance">Variance Analysis:</strong> <span data-i18n="budgets.edit.best_practices.variance_desc">Track over/under spending patterns</span></li>
                        <li><strong data-i18n="budgets.edit.best_practices.reallocation">Reallocation:</strong> <span data-i18n="budgets.edit.best_practices.reallocation_desc">Adjust budgets mid-year if needed</span></li>
                        <li><strong data-i18n="budgets.edit.best_practices.documentation">Documentation:</strong> <span data-i18n="budgets.edit.best_practices.documentation_desc">Keep notes on budget changes</span></li>
                    </ul>
                    <hr>
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong data-i18n="budgets.edit.best_practices.financial">Financial Planning:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li data-i18n="budgets.edit.best_practices.financial_one">Monitor spending against budget monthly</li>
                        <li data-i18n="budgets.edit.best_practices.financial_two">Plan for known recurring expenses</li>
                        <li data-i18n="budgets.edit.best_practices.financial_three">Reserve contingency funds (10-15%)</li>
                        <li data-i18n="budgets.edit.best_practices.financial_four">Track asset lifecycle replacement costs</li>
                    </ul>
                </div>
            </div>

            {{-- Budget Summary --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> <span data-i18n="budgets.edit.summary.title">Budget Summary</span></h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed" style="font-size: 12px; margin-bottom: 0;">
                        <tr>
                            <td><strong><i class="fa fa-sitemap"></i> <span data-i18n="budgets.edit.summary.division">Division:</span></strong></td>
                            <td>{{ $budget->division->name }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-calendar"></i> <span data-i18n="budgets.edit.summary.year">Year:</span></strong></td>
                            <td><span class="badge bg-blue">{{ $budget->year }}</span></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-money-bill-wave"></i> <span data-i18n="budgets.edit.summary.amount">Amount:</span></strong></td>
                            <td><strong style="color: #28a745;">Rp {{ number_format($budget->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-calendar-plus"></i> <span data-i18n="budgets.edit.summary.created">Created:</span></strong></td>
                            <td>{{ $budget->created_at ? $budget->created_at->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    </table>
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
                'budgets.edit.alert.validation': 'Validation Errors',
                'budgets.edit.meta.id': 'Budget ID:',
                'budgets.edit.meta.created': 'Created:',
                'budgets.edit.meta.updated': 'Last Updated:',
                'budgets.edit.form.title': 'Edit Budget Details',
                'budgets.edit.section.info': 'Budget Information',
                'budgets.edit.label.division': 'Division',
                'budgets.edit.help.division': 'Select the division for this budget',
                'budgets.edit.label.year': 'Fiscal Year',
                'budgets.edit.placeholder.year': 'e.g., {{ date('Y') }}',
                'budgets.edit.help.year': 'Enter the fiscal year (e.g., {{ date('Y') }})',
                'budgets.edit.label.total': 'Budget Amount',
                'budgets.edit.placeholder.total': '0.00',
                'budgets.edit.help.total': 'Enter the total budget amount in Rupiah. Current:',
                'budgets.edit.action.submit': 'Update Budget',
                'budgets.edit.action.cancel': 'Cancel',
                'budgets.edit.action.submitting': 'Updating Budget...',
                'budgets.edit.tips.title': 'Edit Tips',
                'budgets.edit.tips.important': 'Important Notes:',
                'budgets.edit.tips.item_division': 'Division Change:',
                'budgets.edit.tips.item_division_desc': 'Changing the division may affect financial reports',
                'budgets.edit.tips.item_year': 'Fiscal Year:',
                'budgets.edit.tips.item_year_desc': 'Ensure the year matches your accounting period',
                'budgets.edit.tips.item_amount': 'Budget Amount:',
                'budgets.edit.tips.item_amount_desc': 'Changes will be reflected in spending tracking',
                'budgets.edit.tips.item_history': 'Historical Data:',
                'budgets.edit.tips.item_history_desc': 'Past allocations and expenses remain unchanged',
                'budgets.edit.quick_actions.title': 'Quick Actions',
                'budgets.edit.quick_actions.back': 'Back to Budgets',
                'budgets.edit.quick_actions.view': 'View Budget Details',
                'budgets.edit.quick_actions.delete': 'Delete Budget',
                'budgets.edit.best_practices.title': 'Best Practices',
                'budgets.edit.best_practices.management': 'Budget Management:',
                'budgets.edit.best_practices.review': 'Regular Reviews:',
                'budgets.edit.best_practices.review_desc': 'Quarterly budget vs. actual comparisons',
                'budgets.edit.best_practices.variance': 'Variance Analysis:',
                'budgets.edit.best_practices.variance_desc': 'Track over/under spending patterns',
                'budgets.edit.best_practices.reallocation': 'Reallocation:',
                'budgets.edit.best_practices.reallocation_desc': 'Adjust budgets mid-year if needed',
                'budgets.edit.best_practices.documentation': 'Documentation:',
                'budgets.edit.best_practices.documentation_desc': 'Keep notes on budget changes',
                'budgets.edit.best_practices.financial': 'Financial Planning:',
                'budgets.edit.best_practices.financial_one': 'Monitor spending against budget monthly',
                'budgets.edit.best_practices.financial_two': 'Plan for known recurring expenses',
                'budgets.edit.best_practices.financial_three': 'Reserve contingency funds (10-15%)',
                'budgets.edit.best_practices.financial_four': 'Track asset lifecycle replacement costs',
                'budgets.edit.summary.title': 'Budget Summary',
                'budgets.edit.summary.division': 'Division:',
                'budgets.edit.summary.year': 'Year:',
                'budgets.edit.summary.amount': 'Amount:',
                'budgets.edit.summary.created': 'Created:',
                'budgets.edit.runtime.required_fields': 'Please fill in all required fields.',
                'budgets.edit.runtime.amount_invalid': 'Budget amount must be greater than 0.',
                'budgets.edit.runtime.year_invalid': 'Please enter a valid fiscal year between 2020 and 2099.',
                'budgets.edit.runtime.delete_confirm': 'Are you sure you want to delete this budget? This action cannot be undone.',
                'budgets.edit.runtime.loading': 'Updating budget...'
            },
            id: {
                'budgets.edit.alert.validation': 'Kesalahan Validasi',
                'budgets.edit.meta.id': 'ID Anggaran:',
                'budgets.edit.meta.created': 'Dibuat:',
                'budgets.edit.meta.updated': 'Terakhir Diperbarui:',
                'budgets.edit.form.title': 'Ubah Detail Anggaran',
                'budgets.edit.section.info': 'Informasi Anggaran',
                'budgets.edit.label.division': 'Divisi',
                'budgets.edit.help.division': 'Pilih divisi untuk anggaran ini',
                'budgets.edit.label.year': 'Tahun Fiskal',
                'budgets.edit.placeholder.year': 'contoh, {{ date('Y') }}',
                'budgets.edit.help.year': 'Masukkan tahun fiskal (contoh, {{ date('Y') }})',
                'budgets.edit.label.total': 'Jumlah Anggaran',
                'budgets.edit.placeholder.total': '0.00',
                'budgets.edit.help.total': 'Masukkan total anggaran dalam Rupiah. Saat ini:',
                'budgets.edit.action.submit': 'Perbarui Anggaran',
                'budgets.edit.action.cancel': 'Batal',
                'budgets.edit.action.submitting': 'Memperbarui Anggaran...',
                'budgets.edit.tips.title': 'Tips Perubahan',
                'budgets.edit.tips.important': 'Catatan Penting:',
                'budgets.edit.tips.item_division': 'Perubahan Divisi:',
                'budgets.edit.tips.item_division_desc': 'Perubahan divisi dapat memengaruhi laporan keuangan',
                'budgets.edit.tips.item_year': 'Tahun Fiskal:',
                'budgets.edit.tips.item_year_desc': 'Pastikan tahun sesuai periode akuntansi',
                'budgets.edit.tips.item_amount': 'Jumlah Anggaran:',
                'budgets.edit.tips.item_amount_desc': 'Perubahan akan tercermin pada pelacakan pengeluaran',
                'budgets.edit.tips.item_history': 'Data Historis:',
                'budgets.edit.tips.item_history_desc': 'Alokasi dan pengeluaran masa lalu tetap tidak berubah',
                'budgets.edit.quick_actions.title': 'Aksi Cepat',
                'budgets.edit.quick_actions.back': 'Kembali ke Anggaran',
                'budgets.edit.quick_actions.view': 'Lihat Detail Anggaran',
                'budgets.edit.quick_actions.delete': 'Hapus Anggaran',
                'budgets.edit.best_practices.title': 'Praktik Terbaik',
                'budgets.edit.best_practices.management': 'Manajemen Anggaran:',
                'budgets.edit.best_practices.review': 'Tinjauan Berkala:',
                'budgets.edit.best_practices.review_desc': 'Bandingkan anggaran vs aktual setiap kuartal',
                'budgets.edit.best_practices.variance': 'Analisis Varians:',
                'budgets.edit.best_practices.variance_desc': 'Lacak pola pengeluaran lebih/kurang',
                'budgets.edit.best_practices.reallocation': 'Realokasi:',
                'budgets.edit.best_practices.reallocation_desc': 'Sesuaikan anggaran di tengah tahun jika diperlukan',
                'budgets.edit.best_practices.documentation': 'Dokumentasi:',
                'budgets.edit.best_practices.documentation_desc': 'Simpan catatan perubahan anggaran',
                'budgets.edit.best_practices.financial': 'Perencanaan Keuangan:',
                'budgets.edit.best_practices.financial_one': 'Pantau pengeluaran terhadap anggaran setiap bulan',
                'budgets.edit.best_practices.financial_two': 'Rencanakan pengeluaran berulang yang diketahui',
                'budgets.edit.best_practices.financial_three': 'Cadangkan dana kontinjensi (10-15%)',
                'budgets.edit.best_practices.financial_four': 'Lacak biaya penggantian siklus hidup aset',
                'budgets.edit.summary.title': 'Ringkasan Anggaran',
                'budgets.edit.summary.division': 'Divisi:',
                'budgets.edit.summary.year': 'Tahun:',
                'budgets.edit.summary.amount': 'Jumlah:',
                'budgets.edit.summary.created': 'Dibuat:',
                'budgets.edit.runtime.required_fields': 'Mohon isi semua bidang wajib.',
                'budgets.edit.runtime.amount_invalid': 'Jumlah anggaran harus lebih besar dari 0.',
                'budgets.edit.runtime.year_invalid': 'Masukkan tahun fiskal valid antara 2020 dan 2099.',
                'budgets.edit.runtime.delete_confirm': 'Apakah Anda yakin ingin menghapus anggaran ini? Tindakan ini tidak dapat dibatalkan.',
                'budgets.edit.runtime.loading': 'Memperbarui anggaran...'
            }
        };

        var currentLanguage = 'en';
        var userId = '{{ (int) auth()->id() }}';
        var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
        var englishButton = document.getElementById('budgetEditLanguageEnglish');
        var indonesianButton = document.getElementById('budgetEditLanguageIndonesian');

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

        window.budgetEditLabel = getLabel;

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
                placeholder: window.budgetEditLabel('budgets.edit.label.division', 'Division')
            });

            // Form validation
            $('#editBudgetForm').on('submit', function(e) {
                var division = $('#division_id').val();
                var year = $('#year').val();
                var total = $('#total').val();

                if (!division || !year || !total) {
                    e.preventDefault();
                    alert(window.budgetEditLabel('budgets.edit.runtime.required_fields', 'Please fill in all required fields.'));
                    return false;
                }

                if (parseFloat(total) <= 0) {
                    e.preventDefault();
                    alert(window.budgetEditLabel('budgets.edit.runtime.amount_invalid', 'Budget amount must be greater than 0.'));
                    return false;
                }

                var yearNum = parseInt(year, 10);
                if (yearNum < 2020 || yearNum > 2099) {
                    e.preventDefault();
                    alert(window.budgetEditLabel('budgets.edit.runtime.year_invalid', 'Please enter a valid fiscal year between 2020 and 2099.'));
                    return false;
                }

                var submitButton = document.getElementById('editBudgetSubmitButton');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + window.budgetEditLabel('budgets.edit.runtime.loading', 'Updating budget...');
                }
            });

            $('#budgetEditDeleteForm').on('submit', function(e) {
                if (!confirm(window.budgetEditLabel('budgets.edit.runtime.delete_confirm', 'Are you sure you want to delete this budget? This action cannot be undone.'))) {
                    e.preventDefault();
                    return false;
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


