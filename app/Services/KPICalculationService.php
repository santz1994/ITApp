<?php

namespace App\Services;

use App\Ticket;
use App\Asset;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * KPICalculationService - Calculate Key Performance Indicators for IT operations
 * 
 * Provides comprehensive metrics for dashboard and management reporting.
 * Uses caching to optimize performance of expensive calculations.
 */
class KPICalculationService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 15;

    /**
     * Get all KPIs for a given time period
     *
     * @param string $period 'day', 'week', 'month', 'year', or 'all'
     * @param int|null $divisionId Optional division filter
     * @return array
     */
    public function getAllKPIs(string $period = 'month', ?int $divisionId = null): array
    {
        $cacheKey = "kpis_{$period}_division_" . ($divisionId ?? 'all');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($period, $divisionId) {
            $dateRange = $this->getDateRange($period);
            
            return [
                'period' => $period,
                'date_range' => $dateRange,
                'division_id' => $divisionId,
                'metrics' => [
                    'mttr' => $this->calculateMTTR($dateRange, $divisionId),
                    'fcr' => $this->calculateFCR($dateRange, $divisionId),
                    'ticket_backlog' => $this->getTicketBacklog($divisionId),
                    'sla_compliance' => $this->calculateSLACompliance($dateRange, $divisionId),
                    'technician_utilization' => $this->getTechnicianUtilization($dateRange, $divisionId),
                    'support_cost_per_asset' => $this->calculateSupportCostPerAsset($dateRange, $divisionId),
                    'asset_utilization' => $this->calculateAssetUtilization($divisionId),
                ],
                'generated_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Calculate MTTR (Mean Time To Resolution)
     * Average time from ticket creation to closure
     *
     * @param array $dateRange
     * @param int|null $divisionId
     * @return array
     */
    public function calculateMTTR(array $dateRange, ?int $divisionId = null): array
    {
        $query = DB::table('tickets')
            ->selectRaw('
                COUNT(*) as total_tickets,
                AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours,
                MIN(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as min_hours,
                MAX(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as max_hours
            ')
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        $result = $query->first();

        return [
            'avg_hours' => round($result->avg_hours ?? 0, 2),
            'avg_days' => round(($result->avg_hours ?? 0) / 24, 2),
            'min_hours' => round($result->min_hours ?? 0, 2),
            'max_hours' => round($result->max_hours ?? 0, 2),
            'total_resolved' => $result->total_tickets ?? 0,
            'label' => 'Mean Time To Resolution',
            'unit' => 'hours',
        ];
    }

    /**
     * Calculate FCR (First Contact Resolution)
     * Percentage of tickets resolved without escalation/reopening
     *
     * @param array $dateRange
     * @param int|null $divisionId
     * @return array
     */
    public function calculateFCR(array $dateRange, ?int $divisionId = null): array
    {
        // Count total closed tickets
        $totalQuery = DB::table('tickets')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($divisionId) {
            $totalQuery->where('division_id', $divisionId);
        }

        $totalTickets = $totalQuery->where('ticket_status_id', function ($query) {
            $query->select('id')
                ->from('tickets_statuses')
                ->where('name', 'Closed')
                ->limit(1);
        })->count();

        // Count tickets resolved on first contact (no reassignments, no comments after first response)
        $fcrQuery = DB::table('tickets as t')
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('t.first_response_at')
            ->whereNotNull('t.resolved_at');

        if ($divisionId) {
            $fcrQuery->where('t.division_id', $divisionId);
        }

        // Exclude tickets with multiple assignments (indicates escalation)
        $fcrQuery->whereRaw('(
            SELECT COUNT(DISTINCT assigned_to) 
            FROM ticket_history 
            WHERE ticket_id = t.id
        ) <= 1');

        $fcrTickets = $fcrQuery->count();

        $fcrPercentage = $totalTickets > 0 ? ($fcrTickets / $totalTickets) * 100 : 0;

        return [
            'fcr_count' => $fcrTickets,
            'total_tickets' => $totalTickets,
            'fcr_percentage' => round($fcrPercentage, 2),
            'label' => 'First Contact Resolution',
            'unit' => '%',
        ];
    }

    /**
     * Get current ticket backlog by status
     *
     * @param int|null $divisionId
     * @return array
     */
    public function getTicketBacklog(?int $divisionId = null): array
    {
        $query = DB::table('tickets as t')
            ->join('tickets_statuses as ts', 't.ticket_status_id', '=', 'ts.id')
            ->select('ts.name as status', DB::raw('COUNT(*) as count'))
            ->where('ts.name', '!=', 'Closed')
            ->groupBy('ts.name');

        if ($divisionId) {
            $query->where('t.division_id', $divisionId);
        }

        $backlogByStatus = $query->get()->pluck('count', 'status')->toArray();

        $totalBacklog = array_sum($backlogByStatus);

        return [
            'total' => $totalBacklog,
            'by_status' => $backlogByStatus,
            'label' => 'Current Ticket Backlog',
            'unit' => 'tickets',
        ];
    }

    /**
     * Calculate SLA Compliance
     * Percentage of tickets resolved within SLA timeframe
     *
     * @param array $dateRange
     * @param int|null $divisionId
     * @return array
     */
    public function calculateSLACompliance(array $dateRange, ?int $divisionId = null): array
    {
        // Total tickets with SLA
        $totalQuery = DB::table('tickets')
            ->whereNotNull('sla_due')
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($divisionId) {
            $totalQuery->where('division_id', $divisionId);
        }

        $totalWithSLA = $totalQuery->count();

        // Tickets resolved within SLA
        $compliantQuery = DB::table('tickets')
            ->whereNotNull('sla_due')
            ->whereNotNull('resolved_at')
            ->whereRaw('resolved_at <= sla_due')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($divisionId) {
            $compliantQuery->where('division_id', $divisionId);
        }

        $compliantTickets = $compliantQuery->count();

        // Tickets breached SLA
        $breachedTickets = $totalWithSLA - $compliantTickets;

        $compliancePercentage = $totalWithSLA > 0 ? ($compliantTickets / $totalWithSLA) * 100 : 0;

        return [
            'compliant_tickets' => $compliantTickets,
            'breached_tickets' => $breachedTickets,
            'total_with_sla' => $totalWithSLA,
            'compliance_percentage' => round($compliancePercentage, 2),
            'label' => 'SLA Compliance',
            'unit' => '%',
        ];
    }

    /**
     * Get technician utilization metrics
     *
     * @param array $dateRange
     * @param int|null $divisionId
     * @return array
     */
    public function getTechnicianUtilization(array $dateRange, ?int $divisionId = null): array
    {
        $query = DB::table('tickets as t')
            ->join('users as u', 't.assigned_to', '=', 'u.id')
            ->select(
                'u.id as user_id',
                'u.name as technician_name',
                DB::raw('COUNT(*) as assigned_tickets'),
                DB::raw('SUM(CASE WHEN t.ticket_status_id IN (
                    SELECT id FROM tickets_statuses WHERE name = "Closed"
                ) THEN 1 ELSE 0 END) as closed_tickets'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at)) as avg_resolution_hours')
            )
            ->whereNotNull('t.assigned_to')
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']]);

        if ($divisionId) {
            $query->where('t.division_id', $divisionId);
        }

        $technicians = $query->groupBy('u.id', 'u.name')->get();

        $totalAssigned = $technicians->sum('assigned_tickets');
        $totalClosed = $technicians->sum('closed_tickets');

        $technicianData = $technicians->map(function ($tech) use ($totalAssigned) {
            return [
                'user_id' => $tech->user_id,
                'name' => $tech->technician_name,
                'assigned_tickets' => $tech->assigned_tickets,
                'closed_tickets' => $tech->closed_tickets,
                'avg_resolution_hours' => round($tech->avg_resolution_hours ?? 0, 2),
                'workload_percentage' => $totalAssigned > 0 ? round(($tech->assigned_tickets / $totalAssigned) * 100, 2) : 0,
            ];
        })->toArray();

        return [
            'total_assigned' => $totalAssigned,
            'total_closed' => $totalClosed,
            'technicians' => $technicianData,
            'label' => 'Technician Utilization',
            'unit' => 'tickets',
        ];
    }

    /**
     * Calculate support cost per asset
     * Estimated based on ticket resolution hours
     *
     * @param array $dateRange
     * @param int|null $divisionId
     * @return array
     */
    public function calculateSupportCostPerAsset(array $dateRange, ?int $divisionId = null): array
    {
        // Total support hours (sum of all ticket resolution times)
        $supportHoursQuery = DB::table('tickets')
            ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as total_hours')
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($divisionId) {
            $supportHoursQuery->where('division_id', $divisionId);
        }

        $totalHours = $supportHoursQuery->first()->total_hours ?? 0;

        // Total assets
        $assetsQuery = Asset::query();
        if ($divisionId) {
            $assetsQuery->where('division_id', $divisionId);
        }
        $totalAssets = $assetsQuery->count();

        $hoursPerAsset = $totalAssets > 0 ? $totalHours / $totalAssets : 0;

        // Assuming hourly rate of $50 for cost estimation
        $estimatedCostPerAsset = $hoursPerAsset * 50;

        return [
            'total_support_hours' => round($totalHours, 2),
            'total_assets' => $totalAssets,
            'hours_per_asset' => round($hoursPerAsset, 2),
            'estimated_cost_per_asset' => round($estimatedCostPerAsset, 2),
            'hourly_rate_assumed' => 50,
            'label' => 'Support Cost Per Asset',
            'unit' => 'USD',
        ];
    }

    /**
     * Calculate asset utilization
     * Percentage of assets in active use vs idle
     *
     * @param int|null $divisionId
     * @return array
     */
    public function calculateAssetUtilization(?int $divisionId = null): array
    {
        $totalQuery = Asset::query();
        if ($divisionId) {
            $totalQuery->where('division_id', $divisionId);
        }
        $totalAssets = $totalQuery->count();

        // Assets assigned to users (in use)
        $inUseQuery = Asset::query()->whereNotNull('assigned_to');
        if ($divisionId) {
            $inUseQuery->where('division_id', $divisionId);
        }
        $inUse = $inUseQuery->count();

        // Assets in stock (idle)
        $idleQuery = Asset::query()->whereNull('assigned_to');
        if ($divisionId) {
            $idleQuery->where('division_id', $divisionId);
        }
        $idle = $idleQuery->count();

        $utilizationPercentage = $totalAssets > 0 ? ($inUse / $totalAssets) * 100 : 0;

        return [
            'total_assets' => $totalAssets,
            'in_use' => $inUse,
            'idle' => $idle,
            'utilization_percentage' => round($utilizationPercentage, 2),
            'label' => 'Asset Utilization',
            'unit' => '%',
        ];
    }

    /**
     * Get date range for a given period
     *
     * @param string $period
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getDateRange(string $period): array
    {
        $end = Carbon::now();
        $start = match ($period) {
            'day' => Carbon::now()->startOfDay(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            'all' => Carbon::create(2000, 1, 1), // Far past date
            default => Carbon::now()->startOfMonth(),
        };

        return [
            'start' => $start,
            'end' => $end,
            'label' => $this->getPeriodLabel($period),
        ];
    }

    /**
     * Get human-readable period label
     *
     * @param string $period
     * @return string
     */
    protected function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'day' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
            'all' => 'All Time',
            default => 'This Month',
        };
    }

    /**
     * Clear KPI cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
