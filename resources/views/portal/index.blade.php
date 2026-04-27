@extends('layouts.portal')

@php
    $moduleI18nMap = [
        'it_support' => ['title' => 'mod_it_support', 'desc' => 'mod_it_support_desc', 'accent' => 'mod-it'],
        'assets_management' => ['title' => 'mod_assets', 'desc' => 'mod_assets_desc', 'accent' => 'mod-assets'],
        'meeting_room' => ['title' => 'mod_meeting', 'desc' => 'mod_meeting_desc', 'accent' => 'mod-meeting'],
        'user_management' => ['title' => 'mod_users', 'desc' => 'mod_users_desc', 'accent' => 'mod-admin'],
        'settings' => ['title' => 'mod_settings', 'desc' => 'mod_settings_desc', 'accent' => 'mod-admin'],
        'purchase_request' => ['title' => 'mod_purchase', 'desc' => 'mod_purchase_desc', 'accent' => 'mod-assets'],
        'profile' => ['title' => 'mod_profile', 'desc' => 'mod_profile_desc', 'accent' => 'mod-it'],
        'kpi_dashboard' => ['title' => 'mod_kpi', 'desc' => 'mod_kpi_desc', 'accent' => 'mod-admin'],
        'lcd_screen' => ['title' => 'mod_lcd', 'desc' => 'mod_lcd_desc', 'accent' => 'mod-meeting'],
    ];

    $portalUser = auth()->user();
    $portalUserName = (string) ($portalUser->name ?? 'User');
    $portalUserEmail = (string) ($portalUser->email ?? '-');
    $portalInitials = '';
    foreach (preg_split('/\s+/', trim($portalUserName)) ?: [] as $namePart) {
        if ($namePart === '') {
            continue;
        }

        $portalInitials .= strtoupper(substr($namePart, 0, 1));
        if (strlen($portalInitials) >= 2) {
            break;
        }
    }

    if ($portalInitials === '') {
        $portalInitials = 'U';
    }

    $primaryBadgeData = is_array($primaryRoleBadge ?? null) ? $primaryRoleBadge : [
        'level' => 1,
        'icon' => 'fa-cog',
        'label_en' => 'User / The Operator',
        'label_id' => 'Pengguna / The Operator',
    ];
    $primaryBadgeLevel = (int) ($primaryBadgeData['level'] ?? 1);
    $primaryBadgeIcon = (string) ($primaryBadgeData['icon'] ?? 'fa-cog');
    $primaryBadgeLabelEn = (string) ($primaryBadgeData['label_en'] ?? 'User / The Operator');
    $primaryBadgeLabelId = (string) ($primaryBadgeData['label_id'] ?? 'Pengguna / The Operator');
    $portalRoleClass = match (true) {
        $primaryBadgeLevel >= 10 => 'cyber-badge-lv10',
        $primaryBadgeLevel >= 9 => 'cyber-badge-lv9',
        $primaryBadgeLevel >= 8 => 'cyber-badge-lv8',
        $primaryBadgeLevel >= 3 => 'cyber-badge-lv3',
        $primaryBadgeLevel >= 2 => 'cyber-badge-lv2',
        default => 'cyber-badge-lv1',
    };
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/portal-index.css') }}">
@endpush

@section('main-content')

<div
    class="portal-shell"
    data-portal-preferences-url="{{ route('api.portal-preferences.index') }}"
    data-portal-user-id="{{ (int) auth()->id() }}"
>
    <div class="portal-utility-bar" id="portal-utility-bar">
        <div class="portal-identity-group">
            <span class="portal-avatar">{{ $portalInitials }}</span>
            <div class="portal-user-meta">
                <span class="portal-user-label" data-i18n="portal_user_label">Signed in as</span>
                <span class="portal-user-name">{{ $portalUserName }}</span>
                <span class="portal-user-email">{{ $portalUserEmail }}</span>
            </div>
            <span
                class="cyber-badge portal-role-badge {{ $portalRoleClass }}"
                data-role-badge-label-en="{{ $primaryBadgeLabelEn }}"
                data-role-badge-label-id="{{ $primaryBadgeLabelId }}"
            >
                <span class="portal-role-level">LV {{ $primaryBadgeLevel }}</span>
                <i class="fa {{ $primaryBadgeIcon }}"></i>
                <span data-role-badge-text>{{ $primaryBadgeLabelEn }}</span>
            </span>
        </div>
        <div class="portal-utility-actions">
            <div class="btn-group btn-group-xs portal-language-toggle" role="group" aria-label="Language Toggle">
                <button type="button" class="btn btn-default" id="lang-id">ID</button>
                <button type="button" class="btn btn-default" id="lang-en">EN</button>
            </div>
            <button
                type="button"
                class="btn btn-xs portal-theme-toggle"
                data-theme-toggle
                aria-label="Theme Toggle"
                data-theme-toggle-aria-label="Theme Toggle"
            >
                <i class="fa fa-moon-o" data-theme-icon></i>
                <span data-theme-label data-i18n="theme_mode_dark">Dark Mode</span>
            </button>
            <div class="portal-clock" id="wib-clock">--:--:-- WIB</div>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-xs portal-logout-btn" id="portal-logout-action">
                        <i class="fa fa-sign-out"></i>
                        <span data-i18n="portal_sign_out">Sign Out</span>
                    </button>
                </form>
            @endauth
        </div>
    </div>

    <div class="portal-headline">
        <span class="portal-kicker" data-i18n="portal_label">Main Portal</span>
        <h2 class="portal-title" data-i18n="modules_title">Module Navigation</h2>
        <p class="portal-subtitle" data-i18n="portal_subtitle">Please select a module to continue operations.</p>
    </div>

    <div id="portalModuleGrid">
        @forelse($modules as $module)
            @php
                $moduleKey = (string) ($module['key'] ?? \Illuminate\Support\Str::slug((string) ($module['title'] ?? 'module')));
                $moduleMeta = $moduleI18nMap[$moduleKey] ?? ['title' => '', 'desc' => '', 'accent' => 'mod-it'];
                $cardClass = 'box box-solid cyber-card ' . $moduleMeta['accent'];

                if (in_array($moduleKey, ['user_management', 'settings', 'kpi_dashboard'], true)) {
                    $cardClass .= ' admin-card';
                }
            @endphp
            <div class="col-lg-4 col-md-6 col-sm-12 portal-module-col" data-card-index="{{ $loop->index }}" data-portal-module-key="{{ $moduleKey }}">
                <div class="{{ $cardClass }}">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa {{ $module['icon'] }}"></i>
                            <span @if($moduleMeta['title'] !== '') data-i18n="{{ $moduleMeta['title'] }}" @endif>
                                {{ $module['title'] }}
                            </span>
                        </h3>
                    </div>
                    <div class="box-body">
                        <p class="module-description" @if($moduleMeta['desc'] !== '') data-i18n="{{ $moduleMeta['desc'] }}" @endif>
                            {{ $module['description'] }}
                        </p>

                        <a href="{{ $module['url'] }}" class="btn btn-sm cyber-action">
                            <span data-i18n="mod_open">Open Module</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-xs-12 portal-grid-empty">
                <div class="alert alert-info">
                    <span data-i18n="modules_empty">No modules are configured for your current role. Please contact administrator.</span>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/portal-index.js') }}"></script>
@endpush

@endsection
