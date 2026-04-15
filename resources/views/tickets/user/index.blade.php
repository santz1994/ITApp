@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $pageTitle }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('tickets.user-create') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Buat Tiket Baru
                    </a>
                </div>
            </div>
            
            <!-- Filter Form -->
            <div class="box-body">
                <form method="GET" action="{{ route('tickets.user-index') }}" class="form-inline" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="search">Cari:</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Kode tiket atau judul...">
                    </div>
                    
                    <div class="form-group" style="margin-left: 15px;">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                    {{ $status->status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    
                    <a href="{{ route('tickets.user-index') }}" class="btn btn-default" style="margin-left: 5px;">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-body">
                @if($tickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Aset</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td>
                                            <strong>{{ $ticket->ticket_code }}</strong>
                                        </td>
                                        <td>{{ $ticket->subject }}</td>
                                        <td>
                                            <span class="label label-info">{{ $ticket->ticket_type->type ?? 'N/A' }}</span>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <span class="label 
                                                @if($ticket->ticket_status->status ?? '' == 'Open')
                                                    label-info
                                                @elseif($ticket->ticket_status->status ?? '' == 'In Progress')
                                                    label-warning
                                                @elseif($ticket->ticket_status->status ?? '' == 'Resolved')
                                                    label-success
                                                @else
                                                    label-default
                                                @endif
                                            ">
                                                {{ $ticket->ticket_status->status ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ticket->asset)
                                                <small>{{ $ticket->asset->asset_tag }}</small>
                                            @else
                                                <em>Tidak ada</em>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $ticket->created_at->format('d M Y H:i') }}</small>
                                            @if($ticket->is_overdue)
                                                <br><span class="label label-danger">Terlambat</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tickets.user-show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="text-center">
                        {{ $tickets->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center" style="padding: 50px;">
                        <h4>Belum ada tiket</h4>
                        <p>Anda belum memiliki tiket. <a href="{{ route('tickets.user-create') }}">Buat tiket pertama Anda</a>.</p>
                    </div>
                @endif
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