@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => $pageTitle ?? 'Create New Asset',
    'subtitle' => 'Add a new asset to the inventory',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Assets', 'url' => route('assets.index')],
        ['label' => 'Create']
    ],
    'actions' => '
        <a href="'.route('assets.index').'" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
        </a>
    '
])

  <div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Asset Information</h3>
        </div>
        <div class="box-body">
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

          <form method="POST" action="{{ url('assets') }}" id="asset-create-form">
            {{csrf_field()}}
            
            {{-- SECTION 1: Basic Information --}}
            <fieldset>
              <legend><i class="fa fa-info-circle"></i> Basic Information</legend>
              
              <div class="form-group">
                <label for="asset_tag">Kode Assets <span class="text-red">*</span></label>
                <input type="text" name="asset_tag" id="asset_tag" class="form-control @error('asset_tag') is-invalid @enderror" value="{{ old('asset_tag') }}" required maxlength="50" placeholder="e.g., AST-001">
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
                  <option value="{{ $atype->id }}" {{ old('asset_type_id') == $atype->id ? 'selected' : '' }}>{{ $atype->type_name }}</option>
                @endforeach
              </select>
              <small class="text-muted">This will filter available models below</small>
              @error('asset_type_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
                <label for="model_id">Model <small class="text-muted">(optional)</small></label>
                <select name="model_id" id="model_id" class="form-control model_id">
                  <option value="">-- Pilih Model (optional) --</option>
                  @foreach($asset_models as $model)
                    <option value="{{ $model->id }}" data-asset-type="{{ $model->asset_type_id }}" {{ old('model_id') == $model->id ? 'selected' : '' }}>{{ $model->manufacturer->name ?? '' }} - {{ $model->asset_model }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Select model after choosing asset type above</small>
              </div>
              
              <div class="form-group">
                <label for="serial_number">Serial Number</label>
                <input type="text" name="serial_number" id="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number') }}">
                <small id="serial-feedback" class="text-muted" style="display:none"></small>
                <small class="text-muted">Leave blank if not applicable</small>
                @error('serial_number')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              
              <div class="form-group">
                <label for="notes">Spesifikasi <span class="text-red">*</span></label>
                <textarea name="notes" id="notes" class="form-control" rows="3" required placeholder="Enter detailed specifications...">{{ old('notes') }}</textarea>
                <small class="text-muted">Describe the asset specifications (min 10 characters)</small>
              </div>
            </fieldset>
            
            {{-- SECTION 2: Location & Assignment --}}
            <fieldset>
              <legend><i class="fa fa-map-marker"></i> Location & Assignment</legend>
              
              <div class="form-group">
                <label for="location_id">Lokasi <span class="text-red">*</span></label>
              <select class="form-control location_id" name="location_id" id="location_id" required>
                <option value="">-- Pilih Lokasi --</option>
                @foreach($locations as $location)
                    <option value="{{$location->id}}" {{ old('location_id') == $location->id ? 'selected' : '' }}>{{$location->location_name}} - {{$location->building}}, {{$location->office}}</option>
                @endforeach
              </select>
            </div>

              <div class="form-group">
                <label for="division_id">Divisi <small class="text-muted">(optional)</small></label>
                <select class="form-control division_id" name="division_id" id="division_id">
                  <option value="">-- Pilih Divisi --</option>
                  @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Division responsible for this asset</small>
              </div>

              <div class="form-group">
                <label for="assigned_to">User / PIC <span class="text-red">*</span></label>
                <select name="assigned_to" id="assigned_to" class="form-control assigned_to" required>
                  <option value="">-- Pilih User / PIC --</option>
                  @php $activeUsers = \App\User::where('is_active', 1)->orderBy('name')->get(); @endphp
                  @foreach($activeUsers as $u)
                    <option value="{{ $u->id }}" {{ old('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                  @endforeach
                </select>
                <small class="text-muted">Person responsible for this asset</small>
              </div>
            </fieldset>
            
            {{-- SECTION 3: Purchase & Warranty Information --}}
            <fieldset>
              <legend><i class="fa fa-shopping-cart"></i> Purchase & Warranty Information</legend>
              
              <div class="form-group">
                <label for="purchase_date">Tanggal Beli <span class="text-red">*</span></label>
                <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date') }}" required>
                <small class="text-muted">Date when asset was purchased</small>
              </div>

              <div class="form-group">
                <label for="supplier_id">Supplier <span class="text-red">*</span></label>
              <select class="form-control supplier_id" name="supplier_id" id="supplier_id" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{$supplier->id}}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{$supplier->name}}</option>
                @endforeach
              </select>
            </div>

              <div class="form-group">
                <label for="purchase_order_id">Purchase Order <small class="text-muted">(optional)</small></label>
                <select class="form-control purchase_order_id" name="purchase_order_id" id="purchase_order_id">
                  <option value="">-- No Purchase Order --</option>
          @foreach($purchaseOrders ?? [] as $po)
            <option value="{{ $po->id }}" {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
              {{ $po->po_number }} - {{ $po->order_date ? \Carbon\Carbon::parse($po->order_date)->format('Y-m-d') : '' }} - {{ $po->supplier ? $po->supplier->name : '' }}
            </option>
          @endforeach
                </select>
                <small class="text-muted">Link to existing purchase order if applicable</small>
              </div>

              <div class="form-group">
                <label for="warranty_type_id">Jenis Garansi <span class="text-red">*</span></label>
                <select class="form-control warranty_type_id" name="warranty_type_id" id="warranty_type_id" required>
                  <option value="">-- Pilih Jenis Garansi --</option>
                  @foreach($warranty_types as $warranty_type)
                      <option value="{{$warranty_type->id}}" {{ old('warranty_type_id') == $warranty_type->id ? 'selected' : '' }}>{{$warranty_type->name}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Warranty type for this asset</small>
              </div>
              
              <div class="form-group">
                <label for="invoice_id">Invoice <small class="text-muted">(optional)</small></label>
                <select class="form-control invoice_id" name="invoice_id" id="invoice_id">
                  <option value="">No Invoice</option>
                  @foreach($invoices as $invoice)
                      <option value="{{$invoice->id}}">{{$invoice->invoice_number}} - {{$invoice->invoiced_date}} - {{$invoice->supplier->name}} - R{{$invoice->total}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Link to purchase invoice</small>
              </div>
            </fieldset>
            
            {{-- SECTION 4: Network & Additional Details --}}
            <fieldset>
              <legend><i class="fa fa-network-wired"></i> Network & Additional Details</legend>
              
              <div class="form-group">
                <label for="ip_address">IP Address</label>
                <input type="text" name="ip_address" id="ip_address" class="form-control @error('ip_address') is-invalid @enderror" value="{{ old('ip_address') }}" placeholder="e.g., 192.168.1.100">
                <small class="text-muted">Only applicable for network devices</small>
                @error('ip_address')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="mac_address">MAC Address</label>
                <input type="text" name="mac_address" id="mac_address" class="form-control @error('mac_address') is-invalid @enderror" value="{{ old('mac_address') }}" placeholder="e.g., 00:1B:44:11:3A:B7">
                <small class="text-muted">Hardware address for network identification</small>
                @error('mac_address')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>
            
            {{-- Keep status and warranty fields hidden but preserve existing inputs so other logic continues to work (set defaults) --}}
            <input type="hidden" name="status_id" value="{{ old('status_id', 1) }}">
            <input type="hidden" name="warranty_months" value="{{ old('warranty_months', 0) }}">

            <div class="form-group" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #ddd;">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> <b>Add New Asset</b>
              </button>
              <a href="{{ route('assets.index') }}" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-xs-12 col-sm-4 col-md-4">
      {{-- Help & Tips Box --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb"></i> Asset Creation Tips</h3>
        </div>
        <div class="box-body">
          <h5><i class="fa fa-check-circle text-success"></i> Before Creating:</h5>
          <ul style="margin-left: 20px; font-size: 13px;">
            <li>Verify asset tag is unique</li>
            <li>Check serial number accuracy</li>
            <li>Confirm warranty information</li>
            <li>Prepare detailed specifications</li>
          </ul>
          
          <h5 style="margin-top: 15px;"><i class="fa fa-info-circle text-primary"></i> Required Information:</h5>
          <ul style="margin-left: 20px; font-size: 13px;">
            <li><strong>Asset Tag:</strong> Unique identifier</li>
            <li><strong>Category:</strong> Asset type classification</li>
            <li><strong>Location:</strong> Physical location</li>
            <li><strong>User/PIC:</strong> Person responsible</li>
            <li><strong>Purchase Date:</strong> Acquisition date</li>
            <li><strong>Supplier:</strong> Vendor information</li>
            <li><strong>Warranty:</strong> Type and coverage</li>
          </ul>
          
          <h5 style="margin-top: 15px;"><i class="fa fa-exclamation-triangle text-warning"></i> Important Notes:</h5>
          <ul style="margin-left: 20px; font-size: 13px;">
            <li>Serial number uniqueness is checked automatically</li>
            <li>Model selection filters by asset type</li>
            <li>IP/MAC address only for network devices</li>
            <li>All fields are validated before saving</li>
          </ul>
        </div>
      </div>

      {{-- Warranty Check Links --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-shield"></i> Warranty Check Links</h3>
        </div>
        <div class="box-body">
          <p style="font-size: 12px; margin-bottom: 10px;">Verify warranty status directly with manufacturers:</p>
          <div style="margin-bottom: 8px;">
            <a href="http://h20564.www2.hp.com/hpsc/wc/public/home" target="_blank" class="btn btn-default btn-block btn-sm">
              <i class="fa fa-external-link"></i> HP Warranty Check
            </a>
          </div>
          <div style="margin-bottom: 8px;">
            <a href="http://customercare.acer-euro.com/customerselfservice/CaseBooking.aspx?CID=ZA&LID=ENG&OP=1#_ga=1.185835882.214577358.1416317708" target="_blank" class="btn btn-default btn-block btn-sm">
              <i class="fa fa-external-link"></i> Acer Warranty Check
            </a>
          </div>
          <div style="margin-bottom: 8px;">
            <a href="https://support.lenovo.com/us/en/warrantylookup" target="_blank" class="btn btn-default btn-block btn-sm">
              <i class="fa fa-external-link"></i> Lenovo Warranty Check
            </a>
          </div>
          <div>
            <a href="https://www.dell.com/support/home/en-us/product-support/servicetag/" target="_blank" class="btn btn-default btn-block btn-sm">
              <i class="fa fa-external-link"></i> Dell Warranty Check
            </a>
          </div>
        </div>
      </div>

      {{-- Quick Actions --}}
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('models.index') }}" class="btn btn-default btn-block" style="margin-bottom: 8px;">
            <i class="fa fa-cube"></i> Manage Models
          </a>
          <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-block" style="margin-bottom: 8px;">
            <i class="fa fa-truck"></i> Manage Suppliers
          </a>
          <a href="{{ route('locations.index') }}" class="btn btn-default btn-block">
            <i class="fa fa-map-marker"></i> Manage Locations
          </a>
        </div>
      </div>

      {{-- Validation Errors --}}
      @if(count($errors))
        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-exclamation-circle"></i> Validation Errors</h3>
          </div>
          <div class="box-body">
            <ul style="margin-left: 20px; margin-bottom: 0;">
              @foreach($errors->all() as $error)
                <li style="color: #dd4b39;">{{$error}}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif
    </div>
  </div>

{{-- Loading Overlay --}}
@include('components.loading-overlay')

@endsection

@section('footer')
  <script type="text/javascript">
    // Form loading state
    $('#asset-create-form').on('submit', function() {
      showLoading('Creating asset...');
    });

    // Serial number uniqueness check (AJAX)
    $(function(){
      $('#serial_number').on('blur', function(){
        var serial = $(this).val().trim();
        if (!serial) {
          $('#serial-feedback').hide();
          return;
        }
        $.getJSON('{{ route("api.assets.checkSerial") }}', { serial: serial })
          .done(function(resp){
            if (resp && resp.success) {
              if (resp.exists) {
                $('#serial-feedback').show().removeClass('text-muted text-success').addClass('text-danger').text('Serial number already exists in the system.');
              } else {
                $('#serial-feedback').show().removeClass('text-danger text-muted').addClass('text-success').text('Serial number available.');
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
      $(".model_id").select2();
      $(".division_id").select2();
      $(".supplier_id").select2();
      $(".location").select2();
      $(".location_id").select2();
      $(".assigned_to").select2();
      $(".asset_type_id").select2();
      $(".warranty_type_id").select2();
      $(".invoice_id").select2();
      $(".purchase_order_id").select2();
      $(".status_id").select2();

      // Handle asset model change to show/hide conditional fields
      $('#asset_type_id').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        var selectedId = $(this).val();
        // Hide PC/Laptop fields by default
        $('.pc-laptop-fields').hide();

        if (selectedText && (selectedText.toLowerCase().includes('pc') || selectedText.toLowerCase().includes('laptop') || selectedText.toLowerCase().includes('computer'))) {
          $('.pc-laptop-fields').show();
        }

        // Filter model select options by data-asset-type
        $('#model_id option').each(function() {
          var mt = $(this).data('asset-type') ? String($(this).data('asset-type')) : '';
          if (!selectedId || mt === '' || mt === selectedId) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
        // Reset model selection if current option hidden
        if ($('#model_id option:selected').is(':hidden')) {
          $('#model_id').val('').trigger('change');
        }
      });

      // Trigger change event on page load if there's a selected value
      if ($('#model_id').val()) {
        $('#model_id').trigger('change');
      }
    });
  </script>
@endsection


