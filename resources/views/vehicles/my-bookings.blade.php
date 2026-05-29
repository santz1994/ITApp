@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Booking Saya',
    'subtitle' => 'Daftar booking kendaraan yang saya ajukan',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Kendaraan', 'url' => route('vehicles.index')],
        ['label' => 'Booking Saya']
    ],
    'actions' => '
        <a href="'.route('vehicles.booking.create').'" class="btn btn-success">
            <i class="fa fa-plus"></i> Booking Baru
        </a>
    '
])

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kendaraan</th>
                        <th>Tujuan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td>{{ $booking->vehicle->name ?? '-' }}<br><small class="text-muted">{{ $booking->vehicle->plate_number ?? '' }}</small></td>
                            <td>{{ $booking->destination }}</td>
                            <td>
                                {{ $booking->start_datetime->format('d M Y H:i') }}
                                <br><small class="text-muted">s/d {{ $booking->end_datetime->format('H:i') }}</small>
                            </td>
                            <td><span class="label label-{{ $booking->status_badge }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                            <td><a href="{{ route('vehicles.booking.show', $booking->id) }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">Belum ada booking kendaraan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection