<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ExportLog Model
 * 
 * Detailed audit trail for each export operation.
 * Tracks progress, events, and errors.
 */
class ExportLog extends Model
{
    use HasFactory;

    protected $table = 'export_logs';

    public $timestamps = false;

    protected $fillable = [
        'export_id',
        'event',
        'message',
        'processed_items',
        'current_batch',
        'total_batches',
        'error_message',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Set created_at to now if not provided
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Relationship: Log belongs to export
     */
    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class, 'export_id', 'export_id');
    }

    /**
     * Scope: Logs for specific export
     */
    public function scopeForExport($query, string $exportId)
    {
        return $query->where('export_id', $exportId);
    }

    /**
     * Scope: Get successful logs
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('event', ['progress', 'completed']);
    }

    /**
     * Scope: Get failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('event', 'failed');
    }

    /**
     * Scope: Get by event type
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Check if log represents success
     */
    public function isSuccess(): bool
    {
        return $this->event === 'completed' && !$this->error_message;
    }

    /**
     * Check if log represents failure
     */
    public function isFailed(): bool
    {
        return $this->event === 'failed' || !is_null($this->error_message);
    }

    /**
     * Get progress percentage (0-100)
     */
    public function getProgress(): float
    {
        if (!$this->total_batches || $this->total_batches === 0) {
            return 0;
        }

        return round(($this->current_batch / $this->total_batches) * 100, 2);
    }

    /**
     * Get human-readable event name
     */
    public function getEventLabel(): string
    {
        return match($this->event) {
            'initiated' => 'Export Initiated',
            'processing' => 'Processing',
            'progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'deleted' => 'Deleted',
            default => ucfirst($this->event),
        };
    }

    /**
     * Create progress log
     */
    public static function createProgress(string $exportId, int $processedItems, int $currentBatch, int $totalBatches): self
    {
        return self::create([
            'export_id' => $exportId,
            'event' => 'progress',
            'message' => "Processing batch {$currentBatch} of {$totalBatches}",
            'processed_items' => $processedItems,
            'current_batch' => $currentBatch,
            'total_batches' => $totalBatches,
        ]);
    }

    /**
     * Create completion log
     */
    public static function createCompletion(string $exportId, int $totalItems, int $durationMs): self
    {
        return self::create([
            'export_id' => $exportId,
            'event' => 'completed',
            'message' => "Export completed successfully ({$totalItems} items in " . ($durationMs / 1000) . "s)",
            'processed_items' => $totalItems,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Create failure log
     */
    public static function createFailure(string $exportId, string $errorMessage): self
    {
        return self::create([
            'export_id' => $exportId,
            'event' => 'failed',
            'message' => 'Export failed',
            'error_message' => $errorMessage,
        ]);
    }
}
