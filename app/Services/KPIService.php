<?php

namespace App\Services;

use App\User;
use App\Ticket;
use App\DailyActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KPIService
{
    /**
     * Get comprehensive admin performance report
     */
    public function getAdminPerformanceReport($period = 'month', $adminId = null)
    {
        $dateRange = $this->getDateRange($period);
        
        if ($adminId) {
            return $this->getIndividualAdminPerformance($adminId, $dateRange);
        }

        return $this->getAllAdminsPerformance($dateRange);
    }

    /**
     * Get individual admin performance
     */
    public function getIndividualAdminPerformance($adminId, $dateRange)
    {
        $admin = User::find($adminId);
        if (!$admin || !$admin->hasRole('admin')) {
            throw new \Exception('Invalid admin');
        }

        $tickets = Ticket::where('assigned_to', $adminId)
                        ->whereBetween('created_at', $dateRange);

        $completedTickets = $tickets->whereNotNull('resolved_at');
        $overdueTickets = $tickets->where('sla_due', '<', now())
                                ->whereNull('resolved_at');

        return [
            'admin' => $admin,
            'period' => $dateRange,
            'metrics' => [
                'total_assigned' => $tickets->count(),
                'total_completed' => $completedTickets->count(),
                'total_overdue' => $overdueTickets->count(),
                'completion_rate' => $this->calculateCompletionRate($tickets),
                'avg_resolution_time' => $this->calculateAverageResolutionTime($completedTickets),
                'sla_compliance' => $this->calculateSLACompliance($tickets),
                'daily_activities' => $this->getDailyActivitiesCount($adminId, $dateRange),
                'ticket_priority_breakdown' => $this->getTicketPriorityBreakdown($adminId, $dateRange),
                'performance_trend' => $this->getPerformanceTrend($adminId, $dateRange)
            ]
        ];
    }

    /**
     * Get all admins performance comparison
     */
    public function getAllAdminsPerformance($dateRange)
    {
        $admins = User::role('admin')->with(['roles', 'adminOnlineStatus'])->get();
        $performance = [];

        foreach ($admins as $admin) {
            $tickets = Ticket::where('assigned_to', $admin->id)
                           ->whereBetween('created_at', $dateRange);

            $completedTickets = $tickets->whereNotNull('resolved_at');
            
            $performance[] = [
                'admin' => $admin,
                'metrics' => [
                    'total_assigned' => $tickets->count(),
                    'total_completed' => $completedTickets->count(),
                    'completion_rate' => $this->calculateCompletionRate($tickets),
                    'avg_resolution_time' => $this->calculateAverageResolutionTime($completedTickets),
                    'sla_compliance' => $this->calculateSLACompliance($tickets),
                    'score' => $this->calculatePerformanceScore($admin->id, $dateRange)
                ]
            ];
        }

        // Sort by performance score
        usort($performance, function($a, $b) {
            return $b['metrics']['score'] <=> $a['metrics']['score'];
        });

        return $performance;
    }

    /**
     * Calculate performance score (0-100)
     */
    public function calculatePerformanceScore($adminId, $dateRange)
    {
        $tickets = Ticket::where('assigned_to', $adminId)
                        ->whereBetween('created_at', $dateRange);

        $totalTickets = $tickets->count();
        if ($totalTickets === 0) return 0;

        $completedTickets = $tickets->whereNotNull('resolved_at')->count();
        $onTimeTickets = $tickets->whereNotNull('resolved_at')
                               ->whereColumn('resolved_at', '<=', 'sla_due')
                               ->count();

        $completionScore = ($completedTickets / $totalTickets) * 50; // Max 50 points
        $slaScore = $totalTickets > 0 ? ($onTimeTickets / $totalTickets) * 50 : 0; // Max 50 points

        return round($completionScore + $slaScore, 2);
    }

    /**
     * Get team performance summary
     */
    public function getTeamPerformanceSummary($period = 'month')
    {
        $dateRange = $this->getDateRange($period);
        
        $totalTickets = Ticket::whereBetween('created_at', $dateRange)->count();
        $completedTickets = Ticket::whereBetween('created_at', $dateRange)
                                 ->whereNotNull('resolved_at')->count();
        $overdueTickets = Ticket::whereBetween('created_at', $dateRange)
                               ->where('sla_due', '<', now())
                               ->whereNull('resolved_at')->count();

        return [
            'total_tickets' => $totalTickets,
            'completed_tickets' => $completedTickets,
            'overdue_tickets' => $overdueTickets,
            'team_completion_rate' => $totalTickets > 0 ? round(($completedTickets / $totalTickets) * 100, 2) : 0,
            'team_sla_compliance' => $this->getTeamSLACompliance($dateRange),
            'avg_team_resolution_time' => $this->getTeamAverageResolutionTime($dateRange),
            'top_performer' => $this->getTopPerformer($dateRange),
            'workload_distribution' => $this->getWorkloadDistribution($dateRange)
        ];
    }

    /**
     * Get ticket trends for dashboard charts
     */
    public function getTicketTrends($days = 30)
    {
        return collect(range($days - 1, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date' => $date->format('Y-m-d'),
                'created' => Ticket::whereDate('created_at', $date)->count(),
                'resolved' => Ticket::whereDate('resolved_at', $date)->count(),
                'overdue' => Ticket::whereDate('sla_due', $date)
                                  ->whereNull('resolved_at')->count()
            ];
        });
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'week':
                return [now()->subWeek(), now()];
            case 'quarter':
                return [now()->subQuarter(), now()];
            case 'year':
                return [now()->subYear(), now()];
            default: // month
                return [now()->subMonth(), now()];
        }
    }

    private function calculateCompletionRate($ticketsQuery)
    {
        $total = $ticketsQuery->count();
        if ($total === 0) return 0;

        $completed = $ticketsQuery->whereNotNull('resolved_at')->count();
        return round(($completed / $total) * 100, 2);
    }

    private function calculateAverageResolutionTime($completedTicketsQuery)
    {
        $tickets = $completedTicketsQuery->get();
        if ($tickets->isEmpty()) return 0;

        $totalHours = $tickets->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $tickets->count(), 2);
    }

    private function calculateSLACompliance($ticketsQuery)
    {
        $total = $ticketsQuery->count();
        if ($total === 0) return 0;

        $onTime = $ticketsQuery->where(function($q) {
            $q->whereNull('resolved_at')->where('sla_due', '>', now())
              ->orWhere(function($subQ) {
                  $subQ->whereNotNull('resolved_at')
                       ->whereColumn('resolved_at', '<=', 'sla_due');
              });
        })->count();

        return round(($onTime / $total) * 100, 2);
    }

    private function getDailyActivitiesCount($adminId, $dateRange)
    {
        return DailyActivity::where('user_id', $adminId)
                           ->whereBetween('activity_date', [
                               Carbon::parse($dateRange[0])->toDateString(),
                               Carbon::parse($dateRange[1])->toDateString()
                           ])
                           ->count();
    }

    private function getTicketPriorityBreakdown($adminId, $dateRange)
    {
        return Ticket::where('assigned_to', $adminId)
                    ->whereBetween('created_at', $dateRange)
                    ->select('ticket_priority_id', DB::raw('count(*) as count'))
                    ->groupBy('ticket_priority_id')
                    ->with('ticket_priority')
                    ->get();
    }

    private function getPerformanceTrend($adminId, $dateRange)
    {
        $days = Carbon::parse($dateRange[0])->diffInDays(Carbon::parse($dateRange[1]));
        $interval = max(1, floor($days / 10)); // Max 10 data points

        return collect(range(0, $days, $interval))->map(function($daysAgo) use ($adminId, $dateRange) {
            $date = Carbon::parse($dateRange[1])->subDays($daysAgo);
            $dayTickets = Ticket::where('assigned_to', $adminId)
                              ->whereDate('created_at', $date);

            return [
                'date' => $date->format('Y-m-d'),
                'completed' => $dayTickets->whereNotNull('resolved_at')->count(),
                'created' => $dayTickets->count()
            ];
        })->reverse()->values();
    }

    private function getTeamSLACompliance($dateRange)
    {
        $totalTickets = Ticket::whereBetween('created_at', $dateRange)->count();
        if ($totalTickets === 0) return 0;

        $onTimeTickets = Ticket::whereBetween('created_at', $dateRange)
                              ->where(function($q) {
                                  $q->whereNull('resolved_at')->where('sla_due', '>', now())
                                    ->orWhere(function($subQ) {
                                        $subQ->whereNotNull('resolved_at')
                                             ->whereColumn('resolved_at', '<=', 'sla_due');
                                    });
                              })->count();

        return round(($onTimeTickets / $totalTickets) * 100, 2);
    }

    private function getTeamAverageResolutionTime($dateRange)
    {
        $resolvedTickets = Ticket::whereBetween('resolved_at', $dateRange)
                                ->whereNotNull('resolved_at')
                                ->get();

        if ($resolvedTickets->isEmpty()) return 0;

        $totalHours = $resolvedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $resolvedTickets->count(), 2);
    }

    private function getTopPerformer($dateRange)
    {
        $admins = User::role('admin')->get();
        $topScore = 0;
        $topPerformer = null;

        foreach ($admins as $admin) {
            $score = $this->calculatePerformanceScore($admin->id, $dateRange);
            if ($score > $topScore) {
                $topScore = $score;
                $topPerformer = $admin;
            }
        }

        return [
            'admin' => $topPerformer,
            'score' => $topScore
        ];
    }

    private function getWorkloadDistribution($dateRange)
    {
        return User::role('admin')
                  ->select('users.name', DB::raw('count(tickets.id) as ticket_count'))
                  ->leftJoin('tickets', function($join) use ($dateRange) {
                      $join->on('users.id', '=', 'tickets.assigned_to')
                           ->whereBetween('tickets.created_at', $dateRange);
                  })
                  ->groupBy('users.id', 'users.name')
                  ->get();
    }
}