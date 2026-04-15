@php
    // Ensure $assetRequest is defined (create view may not pass it)
    $assetRequest = $assetRequest ?? null;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="requester_name">Nama</label>
            <input type="text" id="requester_name" class="form-control" value="{{ old('requester_name', optional(optional($assetRequest)->requestedBy)->name ?? auth()->user()->name ?? '') }}" disabled>
        </div>
    </div>

    <div class="col-md-6">
        @if(optional($assetRequest)->request_number)
            <div class="form-group">
                <label for="request_number">Request #</label>
                <input type="text" id="request_number" class="form-control" value="{{ $assetRequest->request_number }}" disabled>
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="division">Divisi</label>
            <input type="text" id="division" class="form-control" value="{{ old('division', optional(optional($assetRequest)->requestedBy)->division ?? auth()->user()->division ?? '') }}" disabled>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="title">Nama Barang / Asset <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', optional($assetRequest)->title) }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="asset_type_id">Jenis Assets <span class="text-danger">*</span></label>
            <select class="form-control @error('asset_type_id') is-invalid @enderror" id="asset_type_id" name="asset_type_id" required>
                <option value="">Pilih Tipe Asset</option>
                @foreach($assetTypes as $type)
                    <option value="{{ $type->id }}" {{ old('asset_type_id', optional($assetRequest)->asset_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('asset_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="requested_quantity">Jumlah</label>
            <input type="number" name="requested_quantity" id="requested_quantity" class="form-control @error('requested_quantity') is-invalid @enderror" value="{{ old('requested_quantity', optional($assetRequest)->requested_quantity ?? 1) }}" min="1">
            @error('requested_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="unit">Satuan</label>
            <input type="text" name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', optional($assetRequest)->unit) }}">
            @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="priority">Prioritas</label>
            <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority">
                <option value="">Pilih Prioritas</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority }}" {{ old('priority', optional($assetRequest)->priority) == $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                @endforeach
            </select>
            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="justification">Keterangan / Justification <span class="text-danger">*</span></label>
    <textarea class="form-control @error('justification') is-invalid @enderror" id="justification" name="justification" rows="6" required>{{ old('justification', optional($assetRequest)->justification) }}</textarea>
    <small class="form-text text-muted">Jelaskan secara detail: mengapa asset ini dibutuhkan, untuk keperluan apa, dampak jika tidak mendapatkan asset ini</small>
    @error('justification')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
