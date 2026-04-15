{{-- Form Buttons Partial --}}
@php
    $submitText = $submitText ?? 'Save';
    $submitClass = $submitClass ?? 'btn-primary';
    $cancelRoute = $cancelRoute ?? 'javascript:history.back()';
    $showCancel = $showCancel ?? true;
@endphp

<div class="form-group">
    <div class="col-md-8 col-md-offset-4">
        <button type="submit" class="btn {{ $submitClass }}">
            <i class="fa fa-save"></i> {{ $submitText }}
        </button>
        
        @if($showCancel)
            @if(is_string($cancelRoute) && str_starts_with($cancelRoute, 'javascript:'))
                <a href="{{ $cancelRoute }}" class="btn btn-default">
                    <i class="fa fa-times"></i> Cancel
                </a>
            @else
                <a href="{{ route($cancelRoute) }}" class="btn btn-default">
                    <i class="fa fa-times"></i> Cancel
                </a>
            @endif
        @endif
    </div>
</div>