<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Export Model
 * 
 * Tracks all data exports for audit trail and history.
 * Provides status tracking for both sync and async exports.
 */
class Export extends Model
{
    use HasFactory;

    protected $table = 'exports';

    protected $fillable = [
        'export_id',
        'resource_type',
        'export_format',
        'status',
        'total_items',
        'exported_items',
        'failed_items',
        'file_size',
        'file_path',
        'filter_config',
        'column_config',
        'created_by',
        'completed_at',
        'expires_at',
        'download_count',
        'error_details',
        'email_sent_at',
    ];

    protected $casts = [
        'filter_config' => 'array',
        'column_config' => 'array',
        'error_details' => 'array',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    /**
     * Relationship: Export created by user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Export has many logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ExportLog::class, 'export_id', 'export_id');
    }

    /**
     * Scope: Filter by resource type
     */
    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope: Filter by format
     */
    public function scopeByFormat($query, string $format)
    {
        return $query->where('export_format', $format);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by creator
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Get successful exports
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed')->where('failed_items', 0);
    }

    /**
     * Scope: Get failed exports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get expired exports
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope: Get recent exports (last N days)
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_items === 0) {
            return 0;
        }

        return round(($this->exported_items / $this->total_items) * 100, 2);
    }

    /**
     * Check if export is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if export failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if export has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get download URL
     */
    public function getDownloadUrl(): string
    {
        return "/api/v1/exports/{$this->export_id}/download";
    }

    /**
     * Check if can be downloaded
     */
    public function canDownload(): bool
    {
        return $this->isCompleted() && !$this->hasExpired();
    }

    /**
     * Increment download counter
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        $this->save();
    }

    /**
     * Get duration in seconds
     */
    public function getDurationSeconds(): ?float
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->created_at);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(int $exportedItems, ?int $failedItems = 0)
    {
        $this->update([
            'status' => 'completed',
            'exported_items' => $exportedItems,
            'failed_items' => $failedItems,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_details' => [
                'message' => $errorMessage,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Send completion email
     */
    public function sendCompletionEmail()
    {
        if ($this->creator && $this->email_sent_at === null) {
            \Illuminate\Support\Facades\Notification::send($this->creator, new \App\Notifications\ExportCompleted($this));
            $this->update(['email_sent_at' => now()]);
        }
    }
}
