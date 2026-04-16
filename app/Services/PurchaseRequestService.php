<?php

namespace App\Services;

use App\Repositories\PurchaseRequest\PurchaseRequestRepository;
use App\User;

class PurchaseRequestService
{
    protected PurchaseRequestRepository $purchaseRequestRepository;

    public function __construct(PurchaseRequestRepository $purchaseRequestRepository)
    {
        $this->purchaseRequestRepository = $purchaseRequestRepository;
    }

    /**
     * Compose all data required by modular purchase request dashboard.
     */
    public function buildDashboardData(User $user): array
    {
        return [
            'summary' => $this->purchaseRequestRepository->getSummaryForUser($user),
            'statusBreakdown' => $this->purchaseRequestRepository->getStatusBreakdownForUser($user),
            'recentRequests' => $this->purchaseRequestRepository->getRecentRequestsForUser($user),
            'canApprove' => user_has_any_role($user, ['admin', 'super-admin']),
            'canViewAll' => user_has_any_role($user, ['admin', 'super-admin']),
            'subtitle' => $this->buildSubtitle($user),
            'jakartaNow' => now('Asia/Jakarta'),
        ];
    }

    private function buildSubtitle(User $user): string
    {
        if (user_has_any_role($user, ['admin', 'super-admin'])) {
            return 'Review procurement pipeline, approval queue, and fulfillment health in one module.';
        }

        return 'Track your purchase requests from submission to approval and fulfillment.';
    }
}
