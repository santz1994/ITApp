@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit Division',
    'subtitle' => 'Update division information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Divisions', 'url' => url('divisions')],
        ['label' => 'Edit']
    ]
])

<div class="container-fluid">
    <div class="row">
        {{-- Main Content --}}
        <div class="col-md-8">
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-ban"></i> Validation Error!</h4>
                    <ul style="margin-bottom: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Division Metadata --}}
            <div class="alert alert-warning">
                <i class="fa fa-info-circle"></i> <strong>Division Information</strong>
                <div style="margin-top: 10px;">
                    <span style="margin-right: 20px;"><strong>Created:</strong> {{ $division->created_at ? $division->created_at->format('M d, Y') : 'N/A' }}</span>
                    <span><strong>Updated:</strong> {{ $division->updated_at ? $division->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>

            {{-- Edit Form --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Edit Division Details</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="/divisions/{{$division->id}}" id="division-form">
                        {{method_field('PATCH')}}
                        {{csrf_field()}}
                        
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-sitemap"></i></span> Division Information</legend>
                            
                            <div class="form-group {{ hasErrorForClass($errors, 'name') }}">
                                <label for="name">Division Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $division->name) }}" placeholder="e.g., IT Department" required>
                                </div>
                                <small class="help-text">Use the official division/department name</small>
                                {{ hasErrorForField($errors, 'name') }}
                            </div>
                        </fieldset>

                        <div class="form-actions" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> <b>Update Division</b>
                            </button>
                            <a href="{{ url('divisions') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> Cancel
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
                <div class="box-body">
                    <p><i class="fa fa-info-circle text-info"></i> <strong>Impact Warning:</strong></p>
                    <p style="font-size: 13px;">Changing the division name will affect all users, assets, and budgets assigned to this division.</p>
                    
                    <hr>
                    
                    <ul style="margin-left: 20px; font-size: 13px;">
                        <li>Double-check spelling before saving</li>
                        <li>Use official department names</li>
                        <li>Maintain consistency with organizational structure</li>
                        <li>Avoid abbreviations when possible</li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('divisions') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    @if(Route::has('divisions.show'))
                    <a href="{{ route('divisions.show', $division->id) }}" class="btn btn-primary btn-block">
                        <i class="fa fa-eye"></i> View Division Details
                    </a>
                    @endif
                </div>
            </div>

            {{-- Best Practices --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check-circle"></i> Best Practices</h3>
                </div>
                <div class="box-body">
                    <ul style="margin-left: 20px; font-size: 13px;">
                        <li><i class="fa fa-check text-success"></i> Use title case (IT Department, not IT DEPARTMENT)</li>
                        <li><i class="fa fa-check text-success"></i> Be specific and descriptive</li>
                        <li><i class="fa fa-check text-success"></i> Check for existing duplicates</li>
                        <li><i class="fa fa-check text-success"></i> Align with organizational chart</li>
                    </ul>
                    
                    <p style="margin-top: 10px; font-size: 12px;"><strong>Examples:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li>IT Department</li>
                        <li>Human Resources</li>
                        <li>Finance & Accounting</li>
                        <li>Operations</li>
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
</script>
@endpush


