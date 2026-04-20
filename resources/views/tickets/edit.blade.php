@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

@include('components.page-header', [
    'title' => 'Edit Ticket #' . $ticket->ticket_code,
    'subtitle' => 'Update ticket details',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Tickets', 'url' => route('tickets.index')],
        ['label' => 'Edit #' . $ticket->ticket_code]
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('tickets.show', $ticket).'" class="btn btn-info">
                <i class="fa fa-eye"></i> <span class="hidden-xs" data-i18n="tickets.edit.action.view">View</span>
            </a>
            <a href="'.route('tickets.index').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <span class="hidden-xs" data-i18n="tickets.edit.action.back">Back</span>
            </a>
        </div>
    '
])

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'ticketEditLanguageEnglish',
    'indonesianButtonId' => 'ticketEditLanguageIndonesian',
    'ariaLabel' => 'Ticket Edit Language Toggle',
])

<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><span data-i18n="tickets.edit.form.title_prefix">Edit Ticket</span> #{{ $ticket->ticket_code }}</h3>
            </div>
            <div class="box-body">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="icon fa fa-check"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="icon fa fa-ban"></i> {{ session('error') }}
                    </div>
                @endif

                {{-- Ticket Metadata --}}
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> <strong>Ticket Info:</strong>
                    Created: {{ $ticket->created_at ? $ticket->created_at->format('d M Y H:i') : 'N/A' }} by {{ $ticket->user->name ?? 'N/A' }} |
                    Last Updated: {{ $ticket->updated_at ? $ticket->updated_at->format('d M Y H:i') : 'N/A' }}
                    @if($ticket->resolved_at)
                        | <span class="text-success"><i class="fa fa-check-circle"></i> Resolved: {{ $ticket->resolved_at->format('d M Y H:i') }}</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('tickets.update', $ticket) }}" id="ticket-edit-form">
                    @csrf
                    @method('PUT')
                    
                    {{-- SECTION 1: Basic Information --}}
                    <fieldset>
                        <legend><i class="fa fa-info-circle"></i> <span data-i18n="tickets.edit.section.basic">Basic Information</span></legend>

                        <div class="form-group">
                            <label for="subject">Subject <span class="text-red">*</span></label>
                            <input type="text" 
                                   class="form-control @error('subject') is-invalid @enderror" 
                                   name="subject" 
                                   id="subject"
                                   value="{{ old('subject', $ticket->subject) }}" 
                                   required maxlength="255">
                            <small class="text-muted">Brief summary of the issue (max 255 characters)</small>
                            @error('subject')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span class="text-red">*</span></label>
                            <span id="char-counter">0 / 10 characters (minimum 10)</span>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" 
                                      id="description" 
                                      rows="5" 
                                      required minlength="10">{{ old('description', $ticket->description) }}</textarea>
                            <small class="text-muted">Detailed description of the issue or request (minimum 10 characters)</small>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ticket_type_id">Ticket Type <span class="text-red">*</span></label>
                            <select class="form-control @error('ticket_type_id') is-invalid @enderror" 
                                    name="ticket_type_id" 
                                    id="ticket_type_id" 
                                    required>
                                <option value="">-- Select Ticket Type --</option>
                                @foreach($ticketsTypes as $type)
                                    <option value="{{ $type->id }}" 
                                            {{ old('ticket_type_id', $ticket->ticket_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->type }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Category of request (e.g., Hardware Issue, Software Support, Network Problem)</small>
                            @error('ticket_type_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ticket_priority_id">Priority <span class="text-red">*</span></label>
                            <select class="form-control @error('ticket_priority_id') is-invalid @enderror" 
                                    name="ticket_priority_id" 
                                    id="ticket_priority_id" 
                                    required>
                                <option value="">-- Select Priority --</option>
                                @foreach($ticketsPriorities as $priority)
                                    <option value="{{ $priority->id }}" 
                                            data-sla-hours="{{ $priority->priority == 'High' ? 4 : ($priority->priority == 'Medium' ? 24 : 48) }}"
                                            {{ old('ticket_priority_id', $ticket->ticket_priority_id) == $priority->id ? 'selected' : '' }}>
                                        {{ $priority->priority }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Urgency level - affects SLA due date (High = urgent, Medium = normal, Low = can wait)</small>
                            
                            {{-- SLA Change Preview (only if priority changes) --}}
                            @if($ticket->ticket_priority_id)
                                <div id="sla-change-preview" class="alert alert-warning" style="margin-top: 10px; display: none;">
                                    <i class="fa fa-exclamation-triangle"></i> <strong>Priority Changed!</strong>
                                    <br>New SLA Due Date: <span id="new-sla-due-date"></span>
                                    <br><small>Old due date: {{ $ticket->sla_due ? $ticket->sla_due->format('D, M j \a\t g:i A') : 'Not set' }}</small>
                                </div>
                            @endif
                            
                            @error('ticket_priority_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ticket_status_id">Status <span class="text-red">*</span></label>
                            <select class="form-control @error('ticket_status_id') is-invalid @enderror" 
                                    name="ticket_status_id" 
                                    id="ticket_status_id" 
                                    required>
                                <option value="">-- Select Status --</option>
                                @foreach($ticketsStatuses as $status)
                                    <option value="{{ $status->id }}" 
                                            data-status-name="{{ strtolower($status->status) }}"
                                            {{ old('ticket_status_id', $ticket->ticket_status_id) == $status->id ? 'selected' : '' }}>
                                        {{ $status->status }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Current ticket status</small>
                            
                            {{-- Status Change Warning --}}
                            <div id="status-change-warning" class="alert alert-info" style="margin-top: 10px; display: none;">
                                <i class="fa fa-info-circle"></i> <strong>Note:</strong>
                                <span id="status-warning-text"></span>
                            </div>
                            
                            @error('ticket_status_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- SLA Due Date - Only Super-Admin can edit --}}
                        @if(auth()->user()->hasRole('super-admin'))
                        <div class="form-group">
                            <label for="sla_due">SLA Due Date <span class="badge bg-purple">Super Admin Only</span></label>
                            <input type="datetime-local" 
                                   class="form-control @error('sla_due') is-invalid @enderror" 
                                   name="sla_due" 
                                   id="sla_due"
                                   value="{{ old('sla_due', $ticket->sla_due ? $ticket->sla_due->format('Y-m-d\TH:i') : '') }}">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Override automatic SLA calculation. Leave blank to auto-calculate based on priority.
                                @if($ticket->sla_due)
                                    <br><strong>Current:</strong> {{ $ticket->sla_due->format('D, M j, Y \a\t g:i A') }}
                                @endif
                            </small>
                            @error('sla_due')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                        {{-- Show SLA as read-only for non-super-admins --}}
                        @if($ticket->sla_due)
                        <div class="form-group">
                            <label>SLA Due Date</label>
                            <p class="form-control-static">
                                @php
                                    $now = now();
                                    $isOverdue = $now->gt($ticket->sla_due);
                                @endphp
                                @if($isOverdue)
                                    <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> {{ $ticket->sla_due->format('D, M j, Y \a\t g:i A') }} (Overdue)</span>
                                @else
                                    <span class="text-success"><i class="fa fa-clock-o"></i> {{ $ticket->sla_due->format('D, M j, Y \a\t g:i A') }}</span>
                                @endif
                            </p>
                            <small class="text-muted">SLA is automatically calculated. Contact Super-Admin to modify.</small>
                        </div>
                        @endif
                        @endif
                    </fieldset>

                    {{-- SECTION 2: Assignment & Location --}}
                    <fieldset>
                        <legend><i class="fa fa-user"></i> <span data-i18n="tickets.edit.section.assignment">Assignment & Location</span></legend>

                        <div class="form-group">
                            <label for="assigned_to">Assigned To (Agent)</label>
                            <select class="form-control @error('assigned_to') is-invalid @enderror" 
                                    name="assigned_to" 
                                    id="assigned_to">
                                <option value="">-- Unassigned --</option>
                                @foreach($assignableUsers as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('assigned_to', $ticket->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email ?? 'No Email' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select an admin or super-admin to handle this ticket (optional)</small>
                            @error('assigned_to')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location_id">Location <span class="text-red">*</span></label>
                            <select class="form-control @error('location_id') is-invalid @enderror" 
                                    name="location_id" 
                                    id="location_id" required>
                                <option value="">-- Select Location --</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" 
                                            {{ old('location_id', $ticket->location_id) == $location->id ? 'selected' : '' }}>
                                        {{ $location->location_name }} - {{ $location->building }}, {{ $location->office }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Physical location where issue is occurring</small>
                            @error('location_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </fieldset>

                    {{-- SECTION 3: Asset Association --}}
                    <fieldset>
                        <legend><i class="fa fa-laptop"></i> <span data-i18n="tickets.edit.section.asset">Asset Association</span></legend>

                        <div class="form-group">
                            <label for="asset_id">Related Assets (Optional)</label>
                            <select class="form-control @error('asset_ids') is-invalid @enderror @error('asset_ids.*') is-invalid @enderror" 
                                    name="asset_ids[]" 
                                    id="asset_id" multiple>
                                @php $selectedAssets = old('asset_ids', $ticket->assets->pluck('id')->toArray()); @endphp
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" 
                                            {{ in_array($asset->id, $selectedAssets ?? []) ? 'selected' : '' }}>
                                        {{ $asset->model_name ? $asset->model_name : 'Unknown Model' }} ({{ $asset->asset_tag }}) - {{ $asset->location ? $asset->location->location_name : 'No Location' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select one or more assets related to this ticket (use Ctrl/Cmd + Click for multiple)</small>
                            
                            {{-- Asset Change Preview --}}
                            <div id="asset-change-summary" class="alert alert-success" style="margin-top: 10px; display: none;">
                                <i class="fa fa-info-circle"></i> <strong>Asset Changes:</strong>
                                <div id="assets-added" style="display: none;">
                                    <small><i class="fa fa-plus-circle text-success"></i> <strong>Adding:</strong> <span id="assets-added-list"></span></small>
                                </div>
                                <div id="assets-removed" style="display: none;">
                                    <small><i class="fa fa-minus-circle text-danger"></i> <strong>Removing:</strong> <span id="assets-removed-list"></span></small>
                                </div>
                            </div>
                            
                            @error('asset_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('asset_ids.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </fieldset>

                    {{-- Submit Buttons --}}
                    <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> <b data-i18n="tickets.edit.action.submit">Update Ticket</b>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- SIDEBAR: Ticket Information & Help --}}
    <div class="col-xs-12 col-sm-4 col-md-4">
        {{-- Ticket Details --}}
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-ticket"></i> Ticket Details</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal" style="margin-bottom: 0;">
                    <dt>Ticket Code:</dt>
                    <dd><strong class="text-primary">{{ $ticket->ticket_code }}</strong></dd>
                    
                    <dt>Created By:</dt>
                    <dd>{{ $ticket->user->name ?? 'N/A' }}</dd>
                    
                    <dt>Created At:</dt>
                    <dd>{{ $ticket->created_at->format('M j, Y H:i') }}</dd>
                    
                    <dt>Last Updated:</dt>
                    <dd>{{ $ticket->updated_at->format('M j, Y H:i') }}</dd>
                    
                    @if($ticket->resolved_at)
                        <dt>Resolved At:</dt>
                        <dd><span class="text-success"><i class="fa fa-check-circle"></i> {{ $ticket->resolved_at->format('M j, Y H:i') }}</span></dd>
                    @endif

                    @if($ticket->sla_due)
                        <dt>SLA Due:</dt>
                        <dd>
                            @php
                                $now = now();
                                $isOverdue = $now->gt($ticket->sla_due);
                                $hoursRemaining = $now->diffInHours($ticket->sla_due, false);
                            @endphp
                            @if($isOverdue)
                                <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> {{ $ticket->sla_due->format('M j, Y H:i') }}</span>
                                <br><small class="text-danger">Overdue by {{ abs($hoursRemaining) }} hours</small>
                            @elseif($hoursRemaining < 4)
                                <span class="text-warning"><i class="fa fa-clock-o"></i> {{ $ticket->sla_due->format('M j, Y H:i') }}</span>
                                <br><small class="text-warning">{{ $hoursRemaining }} hours remaining</small>
                            @else
                                <span class="text-success"><i class="fa fa-check"></i> {{ $ticket->sla_due->format('M j, Y H:i') }}</span>
                                <br><small class="text-success">{{ $hoursRemaining }} hours remaining</small>
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Current Assets --}}
        @if($ticket->assets->count() > 0)
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-laptop"></i> Current Assets</h3>
                </div>
                <div class="box-body">
                    <ul class="list-unstyled" style="font-size: 12px; margin-bottom: 0;">
                        @foreach($ticket->assets as $asset)
                            <li style="margin-bottom: 8px;">
                                <i class="fa fa-check-circle text-success"></i>
                                <strong>{{ $asset->asset_tag }}</strong><br>
                                <span class="text-muted">{{ $asset->model_name ?? 'Unknown Model' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Help & Tips --}}
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-question-circle"></i> Edit Tips</h3>
            </div>
            <div class="box-body">
                <p><strong>Priority Guidelines:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge bg-red">High</span> System down, critical issue</li>
                    <li><span class="badge bg-yellow">Medium</span> Affecting work but not critical</li>
                    <li><span class="badge bg-green">Low</span> Minor issue or request</li>
                </ul>
                
                <hr>
                
                <p><strong>Status Options:</strong></p>
                <ul style="font-size: 12px;">
                    <li><i class="fa fa-circle-o"></i> Open - Just created</li>
                    <li><i class="fa fa-cog"></i> In Progress - Being worked on</li>
                    <li><i class="fa fa-pause"></i> On Hold - Waiting for info</li>
                    <li><i class="fa fa-check"></i> Resolved - Issue fixed</li>
                    <li><i class="fa fa-times"></i> Closed - Completed</li>
                </ul>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="tickets.edit.quick_actions.title">Quick Actions</span></h3>
            </div>
            <div class="box-body">
                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-info btn-block btn-sm">
                    <i class="fa fa-eye"></i> <span data-i18n="tickets.edit.quick_actions.view">View Full Ticket</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-list"></i> <span data-i18n="tickets.edit.quick_actions.back">Back to All Tickets</span>
                </a>
            </div>
        </div>
    </div>
</div>

@include('components.loading-overlay')

@push('scripts')
<script>
(function() {
    var translations = {
        en: {
            'tickets.edit.action.view': 'View',
            'tickets.edit.action.back': 'Back',
            'tickets.edit.form.title_prefix': 'Edit Ticket',
            'tickets.edit.section.basic': 'Basic Information',
            'tickets.edit.section.assignment': 'Assignment & Location',
            'tickets.edit.section.asset': 'Asset Association',
            'tickets.edit.action.submit': 'Update Ticket',
            'tickets.edit.quick_actions.title': 'Quick Actions',
            'tickets.edit.quick_actions.view': 'View Full Ticket',
            'tickets.edit.quick_actions.back': 'Back to All Tickets',
            'tickets.edit.select2.ticket_type': 'Select ticket type',
            'tickets.edit.select2.priority': 'Select priority',
            'tickets.edit.select2.status': 'Select status',
            'tickets.edit.select2.assigned_to': 'Select technician (optional)',
            'tickets.edit.select2.location': 'Select location',
            'tickets.edit.select2.assets': 'Search and select asset(s)',
            'tickets.edit.runtime.char_counter': '{count} / {min} characters (minimum {min})',
            'tickets.edit.runtime.status_complete': 'Changing status to "{status}" will mark this ticket as complete. Make sure all work is finished.',
            'tickets.edit.runtime.status_on_hold': 'Changing status to "{status}" will pause this ticket. Add comments explaining why.',
            'tickets.edit.runtime.status_in_progress': 'Changing status to "{status}" indicates active work on this ticket.',
            'tickets.edit.runtime.status_changed': 'Status changed to "{status}".',
            'tickets.edit.runtime.loading_update': 'Updating ticket...'
        },
        id: {
            'tickets.edit.action.view': 'Lihat',
            'tickets.edit.action.back': 'Kembali',
            'tickets.edit.form.title_prefix': 'Ubah Tiket',
            'tickets.edit.section.basic': 'Informasi Dasar',
            'tickets.edit.section.assignment': 'Penugasan & Lokasi',
            'tickets.edit.section.asset': 'Asosiasi Aset',
            'tickets.edit.action.submit': 'Perbarui Tiket',
            'tickets.edit.quick_actions.title': 'Aksi Cepat',
            'tickets.edit.quick_actions.view': 'Lihat Tiket Lengkap',
            'tickets.edit.quick_actions.back': 'Kembali ke Semua Tiket',
            'tickets.edit.select2.ticket_type': 'Pilih jenis tiket',
            'tickets.edit.select2.priority': 'Pilih prioritas',
            'tickets.edit.select2.status': 'Pilih status',
            'tickets.edit.select2.assigned_to': 'Pilih teknisi (opsional)',
            'tickets.edit.select2.location': 'Pilih lokasi',
            'tickets.edit.select2.assets': 'Cari dan pilih aset',
            'tickets.edit.runtime.char_counter': '{count} / {min} karakter (minimal {min})',
            'tickets.edit.runtime.status_complete': 'Mengubah status menjadi "{status}" akan menandai tiket ini selesai. Pastikan semua pekerjaan sudah beres.',
            'tickets.edit.runtime.status_on_hold': 'Mengubah status menjadi "{status}" akan menunda tiket ini. Tambahkan komentar penjelasan.',
            'tickets.edit.runtime.status_in_progress': 'Mengubah status menjadi "{status}" menandakan tiket sedang dikerjakan.',
            'tickets.edit.runtime.status_changed': 'Status berubah menjadi "{status}".',
            'tickets.edit.runtime.loading_update': 'Memperbarui tiket...'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('ticketEditLanguageEnglish');
    var indonesianButton = document.getElementById('ticketEditLanguageIndonesian');

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

    function formatLabel(key, fallback, vars) {
        var label = getLabel(key, fallback);
        Object.keys(vars || {}).forEach(function(varKey) {
            label = label.replace(new RegExp('\\{' + varKey + '\\}', 'g'), String(vars[varKey]));
        });
        return label;
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

    window.ticketEditLabel = getLabel;
    window.ticketEditLabelFormat = formatLabel;
    window.ticketEditLocale = function() {
        return currentLanguage === 'id' ? 'id-ID' : 'en-US';
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

    applyLanguage(getLanguage());
})();

$(document).ready(function() {
    // Store original values for change detection
    var originalPriorityId = '{{ $ticket->ticket_priority_id }}';
    var originalAssetIds = {!! json_encode($ticket->assets->pluck('id')->toArray()) !!};
    var originalStatusId = '{{ $ticket->ticket_status_id }}';

    // Initialize Select2 for all dropdowns
    $('#ticket_type_id').select2({ placeholder: window.ticketEditLabel('tickets.edit.select2.ticket_type', 'Select ticket type'), allowClear: false });
    $('#ticket_priority_id').select2({ placeholder: window.ticketEditLabel('tickets.edit.select2.priority', 'Select priority'), allowClear: false });
    $('#ticket_status_id').select2({ placeholder: window.ticketEditLabel('tickets.edit.select2.status', 'Select status'), allowClear: false });
    $('#assigned_to').select2({ placeholder: window.ticketEditLabel('tickets.edit.select2.assigned_to', 'Select technician (optional)'), allowClear: true });
    $('#location_id').select2({ placeholder: window.ticketEditLabel('tickets.edit.select2.location', 'Select location'), allowClear: false });
    
    // Init multi-select for assets with better styling
    $('#asset_id').select2({ 
        placeholder: window.ticketEditLabel('tickets.edit.select2.assets', 'Search and select asset(s)'), 
        allowClear: true,
        width: '100%'
    });

    // Character counter for description
    function updateCharCounter() {
        var length = $('#description').val().length;
        var minLength = 10;
        var counter = $('#char-counter');
        
        counter.text(window.ticketEditLabelFormat('tickets.edit.runtime.char_counter', '{count} / {min} characters (minimum {min})', {
            count: length,
            min: minLength
        }));
        
        if (length >= minLength) {
            counter.removeClass('invalid').addClass('valid');
        } else {
            counter.removeClass('valid').addClass('invalid');
        }
    }

    // Update counter on load and on input
    updateCharCounter();
    $('#description').on('input', updateCharCounter);

    // SLA Change Calculator (when priority changes)
    function calculateNewSLADueDate() {
        var currentPriorityId = $('#ticket_priority_id').val();
        
        // Only show if priority has changed
        if (currentPriorityId && currentPriorityId != originalPriorityId) {
            var slaHours = $('#ticket_priority_id').find(':selected').data('sla-hours');
            
            if (slaHours) {
                var now = new Date();
                var newDueDate = new Date(now.getTime() + (slaHours * 60 * 60 * 1000));
                var formattedDate = newDueDate.toLocaleString(window.ticketEditLocale(), {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                $('#new-sla-due-date').text(formattedDate);
                $('#sla-change-preview').slideDown();
            }
        } else {
            $('#sla-change-preview').slideUp();
        }
    }

    $('#ticket_priority_id').on('change', calculateNewSLADueDate);

    // Status Change Warning
    function checkStatusChange() {
        var selectedStatus = $('#ticket_status_id').find(':selected');
        var statusName = selectedStatus.data('status-name');
        var currentStatusId = $('#ticket_status_id').val();
        
        // Only show warning if status changed
        if (currentStatusId && currentStatusId != originalStatusId) {
            var warningText = '';
            
            if (statusName === 'resolved' || statusName === 'closed') {
                warningText = window.ticketEditLabelFormat('tickets.edit.runtime.status_complete', 'Changing status to "{status}" will mark this ticket as complete. Make sure all work is finished.', {
                    status: selectedStatus.text()
                });
            } else if (statusName === 'on hold') {
                warningText = window.ticketEditLabelFormat('tickets.edit.runtime.status_on_hold', 'Changing status to "{status}" will pause this ticket. Add comments explaining why.', {
                    status: selectedStatus.text()
                });
            } else if (statusName === 'in progress') {
                warningText = window.ticketEditLabelFormat('tickets.edit.runtime.status_in_progress', 'Changing status to "{status}" indicates active work on this ticket.', {
                    status: selectedStatus.text()
                });
            } else {
                warningText = window.ticketEditLabelFormat('tickets.edit.runtime.status_changed', 'Status changed to "{status}".', {
                    status: selectedStatus.text()
                });
            }
            
            $('#status-warning-text').text(warningText);
            $('#status-change-warning').slideDown();
        } else {
            $('#status-change-warning').slideUp();
        }
    }

    $('#ticket_status_id').on('change', checkStatusChange);

    // Asset Change Detection
    function checkAssetChanges() {
        var currentAssetIds = $('#asset_id').val() || [];
        
        // Find added and removed assets
        var addedAssets = currentAssetIds.filter(id => !originalAssetIds.includes(parseInt(id)));
        var removedAssets = originalAssetIds.filter(id => !currentAssetIds.includes(String(id)));
        
        var hasChanges = addedAssets.length > 0 || removedAssets.length > 0;
        
        if (hasChanges) {
            // Get asset names for display
            if (addedAssets.length > 0) {
                var addedNames = addedAssets.map(function(id) {
                    return $('#asset_id option[value="' + id + '"]').text().split('(')[0].trim();
                }).join(', ');
                $('#assets-added-list').text(addedNames);
                $('#assets-added').show();
            } else {
                $('#assets-added').hide();
            }
            
            if (removedAssets.length > 0) {
                var removedNames = removedAssets.map(function(id) {
                    return $('#asset_id option[value="' + id + '"]').text().split('(')[0].trim();
                }).join(', ');
                $('#assets-removed-list').text(removedNames);
                $('#assets-removed').show();
            } else {
                $('#assets-removed').hide();
            }
            
            $('#asset-change-summary').slideDown();
        } else {
            $('#asset-change-summary').slideUp();
        }
    }

    $('#asset_id').on('change', checkAssetChanges);

    // Form submit with loading overlay
    $('#ticket-edit-form').on('submit', function() {
        showLoading(window.ticketEditLabel('tickets.edit.runtime.loading_update', 'Updating ticket...'));
    });

    // Prevent enter key from submitting form
    $(":input").keypress(function(event){
        if (event.which == '10' || event.which == '13') {
            event.preventDefault();
        }
    });
});
</script>
@endpush

@endsection
