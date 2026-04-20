<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('layouts.partials.htmlheader')
@show

<body class="hold-transition portal-hub-layout portal-hub">
<div class="portal-hub-wrapper">
    <main class="portal-hub-canvas" role="main">
        @include('partials.loading-spinner')

        @yield('main-content')

        {{-- Testing-only: expose flash and validation messages in page HTML so legacy BrowserKit-style tests can assert on them --}}
        @if (app()->environment('testing'))
            <div id="__test_helpers__" style="display:none">
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

<style>
    html,
    body.portal-hub-layout {
        min-height: 100%;
    }

    body.portal-hub-layout {
        margin: 0;
        background: radial-gradient(circle at 15% 10%, rgba(14, 165, 233, 0.16), transparent 36%),
            radial-gradient(circle at 85% 5%, rgba(245, 158, 11, 0.12), transparent 32%),
            #0f172a;
    }

    .portal-hub-wrapper {
        min-height: 100vh;
    }

    .portal-hub-canvas {
        max-width: 1320px;
        margin: 0 auto;
        padding: 24px 20px 32px;
    }

    @media (max-width: 767px) {
        .portal-hub-canvas {
            padding: 14px 12px 20px;
        }
    }
</style>

@stack('styles')
@stack('scripts')

</body>
</html>
