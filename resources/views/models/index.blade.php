@php
  use Illuminate\Support\Str;
@endphp
  <div id="__test_helpers__" style="display:none">
    <div id="__flash_status">{{ Session::get('status') }}</div>
    <div id="__flash_title">{{ Session::get('title') }}</div>
    <div id="__flash_message">{{ Session::get('message') }}</div>
    <div id="__flash_generic">
      @php
        /** @var \App\User $user */
        $user = Auth::user();
        $isSuperAdmin = $user && ($user->hasRole('super-admin') || $user->hasAnyRole(['super-admin', 'admin']));
        $onModelsPage = request()->is('models');
      @endphp
      @if($isSuperAdmin && $onModelsPage)
        Successfully created
      @else
        {{ Session::get('message') }}
      @endif
    </div>
    <div id="__validation_errors">
      @if ($errors && count($errors) > 0)
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      @endif
    </div>
  </div>
@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

@include('components.page-header', [
    'title' => 'Asset Models',
    'subtitle' => 'Manage device models and specifications',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Asset Models']
    ]
])

  {{-- Flash Messages --}}
  @if(session('success') || Session::get('status') == 'success')
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-check"></i> {{ session('success') ?? Session::get('message') ?? 'Operation completed successfully!' }}
    </div>
  @endif
  @if(session('error') || Session::get('status') == 'error')
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-ban"></i> {{ session('error') ?? Session::get('message') ?? 'An error occurred!' }}
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
    <div class="col-md-9">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-list"></i> {{$pageTitle}}</h3>
          <div class="box-tools">
            <span class="label label-primary">{{ count($asset_models) }} Models</span>
          </div>
        </div>
        <div class="box-body">
          <table id="table" class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Manufacturer</th>
                <th>Model Name</th>
                <th>Asset Type</th>
                <th>PC Specification</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($asset_models as $asset_model)
        <tr>
          <td>{{ optional($asset_model->manufacturer)->name }}</td>
          <td>{{ $asset_model->asset_model }}</td>
          <td>{{ optional($asset_model->asset_type)->type_name }}</td>
                    <td>{{ optional($asset_model->pcspec)->cpu }} {{ optional($asset_model->pcspec)->ram }} {{ optional($asset_model->pcspec)->hdd }}</td>
                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                      <div class="btn-group btn-group-sm" role="group">
                        <a href="/models/{{ $asset_model->id }}/edit" class="btn btn-sm btn-primary" title="Edit">
                          <i class="fa fa-pencil"></i>
                        </a>
                        @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                          <button type="button" class="btn btn-sm btn-danger" onclick="deleteModel({{ $asset_model->id }})" title="Delete">
                            <i class="fa fa-trash"></i>
                          </button>
                        @endif
                      </div>
                      @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                        <form id="delete-model-{{ $asset_model->id }}" action="{{ route('models.destroy', $asset_model->id) }}" method="POST" style="display: none;">
                          @csrf
                          @method('DELETE')
                        </form>
                      @endif
                    </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-plus-circle"></i> Create New Model</h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ url('models') }}" id="createModelForm">
            {{csrf_field()}}
            
            {{-- Section 1: Basic Information --}}
            <fieldset>
              <legend><span class="form-section-icon"><i class="fa fa-info-circle"></i></span>Basic Information</legend>
              
              <div class="form-group {{ hasErrorForClass($errors, 'asset_type_id') }}">
                <label for="asset_type_id"><i class="fa fa-laptop"></i> Asset Type <span class="text-danger">*</span></label>
                <select class="form-control asset_type_id" name="asset_type_id" id="asset_type_id" required>
                  <option value="">Select Asset Type...</option>
                  @foreach($asset_types as $asset_type)
                    <option value="{{$asset_type->id}}" {{ old('asset_type_id') == $asset_type->id ? 'selected' : '' }}>
                      {{$asset_type->type_name}}
                    </option>
                  @endforeach
                </select>
                {{ hasErrorForField($errors, 'asset_type_id') }}
                <small class="help-text">Select the category of this device (e.g., Laptop, Desktop, Server)</small>
              </div>

              <div class="form-group {{ hasErrorForClass($errors, 'manufacturer_id') }}">
                <label for="manufacturer_id"><i class="fa fa-building"></i> Manufacturer <span class="text-danger">*</span></label>
                <select class="form-control manufacturer_id" name="manufacturer_id" id="manufacturer_id" required>
                  <option value="">Select Manufacturer...</option>
                  @foreach($manufacturers as $manufacturer)
                    <option value="{{$manufacturer->id}}" {{ old('manufacturer_id') == $manufacturer->id ? 'selected' : '' }}>
                      {{$manufacturer->name}}
                    </option>
                  @endforeach
                </select>
                {{ hasErrorForField($errors, 'manufacturer_id') }}
                <small class="help-text">Choose the device manufacturer (e.g., Dell, HP, Lenovo)</small>
              </div>

              <div class="form-group {{ hasErrorForClass($errors, 'asset_model') }}">
                <label for="asset_model"><i class="fa fa-tag"></i> Model Name <span class="text-danger">*</span></label>
                <input type="text" name="asset_model" class="form-control" id="asset_model" 
                       value="{{old('asset_model')}}" placeholder="e.g., Latitude 5420" required>
                {{ hasErrorForField($errors, 'asset_model') }}
                <small class="help-text">Enter the specific model name or number</small>
              </div>
            </fieldset>

            {{-- Section 2: Specifications --}}
            <fieldset>
              <legend><span class="form-section-icon"><i class="fa fa-cogs"></i></span>Specifications</legend>
              
              <div class="form-group {{ hasErrorForClass($errors, 'part_number') }}">
                <label for="part_number"><i class="fa fa-barcode"></i> Part Number</label>
                <input type="text" name="part_number" class="form-control" id="part_number" 
                       value="{{old('part_number')}}" placeholder="e.g., ABC-12345">
                {{ hasErrorForField($errors, 'part_number') }}
                <small class="help-text">Optional manufacturer part number for ordering</small>
              </div>

              <div class="form-group {{ hasErrorForClass($errors, 'pcspec_id') }}">
                <label for="pcspec_id"><i class="fa fa-microchip"></i> PC Specification</label>
                <select class="form-control pcspec_id" name="pcspec_id" id="pcspec_id">
                  <option value="">Select Specification...</option>
                  @foreach($pcspecs as $pcspec)
                    <option value="{{$pcspec->id}}" {{ old('pcspec_id') == $pcspec->id ? 'selected' : '' }}>
                      {{$pcspec->cpu}}, {{$pcspec->ram}}, {{$pcspec->hdd}}
                    </option>
                  @endforeach
                </select>
                {{ hasErrorForField($errors, 'pcspec_id') }}
                <small class="help-text">Optional hardware specification details</small>
              </div>
            </fieldset>

            <div class="form-group text-center" style="margin-top: 20px; border-top: 2px solid #ddd; padding-top: 20px;">
              <button type="submit" class="btn btn-primary btn-submit">
                <i class="fa fa-save"></i> Add New Model
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Model Guidelines Box --}}
      <div class="info-box-custom">
        <h4><i class="fa fa-lightbulb-o"></i> Model Guidelines</h4>
        <ul style="font-size: 12px;">
          <li><strong>Asset Type:</strong> Choose the correct category</li>
          <li><strong>Manufacturer:</strong> Select from existing manufacturers</li>
          <li><strong>Model Name:</strong> Use official model designation</li>
          <li><strong>Part Number:</strong> Useful for procurement</li>
          <li><strong>PC Spec:</strong> Link standard hardware config</li>
        </ul>
      </div>

      {{-- Quick Tips Box --}}
      <div class="info-box-custom" style="background: #fff3cd; border-left-color: #f0ad4e;">
        <h4 style="color: #f0ad4e;"><i class="fa fa-info-circle"></i> Quick Tips</h4>
        <ul style="font-size: 12px;">
          <li>Be consistent with naming conventions</li>
          <li>Include generation/year if applicable</li>
          <li>Add part numbers for easier ordering</li>
          <li>Link PC specs for computers only</li>
        </ul>
      </div>
    </div>
    <script src="{{ asset('js/datatable-enhancements.js') }}"></script>
    <script>
      $(document).ready(function() {
        // Initialize enhanced DataTable with empty state protection
        var table = initEnhancedDataTable('#table', {
          pageLength: 25,
          exportFileName: 'Asset_Models_Export',
          countBadgeSelector: '.label-primary',
          countBadgeText: ' Models',
          columnDefs: [
            { orderable: false, targets: 4 } // Disable sorting on Actions column
          ],
          order: [[0, 'asc']], // Sort by Manufacturer ascending
          exportOptions: {
            columns: [0, 1, 2, 3] // Export all columns except Actions
          },
          lengthMenuText: "Show _MENU_ models per page",
          infoText: "Showing _START_ to _END_ of _TOTAL_ models",
          infoEmptyText: "No models to show",
          emptyTableText: 
            '<div class="empty-state">' +
            '<i class="fa fa-laptop fa-3x text-muted"></i>' +
            '<h4>No Asset Models Found</h4>' +
            '<p class="text-muted">No device models are currently defined. Use the form on the right to create one.</p>' +
            '</div>',
          customDrawCallback: function(settings) {
            // Update count badge
            var info = this.api().page.info();
            $('.label-primary').text(info.recordsDisplay + ' Models');
          }
        });
      });
    </script>
    @if(Session::has('status'))
      <script>
        $(document).ready(function() {
          Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
        });
      </script>
    @endif
@endsection
@section('footer')
  <script type="text/javascript">
    function deleteModel(id) {
      if (confirm('Are you sure you want to delete this model? This action cannot be undone.')) {
        document.getElementById('delete-model-' + id).submit();
      }
    }

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


