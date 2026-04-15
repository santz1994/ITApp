{{-- Search Bar Partial --}}
@php
    $searchRoute = $searchRoute ?? request()->url();
    $searchPlaceholder = $searchPlaceholder ?? 'Search...';
    $searchValue = $searchValue ?? request('search');
@endphp

<div class="search-bar-container mb-3">
    <form method="GET" action="{{ $searchRoute }}" class="search-form">
        @foreach(request()->except(['search', 'page']) as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        
        <div class="input-group">
            <input type="text" 
                   name="search" 
                   class="form-control" 
                   placeholder="{{ $searchPlaceholder }}" 
                   value="{{ $searchValue }}">
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-search"></i>
                </button>
                @if($searchValue)
                    <a href="{{ $searchRoute }}" class="btn btn-default" title="Clear search">
                        <i class="fa fa-times"></i>
                    </a>
                @endif
            </span>
        </div>
    </form>
</div>

<style>
.search-bar-container {
    max-width: 400px;
    margin-bottom: 15px;
}

.search-form .input-group {
    width: 100%;
}

.search-form .input-group-btn .btn {
    border-left: 0;
}

.search-form .input-group-btn .btn:first-child {
    border-left: 1px solid #d2d6de;
}
</style>