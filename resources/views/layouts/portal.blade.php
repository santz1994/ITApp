<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('layouts.partials.htmlheader')
@show

@php
    $portalLayoutRawPreferences = auth()->check() ? (auth()->user()->portal_preferences ?? []) : [];
    $portalLayoutPreferences = is_array($portalLayoutRawPreferences)
        ? $portalLayoutRawPreferences
        : (json_decode((string) $portalLayoutRawPreferences, true) ?: []);
    $portalLayoutTheme = (($portalLayoutPreferences['theme'] ?? 'light') === 'dark') ? 'dark' : 'light';
@endphp

<body class="hold-transition portal-hub-layout portal-hub" data-itapp-theme="{{ $portalLayoutTheme }}">
<div class="portal-hub-wrapper">
    <main class="portal-hub-canvas" role="main">
        @include('partials.loading-spinner')

        @yield('main-content')

        {{-- Testing-only: expose flash and validation messages in page HTML so legacy BrowserKit-style tests can assert on them --}}
        @if (app()->environment('testing'))
            <div id="__test_helpers__" class="itapp-test-helpers-hidden">
                <div id="__flash_status">{{ session('status') }}</div>
                <div id="__flash_title">{{ session('title') }}</div>
                <div id="__flash_message">{{ session('message') }}</div>
                <div id="__flash_generic">{{ session('flash_message') ?? session('flash') }}</div>
                <div id="__legacy_msg">{{ session('legacy_msg') }}</div>
                <div id="__direct_legacy_message">{{ request()->query('direct_legacy_message') }}</div>
                <div id="__validation_errors">
                    @if (isset($errors) && $errors->any())
                        @foreach ($errors->all() as $err)
                            <div class="__err">{{ $err }}</div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    </main>
</div>

@include('layouts.partials.scripts')
@section('scripts')
@show

<link href="{{ asset('/css/portal-layout.css') }}" rel="stylesheet" type="text/css" />

@stack('styles')
@stack('scripts')

</body>
</html>
