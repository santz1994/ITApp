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
            'recentTickets' => $this->portalRepository->getRecentTicketsForUser($user),
            'ticketStatusBreakdown' => $this->portalRepository->getTicketStatusBreakdownForUser($user),
            'meetingStatusBreakdown' => $this->portalRepository->getMeetingStatusBreakdownForUser($user),
            'recentMeetingBookings' => $this->portalRepository->getRecentMeetingBookingsForUser($user),
            'recentAssetRequests' => $this->portalRepository->getRecentAssetRequestsForUser($user),
            'workspaceContext' => $this->portalRepository->getUserWorkspaceContext($user),
            'roleHighlights' => $this->buildRoleHighlights($user, $metrics),
            'primaryRoleBadge' => $primaryRoleBadge,
            'roleSetBadges' => $roleSetBadges,
            'userRoleNames' => user_get_role_names($user)->values()->all(),
            'primaryRoleLabel' => $primaryRoleBadge['label_en'] ?? $this->formatPrimaryRole($user),
            'jakartaNow' => now('Asia/Jakarta'),
            'subtitle' => $this->buildSubtitle($user),
            'assetMetricLabel' => $isStandardUser ? 'My Assets' : 'Total Assets',
        ];
    }

    private function resolveModules(User $user, array $metrics): array
    {
        $isStandardUser = user_has_role($user, 'user');

        $modules = [
            [
                'key' => 'it_support',
                'title' => 'IT Support Module',
                'subtitle' => 'Tiket dan Dukungan Pengguna',
                'description' => 'Create, monitor, and resolve IT support tickets with clear SLA visibility.',
                'icon' => 'fa-life-ring',
                'theme' => 'primary',
                'url' => $this->routeFirstAvailable([
                    $isStandardUser ? 'tickets.user-index' : 'tickets.index',
                    'tickets.index',
                ]),
                'stat' => $metrics['open_tickets'] ?? 0,
                'stat_label' => 'Open Tickets',
                'roles' => [],
            ],
            [
                'key' => 'meeting_room',
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
                'key' => 'assets_management',
                'title' => 'Assets Management',
                'subtitle' => 'Inventaris dan Maintenance',
                'description' => 'Track company assets, ownership, lifecycle, and maintenance workload.',
                'icon' => 'fa-cubes',
                'theme' => 'info',
                'url' => $this->routeFirstAvailable([
                    $isStandardUser ? 'assets.user-index' : 'assets.index',
                    'assets.index',
                ]),
                'stat' => $metrics['total_assets'] ?? 0,
                'stat_label' => $isStandardUser ? 'My Assets' : 'Total Assets',
                'roles' => [],
            ],
            [
                'key' => 'purchase_request',
                'title' => 'Purchase Request',
                'subtitle' => 'Permintaan Pengadaan',
                'description' => 'Submit and monitor procurement requests linked to asset operations.',
                'icon' => 'fa-shopping-cart',
                'theme' => 'warning',
                'url' => $this->routeFirstAvailable(['purchase-requests.index', 'asset-requests.index']),
                'stat' => $metrics['pending_requests'] ?? 0,
                'stat_label' => 'Pending Requests',
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
                'title' => 'Settings & AI',
                'subtitle' => 'Konfigurasi Sistem',
                'description' => 'Configure ticket, asset, and system-level settings securely.',
                'icon' => 'fa-cogs',
                'theme' => 'primary',
                'url' => $this->routeOrFallback('system-settings.index'),
                'stat' => null,
                'stat_label' => null,
                'roles' => ['administrator', 'developer'],
            ],
            [
                'key' => 'kpi_dashboard',
                'title' => 'KPI Dashboard',
                'subtitle' => 'Monitoring Kinerja',
                'description' => 'View KPI metrics for operational and management decision making.',
                'icon' => 'fa-line-chart',
                'theme' => 'success',
                'url' => $this->routeOrFallback('kpi.dashboard'),
                'stat' => $metrics['pending_meeting_approvals'] ?? 0,
                'stat_label' => 'Pending Approvals',
                'roles' => ['director', 'administrator', 'developer'],
            ],
            [
                'key' => 'lcd_screen',
                'title' => 'LCD Screen',
                'subtitle' => 'Live Meeting Display',
                'description' => 'Open public meeting room display for reception and hallway screens.',
                'icon' => 'fa-television',
                'theme' => 'info',
                'url' => $this->routeOrFallback('meeting-room-bookings.lcd-dashboard'),
                'stat' => null,
                'stat_label' => null,
                'roles' => ['receptionist', 'administrator', 'developer', 'director'],
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
            'tickets' => $this->appendWorkspaceContext(
                $this->routeFirstAvailable([
                    $isStandardUser ? 'tickets.user-index' : 'tickets.index',
                    'tickets.index',
                ]),
                'it_support'
            ),
            'tickets_unassigned' => $this->appendWorkspaceContext($this->resolveTicketQueueUrl($user), 'it_support'),
            'meeting_rooms' => $this->appendWorkspaceContext($this->routeOrFallback('meeting-room-bookings.index'), 'meeting_room'),
            'purchase_requests' => $this->appendWorkspaceContext(
                $this->routeFirstAvailable(['purchase-requests.index', 'asset-requests.index']),
                'purchase_request'
            ),
            'assets' => $this->appendWorkspaceContext(
                $this->routeFirstAvailable([
                    $isStandardUser ? 'assets.user-index' : 'assets.index',
                    'assets.index',
                ]),
                'assets_management'
            ),
        ];
    }

    private function buildQuickLinkOptions(array $quickLinks): array
    {
        $definitions = [
            [
                'key' => 'tickets',
                'label' => 'Open Tickets',
                'label_id' => 'Tiket Terbuka',
                'icon' => 'fa-life-ring',
            ],
            [
                'key' => 'tickets_unassigned',
                'label' => 'Unassigned Tickets',
                'label_id' => 'Tiket Belum Ditugaskan',
                'icon' => 'fa-bolt',
            ],
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
            [
                'key' => 'assets',
                'label' => 'Assets',
                'label_id' => 'Aset',
                'icon' => 'fa-cubes',
            ],
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
            [
                'key' => 'tickets',
                'label' => 'Ticket Action Queue',
                'pending_count' => (int) ($metrics['approval_center_ticket_queue'] ?? $metrics['unassigned_open_tickets'] ?? 0),
                'description' => 'Unassigned open tickets waiting for technician pickup.',
                'url' => $this->appendWorkspaceContext($this->resolveTicketQueueUrl($user), 'it_support'),
                'action_label' => 'Review Tickets',
                'theme' => 'aqua',
                'icon' => 'fa-life-ring',
            ],
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
                $this->highlight('Open Tickets', $metrics['open_tickets'] ?? 0, 'fa-life-ring', 'yellow'),
                $this->highlight('Pending Requests', $metrics['pending_requests'] ?? 0, 'fa-shopping-cart', 'blue'),
            ];
        }

        if (user_has_any_role($user, ['administrator', 'developer'])) {
            return [
                $this->highlight('Assigned Open Tickets', $metrics['assigned_open_tickets'] ?? 0, 'fa-user-circle', 'aqua'),
                $this->highlight('Open Tickets', $metrics['open_tickets'] ?? 0, 'fa-life-ring', 'yellow'),
                $this->highlight('Pending Meeting Approvals', $metrics['pending_meeting_approvals'] ?? 0, 'fa-clock-o', 'orange'),
                $this->highlight('Pending Requests', $metrics['pending_requests'] ?? 0, 'fa-shopping-cart', 'blue'),
            ];
        }

        if (user_has_any_role($user, ['director'])) {
            return [
                $this->highlight('Open Tickets', $metrics['open_tickets'] ?? 0, 'fa-line-chart', 'aqua'),
                $this->highlight('Pending Approvals', $metrics['pending_meeting_approvals'] ?? 0, 'fa-check-square-o', 'orange'),
                $this->highlight('Meetings Today', $metrics['meetings_today'] ?? 0, 'fa-calendar-check-o', 'green'),
                $this->highlight('Approved Requests (Month)', $metrics['approved_requests_month'] ?? 0, 'fa-check', 'blue'),
            ];
        }

        return [
            $this->highlight('My Open Tickets', $metrics['open_tickets'] ?? 0, 'fa-ticket', 'aqua'),
            $this->highlight('My Pending Requests', $metrics['pending_requests'] ?? 0, 'fa-shopping-basket', 'yellow'),
            $this->highlight('Meetings Today', $metrics['meetings_today'] ?? 0, 'fa-calendar', 'green'),
            $this->highlight('My Assets', $metrics['total_assets'] ?? 0, 'fa-cubes', 'blue'),
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
            'kpi_dashboard' => 'kpi',
            'lcd_screen' => 'meeting_room',
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

    private function resolveTicketQueueUrl(User $user): string
    {
        if (user_has_any_role($user, ['administrator', 'developer'])) {
            return $this->routeFirstAvailable(['tickets.unassigned', 'tickets.index']);
        }

        return $this->routeFirstAvailable([
            user_has_role($user, 'user') ? 'tickets.user-index' : 'tickets.index',
            'tickets.index',
        ]);
    }

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
