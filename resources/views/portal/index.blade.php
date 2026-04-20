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
<style>
    body.portal-hub {
        background-color: transparent;
        color: #f8fafc;
        font-family: 'Source Sans Pro', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .portal-shell {
        background: linear-gradient(160deg, rgba(15, 23, 42, 0.96) 0%, rgba(17, 24, 39, 0.95) 62%);
        border: 1px solid #24334d;
        border-radius: 18px;
        padding: 22px;
        position: relative;
        overflow: hidden;
        animation: shellReveal 360ms ease-out;
    }

    .portal-shell::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(148, 163, 184, 0.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, 0.06) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.08;
        pointer-events: none;
    }

    .portal-shell > * {
        position: relative;
        z-index: 1;
    }

    @keyframes shellReveal {
        from {
            transform: translateY(8px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .portal-utility-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        padding: 10px 12px;
        border: 1px solid #334155;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(2px);
    }

    .portal-identity-group {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .portal-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #475569;
        background: linear-gradient(135deg, #1f2937, #0f172a);
        color: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        flex-shrink: 0;
    }

    .portal-user-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .portal-user-label {
        color: #94a3b8;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.2;
    }

    .portal-user-name {
        color: #f8fafc;
        font-size: 13px;
        line-height: 1.2;
        font-weight: 700;
    }

    .portal-user-email {
        color: #94a3b8;
        font-size: 11px;
        line-height: 1.2;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .portal-role-badge {
        margin-left: auto;
        font-size: 11px;
    }

    .portal-role-level {
        font-weight: 700;
        margin-right: 3px;
    }

    .portal-logout-btn {
        border: 1px solid #475569;
        border-radius: 8px;
        color: #f8fafc;
        background: #1f2937;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .portal-logout-btn:hover,
    .portal-logout-btn:focus {
        color: #ffffff;
        border-color: #64748b;
        background: #334155;
    }

    .portal-utility-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .portal-language-toggle .btn {
        border-color: #334155;
        color: #cbd5e1;
        background: #1e293b;
    }

    .portal-language-toggle .btn.active {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: #f8fafc;
    }

    .portal-clock {
        font-size: 12px;
        color: #94a3b8;
        letter-spacing: 0.3px;
    }

    #portalModuleGrid {
        --portal-grid-columns: 3;
        --portal-grid-min-card: 260px;
        --portal-grid-gap: 14px;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(var(--portal-grid-columns), minmax(0, 1fr));
        grid-auto-rows: 1fr;
        gap: var(--portal-grid-gap);
        align-items: stretch;
    }

    #portalModuleGrid::before,
    #portalModuleGrid::after {
        content: none !important;
        display: none !important;
    }

    #portalModuleGrid > [class*='col-'] {
        padding: 0;
        width: auto;
        float: none;
    }

    .cyber-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        min-height: 220px;
    }

    .portal-module-col {
        margin-bottom: 0;
        min-width: 0;
        display: flex;
    }

    .portal-module-col .cyber-card {
        width: 100%;
        display: flex;
        flex-direction: column;
        height: 100%;
        animation: cardReveal 420ms ease-out both;
        animation-delay: calc(var(--card-index, 0) * 55ms);
    }

    .portal-module-col .cyber-card .box-header {
        min-height: 58px;
        display: flex;
        align-items: center;
    }

    .portal-module-col .cyber-card .box-title {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        line-height: 1.35;
    }

    .portal-module-col .cyber-card .box-title > i {
        width: 16px;
        text-align: center;
        flex-shrink: 0;
    }

    .portal-module-col .cyber-card .box-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex: 1;
    }

    .portal-module-col .cyber-card .cyber-action {
        margin-top: auto;
        align-self: flex-start;
    }

    .portal-grid-empty {
        grid-column: 1 / -1;
    }

    .cyber-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: transparent;
        transition: background 0.3s ease;
    }

    .cyber-card:hover {
        transform: translateY(-6px);
        border-color: #475569;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
    }

    .cyber-card.mod-it:hover::before {
        background: #06b6d4;
    }

    .cyber-card.mod-assets:hover::before {
        background: #f59e0b;
    }

    .cyber-card.mod-meeting:hover::before {
        background: #10b981;
    }

    .cyber-card.mod-admin:hover::before {
        background: #ef4444;
    }

    .cyber-card.admin-card {
        border-left: 4px solid #ef4444;
    }

    .cyber-card .box-header {
        border-bottom-color: #334155;
        background: #1e293b;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .cyber-card .box-title {
        color: #f8fafc;
        font-weight: 700;
    }

    .module-description {
        color: #cbd5e1;
        min-height: 66px;
        margin-bottom: 0;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cyber-action {
        background: #0b1220;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .cyber-action:hover,
    .cyber-action:focus {
        color: #ffffff;
        border-color: #64748b;
        background: #1f2937;
    }

    .cyber-badge {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .cyber-badge-lv1 {
        background: #64748b;
        color: #ffffff;
        border: 1px solid #475569;
    }

    .cyber-badge-lv2 {
        background: rgba(6, 182, 212, 0.1);
        color: #22d3ee;
        border: 1px solid #06b6d4;
        animation: soft-pulse 3s infinite alternate;
    }

    .cyber-badge-lv3 {
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        border: 1px solid #10b981;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.3) inset;
    }

    .cyber-badge-lv8 {
        background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
        color: #fffbeb;
        border: none;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }

    .cyber-badge-lv9 {
        background: rgba(239, 68, 68, 0.15);
        color: #fca5a5;
        border: 1px solid #ef4444;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.5);
    }

    .cyber-badge-lv10 {
        background: linear-gradient(90deg, #4c1d95, #064e3b);
        background-size: 200% 200%;
        color: #a7f3d0;
        border: 1px solid #8b5cf6;
        position: relative;
        animation: matrix-shift 4s infinite linear;
    }

    @keyframes soft-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.4);
        }
        100% {
            box-shadow: 0 0 0 10px rgba(6, 182, 212, 0);
        }
    }

    @keyframes matrix-shift {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }

    @keyframes cardReveal {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    body.portal-hub[data-portal-screen='compact'] .portal-shell {
        padding: 12px;
    }

    body.portal-hub[data-portal-screen='compact'] #portalModuleGrid {
        --portal-grid-columns: 1;
        --portal-grid-min-card: 210px;
        --portal-grid-gap: 10px;
    }

    body.portal-hub[data-portal-screen='mobile'] #portalModuleGrid {
        --portal-grid-columns: 1;
        --portal-grid-min-card: 220px;
        --portal-grid-gap: 10px;
    }

    body.portal-hub[data-portal-screen='tablet'] #portalModuleGrid {
        --portal-grid-columns: 2;
        --portal-grid-min-card: 235px;
        --portal-grid-gap: 12px;
    }

    body.portal-hub[data-portal-screen='wide'] .portal-shell {
        padding: 24px;
    }

    body.portal-hub[data-portal-screen='wide'] #portalModuleGrid {
        --portal-grid-columns: 3;
        --portal-grid-min-card: 280px;
        --portal-grid-gap: 16px;
    }

    body.portal-hub[data-portal-screen='ultra-wide'] #portalModuleGrid {
        --portal-grid-columns: 3;
        --portal-grid-min-card: 300px;
        --portal-grid-gap: 18px;
    }

    @media (max-width: 767px) {
        .portal-shell {
            padding: 14px;
        }

        .portal-utility-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .portal-identity-group {
            flex-wrap: wrap;
        }

        .portal-role-badge {
            margin-left: 0;
        }

        .portal-user-email {
            max-width: 100%;
        }

        .portal-logout-btn {
            justify-content: center;
        }

        .portal-utility-actions {
            width: 100%;
            justify-content: space-between;
        }

        .module-description {
            min-height: 0;
        }
    }
