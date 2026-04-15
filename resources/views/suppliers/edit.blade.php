@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

@include('components.page-header', [
    'title' => 'Edit Supplier',
    'subtitle' => 'Update supplier information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
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

  {{-- Supplier Metadata --}}
  @if($supplier->created_at)
    <div class="alert alert-info metadata-alert">
      <strong><i class="fa fa-info-circle"></i> Supplier Info:</strong>
      Created on {{ $supplier->created_at->format('M d, Y \a\t h:i A') }}
      @if($supplier->updated_at && $supplier->updated_at != $supplier->created_at)
        | Last updated on {{ $supplier->updated_at->format('M d, Y \a\t h:i A') }}
      @endif
    </div>
  @endif

  <div class="row">
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-edit"></i> Edit Supplier Details</h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" id="edit-supplier-form">
            @method('PATCH')
            @csrf

            <fieldset>
              <legend>
                <span class="form-section-icon"><i class="fa fa-truck"></i></span>
                Supplier Information
              </legend>

              {{-- Supplier Name Field --}}
              <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">
                  Supplier Name <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-building"></i></span>
                  <input type="text" 
                         id="name" 
                         name="name" 
                         class="form-control" 
                         value="{{ old('name', $supplier->name) }}"
                         placeholder="e.g., Dell Technologies, HP Inc."
                         required>
                </div>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> Enter the full legal or trading name of the supplier
                </small>
                @error('name')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>
            </fieldset>

            {{-- Action Buttons --}}
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-lg btn-submit">
                <i class="fa fa-save"></i> Update Supplier
              </button>
              <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-lg">
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
            <li><i class="fa fa-warning text-warning"></i> <strong>Impact:</strong> Changing this supplier may affect purchase orders and assets</li>
            <li><i class="fa fa-box text-info"></i> Existing purchase orders and assets will remain linked</li>
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
          <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-block">
            <i class="fa fa-list"></i> Back to All Suppliers
          </a>
          @if(isset($supplier->id))
            <a href="{{ route('assets.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary btn-block">
              <i class="fa fa-desktop"></i> View Assets from This Supplier
            </a>
          @endif
        </div>
      </div>

      {{-- Supplier Guidelines --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Best Practices</h3>
        </div>
        <div class="box-body info-box-custom">
          <ul>
            <li><i class="fa fa-check text-success"></i> Use official company names</li>
            <li><i class="fa fa-check text-success"></i> Avoid abbreviations</li>
            <li><i class="fa fa-check text-success"></i> Keep naming consistent</li>
            <li><i class="fa fa-check text-success"></i> Check for duplicates</li>
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
  $('#edit-supplier-form').on('submit', function(e) {
    var supplierName = $('#name').val().trim();

    if (supplierName === '') {
      e.preventDefault();
      alert('Supplier name is required!');
      return false;
    }

    if (supplierName.length < 2) {
      e.preventDefault();
      alert('Supplier name must be at least 2 characters long!');
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


