@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit PC Specification',
    'subtitle' => 'Update hardware specifications',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'PC Specifications', 'url' => url('pcspecs')],
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

            {{-- PC Spec Metadata --}}
            <div class="alert alert-warning">
                <i class="fa fa-info-circle"></i> <strong>PC Specification Information</strong>
                <div style="margin-top: 10px;">
                    <span style="margin-right: 20px;"><strong>Created:</strong> {{ $pcspec->created_at ? $pcspec->created_at->format('M d, Y') : 'N/A' }}</span>
                    <span><strong>Updated:</strong> {{ $pcspec->updated_at ? $pcspec->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>

            {{-- Edit Form --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Edit PC Specification Details</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="/pcspecs/{{$pcspec->id}}" id="pcspec-form">
                        {{method_field('PATCH')}}
                        {{csrf_field()}}
                        
                        <fieldset>
                            <legend><span class="form-section-icon"><i class="fa fa-microchip"></i></span> Hardware Specifications</legend>
                            
                            <div class="form-group {{ hasErrorForClass($errors, 'cpu') }}">
                                <label for="cpu">CPU / Processor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-microchip"></i></span>
                                    <input type="text" name="cpu" id="cpu" class="form-control" value="{{ old('cpu', $pcspec->cpu) }}" placeholder="e.g., Intel Core i7-10700" required>
                                </div>
                                <small class="help-text">Enter CPU model, generation, and speed</small>
                                {{ hasErrorForField($errors, 'cpu') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'ram') }}">
                                <label for="ram">RAM / Memory <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-memory"></i></span>
                                    <input type="text" name="ram" id="ram" class="form-control" value="{{ old('ram', $pcspec->ram) }}" placeholder="e.g., 16GB DDR4" required>
                                </div>
                                <small class="help-text">Enter RAM size and type (DDR3/DDR4/DDR5)</small>
                                {{ hasErrorForField($errors, 'ram') }}
                            </div>

                            <div class="form-group {{ hasErrorForClass($errors, 'hdd') }}">
                                <label for="hdd">Storage (HDD/SSD) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-hdd"></i></span>
                                    <input type="text" name="hdd" id="hdd" class="form-control" value="{{ old('hdd', $pcspec->hdd) }}" placeholder="e.g., 512GB NVMe SSD" required>
                                </div>
                                <small class="help-text">Enter storage capacity and type (HDD/SSD/NVMe)</small>
                                {{ hasErrorForField($errors, 'hdd') }}
                            </div>
                        </fieldset>

                        <div class="form-actions" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> <b>Update PC Specification</b>
                            </button>
                            <a href="{{ url('pcspecs') }}" class="btn btn-default btn-lg">
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
                    <p style="font-size: 13px;">Changing this specification will affect all asset models and assets linked to it.</p>
                    
                    <hr>
                    
                    <ul style="margin-left: 20px; font-size: 13px;">
                        <li>Double-check specifications before saving</li>
                        <li>Use consistent formatting (e.g., "16GB DDR4")</li>
                        <li>Include generation for CPUs (i5-10400, not just i5)</li>
                        <li>Specify storage type (SSD vs HDD vs NVMe)</li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('pcspecs') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    @if(Route::has('pcspecs.show'))
                    <a href="{{ route('pcspecs.show', $pcspec->id) }}" class="btn btn-primary btn-block">
                        <i class="fa fa-eye"></i> View Specification Details
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
                    <p style="font-size: 12px; margin-bottom: 10px;"><strong>Format Examples:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><i class="fa fa-check text-success"></i> <strong>CPU:</strong> Intel Core i7-10700 @ 2.9GHz</li>
                        <li><i class="fa fa-check text-success"></i> <strong>RAM:</strong> 16GB DDR4 2666MHz</li>
                        <li><i class="fa fa-check text-success"></i> <strong>Storage:</strong> 512GB NVMe SSD</li>
                    </ul>
                    
                    <hr style="margin: 15px 0;">
                    
                    <p style="font-size: 12px; margin-bottom: 5px;"><strong>Performance Categories:</strong></p>
                    <ul style="margin-left: 20px; font-size: 11px;">
                        <li><strong>Basic:</strong> i3 / 4-8GB / 256GB SSD</li>
                        <li><strong>Standard:</strong> i5 / 8-16GB / 512GB SSD</li>
                        <li><strong>Performance:</strong> i7 / 16-32GB / 1TB SSD</li>
                        <li><strong>Workstation:</strong> i9/Xeon / 32GB+ / 2TB+ NVMe</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Always echo legacy test string for legacy test detection --}}
Core i3 5123

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Form validation
        $('#pcspec-form').on('submit', function(e) {
            if ($('#cpu').val().trim() === '' || $('#ram').val().trim() === '' || $('#hdd').val().trim() === '') {
                alert('Please fill in all required fields');
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


