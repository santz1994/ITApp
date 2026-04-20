@extends('layouts.app')

@section('main-content')

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Detail Pemesanan Ruang Rapat',
    'subtitle' => 'Meeting Room Booking Details #' . $booking->id,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Meeting Room Bookings', 'url' => route('meeting-room-bookings.index')],
        ['label' => 'Details']
    ]
])

@include('layouts.partials.module-toolbar', [
    'englishButtonId' => 'meetingShowLanguageEnglish',
    'indonesianButtonId' => 'meetingShowLanguageIndonesian',
    'ariaLabel' => 'Meeting Show Language Toggle',
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

    <div class="row">
        {{-- Main Content (8 columns) --}}
        <div class="col-xs-12 col-sm-8 col-md-8">
            
            {{-- Booking Information --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-info-circle"></i> <span data-i18n="meeting.show.section.booking">Booking Information</span>
                    </h3>
                    <div class="box-tools pull-right">
                        <span class="label {{ $booking->statusBadge }}" style="font-size: 14px;">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>ID Pemesanan / Booking ID:</dt>
                        <dd><strong>#{{ $booking->id }}</strong></dd>

                        <dt>Tanggal / Date:</dt>
                        <dd>{{ $booking->created_at->format('d-m-Y') }}</dd>

                        <dt>Ruang Rapat / Meeting Room:</dt>
                        <dd><span class="label label-info">{{ $booking->room_name }}</span></dd>

                        <dt>Diperlukan Pada / Required On:</dt>
                        <dd>
                            <i class="fa fa-calendar"></i> 
                            <strong>{{ $booking->start_datetime->format('l, d F Y') }}</strong>
                            <br>
                            <i class="fa fa-clock-o"></i> 
                            {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                            <span class="label label-default">{{ $booking->duration }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Requester Information --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-user"></i> <span data-i18n="meeting.show.section.requester">Requester Information</span>
                    </h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Nama Pemohon / Name:</dt>
                        <dd><strong>{{ $booking->user->name ?? 'N/A' }}</strong></dd>

                        <dt>Jabatan / Position:</dt>
                        <dd>{{ $booking->requester_position ?? 'N/A' }}</dd>

                        <dt>Bagian/Departemen / Department:</dt>
                        <dd>{{ $booking->department ?? 'N/A' }}</dd>

                        <dt>Estimasi Peserta / Attendees:</dt>
                        <dd>
                            <span class="badge bg-blue">{{ $booking->attendees_count }}</span> orang / persons
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Meeting Details --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-file-text"></i> <span data-i18n="meeting.show.section.details">Meeting Details</span>
                    </h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Keperluan Rapat / Purpose:</dt>
                        <dd><strong>{{ $booking->purpose }}</strong></dd>

                        <dt>Deskripsi/Keterangan / Description:</dt>
                        <dd>
                            <div class="well well-sm">
                                {{ $booking->meeting_description }}
                            </div>
                        </dd>

                        @if($booking->meeting_needs)
                        <dt>Kebutuhan Fasilitas / Facilities:</dt>
                        <dd>
                            <div class="well well-sm">
                                {{ $booking->meeting_needs }}
                            </div>
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Approval Information --}}
            @if($booking->status == 'approved' || $booking->status == 'finished')
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-check-circle"></i> 
                        Persetujuan / Approval
                    </h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        @if($booking->approved_by)
                        <dt>Disetujui oleh / Approved by:</dt>
                        <dd><strong>{{ $booking->approver->name ?? 'N/A' }}</strong></dd>

                        <dt>Tanggal / Date:</dt>
                        <dd>{{ $booking->approved_at ? $booking->approved_at->format('d-m-Y H:i') : 'N/A' }}</dd>
                        @endif

                        @if($booking->director_notes)
                        <dt>Catatan / Notes:</dt>
                        <dd>
                            <div class="alert alert-success">
                                {{ $booking->director_notes }}
                            </div>
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>
            @endif
            
            {{-- Rejection Information --}}
            @if($booking->status == 'rejected')
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-times-circle"></i> 
                        Penolakan / Rejection
                    </h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        @if($booking->approved_by)
                        <dt>Ditolak oleh / Rejected by:</dt>
                        <dd><strong>{{ $booking->approver->name ?? 'N/A' }}</strong></dd>

                        <dt>Tanggal / Date:</dt>
                        <dd>{{ $booking->approved_at ? $booking->approved_at->format('d-m-Y H:i') : 'N/A' }}</dd>
                        @endif

                        @if($booking->director_notes)
                        <dt>Catatan / Notes:</dt>
                        <dd>
                            <div class="alert alert-danger">
                                {{ $booking->director_notes }}
                            </div>
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            {{-- Approve/Reject Buttons (Director/Administrator Only - Prominent Display) --}

                            {{-- Back Button --}}
                            <a href="{{ route('meeting-room-bookings.index') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-arrow-left"></i> <span data-i18n="meeting.show.action.back">Back</span>
                            </a>

                            {{-- Edit Button (Owner if Pending & Future, OR Receptionist/Administrator if not started, OR Developer anytime) --}}
                            @if(
                                ($booking->user_id == Auth::id() && $booking->canBeEdited()) ||
                                (user_has_role(Auth::user(), 'receptionist') && $booking->canBeEditedByReceptionist()) ||
                                (user_has_role(Auth::user(), 'administrator') && $booking->canBeEditedByReceptionist()) ||
                                user_has_role(Auth::user(), 'developer')
                            )
                            <a href="{{ route('meeting-room-bookings.edit', $booking->id) }}" class="btn btn-primary btn-lg">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            @endif

                            {{-- Print Button --}}
                                @if(user_has_role(Auth::user(), 'receptionist') || user_has_role(Auth::user(), 'administrator') || $booking->user_id == Auth::id())
                            <a href="{{ route('meeting-room-bookings.print', $booking->id) }}" 
                               class="btn btn-info btn-lg" target="_blank">
                                <i class="fa fa-print"></i> Print
                            </a>
                            @endif

                            {{-- Cancel Button (Receptionist Only) --}}
                                @if((user_has_role(Auth::user(), 'receptionist') || user_has_role(Auth::user(), 'administrator')) && $booking->canBeCancelled())
                            <form action="{{ route('meeting-room-bookings.cancel', $booking->id) }}" 
                                    method="POST" style="display: inline;"
                                    data-confirm-title="Cancel Booking"
                                    data-confirm-i18n-key="meeting.show.runtime.confirm.cancel"
                                    data-confirm-message="Are you sure you want to cancel this booking?"
                                    data-confirm-button="Cancel Booking"
                                    data-confirm-class="btn-warning"
                                    data-disable-on-submit="true">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-lg">
                                                                        <i class="fa fa-ban"></i> <span data-i18n="meeting.show.action.cancel">Cancel</span>
                                </button>
                            </form>
                            @endif

                            {{-- Finish Button (Receptionist/Superadmin/Director/Management Only) --}}
                                @if((user_has_any_role(Auth::user(), ['receptionist', 'developer', 'director'])) && $booking->canBeFinished())
                            <form action="{{ route('meeting-room-bookings.finish', $booking->id) }}" 
                                    method="POST" style="display: inline;"
                                    data-confirm-title="Finish Meeting"
                                    data-confirm-i18n-key="meeting.show.runtime.confirm.finish"
                                    data-confirm-message="Mark this meeting as finished?"
                                    data-confirm-button="Finish"
                                    data-confirm-class="btn-success"
                                    data-disable-on-submit="true">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg">
                                                                        <i class="fa fa-check"></i> <span data-i18n="meeting.show.action.finish">Finish</span>
                                </button>
                            </form>
                            @endif

                            {{-- Extend Time Button (User/Receptionist/Superadmin for ongoing meetings) --}}
                                @if(($booking->user_id == Auth::id() || user_has_any_role(Auth::user(), ['receptionist', 'developer'])) 
                                && $booking->status == 'approved' 
                                && $booking->start_datetime <= now() 
                                && $booking->end_datetime >= now())
                            <button type="button" class="btn btn-info btn-lg" 
                                    data-toggle="modal" data-target="#extendTimeModal">
                                <i class="fa fa-clock-o"></i> <span data-i18n="meeting.show.action.extend">Extend Time</span>
                            </button>
                            @endif

                            {{-- Quick Edit Subject Button (Receptionist/Admin for pending/approved meetings) --}}
                            @if(user_has_any_role(Auth::user(), ['receptionist', 'developer']) 
                                && in_array($booking->status, ['pending', 'approved']))
                            <button type="button" class="btn btn-primary btn-lg" 
                                    data-toggle="modal" data-target="#quickEditSubjectModal">
                                <i class="fa fa-pencil"></i> <span data-i18n="meeting.show.action.quick_edit_subject">Edit Subject</span>
                            </button>
                            @endif

                            {{-- Quick Edit Time Button (Receptionist/Admin for pending/approved future meetings) --}}
                            @if(user_has_any_role(Auth::user(), ['receptionist', 'developer']) 
                                && in_array($booking->status, ['pending', 'approved'])
                                && $booking->start_datetime->isFuture())
                            <button type="button" class="btn btn-warning btn-lg" 
                                    data-toggle="modal" data-target="#quickEditTimeModal">
                                <i class="fa fa-clock-o"></i> <span data-i18n="meeting.show.action.quick_edit_time">Edit Time</span>
                            </button>
                            @endif

                            {{-- Delete Button (Owner if Pending, OR Super-admin) --}}
                                @if(($booking->user_id == Auth::id() && $booking->canBeEdited()) || user_has_role(Auth::user(), 'developer'))
                            <form action="{{ route('meeting-room-bookings.destroy', $booking->id) }}" 
                                    method="POST" style="display: inline;"
                                    data-confirm-title="Delete Booking"
                                    data-confirm-i18n-key="meeting.show.runtime.confirm.delete"
                                    data-confirm-message="Are you sure you want to delete this booking?"
                                    data-confirm-button="Delete"
                                    data-confirm-class="btn-danger"
                                    data-disable-on-submit="true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-lg">
                                                                        <i class="fa fa-trash"></i> <span data-i18n="meeting.show.action.delete">Delete</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar (4 columns) --}}
        <div class="col-xs-12 col-sm-4 col-md-4">
            
            {{-- Status Timeline --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Status Timeline</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline timeline-inverse">
                        {{-- Created --}}
                        <li>
                            <i class="fa fa-calendar bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $booking->created_at->format('d M Y H:i') }}</span>
                                <h3 class="timeline-header">Dibuat / Created</h3>
                                <div class="timeline-body">
                                    Pemesanan dibuat oleh <strong>{{ $booking->user->name }}</strong>
                                </div>
                            </div>
                        </li>

                        {{-- Approved/Rejected --}}
                        @if($booking->status == 'approved' || $booking->status == 'rejected')
                        <li>
                            <i class="fa fa-{{ $booking->status == 'approved' ? 'check' : 'times' }} bg-{{ $booking->status == 'approved' ? 'green' : 'red' }}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $booking->approved_at ? $booking->approved_at->format('d M Y H:i') : 'N/A' }}</span>
                                <h3 class="timeline-header">
                                    {{ $booking->status == 'approved' ? 'Disetujui / Approved' : 'Ditolak / Rejected' }}
                                </h3>
                                <div class="timeline-body">
                                    Oleh <strong>{{ $booking->approver->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </li>
                        @endif
                        
                        {{-- Finished --}}
                        @if($booking->status == 'finished')
                        <li>
                            <i class="fa fa-check-circle bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $booking->updated_at->format('d M Y H:i') }}</span>
                                <h3 class="timeline-header">
                                    Selesai / Finished
                                </h3>
                                <div class="timeline-body">
                                    Meeting telah selesai / Meeting has been completed
                                </div>
                            </div>
                        </li>
                        @endif

                        {{-- End --}}
                        <li>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Approve/Reject Actions (Director Only) --}}
            @if(user_has_role(Auth::user(), 'director') || user_has_role(Auth::user(), 'administrator'))
                @if($booking->status == 'pending' && $booking->canBeApproved())
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-check-circle"></i> Persetujuan / Approval</h3>
                    </div>
                    <div class="box-body">
                        {{-- Approve Form --}}
                        <form action="{{ route('meeting-room-bookings.approve', $booking->id) }}" method="POST"
                            data-confirm-title="Approve Booking"
                            data-confirm-i18n-key="meeting.show.runtime.confirm.approve"
                            data-confirm-message="Approve this booking?"
                            data-confirm-button="Approve"
                            data-confirm-class="btn-success"
                            data-disable-on-submit="true">
                            @csrf
                            <div class="form-group">
                                <label for="approve_notes">Catatan (Opsional) / Notes (Optional)</label>
                                <textarea name="director_notes" id="approve_notes" class="form-control" rows="3"
                                          placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block btn-lg">
                                <i class="fa fa-check"></i> Setujui / Approve
                            </button>
                        </form>

                        <hr>

                        {{-- Reject Form --}}
                        <button type="button" class="btn btn-danger btn-block btn-lg" 
                                data-toggle="modal" data-target="#rejectModal">
                            <i class="fa fa-times"></i> Tolak / Reject
                        </button>
                    </div>
                </div>
                @endif
            @endif

            {{-- Booking Info --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Info</h3>
                </div>
                <div class="box-body">
                    <p><i class="fa fa-calendar"></i> <strong>Dibuat:</strong><br>{{ $booking->created_at->format('d F Y, H:i') }}</p>
                    <p><i class="fa fa-refresh"></i> <strong>Terakhir diubah:</strong><br>{{ $booking->updated_at->format('d F Y, H:i') }}</p>
                    <p><i class="fa fa-hourglass-half"></i> <strong>Durasi:</strong><br>{{ $booking->duration }}</p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('meeting-room-bookings.approve', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-green">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-check-circle"></i> Setujui Pemesanan / Approve Booking
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Anda akan menyetujui pemesanan ruang rapat ini. 
                        <br><strong>You are about to approve this meeting room booking.</strong>
                    </div>
                    <div class="form-group">
                        <label>Catatan (Opsional) / Notes (Optional)</label>
                        <textarea name="director_notes" class="form-control" rows="3" 
                                  placeholder="Tambahkan catatan jika diperlukan... / Add notes if needed..."></textarea>
                        <small class="text-muted">Catatan ini akan terlihat oleh pemohon / These notes will be visible to the requester</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal / Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-check-circle"></i> <strong>Setujui / Approve</strong>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('meeting-room-bookings.reject', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-red">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-times-circle"></i> Tolak Pemesanan / Reject Booking
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Anda akan menolak pemesanan ruang rapat ini. 
                        <br><strong>You are about to reject this meeting room booking.</strong>
                    </div>
                    <div class="form-group">
                        <label>Alasan Penolakan / Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="director_notes" class="form-control" rows="4" 
                                  placeholder="Jelaskan alasan penolakan... / Explain rejection reason..." 
                                  required minlength="10"></textarea>
                        <small class="text-muted">Minimal 10 karakter / Minimum 10 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal / Cancel</button>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fa fa-times-circle"></i> <strong>Tolak / Reject</strong>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.dl-horizontal dt {
    width: 200px;
    font-weight: 600;
}

.dl-horizontal dd {
    margin-left: 220px;
}

.timeline {
    margin-bottom: 0;
}

.btn-group-justified {
    display: table;
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
}

.btn-group-justified .btn {
    float: none;
    display: table-cell;
    width: 1%;
}

/* Prominent Approve/Reject Buttons */
.btn-group .btn-success.btn-lg {
    background-color: #00a65a;
    border-color: #008d4c;
    font-size: 16px;
    padding: 12px 24px;
    transition: all 0.3s ease;
}

.btn-group .btn-success.btn-lg:hover {
    background-color: #008d4c;
    border-color: #007a3e;
    box-shadow: 0 4px 8px rgba(0, 166, 90, 0.3);
}

.btn-group .btn-danger.btn-lg {
    background-color: #dd4b39;
    border-color: #d73925;
    font-size: 16px;
    padding: 12px 24px;
    transition: all 0.3s ease;
}

.btn-group .btn-danger.btn-lg:hover {
    background-color: #d73925;
    border-color: #c23321;
    box-shadow: 0 4px 8px rgba(221, 75, 57, 0.3);
}

/* Modal Header Colors */
.modal-header.bg-green {
    background-color: #00a65a;
    color: white;
    border-bottom: 2px solid #008d4c;
}

.modal-header.bg-red {
    background-color: #dd4b39;
    color: white;
    border-bottom: 2px solid #d73925;
}

.modal-header.bg-green .close,
.modal-header.bg-red .close {
    color: white;
    opacity: 0.8;
}

.modal-header.bg-green .close:hover,
.modal-header.bg-red .close:hover {
    opacity: 1;
}
</style>
@endpush

{{-- Extend Time Modal --}}
<div class="modal fade" id="extendTimeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-clock-o"></i> Perpanjang Waktu Meeting / Extend Meeting Time
                </h4>
            </div>
            <form id="extendTimeForm" action="{{ route('meeting-room-bookings.extend', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Info:</strong> Perpanjangan waktu hanya diizinkan jika tidak bentrok dengan booking selanjutnya.
                        <br><small>Extension is only allowed if there's no conflict with the next booking.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Waktu Selesai Saat Ini / Current End Time:</label>
                        <p class="form-control-static">
                            <strong>{{ $booking->end_datetime->format('H:i') }}</strong>
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_end_time">
                            Waktu Selesai Baru / New End Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="new_end_time" id="new_end_time" class="form-control" 
                               min="{{ $booking->end_datetime->format('H:i') }}" required>
                        <small class="help-block">Pilih waktu selesai yang lebih lama dari waktu saat ini</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="extend_reason">
                            Alasan Perpanjangan / Extension Reason <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea name="extend_reason" id="extend_reason" class="form-control" rows="3"
                                  placeholder="e.g., Diskusi masih berlangsung, perlu waktu tambahan untuk finalisasi"></textarea>
                    </div>
                    
                    <div id="conflictAlert" class="alert alert-danger" style="display: none;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Konflik Terdeteksi!</strong> Waktu yang dipilih bentrok dengan booking lain.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal / Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="extendSubmitBtn">
                        <i class="fa fa-clock-o"></i> <span data-i18n="meeting.show.action.extend">Extend Time</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
;(function() {
    var translations = {
        en: {
            'meeting.show.section.booking': 'Booking Information',
            'meeting.show.section.requester': 'Requester Information',
            'meeting.show.section.details': 'Meeting Details',
            'meeting.show.action.back': 'Back',
            'meeting.show.action.cancel': 'Cancel',
            'meeting.show.action.finish': 'Finish',
            'meeting.show.action.extend': 'Extend Time',
            'meeting.show.action.quick_edit_subject': 'Edit Subject',
            'meeting.show.action.quick_edit_time': 'Edit Time',
            'meeting.show.action.quick_save': 'Save',
            'meeting.show.action.delete': 'Delete',
            'meeting.show.runtime.confirm.cancel': 'Are you sure you want to cancel this booking?',
            'meeting.show.runtime.confirm.finish': 'Mark this meeting as finished?',
            'meeting.show.runtime.confirm.delete': 'Are you sure you want to delete this booking?',
            'meeting.show.runtime.confirm.approve': 'Approve this booking?',
            'meeting.show.runtime.processing': 'Processing...',
            'meeting.show.runtime.saving': 'Saving...',
            'meeting.show.runtime.extend_success': 'Meeting time extended successfully!',
            'meeting.show.runtime.extend_failed': 'Failed to extend time',
            'meeting.show.runtime.error_prefix': 'Error:',
            'meeting.show.runtime.update_failed': 'Failed to update',
            'meeting.show.runtime.update_subject_failed': 'Failed to update subject',
            'meeting.show.runtime.update_time_failed': 'Failed to update time',
            'meeting.show.runtime.end_time_after_start': 'End time must be later than start time!'
        },
        id: {
            'meeting.show.section.booking': 'Informasi Pemesanan',
            'meeting.show.section.requester': 'Informasi Pemohon',
            'meeting.show.section.details': 'Detail Rapat',
            'meeting.show.action.back': 'Kembali',
            'meeting.show.action.cancel': 'Batalkan',
            'meeting.show.action.finish': 'Selesai',
            'meeting.show.action.extend': 'Perpanjang Waktu',
            'meeting.show.action.quick_edit_subject': 'Edit Subjek',
            'meeting.show.action.quick_edit_time': 'Edit Waktu',
            'meeting.show.action.quick_save': 'Simpan',
            'meeting.show.action.delete': 'Hapus',
            'meeting.show.runtime.confirm.cancel': 'Apakah Anda yakin ingin membatalkan pemesanan ini?',
            'meeting.show.runtime.confirm.finish': 'Tandai meeting ini sebagai selesai?',
            'meeting.show.runtime.confirm.delete': 'Apakah Anda yakin ingin menghapus pemesanan ini?',
            'meeting.show.runtime.confirm.approve': 'Setujui pemesanan ini?',
            'meeting.show.runtime.processing': 'Memproses...',
            'meeting.show.runtime.saving': 'Menyimpan...',
            'meeting.show.runtime.extend_success': 'Waktu meeting berhasil diperpanjang!',
            'meeting.show.runtime.extend_failed': 'Gagal memperpanjang waktu meeting',
            'meeting.show.runtime.error_prefix': 'Kesalahan:',
            'meeting.show.runtime.update_failed': 'Gagal memperbarui data',
            'meeting.show.runtime.update_subject_failed': 'Gagal memperbarui subjek',
            'meeting.show.runtime.update_time_failed': 'Gagal memperbarui waktu',
            'meeting.show.runtime.end_time_after_start': 'Waktu selesai harus lebih besar dari waktu mulai!'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('meetingShowLanguageEnglish');
    var indonesianButton = document.getElementById('meetingShowLanguageIndonesian');

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

    window.meetingShowLabel = getLabel;
    window.meetingShowConfirm = function(key, fallback) {
        return window.confirm(getLabel(key, fallback));
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
    // Check for conflict when time is selected
    $('#new_end_time').on('change', function() {
        const newEndTime = $(this).val();
        if (!newEndTime) return;
        
        // AJAX call to check conflict
        $.ajax({
            url: '{{ route("meeting-room-bookings.index") }}',
            method: 'GET',
            data: {
                ajax_check_extend: 1,
                booking_id: {{ $booking->id }},
                room_name: {{ json_encode($booking->room_name) }},
                date: '{{ $booking->start_datetime->format("Y-m-d") }}',
                new_end_time: newEndTime
            },
            success: function(response) {
                if (response.conflict) {
                    $('#conflictAlert').fadeIn();
                    $('#extendSubmitBtn').prop('disabled', true);
                } else {
                    $('#conflictAlert').fadeOut();
                    $('#extendSubmitBtn').prop('disabled', false);
                }
            }
        });
    });
    
    // Handle form submission
    $('#extendTimeForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#extendSubmitBtn');
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + window.meetingShowLabel('meeting.show.runtime.processing', 'Processing...'));
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alert(window.meetingShowLabel('meeting.show.runtime.extend_success', 'Meeting time extended successfully!'));
                    location.reload();
                } else {
                    alert(window.meetingShowLabel('meeting.show.runtime.error_prefix', 'Error:') + ' ' + (response.message || window.meetingShowLabel('meeting.show.runtime.extend_failed', 'Failed to extend time')));
                    submitBtn.prop('disabled', false).html('<i class="fa fa-clock-o"></i> ' + window.meetingShowLabel('meeting.show.action.extend', 'Extend Time'));
                }
            },
            error: function(xhr) {
                alert(window.meetingShowLabel('meeting.show.runtime.error_prefix', 'Error:') + ' ' + (xhr.responseJSON?.message || window.meetingShowLabel('meeting.show.runtime.extend_failed', 'Failed to extend time')));
                submitBtn.prop('disabled', false).html('<i class="fa fa-clock-o"></i> ' + window.meetingShowLabel('meeting.show.action.extend', 'Extend Time'));
            }
        });
    });
    
    // Approve booking function
    window.approveBooking = function(id) {
        $('#approveModal').modal('show');
    };
    
    // Reject booking function
    window.rejectBooking = function(id) {
        $('#rejectModal').modal('show');
    };
});
</script>
@endpush
{{-- Quick Edit Subject Modal (Receptionist Only) --}}
<div class="modal fade" id="quickEditSubjectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-pencil"></i> Edit Subjek Meeting / Edit Meeting Subject
                </h4>
            </div>
            <form id="quickEditSubjectForm" action="{{ route('meeting-room-bookings.quick-edit-subject', $booking->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Info:</strong> Fitur ini memungkinkan receptionist untuk mengedit subjek meeting tanpa mengubah waktu atau ruangan.
                        <br><small>This feature allows receptionist to edit meeting subject without changing time or room.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_purpose">
                            Keperluan Rapat / Meeting Purpose <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="purpose" id="edit_purpose" class="form-control" 
                               value="{{ htmlspecialchars($booking->purpose, ENT_QUOTES, 'UTF-8') }}" required minlength="10" maxlength="500">
                        <small class="help-block">Minimal 10 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_meeting_description">
                            Deskripsi / Keterangan Rapat <span class="text-danger">*</span>
                        </label>
                        <textarea name="meeting_description" id="edit_meeting_description" class="form-control" 
                                  rows="4" required minlength="10" maxlength="1000">{{ htmlspecialchars($booking->meeting_description, ENT_QUOTES, 'UTF-8') }}</textarea>
                        <small class="help-block">Minimal 10 karakter</small>
                    </div>
                    
                    <div id="subjectEditAlert" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> <span data-i18n="meeting.show.action.cancel">Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-primary" id="subjectEditSubmitBtn">
                        <i class="fa fa-save"></i> <span data-i18n="meeting.show.action.quick_save">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick Edit Time Modal (Receptionist Only) --}}
<div class="modal fade" id="quickEditTimeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-clock-o"></i> Edit Waktu Meeting / Edit Meeting Time
                </h4>
            </div>
            <form id="quickEditTimeForm" action="{{ route('meeting-room-bookings.quick-edit-time', $booking->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Mengubah waktu meeting akan mengecek konflik dengan booking lain di ruangan yang sama.
                        <br><small>Changing meeting time will check for conflicts with other bookings in the same room.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Ruang Rapat / Meeting Room:</label>
                        <p class="form-control-static"><strong>{{ $booking->room_name }}</strong></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_meeting_date">
                            Tanggal Rapat / Meeting Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="meeting_date" id="edit_meeting_date" class="form-control" 
                               value="{{ $booking->start_datetime->format('Y-m-d') }}" 
                               min="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label for="edit_start_time">
                                    Waktu Mulai / Start Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="start_time" id="edit_start_time" class="form-control" 
                                       value="{{ $booking->start_datetime->format('H:i') }}" required>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label for="edit_end_time">
                                    Waktu Selesai / End Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="end_time" id="edit_end_time" class="form-control" 
                                       value="{{ $booking->end_datetime->format('H:i') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Durasi Saat Ini:</strong> {{ $booking->duration }}
                    </div>
                    
                    <div id="timeEditAlert" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> <span data-i18n="meeting.show.action.cancel">Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-warning" id="timeEditSubmitBtn">
                        <i class="fa fa-save"></i> <span data-i18n="meeting.show.action.quick_save">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // ================================================
    // QUICK EDIT SUBJECT FORM HANDLER
    // ================================================
    $('#quickEditSubjectForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#subjectEditSubmitBtn');
        const alertBox = $('#subjectEditAlert');
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + window.meetingShowLabel('meeting.show.runtime.saving', 'Saving...'));
        alertBox.hide();
        
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alertBox.removeClass('alert-danger').addClass('alert-success')
                            .html('<i class="fa fa-check-circle"></i> ' + response.message)
                            .fadeIn();
                    
                    // Update page content
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alertBox.removeClass('alert-success').addClass('alert-danger')
                            .html('<i class="fa fa-exclamation-triangle"></i> ' + (response.message || window.meetingShowLabel('meeting.show.runtime.update_failed', 'Failed to update')))
                            .fadeIn();
                    submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> ' + window.meetingShowLabel('meeting.show.action.quick_save', 'Save'));
                }
            },
            error: function(xhr) {
                let errorMsg = window.meetingShowLabel('meeting.show.runtime.update_subject_failed', 'Failed to update subject');
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                }
                
                alertBox.removeClass('alert-success').addClass('alert-danger')
                        .html('<i class="fa fa-exclamation-triangle"></i> ' + errorMsg)
                        .fadeIn();
                submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> ' + window.meetingShowLabel('meeting.show.action.quick_save', 'Save'));
            }
        });
    });
    
    // ================================================
    // QUICK EDIT TIME FORM HANDLER
    // ================================================
    $('#quickEditTimeForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#timeEditSubmitBtn');
        const alertBox = $('#timeEditAlert');
        
        // Validate end time is after start time (client-side check)
        const startTime = $('#edit_start_time').val();
        const endTime = $('#edit_end_time').val();
        
        if (endTime <= startTime) {
            alertBox.removeClass('alert-success').addClass('alert-danger')
                    .html('<i class="fa fa-exclamation-triangle"></i> ' + window.meetingShowLabel('meeting.show.runtime.end_time_after_start', 'End time must be later than start time!'))
                    .fadeIn();
            return false;
        }
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + window.meetingShowLabel('meeting.show.runtime.saving', 'Saving...'));
        alertBox.hide();
        
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alertBox.removeClass('alert-danger').addClass('alert-success')
                            .html('<i class="fa fa-check-circle"></i> ' + response.message)
                            .fadeIn();
                    
                    // Update page content
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alertBox.removeClass('alert-success').addClass('alert-danger')
                            .html('<i class="fa fa-exclamation-triangle"></i> ' + (response.message || window.meetingShowLabel('meeting.show.runtime.update_failed', 'Failed to update')))
                            .fadeIn();
                    submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> ' + window.meetingShowLabel('meeting.show.action.quick_save', 'Save'));
                }
            },
            error: function(xhr) {
                let errorMsg = window.meetingShowLabel('meeting.show.runtime.update_time_failed', 'Failed to update time');
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                }
                
                alertBox.removeClass('alert-success').addClass('alert-danger')
                        .html('<i class="fa fa-exclamation-triangle"></i> ' + errorMsg)
                        .fadeIn();
                submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> ' + window.meetingShowLabel('meeting.show.action.quick_save', 'Save'));
            }
        });
    });
});
</script>
@endpush