</style>
@endpush

@section('main-content')

<div class="portal-shell">
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
            <div class="col-lg-4 col-md-6 col-sm-12 portal-module-col" style="--card-index: {{ $loop->index }};" data-portal-module-key="{{ $moduleKey }}">
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
<script>
const portalTranslations = {
    id: {
        portal_label: 'Portal Utama',
        portal_user_label: 'Login sebagai',
        portal_sign_out: 'Keluar',
        portal_welcome: 'Selamat Datang',
        portal_subtitle: 'Silakan pilih modul untuk melanjutkan operasional.',
        modules_title: 'Navigasi Modul',
        mod_it_support: 'IT Support',
        mod_it_support_desc: 'Manajemen tiket, FAQ, dan bantuan pengguna.',
        mod_assets: 'Assets Management',
        mod_assets_desc: 'Inventaris, formulir serah terima, dan pemeliharaan.',
        mod_meeting: 'Meeting Room',
        mod_meeting_desc: 'Pemesanan ruangan, persetujuan, dan jadwal LCD.',
        mod_users: 'User Management',
        mod_users_desc: 'Kelola akun, peran, dan hak akses.',
        mod_settings: 'Settings & AI',
        mod_settings_desc: 'Konfigurasi sistem, backup, log, dan AI Management.',
        mod_purchase: 'Purchase Request',
        mod_purchase_desc: 'Permintaan pengadaan dan pelacakan proses persetujuan.',
        mod_profile: 'Profile',
        mod_profile_desc: 'Kelola profil, kata sandi, dan preferensi akun.',
        mod_kpi: 'KPI Dashboard',
        mod_kpi_desc: 'Pantau KPI operasional lintas modul secara real-time.',
        mod_lcd: 'LCD Screen',
        mod_lcd_desc: 'Tampilkan jadwal ruang rapat pada layar publik.',
        mod_open: 'Buka Modul',
        modules_empty: 'Belum ada modul untuk peran Anda. Silakan hubungi administrator.',
        badge_open_tickets: 'Tiket Terbuka',
        badge_pending: 'Menunggu'
    },
    en: {
        portal_label: 'Main Portal',
        portal_user_label: 'Signed in as',
        portal_sign_out: 'Sign Out',
        portal_welcome: 'Welcome',
        portal_subtitle: 'Please select a module to continue operations.',
        modules_title: 'Module Navigation',
        mod_it_support: 'IT Support',
        mod_it_support_desc: 'Ticket management, FAQ, and user assistance.',
        mod_assets: 'Assets Management',
        mod_assets_desc: 'Inventory, handover forms, and maintenance.',
        mod_meeting: 'Meeting Room',
        mod_meeting_desc: 'Room bookings, approvals, and LCD scheduling.',
        mod_users: 'User Management',
        mod_users_desc: 'Manage accounts, roles, and access rights.',
        mod_settings: 'Settings & AI',
        mod_settings_desc: 'System config, backups, logs, and AI Management.',
        mod_purchase: 'Purchase Request',
        mod_purchase_desc: 'Procurement requests with approval workflow tracking.',
        mod_profile: 'Profile',
        mod_profile_desc: 'Manage profile, password, and account preferences.',
        mod_kpi: 'KPI Dashboard',
        mod_kpi_desc: 'Monitor cross-module operational KPI in one screen.',
        mod_lcd: 'LCD Screen',
        mod_lcd_desc: 'Display meeting room schedule on public screens.',
        mod_open: 'Open Module',
        modules_empty: 'No modules are configured for your current role. Please contact administrator.',
        badge_open_tickets: 'Open Tickets',
        badge_pending: 'Pending'
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const langIdButton = document.getElementById('lang-id');
    const langEnButton = document.getElementById('lang-en');
    const clockElement = document.getElementById('wib-clock');
    const portalShell = document.querySelector('.portal-shell');
    const portalModuleGrid = document.getElementById('portalModuleGrid');
    const apiBaseUrl = '{{ route("api.portal-preferences.index") }}'.replace(/\/$/, '');

    const resolveViewportPreset = (width) => {
        if (width < 480) {
            return { key: 'compact', columns: 1, minCard: 210, gridGap: 10 };
        }

        if (width < 900) {
            return { key: 'mobile', columns: 1, minCard: 220, gridGap: 10 };
        }

        if (width < 1280) {
            return { key: 'tablet', columns: 2, minCard: 235, gridGap: 12 };
        }

        if (width < 1600) {
            return { key: 'wide', columns: 3, minCard: 265, gridGap: 14 };
        }

        return { key: 'ultra-wide', columns: 3, minCard: 300, gridGap: 16 };
    };

    const applyViewportPreset = (width) => {
        const preset = resolveViewportPreset(width);
        document.body.setAttribute('data-portal-screen', preset.key);

        if (portalModuleGrid) {
            portalModuleGrid.style.setProperty('--portal-grid-columns', String(preset.columns));
            portalModuleGrid.style.setProperty('--portal-grid-min-card', `${preset.minCard}px`);
            portalModuleGrid.style.setProperty('--portal-grid-gap', `${preset.gridGap}px`);
        }
    };

    const setupDynamicViewport = () => {
        const observedElement = document.documentElement;

        const applyFromWindow = () => {
            applyViewportPreset(window.innerWidth || document.documentElement.clientWidth || 1024);
        };

        applyFromWindow();

        if (typeof window.ResizeObserver !== 'undefined') {
            const observer = new window.ResizeObserver((entries) => {
                const firstEntry = entries && entries.length > 0 ? entries[0] : null;
                const width = firstEntry && firstEntry.contentRect ? firstEntry.contentRect.width : (window.innerWidth || 1024);
                applyViewportPreset(width);
            });

            observer.observe(observedElement);
            return;
        }

        window.addEventListener('resize', applyFromWindow);
    };

    const loadServerLanguage = async () => {
        try {
            const response = await fetch(apiBaseUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to load language');
            }

            const payload = await response.json();
            if (payload && payload.success && payload.data && payload.data.language === 'en') {
                return 'en';
            }

            return 'id';
        } catch (error) {
            return null;
        }
    };

    const updateServerLanguage = async (lang) => {
        try {
            await fetch(apiBaseUrl + '/language', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ value: lang })
            });
        } catch (error) {
            // Keep silent to avoid blocking UI interactions.
        }
    };

    const applyLanguage = (lang) => {
        const resolvedLang = lang === 'en' ? 'en' : 'id';
        const dictionary = portalTranslations[resolvedLang] || portalTranslations.id;

        window.localStorage.setItem('portal_lang', resolvedLang);

        if (langIdButton && langEnButton) {
            langIdButton.classList.toggle('active', resolvedLang === 'id');
            langEnButton.classList.toggle('active', resolvedLang === 'en');
        }

        document.querySelectorAll('[data-i18n]').forEach((element) => {
            const key = element.getAttribute('data-i18n');
            if (key && dictionary[key]) {
                element.innerText = dictionary[key];
            }
        });

        document.querySelectorAll('[data-role-badge-label-en]').forEach((badgeElement) => {
            const labelEn = badgeElement.getAttribute('data-role-badge-label-en') || '';
            const labelId = badgeElement.getAttribute('data-role-badge-label-id') || labelEn;
            const labelTarget = badgeElement.querySelector('[data-role-badge-text]');

            if (labelTarget) {
                labelTarget.innerText = resolvedLang === 'id' ? labelId : labelEn;
            }
        });
    };

    const bindLanguageButtons = () => {
        if (langIdButton) {
            langIdButton.addEventListener('click', () => {
                applyLanguage('id');
                updateServerLanguage('id');
            });
        }

        if (langEnButton) {
            langEnButton.addEventListener('click', () => {
                applyLanguage('en');
                updateServerLanguage('en');
            });
        }
    };

    const initializeLanguage = async () => {
        const localLanguage = window.localStorage.getItem('portal_lang');
        if (localLanguage === 'id' || localLanguage === 'en') {
            applyLanguage(localLanguage);
            updateServerLanguage(localLanguage);
        } else {
            const serverLanguage = await loadServerLanguage();
            applyLanguage(serverLanguage || 'id');
        }
    };

    const startWibClock = () => {
        if (!clockElement) {
            return;
        }

        const renderClock = () => {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                timeZone: 'Asia/Jakarta',
                hour12: false
            });
            clockElement.innerText = timeString + ' WIB';
        };

        renderClock();
        window.setInterval(renderClock, 1000);
    };

    bindLanguageButtons();
    setupDynamicViewport();
    initializeLanguage();
    startWibClock();
});
</script>
@endpush

@endsection
