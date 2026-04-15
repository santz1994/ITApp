<?php

namespace App\Traits;

use App\Export;
use App\ExportLog;
use App\Jobs\ExportDataJob;
use App\Notifications\ExportCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Exception;

/**
 * ExportBuilder Trait
 * 
 * Provides reusable export functionality for any Eloquent model.
 * Supports CSV, Excel, and JSON exports with async processing for large datasets.
 * 
 * Usage:
 *   Asset::exportToCSV(['status_id' => 2], ['id', 'name', 'status'])
 *   Ticket::exportToExcelAsync(['is_open' => true], ['subject', 'priority'], 'tickets.xlsx')
 */
trait ExportBuilder
{
    /**
     * Export to CSV (synchronous or async based on size)
     */
    public static function exportToCSV(array $filters = [], array $columns = [], bool $async = false, bool $dryRun = false, ?int $userId = null)
    {
        return static::performExport('csv', $filters, $columns, $async, $dryRun, $userId);
    }

    /**
     * Export to Excel (synchronous or async based on size)
     */
    public static function exportToExcel(array $filters = [], array $columns = [], bool $async = false, bool $dryRun = false, ?int $userId = null)
    {
        return static::performExport('excel', $filters, $columns, $async, $dryRun, $userId);
    }

    /**
     * Export to JSON (synchronous)
     */
    public static function exportToJSON(array $filters = [], array $columns = [], bool $dryRun = false, ?int $userId = null)
    {
        return static::performExport('json', $filters, $columns, false, $dryRun, $userId);
    }

    /**
     * Export to CSV asynchronously
     */
    public static function exportToCSVAsync(array $filters = [], array $columns = [], ?int $userId = null)
    {
        return static::performExport('csv', $filters, $columns, true, false, $userId);
    }

    /**
     * Export to Excel asynchronously
     */
    public static function exportToExcelAsync(array $filters = [], array $columns = [], ?int $userId = null)
    {
        return static::performExport('excel', $filters, $columns, true, false, $userId);
    }

    /**
     * Core export orchestration
     */
    protected static function performExport(string $format, array $filters, array $columns, bool $async, bool $dryRun, ?int $userId = null)
    {
        try {
            // Validate input
            $resourceType = static::getResourceType();
            if (!$resourceType) {
                throw new Exception('Resource type not determined');
            }

            if (empty($columns)) {
                throw new Exception('At least one column must be selected');
            }

            // Get current user
            $userId = $userId ?? auth()->id();
            if (!$userId) {
                throw new Exception('User authentication required for exports');
            }

            // Build query with filters
            $query = static::buildExportQuery($filters);
            $itemCount = $query->count();

            // Determine if async is needed
            $shouldBeAsync = $async || static::determineAsync($itemCount);

            // Dry-run mode: validate only
            if ($dryRun) {
                return [
                    'status' => 'DRY_RUN_SUCCESS',
                    'message' => 'Validation passed. No changes persisted.',
                    'preview_count' => min($itemCount, 10),
                    'total_items' => $itemCount,
                    'estimated_file_size' => static::estimateFileSize($itemCount, $format),
                    'format' => $format,
                ];
            }

            // Create export record
            $export = static::createExportRecord($resourceType, $format, $itemCount, $filters, $columns, $userId);

            // Async processing
            if ($shouldBeAsync) {
                ExportDataJob::dispatch($export, $resourceType, $filters, $columns, $format, $userId);
                
                ExportLog::create([
                    'export_id' => $export->export_id,
                    'event' => 'initiated',
                    'message' => 'Async export job queued',
                    'processed_items' => 0,
                    'total_items' => $itemCount,
                ]);

                return [
                    'status' => 'pending',
                    'export_id' => $export->export_id,
                    'message' => "Export queued for processing. Email notification will be sent when complete.",
                    'estimated_wait' => static::estimateWaitTime($itemCount),
                    'check_status_url' => "/api/v1/exports/{$export->export_id}",
                ];
            }

            // Synchronous processing - stream the response
            $filePath = static::generateExportFile($export, $resourceType, $filters, $columns, $format);

            // Update export record with completion details
            $fileSize = Storage::disk('exports')->size($filePath);
            $export->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'exported_items' => $itemCount,
                'completed_at' => now(),
            ]);

            ExportLog::create([
                'export_id' => $export->export_id,
                'event' => 'completed',
                'message' => 'Export completed successfully',
                'processed_items' => $itemCount,
            ]);

