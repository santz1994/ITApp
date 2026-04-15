@extends('layouts.app')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-edit"></i> Edit SLA Policy
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('sla.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('sla.update', $policy->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="box-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h5><i class="fa fa-exclamation-triangle"></i> Validation Errors:</h5>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">
                                        Policy Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $policy->name) }}" 
                                           placeholder="e.g., Urgent Priority SLA"
                                           required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        A descriptive name for this SLA policy
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority_id">
                                        Ticket Priority <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('priority_id') is-invalid @enderror" 
                                            id="priority_id" 
                                            name="priority_id" 
                                            required>
                                        <option value="">Select Priority</option>
                                        @foreach($priorities as $priority)
                                            <option value="{{ $priority->id }}" 
                                                    {{ old('priority_id', $policy->priority_id) == $priority->id ? 'selected' : '' }}>
                                                {{ $priority->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('priority_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        This policy will apply to tickets with this priority
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="Describe when and how this SLA policy should be used">{{ old('description', $policy->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SLA Timeframes -->
                        <h4>
                            <i class="fa fa-clock-o"></i> SLA Timeframes
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="response_time">
                                        First Response Time (minutes) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('response_time') is-invalid @enderror" 
                                           id="response_time" 
                                           name="response_time" 
                                           value="{{ old('response_time', $policy->response_time) }}" 
                                           min="1"
                                           placeholder="e.g., 60"
                                           required>
                                    @error('response_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Time allowed for first response (e.g., 60 = 1 hour, 1440 = 1 day)
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="resolution_time">
                                        Resolution Time (minutes) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('resolution_time') is-invalid @enderror" 
                                           id="resolution_time" 
                                           name="resolution_time" 
                                           value="{{ old('resolution_time', $policy->resolution_time) }}" 
                                           min="1"
                                           placeholder="e.g., 240"
                                           required>
                                    @error('resolution_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Time allowed to fully resolve the ticket (e.g., 240 = 4 hours, 4320 = 3 days)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="business_hours_only" 
                                               name="business_hours_only" 
                                               value="1"
                                               {{ old('business_hours_only', $policy->business_hours_only) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="business_hours_only">
                                            Calculate SLA during business hours only
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle"></i> 
                                        Business hours: Monday to Friday, 8:00 AM - 5:00 PM. 
                                        When checked, SLA calculations will exclude weekends and after-hours.
                                        Uncheck for 24/7 SLA calculation.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Escalation Settings -->
                        <h4>
                            <i class="fa fa-exclamation-triangle"></i> Escalation Settings
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="escalation_time">
                                        Escalation Time (minutes)
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('escalation_time') is-invalid @enderror" 
                                           id="escalation_time" 
                                           name="escalation_time" 
                                           value="{{ old('escalation_time', $policy->escalation_time) }}" 
                                           min="1"
                                           placeholder="e.g., 120">
                                    @error('escalation_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Time before ticket should be escalated. Leave empty to disable auto-escalation.
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="escalate_to_user_id">
                                        Escalate To User
                                    </label>
                                    <select class="form-control @error('escalate_to_user_id') is-invalid @enderror" 
                                            id="escalate_to_user_id" 
                                            name="escalate_to_user_id">
                                        <option value="">Select User (Optional)</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                    {{ old('escalate_to_user_id', $policy->escalate_to_user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('escalate_to_user_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        User to receive escalated tickets. Leave empty if escalation is not needed.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $policy->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            <strong>Active Policy</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted ml-4">
                                        Only active policies will be applied to tickets
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Time Presets -->
                        <div class="alert alert-info">
                            <h6><i class="fa fa-lightbulb-o"></i> Quick Time Reference:</h6>
                            <ul>
                                <li><strong>1 hour</strong> = 60 minutes</li>
                                <li><strong>4 hours</strong> = 240 minutes</li>
                                <li><strong>1 day</strong> = 1440 minutes</li>
                                <li><strong>3 days</strong> = 4320 minutes</li>
                                <li><strong>1 week</strong> = 10080 minutes</li>
                            </ul>
                        </div>

                        <!-- Metadata -->
                        <div class="callout callout-warning">
                            <small class="text-muted">
                                <strong>Created:</strong> {{ $policy->created_at->format('Y-m-d H:i:s') }}
                                @if($policy->updated_at)
                                    | <strong>Last Updated:</strong> {{ $policy->updated_at->format('Y-m-d H:i:s') }}
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update SLA Policy
                        </button>
                        <a href="{{ route('sla.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                        @can('delete', $policy)
                            <button type="button" 
                                    class="btn btn-danger pull-right" 
                                    onclick="deletePolicy()">
                                <i class="fa fa-trash"></i> Delete Policy
                            </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('sla.destroy', $policy->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<style>
    /* Custom form styling for SLA policy forms */

    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control, .custom-select {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 0.65rem 0.9rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-control:focus, .custom-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }

    .alert {
        border-radius: 8px;
        border-left: 4px solid;
        padding: 1rem 1.25rem;
    }

    .alert-info {
        background-color: #e7f3ff;
        border-left-color: #0099ff;
        color: #004085;
    }

    .alert-info h6 {
        color: #003d73;
        font-weight: 600;
    }

    .alert-secondary {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-left: 4px solid #6c757d;
    }

    .custom-control-label {
        font-weight: 500;
        padding-top: 0.15rem;
    }

    .box-body h4 {
        color: #007bff;
        font-weight: 600;
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
        margin-top: 20px;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .invalid-feedback {
        font-size: 0.875rem;
    }
</style>
@endpush

@section('scripts')
<script>
function deletePolicy() {
    if (confirm('Are you sure you want to delete this SLA policy? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
