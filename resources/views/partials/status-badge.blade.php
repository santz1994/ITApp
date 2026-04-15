{{-- Status Badge Partial --}}
@php
    $status = $status ?? 'unknown';
    $statusText = $statusText ?? ucfirst($status);
    
    // Define status classes
    $statusClasses = [
        'active' => 'label-success',
        'inactive' => 'label-default',
        'pending' => 'label-warning',
        'approved' => 'label-success',
        'rejected' => 'label-danger',
        'completed' => 'label-success',
        'in-progress' => 'label-info',
        'open' => 'label-warning',
        'closed' => 'label-success',
        'assigned' => 'label-info',
        'unassigned' => 'label-default',
        'high' => 'label-danger',
        'medium' => 'label-warning',
        'low' => 'label-success',
        'critical' => 'label-danger',
        'maintenance' => 'label-warning',
        'retired' => 'label-default',
        'available' => 'label-success',
        'unavailable' => 'label-danger',
        'overdue' => 'label-danger',
        'unknown' => 'label-default'
    ];
    
    $badgeClass = $statusClasses[strtolower($status)] ?? 'label-default';
@endphp

<span class="label {{ $badgeClass }}">{{ $statusText }}</span>