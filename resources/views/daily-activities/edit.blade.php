@extends('layouts.app')

@section('main-content')
    @include('components.page-header', [
        'title' => 'Edit Daily Activity',
        'subtitle' => 'Update your daily work activity information',
        'icon' => 'fa-edit',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'dashboard'],
            ['label' => 'Daily Activities', 'url' => route('daily-activities.index')],
            ['label' => 'Edit Activity']
        ]
    ])

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-warning"></i> Validation Errors:</h4>
            <ul style="margin-bottom: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Activity Metadata Alert --}}
    <div class="alert alert-info metadata-alert">
        <strong><i class="fa fa-info-circle"></i> Activity Information:</strong><br>
        <div class="row" style="margin-top: 10px;">
            <div class="col-sm-4">
                <strong>Activity ID:</strong> #{{ $dailyActivity->id }}
            </div>
            <div class="col-sm-4">
                <strong>Created:</strong> {{ $dailyActivity->created_at->format('M d, Y H:i') }}
            </div>
            <div class="col-sm-4">
                <strong>Last Updated:</strong> {{ $dailyActivity->updated_at->format('M d, Y H:i') }}
            </div>
        </div>
        @if(isset($dailyActivity->user))
            <div style="margin-top: 5px;">
                <strong>Logged by:</strong> {{ $dailyActivity->user->name ?? 'Unknown' }}
            </div>
        @endif
    </div>

    <section class="content">
        <form action="{{ route('daily-activities.update', $dailyActivity->id) }}" method="POST" id="editActivityForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                {{-- Main Form: 8 columns --}}
                <div class="col-xs-12 col-sm-8 col-md-8">
                    {{-- Section 1: Activity Details --}}
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-info-circle"></i> Activity Details
                            </h3>
                        </div>
                        <div class="box-body">
                            <fieldset>
                                <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span> Basic Information</legend>
                                
                                <div class="row">
                                    <div class="col-xs-12 col-sm-4 col-md-4">
                                        <div class="form-group">
                                            <label for="activity_date">
                                                <i class="fa fa-calendar"></i> Activity Date <span class="text-red">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('activity_date') is-invalid @enderror" 
                                                   id="activity_date" 
                                                   name="activity_date" 
                                                   value="{{ old('activity_date', $dailyActivity->activity_date->format('Y-m-d')) }}" 
                                                   required 
                                                   max="{{ date('Y-m-d') }}">
                                            <small class="help-text">Date cannot be in the future</small>
                                            @error('activity_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-4 col-md-4">
                                        <div class="form-group">
                                            <label for="activity_type">
                                                <i class="fa fa-tag"></i> Activity Type <span class="text-red">*</span>
                                            </label>
                                            <select class="form-control @error('activity_type') is-invalid @enderror" 
                                                    id="activity_type" 
                                                    name="activity_type" 
                                                    required>
                                                <option value="">Select Activity Type</option>
                                                <option value="ticket_handling" {{ old('activity_type', $dailyActivity->activity_type) == 'ticket_handling' ? 'selected' : '' }}>
                                                    Ticket Handling
                                                </option>
                                                <option value="asset_management" {{ old('activity_type', $dailyActivity->activity_type) == 'asset_management' ? 'selected' : '' }}>
                                                    Asset Management
                                                </option>
                                                <option value="user_support" {{ old('activity_type', $dailyActivity->activity_type) == 'user_support' ? 'selected' : '' }}>
                                                    User Support
                                                </option>
                                                <option value="system_maintenance" {{ old('activity_type', $dailyActivity->activity_type) == 'system_maintenance' ? 'selected' : '' }}>
                                                    System Maintenance
                                                </option>
                                                <option value="documentation" {{ old('activity_type', $dailyActivity->activity_type) == 'documentation' ? 'selected' : '' }}>
                                                    Documentation
                                                </option>
                                                <option value="training" {{ old('activity_type', $dailyActivity->activity_type) == 'training' ? 'selected' : '' }}>
                                                    Training
                                                </option>
                                                <option value="meeting" {{ old('activity_type', $dailyActivity->activity_type) == 'meeting' ? 'selected' : '' }}>
                                                    Meeting
                                                </option>
                                                <option value="project_work" {{ old('activity_type', $dailyActivity->activity_type) == 'project_work' ? 'selected' : '' }}>
                                                    Project Work
                                                </option>
                                                <option value="monitoring" {{ old('activity_type', $dailyActivity->activity_type) == 'monitoring' ? 'selected' : '' }}>
                                                    System Monitoring
                                                </option>
                                                <option value="other" {{ old('activity_type', $dailyActivity->activity_type) == 'other' ? 'selected' : '' }}>
                                                    Other
                                                </option>
                                            </select>
                                            <small class="help-text">Choose the category that best fits this activity</small>
                                            @error('activity_type')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-4 col-md-4">
                                        <div class="form-group">
                                            <label for="duration_minutes">
                                                <i class="fa fa-clock-o"></i> Duration (minutes) <span class="text-red">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control @error('duration_minutes') is-invalid @enderror" 
                                                   id="duration_minutes" 
                                                   name="duration_minutes" 
                                                   value="{{ old('duration_minutes', $dailyActivity->duration_minutes ?? '') }}" 
                                                   required 
                                                   min="1" 
                                                   max="1440">
                                            <small class="help-text">Maximum 1440 minutes (24 hours)</small>
                                            @error('duration_minutes')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">
                                        <i class="fa fa-file-text"></i> Description <span class="text-red">*</span>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              required 
                                              minlength="20" 
                                              maxlength="1000">{{ old('description', $dailyActivity->description) }}</textarea>
                                    <div class="char-counter">
                                        <span id="descCharCount">0</span> / 1000 characters
                                    </div>
                                    <small class="help-text">
                                        Describe what you did in detail (minimum 20 characters)
                                    </small>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    {{-- Section 2: Time Tracking --}}
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-clock-o"></i> Time Tracking
                            </h3>
                        </div>
                        <div class="box-body">
                            <fieldset>
                                <legend><span class="form-section-icon"><i class="fa fa-clock-o"></i></span> Time Details</legend>
                                
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="start_time">
                                                <i class="fa fa-play-circle"></i> Start Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control @error('start_time') is-invalid @enderror" 
                                                   id="start_time" 
                                                   name="start_time" 
                                                   value="{{ old('start_time', $dailyActivity->start_time ? $dailyActivity->start_time->format('H:i') : '') }}">
                                            <small class="help-text">Optional - When did you start this activity?</small>
                                            @error('start_time')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="end_time">
                                                <i class="fa fa-stop-circle"></i> End Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control @error('end_time') is-invalid @enderror" 
                                                   id="end_time" 
                                                   name="end_time" 
                                                   value="{{ old('end_time', $dailyActivity->end_time ? $dailyActivity->end_time->format('H:i') : '') }}">
                                            <small class="help-text">Optional - When did you finish this activity?</small>
                                            @error('end_time')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="location">
                                        <i class="fa fa-map-marker"></i> Work Location
                                    </label>
                                    <select class="form-control @error('location') is-invalid @enderror" 
                                            id="location" 
                                            name="location">
                                        <option value="">Select Location</option>
                                        <option value="office" {{ old('location', $dailyActivity->location ?? '') == 'office' ? 'selected' : '' }}>
                                            Office
                                        </option>
                                        <option value="remote" {{ old('location', $dailyActivity->location ?? '') == 'remote' ? 'selected' : '' }}>
                                            Remote/Work From Home
                                        </option>
                                        <option value="client_site" {{ old('location', $dailyActivity->location ?? '') == 'client_site' ? 'selected' : '' }}>
                                            Client Site/User Location
                                        </option>
                                        <option value="field_work" {{ old('location', $dailyActivity->location ?? '') == 'field_work' ? 'selected' : '' }}>
                                            Field Work
                                        </option>
                                        <option value="other" {{ old('location', $dailyActivity->location ?? '') == 'other' ? 'selected' : '' }}>
                                            Other
                                        </option>
                                    </select>
                                    <small class="help-text">Where did you perform this activity?</small>
                                    @error('location')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    {{-- Section 3: Additional Information --}}
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-link"></i> Additional Information
                            </h3>
                        </div>
                        <div class="box-body">
                            <fieldset>
                                <legend><span class="form-section-icon"><i class="fa fa-sticky-note"></i></span> Extra Details</legend>
                                
                                <div class="form-group">
                                    <label for="ticket_id">
                                        <i class="fa fa-ticket"></i> Related Ticket
                                    </label>
                                    <select class="form-control select2 @error('ticket_id') is-invalid @enderror" 
                                            id="ticket_id" 
                                            name="ticket_id">
                                        <option value="">Select Ticket (if applicable)</option>
                                        @if(isset($tickets))
                                            @foreach($tickets as $ticket)
                                                <option value="{{ $ticket->id }}" {{ old('ticket_id', $dailyActivity->ticket_id ?? '') == $ticket->id ? 'selected' : '' }}>
                                                    #{{ $ticket->id }} - {{ \Illuminate\Support\Str::limit($ticket->subject ?? 'No Subject', 60) }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="help-text">Link this activity to a ticket if it's related to ticket work</small>
                                    @error('ticket_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="notes">
                                        <i class="fa fa-sticky-note"></i> Additional Notes
                                    </label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              maxlength="500">{{ old('notes', $dailyActivity->notes ?? '') }}</textarea>
                                    <small class="help-text">Any additional information useful for reporting or follow-up</small>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="box box-default">
                        <div class="box-body">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> Update Activity
                            </button>
                            <a href="{{ route('daily-activities.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-arrow-left"></i> Cancel
                            </a>
                            <a href="{{ route('daily-activities.show', $dailyActivity->id) }}" class="btn btn-info btn-lg">
                                <i class="fa fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: 4 columns --}}
                <div class="col-xs-12 col-sm-4 col-md-4">
                    {{-- Edit Tips --}}
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Edit Tips</h3>
                        </div>
                        <div class="box-body">
                            <p style="font-size: 13px; line-height: 1.6;">
                                <strong>Important Reminders:</strong>
                            </p>
                            <ul style="font-size: 12px; line-height: 1.8;">
                                <li><strong>Be Accurate:</strong> Update information to reflect actual work done</li>
                                <li><strong>Check Dates:</strong> Ensure activity date is correct</li>
                                <li><strong>Update Time:</strong> Adjust start/end times if they changed</li>
                                <li><strong>Link Tickets:</strong> Associate with correct ticket if applicable</li>
                                <li><strong>Save Changes:</strong> Don't forget to click Update Activity</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Activity History --}}
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-history"></i> Activity History</h3>
                        </div>
                        <div class="box-body">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Created</span>
                                    <span class="info-box-number">{{ $dailyActivity->created_at->format('M d, Y') }}</span>
                                    <small>{{ $dailyActivity->created_at->format('H:i') }}</small>
                                </div>
                            </div>

                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-edit"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Last Updated</span>
                                    <span class="info-box-number">{{ $dailyActivity->updated_at->format('M d, Y') }}</span>
                                    <small>{{ $dailyActivity->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>

                            @if(isset($dailyActivity->user))
                                <div class="info-box bg-green">
                                    <span class="info-box-icon"><i class="fa fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Logged By</span>
                                        <span class="info-box-number">{{ $dailyActivity->user->name }}</span>
                                        <small>{{ $dailyActivity->user->email }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="box-body">
                            <a href="{{ route('daily-activities.index') }}" class="btn btn-default btn-block">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="{{ route('daily-activities.show', $dailyActivity->id) }}" class="btn btn-info btn-block">
                                <i class="fa fa-eye"></i> View Full Details
                            </a>
                            <a href="{{ route('daily-activities.create') }}" class="btn btn-primary btn-block">
                                <i class="fa fa-plus"></i> Add New Activity
                            </a>
                            <hr>
                            <form action="{{ route('daily-activities.destroy', $dailyActivity->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this activity? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fa fa-trash"></i> Delete Activity
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for ticket dropdown
    $('.select2').select2({
        theme: 'bootstrap',
        placeholder: 'Select a ticket...',
        allowClear: true
    });
    
    // Character counter for description
    const descriptionTextarea = $('#description');
    const charCounter = $('#descCharCount');
    
    function updateCharCounter() {
        const currentLength = descriptionTextarea.val().length;
        charCounter.text(currentLength);
        
        // Color coding
        if (currentLength < 20) {
            charCounter.parent().removeClass('valid').addClass('invalid');
        } else {
            charCounter.parent().removeClass('invalid').addClass('valid');
        }
    }
    
    descriptionTextarea.on('input', updateCharCounter);
    updateCharCounter(); // Initial update
    
    // Calculate duration based on start and end time
    const startTimeInput = $('#start_time');
    const endTimeInput = $('#end_time');
    const durationInput = $('#duration_minutes');
    
    function calculateDuration() {
        if (startTimeInput.val() && endTimeInput.val()) {
            const start = new Date('2000-01-01 ' + startTimeInput.val());
            const end = new Date('2000-01-01 ' + endTimeInput.val());
            
            if (end > start) {
                const diffMs = end - start;
                const diffMinutes = Math.round(diffMs / (1000 * 60));
                durationInput.val(diffMinutes);
            } else {
                alert('End time must be after start time');
                endTimeInput.val('');
            }
        }
    }
    
    startTimeInput.on('change', calculateDuration);
    endTimeInput.on('change', calculateDuration);
    
    // Auto-calculate end time based on duration
    durationInput.on('input', function() {
        if (startTimeInput.val() && $(this).val()) {
            const minutes = parseInt($(this).val());
            if (minutes > 0 && minutes <= 1440) {
                const start = new Date('2000-01-01 ' + startTimeInput.val());
                start.setMinutes(start.getMinutes() + minutes);
                const hours = String(start.getHours()).padStart(2, '0');
                const mins = String(start.getMinutes()).padStart(2, '0');
                endTimeInput.val(hours + ':' + mins);
            }
        }
    });
    
    // Form validation before submit
    $('#editActivityForm').on('submit', function(e) {
        const description = descriptionTextarea.val();
        const duration = durationInput.val();
        
        if (description.length < 20) {
            e.preventDefault();
            alert('Description must be at least 20 characters long. Please provide more detail about your activity.');
            descriptionTextarea.focus();
            return false;
        }
        
        if (duration < 1 || duration > 1440) {
            e.preventDefault();
            alert('Duration must be between 1 and 1440 minutes (24 hours).');
            durationInput.focus();
            return false;
        }
        
        return true;
    });
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush
@endsection
