{{-- Resolution Type Badge Component --}}
@props(['resolution' => 'skip'])

@php
    $labels = [
        'skip' => ['text' => 'Skip Row', 'class' => 'label-warning', 'icon' => 'fa-forward'],
        'create_new' => ['text' => 'Create New', 'class' => 'label-success', 'icon' => 'fa-plus'],
        'update_existing' => ['text' => 'Update Existing', 'class' => 'label-info', 'icon' => 'fa-pencil'],
        'merge' => ['text' => 'Merge Records', 'class' => 'label-primary', 'icon' => 'fa-compress'],
    ];

    $info = $labels[$resolution] ?? $labels['skip'];
@endphp

<span class="label {{ $info['class'] }}">
    <i class="fa {{ $info['icon'] }}"></i> {{ $info['text'] }}
</span>
