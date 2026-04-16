@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => $pageTitle ?? 'Edit Asset',
    'subtitle' => 'Update asset information - ' . ($asset->asset_tag ?? ''),
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Assets', 'url' => route('assets.index')],
        ['label' => 'Edit']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('assets.show', $asset->id).'" class="btn btn-info">
                <i class="fa fa-eye"></i> <span class="hidden-xs">View</span>
            </a>
            <a href="'.route('assets.index').'" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
            </a>
        </div>
    '
])

<div class="pull-right" style="margin-top: -52px; margin-bottom: 16px; margin-right: 15px;">
  <div class="btn-group btn-group-xs" role="group" aria-label="Asset Edit Language Toggle">
    <button type="button" class="btn btn-default" id="assetEditLanguageEnglish" data-lang="en">EN</button>
    <button type="button" class="btn btn-default" id="assetEditLanguageIndonesian" data-lang="id">ID</button>
  </div>
</div>
<div class="clearfix"></div>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title" data-i18n="assets.edit.form.title">Asset Information</h3>
        </div>
        <div class="box-body">
          {{-- Flash Messages --}}
          @if(session('success'))
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"></i> {{ session('success') }}
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-ban"></i> {{ session('error') }}
            </div>
          @endif

          {{-- Asset Metadata --}}
          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Asset Info:</strong>
            Created: {{ $asset->created_at ? $asset->created_at->format('d M Y H:i') : 'N/A' }} |
            Last Updated: {{ $asset->updated_at ? $asset->updated_at->format('d M Y H:i') : 'N/A' }}
          </div>

          <form method="POST" action="/assets/{{$asset->id}}" id="asset-edit-form">
            {{method_field('PATCH')}}
            {{csrf_field()}}
            
            {{-- SECTION 1: Basic Information --}}
            <fieldset>
              <legend><i class="fa fa-info-circle"></i> <span data-i18n="assets.edit.section.basic">Basic Information</span></legend>

              <div class="form-group">
                <label for="asset_tag">Kode Assets <span class="text-red">*</span></label>
                <input type="text" name="asset_tag" id="asset_tag" class="form-control @error('asset_tag') is-invalid @enderror" value="{{ old('asset_tag', $asset->asset_tag) }}" required maxlength="50">
                <small class="text-muted">Unique identifier for this asset (max 50 characters)</small>
                @error('asset_tag')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="asset_type_id">Kategori (Tipe Asset) <span class="text-red">*</span></label>
                <select name="asset_type_id" id="asset_type_id" class="form-control asset_type_id @error('asset_type_id') is-invalid @enderror" required>
                  <option value="">-- Pilih Kategori (Tipe) --</option>
                  @foreach($asset_types as $atype)
                    <option value="{{ $atype->id }}" {{ (old('asset_type_id', $asset->model->asset_type_id ?? '') == $atype->id) ? 'selected' : '' }}>{{ $atype->type_name }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Category determines available models (PC, Laptop, Printer, etc.)</small>
                @error('asset_type_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="model_id">Model (optional)</label>
                <select name="model_id" id="model_id" class="form-control model_id @error('model_id') is-invalid @enderror">
                  <option value="">-- Pilih Model (optional) --</option>
                  @foreach($asset_models as $asset_model)
                    <option value="{{ $asset_model->id }}" data-asset-type="{{ $asset_model->asset_type_id }}" {{ (old('model_id', $asset->model_id) == $asset_model->id) ? 'selected' : '' }}>{{ $asset_model->manufacturer->name ?? '' }} - {{ $asset_model->asset_model }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Select model after choosing asset type above</small>
                @error('model_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="serial_number">S/N</label>
                <input type="text" name="serial_number" id="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $asset->serial_number) }}">
                <small class="text-muted">Manufacturer's serial number (optional)</small>
                <small id="serial-feedback" class="text-muted" style="display:none"></small>
                @error('serial_number')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="notes">Spesifikasi <span class="text-red">*</span></label>
                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" required>{{ old('notes', $asset->notes) }}</textarea>
                <small class="text-muted">Detailed specifications (e.g., RAM, CPU, Storage for computers)</small>
                @error('notes')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="status_id">Status <span class="text-red">*</span></label>
                <select class="form-control status_id @error('status_id') is-invalid @enderror" name="status_id" id="status_id" required>
                  <option value="">Select Status</option>
                  @foreach($statuses as $status)
                    <option value="{{$status->id}}" {{ ($asset->status_id == $status->id) ? 'selected' : '' }}>{{$status->name}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Current operational status</small>
                @error('status_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- SECTION 2: Location & Assignment --}}
            <fieldset>
              <legend><i class="fa fa-map-marker"></i> <span data-i18n="assets.edit.section.location">Location & Assignment</span></legend>

              <div class="form-group">
                <label for="location_id">Lokasi <span class="text-red">*</span></label>
                <select class="form-control location_id @error('location_id') is-invalid @enderror" name="location_id" id="location_id" required>
                  <option value="">-- Pilih Lokasi --</option>
                  @foreach($locations as $location)
                    <option value="{{$location->id}}" {{ (old('location_id', $asset->location_id) == $location->id) ? 'selected' : '' }}>{{$location->location_name}} - {{$location->building}}, {{$location->office}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Physical location where asset is deployed</small>
                @error('location_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="assigned_to">User / PIC <span class="text-red">*</span></label>
                <select name="assigned_to" id="assigned_to" class="form-control assigned_to @error('assigned_to') is-invalid @enderror" required>
                  <option value="">-- Pilih User / PIC --</option>
                  @php $activeUsers = \App\User::where('is_active', 1)->orderBy('name')->get(); @endphp
                  @foreach($activeUsers as $u)
                    <option value="{{ $u->id }}" {{ (old('assigned_to', $asset->assigned_to) == $u->id) ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                  @endforeach
                </select>
                <small class="text-muted">Person responsible for this asset</small>
                @error('assigned_to')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- SECTION 3: Purchase & Warranty Information --}}
            <fieldset>
              <legend><i class="fa fa-shopping-cart"></i> <span data-i18n="assets.edit.section.purchase">Purchase & Warranty Information</span></legend>

              <div class="form-group">
                <label for="purchase_date">Tanggal Beli <span class="text-red">*</span></label>
                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" id="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}" required>
                <small class="text-muted">Date when asset was purchased</small>
                @error('purchase_date')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="supplier_id">Suplier <span class="text-red">*</span></label>
                <select class="form-control supplier_id @error('supplier_id') is-invalid @enderror" name="supplier_id" id="supplier_id" required>
                  <option value="">-- Pilih Supplier --</option>
                  @foreach($suppliers as $supplier)
                    <option value="{{$supplier->id}}" {{ (old('supplier_id', $asset->supplier_id) == $supplier->id) ? 'selected' : '' }}>{{$supplier->name}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Vendor who supplied this asset</small>
                @error('supplier_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="purchase_order_id">Purchase Order (Optional)</label>
                <select class="form-control purchase_order_id @error('purchase_order_id') is-invalid @enderror" name="purchase_order_id" id="purchase_order_id">
                  <option value="">-- No Purchase Order --</option>
                  @foreach($purchaseOrders ?? [] as $po)
                    <option value="{{ $po->id }}" {{ (old('purchase_order_id', $asset->purchase_order_id) == $po->id) ? 'selected' : '' }}>
                      {{ $po->po_number }} - {{ $po->order_date ? \Carbon\Carbon::parse($po->order_date)->format('Y-m-d') : '' }} - {{ $po->supplier ? $po->supplier->name : '' }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted">Link to existing purchase order if applicable</small>
                @error('purchase_order_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="warranty_type_id">Jenis Garansi <span class="text-red">*</span></label>
                <select class="form-control warranty_type_id @error('warranty_type_id') is-invalid @enderror" name="warranty_type_id" id="warranty_type_id" required>
                  <option value="">-- Pilih Jenis Garansi --</option>
                  @foreach($warranty_types as $warranty_type)
                    <option value="{{$warranty_type->id}}" {{ (old('warranty_type_id', $asset->warranty_type_id) == $warranty_type->id) ? 'selected' : '' }}>{{$warranty_type->name}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Warranty coverage type</small>
                @error('warranty_type_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- SECTION 4: Network & Additional Details --}}
            <fieldset>
              <legend><i class="fa fa-network-wired"></i> <span data-i18n="assets.edit.section.network">Network & Additional Details</span></legend>

              <div class="form-group">
                <label for="ip_address">IP Address</label>
                <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror" id="ip_address" value="{{ old('ip_address', $asset->ip_address) }}" placeholder="e.g., 192.168.1.100">
                <small class="text-muted">Only applicable for network devices</small>
                @error('ip_address')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="mac_address">MAC Address</label>
                <input type="text" name="mac_address" class="form-control @error('mac_address') is-invalid @enderror" id="mac_address" value="{{ old('mac_address', $asset->mac_address) }}" placeholder="e.g., 00:1B:44:11:3A:B7">
                <small class="text-muted">Hardware address for network identification</small>
                @error('mac_address')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- Submit Buttons --}}
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> <b data-i18n="assets.edit.action.submit">Update Asset</b>
              </button>
              <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-info btn-lg">
                <i class="fa fa-eye"></i> <span data-i18n="assets.edit.action.view">View</span>
              </a>
              <a href="{{ route('assets.index') }}" class="btn btn-secondary btn-lg">
                <i class="fa fa-times"></i> <span data-i18n="assets.edit.action.cancel">Cancel</span>
              </a>
            </div>
          </form>
        </div>
      </div>

      {{-- Display validation errors if any --}}
      @if(count($errors))
        <div class="alert alert-danger">
          <h4><i class="icon fa fa-ban"></i> Validation Errors!</h4>
          <ul>
            @foreach($errors->all() as $error)
              <li>{{$error}}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>

{{-- Loading Overlay --}}
@include('components.loading-overlay')

@endsection

@section('footer')
  <script type="text/javascript">
    (function() {
      var translations = {
        en: {
          'assets.edit.form.title': 'Asset Information',
          'assets.edit.section.basic': 'Basic Information',
          'assets.edit.section.location': 'Location & Assignment',
          'assets.edit.section.purchase': 'Purchase & Warranty Information',
          'assets.edit.section.network': 'Network & Additional Details',
          'assets.edit.action.submit': 'Update Asset',
          'assets.edit.action.view': 'View',
          'assets.edit.action.cancel': 'Cancel',
          'assets.edit.runtime.loading': 'Updating asset...',
          'assets.edit.runtime.serial_exists': 'Serial number already exists in the system.',
          'assets.edit.runtime.serial_available': 'Serial number available.'
        },
        id: {
          'assets.edit.form.title': 'Informasi Aset',
          'assets.edit.section.basic': 'Informasi Dasar',
          'assets.edit.section.location': 'Lokasi & Penugasan',
          'assets.edit.section.purchase': 'Informasi Pembelian & Garansi',
          'assets.edit.section.network': 'Jaringan & Detail Tambahan',
          'assets.edit.action.submit': 'Perbarui Aset',
          'assets.edit.action.view': 'Lihat',
          'assets.edit.action.cancel': 'Batal',
          'assets.edit.runtime.loading': 'Memperbarui aset...',
          'assets.edit.runtime.serial_exists': 'Nomor serial sudah ada di sistem.',
          'assets.edit.runtime.serial_available': 'Nomor serial tersedia.'
        }
      };

      var currentLanguage = 'en';
      var userId = '{{ (int) auth()->id() }}';
      var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
      var englishButton = document.getElementById('assetEditLanguageEnglish');
      var indonesianButton = document.getElementById('assetEditLanguageIndonesian');

      function getLanguage() {
        try {
          var raw = window.localStorage.getItem(languageStorageKey);
          if (!raw) {
            return 'en';
          }

          var parsed = JSON.parse(raw);
          return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
          return 'en';
        }
      }

      function saveLanguage(language) {
        try {
          var raw = window.localStorage.getItem(languageStorageKey);
          var parsed = raw ? JSON.parse(raw) : {};
          parsed.language = language === 'id' ? 'id' : 'en';
          window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
          // Keep silent if localStorage is unavailable.
        }
      }

      function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
      }

      function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
          var key = node.getAttribute('data-i18n');
          if (dictionary[key]) {
            node.textContent = dictionary[key];
          }
        });

        if (englishButton && indonesianButton) {
          englishButton.classList.toggle('active', currentLanguage === 'en');
          indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }
      }

      window.assetEditLabel = getLabel;

      if (englishButton && indonesianButton) {
        englishButton.addEventListener('click', function() {
          saveLanguage('en');
          applyLanguage('en');
        });

        indonesianButton.addEventListener('click', function() {
          saveLanguage('id');
          applyLanguage('id');
        });
      }

      applyLanguage(getLanguage());
    })();

    // Form loading state
    $('#asset-edit-form').on('submit', function() {
      showLoading(window.assetEditLabel('assets.edit.runtime.loading', 'Updating asset...'));
    });

    // Serial number uniqueness check (AJAX) for edit form
    $(function(){
      $('#serial_number').on('blur', function(){
        var serial = $(this).val().trim();
        var excludeId = '{{ $asset->id }}';
        if (!serial) {
          $('#serial-feedback').hide();
          return;
        }
        $.getJSON('{{ route("api.assets.checkSerial") }}', { serial: serial, exclude_id: excludeId })
          .done(function(resp){
            if (resp && resp.success) {
              if (resp.exists) {
                $('#serial-feedback').show().removeClass('text-muted text-success').addClass('text-danger').text(window.assetEditLabel('assets.edit.runtime.serial_exists', 'Serial number already exists in the system.'));
              } else {
                $('#serial-feedback').show().removeClass('text-danger text-muted').addClass('text-success').text(window.assetEditLabel('assets.edit.runtime.serial_available', 'Serial number available.'));
              }
            }
          }).fail(function(){
            $('#serial-feedback').hide();
          });
      });
    });

    $(":input").keypress(function(event){
      if (event.which == '10' || event.which == '13') {
        event.preventDefault();
      }
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      // Initialize Select2 for all dropdowns
      $(".model_id").select2();
      $(".division_id").select2();
      $(".supplier_id").select2();
      $(".invoice_id").select2();
      $(".warranty_type_id").select2();
      $(".status_id").select2();
      $(".location_id").select2();
      $(".assigned_to").select2();
      $(".asset_type_id").select2();
      $(".purchase_order_id").select2();

      // When asset type changes in edit form, filter model options and toggle PC fields
      $('#asset_type_id').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        var selectedId = $(this).val();
        $('.pc-laptop-fields').hide();
        if (selectedText && (selectedText.toLowerCase().includes('pc') || selectedText.toLowerCase().includes('laptop') || selectedText.toLowerCase().includes('computer'))) {
          $('.pc-laptop-fields').show();
        }
        $('#model_id option').each(function() {
          var mt = $(this).data('asset-type') ? String($(this).data('asset-type')) : '';
          if (!selectedId || mt === '' || mt === selectedId) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
        if ($('#model_id option:selected').is(':hidden')) {
          $('#model_id').val('').trigger('change');
        }
      });

      // Trigger change on load to apply filtering if an asset type is already selected
      if ($('#asset_type_id').val()) {
        $('#asset_type_id').trigger('change');
      }
    });
  </script>
@endsection


