@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => $pageTitle ?? 'Divisions',
    'subtitle' => 'Manage organizational divisions',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Divisions']
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
                    <h3 class="box-title"><i class="fa fa-sitemap"></i> Divisions</h3>
                    <span class="count-badge">{{ count($divisions) }}</span>
                </div>
                <div class="box-body">
                    <table id="table" class="table table-enhanced table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Division Name</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($divisions as $division)
                                <tr>
                                    <td><strong>{{ $division->name }}</strong></td>
                                    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/divisions/{{ $division->id }}/edit" class="btn btn-sm btn-primary" title="Edit">
                                                <i class='fa fa-edit'></i>
                                            </a>
                                            @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteDivision({{ $division->id }})" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                                            <form id="delete-division-{{ $division->id }}" method="POST" action="{{ route('divisions.destroy', $division->id) }}" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center empty-state">
                                        <i class="fa fa-sitemap fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                                        <p>No divisions found.</p>
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
                    <h3 class="box-title"><i class="fa fa-plus-circle"></i> Add Division</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ url('divisions') }}" id="division-form">
                        {{csrf_field()}}
                        
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-sitemap"></i></span> Details</legend>
                            
                            <div class="form-group {{ hasErrorForClass($errors, 'name') }}">
                                <label for="name">Division Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                                    <input type="text" name="name" id="name" class="form-control" value="{{old('name')}}" placeholder="e.g., IT Department" required>
                                </div>
                                <small class="help-text">Enter the official division/department name</small>
                                {{ hasErrorForField($errors, 'name') }}
                            </div>
                        </fieldset>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> <b>Add Division</b>
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
                        <li><i class="fa fa-check text-success"></i> Use clear, descriptive names</li>
                        <li><i class="fa fa-check text-success"></i> Match your organizational structure</li>
                        <li><i class="fa fa-check text-success"></i> Check for duplicates first</li>
                        <li><i class="fa fa-check text-success"></i> Use consistent naming conventions</li>
                    </ul>
                    
                    <p style="margin-top: 15px;"><strong>Common Examples:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li>IT Department</li>
                        <li>Human Resources</li>
                        <li>Finance & Accounting</li>
                        <li>Operations</li>
                        <li>Sales & Marketing</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Delete division function
    function deleteDivision(id) {
        if (confirm('Are you sure you want to delete this division? This action cannot be undone.')) {
            document.getElementById('delete-division-' + id).submit();
        }
    }

    $(document).ready(function() {
        @if(count($divisions) > 0)
        // Enhanced DataTable with export buttons
        var table = $('#table').DataTable({
            responsive: true,
            dom: 'lfrtip', // Remove 'B' to prevent duplicate buttons
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: { columns: [0] }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-csv"></i> CSV',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: [0] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: { columns: [0] }
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i> Copy',
                    className: 'btn btn-default btn-sm',
                    exportOptions: { columns: [0] }
                }
            ],
            columnDefs: [{ orderable: false, targets: 1 }],
            order: [[0, "asc"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search divisions...",
                lengthMenu: "Show _MENU_ divisions",
                info: "Showing _START_ to _END_ of _TOTAL_ divisions",
                paginate: {
                    first: '<i class="fa fa-angle-double-left"></i>',
                    last: '<i class="fa fa-angle-double-right"></i>',
                    next: '<i class="fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i>'
                }
            }
        });

        // Move export buttons to header
        table.buttons().container().appendTo($('.box-header .box-title').parent());
        @endif

        // Form validation
        $('#division-form').on('submit', function(e) {
            if ($('#name').val().trim() === '') {
                alert('Please enter the division name');
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


