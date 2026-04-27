<?php

namespace App\Services;

use App\Repositories\Dashboard\DashboardRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    protected DashboardRepository $dashboardRepository;

    protected AssetService $assetService;

    public function __construct(DashboardRepository $dashboardRepository, AssetService $assetService)
    {
        $this->dashboardRepository = $dashboardRepository;
        $this->assetService = $assetService;
    }

    /**
     * Build payload consumed by integrated dashboard view.
     *
     * @return array<string, mixed>
     */
    public function buildDashboardData(): array
    {
        $ticketStats = Cache::remember(
            'dashboard.ticket-stats.v1',
            now('Asia/Jakarta')->addMinutes(2),
            fn () => $this->dashboardRepository->getTicketStats()
        );

        $recentTickets = Cache::remember(
            'dashboard.recent-tickets.v1.limit-10',
            now('Asia/Jakarta')->addMinutes(1),
            fn () => $this->dashboardRepository->getRecentTickets(10)
        );

        $assetStats = $this->assetService->getAssetStatistics();
        $maintenanceDue = $this->assetService->getAssetsNeedingMaintenance();

        $stats = array_merge($ticketStats, [
            'total_assets' => (int) ($assetStats['total'] ?? 0),
            'maintenance_due' => $this->resolveMaintenanceDueCount($maintenanceDue),
        ]);

        return [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'assetStats' => $assetStats,
            'maintenanceDue' => $maintenanceDue,
        ];
    }

    /**
     * @param mixed $maintenanceDue
     */
    protected function resolveMaintenanceDueCount($maintenanceDue): int
    {
        if ($maintenanceDue instanceof Collection) {
            return $maintenanceDue->count();
        }

        if (is_countable($maintenanceDue)) {
            return count($maintenanceDue);
        }

        return 0;
    }
}