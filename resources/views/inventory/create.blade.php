@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Tambah Item Inventaris',
    'subtitle' => 'Daftarkan barang ATK/Sparepart baru',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Inventaris', 'url' => route('inventory.index')],
        ['label' => 'Tambah']
    ]
])

<div class="container-fluid">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin-bottom:0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Informasi Barang</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategori <span class="text-red">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang <span class="text-red">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g., Kertas A4 80gr">
                        </div>
                        <div class="form-group">
                            <label>SKU (Kode Barang) <span class="text-red">*</span></label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" required placeholder="e.g., ATK-001">
                        </div>
                        <div class="form-group">
                            <label>Satuan <span class="text-red">*</span></label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', 'pcs') }}" required placeholder="pcs, box, rim, dll">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi barang...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Stok Awal</label>
                            <input type="number" name="current_stock" class="form-control" value="{{ old('current_stock', 0) }}" min="0">
                        </div>
                        <div class="form-group">
                            <label>Batas Minimum Stok</label>
                            <input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', 0) }}" min="0" placeholder="Alert jika stok di bawah ini">
                        </div>
                        <div class="form-group">
                            <label>Harga Satuan (Rp)</label>
                            <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price', 0) }}" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>Lokasi Penyimpanan</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g., Gudang A, Rak B3">
                        </div>
                        <div class="form-group">
                            <label>Foto</label>
                            <input type="file" name="photo" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <a href="{{ route('inventory.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection