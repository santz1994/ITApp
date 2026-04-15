<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Ticket;
use App\Asset;
use App\DailyActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view users'
            ], 403);
        }

        $query = User::with(['roles', 'division']);

        // Apply filters
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Transform data
        $users->through(function ($user) {
            return $this->transformUser($user);
        });

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * Display the specified user
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management']) && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this user'
            ], 403);
        }

        $user->load(['roles', 'division']);

        return response()->json([
            'success' => true,
            'data' => $this->transformUser($user, true),
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Get user performance metrics
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPerformance(User $user, Request $request)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management']) && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view performance data'
            ], 403);
        }

        $days = $request->get('days', 30);
        $performance = $user->getPerformanceMetrics($days);

        return response()->json([
            'success' => true,
            'data' => $performance,
            'message' => 'Performance metrics retrieved successfully'
        ]);
    }

    /**
     * Get user workload
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkload(User $user)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management']) && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view workload data'
            ], 403);
        }

        $workload = $user->getWorkload();

        return response()->json([
            'success' => true,
            'data' => $workload,
            'message' => 'Workload data retrieved successfully'
        ]);
    }

    /**
     * Get user activities
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivities(User $user, Request $request)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management']) && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view activity data'
            ], 403);
        }

        $query = DailyActivity::where('user_id', $user->id)
                             ->with(['ticket', 'user']);

        // Date filters
        if ($request->has('date_from')) {
            $query->where('activity_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('activity_date', '<=', $request->date_to);
        }

        if ($request->has('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        $query->orderBy('activity_date', 'desc');

        $perPage = $request->get('per_page', 15);
        $activities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities,
            'message' => 'User activities retrieved successfully'
        ]);
    }

    /**
     * Get dashboard statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats()
    {
        $user = auth()->user();
        
        $stats = [
            'my_tickets' => [
                'total' => Ticket::where('user_id', $user->id)->count(),
                'open' => Ticket::where('user_id', $user->id)->where('ticket_status_id', 1)->count(),
                'assigned_to_me' => Ticket::where('assigned_to', $user->id)->count(),
                'overdue' => Ticket::where('assigned_to', $user->id)
                                  ->where('sla_due', '<', now())
                                  ->whereNotIn('ticket_status_id', [3, 4])
                                  ->count()
            ],
            'my_assets' => [
                'assigned_to_me' => Asset::where('assigned_to', $user->id)->count(),
                'warranty_expiring' => Asset::where('assigned_to', $user->id)
                                           ->whereBetween('warranty_expiry', [now(), now()->addDays(30)])
                                           ->count()
            ],
            'recent_activities' => DailyActivity::where('user_id', $user->id)
                                                ->where('activity_date', '>=', now()->subDays(7))
                                                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }

    /**
     * Get KPI data (for management/admin roles)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKpiData()
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view KPI data'
            ], 403);
        }

        $kpi = [
            'tickets' => [
                'total' => Ticket::count(),
                'open' => Ticket::where('ticket_status_id', 1)->count(),
                'in_progress' => Ticket::where('ticket_status_id', 2)->count(),
                'resolved' => Ticket::where('ticket_status_id', 3)->count(),
                'closed' => Ticket::where('ticket_status_id', 4)->count(),
                'overdue' => Ticket::where('sla_due', '<', now())
                                  ->whereNotIn('ticket_status_id', [3, 4])
                                  ->count(),
                'monthly_created' => Ticket::where('created_at', '>=', now()->startOfMonth())->count(),
                'monthly_resolved' => Ticket::where('ticket_status_id', 3)
                                           ->where('updated_at', '>=', now()->startOfMonth())
                                           ->count()
            ],
            'assets' => [
                'total' => Asset::count(),
                'deployed' => Asset::where('status_id', 1)->count(),
                'available' => Asset::where('status_id', 2)->count(),
                'maintenance' => Asset::where('status_id', 3)->count(),
                'retired' => Asset::where('status_id', 4)->count(),
                'assigned' => Asset::whereNotNull('assigned_to')->count(),
                'warranty_expiring' => Asset::whereBetween('warranty_expiry', [now(), now()->addDays(30)])->count(),
                'warranty_expired' => Asset::where('warranty_expiry', '<', now())->count()
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'online_today' => User::where('last_login_at', '>=', now()->startOfDay())->count()
            ],
            'activities' => [
                'today' => DailyActivity::where('activity_date', now()->toDateString())->count(),
                'this_week' => DailyActivity::where('activity_date', '>=', now()->startOfWeek())->count(),
                'this_month' => DailyActivity::where('activity_date', '>=', now()->startOfMonth())->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $kpi,
            'message' => 'KPI data retrieved successfully'
        ]);
    }

    /**
     * Transform user data
     *
     * @param User $user
     * @param bool $detailed
     * @return array
     */
    private function transformUser(User $user, $detailed = false)
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'division' => $user->division ? [
                'id' => $user->division->id,
                'name' => $user->division->name
            ] : null,
            'roles' => $user->getRoleNames(),
            'primary_role' => $user->primary_role,
            'initials' => $user->initials,
            'role_color' => $user->role_color,
            'is_online' => $user->is_online,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];

        if ($detailed) {
            $data['permissions'] = $user->getAllPermissions()->pluck('name');
        }

        return $data;
    }
}