            // Return download info
            return [
                'status' => 'completed',
                'export_id' => $export->export_id,
                'total_items' => $itemCount,
                'file_size' => $fileSize,
                'download_url' => "/api/v1/exports/{$export->export_id}/download",
                'file_path' => $filePath,
                'message' => 'Export completed. Ready for download.',
            ];

        } catch (Exception $e) {
            // Log export error
            if (isset($export)) {
                $export->update([
                    'status' => 'failed',
                    'error_details' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'timestamp' => now()->toIso8601String(),
                    ],
                ]);

                ExportLog::create([
                    'export_id' => $export->export_id,
                    'event' => 'failed',
                    'message' => 'Export failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            return [
                'status' => 'error',
                'message' => 'Export failed: ' . $e->getMessage(),
                'error_details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build query with applied filters
     */
    protected static function buildExportQuery(array $filters = [])
    {
        $query = static::query();

        // Apply resource-specific filters
        $resourceType = static::getResourceType();
        
        if ($resourceType === 'assets') {
            return static::applyAssetFilters($query, $filters);
        } elseif ($resourceType === 'tickets') {
            return static::applyTicketFilters($query, $filters);
        }

        return $query;
    }

    /**
     * Apply filters for assets (uses FilterBuilder if available)
     */
    protected static function applyAssetFilters($query, array $filters = [])
    {
        if (isset($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (isset($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Support FilterBuilder if available
        if (method_exists($query, 'applyCustomFilters')) {
            $query->applyCustomFilters($filters);
        }

        return $query;
    }

    /**
     * Apply filters for tickets (uses FilterBuilder if available)
     */
    protected static function applyTicketFilters($query, array $filters = [])
    {
        if (isset($filters['status_id'])) {
            $query->whereIn('status_id', (array)$filters['status_id']);
        }

        if (isset($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        if (isset($filters['type_id'])) {
            $query->where('type_id', $filters['type_id']);
        }

        if (isset($filters['is_open']) !== null) {
            $query->where('is_open', $filters['is_open']);
        }

        if (isset($filters['is_resolved']) !== null) {
            $query->where('is_resolved', $filters['is_resolved']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Support FilterBuilder if available
        if (method_exists($query, 'applyCustomFilters')) {
            $query->applyCustomFilters($filters);
        }

        return $query;
    }

    /**
     * Select specific columns for export
     */
    protected static function selectExportColumns($query, array $columns)
    {
        $validColumns = static::getExportableColumns();
        
        // Filter to only valid columns
        $safeColumns = array_filter($columns, function ($col) use ($validColumns) {
            return in_array($col, $validColumns);
        });

        if (!empty($safeColumns)) {
            $query->select($safeColumns);
        }

        return $query;
    }

    /**
     * Get list of exportable columns for this resource
     */
    protected static function getExportableColumns(): array
    {
        $resourceType = static::getResourceType();

        if ($resourceType === 'assets') {
            return [
                'id', 'name', 'asset_tag', 'serial_number', 'status_id', 
                'location_id', 'assigned_to', 'manufacturer_id', 'asset_type_id',
                'purchase_date', 'warranty_expiry_date', 'created_at', 'updated_at'
            ];
        }

        if ($resourceType === 'tickets') {
            return [
                'id', 'ticket_code', 'subject', 'description', 'status_id',
                'priority_id', 'type_id', 'assigned_to', 'created_by',
                'is_open', 'is_resolved', 'created_at', 'due_date', 'updated_at'
            ];
        }

        return [];
    }

    /**
     * Determine if export should be async (>10,000 items)
     */
    protected static function determineAsync(int $itemCount): bool
    {
        return $itemCount > 10000;
    }

    /**
     * Estimate wait time for async export
     */
    protected static function estimateWaitTime(int $itemCount): string
    {
        $seconds = ($itemCount / 5000) * 5; // Rough estimate: 5k items per 5 seconds
        
        if ($seconds < 60) {
            return "Less than 1 minute";
        } elseif ($seconds < 300) {
            $minutes = ceil($seconds / 60);
            return "$minutes minute" . ($minutes > 1 ? 's' : '');
        } else {
            $minutes = ceil($seconds / 60);
            return "Up to $minutes minutes";
        }
    }

    /**
     * Estimate file size
     */
    protected static function estimateFileSize(int $itemCount, string $format): string
    {
        // Rough estimates
        switch ($format) {
            case 'csv':
                // ~250 bytes per row average
                $bytes = $itemCount * 250;
                break;
            case 'excel':
                // ~500 bytes per row average (formatting overhead)
                $bytes = $itemCount * 500;
                break;
            case 'json':
                // ~300 bytes per row average
                $bytes = $itemCount * 300;
                break;
            default:
                $bytes = $itemCount * 250;
        }

        return static::formatBytes($bytes);
    }

    /**
     * Format bytes to human readable
     */
    protected static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Create export record in database
     */
    protected static function createExportRecord(string $resourceType, string $format, int $itemCount, array $filters, array $columns, int $userId): Export
    {
        return Export::create([
            'export_id' => 'export-' . Str::uuid(),
            'resource_type' => $resourceType,
            'export_format' => $format,
            'status' => 'pending',
            'total_items' => $itemCount,
            'exported_items' => 0,
            'failed_items' => 0,
            'created_by' => $userId,
            'filter_config' => $filters,
            'column_config' => $columns,
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Generate export file (synchronous processing)
     */
    protected static function generateExportFile(Export $export, string $resourceType, array $filters, array $columns, string $format): string
    {
        // Get data
        $query = static::buildExportQuery($filters);
        $data = static::selectExportColumns($query, $columns)->get()->toArray();

        // Generate file based on format
        switch ($format) {
            case 'csv':
                return static::generateCSVFile($export, $data, $columns);
            case 'excel':
                return static::generateExcelFile($export, $data, $columns);
            case 'json':
                return static::generateJSONFile($export, $data, $columns);
            default:
                throw new Exception("Unsupported export format: $format");
        }
    }

    /**
     * Generate CSV file
     */
    protected static function generateCSVFile(Export $export, array $data, array $columns): string
    {
        $fileName = "{$export->export_id}.csv";
        $path = "exports/{$fileName}";

        $fp = fopen('php://memory', 'w+');

        // Write BOM for UTF-8
        fwrite($fp, "\xEF\xBB\xBF");

        // Write header
        fputcsv($fp, $columns);

        // Write data
        foreach ($data as $row) {
            $csvRow = array_map(function ($col) use ($row) {
                return $row[$col] ?? '';
            }, $columns);
            fputcsv($fp, $csvRow);
        }

        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);

        // Store file
        Storage::disk('exports')->put($path, $content);

        return $path;
    }

    /**
     * Generate Excel file
     */
    protected static function generateExcelFile(Export $export, array $data, array $columns): string
    {
        $fileName = "{$export->export_id}.xlsx";
        $path = "exports/{$fileName}";

        // Use PhpSpreadsheet if available
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Write header
            foreach ($columns as $index => $column) {
                $sheet->setCellValue([($index + 1), 1], $column);
            }

            // Write data
            foreach ($data as $rowIndex => $row) {
                foreach ($columns as $colIndex => $column) {
                    $value = $row[$column] ?? '';
                    $sheet->setCellValue([($colIndex + 1), ($rowIndex + 2)], $value);
                }
            }

            // Auto-fit columns
            foreach (array_keys($columns) as $index) {
                $sheet->getColumnDimensionByColumn($index + 1)->setAutoSize(true);
            }

            // Save
            $spreadsheet->setActiveSheetIndex(0);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Create temp file and save
            $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
            $writer->save($tmpFile);
            
            // Store in exports disk
            $content = file_get_contents($tmpFile);
            Storage::disk('exports')->put($path, $content);
            
            // Clean up temp file
            @unlink($tmpFile);
        } else {
            // Fallback to CSV if PhpSpreadsheet not available
            return static::generateCSVFile($export, $data, $columns);
        }

        return $path;
    }

    /**
     * Generate JSON file
     */
    protected static function generateJSONFile(Export $export, array $data, array $columns): string
    {
        $fileName = "{$export->export_id}.json";
        $path = "exports/{$fileName}";

        $jsonData = [
            'export_id' => $export->export_id,
            'resource_type' => $export->resource_type,
            'exported_at' => now()->toIso8601String(),
            'item_count' => count($data),
            'data' => $data,
        ];

        Storage::disk('exports')->put($path, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    /**
     * Get export history
     */
    public static function getExportHistory(int $limit = 50, ?string $status = null, ?int $userId = null)
    {
        $query = Export::query()
            ->where('resource_type', static::getResourceType());

        if ($status) {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('created_by', $userId);
        } else {
            $query->where('created_by', auth()->id());
        }

        return $query->latest('created_at')->paginate($limit);
    }

    /**
     * Get export status
     */
    public static function getExportStatus(string $exportId)
    {
        $export = Export::where('export_id', $exportId)->firstOrFail();

        return [
            'export_id' => $export->export_id,
            'resource_type' => $export->resource_type,
            'format' => $export->export_format,
            'status' => $export->status,
            'total_items' => $export->total_items,
            'exported_items' => $export->exported_items,
            'failed_items' => $export->failed_items,
            'success_rate' => $export->total_items > 0 
                ? round(($export->exported_items / $export->total_items) * 100, 2) 
                : 0,
            'file_size' => $export->file_size ? static::formatBytes($export->file_size) : null,
            'created_at' => $export->created_at->toIso8601String(),
            'completed_at' => $export->completed_at?->toIso8601String(),
            'duration_seconds' => $export->completed_at ? $export->completed_at->diffInSeconds($export->created_at) : null,
            'download_url' => $export->status === 'completed' ? "/api/v1/exports/{$export->export_id}/download" : null,
            'download_count' => $export->download_count,
            'expires_at' => $export->expires_at?->toIso8601String(),
            'error_details' => $export->error_details,
        ];
    }

    /**
     * Retry failed export
     */
    public static function retryFailedExport(string $exportId)
    {
        $export = Export::where('export_id', $exportId)->firstOrFail();

        if ($export->status !== 'failed') {
            throw new Exception('Only failed exports can be retried');
        }

        // Create new export with same parameters
        return static::performExport(
            $export->export_format,
            $export->filter_config ?? [],
            $export->column_config ?? [],
            true,
            false,
            $export->created_by
        );
    }

    /**
     * Get resource type (asset or ticket)
     */
    protected static function getResourceType(): ?string
    {
        $model = static::class;
        
        if (strpos($model, 'Asset') !== false) {
            return 'assets';
        } elseif (strpos($model, 'Ticket') !== false) {
            return 'tickets';
        }

        return null;
    }
}
