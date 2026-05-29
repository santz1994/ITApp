<?php

namespace App\Services;

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
                // KPI asset/ticket metrics removed (legacy)
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
                // legacy: ticket cache keys removed
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

    private function getMonthlyTicketTrend()
    {
        return collect();
    }

    private function getAssetBreakdown()
    {
        return collect();
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
        return collect();
    }

    /**
     * Get cached ticket types
     */
    public static function getTicketTypes()
    {
        return collect();
    }

    /**
     * Get cached ticket priorities
     */
    public static function getTicketPriorities()
    {
        return collect();
    }

    /**
     * Clear all static data cache
     */
    public static function clearStaticDataCache()
    {
        $keys = [
            'locations_all',
            'statuses_all',
            // ticket static caches removed
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
