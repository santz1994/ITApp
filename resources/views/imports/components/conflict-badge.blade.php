{{-- Conflict Type Badge Component --}}
@props(['type' => 'unknown', 'size' => 'md'])

@php
    $colors = [
        'duplicate_key' => '#DD4B39',
        'duplicate_record' => '#F39C12',
        'foreign_key_not_found' => '#3498DB',
        'invalid_data' => '#9B59B6',
        'business_rule_violation' => '#E74C3C',
    ];
    
    $icons = [
        'duplicate_key' => 'fa-key',
        'duplicate_record' => 'fa-clone',
        'foreign_key_not_found' => 'fa-unlink',
        'invalid_data' => 'fa-times-circle',
        'business_rule_violation' => 'fa-gavel',
    ];

    $color = $colors[$type] ?? '#95A5A6';
    $icon = $icons[$type] ?? 'fa-exclamation';
    $label = ucfirst(str_replace('_', ' ', $type));
@endphp

<span class="badge" style="background-color: {{ $color }}">
    <i class="fa {{ $icon }}"></i> {{ $label }}
</span>
