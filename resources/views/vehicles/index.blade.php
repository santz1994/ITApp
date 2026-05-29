@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Manajemen Kendaraan',
    'subtitle' => 'Daftar kendaraan operasional perusahaan',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Kendaraan']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('vehicles.create').'" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Kendaraan
            </a>
            <a href="'.route('vehicles.my-bookings').'" class="btn btn-primary">
                <i class="fa fa-calendar"></i> Booking Saya
            </a>
            <a href="'.route('vehicles.booking.create').'" class="btn btn-warning">
                <i class="fa fa-car"></i> Booking Kendaraan
            </a>
        </div>
    '
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('vehicles.index') }}" class="form-inline">
                <div class="form-group" style="margin-right: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari kendaraan..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="available" {{ ($filters['status'] ?? '') === 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="in_use" {{ ($filters['status'] ?? '') === 'in_use' ? 'selected' : '' }}>Sedang Digunakan</option>
                        <option value="maintenance" {{ ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="retired" {{ ($filters['status'] ?? '') === 'retired' ? 'selected' : '' }}>Pensiun</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i> Cari</button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
            </form>
        </div>
    </div>

    {{-- Vehicle Cards --}}
    <div class="row">
        @forelse($vehicles as $vehicle)
            <div class="col-md-4 col-sm-6">
                <div class="box box-widget">
                    <div class="box-header with-border">
                        <div class="user-block">
                            <span class="bg-{{ $vehicle->status === 'available' ? 'green' : ($vehicle->status === 'in_use' ? 'blue' : ($vehicle->status === 'maintenance' ? 'yellow' : 'gray')) }} label" style="font-size: 11px;">
                                {{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}
                            </span>
                            <span class="username" style="margin-left: 8px;">{{ $vehicle->name }}</span>
                            <span class="description">{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-6">
                                <p><strong>Plat:</strong> {{ $vehicle->plate_number }}</p>
                                <p><strong>Kapasitas:</strong> {{ $vehicle->capacity }} orang</p>
                            </div>
                            <div class="col-xs-6">
                                <p><strong>Tahun:</strong> {{ $vehicle->year ?? '-' }}</p>
                                <p><strong>Warna:</strong> {{ $vehicle->color ?? '-' }}</p>
                            </div>
                        </div>
                        <p><strong>Kilometer:</strong> {{ number_format($vehicle->current_mileage, 0, ',', '.') }} km</p>

                        @if($vehicle->isStnkExpired())
                            <div class="alert alert-danger" style="margin-top: 10px; padding: 5px 10px;">
                                <i class="fa fa-exclamation-triangle"></i> STNK expired!
                            </div>
                        @endif
                        @if($vehicle->isInsuranceExpired())
                            <div class="alert alert-danger" style="margin-top: 5px; padding: 5px 10px;">
                                <i class="fa fa-exclamation-triangle"></i> Asuransi expired!
                            </div>
                        @endif
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-default btn-sm">
                            <i class="fa fa-eye"></i> Detail
                        </a>
                        @if($vehicle->isAvailable())
                            <a href="{{ route('vehicles.booking.create') }}?vehicle_id={{ $vehicle->id }}" class="btn btn-success btn-sm pull-right">
                                <i class="fa fa-car"></i> Booking
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="box box-widget">
                    <div class="box-body text-center" style="padding: 40px;">
                        <i class="fa fa-car fa-3x text-muted"></i>
                        <p class="text-muted" style="margin-top: 15px;">Belum ada kendaraan terdaftar.</p>
                        <a href="{{ route('vehicles.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> Tambah Kendaraan
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection