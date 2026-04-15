<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveConflictRequest;
use App\Http\Requests\BulkResolveConflictsRequest;
use App\Import;
use App\ImportConflict;
use App\ResolutionChoice;
use App\Services\ConflictResolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ConflictResolutionController extends Controller
{
    protected $conflictResolutionService;

    public function __construct(ConflictResolutionService $conflictResolutionService)
    {
        $this->conflictResolutionService = $conflictResolutionService;
    }

    /**
     * Display conflicts for an import.
     *
     * @param string $importId
     * @return View
     */
    public function index($importId)
    {
        // Verify user has access to this import
        $import = Import::where('import_id', $importId)
            ->firstOrFail();

        $this->authorize('view', $import);

        $conflicts = $this->conflictResolutionService->getUnresolvedConflicts($importId);
        $statistics = $this->conflictResolutionService->getConflictStatistics($importId);
        $conflictsByType = $this->conflictResolutionService->getConflictsByType($importId);

        return view('imports.conflicts.index', [
            'import' => $import,
            'conflicts' => $conflicts,
            'statistics' => $statistics,
            'conflictsByType' => $conflictsByType,
        ]);
    }

    /**
     * Show detail view for a specific conflict.
     *
     * @param int $conflictId
     * @return View
     */
    public function show($conflictId)
    {
        $conflict = ImportConflict::with('import')->findOrFail($conflictId);
        $import = $conflict->import;

        $this->authorize('view', $import);

        $relatedConflicts = ImportConflict::forImport($conflict->import_id)
            ->where('id', '!=', $conflictId)
            ->byConflictType($conflict->conflict_type)
            ->limit(5)
            ->get();

        return view('imports.conflicts.show', [
            'conflict' => $conflict,
            'import' => $import,
            'relatedConflicts' => $relatedConflicts,
        ]);
    }

    /**
     * Resolve a single conflict.
     *
     * @param ResolveConflictRequest $request
     * @param int $conflictId
     * @return RedirectResponse|JsonResponse
     */
    public function resolve(ResolveConflictRequest $request, $conflictId)
    {
        $conflict = ImportConflict::findOrFail($conflictId);
        $import = $conflict->import;

        $this->authorize('update', $import);

        try {
            $resolutionChoice = $this->conflictResolutionService->resolveConflict(
                $conflictId,
                $request->validated()['resolution'],
                $request->validated()['details'] ?? [],
                auth()->id()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conflict resolved successfully',
                    'resolution_choice' => $resolutionChoice,
                ]);
            }

            return back()->with('success', 'Conflict resolved successfully');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resolve conflict: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Failed to resolve conflict: ' . $e->getMessage());
        }
    }

    /**
     * Bulk resolve conflicts.
     *
     * @param BulkResolveConflictsRequest $request
     * @param string $importId
     * @return JsonResponse|RedirectResponse
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

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Resolved {$results->count()} conflicts",
                    'count' => $results->count(),
                ]);
            }

            return back()->with('success', "Successfully resolved {$results->count()} conflicts");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk resolution failed: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Bulk resolution failed: ' . $e->getMessage());
        }
    }

    /**
     * Auto-resolve conflicts based on strategy.
     *
     * @param Request $request
     * @param string $importId
     * @return JsonResponse|RedirectResponse
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

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'results' => $results,
                ]);
            }

            return back()->with('success', "Auto-resolved {$results['resolved']} conflicts");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auto-resolution failed: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Auto-resolution failed: ' . $e->getMessage());
        }
    }

    /**
     * View resolution history for an import.
     *
     * @param string $importId
     * @return View
     */
    public function history($importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('view', $import);

        $resolutionHistory = $this->conflictResolutionService->getResolutionHistory($importId);

        return view('imports.conflicts.history', [
            'import' => $import,
            'resolutionHistory' => $resolutionHistory,
        ]);
    }

    /**
     * Export conflict report.
     *
     * @param string $importId
     * @return JsonResponse
     */
    public function exportReport($importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('view', $import);

        $report = $this->conflictResolutionService->exportConflictReport($importId);

        return response()->json($report);
    }

    /**
     * Rollback resolutions.
     *
     * @param Request $request
     * @param string $importId
     * @return RedirectResponse|JsonResponse
     */
    public function rollback(Request $request, $importId)
    {
        $import = Import::where('import_id', $importId)->firstOrFail();

        $this->authorize('update', $import);

        try {
            $count = $this->conflictResolutionService->rollbackResolutions($importId);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Rolled back $count resolutions",
                    'count' => $count,
                ]);
            }

            return back()->with('success', "Rolled back $count resolutions");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rollback failed: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }
}
