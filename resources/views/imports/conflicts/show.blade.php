@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Conflict Detail',
    'subtitle' => 'Review and resolve this conflict',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Master Data', 'url' => route('masterdata.index')],
        ['label' => 'Imports', 'url' => route('masterdata.imports')],
        ['label' => 'Conflicts', 'url' => route('imports.conflicts.index', $import->import_id)],
        ['label' => 'Detail']
    ],
    'actions' => '
        <a href="'.route('imports.conflicts.index', $import->import_id).'" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
        </a>
    '
])

<div class="row">
    <div class="col-md-8">
        <!-- Conflict Information -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-circle"></i> Conflict Information
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Import ID:</dt>
                            <dd><code>{{ $import->import_id }}</code></dd>
                            
                            <dt>Row Number:</dt>
                            <dd>{{ $conflict->row_number }}</dd>
                            
                            <dt>Conflict Type:</dt>
                            <dd>
                                <span class="badge" style="background-color: @switch($conflict->conflict_type)
                                    @case('duplicate_key') #DD4B39 @break
                                    @case('duplicate_record') #F39C12 @break
                                    @case('foreign_key_not_found') #3498DB @break
                                    @case('invalid_data') #9B59B6 @break
                                    @case('business_rule_violation') #E74C3C @break
                                    @default #95A5A6
                                @endswitch">
                                    {{ $conflict->getConflictTypeLabel() }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Status:</dt>
                            <dd>
                                @if($conflict->isResolved())
                                    <span class="label label-success">Resolved</span>
                                @else
                                    <span class="label label-danger">Unresolved</span>
                                @endif
                            </dd>
                            
                            <dt>Suggested Resolution:</dt>
                            <dd>
                                <span class="label label-info">
                                    {{ ucfirst(str_replace('_', ' ', $conflict->suggested_resolution)) }}
                                </span>
                            </dd>
                            
                            <dt>Created At:</dt>
                            <dd>{{ $conflict->created_at->format('M d, Y h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Record Data -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-file-o"></i> New Record Data
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                @if($conflict->new_record_data)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="30%">Field</th>
                                    <th width="70%">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($conflict->new_record_data as $field => $value)
                                    <tr>
                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong></td>
                                        <td>
                                            @if(is_array($value))
                                                <code>{{ json_encode($value) }}</code>
                                            @elseif(is_null($value))
                                                <span class="text-muted">NULL</span>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No record data available</p>
                @endif
            </div>
        </div>

        <!-- Existing Record Information -->
        @if($conflict->existing_record_id)
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-database"></i> Existing Record (#{{ $conflict->existing_record_id }})
                    </h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">Existing record ID: <strong>{{ $conflict->existing_record_id }}</strong></p>
                    <p class="text-muted" style="font-size: 12px;">The imported data conflicts with this existing record in the system.</p>
                </div>
            </div>
        @endif

        <!-- Related Conflicts -->
        @if($relatedConflicts->count() > 0)
            <div class="box box-secondary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-link"></i> Similar Conflicts ({{ $relatedConflicts->count() }})
                    </h3>
                </div>
                <div class="box-body">
                    <div class="list-group">
                        @foreach($relatedConflicts as $related)
                            <a href="{{ route('imports.conflicts.show', [$import->import_id, $related->id]) }}" 
                               class="list-group-item">
                                <h4 class="list-group-item-heading">
                                    Row {{ $related->row_number }}
                                    @if($related->id === $conflict->id)
                                        <span class="label label-primary">Current</span>
                                    @endif
                                </h4>
                                <p class="list-group-item-text">
                                    {{ $related->getConflictTypeLabel() }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Resolution Panel -->
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-check-circle"></i> Resolve Conflict
                </h3>
            </div>
            <div class="box-body">
                @if($conflict->isResolved())
                    <div class="alert alert-success">
                        <strong>Already Resolved!</strong><br>
                        Resolution: <strong>{{ $conflict->getResolutionLabel() }}</strong><br>
                        Resolved At: <strong>{{ $conflict->updated_at->format('M d, Y h:i A') }}</strong>
                    </div>
                @else
                    <form id="resolution-form">
                        {{ csrf_field() }}
                        
                        <div class="form-group">
                            <label for="resolution">Select Resolution Type:</label>
                            <select id="resolution" name="resolution" class="form-control" required>
                                <option value="">-- Choose Resolution --</option>
                                <option value="skip" @if($conflict->suggested_resolution === 'skip') selected @endif>
                                    <i class="fa fa-forward"></i> Skip Row
                                </option>
                                <option value="create_new" @if($conflict->suggested_resolution === 'create_new') selected @endif>
                                    <i class="fa fa-plus"></i> Create New Record
                                </option>
                                <option value="update_existing" @if($conflict->suggested_resolution === 'update_existing') selected @endif>
                                    <i class="fa fa-pencil"></i> Update Existing
                                </option>
                                <option value="merge" @if($conflict->suggested_resolution === 'merge') selected @endif>
                                    <i class="fa fa-compress"></i> Merge Records
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Suggested:</strong> {{ $conflict->getResolutionLabel() }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="details">Additional Notes (Optional):</label>
                            <textarea id="details" name="details" class="form-control" rows="3" 
                                      placeholder="Enter any additional information about this resolution..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-check"></i> Resolve This Conflict
                        </button>
                    </form>
                @endif

                <!-- Suggested Resolutions Guide -->
                <hr>
                <h5><i class="fa fa-lightbulb-o"></i> Resolution Guide</h5>
                <div style="font-size: 12px;">
                    <div class="form-group">
                        <strong class="text-info">Skip Row</strong>
                        <p class="text-muted">Don't import this row. Use when the data is invalid or unwanted.</p>
                    </div>
                    <div class="form-group">
                        <strong class="text-success">Create New</strong>
                        <p class="text-muted">Create a new record with the imported data instead of updating existing.</p>
                    </div>
                    <div class="form-group">
                        <strong class="text-warning">Update Existing</strong>
                        <p class="text-muted">Update the existing record with the new imported data.</p>
                    </div>
                    <div class="form-group">
                        <strong class="text-primary">Merge</strong>
                        <p class="text-muted">Intelligently merge existing and new data, keeping the best of both.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#resolution-form').on('submit', function(e) {
        e.preventDefault();

        const resolution = $('#resolution').val();
        const details = $('#details').val();

        if (!resolution) {
            toastr.error('Please select a resolution type', 'Error');
            return;
        }

        $.ajax({
            url: '{{ route("imports.conflicts.resolve", [$import->import_id, $conflict->id]) }}',
            method: 'POST',
            data: {
                resolution: resolution,
                details: details,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('Conflict resolved successfully', 'Success');
                setTimeout(function() {
                    window.location.href = '{{ route("imports.conflicts.index", $import->import_id) }}';
                }, 1500);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to resolve conflict';
                toastr.error(error, 'Error');
            }
        });
    });
});
</script>
@endsection
