@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Tiket Belum Ditangani</h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-toggle="tooltip" title="Refresh" onclick="location.reload()">
                        <i class="fa fa-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                @if($tickets->count() > 0)
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Ada <strong>{{ $tickets->count() }}</strong> tiket yang belum ditangani. 
                    Klik tombol <strong>"Ambil"</strong> untuk menangani tiket.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="120">Kode Tiket</th>
                                <th>Pengirim</th>
                                <th>Lokasi</th>
                                <th>Prioritas</th>
                                <th>Tipe</th>
                                <th>Subjek</th>
                                <th>Aset</th>
                                <th width="100">Dibuat</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <span class="label label-default">{{ $ticket->ticket_code ?? 'TIK-'.date('Ymd').'-'.str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <strong>{{ $ticket->user->name }}</strong><br>
                                    <small class="text-muted">{{ $ticket->user->email }}</small>
                                </td>
                                <td>{{ $ticket->location->location_name ?? '-' }}</td>
                                <td>
                                    @if($ticket->ticket_priority)
                                        @if($ticket->ticket_priority->priority == 'Critical')
                                            <span class="label label-danger">
                                        @elseif($ticket->ticket_priority->priority == 'High')
                                            <span class="label label-warning">
                                        @elseif($ticket->ticket_priority->priority == 'Medium')
                                            <span class="label label-info">
                                        @else
                                            <span class="label label-success">
                                        @endif
                                        {{ $ticket->ticket_priority->priority }}</span>
                                    @else
                                        <span class="label label-default">-</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->ticket_type->type ?? '-' }}</td>
                                <td>
                                    <strong>{{ \Illuminate\Support\Str::limit($ticket->subject, 40) }}</strong>
                                    @if(strlen($ticket->description) > 0)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($ticket->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->asset)
                                        <span class="label label-primary">{{ $ticket->asset->asset_tag }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $ticket->created_at->format('d/m H:i') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('tickets.self-assign', $ticket) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Apakah Anda yakin ingin mengambil tiket ini untuk ditangani?')">
                                            <i class="fa fa-hand-grab-o"></i> Ambil
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> Tidak ada tiket yang belum ditangani. Semua tiket sudah di-assign!
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Auto refresh setiap 30 detik -->
<script>
setTimeout(function(){
    location.reload();
}, 30000);
</script>
@endsection