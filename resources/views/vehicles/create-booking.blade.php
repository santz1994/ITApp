@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Booking Kendaraan',
    'subtitle' => 'Ajukan pemesanan kendaraan operasional',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Kendaraan', 'url' => route('vehicles.index')],
        ['label' => 'Booking Baru']
    ]
])

<div class="container-fluid">
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

    <form action="{{ route('vehicles.booking.store') }}" method="POST">
        @csrf
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Informasi Booking</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kendaraan <span class="text-red">*</span></label>
                            <select name="vehicle_id" class="form-control" required id="vehicle-select">
                                <option value="">Pilih Kendaraan</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" 
                                        {{ (request('vehicle_id') == $vehicle->id || old('vehicle_id') == $vehicle->id) ? 'selected' : '' }}
                                        data-capacity="{{ $vehicle->capacity }}">
                                        {{ $vehicle->full_name }} - {{ $vehicle->capacity }} orang
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tujuan <span class="text-red">*</span></label>
                            <input type="text" name="destination" class="form-control" value="{{ old('destination') }}" placeholder="e.g., Kantor Pusat Jakarta" required>
                        </div>
                        <div class="form-group">
                            <label>Keperluan <span class="text-red">*</span></label>
                            <textarea name="purpose" class="form-control" rows="3" required placeholder="Jelaskan keperluan penggunaan kendaraan...">{{ old('purpose') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Waktu Mulai <span class="text-red">*</span></label>
                            <input type="datetime-local" name="start_datetime" class="form-control" value="{{ old('start_datetime') }}" required id="start-datetime">
                        </div>
                        <div class="form-group">
                            <label>Waktu Selesai <span class="text-red">*</span></label>
                            <input type="datetime-local" name="end_datetime" class="form-control" value="{{ old('end_datetime') }}" required id="end-datetime">
                        </div>
                        <div class="form-group">
                            <label>Jumlah Penumpang <span class="text-red">*</span></label>
                            <input type="number" name="passengers" class="form-control" value="{{ old('passengers', 1) }}" min="1" max="50" required>
                        </div>
                        <div class="form-group">
                            <label>Estimasi Jarak (km)</label>
                            <input type="number" name="estimated_distance" class="form-control" value="{{ old('estimated_distance') }}" min="0" step="0.1" placeholder="Opsional">
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Availability Check --}}
                <div id="availability-result" class="alert" style="display: none;"></div>
                <button type="button" class="btn btn-info btn-sm" id="check-availability">
                    <i class="fa fa-check-circle"></i> Cek Ketersediaan
                </button>
            </div>
            <div class="box-footer">
                <a href="{{ route('vehicles.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-paper-plane"></i> Ajukan Booking</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#check-availability').click(function() {
        var vehicleId = $('#vehicle-select').val();
        var startDatetime = $('#start-datetime').val();
        var endDatetime = $('#end-datetime').val();

        if (!vehicleId || !startDatetime || !endDatetime) {
            alert('Pilih kendaraan, waktu mulai, dan waktu selesai terlebih dahulu.');
            return;
        }

        $.ajax({
            url: '{{ route("vehicles.check-availability") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                vehicle_id: vehicleId,
                start_datetime: startDatetime,
                end_datetime: endDatetime
            },
            success: function(response) {
                var resultDiv = $('#availability-result');
                if (response.available) {
                    resultDiv.removeClass('alert-danger').addClass('alert-success')
                        .html('<i class="fa fa-check"></i> Kendaraan tersedia pada waktu tersebut.')
                        .show();
                } else {
                    resultDiv.removeClass('alert-success').addClass('alert-danger')
                        .html('<i class="fa fa-times"></i> Kendaraan tidak tersedia. Terdapat bentrok jadwal.')
                        .show();
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat mengecek ketersediaan.');
            }
        });
    });
});
</script>
@endsection