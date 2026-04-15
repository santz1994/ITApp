@extends('layouts.app')

@section('main-content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $pageTitle }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('tickets.user-index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Daftar Tiket
                    </a>
                </div>
            </div>
            
            <div class="box-body">
                <form method="POST" action="{{ route('tickets.user-store') }}">
                    @csrf
                    
                    <!-- Kategori Masalah -->
                    <div class="form-group">
                        <label for="ticket_type_id">
                            <i class="fa fa-tag"></i> Kategori Masalah <span class="text-red">*</span>
                            @if ($errors->has('ticket_type_id'))
                                <small class="text-red">{{ $errors->first('ticket_type_id') }}</small>
                            @endif
                        </label>
                        <select class="form-control" name="ticket_type_id" required>
                            <option value="">-- Pilih Kategori Masalah --</option>
                            @foreach($ticketTypes as $type)
                                <option value="{{ $type->id }}" {{ old('ticket_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih kategori yang paling sesuai dengan masalah Anda</small>
                    </div>

                    <!-- Tingkat Prioritas -->
                    <div class="form-group">
                        <label for="ticket_priority_id">
                            <i class="fa fa-exclamation-triangle"></i> Tingkat Dampak <span class="text-red">*</span>
                            @if ($errors->has('ticket_priority_id'))
                                <small class="text-red">{{ $errors->first('ticket_priority_id') }}</small>
                            @endif
                        </label>
                        <select class="form-control" name="ticket_priority_id" required>
                            <option value="">-- Pilih Tingkat Dampak --</option>
                            @foreach($ticketPriorities as $priority)
                                <option value="{{ $priority->id }}" {{ old('ticket_priority_id') == $priority->id ? 'selected' : '' }}>
                                    {{ $priority->priority }}
                                    @if($priority->priority == 'Urgent')
                                        - Sistem tidak bisa digunakan sama sekali
                                    @elseif($priority->priority == 'High')
                                        - Mengganggu pekerjaan secara signifikan
                                    @elseif($priority->priority == 'Normal')
                                        - Mengganggu tapi masih bisa bekerja
                                    @elseif($priority->priority == 'Low')
                                        - Gangguan kecil/permintaan enhancement
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Seberapa besar dampak masalah ini terhadap pekerjaan Anda?</small>
                    </div>

                    <!-- Aset yang Bermasalah -->
                    <div class="form-group">
                        <label for="asset_id">
                            <i class="fa fa-desktop"></i> Aset yang Bermasalah
                            @if ($errors->has('asset_id'))
                                <small class="text-red">{{ $errors->first('asset_id') }}</small>
                            @endif
                        </label>
                        <select class="form-control" name="asset_id">
                            <option value="">-- Tidak terkait dengan aset tertentu --</option>
                            @foreach($userAssets as $asset)
                                <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->asset_tag }} - {{ $asset->model->model_name ?? 'Model tidak diketahui' }}
                                    @if($asset->serial_number)
                                        (S/N: {{ $asset->serial_number }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih aset jika masalah terkait dengan perangkat tertentu</small>
                    </div>

                    <!-- Lokasi -->
                    <div class="form-group">
                        <label for="location_id">
                            <i class="fa fa-map-marker"></i> Lokasi
                            @if ($errors->has('location_id'))
                                <small class="text-red">{{ $errors->first('location_id') }}</small>
                            @endif
                        </label>
                        <select class="form-control" name="location_id">
                            <option value="">-- Pilih Lokasi (Opsional) --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Dimana masalah ini terjadi?</small>
                    </div>

                    <!-- Judul Masalah -->
                    <div class="form-group">
                        <label for="subject">
                            <i class="fa fa-edit"></i> Judul Masalah <span class="text-red">*</span>
                            @if ($errors->has('subject'))
                                <small class="text-red">{{ $errors->first('subject') }}</small>
                            @endif
                        </label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject') }}" 
                               placeholder="Contoh: Laptop tidak bisa menyala" maxlength="255" required>
                        <small class="text-muted">Berikan judul yang singkat dan jelas menggambarkan masalah</small>
                    </div>

                    <!-- Deskripsi Detail -->
                    <div class="form-group">
                        <label for="description">
                            <i class="fa fa-comment"></i> Deskripsi Detail <span class="text-red">*</span>
                            @if ($errors->has('description'))
                                <small class="text-red">{{ $errors->first('description') }}</small>
                            @endif
                        </label>
                        <textarea class="form-control" name="description" rows="6" 
                                  placeholder="Jelaskan masalah secara detail:&#10;- Apa yang sedang Anda lakukan saat masalah terjadi?&#10;- Pesan error apa yang muncul (jika ada)?&#10;- Sudah berapa lama masalah ini terjadi?&#10;- Langkah apa yang sudah Anda coba?" required>{{ old('description') }}</textarea>
                        <small class="text-muted">Semakin detail informasi yang Anda berikan, semakin cepat kami dapat membantu</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-paper-plane"></i> Kirim Tiket
                        </button>
                        <a href="{{ route('tickets.user-index') }}" class="btn btn-default btn-lg">
                            <i class="fa fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if(count($errors))
            <div class="alert alert-danger">
                <h4><i class="fa fa-exclamation-triangle"></i> Mohon perbaiki kesalahan berikut:</h4>
                <ul style="margin-bottom: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    
    <!-- Sidebar dengan Tips -->
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Tips Membuat Tiket</h3>
            </div>
            <div class="box-body">
                <h5><i class="fa fa-check"></i> Judul yang Baik:</h5>
                <ul>
                    <li>✅ "Laptop tidak bisa menyala"</li>
                    <li>✅ "Printer tidak bisa print warna"</li>
                    <li>❌ "Tolong bantu"</li>
                    <li>❌ "Ada masalah"</li>
                </ul>
                
                <h5><i class="fa fa-info-circle"></i> Informasi Penting:</h5>
                <ul>
                    <li>Jelaskan langkah-langkah yang sudah dicoba</li>
                    <li>Sertakan pesan error jika ada</li>
                    <li>Ceritakan kapan masalah mulai terjadi</li>
                    <li>Apakah masalah terjadi terus-menerus atau kadang-kadang</li>
                </ul>
                
                <h5><i class="fa fa-clock-o"></i> Tingkat Prioritas:</h5>
                <ul class="list-unstyled">
                    <li><span class="label label-danger">Urgent</span> - Sistem down total</li>
                    <li><span class="label label-warning">High</span> - Tidak bisa bekerja</li>
                    <li><span class="label label-primary">Normal</span> - Masih bisa bekerja</li>
                    <li><span class="label label-success">Low</span> - Bug kecil</li>
                </ul>
            </div>
        </div>
        
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-phone"></i> Kontak Darurat</h3>
            </div>
            <div class="box-body">
                <p>Untuk masalah <strong>URGENT</strong> yang memerlukan penanganan segera:</p>
                <ul class="list-unstyled">
                    <li><i class="fa fa-phone"></i> <strong>Telp:</strong> Ext. 123</li>
                    <li><i class="fa fa-whatsapp"></i> <strong>WhatsApp:</strong> 08XX-XXXX-XXXX</li>
                </ul>
                <small class="text-muted">Gunakan kontak darurat hanya untuk masalah yang benar-benar urgent</small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-select priority based on asset status
    $('select[name="asset_id"]').change(function() {
        var assetId = $(this).val();
        if (assetId) {
            // You can add AJAX call here to get asset details and suggest priority
        }
    });
    
    // Character counter for subject
    $('input[name="subject"]').on('input', function() {
        var maxLength = 255;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        // Remove existing counter
        $(this).siblings('.char-counter').remove();
        
        // Add counter
        if (remaining < 50) {
            $(this).after('<small class="char-counter text-muted">' + remaining + ' karakter tersisa</small>');
        }
    });
</script>
@endsection