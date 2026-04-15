<?php

namespace App\Repositories\Portal;

use App\Asset;
use App\AssetRequest;
use App\MeetingRoomBooking;
use App\Ticket;
use App\TicketsStatus;
use App\User;
use Illuminate\Support\Collection;

class MainPortalRepository
{
    /**
     * Build lightweight dashboard metrics for the main portal.
     */
    public function getMetricsForUser(User $user): array
    {
        $isPersonalScope = $this->shouldUsePersonalScope($user);
        $closedStatusIds = $this->getClosedStatusIds();
        $todayWib = now('Asia/Jakarta')->toDateString();

        $openTickets = $this->safeCount(function () use ($isPersonalScope, $user, $closedStatusIds) {
            $query = Ticket::query();

            if ($isPersonalScope) {
                $query->where('user_id', $user->id);
            }

            return $query->whereNotIn('ticket_status_id', $closedStatusIds)->count();
        });

        $meetingsToday = $this->safeCount(function () use ($isPersonalScope, $user, $todayWib) {
            $query = MeetingRoomBooking::query()
                ->whereDate('start_datetime', $todayWib)
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
            $query = AssetRequest::query()->where('status', 'pending');

            if ($isPersonalScope) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('requested_by', $user->id)
                        ->orWhere('user_id', $user->id);
                });
            }

            return $query->count();
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

        return [
            'open_tickets' => $openTickets,
            'meetings_today' => $meetingsToday,
            'total_assets' => $totalAssets,
            'pending_requests' => $pendingRequests,
            'pending_meeting_approvals' => $pendingMeetingApprovals,
            'active_users' => $activeUsers,
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
