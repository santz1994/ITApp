@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'Import Conflicts',
    'subtitle' => 'Manage and resolve conflicts from data imports',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Master Data', 'url' => route('masterdata.index')],
        ['label' => 'Imports', 'url' => route('masterdata.imports')],
        ['label' => 'Conflicts']
    ],
    'actions' => '
        <a href="'.route('masterdata.imports').'" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> <span class="hidden-xs">Back</span>
        </a>
    '
])

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Conflicts</span>
                <span class="info-box-number">{{ $statistics['total_conflicts'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-orange"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Unresolved</span>
                <span class="info-box-number">{{ $statistics['unresolved_conflicts'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Resolved</span>
                <span class="info-box-number">{{ $statistics['resolved_conflicts'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Resolution Rate</span>
                <span class="info-box-number">{{ $statistics['resolution_rate'] }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list"></i> Conflicts by Type
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    @forelse($statistics['by_type'] as $type => $data)
                        <div class="col-md-4 col-sm-6">
                            <div class="small-box" style="background-color: @switch($type)
                                @case('duplicate_key') #DD4B39 @break
                                @case('duplicate_record') #F39C12 @break
                                @case('foreign_key_not_found') #3498DB @break
                                @case('invalid_data') #9B59B6 @break
                                @case('business_rule_violation') #E74C3C @break
                                @default #95A5A6
                            @endswitch">
                                <div class="inner">
                                    <h3>{{ $data['count'] }}</h3>
                                    <p>{{ ucfirst(str_replace('_', ' ', $type)) }}</p>
                                </div>
                                <div class="icon">
                                    <i class="fa @switch($type)
                                        @case('duplicate_key') fa-key @break
                                        @case('duplicate_record') fa-clone @break
                                        @case('foreign_key_not_found') fa-unlink @break
                                        @case('invalid_data') fa-times-circle @break
                                        @case('business_rule_violation') fa-gavel @break
                                        @default fa-exclamation
                                    @endswitch"></i>
                                </div>
                                <a href="#{{ $type }}" class="small-box-footer">
                                    View Details <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12">
                            <p class="text-center text-muted">No conflicts found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cogs"></i> Resolution Actions</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-success" id="auto-resolve-skip">
                            <i class="fa fa-fast-forward"></i> Auto-Resolve All (Skip)
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-info" id="export-report">
                            <i class="fa fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-warning" id="auto-resolve-update">
                            <i class="fa fa-refresh"></i> Auto-Resolve All (Update)
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-danger" id="rollback-resolutions">
                            <i class="fa fa-undo"></i> Rollback All Resolutions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conflicts Table -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Unresolved Conflicts
                </h3>
                <div class="box-tools pull-right">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" id="conflict-search" class="form-control" placeholder="Search conflicts">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="box-body">
                @if($conflicts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="conflicts-table">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="select-all-conflicts">
                                    </th>
                                    <th width="10%">Row #</th>
                                    <th width="20%">Conflict Type</th>
                                    <th width="30%">Details</th>
                                    <th width="20%">Suggested Resolution</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conflicts as $conflict)
                                    <tr id="conflict-{{ $conflict->id }}">
                                        <td>
                                            <input type="checkbox" class="conflict-checkbox" value="{{ $conflict->id }}">
                                        </td>
                                        <td>{{ $conflict->row_number }}</td>
                                        <td>
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
                                        </td>
                                        <td>
                                            @if($conflict->existing_record_id)
                                                <strong>Existing Record:</strong> ID #{{ $conflict->existing_record_id }}<br>
                                            @endif
                                            @if($conflict->new_record_data)
                                                <small class="text-muted">{{ count($conflict->new_record_data) }} fields</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label label-info">
                                                {{ ucfirst(str_replace('_', ' ', $conflict->suggested_resolution)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('imports.conflicts.show', [$import->import_id, $conflict->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <i class="fa fa-check-circle"></i> All conflicts have been resolved!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-success" role="alert">
                        <i class="fa fa-check-circle"></i> <strong>Great!</strong> No unresolved conflicts found.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Select all conflicts
    $('#select-all-conflicts').on('change', function() {
        $('.conflict-checkbox').prop('checked', this.checked);
    });

    // Auto-resolve with skip strategy
    $('#auto-resolve-skip').on('click', function() {
        if (confirm('Auto-resolve all conflicts by skipping? This action cannot be undone.')) {
            autoResolve('skip');
        }
    });

    // Auto-resolve with update strategy
    $('#auto-resolve-update').on('click', function() {
        if (confirm('Auto-resolve all conflicts by updating existing records? This action cannot be undone.')) {
            autoResolve('update');
        }
    });

    // Rollback all resolutions
    $('#rollback-resolutions').on('click', function() {
        if (confirm('Rollback all resolutions? This will mark all conflicts as unresolved.')) {
            rollbackResolutions();
        }
    });

    // Export report
    $('#export-report').on('click', function() {
        window.location.href = '{{ route("imports.conflicts.export-report", $import->import_id) }}';
    });

    function autoResolve(strategy) {
        $.ajax({
            url: '{{ route("imports.conflicts.auto-resolve", $import->import_id) }}',
            method: 'POST',
            data: {
                strategy: strategy,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('Successfully auto-resolved conflicts', 'Success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to auto-resolve conflicts', 'Error');
            }
        });
    }

    function rollbackResolutions() {
        $.ajax({
            url: '{{ route("imports.conflicts.rollback", $import->import_id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('Resolutions rolled back successfully', 'Success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to rollback resolutions', 'Error');
            }
        });
    }
});
</script>
@endsection
