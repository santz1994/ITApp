@extends('layouts.app')

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit Pemesanan Ruang Rapat',
    'subtitle' => 'Edit Meeting Room Booking #' . $booking->id,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Meeting Room Bookings', 'url' => route('meeting-room-bookings.index')],
        ['label' => 'Edit']
    ]
])

<div class="pull-right" style="margin-top: -52px; margin-bottom: 16px; margin-right: 15px;">
    <div class="btn-group btn-group-xs" role="group" aria-label="Meeting Edit Language Toggle">
        <button type="button" class="btn btn-default" id="meetingEditLanguageEnglish" data-lang="en">EN</button>
        <button type="button" class="btn btn-default" id="meetingEditLanguageIndonesian" data-lang="id">ID</button>
    </div>
</div>
<div class="clearfix"></div>

<div class="container-fluid">
    
    {{-- Warning if not editable --}}
    @php
        $isReceptionist = Auth::user()->hasRole(['receptionist']);
        $isSuperAdmin = Auth::user()->hasRole('super-admin');
        $isDaniel = Auth::user()->email === 'daniel@quty.co.id';
        $canEditApproved = $isReceptionist || $isSuperAdmin || $isDaniel;
        $isOwner = $booking->user_id == Auth::id();
        
        // Owner can only edit pending bookings
        // Receptionist/Super-admin/Daniel can edit pending OR approved bookings
        if ($canEditApproved) {
            $allowEdit = in_array($booking->status, ['pending', 'approved']) && $booking->start_datetime->isFuture();
        } else {
            $allowEdit = $booking->canBeEdited(); // pending AND future only
        }
    @endphp
    
    @if(!$allowEdit)
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i>
            <strong data-i18n="meeting.edit.locked.alert_title">Warning!</strong> <span data-i18n="meeting.edit.locked.alert_message">This booking cannot be edited because it is already approved/rejected or the meeting time has passed.</span>
            @if($isOwner && $booking->status === 'approved')
                <br><br>
                <em>Note: Only Receptionist, Super Admin, or daniel@quty.co.id can edit approved bookings.</em>
            @endif
        </div>
        <a href="{{ route('meeting-room-bookings.show', $booking->id) }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span data-i18n="meeting.edit.locked.back">Back</span>
        </a>
    @else
    
    <div class="row">
        {{-- Main Form (8 columns) --}}
        <div class="col-xs-12 col-sm-8 col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-edit"></i> <span data-i18n="meeting.edit.form.title">Edit Booking Details</span>
                    </h3>
                </div>
                <div class="box-body">

                    {{-- Flash Messages --}}
                    @if($errors->any())
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fa fa-exclamation-circle"></i> <strong data-i18n="meeting.edit.validation.title">Validation errors:</strong>
                            <ul style="margin-bottom: 0; margin-top: 5px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('meeting-room-bookings.update', $booking->id) }}" method="POST" id="booking-form">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Informasi Pemohon (Requester Information) --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-user"></i></span> 
                                <span data-i18n="meeting.edit.section.requester">Requester Information</span>
                            </legend>
                            
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="requester_name">
                                            Nama Pemohon / Requester Name
                                            <span class="text-muted">(Auto)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" id="requester_name" class="form-control" 
                                                   value="{{ $booking->user->name }}" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="requester_position">
                                            Jabatan Pemohon / Position <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-id-badge"></i></span>
                                            <input type="text" name="requester_position" id="requester_position" 
                                                   class="form-control @error('requester_position') is-invalid @enderror" 
                                                   value="{{ old('requester_position', $booking->requester_position) }}" 
                                                   required>
                                        </div>
                                        @error('requester_position')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="department">
                                            Bagian / Departemen <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                            <input type="text" name="department" id="department" 
                                                   class="form-control @error('department') is-invalid @enderror" 
                                                   value="{{ old('department', $booking->department) }}" 
                                                   required>
                                        </div>
                                        @error('department')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="booking_date">
                                            Tanggal / Date <span class="text-muted">(Original)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" id="booking_date" class="form-control" 
                                                   value="{{ $booking->created_at->format('d F Y') }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Section 2: Detail Rapat (Meeting Details) --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-calendar-check-o"></i></span> 
                                <span data-i18n="meeting.edit.section.details">Meeting Details</span>
                            </legend>
                            
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="room_name">
                                            Ruang Rapat / Meeting Room <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('room_name') is-invalid @enderror" 
                                                id="room_name" name="room_name" required>
                                            <option value="">-- Pilih Ruang Rapat / Select Room --</option>
                                            @foreach($rooms as $room)
                                                <option value="{{ $room }}" {{ old('room_name', $booking->room_name) == $room ? 'selected' : '' }}>
                                                    {{ $room }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('room_name')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label for="attendees_count">
                                            Estimasi Peserta / Estimated Attendees <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                            <input type="number" name="attendees_count" id="attendees_count" 
                                                   class="form-control @error('attendees_count') is-invalid @enderror" 
                                                   value="{{ old('attendees_count', $booking->attendees_count) }}" 
                                                   min="1" max="100" required>
                                            <span class="input-group-addon">orang / persons</span>
                                        </div>
                                        @error('attendees_count')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="purpose">
                                    Keperluan Rapat / Meeting Purpose <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-bullseye"></i></span>
                                    <input type="text" name="purpose" id="purpose" 
                                           class="form-control @error('purpose') is-invalid @enderror" 
                                           value="{{ old('purpose', $booking->purpose) }}" 
                                           required minlength="10">
                                </div>
                                @error('purpose')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="meeting_description">
                                    Deskripsi / Keterangan Rapat <span class="text-danger">*</span>
                                </label>
                                <textarea name="meeting_description" id="meeting_description" 
                                          class="form-control @error('meeting_description') is-invalid @enderror" 
                                          rows="3" required minlength="10">{{ old('meeting_description', $booking->meeting_description) }}</textarea>
                                @error('meeting_description')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="meeting_needs">
                                    Kebutuhan Fasilitas / Facility Needs <span class="text-muted">(Optional)</span>
                                </label>
                                <textarea name="meeting_needs" id="meeting_needs" 
                                          class="form-control @error('meeting_needs') is-invalid @enderror" 
                                          rows="2">{{ old('meeting_needs', $booking->meeting_needs) }}</textarea>
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
                                                   value="{{ old('meeting_date', $booking->start_datetime->format('Y-m-d')) }}" 
                                                   min="{{ date('Y-m-d') }}" required>
                                        </div>
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
                                                   value="{{ old('start_time', $booking->start_datetime->format('H:i')) }}" 
                                                   maxlength="5"
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                                   placeholder="HH:MM (24-hour)"
                                                   required>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <i class="fa fa-clock-o"></i> <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right time-picker-dropdown">
                                                    <li class="dropdown-header">Pilih Waktu</li>
                                                    @for($h = 7; $h <= 22; $h++)
                                                        <li><a href="#" data-time="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</a></li>
                                                    @endfor
                                                </ul>
                                            </span>
                                        </div>
                                        <small class="help-text text-muted"><strong>Format 24 jam:</strong> 00:00 - 23:59</small>
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
                                                   value="{{ old('end_time', $booking->end_datetime->format('H:i')) }}" 
                                                   maxlength="5"
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                                   placeholder="HH:MM (24-hour)"
                                                   required>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <i class="fa fa-clock-o"></i> <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right time-picker-dropdown">
                                                    <li class="dropdown-header">Pilih Waktu</li>
                                                    @for($h = 8; $h <= 23; $h++)
                                                        <li><a href="#" data-time="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</a></li>
                                                    @endfor
                                                </ul>
                                            </span>
                                        </div>
                                        <small class="help-text text-muted"><strong>Format 24 jam:</strong> 00:00 - 23:59</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Duration Display --}}
                            <div class="alert alert-info" id="duration-display">
                                <i class="fa fa-info-circle"></i>
                                <strong data-i18n="meeting.edit.duration.label">Meeting Duration:</strong> 
                                <span id="duration-text">{{ $booking->duration }}</span>
                            </div>
                        </fieldset>

                        {{-- Hidden Fields for Combined DateTime --}}
                        <input type="hidden" name="start_datetime" id="start_datetime">
                        <input type="hidden" name="end_datetime" id="end_datetime">

                        {{-- Submit Buttons --}}
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                <i class="fa fa-save"></i> <span data-i18n="meeting.edit.action.submit">Save Changes</span>
                            </button>
                            <a href="{{ route('meeting-room-bookings.show', $booking->id) }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> <span data-i18n="meeting.edit.action.cancel">Cancel</span>
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- Sidebar (4 columns) --}}
        <div class="col-xs-12 col-sm-4 col-md-4">
            
            {{-- Original Booking Info --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Booking Asli / Original Booking</h3>
                </div>
                <div class="box-body">
                    <p><strong>ID:</strong> #{{ $booking->id }}</p>
                    <p><strong>Ruang / Room:</strong><br>{{ $booking->room_name }}</p>
                    <p><strong>Tanggal / Date:</strong><br>{{ $booking->start_datetime->format('d F Y') }}</p>
                    <p><strong>Waktu / Time:</strong><br>{{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}</p>
                    <p><strong>Status:</strong><br><span class="label {{ $booking->statusBadge }}">{{ $booking->status }}</span></p>
                </div>
            </div>

            {{-- Edit Guidelines --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-circle"></i> Perhatian</h3>
                </div>
                <div class="box-body">
                    <ul class="fa-ul">
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Hanya booking dengan status <strong>Pending</strong> yang dapat diedit
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Waktu rapat harus di <strong>masa depan</strong>
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Pastikan ruang tersedia pada waktu yang dipilih
                        </li>
                        <li><i class="fa-li fa fa-check-circle text-green"></i> 
                            Perubahan akan direview ulang oleh Direktur
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Emergency Actions (Receptionist & Super-admin Only) --}}
            @if((user_has_role(Auth::user(), 'receptionist') || user_has_role(Auth::user(), 'super-admin')) 
                && !in_array($booking->status, ['cancelled', 'finished']))
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Aksi Darurat / Emergency Actions</h3>
                </div>
                <div class="box-body">
                    <p class="text-danger">
                        <i class="fa fa-warning"></i> <strong>HANYA untuk keadaan darurat!</strong><br>
                        <small>ONLY for emergency situations!</small>
                    </p>
                    
                    {{-- Force Cancel Button --}}
                    <form action="{{ route('meeting-room-bookings.cancel', $booking->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('⚠️ PERHATIAN! ⚠️\n\nAnda akan MEMBATALKAN PAKSA meeting ini bahkan jika sedang berlangsung!\nIni hanya untuk KEADAAN DARURAT (contoh: tamu mendadak datang).\n\n⚠️ WARNING! ⚠️\n\nYou will FORCE CANCEL this meeting even if it\'s in progress!\nThis is ONLY for EMERGENCY situations (e.g., unexpected visitor arrival).\n\nApakah Anda yakin?\nAre you sure?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-block btn-lg" 
                                style="background-color: #dd4b39; border-color: #d73925;">
                            <i class="fa fa-exclamation-triangle"></i> FORCE CANCEL<br>
                            <small>(Emergency Override)</small>
                        </button>
                    </form>
                    
                    <p class="text-muted" style="margin-top: 10px; font-size: 11px;">
                        <i class="fa fa-info-circle"></i> 
                        Gunakan hanya untuk: tamu VIP mendadak, keadaan darurat, force majeure<br>
                        <small>Use only for: unexpected VIP guests, emergencies, force majeure</small>
                    </p>
                </div>
            </div>
            @endif

        </div>
    </div>

    @endif
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
    color: #f39c12;
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
    background-color: #f39c12;
    color: white;
}

.time-input.is-invalid {
    border-color: #dd4b39;
    box-shadow: 0 0 5px rgba(221, 75, 57, 0.5);
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var translations = {
        en: {
            'meeting.edit.locked.alert_title': 'Warning!',
            'meeting.edit.locked.alert_message': 'This booking cannot be edited because it is already approved/rejected or the meeting time has passed.',
            'meeting.edit.locked.back': 'Back',
            'meeting.edit.form.title': 'Edit Booking Details',
            'meeting.edit.validation.title': 'Validation errors:',
            'meeting.edit.section.requester': 'Requester Information',
            'meeting.edit.section.details': 'Meeting Details',
            'meeting.edit.duration.label': 'Meeting Duration:',
            'meeting.edit.action.submit': 'Save Changes',
            'meeting.edit.action.cancel': 'Cancel',
            'meeting.edit.runtime.invalid_time_range': 'Invalid time format! Use 00:00 - 23:59.',
            'meeting.edit.runtime.invalid_time_format': 'Format must be HH:MM (example: 09:00, 14:30).',
            'meeting.edit.runtime.hour': 'hour',
            'meeting.edit.runtime.minute': 'minute',
            'meeting.edit.runtime.end_after_start': 'End time must be greater than start time!',
            'meeting.edit.runtime.processing': 'Processing...'
        },
        id: {
            'meeting.edit.locked.alert_title': 'Perhatian!',
            'meeting.edit.locked.alert_message': 'Pemesanan ini tidak dapat diedit karena sudah disetujui/ditolak atau waktu rapat telah lewat.',
            'meeting.edit.locked.back': 'Kembali',
            'meeting.edit.form.title': 'Ubah Detail Pemesanan',
            'meeting.edit.validation.title': 'Kesalahan validasi:',
            'meeting.edit.section.requester': 'Informasi Pemohon',
            'meeting.edit.section.details': 'Detail Rapat',
            'meeting.edit.duration.label': 'Durasi Rapat:',
            'meeting.edit.action.submit': 'Simpan Perubahan',
            'meeting.edit.action.cancel': 'Batal',
            'meeting.edit.runtime.invalid_time_range': 'Format waktu tidak valid! Gunakan 00:00 - 23:59.',
            'meeting.edit.runtime.invalid_time_format': 'Format harus HH:MM (contoh: 09:00, 14:30).',
            'meeting.edit.runtime.hour': 'jam',
            'meeting.edit.runtime.minute': 'menit',
            'meeting.edit.runtime.end_after_start': 'Waktu selesai harus lebih besar dari waktu mulai!',
            'meeting.edit.runtime.processing': 'Memproses...'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('meetingEditLanguageEnglish');
    var indonesianButton = document.getElementById('meetingEditLanguageIndonesian');

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

    window.meetingEditLabel = getLabel;

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
    
    // Auto-format time as user types (HH:MM)
    $('.time-input').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
        var formatted = '';
        
        if (value.length > 0) {
            // First digit of hour (0-2)
            var firstDigit = parseInt(value[0]);
            if (firstDigit > 2) {
                formatted = '0' + firstDigit + ':';
                if (value.length > 1) {
                    formatted += value.substring(1, 3);
                }
            } else {
                formatted = value[0];
                
                if (value.length > 1) {
                    // Second digit of hour (based on first digit)
                    var secondDigit = parseInt(value[1]);
                    if (firstDigit === 2 && secondDigit > 3) {
                        secondDigit = 3; // Force max hour 23
                    }
                    formatted += secondDigit + ':';
                    
                    if (value.length > 2) {
                        // First digit of minute (0-5)
                        var minuteFirst = parseInt(value[2]);
                        if (minuteFirst > 5) {
                            minuteFirst = 5;
                        }
                        formatted += minuteFirst;
                        
                        if (value.length > 3) {
                            formatted += value[3];
                        }
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
            var match = value.match(/^(\d{1,2}):?(\d{0,2})$/);
            
            if (match) {
                var hours = parseInt(match[1], 10);
                var minutes = match[2] ? parseInt(match[2], 10) : 0;
                
                if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
                    var formatted = 
                        (hours < 10 ? '0' + hours : hours) + ':' + 
                        (minutes < 10 ? '0' + minutes : minutes);
                    $(this).val(formatted);
                    $(this).removeClass('is-invalid');
                } else {
                    $(this).addClass('is-invalid');
                    alert(window.meetingEditLabel('meeting.edit.runtime.invalid_time_range', 'Invalid time format! Use 00:00 - 23:59.'));
                    $(this).focus();
                }
            } else {
                $(this).addClass('is-invalid');
                alert(window.meetingEditLabel('meeting.edit.runtime.invalid_time_format', 'Format must be HH:MM (example: 09:00, 14:30).'));
                $(this).focus();
            }
        }
    });
    
    // Remove invalid class on focus
    $('.time-input').on('focus', function() {
        $(this).removeClass('is-invalid');
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
                var totalMinutes = Math.round(diff);
                var durationText;
                
                if (totalMinutes >= 60) {
                    var hours = Math.floor(totalMinutes / 60);
                    var minutes = totalMinutes % 60;
                    durationText = hours + ' ' + window.meetingEditLabel('meeting.edit.runtime.hour', 'hour');
                    if (minutes > 0) {
                        durationText += ' ' + minutes + ' ' + window.meetingEditLabel('meeting.edit.runtime.minute', 'minute');
                    }
                } else {
                    durationText = totalMinutes + ' ' + window.meetingEditLabel('meeting.edit.runtime.minute', 'minute');
                }
                
                $('#duration-text').text(durationText);
            }
        }
    }
    
    // Update on change
    $('#meeting_date, #start_time, #end_time').on('change', updateDateTimeFields);
    
    // Initial update
    updateDateTimeFields();
    
    // Form validation before submit
    $('#booking-form').on('submit', function(e) {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (startTime >= endTime) {
            e.preventDefault();
            alert(window.meetingEditLabel('meeting.edit.runtime.end_after_start', 'End time must be greater than start time!'));
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + window.meetingEditLabel('meeting.edit.runtime.processing', 'Processing...'));
    });
});
</script>
@endpush
