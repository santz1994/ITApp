@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Create Asset Request',
    'subtitle' => 'Submit a new asset request for approval',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Asset Requests', 'url' => route('asset-requests.index')],
        ['label' => 'Create']
    ]
])

<div class="container-fluid">
    <div class="row">
        {{-- Main Form (8 columns) --}}
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clipboard-list"></i> Request Details</h3>
                </div>
                <div class="box-body">

                    {{-- Flash Messages --}}
                    @if($errors->any())
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fa fa-exclamation-circle"></i> <strong>Validation errors:</strong>
                            <ul style="margin-bottom: 0; margin-top: 5px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('asset-requests.store') }}" method="POST" id="asset-request-form">
                        @csrf

                        {{-- Section 1: Requester Information --}}
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-user"></i></span> Requester Information</legend>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="requester_name">Requester Name</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" id="requester_name" class="form-control" 
                                                   value="{{ auth()->user()->name ?? '' }}" disabled>
                                        </div>
                                        <small class="help-text">Your name (auto-filled from your account)</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="division">Division</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                            <input type="text" id="division" class="form-control" 
                                                   value="{{ auth()->user()->division ?? '' }}" disabled>
                                        </div>
                                        <small class="help-text">Your division (auto-filled from your account)</small>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Section 2: Asset Details --}}
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-box"></i></span> Asset Details</legend>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="title">Asset Name / Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                            <input type="text" name="title" id="title" 
                                                   class="form-control @error('title') is-invalid @enderror" 
                                                   value="{{ old('title') }}" 
                                                   placeholder="e.g., Dell Latitude 7420 Laptop" required>
                                        </div>
                                        <small class="help-text">Enter the name or model of the asset you need</small>
                                        @error('title')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="asset_type_id">Asset Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('asset_type_id') is-invalid @enderror" 
                                                id="asset_type_id" name="asset_type_id" required>
                                            <option value="">Select Type</option>
                                            @foreach($assetTypes as $type)
                                                <option value="{{ $type->id }}" {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
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
                                                   value="{{ old('requested_quantity', 1) }}" min="1">
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
                                                   value="{{ old('unit') }}"
                                                   placeholder="e.g., pcs, set, unit">
                                        </div>
                                        <small class="help-text">Unit of measurement</small>
                                        @error('unit')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="priority">Priority</label>
                                        <select class="form-control @error('priority') is-invalid @enderror" 
                                                id="priority" name="priority">
                                            <option value="">Select Priority</option>
                                            @foreach($priorities as $priority)
                                                <option value="{{ $priority }}" {{ old('priority') == $priority ? 'selected' : '' }}>
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
                                                   value="{{ old('needed_date') }}">
                                        </div>
                                        <small class="help-text">When do you need this asset?</small>
                                        @error('needed_date')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Section 3: Justification --}}
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-file-alt"></i></span> Justification</legend>
                            
                            <div class="form-group">
                                <label for="justification">Business Justification <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('justification') is-invalid @enderror" 
                                          id="justification" name="justification" rows="6" required>{{ old('justification') }}</textarea>
                                <small class="help-text">
                                    <strong>Please explain:</strong>
                                    <ul style="margin: 5px 0 0 20px;">
                                        <li>Why is this asset needed?</li>
                                        <li>What will it be used for?</li>
                                        <li>What is the business impact if not provided?</li>
                                    </ul>
                                </small>
                                @error('justification')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Additional Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                <small class="help-text">Any additional information or special requirements</small>
                                @error('notes')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </fieldset>

                        {{-- Submit Buttons --}}
                        <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-paper-plane"></i> <b>Submit Request</b>
                            </button>
                            <a href="{{ route('asset-requests.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar (4 columns) --}}
        <div class="col-md-4">
            
            {{-- Request Guidelines --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> Request Guidelines</h3>
                </div>
                <div class="box-body">
                    <p><strong>Before submitting:</strong></p>
                    <ul style="margin-left: 20px;">
                        <li><i class="fa fa-check text-success"></i> Ensure the asset is necessary for your work</li>
                        <li><i class="fa fa-check text-success"></i> Check if similar assets are available</li>
                        <li><i class="fa fa-check text-success"></i> Get manager's verbal approval first</li>
                        <li><i class="fa fa-check text-success"></i> Provide detailed justification</li>
                    </ul>
                </div>
            </div>

            {{-- Priority Guide --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Priority Levels</h3>
                </div>
                <div class="box-body">
                    <div style="margin-bottom: 10px;">
                        <span class="label label-danger"><i class="fa fa-bolt"></i> Urgent</span>
                        <small class="text-muted" style="display: block; margin-top: 3px;">Critical - needed within 24-48 hours</small>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span class="label label-warning"><i class="fa fa-arrow-up"></i> High</span>
                        <small class="text-muted" style="display: block; margin-top: 3px;">Important - needed within 1 week</small>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span class="label label-info"><i class="fa fa-minus"></i> Medium</span>
                        <small class="text-muted" style="display: block; margin-top: 3px;">Normal - needed within 2-3 weeks</small>
                    </div>
                    <div>
                        <span class="label label-default"><i class="fa fa-arrow-down"></i> Low</span>
                        <small class="text-muted" style="display: block; margin-top: 3px;">Can wait - flexible timeline</small>
                    </div>
                </div>
            </div>

            {{-- Approval Process --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check-circle"></i> Approval Process</h3>
                </div>
                <div class="box-body">
                    <ol style="margin-left: 20px;">
                        <li><strong>Submit Request</strong> - Complete this form</li>
                        <li><strong>Under Review</strong> - IT reviews feasibility</li>
                        <li><strong>Approval</strong> - Management approves budget</li>
                        <li><strong>Fulfillment</strong> - Asset is procured/allocated</li>
                    </ol>
                    <p class="text-muted" style="margin-top: 10px;">
                        <i class="fa fa-clock"></i> Average approval time: 3-5 business days
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#asset-request-form').on('submit', function(e) {
        var isValid = true;
        
        // Check required fields
        if ($('#title').val().trim() === '') {
            alert('Please enter the asset name/title');
            $('#title').focus();
            return false;
        }
        
        if ($('#asset_type_id').val() === '') {
            alert('Please select an asset type');
            $('#asset_type_id').focus();
            return false;
        }
        
        if ($('#justification').val().trim().length < 20) {
            alert('Please provide a detailed justification (minimum 20 characters)');
            $('#justification').focus();
            return false;
        }
        
        return true;
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
