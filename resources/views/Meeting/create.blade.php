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

<div class="container-fluid">
    <div class="row">
        {{-- Main Form (8 columns) --}}
        <div class="col-xs-12 col-sm-8 col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-calendar-plus-o"></i> Booking Details / Detail Pemesanan
                    </h3>
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

                    <form action="{{ route('meeting-room-bookings.store') }}" method="POST" id="booking-form">
                        @csrf

                        {{-- Section 1: Informasi Pemohon (Requester Information) --}}
                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-user"></i></span> 
                                Informasi Pemohon / Requester Information
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
                                                   value="{{ Auth::user()->name }}" disabled>
                                        </div>
                                        <small class="help-text text-muted">Nama Anda (otomatis terisi)</small>
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
                                                   value="{{ old('requester_position') }}" 
                                                   placeholder="e.g., Staff IT, Manager Marketing" required>
                                        </div>
                                        <small class="help-text text-muted">Masukkan jabatan Anda</small>
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
                                                   value="{{ old('department') }}" 
                                                   placeholder="e.g., IT Department, Marketing" required>
                                        </div>
                                        <small class="help-text text-muted">Bagian/departemen Anda</small>
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
                                Detail Rapat / Meeting Details
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
                                                <option value="{{ $room }}" {{ old('room_name') == $room ? 'selected' : '' }}>
                                                    {{ $room }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text text-muted">Pilih ruang rapat yang diinginkan</small>
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
                                                   value="{{ old('attendees_count', 1) }}" 
                                                   min="1" max="100" required>
                                            <span class="input-group-addon">orang / persons</span>
                                        </div>
                                        <small class="help-text text-muted">Jumlah peserta rapat (1-100)</small>
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
                                           value="{{ old('purpose') }}" 
                                           placeholder="e.g., Review Proyek Q4, Training Karyawan" 
                                           required minlength="10">
                                </div>
                                <small class="help-text text-muted">Tujuan/keperluan rapat (minimal 10 karakter)</small>
                                @error('purpose')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="meeting_description">
                                    Deskripsi / Keterangan Rapat <span class="text-danger">*</span>
                                </label>
                                <textarea name="meeting_description" id="meeting_description" 
                                          class="form-control @error('meeting_description') is-invalid @enderror" 
                                          rows="3" required minlength="10"
                                          placeholder="Jelaskan detail rapat, agenda, dan hal-hal penting lainnya...">{{ old('meeting_description') }}</textarea>
                                <small class="help-text text-muted">Deskripsi lengkap rapat (minimal 10 karakter)</small>
                                @error('meeting_description')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="meeting_needs">
                                    Kebutuhan Fasilitas / Facility Needs <span class="text-muted">(Optional)</span>
                                </label>
                                <textarea name="meeting_needs" id="meeting_needs" 
                                          class="form-control @error('meeting_needs') is-invalid @enderror" 
                                          rows="2"
                                          placeholder="e.g., Proyektor, Whiteboard, Sound System, Snack & Coffee">{{ old('meeting_needs') }}</textarea>
                                <small class="help-text text-muted">Fasilitas tambahan yang dibutuhkan (opsional)</small>
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
                                <strong>Durasi Rapat / Duration:</strong> 
                                <span id="duration-text">0</span>
                            </div>

                            {{-- Conflict Check Result --}}
                            <div class="alert alert-warning" id="conflict-warning" style="display: none;">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>Perhatian!</strong> Ruang ini sudah dibooking pada waktu tersebut.
                                <br>Silakan pilih ruang lain atau waktu yang berbeda.
                            </div>
                        </fieldset>

                        {{-- Hidden Fields for Combined DateTime --}}
                        <input type="hidden" name="start_datetime" id="start_datetime">
                        <input type="hidden" name="end_datetime" id="end_datetime">

                        {{-- Submit Buttons --}}
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                <i class="fa fa-paper-plane"></i> Kirim Permohonan / Submit Request
                            </button>
                            <a href="{{ route('meeting-room-bookings.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> Batal / Cancel
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
                    <h3 class="box-title"><i class="fa fa-calendar-o"></i> Jadwal Hari Ini</h3>
                </div>
                <div class="box-body">
                    <div id="today-schedule">
                        <div class="text-center text-muted">
                            <i class="fa fa-spinner fa-spin"></i> Loading...
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
                    $(this).after('<span class="text-danger time-error">Format waktu tidak valid! Gunakan format 24 jam (00:00 - 23:59)</span>');
                    setTimeout(function() { $('.time-error').remove(); }, 3000);
                }
            } else {
                // Invalid format
                $(this).addClass('is-invalid');
                $(this).after('<span class="text-danger time-error">Format harus HH:MM (contoh: 09:00, 14:30)</span>');
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
            alert('Waktu selesai harus lebih besar dari waktu mulai!\nEnd time must be after start time!');
            return false;
        }
        
        // Calculate duration in minutes
        var start = new Date('2000-01-01 ' + startTime);
        var end = new Date('2000-01-01 ' + endTime);
        var durationMinutes = (end - start) / 1000 / 60;
        
        // Check minimum duration (30 minutes)
        if (durationMinutes < 30) {
            e.preventDefault();
            alert('Durasi rapat minimal 30 menit!\nMinimum meeting duration is 30 minutes!\n\nDurasi saat ini: ' + durationMinutes + ' menit');
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
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
                    'Lihat jadwal lengkap di halaman berikut:</p>' +
                    '<a href="{{ route("meeting-room-bookings.lcd-dashboard") }}" class="btn btn-sm btn-info btn-block" target="_blank">' +
                    '<i class="fa fa-desktop"></i> LCD Dashboard' +
                    '</a>' +
                    '<a href="{{ route("meeting-room-bookings.calendar") }}" class="btn btn-sm btn-primary btn-block" style="margin-top: 5px;">' +
                    '<i class="fa fa-calendar"></i> Calendar View' +
                    '</a>'
                );
            },
            error: function() {
                $('#today-schedule').html(
                    '<p class="text-muted"><i class="fa fa-exclamation-circle"></i> ' +
                    'Gagal memuat jadwal</p>'
                );
            }
        });
    }
});
</script>
@endpush
