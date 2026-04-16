<?php

namespace App\Repositories\Portal;

use App\Asset;
use App\AssetRequest;
use App\MeetingRoomBooking;
use App\Ticket;
use App\TicketsStatus;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MainPortalRepository
{
    /**
     * Build lightweight dashboard metrics for the main portal.
     */
    public function getMetricsForUser(User $user): array
    {
        $isPersonalScope = $this->shouldUsePersonalScope($user);
        $canViewAllAssetRequests = $this->canViewAllAssetRequests($user);
        $closedStatusIds = $this->getClosedStatusIds();
        $todayWib = now('Asia/Jakarta');
        $upcomingWindowWib = $todayWib->copy()->addDays(7);
        $monthStartWib = now('Asia/Jakarta')->startOfMonth()->toDateTimeString();

        $openTickets = $this->safeCount(function () use ($isPersonalScope, $user, $closedStatusIds) {
            $query = Ticket::query();

            if ($isPersonalScope) {
                $query->where('user_id', $user->id);
            }

            return $query->whereNotIn('ticket_status_id', $closedStatusIds)->count();
        });

        $meetingsToday = $this->safeCount(function () use ($isPersonalScope, $user, $todayWib) {
            $query = MeetingRoomBooking::query()
                ->whereDate('start_datetime', $todayWib->toDateString())
                ->whereIn('status', ['pending', 'approved']);

            if ($isPersonalScope) {
                $query->where('user_id', $user->id);
            }

            return $query->count();
        });

        $totalAssets = $this->safeCount(function () use ($isPersonalScope, $user) {
            if ($isPersonalScope) {
                return Asset::query()->where('assigned_to', $user->id)->count();
            }

            return Asset::query()->count();
        });

        $pendingRequests = $this->safeCount(function () use ($isPersonalScope, $user) {
            return $this->assetRequestScopedQuery($user)
                ->where('status', 'pending')
                ->count();
        });

        $pendingMeetingApprovals = $this->safeCount(function () use ($user) {
            if (!user_has_any_role($user, ['director', 'admin', 'super-admin', 'management'])) {
                return 0;
            }

            return MeetingRoomBooking::query()->where('status', 'pending')->count();
        });

        $activeUsers = $this->safeCount(function () use ($user) {
            if (!user_has_any_role($user, ['admin', 'super-admin'])) {
                return 0;
            }

            return User::query()->where('is_active', true)->count();
        });

        $assignedOpenTickets = $this->safeCount(function () use ($user, $closedStatusIds) {
            return Ticket::query()
                ->where('assigned_to', $user->id)
                ->whereNotIn('ticket_status_id', $closedStatusIds)
                ->count();
        });

        $upcomingMeetings7Days = $this->safeCount(function () use ($isPersonalScope, $user, $todayWib, $upcomingWindowWib) {
            $query = MeetingRoomBooking::query()
                ->whereBetween('start_datetime', [$todayWib->toDateTimeString(), $upcomingWindowWib->toDateTimeString()])
                ->whereIn('status', ['pending', 'approved']);

            if ($isPersonalScope) {
                $query->where('user_id', $user->id);
            }

            return $query->count();
        });

        $totalRequests = $this->safeCount(function () use ($user) {
            return $this->assetRequestScopedQuery($user)->count();
        });

        $approvedRequestsMonth = $this->safeCount(function () use ($user, $monthStartWib) {
            return $this->assetRequestScopedQuery($user)
                ->where('status', 'approved')
                ->where('created_at', '>=', $monthStartWib)
                ->count();
        });

        $fulfilledRequestsMonth = $this->safeCount(function () use ($user, $monthStartWib) {
            return $this->assetRequestScopedQuery($user)
                ->where('status', 'fulfilled')
                ->where('created_at', '>=', $monthStartWib)
                ->count();
        });

        $rejectedRequestsMonth = $this->safeCount(function () use ($user, $monthStartWib) {
            return $this->assetRequestScopedQuery($user)
                ->where('status', 'rejected')
                ->where('created_at', '>=', $monthStartWib)
                ->count();
        });

        return [
            'open_tickets' => $openTickets,
            'meetings_today' => $meetingsToday,
            'total_assets' => $totalAssets,
            'pending_requests' => $pendingRequests,
            'pending_meeting_approvals' => $pendingMeetingApprovals,
            'active_users' => $activeUsers,
            'assigned_open_tickets' => $assignedOpenTickets,
            'upcoming_meetings_7d' => $upcomingMeetings7Days,
            'total_requests' => $totalRequests,
            'approved_requests_month' => $approvedRequestsMonth,
            'fulfilled_requests_month' => $fulfilledRequestsMonth,
            'rejected_requests_month' => $rejectedRequestsMonth,
            'can_view_all_asset_requests' => $canViewAllAssetRequests,
            'is_personal_scope' => $isPersonalScope,
        ];
    }

    /**
     * Fetch recent tickets to show a quick operational snapshot.
     */
    public function getRecentTicketsForUser(User $user, int $limit = 6): Collection
    {
        try {
            $query = Ticket::query()
                ->with(['user', 'ticket_status', 'ticket_priority'])
                ->orderBy('created_at', 'desc');

            if ($this->shouldUsePersonalScope($user)) {
                $query->where('user_id', $user->id);
            }

            return $query->take($limit)->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * Fetch recent purchase requests for quick portal snapshot.
     */
    public function getRecentAssetRequestsForUser(User $user, int $limit = 6): Collection
    {
        try {
            return $this->assetRequestScopedQuery($user)
                ->with(['assetType', 'requestedBy', 'approvedBy'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * Ticket status breakdown to support IT Support widget.
     */
    public function getTicketStatusBreakdownForUser(User $user): array
    {
        $closedStatusIds = $this->getClosedStatusIds();

        $openCount = $this->safeCount(function () use ($user, $closedStatusIds) {
            return $this->ticketScopedQuery($user)
                ->whereNotIn('ticket_status_id', $closedStatusIds)
                ->count();
        });

        $assignedOpenCount = $this->safeCount(function () use ($user, $closedStatusIds) {
            return $this->ticketScopedQuery($user)
                ->where('assigned_to', $user->id)
                ->whereNotIn('ticket_status_id', $closedStatusIds)
                ->count();
        });

        $urgentOpenCount = $this->safeCount(function () use ($user, $closedStatusIds) {
            return $this->ticketScopedQuery($user)
                ->whereNotIn('ticket_status_id', $closedStatusIds)
                ->whereHas('ticket_priority', function (Builder $priorityQuery) {
                    $priorityQuery->whereIn('priority', ['Urgent', 'High', 'urgent', 'high']);
                })
                ->count();
        });

        $resolvedCount = $this->safeCount(function () use ($user) {
            return $this->ticketScopedQuery($user)
                ->whereHas('ticket_status', function (Builder $statusQuery) {
                    $statusQuery->whereIn('status', ['Resolved', 'resolved']);
                })
                ->count();
        });

        $closedCount = $this->safeCount(function () use ($user) {
            return $this->ticketScopedQuery($user)
                ->whereHas('ticket_status', function (Builder $statusQuery) {
                    $statusQuery->whereIn('status', ['Closed', 'closed']);
                })
                ->count();
        });

        return [
            'open' => $openCount,
            'assigned_open' => $assignedOpenCount,
            'urgent_open' => $urgentOpenCount,
            'resolved' => $resolvedCount,
            'closed' => $closedCount,
        ];
    }

    /**
     * Meeting booking status breakdown to support Meeting Room widget.
     */
    public function getMeetingStatusBreakdownForUser(User $user): array
    {
        $statuses = ['pending', 'approved', 'rejected', 'finished', 'cancelled'];
        $breakdown = [];

        foreach ($statuses as $status) {
            $breakdown[$status] = $this->safeCount(function () use ($user, $status) {
                return $this->meetingScopedQuery($user)
                    ->where('status', $status)
                    ->count();
            });
        }

        return $breakdown;
    }

    /**
     * Recent meeting bookings for Meeting Room widget.
     */
    public function getRecentMeetingBookingsForUser(User $user, int $limit = 6): Collection
    {
        try {
            return $this->meetingScopedQuery($user)
                ->with(['user', 'approver'])
                ->orderBy('start_datetime', 'desc')
                ->take($limit)
                ->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * Get user workspace context data for richer portal header.
     */
    public function getUserWorkspaceContext(User $user): array
    {
        try {
            $user->loadMissing(['division', 'location']);

            $lastLoginWib = $user->last_login_at
                ? $user->last_login_at->copy()->timezone('Asia/Jakarta')
                : null;

            return [
                'division' => optional($user->division)->name ?: '-',
                'location' => optional($user->location)->location_name
                    ?: (optional($user->location)->office ?: '-'),
                'building' => optional($user->location)->building ?: '-',
                'last_login_wib' => $lastLoginWib,
                'last_login_human' => $lastLoginWib ? $lastLoginWib->diffForHumans() : 'Never',
                'is_active' => (bool) $user->is_active,
            ];
        } catch (\Throwable $exception) {
            return [
                'division' => '-',
                'location' => '-',
                'building' => '-',
                'last_login_wib' => null,
                'last_login_human' => 'Unknown',
                'is_active' => false,
            ];
        }
    }

    private function assetRequestScopedQuery(User $user): Builder
    {
        $query = AssetRequest::query();

        if (!$this->canViewAllAssetRequests($user)) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('requested_by', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        }

        return $query;
    }

    private function ticketScopedQuery(User $user): Builder
    {
        $query = Ticket::query();

        if ($this->shouldUsePersonalScope($user)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function meetingScopedQuery(User $user): Builder
    {
        $query = MeetingRoomBooking::query();

        if ($this->shouldUsePersonalScope($user)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function canViewAllAssetRequests(User $user): bool
    {
        return user_has_any_role($user, ['admin', 'super-admin']);
    }

    private function shouldUsePersonalScope(User $user): bool
    {
        return user_has_role($user, 'user')
            && !user_has_any_role($user, ['admin', 'super-admin', 'management', 'director', 'receptionist']);
    }

    private function getClosedStatusIds(): array
    {
        try {
            $statusIds = TicketsStatus::query()
                ->whereIn('status', ['Closed', 'Resolved'])
                ->pluck('id')
                ->filter()
                ->values()
                ->all();

            if (!empty($statusIds)) {
                return $statusIds;
            }
        } catch (\Throwable $exception) {
            // Fall through to defaults.
        }

        // Keep legacy fallback status id for old datasets.
        return [3];
    }

    private function safeCount(callable $callback): int
    {
        try {
            return (int) $callback();
        } catch (\Throwable $exception) {
            return 0;
        }
    }
}
