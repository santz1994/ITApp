<?php

namespace App\Jobs;

use App\Export;
use App\ExportLog;
use App\Notifications\ExportCompleted;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * ExportDataJob
 * 
 * Handles asynchronous export processing for large datasets.
 * Queued for exports > 10,000 items.
 */
class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Export $export;
    public string $resourceType;
    public array $filters;
    public array $columns;
    public string $format;
    public int $userId;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;     // Retry 3 times
    public int $backoff = 60;  // Wait 60 seconds between retries

    /**
     * Create a new job instance
     */
    public function __construct(Export $export, string $resourceType, array $filters, array $columns, string $format, int $userId)
    {
        $this->export = $export;
        $this->resourceType = $resourceType;
        $this->filters = $filters;
        $this->columns = $columns;
        $this->format = $format;
        $this->userId = $userId;
    }

    /**
     * Execute the job
     */
    public function handle()
    {
        try {
            $startTime = microtime(true);

            // Mark as processing
            $this->export->markAsProcessing();

            ExportLog::create([
                'export_id' => $this->export->export_id,
                'event' => 'processing',
                'message' => 'Async export job started processing',
            ]);

            // Get model class
            $modelClass = $this->resourceType === 'assets' ? \App\Asset::class : \App\Ticket::class;

            // Build query with filters
            $query = $modelClass::query();

            if ($this->resourceType === 'assets') {
                $query = $this->applyAssetFilters($query);
            } elseif ($this->resourceType === 'tickets') {
                $query = $this->applyTicketFilters($query);
            }

            // Get data
            $data = $query->get();
            $totalItems = $data->count();

            // Generate file based on format
            switch ($this->format) {
                case 'csv':
                    $filePath = $this->generateCSVFile($data);
                    break;
                case 'excel':
                    $filePath = $this->generateExcelFile($data);
                    break;
                case 'json':
                    $filePath = $this->generateJSONFile($data);
                    break;
                default:
                    throw new \Exception("Unsupported export format: {$this->format}");
            }

            // Update export record
            $fileSize = Storage::disk('exports')->size($filePath);
            $this->export->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'exported_items' => $totalItems,
                'completed_at' => now(),
            ]);

            // Log completion
            $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            ExportLog::createCompletion($this->export->export_id, $totalItems, (int)$duration);

            // Send email notification
            $user = User::find($this->userId);
            if ($user) {
                Notification::send($user, new ExportCompleted($this->export));
                $this->export->update(['email_sent_at' => now()]);
            }

        } catch (Throwable $e) {
            $this->failed($e);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception)
    {
        // Update export status to failed
        $this->export->markAsFailed($exception->getMessage());

        // Log failure
        ExportLog::createFailure($this->export->export_id, $exception->getMessage());

        // Send failure email
        $user = User::find($this->userId);
        if ($user) {
            // In real implementation, send failure notification
            Notification::send($user, new ExportCompleted($this->export));
        }
    }

    /**
     * Apply asset filters
     */
    protected function applyAssetFilters($query)
    {
        if (isset($this->filters['status_id'])) {
            $query->where('status_id', $this->filters['status_id']);
        }

        if (isset($this->filters['location_id'])) {
            $query->where('location_id', $this->filters['location_id']);
        }

        if (isset($this->filters['assigned_to'])) {
            $query->where('assigned_to', $this->filters['assigned_to']);
        }

        if (isset($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    /**
     * Apply ticket filters
     */
    protected function applyTicketFilters($query)
    {
        if (isset($this->filters['status_id'])) {
            $query->whereIn('status_id', (array)$this->filters['status_id']);
        }

        if (isset($this->filters['priority_id'])) {
            $query->where('priority_id', $this->filters['priority_id']);
        }

        if (isset($this->filters['is_open']) !== null) {
            $query->where('is_open', $this->filters['is_open']);
        }

        if (isset($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    /**
     * Generate CSV file
     */
    protected function generateCSVFile($data): string
    {
        $fileName = "{$this->export->export_id}.csv";
        $path = "exports/{$fileName}";

        $fp = fopen('php://memory', 'w+');

        // Write BOM for UTF-8
        fwrite($fp, "\xEF\xBB\xBF");

        // Write header
        fputcsv($fp, $this->columns);

        // Write data
        foreach ($data as $item) {
            $row = [];
            foreach ($this->columns as $column) {
                $value = $item[$column] ?? ($item->$column ?? '');
                $row[] = $value;
            }
            fputcsv($fp, $row);
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
    protected function generateExcelFile($data): string
    {
        $fileName = "{$this->export->export_id}.xlsx";
        $path = "exports/{$fileName}";

        // Use PhpSpreadsheet if available
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Write header
            foreach ($this->columns as $index => $column) {
                $sheet->setCellValue([($index + 1), 1], $column);
            }

            // Write data
            foreach ($data as $rowIndex => $item) {
                foreach ($this->columns as $colIndex => $column) {
                    $value = $item[$column] ?? ($item->$column ?? '');
                    $sheet->setCellValue([($colIndex + 1), ($rowIndex + 2)], $value);
                }
            }

            // Auto-fit columns
            foreach (array_keys($this->columns) as $index) {
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
            // Fallback to CSV
            return $this->generateCSVFile($data);
        }

        return $path;
    }

    /**
     * Generate JSON file
     */
    protected function generateJSONFile($data): string
    {
        $fileName = "{$this->export->export_id}.json";
        $path = "exports/{$fileName}";

        $jsonData = [
            'export_id' => $this->export->export_id,
            'resource_type' => $this->resourceType,
            'format' => $this->format,
            'exported_at' => now()->toIso8601String(),
            'item_count' => $data->count(),
            'columns' => $this->columns,
            'data' => $data->toArray(),
        ];

        $content = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Storage::disk('exports')->put($path, $content);

        return $path;
    }
}
