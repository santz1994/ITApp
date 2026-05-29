@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Detail Booking Kendaraan',
    'subtitle' => 'ID: #' . $booking->id,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Kendaraan', 'url' => route('vehicles.index')],
        ['label' => 'Booking #' . $booking->id]
    ]
])

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin-bottom:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Booking Info --}}
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Booking</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-{{ $booking->status_badge }}" style="font-size: 14px;">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Kendaraan</th>
                                    <td>{{ $booking->vehicle->full_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tujuan</th>
                                    <td>{{ $booking->destination }}</td>
                                </tr>
                                <tr>
                                    <th>Keperluan</th>
                                    <td>{{ $booking->purpose }}</td>
                                </tr>
                                <tr>
                                    <th>Penumpang</th>
                                    <td>{{ $booking->passengers }} orang</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Waktu Mulai</th>
                                    <td>{{ $booking->start_datetime->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Waktu Selesai</th>
                                    <td>{{ $booking->end_datetime->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Estimasi Jarak</th>
                                    <td>{{ $booking->estimated_distance ? number_format($booking->estimated_distance, 1) . ' km' : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Catatan</th>
                                    <td>{{ $booking->notes ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Actual Trip Data --}}
                    @if($booking->status === 'completed' || $booking->status === 'in_progress')
                        <hr>
                        <h4><i class="fa fa-road"></i> Data Aktual</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua"><i class="fa fa-road"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Jarak Aktual</span>
                                        <span class="info-box-number">{{ $booking->actual_distance ? number_format($booking->actual_distance, 1) . ' km' : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Biaya BBM</span>
                                        <span class="info-box-number">{{ $booking->actual_fuel_cost ? 'Rp ' . number_format($booking->actual_fuel_cost, 0, ',', '.') : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Pemohon</span>
                                        <span class="info-box-number">{{ $booking->requester->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Rejection Reason --}}
                    @if($booking->status === 'rejected' && $booking->rejection_reason)
                        <div class="alert alert-danger">
                            <h4><i class="fa fa-ban"></i> Alasan Penolakan</h4>
                            <p>{{ $booking->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="col-md-4">
            {{-- Status Timeline --}}
            <div class="box box-widget">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Timeline</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline">
                        <li>
                            <i class="fa fa-paper-plane bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $booking->created_at->format('d M Y H:i') }}</span>
                                <h3 class="timeline-header">Booking Diajukan</h3>
                                <div class="timeline-body">Oleh {{ $booking->requester->name ?? '-' }}</div>
                            </div>
                        </li>
                        @if($booking->approved_at)
                            <li>
                                <i class="fa fa-{{ $booking->status === 'rejected' ? 'times bg-red' : 'check bg-green' }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> {{ $booking->approved_at->format('d M Y H:i') }}</span>
                                    <h3 class="timeline-header">{{ $booking->status === 'rejected' ? 'Ditolak' : 'Disetujui' }}</h3>
                                    <div class="timeline-body">Oleh {{ $booking->approver->name ?? '-' }}</div>
                                </div>
                            </li>
                        @endif
                        @if($booking->status === 'in_progress')
                            <li>
                                <i class="fa fa-car bg-aqua"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Perjalanan Dimulai</h3>
                                </div>
                            </li>
                        @endif
                        @if($booking->status === 'completed')
                            <li>
                                <i class="fa fa-flag-checkered bg-green"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Perjalanan Selesai</h3>
                                </div>
                            </li>
                        @endif
                        @if($booking->status === 'cancelled')
                            <li>
                                <i class="fa fa-ban bg-gray"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Dibatalkan</h3>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cogs"></i> Aksi</h3>
                </div>
                <div class="box-body">
                    {{-- Approve/Reject (Manager/HRD-GA) --}}
                    @if($booking->status === 'pending')
                        <form action="{{ route('vehicles.booking.approve', $booking->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-check"></i> Setujui
                            </button>
                        </form>
                        <hr>
                        <form action="{{ route('vehicles.booking.reject', $booking->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Alasan penolakan (opsional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fa fa-times"></i> Tolak
                            </button>
                        </form>
                    @endif

                    {{-- Start Trip --}}
                    @if($booking->status === 'approved')
                        <form action="{{ route('vehicles.booking.start', $booking->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fa fa-car"></i> Mulai Perjalanan
                            </button>
                        </form>
                    @endif

                    {{-- Complete Trip --}}
                    @if($booking->status === 'in_progress')
                        <form action="{{ route('vehicles.booking.complete', $booking->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Jarak Aktual (km)</label>
                                <input type="number" name="actual_distance" class="form-control" min="0" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Biaya BBM (Rp)</label>
                                <input type="number" name="actual_fuel_cost" class="form-control" min="0" step="1000">
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-flag-checkered"></i> Selesaikan Perjalanan
                            </button>
                        </form>
                    @endif

                    {{-- Cancel --}}
                    @if(in_array($booking->status, ['pending', 'approved']))
                        <hr>
                        <form action="{{ route('vehicles.booking.cancel', $booking->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-default btn-block" onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                                <i class="fa fa-ban"></i> Batalkan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection