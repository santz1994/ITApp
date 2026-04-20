<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TicketService;
use App\Services\AssetService;
use App\Services\KPIService;
use App\Ticket;
use App\Asset;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagementDashboardController extends Controller
{
    protected $ticketService;
    protected $assetService;
    protected $kpiService;

    public function __construct(
        TicketService $ticketService,
        AssetService $assetService, 
        KPIService $kpiService
    ) {
        $this->ticketService = $ticketService;
        $this->assetService = $assetService;
        $this->kpiService = $kpiService;
        
        $this->middleware('permission:view-kpi-dashboard');
    }

    public function index()
    {
        $data = [
            'overview' => $this->getOverviewData(),
            'admin_performance' => $this->getAdminPerformanceData(),
            'ticket_trends' => $this->getTicketTrends(),
            'asset_overview' => $this->getAssetOverview(),
            'sla_compliance' => $this->getSLACompliance()
        ];

        return view('management.dashboard', $data);
    }

    public function adminPerformance(Request $request)
    {
        $period = $request->get('period', 'month'); // month, quarter, year
        $adminId = $request->get('admin_id');
        
        // Define agent emails (Ridwan and Idol only)
        $agentEmails = ['ridwan_it@quty.co.id', 'idol@quty.co.id'];

        if ($adminId) {
            // Individual admin performance
            $data = $this->kpiService->getAdminPerformanceReport($period, $adminId);
        } else {
            // Get performance data for agents only
            $agents = User::whereIn('email', $agentEmails)->get();
            $performanceData = [];
            
            foreach ($agents as $agent) {
                $metrics = $this->ticketService->getAdminPerformance($agent->id);
                $performanceData[] = [
                    'admin' => $agent,
                    'metrics' => $metrics
                ];
            }
            
            // Eager load adminOnlineStatus to prevent N+1 query problem
            $agentIds = collect($performanceData)->pluck('admin.id')->unique();
            $agentsWithStatus = User::with('adminOnlineStatus')
                ->whereIn('id', $agentIds)
                ->get()
                ->keyBy('id');
            
            $adminPerformance = collect($performanceData)->map(function($item) use ($agentsWithStatus) {
                $agentId = $item['admin']->id;
                $agent = $agentsWithStatus->get($agentId) ?? $item['admin'];
                $metrics = $item['metrics'];
                
                // Add metrics as properties to the admin object
                $agent->assigned_tickets = $metrics['total_assigned'] ?? 0;
                $agent->resolved_tickets = $metrics['completed'] ?? 0;
                $agent->avg_response_time = $metrics['avg_resolution_time'] ?? 0;
                
                // Add online status check (using preloaded relationship)
                $agent->is_online = $agent->adminOnlineStatus && 
                                   $agent->adminOnlineStatus->last_activity && 
                                   $agent->adminOnlineStatus->last_activity->gt(now()->subMinutes(10));
                
                // Add last activity (using preloaded relationship)
                $agent->last_activity = $agent->adminOnlineStatus ? 
                                       $agent->adminOnlineStatus->last_activity : null;
                
                return $agent;
            });
            
            // Calculate summary metrics for cards
            $totalAdmins = $adminPerformance->count();
            $activeAdmins = $adminPerformance->filter(fn($admin) => $admin->is_online)->count();
            $resolvedTickets = $adminPerformance->sum('resolved_tickets');
            $totalTickets = $adminPerformance->sum('assigned_tickets');
            $avgResponseTime = $totalAdmins > 0 ? 
                             $adminPerformance->avg('avg_response_time') : 0;
            
            $data = [
                'adminPerformance' => $adminPerformance,
                'totalAdmins' => $totalAdmins,
                'activeAdmins' => $activeAdmins,
                'resolvedTickets' => $resolvedTickets,
                'avgResponseTime' => $avgResponseTime,
                'period' => $period
            ];
        }

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('management.admin-performance', $data);
    }

    public function ticketReports(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $data = [
            'ticket_summary' => $this->getTicketSummary($startDate, $endDate),
            'priority_breakdown' => $this->getPriorityBreakdown($startDate, $endDate),
            'status_breakdown' => $this->getStatusBreakdown($startDate, $endDate),
            'resolution_times' => $this->getResolutionTimes($startDate, $endDate)
        ];

        return view('management.ticket-reports', $data);
    }

    public function assetReports()
    {
        $data = [
            'asset_summary' => $this->getAssetSummary(),
            'asset_by_status' => $this->getAssetsByStatus(),
            'asset_by_location' => $this->getAssetsByLocation(),
            'lemon_assets' => $this->getLemonAssets(),
            'warranty_expiring' => $this->getWarrantyExpiring()
        ];

        return view('management.asset-reports', $data);
    }

    private function getOverviewData()
    {
        return [
            'total_tickets_today' => Ticket::whereDate('created_at', today())->count(),
            'total_tickets_month' => Ticket::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'overdue_tickets' => $this->ticketService->getOverdueTickets()->count(),
            // Provide a small list of recent overdue tickets for the widget
            'recent_overdue_tickets' => $this->ticketService->getOverdueTickets()->sortByDesc('created_at')->take(5),
            'unassigned_tickets' => $this->ticketService->getUnassignedTickets()->count(),
            'total_assets' => Asset::count(),
            'active_admins' => User::role('administrator')->whereHas('adminOnlineStatus', function($q) {
                $q->where('last_activity', '>=', now()->subHours(8));
            })->count()
        ];
    }

    private function getAdminPerformanceData()
    {
        // Define agent emails (Ridwan and Idol only)
        $agentEmails = ['ridwan_it@quty.co.id', 'idol@quty.co.id'];
        
        $agents = User::whereIn('email', $agentEmails)->get();
        $performance = [];

        foreach ($agents as $agent) {
            $metrics = $this->ticketService->getAdminPerformance($agent->id);
            $performance[] = [
                'admin' => $agent,
                'metrics' => $metrics
            ];
        }

        return $performance;
    }

    private function getTicketTrends()
    {
        $last30Days = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date' => $date->format('Y-m-d'),
                'created' => Ticket::whereDate('created_at', $date)->count(),
                'resolved' => Ticket::whereDate('resolved_at', $date)->count()
            ];
        });

        return $last30Days;
    }

    private function getAssetOverview()
    {
        return [
            'total' => Asset::count(),
            'in_use' => Asset::inUse()->count(),
            'in_stock' => Asset::inStock()->count(),
            'in_repair' => Asset::inRepair()->count(),
            'disposed' => Asset::disposed()->count()
        ];
    }

    private function getSLACompliance()
    {
        $totalTickets = Ticket::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $onTimeTickets = Ticket::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                              ->where(function($q) {
                                  $q->whereNull('resolved_at')
                                    ->where('sla_due', '>', now())
                                    ->orWhere(function($subQ) {
                                        $subQ->whereNotNull('resolved_at')
                                             ->whereColumn('resolved_at', '<=', 'sla_due');
                                    });
                              })->count();

        return $totalTickets > 0 ? round(($onTimeTickets / $totalTickets) * 100, 2) : 0;
    }

    private function getTicketSummary($startDate, $endDate)
    {
        return [
            'total_created' => Ticket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_resolved' => Ticket::whereBetween('resolved_at', [$startDate, $endDate])->count(),
            'avg_resolution_time' => $this->getAverageResolutionTime($startDate, $endDate),
            'sla_compliance' => $this->getSLAComplianceForPeriod($startDate, $endDate)
        ];
    }

    private function getPriorityBreakdown($startDate, $endDate)
    {
        return Ticket::select('ticket_priority_id', DB::raw('count(*) as count'))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('ticket_priority')
                    ->groupBy('ticket_priority_id')
                    ->get();
    }

    private function getStatusBreakdown($startDate, $endDate)
    {
        return Ticket::select('ticket_status_id', DB::raw('count(*) as count'))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('ticket_status')
                    ->groupBy('ticket_status_id')
                    ->get();
    }

    private function getResolutionTimes($startDate, $endDate)
    {
        return Ticket::whereBetween('resolved_at', [$startDate, $endDate])
                    ->whereNotNull('resolved_at')
                    ->select(DB::raw('
                        AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours,
                        MIN(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as min_hours,
                        MAX(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as max_hours
                    '))->first();
    }

    private function getAssetSummary()
    {
        return [
            'total_assets' => Asset::count(),
            'by_type' => Asset::select('asset_types.type_name as name', DB::raw('count(*) as count'))
                             ->join('asset_models', 'assets.model_id', '=', 'asset_models.id')
                             ->join('asset_types', 'asset_models.asset_type_id', '=', 'asset_types.id')
                             ->groupBy('asset_types.type_name')
                             ->get(),
            'by_status' => Asset::select('statuses.name', DB::raw('count(*) as count'))
                               ->join('statuses', 'assets.status_id', '=', 'statuses.id')
                               ->groupBy('statuses.name')
                               ->get()
        ];
    }

    private function getAssetsByStatus()
    {
        return Asset::select('status_id', DB::raw('count(*) as count'))
                   ->with('status')
                   ->groupBy('status_id')
                   ->get();
    }

    private function getAssetsByLocation()
    {
        return Asset::select('locations.name', DB::raw('count(*) as count'))
                   ->join('asset_models', 'assets.model_id', '=', 'asset_models.id')
                   ->join('divisions', 'assets.division_id', '=', 'divisions.id')
                   ->join('locations', 'divisions.location_id', '=', 'locations.id')
                   ->groupBy('locations.name')
                   ->get();
    }

    private function getLemonAssets()
    {
        return Asset::whereHas('tickets', function($q) {
                    $q->where('tickets.created_at', '>=', now()->subMonths(6));
                }, '>=', 3)
                ->with(['model', 'tickets' => function($q) {
                    $q->where('tickets.created_at', '>=', now()->subMonths(6));
                }])
                ->get();
    }

    private function getWarrantyExpiring()
    {
        // Compute warranty expiration using PHP (Carbon) to be DB-agnostic
        $now = now();
        $nextMonth = now()->addMonth();

        $assets = Asset::whereNotNull('purchase_date')
                   ->whereNotNull('warranty_months')
                   ->with(['model', 'assignedTo'])
                   ->get()
                   ->filter(function($asset) use ($now, $nextMonth) {
                       try {
                           $purchase = Carbon::parse($asset->purchase_date);
                       } catch (\Exception $e) {
                           return false;
                       }

                       $expiry = $purchase->copy()->addMonths($asset->warranty_months);

                       return $expiry->lte($nextMonth) && $expiry->gt($now);
                   });

        return $assets->values();
    }

    private function getAverageResolutionTime($startDate, $endDate)
    {
        $resolved = Ticket::whereBetween('resolved_at', [$startDate, $endDate])
                         ->whereNotNull('resolved_at')
                         ->get();

        if ($resolved->isEmpty()) return 0;

        $totalHours = $resolved->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $resolved->count(), 2);
    }

    private function getSLAComplianceForPeriod($startDate, $endDate)
    {
        $totalTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();
        $onTimeTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
                              ->where(function($q) {
                                  $q->whereNull('resolved_at')
                                    ->where('sla_due', '>', now())
                                    ->orWhere(function($subQ) {
                                        $subQ->whereNotNull('resolved_at')
                                             ->whereColumn('resolved_at', '<=', 'sla_due');
                                    });
                              })->count();

        return $totalTickets > 0 ? round(($onTimeTickets / $totalTickets) * 100, 2) : 0;
    }
}