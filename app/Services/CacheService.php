<?php

namespace App\Services;

use App\Ticket;
use App\Asset;
use App\DailyActivity;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CacheService
{
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cached dashboard statistics
     */
    public function getDashboardStats()
    {
        return Cache::remember('dashboard_stats', self::CACHE_TTL, function () {
            return [
                'total_tickets' => Ticket::count(),
                'open_tickets' => Ticket::whereHas('ticket_status', function($q) {
                    $q->whereNotIn('name', ['Closed', 'Resolved']);
                })->count(),
                'overdue_tickets' => Ticket::where('sla_due', '<', now())
                    ->whereHas('ticket_status', function($q) {
                        $q->whereNotIn('name', ['Closed', 'Resolved']);
                    })->count(),
                'total_assets' => Asset::count(),
                'active_assets' => Asset::whereHas('status', function($q) {
                    $q->where('name', 'Active');
                })->count(),
                'last_updated' => now()
            ];
        });
    }

    /**
     * Get cached KPI data
     */
    public function getKPIData()
    {
        return Cache::remember('kpi_data', self::CACHE_TTL, function () {
            return [
                'avg_resolution_time' => $this->calculateAverageResolutionTime(),
                'sla_compliance' => $this->calculateSLACompliance(),
                'monthly_trend' => $this->getMonthlyTicketTrend(),
                'team_performance' => $this->getTeamPerformance(),
                'asset_breakdown' => $this->getAssetBreakdown(),
            ];
        });
    }

    /**
     * Clear all cached data
     */
    public function clearAllCache()
    {
        Cache::forget('dashboard_stats');
        Cache::forget('kpi_data');
        Cache::forget('user_permissions_' . auth()->id());
        
        return true;
    }

    /**
     * Clear cache when data changes
     */
    public function clearCacheOnUpdate($type)
    {
        switch ($type) {
            case 'ticket':
                Cache::forget('dashboard_stats');
                Cache::forget('kpi_data');
                break;
            case 'asset':
                Cache::forget('dashboard_stats');
                Cache::forget('kpi_data');
                break;
            case 'user':
                Cache::forget('dashboard_stats');
                break;
        }
    }

    private function calculateAverageResolutionTime()
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

    private function calculateSLACompliance()
    {
        $total = Ticket::whereNotNull('resolved_at')->count();
        if ($total === 0) return 100;

        $onTime = Ticket::whereNotNull('resolved_at')
                       ->whereColumn('resolved_at', '<=', 'sla_due')
                       ->count();

        return round(($onTime / $total) * 100, 2);
    }

    private function getMonthlyTicketTrend()
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $monthExpr = "strftime('%Y-%m', created_at) as month";
        } else {
            // default to MySQL-compatible
            $monthExpr = "DATE_FORMAT(created_at, '%Y-%m') as month";
        }

        return Ticket::select(
            DB::raw($monthExpr),
            DB::raw('count(*) as total')
        )
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month');
    }

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

    private function getAssetBreakdown()
    {
        return Asset::join('statuses', 'assets.status_id', '=', 'statuses.id')
                   ->groupBy('statuses.name')
                   ->select('statuses.name', DB::raw('count(*) as total'))
                   ->pluck('total', 'name');
    }

    /**
     * Get cached locations
     */
    public static function getLocations()
    {
        return Cache::remember('locations_all', self::CACHE_TTL, function () {
            return \App\Location::orderBy('location_name')->get();
        });
    }

    /**
     * Get cached statuses
     */
    public static function getStatuses()
    {
        return Cache::remember('statuses_all', self::CACHE_TTL, function () {
            return \App\Status::orderBy('name')->get();
        });
    }

    /**
     * Get cached ticket statuses
     */
    public static function getTicketStatuses()
    {
        return Cache::remember('ticket_statuses_all', self::CACHE_TTL, function () {
            return \App\TicketsStatus::orderBy('status')->get();
        });
    }

    /**
     * Get cached ticket types
     */
    public static function getTicketTypes()
    {
        return Cache::remember('ticket_types_all', self::CACHE_TTL, function () {
            return \App\TicketsType::orderBy('type')->get();
        });
    }

    /**
     * Get cached ticket priorities
     */
    public static function getTicketPriorities()
    {
        return Cache::remember('ticket_priorities_all', self::CACHE_TTL, function () {
            // Order by priority level: Urgent, High, Normal, Low
            return \App\TicketsPriority::orderByRaw("
                CASE priority 
                    WHEN 'Urgent' THEN 1
                    WHEN 'High' THEN 2
                    WHEN 'Normal' THEN 3
                    WHEN 'Low' THEN 4
                    ELSE 5
                END
            ")->get();
        });
    }

    /**
     * Clear all static data cache
     */
    public static function clearStaticDataCache()
    {
        $keys = [
            'locations_all',
            'statuses_all',
            'ticket_statuses_all',
            'ticket_types_all',
            'ticket_priorities_all'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get all roles with caching (for UserController and forms)
     */
    public static function getRoles()
    {
        return Cache::remember('roles_all', self::CACHE_TTL, function () {
            return \App\Role::query()
                ->canonical()
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get specific role by name with caching
     */
    public static function getRoleByName($roleName)
    {
        return Cache::remember("roles_name_{$roleName}", self::CACHE_TTL, function () use ($roleName) {
            return \App\Role::query()
                ->canonical()
                ->where('name', $roleName)
                ->first();
        });
    }

    /**
     * Get all divisions with caching
     */
    public static function getDivisions()
    {
        return Cache::remember('divisions_all', self::CACHE_TTL, function () {
            return \App\Division::orderBy('name')->get();
        });
    }

    /**
     * Get all asset models with caching
     */
    public static function getAssetModels()
    {
        return Cache::remember('asset_models_all', self::CACHE_TTL, function () {
            return \App\AssetModel::with('manufacturer')->orderBy('asset_model')->get();
        });
    }

    /**
     * Get all suppliers with caching
     */
    public static function getSuppliers()
    {
        return Cache::remember('suppliers_all', self::CACHE_TTL, function () {
            return \App\Supplier::orderBy('name')->get();
        });
    }

    /**
     * Get all asset types with caching
     */
    public static function getAssetTypes()
    {
        return Cache::remember('asset_types_all', self::CACHE_TTL, function () {
            return \App\AssetType::orderBy('type_name')->get();
        });
    }

    /**
     * Get all users with caching (for dropdowns/selects)
     */
    public static function getUsers()
    {
        return Cache::remember('users_active', self::CACHE_TTL, function () {
            return \App\User::where('is_active', 1)->orderBy('name')->get();
        });
    }

    /**
     * Get all active admins with caching
     */
    public static function getAdmins()
    {
        return Cache::remember('admins_all', self::CACHE_TTL, function () {
            return \App\User::role(['admin', 'super-admin'])
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Warm up all caches - useful after deployment
     */
    public static function warmUpCaches()
    {
        self::getLocations();
        self::getStatuses();
        self::getTicketStatuses();
        self::getTicketTypes();
        self::getTicketPriorities();
        self::getRoles();
        self::getDivisions();
        self::getAssetModels();
        self::getSuppliers();
        self::getAssetTypes();
        self::getUsers();
        self::getAdmins();

        return [
            'status' => 'success',
            'message' => 'All caches warmed up successfully (12 cache keys)',
            'timestamp' => now()
        ];
    }

    /**
     * Clear all cache (including static data)
     */
    public static function clearAllSystemCache()
    {
        $keys = [
            'locations_all',
            'statuses_all',
            'ticket_statuses_all',
            'ticket_types_all',
            'ticket_priorities_all',
            'roles_all',
            'divisions_all',
            'asset_models_all',
            'suppliers_all',
            'asset_types_all',
            'users_all',
            'admins_all'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return [
            'status' => 'success',
            'message' => 'All system caches cleared (12 cache keys)',
            'timestamp' => now()
        ];
    }
}
