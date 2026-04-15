<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\Asset;
use App\User;
use App\DailyActivity;
use Illuminate\Support\Facades\DB;

class KPIDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display KPI Dashboard for Management
     */
    public function index()
    {
        // Check permission
        /** @var \App\User $user */
        $user = auth()->user();
        if (!$user || !$user->hasRole(['management', 'super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to KPI Dashboard');
        }

        $data = [
            'totalTickets' => $this->getTotalTickets(),
            'openTickets' => $this->getOpenTickets(),
            'closedTickets' => $this->getClosedTickets(),
            'overdueTickets' => $this->getOverdueTickets(),
            'avgResolutionTime' => $this->getAverageResolutionTime(),
            'ticketsByPriority' => $this->getTicketsByPriority(),
            'ticketsByType' => $this->getTicketsByType(),
            'ticketsByStatus' => $this->getTicketsByStatus(),
            'monthlyTicketTrend' => $this->getMonthlyTicketTrend(),
            'totalAssets' => $this->getTotalAssets(),
            'assetsBreakdown' => $this->getAssetsBreakdown(),
            'topBrokenAssets' => $this->getTopBrokenAssets(),
            'teamPerformance' => $this->getTeamPerformance(),
            'recentActivities' => $this->getRecentActivities(),
            'slaCompliance' => $this->getSLACompliance(),
        ];

        return view('kpi.dashboard', compact('data'));
    }

    /**
     * Get total tickets count
     */
    private function getTotalTickets()
    {
        return Ticket::count();
    }

    /**
     * Get open tickets count
     */
    private function getOpenTickets()
    {
        return Ticket::whereHas('ticket_status', function($query) {
            $query->whereNotIn('status', ['Closed', 'Resolved']);
        })->count();
    }

    /**
     * Get closed tickets count
     */
    private function getClosedTickets()
    {
        return Ticket::whereHas('ticket_status', function($query) {
            $query->whereIn('status', ['Closed', 'Resolved']);
        })->count();
    }

    /**
     * Get overdue tickets count
     */
    private function getOverdueTickets()
    {
        return Ticket::where('sla_due', '<', now())
                    ->whereHas('ticket_status', function($query) {
                        $query->whereNotIn('status', ['Closed', 'Resolved']);
                    })->count();
    }

    /**
     * Get average resolution time in hours
     */
    private function getAverageResolutionTime()
    {
        $resolved = Ticket::whereNotNull('resolved_at')
                         ->whereNotNull('created_at')
                         ->get();

        if ($resolved->count() === 0) {
            return 0;
        }

        $totalHours = $resolved->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $resolved->count(), 2);
    }

    /**
     * Get tickets breakdown by priority
     */
    private function getTicketsByPriority()
    {
        return Ticket::join('tickets_priorities', 'tickets.ticket_priority_id', '=', 'tickets_priorities.id')
                    ->groupBy('tickets_priorities.priority')
                    ->select('tickets_priorities.priority', DB::raw('count(*) as total'))
                    ->get()
                    ->pluck('total', 'priority');
    }

    /**
     * Get tickets breakdown by type
     */
    private function getTicketsByType()
    {
        return Ticket::join('tickets_types', 'tickets.ticket_type_id', '=', 'tickets_types.id')
                    ->groupBy('tickets_types.type')
                    ->select('tickets_types.type', DB::raw('count(*) as total'))
                    ->get()
                    ->pluck('total', 'type');
    }

    /**
     * Get tickets breakdown by status
     */
    private function getTicketsByStatus()
    {
        return Ticket::join('tickets_statuses', 'tickets.ticket_status_id', '=', 'tickets_statuses.id')
                    ->groupBy('tickets_statuses.status')
                    ->select('tickets_statuses.status', DB::raw('count(*) as total'))
                    ->get()
                    ->pluck('total', 'status');
    }

    /**
     * Get monthly ticket creation trend (last 12 months)
     */
    private function getMonthlyTicketTrend()
    {
        $startDate = now()->subMonths(11)->startOfMonth();
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $monthExpr = "strftime('%Y-%m', created_at) as month";
        } else {
            $monthExpr = "DATE_FORMAT(created_at, '%Y-%m') as month";
        }

        return Ticket::select(
                    DB::raw($monthExpr),
                    DB::raw('count(*) as total')
                )
                ->where('created_at', '>=', $startDate)
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('total', 'month');
    }

    /**
     * Get total assets count
     */
    private function getTotalAssets()
    {
        return Asset::count();
    }

    /**
     * Get assets breakdown by status
     */
    private function getAssetsBreakdown()
    {
        return Asset::join('statuses', 'assets.status_id', '=', 'statuses.id')
                   ->groupBy('statuses.name')
                   ->select('statuses.name', DB::raw('count(*) as total'))
                   ->get()
                   ->pluck('total', 'name');
    }

    /**
     * Get assets with most tickets (most problematic)
     */
    private function getTopBrokenAssets()
    {
        return Asset::leftJoin('tickets', 'assets.id', '=', 'tickets.asset_id')
                   ->select('assets.asset_tag', 'assets.id', DB::raw('count(tickets.id) as ticket_count'))
                   ->groupBy('assets.id', 'assets.asset_tag')
                   ->having('ticket_count', '>', 0)
                   ->orderBy('ticket_count', 'desc')
                   ->limit(10)
                   ->get();
    }

    /**
     * Get team performance (tickets resolved by user)
     */
    private function getTeamPerformance()
    {
        return User::leftJoin('tickets', 'users.id', '=', 'tickets.assigned_to')
                  ->whereNotNull('tickets.resolved_at')
                  ->select('users.name', DB::raw('count(tickets.id) as resolved_tickets'))
                  ->groupBy('users.id', 'users.name')
                  ->orderBy('resolved_tickets', 'desc')
                  ->limit(10)
                  ->get();
    }

    /**
     * Get recent daily activities
     */
    private function getRecentActivities()
    {
        return DailyActivity::with('user')
                           ->orderBy('activity_date', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->limit(20)
                           ->get();
    }

    /**
     * Get SLA compliance percentage
     */
    private function getSLACompliance()
    {
        $totalResolved = Ticket::whereNotNull('resolved_at')->count();
        
        if ($totalResolved === 0) {
            return 100;
        }

        $onTimeResolved = Ticket::whereNotNull('resolved_at')
                               ->whereColumn('resolved_at', '<=', 'sla_due')
                               ->count();

        return round(($onTimeResolved / $totalResolved) * 100, 2);
    }

    /**
     * Get KPI data as JSON for AJAX requests
     */
    public function getKPIData()
    {
        return response()->json([
            'totalTickets' => $this->getTotalTickets(),
            'openTickets' => $this->getOpenTickets(),
            'closedTickets' => $this->getClosedTickets(),
            'overdueTickets' => $this->getOverdueTickets(),
            'avgResolutionTime' => $this->getAverageResolutionTime(),
            'slaCompliance' => $this->getSLACompliance(),
        ]);
    }
}
