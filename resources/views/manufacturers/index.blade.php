@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => $pageTitle ?? 'Manufacturers',
    'subtitle' => 'Manage hardware manufacturers',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Manufacturers']
    ]
])

<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

  <div class="row">
    <div class="col-md-9">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-industry"></i> Manufacturers</h3>
          <span class="count-badge">{{ count($manufacturers) }}</span>
        </div>
        <div class="box-body">
          <table id="table" class="table table-enhanced table-striped table-hover">
            <thead>
              <tr>
                <th>Manufacturer Name</th>
                <th style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($manufacturers as $manufacturer)
                <tr>
                    <td><strong>{{$manufacturer->name}}</strong></td>
                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                      <div class="btn-group btn-group-sm" role="group">
                          <a href="/manufacturers/{{ $manufacturer->id }}/edit" class="btn btn-sm btn-primary" title="Edit">
                            <i class='fa fa-edit'></i>
                          </a>
                          @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                              <button type="button" class="btn btn-sm btn-danger" onclick="deleteManufacturer({{ $manufacturer->id }})" title="Delete">
                                <i class="fa fa-trash"></i>
                              </button>
                          @endif
                      </div>
                      @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                          <form id="delete-manufacturer-{{ $manufacturer->id }}" method="POST" action="{{ route('manufacturers.destroy', $manufacturer->id) }}" style="display:none;">
                            @csrf
                            @method('DELETE')
                          </form>
                      @endif
                    </td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" class="text-center empty-state">
                    <i class="fa fa-industry fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                    <p>No manufacturers found.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <div class="col-md-3">
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-plus-circle"></i> Add Manufacturer</h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ url('manufacturers') }}" id="manufacturer-form">
            {{csrf_field()}}
            
            <fieldset>
              <legend><span class="form-section-icon"><i class="fa fa-industry"></i></span> Details</legend>
              
              <div class="form-group {{ hasErrorForClass($errors, 'name') }}">
                <label for="name">Manufacturer Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-industry"></i></span>
                  <input type="text" name="name" id="name" class="form-control" value="{{old('name')}}" placeholder="e.g., Dell, HP, Lenovo" required>
                </div>
                <small class="help-text">Enter the official manufacturer name</small>
                {{ hasErrorForField($errors, 'name') }}
              </div>
            </fieldset>

            <div class="form-group" style="margin-top: 20px;">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fa fa-save"></i> <b>Add Manufacturer</b>
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Guidelines --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb"></i> Guidelines</h3>
        </div>
        <div class="box-body">
          <ul style="margin-left: 20px;">
            <li><i class="fa fa-check text-success"></i> Use official company names</li>
            <li><i class="fa fa-check text-success"></i> Avoid abbreviations (use "Hewlett-Packard" not "HP")</li>
            <li><i class="fa fa-check text-success"></i> Check for duplicates first</li>
            <li><i class="fa fa-check text-success"></i> Include "Inc." or "Ltd." if part of official name</li>
          </ul>
          
          <p style="margin-top: 15px;"><strong>Common Examples:</strong></p>
          <ul style="margin-left: 20px; font-size: 12px;">
            <li>Dell Technologies</li>
            <li>HP Inc.</li>
            <li>Lenovo Group Limited</li>
            <li>Microsoft Corporation</li>
            <li>Cisco Systems</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/datatable-enhancements.js') }}"></script>
<script>
  // Delete manufacturer function
  function deleteManufacturer(id) {
      if (confirm('Are you sure you want to delete this manufacturer? This action cannot be undone.')) {
          document.getElementById('delete-manufacturer-' + id).submit();
      }
  }

  $(document).ready(function() {
    // Initialize enhanced DataTable with empty state protection
    var table = initEnhancedDataTable('#table', {
      pageLength: 25,
      exportFileName: 'Manufacturers_Export',
      countBadgeSelector: '.count-badge',
      countBadgeText: '',
      columnDefs: [
        { orderable: false, targets: 1 } // Disable sorting on Actions column
      ],
      order: [[0, 'asc']], // Sort by Manufacturer Name ascending
      exportOptions: {
        columns: [0] // Export only manufacturer name column
      },
      lengthMenuText: "Show _MENU_ manufacturers",
      infoText: "Showing _START_ to _END_ of _TOTAL_ manufacturers",
      infoEmptyText: "No manufacturers available",
      emptyTableText: 
        '<div class="empty-state">' +
        '<i class="fa fa-industry fa-3x text-muted"></i>' +
        '<h4>No Manufacturers Found</h4>' +
        '<p class="text-muted">No hardware manufacturers are currently defined. Use the form on the right to add one.</p>' +
        '</div>',
      customDrawCallback: function(settings) {
        // Update count badge
        var info = this.api().page.info();
        $('.count-badge').text(info.recordsDisplay);
      }
    });

    // Form validation
    $('#manufacturer-form').on('submit', function(e) {
      if ($('#name').val().trim() === '') {
        alert('Please enter the manufacturer name');
        $('#name').focus();
        return false;
      }
      return true;
    });

    // Auto-dismiss alerts
    setTimeout(function() {
      $('.alert').fadeOut('slow');
    }, 5000);
  });

  @if(Session::has('status'))
    $(document).ready(function() {
      Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
    });
  @endif
</script>
@endpush