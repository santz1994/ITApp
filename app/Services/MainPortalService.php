<?php

namespace App\Services;

use App\Repositories\Portal\MainPortalRepository;
use App\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MainPortalService
{
    protected MainPortalRepository $portalRepository;
    protected UserRoleBadgeService $roleBadgeService;
    protected PortalPreferenceService $preferenceService;

    public function __construct(
        MainPortalRepository $portalRepository, 
        UserRoleBadgeService $roleBadgeService,
        PortalPreferenceService $preferenceService
    ) {
        $this->portalRepository = $portalRepository;
        $this->roleBadgeService = $roleBadgeService;
        $this->preferenceService = $preferenceService;
    }

    /**
     * Compose all data required by the main portal view.
     */
    public function buildPortalData(User $user): array
    {
        $portalPreferences = $this->preferenceService->loadPreferences($user);
        $metrics = $this->portalRepository->getMetricsForUser($user);
        $isStandardUser = $this->isStandardUser($user);
        $modules = $this->resolveModules($user, $metrics);
        $quickLinks = $this->buildQuickLinks($user);
        $primaryRoleBadge = $this->roleBadgeService->resolvePrimaryBadge($user);
        $roleSetBadges = $this->roleBadgeService->resolveRoleSetBadges($user);

        return [
            'metrics' => $metrics,
            'modules' => $modules,
            'quickLinks' => $quickLinks,
            'quickLinkOptions' => $this->buildQuickLinkOptions($quickLinks),
            'approvalCenter' => $this->buildApprovalCenter($user, $metrics),
            'meetingStatusBreakdown' => $this->portalRepository->getMeetingStatusBreakdownForUser($user),
            'recentMeetingBookings' => $this->portalRepository->getRecentMeetingBookingsForUser($user),
            'workspaceContext' => $this->portalRepository->getUserWorkspaceContext($user),
            'roleHighlights' => $this->buildRoleHighlights($user, $metrics),
            'primaryRoleBadge' => $primaryRoleBadge,
            'roleSetBadges' => $roleSetBadges,
            'portalPreferences' => $portalPreferences,
            'userRoleNames' => user_get_role_names($user)->values()->all(),
            'primaryRoleLabel' => $primaryRoleBadge['label_en'] ?? $this->formatPrimaryRole($user),
            'jakartaNow' => now('Asia/Jakarta'),
            'subtitle' => $this->buildSubtitle($user),
            'assetMetricLabel' => 'Inventory',
        ];
    }

    private function resolveModules(User $user, array $metrics): array
    {
        $modules = [
            [
                'key' => 'meeting_room',
                'title' => 'Meeting Room',
                'subtitle' => 'Booking dan Jadwal',
                'description' => 'Reserve meeting rooms, monitor availability, and manage approvals.',
                'icon' => 'fa-calendar-check-o',
                'theme' => 'success',
                'url' => $this->routeOrFallback('meeting-room-bookings.index'),
                'stat' => $metrics['pending_meeting_approvals'] ?? 0,
                'stat_label' => 'Pending Approvals',
                'roles' => [],
            ],
            [
                'key' => 'inventory',
                'title' => 'Inventory',
                'subtitle' => 'ATK dan Sparepart',
                'description' => 'Track stock, low-stock alerts, and inventory requests.',
                'icon' => 'fa-cubes',
                'theme' => 'info',
                'url' => $this->routeOrFallback('inventory.index'),
                'stat' => $metrics['pending_inventory_requests'] ?? 0,
                'stat_label' => 'Pending Requests',
                'roles' => [],
            ],
            [
                'key' => 'vehicle',
                'title' => 'Vehicle Booking',
                'subtitle' => 'Operasional Kendaraan',
                'description' => 'Request vehicles and monitor trip status in one place.',
                'icon' => 'fa-car',
                'theme' => 'warning',
                'url' => $this->routeOrFallback('vehicles.index'),
                'stat' => $metrics['pending_vehicle_bookings'] ?? 0,
                'stat_label' => 'Pending Trips',
                'roles' => [],
            ],
            [
                'key' => 'approvals',
                'title' => 'Approvals',
                'subtitle' => 'Alur Persetujuan',
                'description' => 'Review meeting, inventory, and vehicle approvals.',
                'icon' => 'fa-check-square-o',
                'theme' => 'primary',
                'url' => $this->routeOrFallback('approvals.pending'),
                'stat' => ($metrics['pending_meeting_approvals'] ?? 0) + ($metrics['pending_inventory_requests'] ?? 0) + ($metrics['pending_vehicle_bookings'] ?? 0),
                'stat_label' => 'Pending Items',
                'roles' => [],
            ],
            [
                'key' => 'profile',
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
                'key' => 'user_management',
                'title' => 'User Management',
                'subtitle' => 'Role dan Permission',
                'description' => 'Create users, manage access levels, and maintain role permissions.',
                'icon' => 'fa-users',
                'theme' => 'danger',
                'url' => $this->routeOrFallback('users.index'),
                'stat' => $metrics['active_users'] ?? 0,
                'stat_label' => 'Active Users',
                'roles' => ['administrator', 'developer'],
            ],
            [
                'key' => 'settings',
                'title' => 'Settings',
                'subtitle' => 'Konfigurasi Sistem',
                'description' => 'Configure system settings, roles, and menus securely.',
                'icon' => 'fa-cogs',
                'theme' => 'primary',
                'url' => $this->routeOrFallback('system-settings.index'),
                'stat' => null,
                'stat_label' => null,
                'roles' => ['administrator', 'developer'],
            ],
            [
                'key' => 'reporting',
                'title' => 'Reporting',
                'subtitle' => 'Monitoring Kinerja',
                'description' => 'View operational reporting and analytics for bookings and requests.',
                'icon' => 'fa-line-chart',
                'theme' => 'success',
                'url' => $this->routeOrFallback('home'),
                'stat' => $metrics['meetings_today'] ?? 0,
                'stat_label' => 'Meetings Today',
                'roles' => ['director', 'administrator', 'developer'],
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

            $workspaceKey = $this->resolveWorkspaceKeyForModule((string) ($module['key'] ?? ''));
            $module['url'] = $this->appendWorkspaceContext((string) $module['url'], $workspaceKey);

            $availableModules[] = $module;
        }

        return $availableModules;
    }

    private function buildQuickLinks(User $user): array
    {
        $isStandardUser = $this->isStandardUser($user);

        return [
            'meeting_rooms' => $this->appendWorkspaceContext($this->routeOrFallback('meeting-room-bookings.index'), 'meeting_room'),
            'purchase_requests' => $this->appendWorkspaceContext(
                $this->routeFirstAvailable(['purchase-requests.index', 'asset-requests.index']),
                'purchase_request'
            ),
            // assets quicklink removed (assets module deleted)
        ];
    }

    private function buildQuickLinkOptions(array $quickLinks): array
    {
        $definitions = [
            [
                'key' => 'meeting_rooms',
                'label' => 'Meeting Rooms',
                'label_id' => 'Ruang Rapat',
                'icon' => 'fa-calendar-check-o',
            ],
            [
                'key' => 'purchase_requests',
                'label' => 'Purchase Requests',
                'label_id' => 'Permintaan Pengadaan',
                'icon' => 'fa-shopping-cart',
            ],
            // assets quicklink option removed (assets module deleted)
        ];

        $options = [];

        foreach ($definitions as $definition) {
            $url = (string) ($quickLinks[$definition['key']] ?? '#');

            if ($url === '#') {
                continue;
            }

            $options[] = [
                'key' => $definition['key'],
                'label' => $definition['label'],
                'label_id' => $definition['label_id'],
                'icon' => $definition['icon'],
                'url' => $url,
            ];
        }

        return $options;
    }

    private function buildApprovalCenter(User $user, array $metrics): array
    {
        if (!user_has_any_role($user, ['administrator', 'developer', 'director'])) {
            return [
                'enabled' => false,
                'total_pending' => 0,
                'items' => [],
            ];
        }

        $items = [
            // Tickets approval queue removed (legacy)
            [
                'key' => 'meeting',
                'label' => 'Meeting Approval Queue',
                'pending_count' => (int) ($metrics['approval_center_meeting_queue'] ?? $metrics['pending_meeting_approvals'] ?? 0),
                'description' => 'Meeting room bookings waiting for approval decisions.',
                'url' => $this->appendWorkspaceContext(
                    $this->routeWithQueryOrFallback(['meeting-room-bookings.index'], ['status' => 'pending']),
                    'meeting_room'
                ),
                'action_label' => 'Review Bookings',
                'theme' => 'orange',
                'icon' => 'fa-calendar-check-o',
            ],
            [
                'key' => 'purchase',
                'label' => 'Purchase Approval Queue',
                'pending_count' => (int) ($metrics['approval_center_purchase_queue'] ?? $metrics['pending_requests'] ?? 0),
                'description' => 'Purchase requests pending validation and approval workflow.',
                'url' => $this->appendWorkspaceContext(
                    $this->routeWithQueryOrFallback(['purchase-requests.index', 'asset-requests.index'], ['status' => 'pending']),
                    'purchase_request'
                ),
                'action_label' => 'Review Requests',
                'theme' => 'blue',
                'icon' => 'fa-shopping-cart',
            ],
        ];

        $items = array_values(array_filter($items, static function (array $item): bool {
            return ($item['url'] ?? '#') !== '#';
        }));

        $totalPending = (int) array_sum(array_map(static function (array $item): int {
            return (int) ($item['pending_count'] ?? 0);
        }, $items));

        return [
            'enabled' => !empty($items),
            'total_pending' => $totalPending,
            'items' => $items,
        ];
    }

    private function buildRoleHighlights(User $user, array $metrics): array
    {
        if (user_has_role($user, 'receptionist')) {
            return [
                $this->highlight('Meetings Today', $metrics['meetings_today'] ?? 0, 'fa-calendar-check-o', 'green'),
                $this->highlight('Upcoming 7 Days', $metrics['upcoming_meetings_7d'] ?? 0, 'fa-calendar', 'aqua'),
                $this->highlight('Pending Requests', $metrics['pending_requests'] ?? 0, 'fa-shopping-cart', 'blue'),
                $this->highlight('Inventory Items', $metrics['total_assets'] ?? 0, 'fa-cubes', 'blue'),
            ];
        }

        if (user_has_any_role($user, ['administrator', 'developer'])) {
            return [
                $this->highlight('Pending Meeting Approvals', $metrics['pending_meeting_approvals'] ?? 0, 'fa-clock-o', 'orange'),
                $this->highlight('Pending Requests', $metrics['pending_requests'] ?? 0, 'fa-shopping-cart', 'blue'),
                $this->highlight('Meetings Today', $metrics['meetings_today'] ?? 0, 'fa-calendar-check-o', 'green'),
                $this->highlight('Inventory Items', $metrics['total_assets'] ?? 0, 'fa-cubes', 'blue'),
            ];
        }

        if (user_has_any_role($user, ['director'])) {
            return [
                $this->highlight('Pending Approvals', $metrics['pending_meeting_approvals'] ?? 0, 'fa-check-square-o', 'orange'),
                $this->highlight('Meetings Today', $metrics['meetings_today'] ?? 0, 'fa-calendar-check-o', 'green'),
                $this->highlight('Approved Requests (Month)', $metrics['approved_requests_month'] ?? 0, 'fa-check', 'blue'),
                $this->highlight('Inventory Items', $metrics['total_assets'] ?? 0, 'fa-cubes', 'blue'),
            ];
        }

        return [
            $this->highlight('My Pending Requests', $metrics['pending_requests'] ?? 0, 'fa-shopping-basket', 'yellow'),
            $this->highlight('Meetings Today', $metrics['meetings_today'] ?? 0, 'fa-calendar', 'green'),
        ];
    }

    private function highlight(string $label, int $value, string $icon, string $theme): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'theme' => $theme,
        ];
    }

    private function resolveWorkspaceKeyForModule(string $moduleKey): string
    {
        return match ($moduleKey) {
            'reporting' => 'kpi',
            'inventory' => 'settings',
            'vehicle' => 'settings',
            'approvals' => 'settings',
            default => $moduleKey,
        };
    }

    private function appendWorkspaceContext(string $url, string $workspaceKey): string
    {
        if ($url === '#' || $workspaceKey === '') {
            return $url;
        }

        if (str_contains($url, 'workspace=')) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query(['workspace' => $workspaceKey]);
    }

    private function routeOrFallback(string $routeName): string
    {
        return Route::has($routeName) ? route($routeName) : '#';
    }

    /**
     * Resolve the first existing route from the provided list.
     */
    private function routeFirstAvailable(array $routeNames): string
    {
        foreach ($routeNames as $routeName) {
            if (empty($routeName)) {
                continue;
            }

            if (Route::has($routeName)) {
                return route($routeName);
            }
        }

        return '#';
    }

    /**
     * Resolve the first existing route and append query parameters.
     */
    private function routeWithQueryOrFallback(array $routeNames, array $query): string
    {
        foreach ($routeNames as $routeName) {
            if (empty($routeName) || !Route::has($routeName)) {
                continue;
            }

            $url = route($routeName);

            return empty($query) ? $url : $url . '?' . http_build_query($query);
        }

        return '#';
    }

    // Ticket queue URL resolver removed (ticket module deleted)

    private function isStandardUser(User $user): bool
    {
        return user_has_role($user, 'user')
            && !user_has_any_role($user, ['administrator', 'developer', 'director', 'receptionist']);
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

        if (user_has_any_role($user, ['director'])) {
            return 'Overview of operational metrics, approvals, and cross-module activity.';
        }

        if (user_has_any_role($user, ['administrator', 'developer'])) {
            return 'Unified operational command center for support, assets, governance, and reporting.';
        }

        return 'Your central workspace for tickets, bookings, requests, and profile tools.';
    }
}
