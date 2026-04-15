<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * BulkOperation Model
 *
 * Tracks high-level bulk operation records
 * Provides audit trail and operation history for compliance
 *
 * @property string $operation_id Unique operation identifier
 * @property string $resource_type Type of resource (assets, tickets)
 * @property string $operation_type Type of operation (status_update, assignment, field_update)
 * @property string $status Current status (pending, processing, completed, failed, partial)
 * @property integer $total_items Total items in operation
 * @property integer $processed_items Successfully processed items
 * @property integer $failed_items Failed items
 * @property integer $created_by User ID who initiated operation
 * @property \Carbon\Carbon $created_at Operation start time
 * @property \Carbon\Carbon $completed_at Operation completion time
 * @property array|null $error_details JSON error information
 * @property \Carbon\Carbon $updated_at
 */
class BulkOperation extends Model
{
    protected $table = 'bulk_operations';

    protected $fillable = [
        'operation_id',
        'resource_type',
        'operation_type',
        'status',
        'total_items',
        'processed_items',
        'failed_items',
        'created_by',
        'completed_at',
        'error_details'
    ];

    protected $casts = [
        'total_items' => 'integer',
        'processed_items' => 'integer',
        'failed_items' => 'integer',
        'created_by' => 'integer',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'error_details' => 'json'
    ];

    /**
     * Relationship: Creator user
     */
    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Relationship: Logs for this operation
     */
    public function logs()
    {
        return $this->hasMany(\App\BulkOperationLog::class, 'bulk_operation_id', 'operation_id');
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_items === 0) {
            return 100;
        }
        return round(($this->processed_items / $this->total_items) * 100, 2);
    }

    /**
     * Check if operation fully succeeded
     */
    public function isFullSuccess()
    {
        return $this->failed_items === 0 && $this->status === 'completed';
    }

    /**
     * Check if operation has any failures
     */
    public function hasFailures()
    {
        return $this->failed_items > 0;
    }

    /**
     * Get duration in seconds
     */
    public function getDurationSeconds()
    {
        if (!$this->completed_at) {
            return null;
        }
        return $this->completed_at->diffInSeconds($this->created_at);
    }

    /**
     * Scope: Filter by resource type
     */
    public function scopeByResourceType($query, $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope: Filter by operation type
     */
    public function scopeByOperationType($query, $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Get only failed/partial operations
     */
    public function scopeWithFailures($query)
    {
        return $query->where('failed_items', '>', 0);
    }

    /**
     * Scope: Get only successful operations
     */
    public function scopeFullySuccessful($query)
    {
        return $query->where('failed_items', 0)
            ->where('status', 'completed');
    }

    /**
     * Scope: Order by recent first
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }
}
