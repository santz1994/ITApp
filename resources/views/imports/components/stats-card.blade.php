{{-- Conflict Statistics Card Component --}}
@props(['title' => '', 'value' => '0', 'icon' => 'fa-exclamation', 'bgColor' => 'bg-red', 'link' => null])

<div class="info-box">
    <span class="info-box-icon {{ $bgColor }}"><i class="fa {{ $icon }}"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">{{ $title }}</span>
        <span class="info-box-number">{{ $value }}</span>
        @if($link)
            <span class="progress-description">
                <a href="{{ $link }}" class="small">View Details →</a>
            </span>
        @endif
    </div>
</div>
