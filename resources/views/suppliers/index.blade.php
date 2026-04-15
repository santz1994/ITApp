@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

@include('components.page-header', [
    'title' => 'Suppliers',
    'subtitle' => 'Manage vendors and suppliers',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Suppliers']
    ]
])

<div class="container-fluid">
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
      <h4><i class="icon fa fa-warning"></i> Please correct the following errors:</h4>
      <ul style="margin-bottom: 0;">
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
          <h3 class="box-title">
            <i class="fa fa-truck"></i> All Suppliers 
            <span class="badge bg-blue count-badge">{{ count($suppliers) }}</span>
          </h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
              <i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body">
          <table id="table" class="table table-striped table-bordered table-hover table-enhanced">
            <thead>
              <tr>
                <th style="width: 60px;"><i class="fa fa-hashtag"></i> ID</th>
                <th><i class="fa fa-building"></i> Supplier Name</th>
                <th style="width: 150px;"><i class="fa fa-calendar"></i> Created Date</th>
                <th style="width: 100px;"><i class="fa fa-cogs"></i> Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($suppliers as $supplier)
                <tr>
                  <td class="text-center">{{ $supplier->id }}</td>
                  <td><strong>{{ $supplier->name }}</strong></td>
                  <td>{{ $supplier->created_at ? $supplier->created_at->format('M d, Y') : '-' }}</td>
                  <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-primary" title="Edit">
                          <i class="fa fa-pencil"></i>
                        </a>
                        @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSupplier({{ $supplier->id }})" title="Delete">
                              <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    </div>
                    @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                        <form id="delete-supplier-{{ $supplier->id }}" method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" style="display:none;">
                          @csrf
                          @method('DELETE')
                        </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center empty-state">
                    <i class="fa fa-info-circle"></i> No suppliers found. Create one using the form on the right.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-plus-circle"></i> Create New Supplier</h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ url('suppliers') }}" id="create-supplier-form">
            @csrf

            <fieldset>
              <legend>
                <span class="form-section-icon"><i class="fa fa-info-circle"></i></span>
                Supplier Details
              </legend>

              {{-- Supplier Name Field --}}
              <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">
                  Supplier Name <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control" 
                       value="{{ old('name') }}"
                       placeholder="e.g., Dell Technologies, HP Inc."
                       required>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> Enter the full legal or trading name of the supplier
                </small>
                @error('name')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>
            </fieldset>

            {{-- Submit Button --}}
            <div class="form-group" style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-block btn-submit">
                <i class="fa fa-plus-circle"></i> Add New Supplier
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Help & Tips Sidebar --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Supplier Guidelines</h3>
        </div>
        <div class="box-body info-box-custom">
          <h4><i class="fa fa-info-circle"></i> Best Practices</h4>
          <ul>
            <li><i class="fa fa-check text-success"></i> Use official company names</li>
            <li><i class="fa fa-check text-success"></i> Avoid abbreviations unless standard</li>
            <li><i class="fa fa-check text-success"></i> Check for duplicates before adding</li>
            <li><i class="fa fa-check text-success"></i> Keep naming consistent</li>
          </ul>

          <h4 style="margin-top: 15px;"><i class="fa fa-list"></i> Common Suppliers</h4>
          <ul>
            <li><i class="fa fa-building text-info"></i> Dell Technologies</li>
            <li><i class="fa fa-building text-info"></i> HP Inc.</li>
            <li><i class="fa fa-building text-info"></i> Lenovo</li>
            <li><i class="fa fa-building text-info"></i> Microsoft</li>
            <li><i class="fa fa-building text-info"></i> Cisco Systems</li>
          </ul>
        </div>
      </div>

      {{-- Supplier Stats --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bar-chart"></i> Quick Stats</h3>
        </div>
        <div class="box-body">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-truck"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Suppliers</span>
              <span class="info-box-number">{{ count($suppliers) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    // Delete supplier function
    function deleteSupplier(id) {
        if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
            document.getElementById('delete-supplier-' + id).submit();
        }
    }

    $(document).ready(function() {
  // Initialize DataTable
  var table = $('#table').DataTable({
    pageLength: 25,
    order: [[1, 'asc']], // Sort by Supplier Name ascending
    columnDefs: [
      { orderable: false, targets: -1 } // Disable sorting on last column (Actions)
    ],
    language: {
      lengthMenu: "Show _MENU_ suppliers per page",
      info: "Showing _START_ to _END_ of _TOTAL_ suppliers",
      infoEmpty: "No suppliers available",
      emptyTable: "No suppliers found. Use the form on the right to add one.",
      search: "Search suppliers:",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous"
      }
    },
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'copy',
        text: '<i class="fa fa-copy"></i> Copy',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'csv',
        text: '<i class="fa fa-file-text-o"></i> CSV',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'excel',
        text: '<i class="fa fa-file-excel-o"></i> Excel',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'pdf',
        text: '<i class="fa fa-file-pdf-o"></i> PDF',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'print',
        text: '<i class="fa fa-print"></i> Print',
        exportOptions: { columns: ':not(:last-child)' }
      }
    ]
  });

  // Update count badge
  table.on('draw', function() {
    var info = table.page.info();
    $('.count-badge').text(info.recordsDisplay);
  });

  // Form validation
  $('#create-supplier-form').on('submit', function(e) {
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


