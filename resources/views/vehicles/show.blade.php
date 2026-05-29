@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => $vehicle->name,
    'subtitle' => $vehicle->full_name,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Kendaraan', 'url' => route('vehicles.index')],
        ['label' => $vehicle->name]
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('vehicles.edit', $vehicle->id).'" class="btn btn-warning">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="'.route('vehicles.booking.create').'?vehicle_id='.$vehicle->id.'" class="btn btn-success">
                <i class="fa fa-car"></i> Booking
            </a>
        </div>
    '
])

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Vehicle Info --}}
        <div class="col-md-4">
            <div class="box box-widget widget-user-2">
                <div class="widget-user-header bg-{{ $vehicle->status === 'available' ? 'green' : ($vehicle->status === 'in_use' ? 'blue' : ($vehicle->status === 'maintenance' ? 'yellow' : 'gray')) }}">
                    <h3 class="widget-user-username">{{ $vehicle->name }}</h3>
                    <h5 class="widget-user-desc">{{ $vehicle->brand }} {{ $vehicle->model }}</h5>
                </div>
                <div class="box-footer no-padding">
                    <ul class="nav nav-stacked">
                        <li><a href="#">Plat Nomor <span class="pull-right badge bg-blue">{{ $vehicle->plate_number }}</span></a></li>
                        <li><a href="#">Tahun <span class="pull-right badge bg-aqua">{{ $vehicle->year ?? '-' }}</span></a></li>
                        <li><a href="#">Warna <span class="pull-right badge bg-gray">{{ $vehicle->color ?? '-' }}</span></a></li>
                        <li><a href="#">Kapasitas <span class="pull-right badge bg-purple">{{ $vehicle->capacity }} orang</span></a></li>
                        <li><a href="#">Bahan Bakar <span class="pull-right badge bg-maroon">{{ $vehicle->fuel_type ?? '-' }}</span></a></li>
                        <li><a href="#">Kilometer <span class="pull-right badge bg-teal">{{ number_format($vehicle->current_mileage, 0, ',', '.') }} km</span></a></li>
                        <li><a href="#">STNK Exp <span class="pull-right badge {{ $vehicle->isStnkExpired() ? 'bg-red' : 'bg-green' }}">{{ $vehicle->stnk_expiry ?? '-' }}</span></a></li>
                        <li><a href="#">Asuransi Exp <span class="pull-right badge {{ $vehicle->isInsuranceExpired() ? 'bg-red' : 'bg-green' }}">{{ $vehicle->insurance_expiry ?? '-' }}</span></a></li>
                    </ul>
                </div>
            </div>

            {{-- Maintenance Logs --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-wrench"></i> Maintenance Logs</h3>
                </div>
                <div class="box-body">
                    @forelse($maintenanceLogs as $log)
                        <div class="post">
                            <p><strong>{{ $log->maintenance_type }}</strong> - {{ $log->maintenance_date->format('d M Y') }}</p>
                            <p class="text-muted">{{ $log->description }}</p>
                            <p><small>Biaya: Rp {{ number_format($log->cost ?? 0, 0, ',', '.') }} | Oleh: {{ $log->recorder->name ?? '-' }}</small></p>
                        </div>
                        <hr>
                    @empty
                        <p class="text-muted text-center">Belum ada log maintenance.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Booking History --}}
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Booking</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pemohon</th>
                                <th>Tujuan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::parse($booking->start_datetime)->format('d M Y H:i') }}
                                        <br><small class="text-muted">s/d {{ \Carbon\Carbon::parse($booking->end_datetime)->format('H:i') }}</small>
                                    </td>
                                    <td>{{ $booking->requester->name ?? '-' }}</td>
                                    <td>{{ $booking->destination }}</td>
                                    <td>
                                        <span class="label label-{{ $booking->status_badge }}">{{ ucfirst($booking->status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('vehicles.booking.show', $booking->id) }}" class="btn btn-xs btn-default">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada booking untuk kendaraan ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add Maintenance Log --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-plus"></i> Tambah Log Maintenance</h3>
                </div>
                <form action="{{ route('vehicles.maintenance.add', $vehicle->id) }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Maintenance <span class="text-red">*</span></label>
                                    <select name="maintenance_type" class="form-control" required>
                                        <option value="">Pilih</option>
                                        <option value="Servis Berkala">Servis Berkala</option>
                                        <option value="Perbaikan">Perbaikan</option>
                                        <option value="Penggantian Sparepart">Penggantian Sparepart</option>
                                        <option value="Pencucian">Pencucian</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal <span class="text-red">*</span></label>
                                    <input type="date" name="maintenance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Biaya (Rp)</label>
                                    <input type="number" name="cost" class="form-control" min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Deskripsi <span class="text-red">*</span></label>
                                    <textarea name="description" class="form-control" rows="3" required placeholder="Detail maintenance..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Kilometer Saat Ini</label>
                                    <input type="number" name="mileage_at_service" class="form-control" value="{{ $vehicle->current_mileage }}" min="0" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label>Vendor/Bengkel</label>
                                    <input type="text" name="service_provider" class="form-control" placeholder="e.g., Auto2000">
                                </div>
                            </div>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="set_maintenance_status" value="1"> Set status kendaraan ke "Maintenance"
                            </label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Simpan Log</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection