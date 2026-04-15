<?php

namespace App\Http\Controllers;

use App\AuditLog;
use App\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * The audit log service instance.
     *
     * @var AuditLogService
     */
    protected $auditLogService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->auditLogService = new AuditLogService();
    }

    /**
     * Display a listing of audit logs.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Check authorization (only admins can view audit logs)
        if (!auth()->user()->hasRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to audit logs.');
        }

        $query = AuditLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Order by latest
        $query->orderBy('created_at', 'desc');

        // Paginate results with eager loading
        $auditLogs = $query->with('user')->paginate(50);

        // Get filter options
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $eventTypes = AuditLog::select('event_type')->distinct()->orderBy('event_type')->pluck('event_type');
        $modelTypes = AuditLog::select('model_type')
            ->distinct()
            ->whereNotNull('model_type')
            ->orderBy('model_type')
            ->get()
            ->map(function($item) {
                return class_basename($item->model_type);
            })
            ->unique()
            ->sort()
            ->values();

        return view('audit_logs.index', compact('auditLogs', 'users', 'actions', 'eventTypes', 'modelTypes'));
    }

    /**
     * Display the specified audit log.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Check authorization (only admins can view audit logs)
        if (!auth()->user()->hasRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to audit logs.');
        }

        $auditLog = AuditLog::with('user')->findOrFail($id);

        return view('audit_logs.show', compact('auditLog'));
    }

    /**
     * Get audit logs for a specific model (API endpoint).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModelLogs(Request $request)
    {
        // Check authorization
        if (!auth()->user()->hasRole(['super-admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = AuditLog::where('model_type', $request->model_type)
            ->where('model_id', $request->model_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Get audit logs for the authenticated user (API endpoint).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyLogs(Request $request)
    {
        $limit = $request->input('limit', 50);

        $logs = AuditLog::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Get audit statistics (API endpoint).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        // Check authorization
        if (!auth()->user()->hasRole(['super-admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $startDate = $request->input('start_date', now()->subDays(30));
        $endDate = $request->input('end_date', now());

        $statistics = [
            'total_logs' => AuditLog::whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_action' => AuditLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'by_event_type' => AuditLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('event_type', DB::raw('count(*) as count'))
                ->groupBy('event_type')
                ->get(),
            'by_user' => AuditLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('user_id', DB::raw('count(*) as count'))
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->with('user')
                ->get(),
            'top_models' => AuditLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('model_type', DB::raw('count(*) as count'))
                ->whereNotNull('model_type')
                ->groupBy('model_type')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    $item->model_name = class_basename($item->model_type);
                    return $item;
                }),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Export audit logs to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        // Check authorization
        if (!auth()->user()->hasRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to export audit logs.');
        }

        $query = AuditLog::with('user');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $query->orderBy('created_at', 'desc');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"',
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Action',
                'Model Type',
                'Model ID',
                'Description',
                'Event Type',
                'IP Address',
            ]);

            // Stream results in chunks
            $query->chunk(500, function($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user ? $log->user->name : 'System',
                        $log->action,
                        $log->model_name,
                        $log->model_id ?? '',
                        $log->description,
                        $log->event_type,
                        $log->ip_address,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clean up old audit logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanup(Request $request)
    {
        // Only super-admin can cleanup logs
        if (!auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized access to cleanup audit logs.');
        }

        $request->validate([
            'days_to_keep' => 'required|integer|min:30|max:365',
        ]);

        $daysToKeep = $request->input('days_to_keep', 90);
        $deletedCount = $this->auditLogService->cleanupOldLogs($daysToKeep);

        return redirect()->route('audit-logs.index')
            ->with('success', "Successfully deleted {$deletedCount} old audit log(s).");
    }
}
