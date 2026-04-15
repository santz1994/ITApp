@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $pageTitle }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('tickets.user-create') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Laporkan Masalah
                    </a>
                </div>
            </div>
            
            <!-- Filter Form -->
            <div class="box-body">
                <form method="GET" action="{{ route('assets.user-index') }}" class="form-inline" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="search">Cari:</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                               placeholder="Tag aset, serial number, atau model...">
                    </div>
                    
                    <div class="form-group" style="margin-left: 15px;">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    
                    <a href="{{ route('assets.user-index') }}" class="btn btn-default" style="margin-left: 5px;">
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
                @if($assets->count() > 0)
                    <div class="row">
                        @foreach($assets as $asset)
                            <div class="col-md-6 col-lg-4">
                                <div class="box box-widget widget-user-2">
                                    <div class="widget-user-header 
                                        @if($asset->status->name == 'Active')
                                            bg-green
                                        @elseif($asset->status->name == 'In Repair' || $asset->status->name == 'Maintenance')
                                            bg-yellow
                                        @elseif($asset->status->name == 'Retired' || $asset->status->name == 'Disposed')
                                            bg-red
                                        @else
                                            bg-blue
                                        @endif
                                    ">
                                        <div class="widget-user-image">
                                            <i class="fa 
                                                @if(str_contains(strtolower($asset->model->model_name ?? ''), 'laptop'))
                                                    fa-laptop
                                                @elseif(str_contains(strtolower($asset->model->model_name ?? ''), 'desktop'))
                                                    fa-desktop
                                                @elseif(str_contains(strtolower($asset->model->model_name ?? ''), 'printer'))
                                                    fa-print
                                                @elseif(str_contains(strtolower($asset->model->model_name ?? ''), 'server'))
                                                    fa-server
                                                @else
                                                    fa-cube
                                                @endif
                                                fa-3x text-white
                                            "></i>
                                        </div>
                                        <h3 class="widget-user-username">{{ $asset->asset_tag }}</h3>
                                        <h5 class="widget-user-desc">{{ $asset->model->model_name ?? 'Model tidak diketahui' }}</h5>
                                    </div>
                                    
                                    <div class="box-footer no-padding">
                                        <ul class="nav nav-stacked">
                                            <li>
                                                <span class="text-muted">
                                                    <i class="fa fa-barcode margin-r-5"></i> Serial: 
                                                    {{ $asset->serial_number ?: 'N/A' }}
                                                </span>
                                            </li>
                                            <li>
                                                <span class="text-muted">
                                                    <i class="fa fa-calendar margin-r-5"></i> Dibeli: 
                                                    {{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : 'N/A' }}
                                                </span>
                                            </li>
                                            <li>
                                                <span class="text-muted">
                                                    <i class="fa fa-shield margin-r-5"></i> Garansi: 
                                                    @if($asset->warranty_end_date)
                                                        @if($asset->warranty_end_date->isFuture())
                                                            <span class="text-green">Berlaku hingga {{ $asset->warranty_end_date->format('d M Y') }}</span>
                                                        @else
                                                            <span class="text-red">Expired {{ $asset->warranty_end_date->format('d M Y') }}</span>
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </li>
                                            @if($asset->location)
                                                <li>
                                                    <span class="text-muted">
                                                        <i class="fa fa-map-marker margin-r-5"></i> Lokasi: 
                                                        {{ $asset->location->location_name }}
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                        
                                        <div class="box-footer">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <a href="{{ route('assets.user-show', $asset->id) }}" class="btn btn-default btn-block">
                                                        <i class="fa fa-eye"></i> Detail
                                                    </a>
                                                </div>
                                                <div class="col-sm-6">
                                                    <a href="{{ route('tickets.user-create', ['asset_id' => $asset->id]) }}" class="btn btn-warning btn-block">
                                                        <i class="fa fa-exclamation-triangle"></i> Laporkan
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="text-center">
                        {{ $assets->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center" style="padding: 50px;">
                        <i class="fa fa-cube fa-5x text-muted"></i>
                        <h4>Tidak ada aset yang ditugaskan</h4>
                        <p>Saat ini tidak ada aset yang ditugaskan kepada Anda. Hubungi administrator untuk informasi lebih lanjut.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Info Box -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Penting</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5><i class="fa fa-exclamation-triangle text-warning"></i> Melaporkan Masalah</h5>
                        <p>Jika mengalami masalah dengan aset Anda, klik tombol "Laporkan" atau buat tiket baru melalui menu tiket.</p>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fa fa-shield text-success"></i> Menjaga Aset</h5>
                        <p>Jaga aset dengan baik. Laporkan kerusakan atau kehilangan segera kepada IT Support.</p>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fa fa-phone text-info"></i> Kontak Darurat</h5>
                        <p>Untuk masalah urgent, hubungi helpdesk di Ext. 123 atau WhatsApp 08XX-XXXX-XXXX.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection