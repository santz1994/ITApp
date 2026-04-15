<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\AssetExportRequest;
use App\Http\Requests\TicketExportRequest;
use App\Http\Controllers\Controller;
use App\Export;
use App\Asset;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ExportController
 * 
 * Handles export operations for assets and tickets.
 * Supports CSV, Excel, and JSON formats.
 * Provides async processing for large exports.
 */
class ExportController extends Controller
{
    /**
     * POST /api/v1/assets/export
     * Export assets data
     */
    public function exportAssets(AssetExportRequest $request)
    {
        try {
            $params = $request->getExportParams();
            
            // Perform export
            $result = Asset::performExport(
                $params['format'],
                $params['filters'],
                $params['columns'],
                $params['async'],
                false,
                auth()->id()
            );

            // Determine response code
            $statusCode = isset($result['file_path']) ? 200 : 202;

            // If synchronous export, stream file
            if (isset($result['file_path']) && !$params['async']) {
                return $this->streamFile($result['file_path'], $params['format']);
            }

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/v1/tickets/export
     * Export tickets data
     */
    public function exportTickets(TicketExportRequest $request)
    {
        try {
            $params = $request->getExportParams();
            
            // Perform export
            $result = Ticket::performExport(
                $params['format'],
                $params['filters'],
                $params['columns'],
                $params['async'],
                false,
                auth()->id()
            );

            // Determine response code
            $statusCode = isset($result['file_path']) ? 200 : 202;

            // If synchronous export, stream file
            if (isset($result['file_path']) && !$params['async']) {
                return $this->streamFile($result['file_path'], $params['format']);
            }

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/exports
     * List export history
     */
    public function listExports(Request $request)
    {
        try {
            $query = Export::query()
                ->where('created_by', auth()->id());

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Filter by resource type
            if ($request->has('resource_type')) {
                $query->where('resource_type', $request->input('resource_type'));
            }

            // Filter by format
            if ($request->has('format')) {
                $query->where('export_format', $request->input('format'));
            }

            // Filter by date range
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Pagination
            $limit = min($request->input('limit', 20), 100); // Max 100 per page
            $exports = $query->latest('created_at')->paginate($limit);

            // Transform data
            $data = [];
            foreach ($exports as $export) {
                $data[] = $this->formatExportForResponse($export);
            }

            return response()->json([
                'data' => $data,
                'pagination' => [
                    'total' => $exports->total(),
                    'per_page' => $exports->perPage(),
                    'current_page' => $exports->currentPage(),
                    'last_page' => $exports->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve exports: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/exports/{export_id}
     * Get export status and details
     */
    public function getExportStatus($exportId)
    {
        try {
            $export = Export::where('export_id', $exportId)
                ->where('created_by', auth()->id())
                ->firstOrFail();

            return response()->json($this->formatExportForResponse($export));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve export: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/exports/{export_id}/download
     * Download exported file
     */
    public function downloadExport($exportId)
    {
        try {
            $export = Export::where('export_id', $exportId)
                ->where('created_by', auth()->id())
                ->firstOrFail();

            // Check if export is ready for download
            if (!$export->canDownload()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Export is not available for download',
                    'export_status' => $export->status,
                ], 400);
            }

            // Check if file exists
            if (!Storage::disk('exports')->exists($export->file_path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Export file not found',
                ], 404);
            }

            // Increment download count
            $export->incrementDownloadCount();

            // Stream file
            return $this->streamFile($export->file_path, $export->export_format);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download export: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/v1/exports/{export_id}/retry
     * Retry failed export
     */
    public function retryExport($exportId)
    {
        try {
            $export = Export::where('export_id', $exportId)
                ->where('created_by', auth()->id())
                ->firstOrFail();

            // Check if export is failed
            if ($export->status !== 'failed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only failed exports can be retried',
                    'export_status' => $export->status,
                ], 400);
            }

            // Determine resource type model
            $modelClass = $export->resource_type === 'assets' ? Asset::class : Ticket::class;

            // Retry export
            $result = $modelClass::performExport(
                $export->export_format,
                $export->filter_config ?? [],
                $export->column_config ?? [],
                true, // Force async
                false,
                auth()->id()
            );

            return response()->json($result, 202);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retry export: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/exports/{export_id}/logs
     * Get detailed export logs
     */
    public function getExportLogs($exportId, Request $request)
    {
        try {
            $export = Export::where('export_id', $exportId)
                ->where('created_by', auth()->id())
                ->firstOrFail();

            $query = $export->logs();

            // Filter by event type
            if ($request->has('event')) {
                $query->where('event', $request->input('event'));
            }

            // Pagination
            $limit = min($request->input('limit', 50), 500);
            $logs = $query->latest('created_at')->paginate($limit);

            // Transform logs
            $data = [];
            foreach ($logs as $log) {
                $data[] = [
                    'id' => $log->id,
                    'event' => $log->event,
                    'event_label' => $log->getEventLabel(),
                    'message' => $log->message,
                    'processed_items' => $log->processed_items,
                    'current_batch' => $log->current_batch,
                    'total_batches' => $log->total_batches,
                    'progress_percent' => $log->getProgress(),
                    'error_message' => $log->error_message,
                    'duration_ms' => $log->duration_ms,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            }

            return response()->json([
                'export_id' => $export->export_id,
                'data' => $data,
                'pagination' => [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve logs: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Stream file to user
     */
    protected function streamFile(string $filePath, string $format)
    {
        $fileName = Str::slug(pathinfo($filePath, PATHINFO_FILENAME)) . '.' . $this->getFileExtension($format);

        $headers = $this->getFileHeaders($format, $fileName);

        // Read file content
        $content = Storage::disk('exports')->get($filePath);

        return response($content, 200, $headers);
    }

    /**
     * Get appropriate file extension
     */
    protected function getFileExtension(string $format): string
    {
        return match($format) {
            'csv' => 'csv',
            'excel' => 'xlsx',
            'json' => 'json',
            default => 'txt',
        };
    }

    /**
     * Get HTTP headers for file download
     */
    protected function getFileHeaders(string $format, string $fileName): array
    {
        return [
            'Content-Type' => match($format) {
                'csv' => 'text/csv; charset=utf-8',
                'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'json' => 'application/json; charset=utf-8',
                default => 'application/octet-stream',
            },
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
    }

    /**
     * Format export data for API response
     */
    protected function formatExportForResponse(Export $export): array
    {
        return [
            'export_id' => $export->export_id,
            'resource_type' => $export->resource_type,
            'format' => $export->export_format,
            'status' => $export->status,
            'total_items' => $export->total_items,
            'exported_items' => $export->exported_items,
            'failed_items' => $export->failed_items,
            'success_rate' => $export->success_rate,
            'file_size' => $export->file_size ? $this->formatBytes($export->file_size) : null,
            'created_at' => $export->created_at->toIso8601String(),
            'completed_at' => $export->completed_at?->toIso8601String(),
            'duration_seconds' => $export->getDurationSeconds(),
            'download_url' => $export->canDownload() ? "/api/v1/exports/{$export->export_id}/download" : null,
            'download_count' => $export->download_count,
            'expires_at' => $export->expires_at?->toIso8601String(),
            'error_details' => $export->error_details,
        ];
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
