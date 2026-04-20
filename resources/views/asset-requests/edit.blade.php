@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit Asset Request',
    'subtitle' => 'Modify asset request details',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Asset Requests', 'url' => route('asset-requests.index')],
        ['label' => 'Edit Request #'.$assetRequest->id]
    ]
])

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'assetRequestEditLanguageEnglish',
    'indonesianButtonId' => 'assetRequestEditLanguageIndonesian',
    'ariaLabel' => 'Asset Request Edit Language Toggle',
])

<div class="container-fluid">

    {{-- Request Metadata Alert --}}
    <div class="alert metadata-alert">
        <i class="fa fa-info-circle"></i> <strong>Request #{{ $assetRequest->request_number ?? $assetRequest->id }}</strong>
        <span class="pull-right">
            <small>
                Created: {{ $assetRequest->created_at->format('d M Y, h:i A') }}
                @if($assetRequest->updated_at && $assetRequest->updated_at != $assetRequest->created_at)
                    | Last Updated: {{ $assetRequest->updated_at->format('d M Y, h:i A') }}
                @endif
            </small>
        </span>
    </div>

    <div class="row">
        {{-- Main Form (8 columns) --}}
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> <span data-i18n="asset_request.edit.form.title">Edit Request Details</span></h3>
                </div>
                <div class="box-body">

                    {{-- Flash Messages --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fa fa-exclamation-circle"></i> <strong data-i18n="asset_request.edit.validation.title">Validation errors:</strong>
                            <ul style="margin-bottom: 0; margin-top: 5px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($assetRequest->status !== 'pending')
                        <div class="alert alert-info">
                            <i class="fa fa-lock"></i> <strong>Note:</strong> This request cannot be edited because it has already been {{ $assetRequest->status }}.
                        </div>
                    @endif

                    <form action="{{ route('asset-requests.update', $assetRequest->id) }}" method="POST" id="asset-request-form">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Asset Details --}}
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-box"></i></span> <span data-i18n="asset_request.edit.section.asset">Asset Details</span></legend>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="title">Asset Name / Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                            <input type="text" name="title" id="title" 
                                                   class="form-control @error('title') is-invalid @enderror" 
                                                   value="{{ old('title', $assetRequest->title) }}" 
                                                   placeholder="e.g., Dell Latitude 7420 Laptop" 
                                                   {{ $assetRequest->status !== 'pending' ? 'disabled' : 'required' }}>
                                        </div>
                                        <small class="help-text">Enter the name or model of the asset you need</small>
                                        @error('title')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="asset_type_id">Asset Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('asset_type_id') is-invalid @enderror" 
                                                id="asset_type_id" name="asset_type_id" 
                                                {{ $assetRequest->status !== 'pending' ? 'disabled' : 'required' }}>
                                            <option value="">Select Type</option>
                                            @foreach($assetTypes as $type)
                                                <option value="{{ $type->id }}" {{ old('asset_type_id', $assetRequest->asset_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text">Category of asset</small>
                                        @error('asset_type_id')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="requested_quantity">Quantity</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                            <input type="number" name="requested_quantity" id="requested_quantity" 
                                                   class="form-control @error('requested_quantity') is-invalid @enderror" 
                                                   value="{{ old('requested_quantity', $assetRequest->requested_quantity ?? 1) }}" 
                                                   min="1"
                                                   {{ $assetRequest->status !== 'pending' ? 'disabled' : '' }}>
                                        </div>
                                        <small class="help-text">How many units needed</small>
                                        @error('requested_quantity')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="unit">Unit</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-cube"></i></span>
                                            <input type="text" name="unit" id="unit" 
                                                   class="form-control @error('unit') is-invalid @enderror" 
                                                   value="{{ old('unit', $assetRequest->unit) }}"
                                                   placeholder="e.g., pcs, set, unit"
                                                   {{ $assetRequest->status !== 'pending' ? 'disabled' : '' }}>
                                        </div>
                                        <small class="help-text">Unit of measurement</small>
                                        @error('unit')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="priority">Priority</label>
                                        @php($priorityOptions = $priorities ?? ['low', 'medium', 'high', 'urgent'])
                                        <select class="form-control @error('priority') is-invalid @enderror" 
                                                id="priority" name="priority"
                                                {{ $assetRequest->status !== 'pending' ? 'disabled' : '' }}>
                                            <option value="">Select Priority</option>
                                            @foreach($priorityOptions as $priority)
                                                <option value="{{ $priority }}" {{ old('priority', $assetRequest->priority) == $priority ? 'selected' : '' }}>
                                                    {{ ucfirst($priority) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text">Urgency level</small>
                                        @error('priority')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="needed_date">Needed By Date</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="date" name="needed_date" id="needed_date" 
                                                   class="form-control @error('needed_date') is-invalid @enderror" 
                                                   value="{{ old('needed_date', $assetRequest->needed_date ? $assetRequest->needed_date->format('Y-m-d') : '') }}"
                                                   {{ $assetRequest->status !== 'pending' ? 'disabled' : '' }}>
                                        </div>
                                        <small class="help-text">When do you need this asset?</small>
                                        @error('needed_date')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Section 2: Justification --}}
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-file-alt"></i></span> <span data-i18n="asset_request.edit.section.justification">Justification</span></legend>
                            
                            <div class="form-group">
                                <label for="justification">Business Justification <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('justification') is-invalid @enderror" 
                                          id="justification" name="justification" rows="6" 
                                          {{ $assetRequest->status !== 'pending' ? 'disabled' : 'required' }}>{{ old('justification', $assetRequest->justification) }}</textarea>
                                <small class="help-text">Explain why this asset is needed and its business impact</small>
                                @error('justification')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Additional Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3"
                                          {{ $assetRequest->status !== 'pending' ? 'disabled' : '' }}>{{ old('notes', $assetRequest->notes) }}</textarea>
                                <small class="help-text">Any additional information or special requirements</small>
                                @error('notes')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </fieldset>

                        {{-- Submit Buttons --}}
                        @if($assetRequest->status === 'pending')
                            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-save"></i> <b data-i18n="asset_request.edit.action.submit">Update Request</b>
                                </button>
                                <a href="{{ route('asset-requests.index') }}" class="btn btn-default btn-lg">
                                    <i class="fa fa-times"></i> <span data-i18n="asset_request.edit.action.cancel">Cancel</span>
                                </a>
                            </div>
                        @else
                            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
                                <a href="{{ route('asset-requests.index') }}" class="btn btn-default btn-lg">
                                    <i class="fa fa-arrow-left"></i> Back to List
                                </a>
                                <a href="{{ route('asset-requests.show', $assetRequest->id) }}" class="btn btn-info btn-lg">
                                    <i class="fa fa-eye"></i> View Details
                                </a>
                            </div>
                        @endif

                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar (4 columns) --}}
        <div class="col-md-4">
            
            {{-- Request Status --}}
            <div class="box box-{{ $assetRequest->status === 'approved' ? 'success' : ($assetRequest->status === 'rejected' ? 'danger' : 'warning') }}">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Request Status</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Status:</dt>
                        <dd>
                            @if($assetRequest->status === 'fulfilled')
                                <span class="label label-primary"><i class="fa fa-check-double"></i> Fulfilled</span>
                            @elseif($assetRequest->status === 'approved')
                                <span class="label label-success"><i class="fa fa-check"></i> Approved</span>
                            @elseif($assetRequest->status === 'rejected')
                                <span class="label label-danger"><i class="fa fa-times"></i> Rejected</span>
                            @else
                                <span class="label label-warning"><i class="fa fa-clock"></i> Pending</span>
                            @endif
                        </dd>
                        
                        <dt>Requested By:</dt>
                        <dd>{{ $assetRequest->requestedBy->name ?? 'N/A' }}</dd>
                        
                        <dt>Division:</dt>
                        <dd>{{ $assetRequest->requestedBy->division ?? 'N/A' }}</dd>
                        
                        <dt>Created:</dt>
                        <dd>{{ $assetRequest->created_at->format('d M Y') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Edit Tips --}}
            @if($assetRequest->status === 'pending')
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> Edit Tips</h3>
                </div>
                <div class="box-body">
                    <ul style="margin-left: 20px;">
                        <li><i class="fa fa-exclamation-triangle text-warning"></i> Only pending requests can be edited</li>
                        <li><i class="fa fa-info-circle text-info"></i> Changes will reset approval workflow</li>
                        <li><i class="fa fa-check text-success"></i> Review all details before saving</li>
                        <li><i class="fa fa-clock text-muted"></i> Notify your manager of changes</li>
                    </ul>
                </div>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ route('asset-requests.index') }}" class="btn btn-default btn-block">
                        <i class="fa fa-list"></i> Back to List
                    </a>
                    <a href="{{ route('asset-requests.show', $assetRequest->id) }}" class="btn btn-info btn-block">
                        <i class="fa fa-eye"></i> View Full Details
                    </a>
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
            'asset_request.edit.form.title': 'Edit Request Details',
            'asset_request.edit.validation.title': 'Validation errors:',
            'asset_request.edit.section.asset': 'Asset Details',
            'asset_request.edit.section.justification': 'Justification',
            'asset_request.edit.action.submit': 'Update Request',
            'asset_request.edit.action.cancel': 'Cancel',
            'asset_request.edit.runtime.asset_name_required': 'Please enter the asset name/title',
            'asset_request.edit.runtime.asset_type_required': 'Please select an asset type',
            'asset_request.edit.runtime.justification_required': 'Please provide a detailed justification (minimum 20 characters)'
        },
        id: {
            'asset_request.edit.form.title': 'Ubah Detail Permintaan',
            'asset_request.edit.validation.title': 'Kesalahan validasi:',
            'asset_request.edit.section.asset': 'Detail Aset',
            'asset_request.edit.section.justification': 'Justifikasi',
            'asset_request.edit.action.submit': 'Perbarui Permintaan',
            'asset_request.edit.action.cancel': 'Batal',
            'asset_request.edit.runtime.asset_name_required': 'Silakan masukkan nama/judul aset',
            'asset_request.edit.runtime.asset_type_required': 'Silakan pilih jenis aset',
            'asset_request.edit.runtime.justification_required': 'Silakan isi justifikasi detail (minimal 20 karakter)'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('assetRequestEditLanguageEnglish');
    var indonesianButton = document.getElementById('assetRequestEditLanguageIndonesian');

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

    window.assetRequestEditLabel = getLabel;

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

$(document).ready(function() {
    // Form validation (only if editable)
    @if($assetRequest->status === 'pending')
    $('#asset-request-form').on('submit', function(e) {
        if ($('#title').val().trim() === '') {
            alert(window.assetRequestEditLabel('asset_request.edit.runtime.asset_name_required', 'Please enter the asset name/title'));
            $('#title').focus();
            return false;
        }
        
        if ($('#asset_type_id').val() === '') {
            alert(window.assetRequestEditLabel('asset_request.edit.runtime.asset_type_required', 'Please select an asset type'));
            $('#asset_type_id').focus();
            return false;
        }
        
        if ($('#justification').val().trim().length < 20) {
            alert(window.assetRequestEditLabel('asset_request.edit.runtime.justification_required', 'Please provide a detailed justification (minimum 20 characters)'));
            $('#justification').focus();
            return false;
        }
        
        return true;
    });
    @endif

    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
