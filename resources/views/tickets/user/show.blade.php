@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-8">
        <!-- Ticket Information -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $pageTitle }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('tickets.user-index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Daftar Tiket
                    </a>
                </div>
            </div>
            
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fa fa-ticket"></i> Kode Tiket:</strong>
                        <p class="text-muted">{{ $ticket->ticket_code }}</p>
                        
                        <strong><i class="fa fa-calendar"></i> Tanggal Dibuat:</strong>
                        <p class="text-muted">{{ $ticket->created_at->format('d F Y, H:i') }} WIB</p>
                        
                        <strong><i class="fa fa-tag"></i> Kategori:</strong>
                        <p class="text-muted">
                            <span class="label label-info">{{ $ticket->ticket_type->type ?? 'N/A' }}</span>
                        </p>
                        
                        <strong><i class="fa fa-exclamation-triangle"></i> Prioritas:</strong>
                        <p class="text-muted">
                            <span class="label 
                                @if($ticket->ticket_priority->priority ?? '' == 'Urgent')
                                    label-danger
                                @elseif($ticket->ticket_priority->priority ?? '' == 'High')
                                    label-warning
                                @elseif($ticket->ticket_priority->priority ?? '' == 'Normal')
                                    label-primary
                                @else
                                    label-success
                                @endif
                            ">
                                {{ $ticket->ticket_priority->priority ?? 'Normal' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <strong><i class="fa fa-info-circle"></i> Status:</strong>
                        <p class="text-muted">
                            <span class="label 
                                @if($ticket->ticket_status->status ?? '' == 'Open')
                                    label-info
                                @elseif($ticket->ticket_status->status ?? '' == 'In Progress')
                                    label-warning
                                @elseif($ticket->ticket_status->status ?? '' == 'Resolved')
                                    label-success
                                @elseif($ticket->ticket_status->status ?? '' == 'Closed')
                                    label-default
                                @else
                                    label-default
                                @endif
                            ">
                                {{ $ticket->ticket_status->status ?? 'Unknown' }}
                            </span>
                        </p>
                        
                        @if($ticket->assignedTo)
                            <strong><i class="fa fa-user"></i> Teknisi yang Menangani:</strong>
                            <p class="text-muted">{{ $ticket->assignedTo->name }}</p>
                        @else
                            <strong><i class="fa fa-user"></i> Teknisi:</strong>
                            <p class="text-muted"><em>Belum ditugaskan</em></p>
                        @endif
                        
                        @if($ticket->location)
                            <strong><i class="fa fa-map-marker"></i> Lokasi:</strong>
                            <p class="text-muted">{{ $ticket->location->location_name }}</p>
                        @endif
                        
                        @if($ticket->asset)
                            <strong><i class="fa fa-desktop"></i> Aset:</strong>
                            <p class="text-muted">
                                {{ $ticket->asset->asset_tag }}
                                @if($ticket->asset->model)
                                    - {{ $ticket->asset->model->model_name }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
                
                <hr>
                
                <strong><i class="fa fa-edit"></i> Judul Masalah:</strong>
                <p class="text-muted">{{ $ticket->subject }}</p>
                
                <strong><i class="fa fa-comment"></i> Deskripsi:</strong>
                <div class="well well-sm">
                    {!! nl2br(e($ticket->description)) !!}
                </div>
                
                <!-- SLA Information -->
                @if($ticket->sla_due)
                    <div class="alert 
                        @if($ticket->is_overdue)
                            alert-danger
                        @elseif($ticket->sla_due->diffInHours(now()) <= 2)
                            alert-warning
                        @else
                            alert-info
                        @endif
                    ">
                        <i class="fa fa-clock-o"></i> 
                        <strong>Target Penyelesaian:</strong> {{ $ticket->sla_due->format('d F Y, H:i') }} WIB
                        @if($ticket->is_overdue)
                            <br><small>Tiket ini sudah melewati target waktu penyelesaian.</small>
                        @else
                            <br><small>{{ $ticket->time_to_sla }}</small>
                        @endif
                    </div>
                @endif
                
                <!-- Resolution (if resolved) -->
                @if($ticket->resolved_at && $ticket->resolution)
                    <div class="alert alert-success">
                        <h4><i class="fa fa-check-circle"></i> Tiket Diselesaikan</h4>
                        <p><strong>Tanggal Selesai:</strong> {{ $ticket->resolved_at->format('d F Y, H:i') }} WIB</p>
                        <p><strong>Solusi:</strong></p>
                        <div class="well well-sm">
                            {!! nl2br(e($ticket->resolution)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Ticket Timeline -->
        @if($ticketEntries->count() > 0)
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Aktivitas</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline">
                        @foreach($ticketEntries as $entry)
                            <li>
                                <i class="fa fa-comment bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fa fa-clock-o"></i> {{ $entry->created_at->format('d M Y H:i') }}
                                    </span>
                                    <h3 class="timeline-header">
                                        <strong>{{ $entry->user->name ?? 'System' }}</strong>
                                        @if($entry->user->hasRole('admin') || $entry->user->hasRole('super-admin'))
                                            <span class="label label-primary">Teknisi</span>
                                        @endif
                                    </h3>
                                    <div class="timeline-body">
                                        {!! nl2br(e($entry->body)) !!}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        <li>
                            <i class="fa fa-plus bg-green"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fa fa-clock-o"></i> {{ $ticket->created_at->format('d M Y H:i') }}
                                </span>
                                <h3 class="timeline-header">
                                    <strong>{{ $ticket->user->name }}</strong> membuat tiket
                                </h3>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Info -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
            </div>
            <div class="box-body">
                @if($ticket->ticket_status->status == 'Open')
                    <div class="alert alert-info">
                        <i class="fa fa-clock-o"></i> Tiket Anda sedang menunggu untuk ditugaskan ke teknisi.
                    </div>
                @elseif($ticket->ticket_status->status == 'In Progress')
                    <div class="alert alert-warning">
                        <i class="fa fa-cogs"></i> Teknisi sedang menangani tiket Anda.
                    </div>
                @elseif($ticket->ticket_status->status == 'Pending')
                    <div class="alert alert-warning">
                        <i class="fa fa-pause"></i> Tiket menunggu informasi atau aksi dari Anda.
                    </div>
                @elseif($ticket->ticket_status->status == 'Resolved')
                    <div class="alert alert-success">
                        <i class="fa fa-check"></i> Tiket telah diselesaikan!
                    </div>
                @endif
                
                <h5><strong>Yang Perlu Anda Ketahui:</strong></h5>
                <ul>
                    <li>Anda akan mendapat notifikasi email untuk setiap update</li>
                    <li>Jika ada informasi tambahan, teknisi akan menghubungi Anda</li>
                    <li>Tiket akan otomatis ditutup 3 hari setelah diselesaikan</li>
                </ul>
            </div>
        </div>
        
        <!-- Asset Information (if any) -->
        @if($ticket->asset)
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-desktop"></i> Informasi Aset</h3>
                </div>
                <div class="box-body">
                    <p><strong>Tag Aset:</strong> {{ $ticket->asset->asset_tag }}</p>
                    @if($ticket->asset->model)
                        <p><strong>Model:</strong> {{ $ticket->asset->model->model_name }}</p>
                    @endif
                    @if($ticket->asset->serial_number)
                        <p><strong>Serial Number:</strong> {{ $ticket->asset->serial_number }}</p>
                    @endif
                    <p><strong>Status:</strong> 
                        <span class="label label-info">{{ $ticket->asset->status->name ?? 'Unknown' }}</span>
                    </p>
                    
                    <a href="{{ route('assets.user-show', $ticket->asset->id) }}" class="btn btn-sm btn-default">
                        <i class="fa fa-eye"></i> Lihat Detail Aset
                    </a>
                </div>
            </div>
        @endif
        
        <!-- Contact Support -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-phone"></i> Butuh Bantuan?</h3>
            </div>
            <div class="box-body">
                @if($ticket->ticket_priority->priority == 'Urgent')
                    <div class="alert alert-danger">
                        <strong>Masalah Urgent?</strong><br>
                        Hubungi helpdesk segera di Ext. 123
                    </div>
                @endif
                
                <p>Jika Anda memiliki informasi tambahan atau pertanyaan:</p>
                <ul class="list-unstyled">
                    <li><i class="fa fa-phone"></i> <strong>Helpdesk:</strong> Ext. 123</li>
                    <li><i class="fa fa-envelope"></i> <strong>Email:</strong> helpdesk@company.com</li>
                </ul>
                
                <small class="text-muted">Saat menghubungi, sebutkan kode tiket: <strong>{{ $ticket->ticket_code }}</strong></small>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('{{ session('success') }}');
        });
    </script>
@endif

@endsection