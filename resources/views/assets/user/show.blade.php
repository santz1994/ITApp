@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-8">
        <!-- Asset Information -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $pageTitle }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('assets.user-index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Daftar Aset
                    </a>
                    <a href="{{ route('tickets.user-create', ['asset_id' => $asset->id]) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-exclamation-triangle"></i> Laporkan Masalah
                    </a>
                </div>
            </div>
            
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-info-circle text-primary"></i> Informasi Dasar</h4>
                        
                        <table class="table table-striped">
                            <tr>
                                <th>Tag Aset:</th>
                                <td><strong>{{ $asset->asset_tag }}</strong></td>
                            </tr>
                            <tr>
                                <th>Model:</th>
                                <td>{{ $asset->model->model_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Serial Number:</th>
                                <td>{{ $asset->serial_number ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="label 
                                        @if($asset->status->name == 'Active')
                                            label-success
                                        @elseif($asset->status->name == 'In Repair' || $asset->status->name == 'Maintenance')
                                            label-warning
                                        @elseif($asset->status->name == 'Retired' || $asset->status->name == 'Disposed')
                                            label-danger
                                        @else
                                            label-info
                                        @endif
                                    ">
                                        {{ $asset->status->name ?? 'Unknown' }}
                                    </span>
                                </td>
                            </tr>
                            @if($asset->location)
                                <tr>
                                    <th>Lokasi:</th>
                                    <td>{{ $asset->location->location_name }}</td>
                                </tr>
                            @endif
                            @if($asset->division)
                                <tr>
                                    <th>Divisi:</th>
                                    <td>{{ $asset->division->division_name }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h4><i class="fa fa-calendar text-info"></i> Informasi Pembelian & Garansi</h4>
                        
                        <table class="table table-striped">
                            @if($asset->purchase_date)
                                <tr>
                                    <th>Tanggal Pembelian:</th>
                                    <td>{{ $asset->purchase_date->format('d F Y') }}</td>
                                </tr>
                            @endif
                            
                            @if($asset->supplier)
                                <tr>
                                    <th>Supplier:</th>
                                    <td>{{ $asset->supplier->supplier_name }}</td>
                                </tr>
                            @endif
                            
                            @if($asset->warranty_months)
                                <tr>
                                    <th>Masa Garansi:</th>
                                    <td>{{ $asset->warranty_months }} bulan</td>
                                </tr>
                            @endif
                            
                            @if($asset->warranty_end_date)
                                <tr>
                                    <th>Berakhir Garansi:</th>
                                    <td>
                                        {{ $asset->warranty_end_date->format('d F Y') }}
                                        @if($asset->warranty_end_date->isFuture())
                                            <span class="label label-success">Masih Berlaku</span>
                                        @else
                                            <span class="label label-danger">Expired</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            
                            @if($asset->warranty_type)
                                <tr>
                                    <th>Jenis Garansi:</th>
                                    <td>{{ $asset->warranty_type->type_name }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                @if($asset->ip_address || $asset->mac_address)
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fa fa-network-wired text-success"></i> Informasi Jaringan</h4>
                            <table class="table table-striped">
                                @if($asset->ip_address)
                                    <tr>
                                        <th>IP Address:</th>
                                        <td><code>{{ $asset->ip_address }}</code></td>
                                    </tr>
                                @endif
                                @if($asset->mac_address)
                                    <tr>
                                        <th>MAC Address:</th>
                                        <td><code>{{ $asset->mac_address }}</code></td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif
                
                @if($asset->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fa fa-sticky-note text-warning"></i> Catatan</h4>
                            <div class="well well-sm">
                                {!! nl2br(e($asset->notes)) !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Ticket History -->
        @if($ticketHistory->count() > 0)
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Tiket Aset Ini</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kode Tiket</th>
                                    <th>Judul</th>
                                    <th>Status</th>
                                    <th>Prioritas</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ticketHistory as $ticket)
                                    <tr>
                                        <td><strong>{{ $ticket->ticket_code }}</strong></td>
                                        <td>{{ $ticket->subject }}</td>
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
                                        <td>{{ $ticket->created_at->format('d M Y') }}</td>
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
                    
                    @if($ticketHistory->count() >= 10)
                        <div class="text-center">
                            <em>Menampilkan 10 tiket terbaru. <a href="{{ route('tickets.user-index', ['asset_id' => $asset->id]) }}">Lihat semua tiket untuk aset ini</a></em>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="box box-default">
                <div class="box-body text-center">
                    <i class="fa fa-history fa-3x text-muted"></i>
                    <h4>Belum Ada Riwayat Tiket</h4>
                    <p>Aset ini belum pernah dilaporkan bermasalah.</p>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bolt"></i> Aksi Cepat</h3>
            </div>
            <div class="box-body">
                <div class="btn-group-vertical btn-block">
                    <a href="{{ route('tickets.user-create', ['asset_id' => $asset->id]) }}" class="btn btn-warning">
                        <i class="fa fa-exclamation-triangle"></i> Laporkan Masalah
                    </a>
                    @if($asset->qr_code)
                        <button class="btn btn-info" onclick="showQRCode()">
                            <i class="fa fa-qrcode"></i> Lihat QR Code
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Asset Status -->
        <div class="box 
            @if($asset->status->name == 'Active')
                box-success
            @elseif($asset->status->name == 'In Repair' || $asset->status->name == 'Maintenance')
                box-warning
            @elseif($asset->status->name == 'Retired' || $asset->status->name == 'Disposed')
                box-danger
            @else
                box-info
            @endif
        ">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Status Aset</h3>
            </div>
            <div class="box-body">
                @if($asset->status->name == 'Active')
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> Aset dalam kondisi baik dan siap digunakan.
                    </div>
                @elseif($asset->status->name == 'In Repair')
                    <div class="alert alert-warning">
                        <i class="fa fa-wrench"></i> Aset sedang dalam perbaikan. 
                        Hubungi IT Support untuk informasi lebih lanjut.
                    </div>
                @elseif($asset->status->name == 'Maintenance')
                    <div class="alert alert-warning">
                        <i class="fa fa-cog"></i> Aset sedang dalam maintenance terjadwal.
                    </div>
                @elseif($asset->status->name == 'Retired')
                    <div class="alert alert-danger">
                        <i class="fa fa-times-circle"></i> Aset telah dinyatakan tidak aktif.
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Status: {{ $asset->status->name }}
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Warranty Status -->
        @if($asset->warranty_end_date)
            <div class="box 
                @if($asset->warranty_end_date->isFuture())
                    box-success
                @else
                    box-danger
                @endif
            ">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shield"></i> Status Garansi</h3>
                </div>
                <div class="box-body">
                    @if($asset->warranty_end_date->isFuture())
                        <div class="alert alert-success">
                            <i class="fa fa-shield"></i> Garansi masih berlaku hingga 
                            <strong>{{ $asset->warranty_end_date->format('d F Y') }}</strong>
                            <br><small>{{ $asset->warranty_end_date->diffForHumans() }}</small>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fa fa-shield"></i> Garansi expired sejak 
                            <strong>{{ $asset->warranty_end_date->format('d F Y') }}</strong>
                            <br><small>{{ $asset->warranty_end_date->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        <!-- Contact Support -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-phone"></i> Butuh Bantuan?</h3>
            </div>
            <div class="box-body">
                <p>Jika mengalami masalah dengan aset ini:</p>
                <ul class="list-unstyled">
                    <li><i class="fa fa-ticket"></i> <a href="{{ route('tickets.user-create', ['asset_id' => $asset->id]) }}">Buat tiket baru</a></li>
                    <li><i class="fa fa-phone"></i> <strong>Helpdesk:</strong> Ext. 123</li>
                    <li><i class="fa fa-envelope"></i> <strong>Email:</strong> helpdesk@company.com</li>
                </ul>
                
                <small class="text-muted">Saat menghubungi, sebutkan tag aset: <strong>{{ $asset->asset_tag }}</strong></small>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
@if($asset->qr_code)
    <div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title">QR Code - {{ $asset->asset_tag }}</h4>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode"></div>
                    <p class="text-muted">{{ $asset->qr_code }}</p>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@if($asset->qr_code)
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        function showQRCode() {
            $('#qrCodeModal').modal('show');
            
            // Generate QR Code
            const qrCodeDiv = document.getElementById('qrcode');
            qrCodeDiv.innerHTML = ''; // Clear previous QR code
            
            QRCode.toCanvas(qrCodeDiv, '{{ $asset->qr_code }}', {
                width: 200,
                height: 200,
            }, function (error) {
                if (error) console.error(error);
            });
        }
    </script>
@endif
@endsection