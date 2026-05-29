@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Tambah Kendaraan',
    'subtitle' => 'Daftarkan kendaraan baru',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Kendaraan', 'url' => route('vehicles.index')],
        ['label' => 'Tambah']
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

    <form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Informasi Kendaraan</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Kendaraan <span class="text-red">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., Avanza Putih Operasional" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Plat <span class="text-red">*</span></label>
                            <input type="text" name="plate_number" class="form-control" value="{{ old('plate_number') }}" placeholder="e.g., B 1234 ABC" required>
                        </div>
                        <div class="form-group">
                            <label>Merek <span class="text-red">*</span></label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="e.g., Toyota" required>
                        </div>
                        <div class="form-group">
                            <label>Model <span class="text-red">*</span></label>
                            <input type="text" name="model" class="form-control" value="{{ old('model') }}" placeholder="e.g., Avanza" required>
                        </div>
                        <div class="form-group">
                            <label>Tahun</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year') }}" min="1990" max="{{ date('Y') + 1 }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Warna</label>
                            <input type="text" name="color" class="form-control" value="{{ old('color') }}" placeholder="e.g., Putih">
                        </div>
                        <div class="form-group">
                            <label>Kapasitas Penumpang <span class="text-red">*</span></label>
                            <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 4) }}" min="1" max="50" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis Bahan Bakar</label>
                            <select name="fuel_type" class="form-control">
                                <option value="">Pilih</option>
                                <option value="Bensin" {{ old('fuel_type') === 'Bensin' ? 'selected' : '' }}>Bensin</option>
                                <option value="Diesel" {{ old('fuel_type') === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                <option value="Hybrid" {{ old('fuel_type') === 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                <option value="Listrik" {{ old('fuel_type') === 'Listrik' ? 'selected' : '' }}>Listrik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Masa Berlaku STNK</label>
                            <input type="date" name="stnk_expiry" class="form-control" value="{{ old('stnk_expiry') }}">
                        </div>
                        <div class="form-group">
                            <label>Masa Berlaku Asuransi</label>
                            <input type="date" name="insurance_expiry" class="form-control" value="{{ old('insurance_expiry') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Foto Kendaraan</label>
                            <input type="file" name="photo" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <a href="{{ route('vehicles.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Simpan Kendaraan</button>
            </div>
        </div>
    </form>
</div>
@endsection