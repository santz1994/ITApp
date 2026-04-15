@extends('layouts.app')

@php
// Helper function to format minutes to human-readable format
if (!function_exists('formatMinutesToHumanReadable')) {
    function formatMinutesToHumanReadable($minutes) {
        if ($minutes < 60) {
            return $minutes . ' min';
        } elseif ($minutes < 1440) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return $hours . 'h' . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . 'm' : '');
        } else {
            $days = floor($minutes / 1440);
            $remainingHours = floor(($minutes % 1440) / 60);
            return $days . 'd' . ($remainingHours > 0 ? ' ' . $remainingHours . 'h' : '');
        }
    }
}
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom-tables.css') }}">
<style>
    .box {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e0e0e0;
    }

    .box-header {
        background-color: #007bff;
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .box-title {
        color: white;
        font-weight: 600;
        font-size: 1.3rem;
        margin: 0;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background-color: #007bff;
        color: white;
        border: none;
        font-weight: 600;
        padding: 0.95rem 1rem;
        text-transform: uppercase;
        font-size: 0.82rem;
        letter-spacing: 0.3px;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table tbody td {
        padding: 0.95rem 1rem;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
    }

    .badge {
        padding: 0.45rem 0.7rem;
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 4px;
    }

    .badge-info {
        background-color: #17a2b8;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .btn-toggle-status {
        transition: all 0.2s ease;
        border-radius: 4px;
        font-weight: 600;
    }

    .btn-toggle-status:hover {
        opacity: 0.9;
    }

    .alert {
        border-radius: 6px;
        border-left: 4px solid;
        padding: 1rem 1.25rem;
        animation: slideInDown 0.4s ease;
    }

    .alert-success {
        background-color: #d4edda;
        border-left-color: #28a745;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-left-color: #dc3545;
        color: #721c24;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .text-center i.fa-inbox {
        color: #dee2e6;
    }

    .table-responsive {
        border-radius: 6px;
        overflow: hidden;
    }
</style>
@endpush

@section('main-content')
    @include('components.page-header', [
        'title' => 'SLA Policies',
        'subtitle' => 'Manage Service Level Agreement policies',
        'icon' => 'fa-clock-o',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'fa-dashboard'],
            ['label' => 'SLA Policies', 'active' => true]
        ],
        'actions' => '
            <div class="btn-group" role="group">
                <a href="' . route('sla.dashboard') . '" class="btn btn-info">
                    <i class="fa fa-line-chart"></i> <span class="hidden-xs">Dashboard</span>
                </a>
                <a href="' . route('sla.create') . '" class="btn btn-primary">
                    <i class="fa fa-plus"></i> <span class="hidden-xs">New Policy</span>
                </a>
            </div>
        '
    ])

    @include('components.loading-overlay')

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-list"></i> SLA Policies List
                            </h3>
                        </div>

                        <div class="box-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="20%">Policy Name</th>
                                    <th width="15%">Priority</th>
                                    <th width="15%">Response Time</th>
                                    <th width="15%">Resolution Time</th>
                                    <th width="10%">Business Hours</th>
                                    <th width="10%">Status</th>
                                    <th width="10%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($policies as $policy)
                                    <tr>
                                        <td>{{ $policy->id }}</td>
                                        <td>
                                            <strong>{{ $policy->name }}</strong>
                                            @if($policy->description)
                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($policy->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($policy->priority)
                                                <span class="label label-{{ $policy->priority->color ?? 'default' }}">
                                                    {{ $policy->priority->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">No Priority</span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fa fa-reply text-info"></i> 
                                            {{ $policy->response_time }} minutes
                                            <br><small class="text-muted">({{ formatMinutesToHumanReadable($policy->response_time) }})</small>
                                        </td>
                                        <td>
                                            <i class="fa fa-check-circle text-success"></i> 
                                            {{ $policy->resolution_time }} minutes
                                            <br><small class="text-muted">({{ formatMinutesToHumanReadable($policy->resolution_time) }})</small>
                                        </td>
                                        <td>
                                            @if($policy->business_hours_only)
                                                <span class="label label-info">
                                                    <i class="fa fa-clock-o"></i> Business Hours
                                                </span>
                                            @else
                                                <span class="label label-warning">
                                                    <i class="fa fa-clock-o"></i> 24/7
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-toggle-status {{ $policy->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                    onclick="toggleStatus({{ $policy->id }})"
                                                    data-id="{{ $policy->id }}">
                                                <i class="fa fa-{{ $policy->is_active ? 'check' : 'times' }}"></i>
                                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('view', $policy)
                                                    <a href="{{ route('sla.show', $policy->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('update', $policy)
                                                    <a href="{{ route('sla.edit', $policy->id) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('delete', $policy)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="deletePolicy({{ $policy->id }})"
                                                            title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-3x" style="margin-bottom: 15px;"></i>
                            <p>No SLA policies found.</p>
                            @can('create', App\SlaPolicy::class)
                                <a href="{{ route('sla.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Create First Policy
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($policies->hasPages())
        <div class="mt-3">
            {{ $policies->links() }}
        </div>
    @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('scripts')
<script>
function toggleStatus(policyId) {
    if (confirm('Are you sure you want to toggle the status of this SLA policy?')) {
        $.ajax({
            url: `/sla/${policyId}/toggle-active`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message || 'Failed to toggle status');
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while toggling the status');
            }
        });
    }
}

function deletePolicy(policyId) {
    if (confirm('Are you sure you want to delete this SLA policy? This action cannot be undone.')) {
        const form = document.getElementById('delete-form');
        form.action = `/sla/${policyId}`;
        form.submit();
    }
}

// Tooltip initialization
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection