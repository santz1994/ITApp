<?php

namespace App\Repositories\PurchaseRequest;

use App\AssetRequest;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PurchaseRequestRepository
{
    /**
     * Build summary metrics for purchase request module dashboard.
     */
    public function getSummaryForUser(User $user): array
    {
        $monthStartWib = now('Asia/Jakarta')->startOfMonth()->toDateTimeString();

        $totalRequests = $this->safeCount(function () use ($user) {
            return $this->scopedQuery($user)->count();
        });

        $pendingRequests = $this->safeCount(function () use ($user) {
            return $this->scopedQuery($user)
                ->where('status', 'pending')
                ->count();
        });

        $approvedThisMonth = $this->safeCount(function () use ($user, $monthStartWib) {
            return $this->scopedQuery($user)
                ->where('status', 'approved')
                ->where('created_at', '>=', $monthStartWib)
                ->count();
        });

        $fulfilledThisMonth = $this->safeCount(function () use ($user, $monthStartWib) {
            return $this->scopedQuery($user)
                ->where('status', 'fulfilled')
                ->where('created_at', '>=', $monthStartWib)
                ->count();
        });

        $rejectedThisMonth = $this->safeCount(function () use ($user, $monthStartWib) {
            return $this->scopedQuery($user)
                ->where('status', 'rejected')
                ->where('created_at', '>=', $monthStartWib)
                ->count();
        });

        return [
            'total' => $totalRequests,
            'pending' => $pendingRequests,
            'approved_month' => $approvedThisMonth,
            'fulfilled_month' => $fulfilledThisMonth,
            'rejected_month' => $rejectedThisMonth,
        ];
    }

    /**
     * Recent purchase requests for module table.
     */
    public function getRecentRequestsForUser(User $user, int $limit = 12): Collection
    {
        try {
            return $this->scopedQuery($user)
                ->with(['assetType', 'requestedBy', 'approvedBy'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * Status breakdown map for compact overview widgets.
     */
    public function getStatusBreakdownForUser(User $user): array
    {
        $statuses = ['pending', 'approved', 'rejected', 'fulfilled'];
        $result = [];

        foreach ($statuses as $status) {
            $result[$status] = $this->safeCount(function () use ($user, $status) {
                return $this->scopedQuery($user)
                    ->where('status', $status)
                    ->count();
            });
        }

        return $result;
    }

    private function scopedQuery(User $user): Builder
    {
        $query = AssetRequest::query();

        if (!$this->canViewAll($user)) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('requested_by', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        }

        return $query;
    }

    private function canViewAll(User $user): bool
    {
        return user_has_any_role($user, ['admin', 'super-admin']);
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
