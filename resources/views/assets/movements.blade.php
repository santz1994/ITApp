@extends('layouts.app')

@section('main-content')

{{-- Page Header Component --}}
@component('components.page-header')
    @slot('icon') fa-exchange @endslot
    @slot('title') Movement History @endslot
    @slot('subtitle') Track location changes and assignment history for {{ $asset->asset_tag }} @endslot
@endcomponent

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
    {{-- Main Content: 9 columns --}}
    <div class="col-md-9">
        {{-- Asset Quick Info --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-info-circle"></i> Asset Information
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong><i class="fa fa-tag"></i> Asset Tag:</strong><br>
                        {{ $asset->asset_tag }}
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fa fa-laptop"></i> Model:</strong><br>
                        {{ optional($asset->model)->asset_model ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fa fa-map-marker"></i> Current Location:</strong><br>
                        {{ optional($asset->location)->location_name ?? 'Unassigned' }}
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fa fa-user"></i> Assigned To:</strong><br>
                        {{ optional($asset->assignedTo)->name ?? 'Unassigned' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Movement Timeline --}}
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-history"></i> Movement Timeline
                    <span class="count-badge">{{ $movements->count() }}</span>
                </h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-sm btn-primary" onclick="exportMovements()">
                        <i class="fa fa-download"></i> Export to Excel
                    </button>
                </div>
            </div>
            <div class="box-body">
                @if($movements->isEmpty())
                    <div class="empty-state">
                        <i class="fa fa-exchange fa-3x text-muted"></i>
                        <p>No movement history recorded for this asset.</p>
                        <p class="text-muted">Movement records will appear here when the asset is moved to different locations.</p>
                    </div>
                @else
                    {{-- Timeline Display --}}
                    <ul class="timeline">
                        @foreach($movements as $index => $movement)
                            <li>
                                <i class="fa fa-exchange bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fa fa-clock-o"></i> {{ $movement->created_at->format('M d, Y') }}
                                        <small>{{ $movement->created_at->format('h:i A') }}</small>
                                    </span>
                                    <h3 class="timeline-header">
                                        <strong>Location Change</strong>
                                        @if($movement->moved_by)
                                            by {{ optional($movement->moved_by)->name }}
                                        @elseif($movement->user)
                                            by {{ optional($movement->user)->name }}
                                        @endif
                                    </h3>
                                    <div class="timeline-body">
                                        <div class="row">
                                            <div class="col-sm-5 text-center">
                                                <div class="well well-sm" style="background: #f9f9f9;">
                                                    <strong>From:</strong><br>
                                                    <i class="fa fa-map-marker text-danger"></i>
                                                    <span style="font-size: 16px;">
                                                        {{ optional($movement->from_location)->location_name ?? 'Initial Location' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-2 text-center" style="padding-top: 20px;">
                                                <i class="fa fa-arrow-right fa-2x text-blue"></i>
                                            </div>
                                            <div class="col-sm-5 text-center">
                                                <div class="well well-sm" style="background: #e8f5e9;">
                                                    <strong>To:</strong><br>
                                                    <i class="fa fa-map-marker text-success"></i>
                                                    <span style="font-size: 16px;">
                                                        {{ optional($movement->to_location)->location_name ?? 'Unknown' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($movement->notes)
                                            <div style="margin-top: 10px; padding: 10px; background: #fffbea; border-left: 3px solid #f39c12; border-radius: 3px;">
                                                <i class="fa fa-sticky-note text-warning"></i> <strong>Notes:</strong> {{ $movement->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        <li>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </li>
                    </ul>

                    {{-- Table View (Alternative) --}}
                    <div style="margin-top: 30px;">
                        <h4><i class="fa fa-table"></i> Detailed Movement Log</h4>
                        <table class="table table-striped table-bordered table-enhanced" id="movementsTable">
                            <thead>
                                <tr>
                                    <th width="15%">Date & Time</th>
                                    <th width="20%">From Location</th>
                                    <th width="20%">To Location</th>
                                    <th width="15%">Moved By</th>
                                    <th width="10%">Days at Location</th>
                                    <th width="20%">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $index => $movement)
                                    @php
                                        $daysAtLocation = null;
                                        if (isset($movements[$index + 1])) {
                                            $daysAtLocation = $movement->created_at->diffInDays($movements[$index + 1]->created_at);
                                        } else {
                                            $daysAtLocation = $movement->created_at->diffInDays(now());
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $movement->created_at->format('M d, Y') }}<br>
                                            <small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <i class="fa fa-map-marker text-danger"></i>
                                            {{ optional($movement->from_location)->location_name ?? '—' }}
                                        </td>
                                        <td>
                                            <i class="fa fa-map-marker text-success"></i>
                                            {{ optional($movement->to_location)->location_name ?? '—' }}
                                        </td>
                                        <td>{{ optional($movement->moved_by)->name ?? (optional($movement->user)->name ?? '—') }}</td>
                                        <td>
                                            <span class="label label-info">{{ $daysAtLocation }} days</span>
                                        </td>
                                        <td>{{ $movement->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- End Main Content --}}

    {{-- Sidebar: 3 columns --}}
    <div class="col-md-3">
        {{-- Movement Statistics --}}
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bar-chart"></i> Movement Statistics
                </h3>
            </div>
            <div class="box-body">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-exchange"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Movements</span>
                        <span class="info-box-number">{{ $movements->count() }}</span>
                    </div>
                </div>

                @if($movements->count() > 0)
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-map-marker"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Locations Visited</span>
                            <span class="info-box-number">
                                {{ $movements->pluck('to_location.location_name')->unique()->count() }}
                            </span>
                        </div>
                    </div>

                    @php
                        $firstMovement = $movements->last();
                        $daysSinceFirst = $firstMovement ? $firstMovement->created_at->diffInDays(now()) : 0;
                        $lastMovement = $movements->first();
                        $daysSinceLast = $lastMovement ? $lastMovement->created_at->diffInDays(now()) : 0;
                    @endphp

                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Days Since Last Move</span>
                            <span class="info-box-number">{{ $daysSinceLast }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Assign to User Section --}}
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-user"></i> Assign to User</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-info">
                    <strong>Current Assignment:</strong><br>
                    {{ optional($asset->assignedTo)->name ?? 'Unassigned' }}
                </div>

                <form method="POST" action="{{ route('assets.assign', $asset->id) }}" id="assignForm">
                    @csrf
                    <div class="form-group">
                        <label for="user_id">
                            <i class="fa fa-user"></i> Assign to User
                        </label>
                        <select name="user_id" id="user_id" class="form-control select2" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $asset->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notes">
                            <i class="fa fa-sticky-note"></i> Notes (Optional)
                        </label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add assignment notes..."></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-save"></i> Assign Asset
                        </button>
                        @if($asset->assigned_to)
                            <button type="button" class="btn btn-warning btn-block" onclick="unassignAsset()">
                                <i class="fa fa-user-times"></i> Unassign
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bolt"></i> Quick Actions
                </h3>
            </div>
            <div class="box-body">
                <a href="{{ route('assets.show', $asset) }}" class="btn btn-default btn-block">
                    <i class="fa fa-eye"></i> View Asset Details
                </a>
                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-primary btn-block">
                    <i class="fa fa-edit"></i> Edit Asset
                </a>
                <a href="{{ route('assets.index') }}" class="btn btn-default btn-block">
                    <i class="fa fa-list"></i> Back to Assets List
                </a>
            </div>
        </div>
    </div>
    {{-- End Sidebar --}}
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select a user",
        allowClear: true
    });

    // Initialize DataTable for movements table
    @if($movements->count() > 0)
        $('#movementsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']], // Sort by date descending
            pageLength: 10,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i> CSV',
                    titleAttr: 'Export to CSV',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'Export to PDF',
                    className: 'btn btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    titleAttr: 'Print',
                    className: 'btn btn-default btn-sm'
                }
            ],
            language: {
                search: "Search movements:",
                lengthMenu: "Show _MENU_ movements per page",
                info: "Showing _START_ to _END_ of _TOTAL_ movements",
                infoEmpty: "No movements to show",
                infoFiltered: "(filtered from _MAX_ total movements)",
                paginate: {
                    first: '<i class="fa fa-angle-double-left"></i>',
                    previous: '<i class="fa fa-angle-left"></i>',
                    next: '<i class="fa fa-angle-right"></i>',
                    last: '<i class="fa fa-angle-double-right"></i>'
                }
            }
        });
    @endif
});

// Export movements function (called by button in header)
function exportMovements() {
    @if($movements->count() > 0)
        // Trigger DataTable Excel export
        $('#movementsTable').DataTable().button('.buttons-excel').trigger();
    @else
        alert('No movements to export');
    @endif
}

// Unassign asset function
function unassignAsset() {
    if (confirm('Are you sure you want to unassign this asset from {{ optional($asset->assignedTo)->name }}?')) {
        fetch('{{ route("assets.unassign", $asset->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Asset unassigned successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to unassign asset'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while unassigning the asset. Please try again.');
        });
    }
}
</script>
@endsection
