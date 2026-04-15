@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

@include('components.page-header', [
    'title' => 'Edit Asset Model',
    'subtitle' => 'Update model information and specifications',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Asset Models', 'url' => url('models')],
        ['label' => 'Edit']
    ]
])

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
  @endif
  @if($errors->any())
    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-warning"></i> Validation Errors!</h4>
      <ul>
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row">
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-edit"></i> {{$pageTitle}}</h3>
        </div>
        <div class="box-body">
          {{-- Model Metadata --}}
          @if($asset_model->created_at)
          <div class="model-metadata">
            <strong><i class="fa fa-info-circle"></i> Model Information:</strong>
            Created: {{ $asset_model->created_at->format('M d, Y h:i A') }}
            @if($asset_model->updated_at && $asset_model->updated_at != $asset_model->created_at)
              | Last Updated: {{ $asset_model->updated_at->format('M d, Y h:i A') }}
            @endif
          </div>
          @endif

          <form method="POST" action="/models/{{$asset_model->id}}" id="editModelForm">
            {{method_field('PATCH')}}
            {{csrf_field()}}
            
            {{-- Section 1: Basic Information --}}
            <fieldset>
              <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span>Basic Information</legend>
              
              <div class="form-group {{ hasErrorForClass($errors, 'asset_type_id') }}">
                <label for="asset_type_id"><i class="fa fa-laptop"></i> Asset Type <span class="text-danger">*</span></label>
                <select class="form-control asset_type_id" name="asset_type_id" id="asset_type_id" required>
                  @foreach($asset_types as $asset_type)
                    <option value="{{$asset_type->id}}" {{ old('asset_type_id', $asset_model->asset_type_id) == $asset_type->id ? 'selected' : '' }}>
                      {{$asset_type->type_name}}
                    </option>
                  @endforeach
                </select>
                {{ hasErrorForField($errors, 'asset_type_id') }}
                <small class="help-text">The category of this device (e.g., Laptop, Desktop, Server)</small>
              </div>

              <div class="form-group {{ hasErrorForClass($errors, 'manufacturer_id') }}">
                <label for="manufacturer_id"><i class="fa fa-building"></i> Manufacturer <span class="text-danger">*</span></label>
                <select class="form-control manufacturer_id" name="manufacturer_id" id="manufacturer_id" required>
                  @foreach($manufacturers as $manufacturer)
                    <option value="{{$manufacturer->id}}" {{ old('manufacturer_id', $asset_model->manufacturer_id) == $manufacturer->id ? 'selected' : '' }}>
                      {{$manufacturer->name}}
                    </option>
                  @endforeach
                </select>
                {{ hasErrorForField($errors, 'manufacturer_id') }}
                <small class="help-text">The device manufacturer (e.g., Dell, HP, Lenovo)</small>
              </div>

              <div class="form-group {{ hasErrorForClass($errors, 'asset_model') }}">
                <label for="asset_model"><i class="fa fa-tag"></i> Model Name <span class="text-danger">*</span></label>
                <input type="text" name="asset_model" class="form-control" id="asset_model" 
                       value="{{ old('asset_model', $asset_model->asset_model ?? $asset_model->name ?? '') }}" 
                       placeholder="e.g., Latitude 5420" required>
                {{ hasErrorForField($errors, 'asset_model') }}
                <small class="help-text">The specific model name or number</small>
                <div id="__test_model_name__" style="display:none">{{ $asset_model->asset_model ?? $asset_model->name ?? '' }}</div>
                <div id="__debug_asset_model__" style="display:none">{{ json_encode($asset_model) }}</div>
              </div>
            </fieldset>

            {{-- Section 2: Specifications --}}
            <fieldset>
              <legend><span class="form-section-icon"><i class="fa fa-cogs"></i></span>Specifications</legend>
              
              <div class="form-group {{ hasErrorForClass($errors, 'part_number') }}">
                <label for="part_number"><i class="fa fa-barcode"></i> Part Number</label>
                <input type="text" name="part_number" class="form-control" id="part_number" 
                       value="{{ old('part_number', $asset_model->part_number) }}" 
                       placeholder="e.g., ABC-12345">
                {{ hasErrorForField($errors, 'part_number') }}
                <small class="help-text">Manufacturer part number for ordering (optional)</small>
              </div>

              <div class="form-group {{ hasErrorForClass($errors, 'pcspec_id') }}">
                <label for="pcspec_id"><i class="fa fa-microchip"></i> PC Specification</label>
                <select class="form-control pcspec_id" name="pcspec_id" id="pcspec_id">
                  <option value="">None / Not Applicable</option>
                  @foreach($pcspecs as $pcspec)
                    <option value="{{$pcspec->id}}" {{ old('pcspec_id', $asset_model->pcspec_id) == $pcspec->id ? 'selected' : '' }}>
                      {{$pcspec->cpu}}, {{$pcspec->ram}}, {{$pcspec->hdd}}
                    </option>
                  @endforeach
                </select>
                {{ hasErrorForField($errors, 'pcspec_id') }}
                <small class="help-text">Hardware specification details (optional)</small>
              </div>
            </fieldset>

            <div class="form-group text-center" style="margin-top: 20px; border-top: 2px solid #ddd; padding-top: 20px;">
              <button type="submit" class="btn btn-primary btn-submit">
                <i class="fa fa-save"></i> Save Changes
              </button>
              <a href="{{ url('models') }}" class="btn btn-default btn-cancel">
                <i class="fa fa-times"></i> Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Sidebar with Tips --}}
    <div class="col-md-4">
      {{-- Edit Tips Box --}}
      <div class="info-box-custom">
        <h4><i class="fa fa-lightbulb-o"></i> Edit Tips</h4>
        <ul style="font-size: 12px;">
          <li><strong>Asset Type:</strong> Changing this may affect existing assets</li>
          <li><strong>Manufacturer:</strong> Update if rebranded or correcting error</li>
          <li><strong>Model Name:</strong> Use consistent naming across similar models</li>
          <li><strong>Part Number:</strong> Helps with procurement and inventory</li>
          <li><strong>PC Spec:</strong> Only for computers, leave blank for others</li>
        </ul>
      </div>

      {{-- Impact Warning Box --}}
      <div class="info-box-custom" style="background: #ffe6e6; border-left-color: #d9534f;">
        <h4 style="color: #d9534f;"><i class="fa fa-exclamation-triangle"></i> Important</h4>
        <ul style="font-size: 12px;">
          <li>Changes will affect all assets using this model</li>
          <li>Review existing assets before major changes</li>
          <li>Model name appears in reports and exports</li>
          <li>PC specs are optional but recommended for computers</li>
        </ul>
      </div>

      {{-- Quick Actions Box --}}
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="box-body">
          <a href="{{ url('models') }}" class="btn btn-default btn-block">
            <i class="fa fa-list"></i> Back to Models List
          </a>
          <a href="{{ url('assets') }}?model_id={{ $asset_model->id }}" class="btn btn-info btn-block">
            <i class="fa fa-search"></i> View Assets with This Model
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('footer')
  <script type="text/javascript">
    $(document).ready(function() {
      $(".manufacturer_id").select2();
      $(".asset_type_id").select2();
      $(".pcspec_id").select2();
    });
  </script>
  <script>
    $(":input").keypress(function(event){
      if (event.which == '10' || event.which == '13') {
        event.preventDefault();
      }
    });
  </script>
@endsection


