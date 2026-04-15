<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * BulkOperationLog Model
 *
 * Records detailed logs for individual items in a bulk operation
 * Provides before/after values for audit trail and compliance
 *
 * @property string $bulk_operation_id Foreign key to BulkOperation
 * @property string $resource_type Type of resource (assets, tickets)
 * @property integer $resource_id ID of the resource being updated
 * @property string $operation_type Type of operation (status_update, assignment, field_update)
 * @property array $old_values JSON snapshot of values before update
 * @property array $new_values JSON snapshot of values after update
 * @property string $status Success/failure status (success, failed)
 * @property string|null $error_message Error message if failed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BulkOperationLog extends Model
{
    protected $table = 'bulk_operation_logs';

    protected $fillable = [
        'bulk_operation_id',
        'resource_type',
        'resource_id',
        'operation_type',
        'old_values',
        'new_values',
        'status',
        'error_message'
    ];

    protected $casts = [
        'resource_id' => 'integer',
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship: Parent bulk operation
     */
    public function bulkOperation()
    {
        return $this->belongsTo(\App\BulkOperation::class, 'bulk_operation_id', 'operation_id');
    }

    /**
     * Check if this log entry represents a success
     */
    public function isSuccess()
    {
        return $this->status === 'success';
    }

    /**
     * Check if this log entry represents a failure
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Get the resource object (asset or ticket)
     * Note: This is a helper method - actual resource retrieval depends on resource_type
     */
    public function getResource()
    {
        switch ($this->resource_type) {
            case 'assets':
                return \App\Asset::find($this->resource_id);
            case 'tickets':
                return \App\Ticket::find($this->resource_id);
            default:
                return null;
        }
    }

    /**
     * Get fields that changed
     */
    public function getChangedFields()
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changed = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changed;
    }

    /**
     * Scope: Get logs for a specific resource
     */
    public function scopeForResource($query, $resourceType, $resourceId)
    {
        return $query->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId);
    }

    /**
     * Scope: Get only successful logs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Get only failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Filter by operation type
     */
    public function scopeByOperationType($query, $operationType)
    {
        return $query->where('operation_type', $operationType);
    }
}
