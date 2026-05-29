<?php

namespace App\Services;

use App\Repositories\Dashboard\DashboardRepository;
use App\User;

class DashboardService
{
    protected ?DashboardRepository $dashboardRepository;
    protected $assetService;

    public function __construct(?DashboardRepository $dashboardRepository = null, $assetService = null)
    {
        $this->dashboardRepository = $dashboardRepository;
        $this->assetService = $assetService;
    }

    public function buildDashboardData(User $user = null): array
    {
        $stats = $this->dashboardRepository?->getDashboardStats() ?? [];

        return [
            'stats' => $stats,
            'recentTickets' => $this->dashboardRepository?->getRecentItems(5) ?? collect(),
            'assetStats' => ['total_assets' => $stats['total_inventory_items'] ?? 0],
            'maintenanceDue' => collect(),
        ];
    }
}