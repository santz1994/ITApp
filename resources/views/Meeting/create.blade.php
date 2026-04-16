@extends('layouts.app')

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Formulir Pemesanan Ruang Rapat',
    'subtitle' => 'Meeting Room Booking Request Form',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Meeting Room Bookings', 'url' => route('meeting-room-bookings.index')],
        ['label' => 'Create']
    ]
])

<div class="pull-right" style="margin-top: -52px; margin-bottom: 16px; margin-right: 15px;">
    <div class="btn-group btn-group-xs" role="group" aria-label="Meeting Create Language Toggle">
        <button type="button" class="btn btn-default" id="meetingCreateLanguageEnglish" data-lang="en">EN</button>
        <button type="button" class="btn btn-default" id="meetingCreateLanguageIndonesian" data-lang="id">ID</button>
    </div>
</div>
<div class="clearfix"></div>

<div class="container-fluid">
    <div class="row">
        {{-- Main Form (8 columns) --}}
        <div class="col-xs-12 col-sm-8 col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-calendar-plus-o"></i> <span data-i18n="meeting.create.form.title">Booking Details</span>
                    </h3>
                </div>
                <div class="box-body">

                    {{-- Flash Messages --}}
                    @if($errors->any())
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fa fa-exclamation-circle"></i> <strong data-i18n="meeting.create.validation.title">Validation errors:</strong>
                            <ul style="margin-bottom: 0; margin-top: 5px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('meeting-room-bookings.store') }}" method="POST" id="booking-form">
                        @csrf

                        {{-- Section 1: Informasi Pemohon (Requester Information) --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-user"></i></span> 
                                <span data-i18n="meeting.create.section.requester">Requester Information</span>
                            </legend>
                            
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="requester_name">
                                            <span data-i18n="meeting.create.field.requester_name">Requester Name</span>
                                            <span class="text-muted">(Auto)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" id="requester_name" class="form-control" 
                                                   value="{{ Auth::user()->name }}" disabled>
                                        </div>
                                        <small class="help-text text-muted" data-i18n="meeting.create.help.requester_name">Your name (auto-filled)</small>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="requester_position">
                                            <span data-i18n="meeting.create.field.requester_position">Requester Position</span> <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-id-badge"></i></span>
                                            <input type="text" name="requester_position" id="requester_position" 
                                                   class="form-control @error('requester_position') is-invalid @enderror" 
                                                   value="{{ old('requester_position') }}" 
                                                   placeholder="e.g., IT Staff, Marketing Manager" data-i18n-placeholder="meeting.create.placeholder.requester_position" required>
                                        </div>
                                        <small class="help-text text-muted" data-i18n="meeting.create.help.requester_position">Enter your current position</small>
                                        @error('requester_position')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="department">
                                            <span data-i18n="meeting.create.field.department">Department</span> <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                            <input type="text" name="department" id="department" 
                                                   class="form-control @error('department') is-invalid @enderror" 
                                                   value="{{ old('department') }}" 
                                                   placeholder="e.g., IT Department, Marketing" data-i18n-placeholder="meeting.create.placeholder.department" required>
                                        </div>
                                        <small class="help-text text-muted" data-i18n="meeting.create.help.department">Your working department</small>
                                        @error('department')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="booking_date">
                                            Tanggal / Date <span class="text-muted">(Auto)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" id="booking_date" class="form-control" 
                                                   value="{{ date('d F Y') }}" disabled>
                                        </div>
                                        <small class="help-text text-muted">Tanggal pembuatan form</small>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Section 2: Detail Rapat (Meeting Details) --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-calendar-check-o"></i></span> 
                                <span data-i18n="meeting.create.section.details">Meeting Details</span>
                            </legend>
                            
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="room_name">
                                            <span data-i18n="meeting.create.field.room">Meeting Room</span> <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('room_name') is-invalid @enderror" 
                                                id="room_name" name="room_name" required>
                                            <option value="" data-i18n="meeting.create.option.select_room">-- Select Room --</option>
                                            @foreach($rooms as $room)
                                                <option value="{{ $room }}" {{ old('room_name') == $room ? 'selected' : '' }}>
                                                    {{ $room }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text text-muted" data-i18n="meeting.create.help.room">Select your desired room</small>
                                        @error('room_name')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="attendees_count">
                                            <span data-i18n="meeting.create.field.attendees">Estimated Attendees</span> <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                            <input type="number" name="attendees_count" id="attendees_count" 
                                                   class="form-control @error('attendees_count') is-invalid @enderror" 
                                                   value="{{ old('attendees_count', 1) }}" 
                                                   min="1" max="100" required>
                                            <span class="input-group-addon" data-i18n="meeting.create.unit.people">people</span>
                                        </div>
                                        <small class="help-text text-muted" data-i18n="meeting.create.help.attendees">Meeting participant count (1-100)</small>
                                        @error('attendees_count')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="purpose">
                                    <span data-i18n="meeting.create.field.purpose">Meeting Purpose</span> <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-bullseye"></i></span>
                                    <input type="text" name="purpose" id="purpose" 
                                           class="form-control @error('purpose') is-invalid @enderror" 
                                           value="{{ old('purpose') }}" 
                                           placeholder="e.g., Q4 Project Review, Employee Training" data-i18n-placeholder="meeting.create.placeholder.purpose"
                                           required minlength="10">
                                </div>
                                <small class="help-text text-muted" data-i18n="meeting.create.help.purpose">Meeting objective (minimum 10 characters)</small>
                                @error('purpose')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="meeting_description">
                                    <span data-i18n="meeting.create.field.description">Meeting Description</span> <span class="text-danger">*</span>
                                </label>
                                <textarea name="meeting_description" id="meeting_description" 
                                          class="form-control @error('meeting_description') is-invalid @enderror" 
                                          rows="3" required minlength="10"
                                          placeholder="Describe agenda and important details..." data-i18n-placeholder="meeting.create.placeholder.description">{{ old('meeting_description') }}</textarea>
                                <small class="help-text text-muted" data-i18n="meeting.create.help.description">Complete meeting description (minimum 10 characters)</small>
                                @error('meeting_description')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="meeting_needs">
                                    <span data-i18n="meeting.create.field.needs">Facility Needs</span> <span class="text-muted" data-i18n="meeting.create.optional">(Optional)</span>
                                </label>
                                <textarea name="meeting_needs" id="meeting_needs" 
                                          class="form-control @error('meeting_needs') is-invalid @enderror" 
                                          rows="2"
                                          placeholder="e.g., Projector, Whiteboard, Sound System" data-i18n-placeholder="meeting.create.placeholder.needs">{{ old('meeting_needs') }}</textarea>
                                <small class="help-text text-muted" data-i18n="meeting.create.help.needs">Additional facilities needed (optional)</small>
                                @error('meeting_needs')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </fieldset>

                        {{-- Section 3: Waktu Rapat (Meeting Time) --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-clock-o"></i></span> 
                                Diperlukan Pada / Required On
                            </legend>
                            
                            <div class="row">
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div class="form-group">
                                        <label for="meeting_date">
                                            Tanggal Rapat / Meeting Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="date" name="meeting_date" id="meeting_date" 
                                                   class="form-control @error('start_datetime') is-invalid @enderror" 
                                                   value="{{ old('meeting_date', date('Y-m-d', strtotime('+0 day'))) }}" 
                                                   min="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <small class="help-text text-muted">Pilih tanggal rapat</small>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div class="form-group">
                                        <label for="start_time">
                                            Waktu Mulai / Start Time <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                            <input type="text" name="start_time" id="start_time" 
                                                   class="form-control time-input @error('start_datetime') is-invalid @enderror" 
                                                   value="{{ old('start_time', '09:00') }}" 
                                                   maxlength="5"
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                                   placeholder="09:00 (Format 24 jam)"
                                                   autocomplete="off"
                                                   required>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <i class="fa fa-clock-o"></i> <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right time-picker-dropdown">
                                                    <li class="dropdown-header">Pilih Waktu / Select Time</li>
                                                    <li><a href="#" data-time="07:00">07:00 (Pagi)</a></li>
                                                    <li><a href="#" data-time="08:00">08:00</a></li>
                                                    <li><a href="#" data-time="09:00">09:00</a></li>
                                                    <li><a href="#" data-time="10:00">10:00</a></li>
                                                    <li><a href="#" data-time="11:00">11:00</a></li>
                                                    <li><a href="#" data-time="12:00">12:00 (Siang)</a></li>
                                                    <li><a href="#" data-time="13:00">13:00</a></li>
                                                    <li><a href="#" data-time="14:00">14:00</a></li>
                                                    <li><a href="#" data-time="15:00">15:00</a></li>
                                                    <li><a href="#" data-time="16:00">16:00</a></li>
                                                    <li><a href="#" data-time="17:00">17:00 (Sore)</a></li>
                                                    <li><a href="#" data-time="18:00">18:00</a></li>
                                                    <li><a href="#" data-time="19:00">19:00</a></li>
                                                    <li><a href="#" data-time="20:00">20:00 (Malam)</a></li>
                                                </ul>
                                            </span>
                                        </div>
                                        <small class="help-text text-muted"><strong>Format 24 jam:</strong> 00:00 - 23:59 (contoh: 09:00, 14:30, 20:00)</small>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div class="form-group">
                                        <label for="end_time">
                                            Waktu Selesai / End Time <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                            <input type="text" name="end_time" id="end_time" 
                                                   class="form-control time-input @error('end_datetime') is-invalid @enderror" 
                                                   value="{{ old('end_time', '11:00') }}" 
                                                   maxlength="5"
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                                   placeholder="11:00 (Format 24 jam)"
                                                   autocomplete="off"
                                                   required>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <i class="fa fa-clock-o"></i> <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right time-picker-dropdown">
                                                    <li class="dropdown-header">Pilih Waktu / Select Time</li>
                                                    <li><a href="#" data-time="08:00">08:00 (Pagi)</a></li>
                                                    <li><a href="#" data-time="09:00">09:00</a></li>
                                                    <li><a href="#" data-time="10:00">10:00</a></li>
                                                    <li><a href="#" data-time="11:00">11:00</a></li>
                                                    <li><a href="#" data-time="12:00">12:00 (Siang)</a></li>
                                                    <li><a href="#" data-time="13:00">13:00</a></li>
                                                    <li><a href="#" data-time="14:00">14:00</a></li>
                                                    <li><a href="#" data-time="15:00">15:00</a></li>
                                                    <li><a href="#" data-time="16:00">16:00</a></li>
                                                    <li><a href="#" data-time="17:00">17:00 (Sore)</a></li>
                                                    <li><a href="#" data-time="18:00">18:00</a></li>
                                                    <li><a href="#" data-time="19:00">19:00</a></li>
                                                    <li><a href="#" data-time="20:00">20:00 (Malam)</a></li>
                                                    <li><a href="#" data-time="21:00">21:00</a></li>
                                                    <li><a href="#" data-time="22:00">22:00</a></li>
                                                </ul>
                                            </span>
                                        </div>
                                        <small class="help-text text-muted"><strong>Format 24 jam:</strong> 00:00 - 23:59 (contoh: 11:00, 16:30, 22:00)</small>
                                    </div>
                                </div>
                            </div>

                            @if($errors->has('start_datetime') || $errors->has('end_datetime'))
                                <div class="alert alert-danger">
                                    <i class="fa fa-exclamation-circle"></i>
                                    @error('start_datetime'){{ $message }}@enderror
                                    @error('end_datetime'){{ $message }}@enderror
                                </div>
                            @endif

                            {{-- Duration Display --}}
                            <div class="alert alert-info" id="duration-display" style="display: none;">
                                <i class="fa fa-info-circle"></i>
                                <strong data-i18n="meeting.create.duration.label">Meeting Duration:</strong> 
                                <span id="duration-text">0</span>
                            </div>

                            {{-- Conflict Check Result --}}
                            <div class="alert alert-warning" id="conflict-warning" style="display: none;">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong data-i18n="meeting.create.conflict.title">Attention!</strong> <span data-i18n="meeting.create.conflict.line1">This room is already booked for the selected time.</span>
                                <br><span data-i18n="meeting.create.conflict.line2">Please choose another room or time slot.</span>
                            </div>
                        </fieldset>

                        {{-- Hidden Fields for Combined DateTime --}}
                        <input type="hidden" name="start_datetime" id="start_datetime">
                        <input type="hidden" name="end_datetime" id="end_datetime">

                        {{-- Submit Buttons --}}
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                <i class="fa fa-paper-plane"></i> <span data-i18n="meeting.create.action.submit">Submit Request</span>
                            </button>
                            <a href="{{ route('meeting-room-bookings.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> <span data-i18n="meeting.create.action.cancel">Cancel</span>
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- Sidebar (4 columns) --}}
        <div class="col-xs-12 col-sm-4 col-md-4">
            
            {{-- Booking Guidelines --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Panduan Pemesanan</h3>
                </div>
                <div class="box-body">
                    <ul class="fa-ul">
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Permohonan harus diajukan minimal <strong>15 menit sebelumnya</strong>
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Durasi rapat <strong>minimal 30 menit</strong>, maksimal <strong>8 jam</strong>
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Persetujuan dari <strong>Direktur (wajib)</strong>
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Status: <span class="label bg-yellow">Pending</span> → 
                            <span class="label bg-green">Approved</span>
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Edit/batalkan booking selama masih <strong>pending</strong>
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-blue"></i> 
                            Waktu rapat dapat <strong>diperpanjang</strong> jika tidak bentrok
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-blue"></i> 
                            Lihat jadwal di <a href="{{ route('meeting-room-bookings.lcd-dashboard') }}" target="_blank">LCD Dashboard</a> atau <a href="{{ route('meeting-room-bookings.calendar') }}">Calendar</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Available Rooms --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-door-open"></i> Ruang Tersedia</h3>
                </div>
                <div class="box-body">
                    <ul class="list-unstyled">
                        @foreach($rooms as $room)
                        <li style="padding: 5px 0;">
                            <i class="fa fa-building text-green"></i> 
                            <strong>{{ $room }}</strong>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Today's Schedule --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calendar-o"></i> <span data-i18n="meeting.create.schedule.title">Today's Schedule</span></h3>
                </div>
                <div class="box-body">
                    <div id="today-schedule">
                        <div class="text-center text-muted">
                            <i class="fa fa-spinner fa-spin"></i> <span data-i18n="meeting.create.schedule.loading">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
fieldset {
    border: 2px solid #e8e8e8;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    background-color: #fafafa;
}

fieldset legend {
    border-bottom: none;
    width: auto;
    padding: 0 10px;
    margin-bottom: 0;
    font-size: 16px;
    font-weight: bold;
    color: #3c8dbc;
}

.form-section-icon {
    margin-right: 5px;
}

.help-text {
    display: block;
    margin-top: 5px;
    font-size: 12px;
}

/* 24-Hour Time Picker Styles */
.time-input {
    font-family: 'Courier New', monospace;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 1px;
    text-align: center;
}

.time-picker-dropdown {
    max-height: 300px;
    overflow-y: auto;
    min-width: 160px;
}

.time-picker-dropdown li a {
    padding: 8px 15px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
}

.time-picker-dropdown li a:hover {
    background-color: #3c8dbc;
    color: white;
}

.time-input.is-invalid {
    border-color: #dd4b39;
    box-shadow: 0 0 5px rgba(221, 75, 57, 0.5);
}

.time-error {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var translations = {
        en: {
            'meeting.create.form.title': 'Booking Details',
            'meeting.create.validation.title': 'Validation errors:',
            'meeting.create.section.requester': 'Requester Information',
            'meeting.create.section.details': 'Meeting Details',
            'meeting.create.field.requester_name': 'Requester Name',
            'meeting.create.help.requester_name': 'Your name (auto-filled)',
            'meeting.create.field.requester_position': 'Requester Position',
            'meeting.create.placeholder.requester_position': 'e.g., IT Staff, Marketing Manager',
            'meeting.create.help.requester_position': 'Enter your current position',
            'meeting.create.field.department': 'Department',
            'meeting.create.placeholder.department': 'e.g., IT Department, Marketing',
            'meeting.create.help.department': 'Your working department',
            'meeting.create.field.room': 'Meeting Room',
            'meeting.create.option.select_room': '-- Select Room --',
            'meeting.create.help.room': 'Select your desired room',
            'meeting.create.field.attendees': 'Estimated Attendees',
            'meeting.create.unit.people': 'people',
            'meeting.create.help.attendees': 'Meeting participant count (1-100)',
            'meeting.create.field.purpose': 'Meeting Purpose',
            'meeting.create.placeholder.purpose': 'e.g., Q4 Project Review, Employee Training',
            'meeting.create.help.purpose': 'Meeting objective (minimum 10 characters)',
            'meeting.create.field.description': 'Meeting Description',
            'meeting.create.placeholder.description': 'Describe agenda and important details...',
            'meeting.create.help.description': 'Complete meeting description (minimum 10 characters)',
            'meeting.create.field.needs': 'Facility Needs',
            'meeting.create.optional': '(Optional)',
            'meeting.create.placeholder.needs': 'e.g., Projector, Whiteboard, Sound System',
            'meeting.create.help.needs': 'Additional facilities needed (optional)',
            'meeting.create.action.submit': 'Submit Request',
            'meeting.create.action.cancel': 'Cancel',
            'meeting.create.duration.label': 'Meeting Duration:',
            'meeting.create.conflict.title': 'Attention!',
            'meeting.create.conflict.line1': 'This room is already booked for the selected time.',
            'meeting.create.conflict.line2': 'Please choose another room or time slot.',
            'meeting.create.schedule.title': "Today's Schedule",
            'meeting.create.schedule.loading': 'Loading...',
            'meeting.create.runtime.invalid_time_range': 'Invalid time format! Use 24-hour format (00:00 - 23:59)',
            'meeting.create.runtime.invalid_time_format': 'Format must be HH:MM (example: 09:00, 14:30)',
            'meeting.create.runtime.end_after_start': 'End time must be after start time!',
            'meeting.create.runtime.minimum_duration': 'Minimum meeting duration is 30 minutes.\n\nCurrent duration: {duration} minutes',
            'meeting.create.runtime.processing': 'Processing...',
            'meeting.create.runtime.schedule_intro': 'See complete schedule on the pages below:',
            'meeting.create.runtime.schedule_lcd': 'LCD Dashboard',
            'meeting.create.runtime.schedule_calendar': 'Calendar View',
            'meeting.create.runtime.schedule_failed': 'Failed to load schedule'
        },
        id: {
            'meeting.create.form.title': 'Detail Pemesanan',
            'meeting.create.validation.title': 'Kesalahan validasi:',
            'meeting.create.section.requester': 'Informasi Pemohon',
            'meeting.create.section.details': 'Detail Rapat',
            'meeting.create.field.requester_name': 'Nama Pemohon',
            'meeting.create.help.requester_name': 'Nama Anda (otomatis terisi)',
            'meeting.create.field.requester_position': 'Jabatan Pemohon',
            'meeting.create.placeholder.requester_position': 'contoh: Staff IT, Manager Marketing',
            'meeting.create.help.requester_position': 'Masukkan jabatan Anda saat ini',
            'meeting.create.field.department': 'Bagian / Departemen',
            'meeting.create.placeholder.department': 'contoh: Departemen IT, Marketing',
            'meeting.create.help.department': 'Departemen tempat Anda bekerja',
            'meeting.create.field.room': 'Ruang Rapat',
            'meeting.create.option.select_room': '-- Pilih Ruang --',
            'meeting.create.help.room': 'Pilih ruang rapat yang diinginkan',
            'meeting.create.field.attendees': 'Estimasi Peserta',
            'meeting.create.unit.people': 'orang',
            'meeting.create.help.attendees': 'Jumlah peserta rapat (1-100)',
            'meeting.create.field.purpose': 'Keperluan Rapat',
            'meeting.create.placeholder.purpose': 'contoh: Review Proyek Q4, Training Karyawan',
            'meeting.create.help.purpose': 'Tujuan rapat (minimal 10 karakter)',
            'meeting.create.field.description': 'Deskripsi Rapat',
            'meeting.create.placeholder.description': 'Jelaskan agenda dan detail penting lainnya...',
            'meeting.create.help.description': 'Deskripsi lengkap rapat (minimal 10 karakter)',
            'meeting.create.field.needs': 'Kebutuhan Fasilitas',
            'meeting.create.optional': '(Opsional)',
            'meeting.create.placeholder.needs': 'contoh: Proyektor, Whiteboard, Sound System',
            'meeting.create.help.needs': 'Fasilitas tambahan yang dibutuhkan (opsional)',
            'meeting.create.action.submit': 'Kirim Permohonan',
            'meeting.create.action.cancel': 'Batal',
            'meeting.create.duration.label': 'Durasi Rapat:',
            'meeting.create.conflict.title': 'Perhatian!',
            'meeting.create.conflict.line1': 'Ruang ini sudah dibooking pada waktu tersebut.',
            'meeting.create.conflict.line2': 'Silakan pilih ruang lain atau waktu yang berbeda.',
            'meeting.create.schedule.title': 'Jadwal Hari Ini',
            'meeting.create.schedule.loading': 'Memuat...',
            'meeting.create.runtime.invalid_time_range': 'Format waktu tidak valid! Gunakan format 24 jam (00:00 - 23:59)',
            'meeting.create.runtime.invalid_time_format': 'Format harus HH:MM (contoh: 09:00, 14:30)',
            'meeting.create.runtime.end_after_start': 'Waktu selesai harus lebih besar dari waktu mulai!',
            'meeting.create.runtime.minimum_duration': 'Durasi rapat minimal 30 menit.\n\nDurasi saat ini: {duration} menit',
            'meeting.create.runtime.processing': 'Memproses...',
            'meeting.create.runtime.schedule_intro': 'Lihat jadwal lengkap di halaman berikut:',
            'meeting.create.runtime.schedule_lcd': 'LCD Dashboard',
            'meeting.create.runtime.schedule_calendar': 'Calendar View',
            'meeting.create.runtime.schedule_failed': 'Gagal memuat jadwal'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('meetingCreateLanguageEnglish');
    var indonesianButton = document.getElementById('meetingCreateLanguageIndonesian');

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

    window.meetingCreateLabel = getLabel;
    window.meetingCreateLabelFormat = formatLabel;

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
    // ============================================
    // 24-HOUR TIME INPUT WITH AUTO-MASKING
    // ============================================
    
    // Handle dropdown time selection
    $('.time-picker-dropdown a').on('click', function(e) {
        e.preventDefault();
        var time = $(this).data('time');
        var input = $(this).closest('.input-group').find('.time-input');
        input.val(time).trigger('change');
    });
    
    // Auto-format time input as user types (HH:MM masking)
    $('.time-input').on('input', function(e) {
        var value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
        var formatted = '';
        
        if (value.length > 0) {
            // First digit of hour (0-2)
            formatted = value[0];
            
            if (value.length > 1) {
                // Second digit of hour
                var firstDigit = parseInt(value[0]);
                var secondDigit = parseInt(value[1]);
                
                // Validate hour (max 23)
                if (firstDigit === 2 && secondDigit > 3) {
                    secondDigit = 3; // Force max hour 23
                }
                formatted += secondDigit + ':';
                
                if (value.length > 2) {
                    // First digit of minute (0-5)
                    var minuteFirst = parseInt(value[2]);
                    if (minuteFirst > 5) {
                        minuteFirst = 5; // Force max minute 59
                    }
                    formatted += minuteFirst;
                    
                    if (value.length > 3) {
                        // Second digit of minute
                        formatted += value[3];
                    }
                }
            }
        }
        
        $(this).val(formatted);
    });
    
    // Validate and format on blur
    $('.time-input').on('blur', function() {
        var value = $(this).val();
        
        if (value) {
            // Check if format is correct HH:MM
            var match = value.match(/^(\d{1,2}):?(\d{0,2})$/);
            
            if (match) {
                var hours = parseInt(match[1], 10);
                var minutes = match[2] ? parseInt(match[2], 10) : 0;
                
                // Validate ranges
                if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
                    // Format with leading zeros
                    var formatted = 
                        (hours < 10 ? '0' + hours : hours) + ':' + 
                        (minutes < 10 ? '0' + minutes : minutes);
                    $(this).val(formatted);
                    $(this).removeClass('is-invalid');
                } else {
                    // Invalid time
                    $(this).addClass('is-invalid');
                    $(this).after('<span class="text-danger time-error">' +
                        window.meetingCreateLabel('meeting.create.runtime.invalid_time_range', 'Invalid time format! Use 24-hour format (00:00 - 23:59)') +
                        '</span>');
                    setTimeout(function() { $('.time-error').remove(); }, 3000);
                }
            } else {
                // Invalid format
                $(this).addClass('is-invalid');
                $(this).after('<span class="text-danger time-error">' +
                    window.meetingCreateLabel('meeting.create.runtime.invalid_time_format', 'Format must be HH:MM (example: 09:00, 14:30)') +
                    '</span>');
                setTimeout(function() { $('.time-error').remove(); }, 3000);
            }
        }
    });
    
    // Remove invalid class on focus
    $('.time-input').on('focus', function() {
        $(this).removeClass('is-invalid');
        $('.time-error').remove();
    });
    
    // Combine date and time fields into datetime fields
    function updateDateTimeFields() {
        var date = $('#meeting_date').val();
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (date && startTime) {
            $('#start_datetime').val(date + ' ' + startTime + ':00');
        }
        
        if (date && endTime) {
            $('#end_datetime').val(date + ' ' + endTime + ':00');
        }
        
        // Calculate duration
        if (startTime && endTime) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date('2000-01-01 ' + endTime);
            var diff = (end - start) / 1000 / 60; // minutes
            
            if (diff > 0) {
                // Format duration with hours and minutes
                var totalMinutes = Math.round(diff);
                var durationText;
                
                if (totalMinutes >= 60) {
                    var hours = Math.floor(totalMinutes / 60);
                    var minutes = totalMinutes % 60;
                    durationText = hours + ' jam';
                    if (minutes > 0) {
                        durationText += ' ' + minutes + ' menit';
                    }
                } else {
                    durationText = totalMinutes + ' menit';
                }
                
                $('#duration-text').text(durationText);
                $('#duration-display').fadeIn();
            } else {
                $('#duration-display').fadeOut();
            }
        }
    }
    
    // Update on change
    $('#meeting_date, #start_time, #end_time').on('change blur', function() {
        updateDateTimeFields();
        checkConflict();
    });
    
    // Initial update
    updateDateTimeFields();
    
    // Conflict detection (AJAX)
    function checkConflict() {
        var room = $('#room_name').val();
        var startDateTime = $('#start_datetime').val();
        var endDateTime = $('#end_datetime').val();
        
        if (!room || !startDateTime || !endDateTime) {
            return;
        }
        
        // TODO: Implement AJAX call to check conflicts
        // For now, hide the warning
        $('#conflict-warning').fadeOut();
    }
    
    $('#room_name').on('change', checkConflict);
    
    // Form validation before submit
    $('#booking-form').on('submit', function(e) {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (startTime >= endTime) {
            e.preventDefault();
            alert(window.meetingCreateLabel('meeting.create.runtime.end_after_start', 'End time must be after start time!'));
            return false;
        }
        
        // Calculate duration in minutes
        var start = new Date('2000-01-01 ' + startTime);
        var end = new Date('2000-01-01 ' + endTime);
        var durationMinutes = (end - start) / 1000 / 60;
        
        // Check minimum duration (30 minutes)
        if (durationMinutes < 30) {
            e.preventDefault();
            alert(window.meetingCreateLabelFormat('meeting.create.runtime.minimum_duration', 'Minimum meeting duration is 30 minutes.\n\nCurrent duration: {duration} minutes', {
                duration: durationMinutes
            }));
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + window.meetingCreateLabel('meeting.create.runtime.processing', 'Processing...'));
    });
    
    // Load today's schedule
    loadTodaySchedule();
    
    function loadTodaySchedule() {
        $.ajax({
            url: '{{ route("meeting-room-bookings.index") }}',
            data: { 
                ajax: 1,
                date: '{{ date("Y-m-d") }}',
                status: 'approved'
            },
            success: function(response) {
                // This would need an API endpoint, for now show placeholder
                $('#today-schedule').html(
                    '<p class="text-muted"><i class="fa fa-calendar-check-o"></i> ' +
                    window.meetingCreateLabel('meeting.create.runtime.schedule_intro', 'See complete schedule on the pages below:') + '</p>' +
                    '<a href="{{ route("meeting-room-bookings.lcd-dashboard") }}" class="btn btn-sm btn-info btn-block" target="_blank">' +
                    '<i class="fa fa-desktop"></i> ' + window.meetingCreateLabel('meeting.create.runtime.schedule_lcd', 'LCD Dashboard') +
                    '</a>' +
                    '<a href="{{ route("meeting-room-bookings.calendar") }}" class="btn btn-sm btn-primary btn-block" style="margin-top: 5px;">' +
                    '<i class="fa fa-calendar"></i> ' + window.meetingCreateLabel('meeting.create.runtime.schedule_calendar', 'Calendar View') +
                    '</a>'
                );
            },
            error: function() {
                $('#today-schedule').html(
                    '<p class="text-muted"><i class="fa fa-exclamation-circle"></i> ' +
                    window.meetingCreateLabel('meeting.create.runtime.schedule_failed', 'Failed to load schedule') + '</p>'
                );
            }
        });
    }
});
</script>
@endpush
