<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('layouts.partials.htmlheader')
@show

@php
    $appLayoutRawPreferences = auth()->check() ? (auth()->user()->portal_preferences ?? []) : [];
    $appLayoutPreferences = is_array($appLayoutRawPreferences)
        ? $appLayoutRawPreferences
        : (json_decode((string) $appLayoutRawPreferences, true) ?: []);
    $appLayoutTheme = (($appLayoutPreferences['theme'] ?? 'light') === 'dark') ? 'dark' : 'light';
@endphp

<body class="skin-blue sidebar-mini" data-itapp-theme="{{ $appLayoutTheme }}">
<div class="wrapper">

    @include('layouts.partials.mainheader')

    @include('layouts.partials.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        @include('layouts.partials.contentheader')

        <!-- Main content -->
        <section class="content">
            <!-- Loading Spinner -->
            @include('partials.loading-spinner')
            
            <!-- Your Page Content Here -->
            @yield('main-content')

            {{-- Testing-only: expose flash and validation messages in page HTML so legacy BrowserKit-style tests can assert on them --}}
            @if (app()->environment('testing'))
                <div id="__test_helpers__" style="display:none">
                    <div id="__flash_status">{{ session('status') }}</div>
                    <div id="__flash_title">{{ session('title') }}</div>
                    <div id="__flash_message">{{ session('message') }}</div>
                    <div id="__flash_generic">{{ session('flash_message') ?? session('flash') }}</div>
                    {{-- Support legacy compatibility: some controllers attach legacy_msg or pass a direct_legacy_message via query string --}}
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
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    @include('layouts.partials.controlsidebar')

    @yield('footer')
    @include('layouts.partials.footer')

</div><!-- ./wrapper -->

@include('layouts.partials.scripts')
@section('scripts')
@show

<!-- Custom Styles -->
@stack('styles')

<!-- Monthly Report Modal Script (for sidebar link) -->
<script>
function openMonthlyReportModal() {
    // Check if we're on receptionist dashboard
    if ($('#monthlyReportModal').length > 0) {
        // Modal exists on current page, open it directly
        $('#monthlyReportModal').modal('show');
    } else {
        // Redirect to receptionist dashboard with modal trigger
        window.location.href = '{{ route('meeting-room-bookings.receptionist-dashboard') }}?open_report_modal=true';
    }
}
</script>

<!-- Custom Scripts -->
@stack('scripts')


</body>
</html>
