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
    const apiBaseUrl = ((portalShell && portalShell.getAttribute('data-portal-preferences-url')) || '').replace(/\/$/, '');

    const applyCardAnimationIndexes = () => {
        document.querySelectorAll('.portal-module-col[data-card-index]').forEach((cardElement) => {
            const cardIndex = Number(cardElement.getAttribute('data-card-index') || 0);
            cardElement.style.setProperty('--card-index', String(cardIndex));
        });
    };

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
        if (!apiBaseUrl) {
            return null;
        }

        try {
            const response = await fetch(apiBaseUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json'
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
        if (!apiBaseUrl) {
            return;
        }

        try {
            await fetch(`${apiBaseUrl}/language`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json'
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
            clockElement.innerText = `${timeString} WIB`;
        };

        renderClock();
        window.setInterval(renderClock, 1000);
    };

    applyCardAnimationIndexes();
    bindLanguageButtons();
    setupDynamicViewport();
    initializeLanguage();
    startWibClock();
});
