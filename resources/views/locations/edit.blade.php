@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

@include('components.page-header', [
    'title' => 'Edit Location',
    'subtitle' => 'Update location information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Locations', 'url' => route('locations.index')],
        ['label' => 'Edit']
    ]
])

<div class="container-fluid">
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
      <h4><i class="icon fa fa-warning"></i> Please correct the following errors:</h4>
      <ul style="margin-bottom: 0;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Location Metadata --}}
  @if($location->created_at)
    <div class="alert alert-info metadata-alert">
      <strong><i class="fa fa-info-circle"></i> Location Info:</strong>
      Created on {{ $location->created_at->format('M d, Y \a\t h:i A') }}
      @if($location->updated_at && $location->updated_at != $location->created_at)
        | Last updated on {{ $location->updated_at->format('M d, Y \a\t h:i A') }}
      @endif
    </div>
  @endif

  <div class="row">
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-edit"></i> Edit Location Details</h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ route('locations.update', $location->id) }}" id="edit-location-form">
            @method('PATCH')
            @csrf

            <fieldset>
              <legend>
                <span class="form-section-icon"><i class="fa fa-map-marker"></i></span>
                Location Information
              </legend>

              {{-- Building Field --}}
              <div class="form-group {{ $errors->has('building') ? 'has-error' : '' }}">
                <label for="building">
                  Building <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-building"></i></span>
                  <input type="text" 
                         id="building" 
                         name="building" 
                         class="form-control" 
                         value="{{ old('building', $location->building) }}"
                         placeholder="e.g., Tower A, Main Building"
                         required>
                </div>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> Enter the building name or identifier
                </small>
                @error('building')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>

              {{-- Office Field --}}
              <div class="form-group {{ $errors->has('office') ? 'has-error' : '' }}">
                <label for="office">
                  Office <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-door-open"></i></span>
                  <input type="text" 
                         id="office" 
                         name="office" 
                         class="form-control" 
                         value="{{ old('office', $location->office) }}"
                         placeholder="e.g., Floor 3, Room 301"
                         required>
                </div>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> Specify the floor or room number
                </small>
                @error('office')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>

              {{-- Location Name Field --}}
              <div class="form-group {{ $errors->has('location_name') ? 'has-error' : '' }}">
                <label for="location_name">
                  Location Name <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-map-marker-alt"></i></span>
                  <input type="text" 
                         id="location_name" 
                         name="location_name" 
                         class="form-control" 
                         value="{{ old('location_name', $location->location_name) }}"
                         placeholder="e.g., IT Department, HR Office"
                         required>
                </div>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> Full descriptive name for this location
                </small>
                @error('location_name')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>
            </fieldset>

            {{-- Action Buttons --}}
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-lg btn-submit">
                <i class="fa fa-save"></i> Update Location
              </button>
              <a href="{{ route('locations.index') }}" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
      {{-- Edit Tips --}}
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Edit Tips</h3>
        </div>
        <div class="box-body info-box-custom">
          <ul>
            <li><i class="fa fa-warning text-warning"></i> <strong>Impact:</strong> Changing this location may affect assets and tickets assigned to it</li>
            <li><i class="fa fa-users text-info"></i> Assets and tickets will remain linked to this location</li>
            <li><i class="fa fa-check text-success"></i> All changes are logged for audit purposes</li>
            <li><i class="fa fa-history text-muted"></i> You can view the change history after saving</li>
          </ul>
        </div>
      </div>

      {{-- Quick Actions --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="box-body">
          <a href="{{ route('locations.index') }}" class="btn btn-default btn-block">
            <i class="fa fa-list"></i> Back to All Locations
          </a>
          <a href="{{ route('assets.index', ['location_id' => $location->id]) }}" class="btn btn-primary btn-block">
            <i class="fa fa-desktop"></i> View Assets at This Location
          </a>
        </div>
      </div>

      {{-- Location Guidelines --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Best Practices</h3>
        </div>
        <div class="box-body info-box-custom">
          <ul>
            <li><i class="fa fa-check text-success"></i> Keep naming consistent</li>
            <li><i class="fa fa-check text-success"></i> Include building and floor</li>
            <li><i class="fa fa-check text-success"></i> Make names searchable</li>
            <li><i class="fa fa-check text-success"></i> Avoid special characters</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // Form validation
  $('#edit-location-form').on('submit', function(e) {
    var building = $('#building').val().trim();
    var office = $('#office').val().trim();
    var locationName = $('#location_name').val().trim();

    if (building === '' || office === '' || locationName === '') {
      e.preventDefault();
      alert('All fields are required!');
      return false;
    }
  });

  // Auto-dismiss alerts after 5 seconds
  setTimeout(function() {
    $('.alert-dismissible').fadeOut('slow');
  }, 5000);
});
</script>
@endpush


