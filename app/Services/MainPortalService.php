<?php

namespace App\Services;

use App\Repositories\Portal\MainPortalRepository;
use App\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MainPortalService
{
    protected MainPortalRepository $portalRepository;

    public function __construct(MainPortalRepository $portalRepository)
    {
        $this->portalRepository = $portalRepository;
    }

    /**
     * Compose all data required by the main portal view.
     */
    public function buildPortalData(User $user): array
    {
        $metrics = $this->portalRepository->getMetricsForUser($user);

        return [
            'metrics' => $metrics,
            'modules' => $this->resolveModules($user, $metrics),
            'recentTickets' => $this->portalRepository->getRecentTicketsForUser($user),
            'userRoleNames' => user_get_role_names($user)->values()->all(),
            'primaryRoleLabel' => $this->formatPrimaryRole($user),
            'jakartaNow' => now('Asia/Jakarta'),
            'subtitle' => $this->buildSubtitle($user),
        ];
    }

    private function resolveModules(User $user, array $metrics): array
    {
        $isStandardUser = user_has_role($user, 'user');

        $modules = [
            [
                'title' => 'IT Support Module',
                'subtitle' => 'Tiket dan Dukungan Pengguna',
                'description' => 'Create, monitor, and resolve IT support tickets with clear SLA visibility.',
                'icon' => 'fa-life-ring',
                'theme' => 'primary',
                'url' => $this->routeOrFallback($isStandardUser ? 'tickets.user-index' : 'tickets.index'),
                'stat' => $metrics['open_tickets'] ?? 0,
                'stat_label' => 'Open Tickets',
                'roles' => [],
            ],
            [
                'title' => 'Meeting Room',
                'subtitle' => 'Booking dan Jadwal',
                'description' => 'Book rooms, check calendar availability, and manage approvals.',
                'icon' => 'fa-calendar-check-o',
                'theme' => 'success',
                'url' => $this->routeOrFallback('meeting-room-bookings.index'),
                'stat' => $metrics['meetings_today'] ?? 0,
                'stat_label' => 'Meetings Today',
                'roles' => [],
            ],
            [
                'title' => 'Assets Management',
                'subtitle' => 'Inventaris dan Maintenance',
                'description' => 'Track company assets, ownership, lifecycle, and maintenance workload.',
                'icon' => 'fa-cubes',
                'theme' => 'info',
                'url' => $this->routeOrFallback($isStandardUser ? 'assets.user-index' : 'assets.index'),
                'stat' => $metrics['total_assets'] ?? 0,
                'stat_label' => $isStandardUser ? 'My Assets' : 'Total Assets',
                'roles' => [],
            ],
            [
                'title' => 'Purchase Request',
                'subtitle' => 'Permintaan Pengadaan',
                'description' => 'Submit and monitor procurement requests linked to asset operations.',
                'icon' => 'fa-shopping-cart',
                'theme' => 'warning',
                'url' => $this->routeOrFallback('asset-requests.index'),
                'stat' => $metrics['pending_requests'] ?? 0,
                'stat_label' => 'Pending Requests',
                'roles' => [],
            ],
            [
                'title' => 'Profile',
                'subtitle' => 'Akun dan Preferensi',
                'description' => 'Manage personal profile, password, and notification preferences.',
                'icon' => 'fa-user-circle',
                'theme' => 'default',
                'url' => $this->routeOrFallback('profile.edit'),
                'stat' => null,
                'stat_label' => null,
                'roles' => [],
            ],
            [
                'title' => 'User Management',
                'subtitle' => 'Role dan Permission',
                'description' => 'Create users, manage access levels, and maintain role permissions.',
                'icon' => 'fa-users',
                'theme' => 'danger',
                'url' => $this->routeOrFallback('users.index'),
                'stat' => $metrics['active_users'] ?? 0,
                'stat_label' => 'Active Users',
                'roles' => ['admin', 'super-admin', 'developer'],
            ],
            [
                'title' => 'Settings',
                'subtitle' => 'Konfigurasi Sistem',
                'description' => 'Configure ticket, asset, and system-level settings securely.',
                'icon' => 'fa-cogs',
                'theme' => 'primary',
                'url' => $this->routeOrFallback('system-settings.index'),
                'stat' => null,
                'stat_label' => null,
                'roles' => ['admin', 'super-admin', 'developer'],
            ],
            [
                'title' => 'KPI Dashboard',
                'subtitle' => 'Monitoring Kinerja',
                'description' => 'View KPI metrics for operational and management decision making.',
                'icon' => 'fa-line-chart',
                'theme' => 'success',
                'url' => $this->routeOrFallback('kpi.dashboard'),
                'stat' => $metrics['pending_meeting_approvals'] ?? 0,
                'stat_label' => 'Pending Approvals',
                'roles' => ['director', 'management', 'admin', 'super-admin'],
            ],
            [
                'title' => 'LCD Screen',
                'subtitle' => 'Live Meeting Display',
                'description' => 'Open public meeting room display for reception and hallway screens.',
                'icon' => 'fa-television',
                'theme' => 'info',
                'url' => $this->routeOrFallback('meeting-room-bookings.lcd-dashboard'),
                'stat' => null,
                'stat_label' => null,
                'roles' => ['receptionist', 'admin', 'super-admin', 'director'],
            ],
        ];

        $availableModules = [];
        foreach ($modules as $module) {
            if (!$this->isAllowedForRoles($user, $module['roles'])) {
                continue;
            }

            if ($module['url'] === '#') {
                continue;
            }

            $availableModules[] = $module;
        }

        return $availableModules;
    }

    private function routeOrFallback(string $routeName): string
    {
        return Route::has($routeName) ? route($routeName) : '#';
    }

    private function isAllowedForRoles(User $user, array $roles): bool
    {
        if (empty($roles)) {
            return true;
        }

        return user_has_any_role($user, $roles);
    }

    private function formatPrimaryRole(User $user): string
    {
        $firstRole = user_get_role_names($user)->first();
        if (!$firstRole) {
            return 'User';
        }

        return Str::title(str_replace('-', ' ', (string) $firstRole));
    }

    private function buildSubtitle(User $user): string
    {
        if (user_has_role($user, 'receptionist')) {
            return 'Meeting room operations, live status, and daily support shortcuts.';
        }

        if (user_has_any_role($user, ['director', 'management'])) {
            return 'Overview of operational metrics, approvals, and cross-module activity.';
        }

        if (user_has_any_role($user, ['admin', 'super-admin'])) {
            return 'Unified operational command center for support, assets, governance, and reporting.';
        }

        return 'Your central workspace for tickets, bookings, requests, and profile tools.';
    }
}
