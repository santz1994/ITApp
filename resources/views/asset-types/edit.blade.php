@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => $pageTitle ?? 'Edit Asset Type',
    'subtitle' => 'Modify asset type information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Asset Types', 'url' => url('asset-types')],
        ['label' => 'Edit']
    ]
])

<div class="container-fluid">

    {{-- Metadata Alert --}}
    @if($asset_type->created_at)
    <div class="alert metadata-alert">
        <i class="fa fa-info-circle"></i> <strong>Asset Type:</strong> {{ $asset_type->type_name }}
        <span class="pull-right">
            <small>Created: {{ $asset_type->created_at->format('d M Y') }}
            @if($asset_type->updated_at && $asset_type->updated_at != $asset_type->created_at)
                | Updated: {{ $asset_type->updated_at->format('d M Y') }}
            @endif
            </small>
        </span>
    </div>
    @endif

  <div class="row">
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-edit"></i> Edit Asset Type</h3>
        </div>
        <div class="box-body">
            <form method="POST" action="/asset-types/{{$asset_type->id}}" id="edit-form">
              {{method_field('PATCH')}}
              {{csrf_field()}}
              
              <fieldset>
                <legend><span class="form-section-icon"><i class="fa fa-tag"></i></span> Type Information</legend>
                
                <div class="form-group {{ hasErrorForClass($errors, 'type_name') }}">
                  <label for="type_name">Asset Type Name <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <input type="text" name="type_name" id="type_name" class="form-control" value="{{$asset_type->type_name}}" required>
                  </div>
                  <small class="help-text">The asset type category name</small>
                  {{ hasErrorForField($errors, 'type_name') }}
                </div>
                
                <div class="form-group {{ hasErrorForClass($errors, 'abbreviation') }}">
                  <label for="abbreviation">Abbreviation <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-font"></i></span>
                    <input type="text" name="abbreviation" id="abbreviation" class="form-control" value="{{$asset_type->abbreviation}}" maxlength="10" required>
                  </div>
                  <small class="help-text">Short code (max 10 characters)</small>
                  {{ hasErrorForField($errors, 'abbreviation') }}
                </div>
                
                <div class="form-group {{ hasErrorForClass($errors, 'spare') }}">
                  <label for="spare">Track Spare Level?</label>
                  <select class="form-control spare" name="spare" id="spare">
                    <option value="0" {{ $asset_type->spare == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $asset_type->spare == 1 ? 'selected' : '' }}>Yes</option>
                  </select>
                  <small class="help-text">Enable inventory tracking for spare parts</small>
                  {{ hasErrorForField($errors, 'spare') }}
                </div>
              </fieldset>

              <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="fa fa-save"></i> <b>Update Asset Type</b>
                </button>
                <a href="{{ url('asset-types') }}" class="btn btn-default btn-lg">
                  <i class="fa fa-times"></i> Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Edit Tips</h3>
        </div>
        <div class="box-body">
          <ul style="margin-left: 20px;">
            <li><i class="fa fa-exclamation-triangle text-warning"></i> Changing the name affects all related assets</li>
            <li><i class="fa fa-info-circle text-info"></i> Abbreviation is used in asset tags</li>
            <li><i class="fa fa-check text-success"></i> Spare tracking can be enabled anytime</li>
          </ul>
        </div>
      </div>

      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="box-body">
          <a href="{{ url('asset-types') }}" class="btn btn-default btn-block">
            <i class="fa fa-list"></i> Back to List
          </a>
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
  $('#edit-form').on('submit', function(e) {
    if ($('#type_name').val().trim() === '') {
      alert('Please enter the asset type name');
      $('#type_name').focus();
      return false;
    }
    if ($('#abbreviation').val().trim() === '') {
      alert('Please enter an abbreviation');
      $('#abbreviation').focus();
      return false;
    }
    return true;
  });
});
</script>
@endpush


