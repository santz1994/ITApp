@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Manajemen Inventaris',
    'subtitle' => 'ATK & Sparepart Management',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Inventaris']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('inventory.create').'" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Barang
            </a>
            <a href="'.route('inventory.request.create').'" class="btn btn-warning">
                <i class="fa fa-file-text"></i> Buat Request
            </a>
            <a href="'.route('inventory.requests').'" class="btn btn-primary">
                <i class="fa fa-list"></i> Daftar Request
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

    {{-- Dashboard Stats --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Item</span>
                    <span class="info-box-number">{{ $dashboardStats['total_items'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Kategori</span>
                    <span class="info-box-number">{{ $dashboardStats['total_categories'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Stok Rendah</span>
                    <span class="info-box-number">{{ $dashboardStats['low_stock_items'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Request Pending</span>
                    <span class="info-box-number">{{ $dashboardStats['pending_requests'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('inventory.index') }}" class="form-inline">
                <div class="form-group" style="margin-right: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari barang/SKU..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <select name="category_id" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->items_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="low_stock" value="1" {{ ($filters['low_stock'] ?? '') ? 'checked' : '' }}> Stok Rendah Saja
                    </label>
                </div>
                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i> Cari</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
            </form>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="box box-primary">
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Harga Satuan</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><code>{{ $item->sku }}</code></td>
                            <td>
                                <a href="{{ route('inventory.show', $item->id) }}">{{ $item->name }}</a>
                            </td>
                            <td><span class="label label-default">{{ $item->category->name ?? '-' }}</span></td>
                            <td>
                                <strong>{{ $item->current_stock }}</strong>
                                @if($item->isLowStock())
                                    <span class="text-red"><i class="fa fa-exclamation-triangle"></i></span>
                                @endif
                            </td>
                            <td>{{ $item->unit }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td>{{ $item->location ?? '-' }}</td>
                            <td>
                                @if($item->isOutOfStock())
                                    <span class="label label-danger">Habis</span>
                                @elseif($item->isLowStock())
                                    <span class="label label-warning">Stok Rendah</span>
                                @else
                                    <span class="label label-success">Normal</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted" style="padding:30px;">
                                <i class="fa fa-cubes fa-2x"></i><br>Belum ada item inventaris.
                                <br><a href="{{ route('inventory.create') }}" class="btn btn-success btn-sm" style="margin-top:10px;">Tambah Barang</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection