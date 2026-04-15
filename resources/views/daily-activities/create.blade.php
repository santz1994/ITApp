@extends('layouts.app')

@section('main-content')
    @include('components.page-header', [
        'title' => 'Add Daily Activity',
        'subtitle' => 'Log your daily work activities and time tracking',
        'icon' => 'fa-plus-circle',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'dashboard'],
            ['label' => 'Daily Activities', 'url' => route('daily-activities.index')],
            ['label' => 'Add Activity']
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

    <section class="content">
        <form action="{{ route('daily-activities.store') }}" method="POST" id="activityForm">
            @csrf
            
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
                                                   value="{{ old('activity_date', $today ?? today()->format('Y-m-d')) }}" 
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
                                                <option value="ticket_handling" {{ old('activity_type') == 'ticket_handling' ? 'selected' : '' }}>
                                                    Ticket Handling
                                                </option>
                                                <option value="asset_management" {{ old('activity_type') == 'asset_management' ? 'selected' : '' }}>
                                                    Asset Management
                                                </option>
                                                <option value="user_support" {{ old('activity_type') == 'user_support' ? 'selected' : '' }}>
                                                    User Support
                                                </option>
                                                <option value="system_maintenance" {{ old('activity_type') == 'system_maintenance' ? 'selected' : '' }}>
                                                    System Maintenance
                                                </option>
                                                <option value="documentation" {{ old('activity_type') == 'documentation' ? 'selected' : '' }}>
                                                    Documentation
                                                </option>
                                                <option value="training" {{ old('activity_type') == 'training' ? 'selected' : '' }}>
                                                    Training
                                                </option>
                                                <option value="meeting" {{ old('activity_type') == 'meeting' ? 'selected' : '' }}>
                                                    Meeting
                                                </option>
                                                <option value="project_work" {{ old('activity_type') == 'project_work' ? 'selected' : '' }}>
                                                    Project Work
                                                </option>
                                                <option value="monitoring" {{ old('activity_type') == 'monitoring' ? 'selected' : '' }}>
                                                    System Monitoring
                                                </option>
                                                <option value="other" {{ old('activity_type') == 'other' ? 'selected' : '' }}>
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
                                                   value="{{ old('duration_minutes') }}" 
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
                                              maxlength="1000">{{ old('description') }}</textarea>
                                    <div class="char-counter">
                                        <span id="descCharCount">0</span> / 1000 characters
                                    </div>
                                    <small class="help-text">
                                        Describe what you did in detail (minimum 20 characters):
                                        <ul style="margin-top: 5px; font-size: 11px;">
                                            <li>What was done?</li>
                                            <li>Who was involved?</li>
                                            <li>What was achieved?</li>
                                            <li>Tools/software used</li>
                                        </ul>
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
                                                   value="{{ old('start_time') }}">
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
                                                   value="{{ old('end_time') }}">
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
                                        <option value="office" {{ old('location') == 'office' ? 'selected' : '' }}>
                                            Office
                                        </option>
                                        <option value="remote" {{ old('location') == 'remote' ? 'selected' : '' }}>
                                            Remote/Work From Home
                                        </option>
                                        <option value="client_site" {{ old('location') == 'client_site' ? 'selected' : '' }}>
                                            Client Site/User Location
                                        </option>
                                        <option value="field_work" {{ old('location') == 'field_work' ? 'selected' : '' }}>
                                            Field Work
                                        </option>
                                        <option value="other" {{ old('location') == 'other' ? 'selected' : '' }}>
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

                    {{-- Section 3: Ticket Association --}}
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-link"></i> Ticket Association
                            </h3>
                        </div>
                        <div class="box-body">
                            <fieldset>
                                <legend><span class="form-section-icon"><i class="fa fa-ticket"></i></span> Related Items</legend>
                                
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
                                                <option value="{{ $ticket->id }}" {{ old('ticket_id') == $ticket->id ? 'selected' : '' }}>
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
                                    <label for="technologies_used">
                                        <i class="fa fa-cogs"></i> Technologies/Tools Used
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('technologies_used') is-invalid @enderror" 
                                           id="technologies_used" 
                                           name="technologies_used" 
                                           value="{{ old('technologies_used') }}" 
                                           maxlength="500">
                                    <small class="help-text">
                                        Comma-separated. Example: Windows Server, Active Directory, PowerShell
                                    </small>
                                    @error('technologies_used')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="outcome_achieved">
                                        <i class="fa fa-check-circle"></i> Outcome Achieved
                                    </label>
                                    <textarea class="form-control @error('outcome_achieved') is-invalid @enderror" 
                                              id="outcome_achieved" 
                                              name="outcome_achieved" 
                                              rows="3" 
                                              maxlength="500">{{ old('outcome_achieved') }}</textarea>
                                    <small class="help-text">What concrete results were achieved? What problems were solved?</small>
                                    @error('outcome_achieved')
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
                                              rows="2" 
                                              maxlength="500">{{ old('notes') }}</textarea>
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
                                <i class="fa fa-save"></i> Save Activity
                            </button>
                            <a href="{{ route('daily-activities.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-arrow-left"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-info btn-lg" id="saveAndAddAnother">
                                <i class="fa fa-plus-circle"></i> Save & Add Another
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: 4 columns --}}
                <div class="col-xs-12 col-sm-4 col-md-4">
                    {{-- Activity Guidelines --}}
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Activity Guidelines</h3>
                        </div>
                        <div class="box-body">
                            <p style="font-size: 13px; line-height: 1.6;">
                                <strong>How to Log Activities:</strong>
                            </p>
                            <ul style="font-size: 12px; line-height: 1.8;">
                                <li><strong>Be Specific:</strong> Describe exactly what you did</li>
                                <li><strong>Include Context:</strong> Mention who, what, where, why</li>
                                <li><strong>Track Time:</strong> Log start/end times for accuracy</li>
                                <li><strong>Link Tickets:</strong> Associate with related tickets</li>
                                <li><strong>Note Tools:</strong> List software/tools used</li>
                                <li><strong>Record Results:</strong> What was achieved?</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Quick Templates --}}
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file-text-o"></i> Quick Templates</h3>
                        </div>
                        <div class="box-body">
                            <p style="font-size: 12px; margin-bottom: 10px;"><strong>Click to use:</strong></p>
                            <div class="list-group">
                                <a href="#" class="list-group-item template-quick" data-template="Handled ticket #[TICKET] regarding [ISSUE]. Resolution: [SOLUTION]. User satisfied with outcome.">
                                    <i class="fa fa-ticket text-blue"></i> Ticket Resolution
                                </a>
                                <a href="#" class="list-group-item template-quick" data-template="Performed asset inventory check for [LOCATION]. Updated [COUNT] asset records. All assets accounted for.">
                                    <i class="fa fa-cube text-green"></i> Asset Inventory
                                </a>
                                <a href="#" class="list-group-item template-quick" data-template="Provided on-site support to [USER] for [ISSUE]. Issue resolved. Training provided on [TOPIC].">
                                    <i class="fa fa-life-ring text-yellow"></i> User Support
                                </a>
                                <a href="#" class="list-group-item template-quick" data-template="System maintenance: [SYSTEM]. Updated software, applied patches, verified backups. All systems operational.">
                                    <i class="fa fa-cogs text-red"></i> Maintenance
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Help & Tips --}}
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-question-circle"></i> Help & Tips</h3>
                        </div>
                        <div class="box-body">
                            <p style="font-size: 12px; line-height: 1.6;">
                                <strong><i class="fa fa-clock-o"></i> Time Calculation:</strong><br>
                                Enter start and end times, and duration will auto-calculate, or vice versa.
                            </p>
                            <hr>
                            <p style="font-size: 12px; line-height: 1.6;">
                                <strong><i class="fa fa-calendar"></i> Best Practice:</strong><br>
                                Log activities daily to ensure accurate time tracking and reporting.
                            </p>
                            <hr>
                            <p style="font-size: 12px; line-height: 1.6;">
                                <strong><i class="fa fa-link"></i> Ticket Linking:</strong><br>
                                Always link activities to tickets when working on ticket-related tasks.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

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
    
    // Set current time when focus on start time
    startTimeInput.on('focus', function() {
        if (!$(this).val()) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            $(this).val(hours + ':' + minutes);
        }
    });
    
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
    
    // Auto-fill template when activity type changes
    const activityTypeSelect = $('#activity_type');
    
    activityTypeSelect.on('change', function() {
        if (!descriptionTextarea.val()) { // Only if description is empty
            const templates = {
                'ticket_handling': 'Handled ticket #[TICKET_NUMBER] regarding [ISSUE_SUMMARY]. Resolution: [SOLUTION_PROVIDED]. User confirmed issue is resolved.',
                'asset_management': 'Performed asset management tasks: [TASK_DESCRIPTION]. Updated [COUNT] asset records. All assets verified and documented.',
                'user_support': 'Provided on-site/remote support to [USER_NAME] for [ISSUE_TYPE]. Resolution: [SOLUTION]. User trained on [TOPIC].',
                'system_maintenance': 'Performed system maintenance on [SYSTEM_NAME]. Tasks completed: [TASK_LIST]. All systems operational and verified.',
                'documentation': 'Created/updated documentation for [TOPIC]. Document includes [SECTIONS]. Ready for team review.',
                'training': 'Attended/conducted training session on [TOPIC]. Duration: [DURATION]. Key learnings: [SUMMARY].',
                'meeting': 'Attended meeting regarding [TOPIC]. Attendees: [PARTICIPANTS]. Key decisions: [DECISIONS]. Action items: [ACTIONS].',
                'project_work': 'Worked on project [PROJECT_NAME]. Completed: [TASKS]. Next steps: [NEXT_ACTIONS].',
                'monitoring': 'Monitored system/infrastructure: [SYSTEM_NAME]. All services operational. No issues detected.',
                'other': '[Describe your activity in detail...]'
            };
            
            if (templates[$(this).val()]) {
                descriptionTextarea.val(templates[$(this).val()]);
                updateCharCounter();
                descriptionTextarea.focus();
                // Move cursor to end
                const textLength = descriptionTextarea.val().length;
                descriptionTextarea[0].setSelectionRange(textLength, textLength);
            }
        }
    });
    
    // Handle "Save & Add Another" button
    $('#saveAndAddAnother').on('click', function() {
        const form = $('#activityForm');
        $('<input>').attr({
            type: 'hidden',
            name: 'add_another',
            value: '1'
        }).appendTo(form);
        form.submit();
    });
    
    // Quick template click - insert into description
    $('.template-quick').on('click', function(e) {
        e.preventDefault();
        const template = $(this).data('template');
        descriptionTextarea.val(template);
        updateCharCounter();
        descriptionTextarea.focus();
        
        // Visual feedback
        $(this).addClass('active');
        setTimeout(() => {
            $(this).removeClass('active');
        }, 500);
    });
    
    // Form validation before submit
    $('#activityForm').on('submit', function(e) {
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

