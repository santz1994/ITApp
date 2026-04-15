<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveConflictRequest;
use App\Http\Requests\BulkResolveConflictsRequest;
use App\Import;
use App\ImportConflict;
use App\Services\ConflictResolutionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConflictResolutionController extends Controller
{
    protected $conflictResolutionService;

    public function __construct(ConflictResolutionService $conflictResolutionService)
    {
        $this->conflictResolutionService = $conflictResolutionService;
    }

    /**
     * Get conflicts for an import (API).
     *
     * @param string $importId
     * @return JsonResponse
     */
    public function index($importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('view', $import);

        $conflicts = $this->conflictResolutionService->getUnresolvedConflicts($importId);

        return response()->json([
            'success' => true,
            'import_id' => $importId,
            'conflicts' => $conflicts,
            'count' => $conflicts->count(),
        ]);
    }

    /**
     * Get conflict statistics (API).
     *
     * @param string $importId
     * @return JsonResponse
     */
    public function statistics($importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('view', $import);

        $stats = $this->conflictResolutionService->getConflictStatistics($importId);

        return response()->json([
            'success' => true,
            'import_id' => $importId,
            'statistics' => $stats,
        ]);
    }

    /**
     * Get a specific conflict detail (API).
     *
     * @param string $importId
     * @param int $conflictId
     * @return JsonResponse
     */
    public function show($importId, $conflictId)
    {
        $conflict = ImportConflict::where('id', $conflictId)
            ->where('import_id', $importId)
            ->firstOrFail();

        $import = $conflict->import;
        $this->authorize('view', $import);

        return response()->json([
            'success' => true,
            'conflict' => $conflict,
        ]);
    }

    /**
     * Resolve a single conflict (API).
     *
     * @param ResolveConflictRequest $request
     * @param string $importId
     * @param int $conflictId
     * @return JsonResponse
     */
    public function resolve(ResolveConflictRequest $request, $importId, $conflictId)
    {
        $conflict = ImportConflict::where('id', $conflictId)
            ->where('import_id', $importId)
            ->firstOrFail();

        $import = $conflict->import;
        $this->authorize('update', $import);

        try {
            $resolutionChoice = $this->conflictResolutionService->resolveConflict(
                $conflictId,
                $request->validated()['resolution'],
                $request->validated()['details'] ?? [],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Conflict resolved successfully',
                'resolution_choice' => $resolutionChoice,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conflict: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk resolve conflicts (API).
     *
     * @param BulkResolveConflictsRequest $request
     * @param string $importId
     * @return JsonResponse
     */
    public function bulkResolve(BulkResolveConflictsRequest $request, $importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('update', $import);

        try {
            $results = $this->conflictResolutionService->bulkResolveConflicts(
                $importId,
                $request->validated()['resolutions'],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => "Resolved {$results->count()} conflicts",
                'count' => $results->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk resolution failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Auto-resolve conflicts (API).
     *
     * @param Request $request
     * @param string $importId
     * @return JsonResponse
     */
    public function autoResolve(Request $request, $importId)
    {
        $request->validate([
            'strategy' => 'required|in:skip,update,merge',
        ]);

        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('update', $import);

        try {
            $results = $this->conflictResolutionService->autoResolveConflicts(
                $importId,
                $request->strategy,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-resolution failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get resolution history (API).
     *
     * @param string $importId
     * @return JsonResponse
     */
    public function history($importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('view', $import);

        $resolutionHistory = $this->conflictResolutionService->getResolutionHistory($importId);

        return response()->json([
            'success' => true,
            'history' => $resolutionHistory,
            'count' => $resolutionHistory->count(),
        ]);
    }

    /**
     * Export conflict report (API).
     *
     * @param string $importId
     * @return JsonResponse
     */
    public function exportReport($importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('view', $import);

        $report = $this->conflictResolutionService->exportConflictReport($importId);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Rollback resolutions (API).
     *
     * @param Request $request
     * @param string $importId
     * @return JsonResponse
     */
    public function rollback(Request $request, $importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('update', $import);

        try {
            $count = $this->conflictResolutionService->rollbackResolutions($importId);

            return response()->json([
                'success' => true,
                'message' => "Rolled back $count resolutions",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: ' . $e->getMessage(),
            ], 422);
        }
    }
}
