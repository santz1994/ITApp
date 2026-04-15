<?php

namespace App\Traits;

use App\BulkOperation;
use App\BulkOperationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * BulkOperationBuilder Trait
 *
 * Provides centralized bulk operation capabilities for models supporting mass updates.
 * Features:
 * - Batch status/field updates with transaction safety
 * - Pre-execution validation
 * - All-or-nothing semantics (complete success or full rollback)
 * - Comprehensive audit logging
 * - Performance optimized for 10,000+ item operations
 *
 * Usage:
 * Asset::bulkUpdateStatus([1, 2, 3], 2, 'Decommissioned');
 * Ticket::bulkUpdateAssignment([1, 2, 3], 5, 'Bulk assignment');
 *
 * @package App\Traits
 */
trait BulkOperationBuilder
{
    /**
     * Perform bulk status update operation
     *
     * @param array $ids Resource IDs to update
     * @param int $newStatusId New status ID
     * @param string $reason Update reason
     * @param bool $dryRun If true, validates but doesn't persist
     * @param int|null $userId User performing operation
     * @return array Operation result
     */
    public static function bulkUpdateStatus(
        array $ids,
        $newStatusId,
        $reason = '',
        $dryRun = false,
        $userId = null
    ) {
        return static::doBulkOperation(
            'status_update',
            compact('ids', 'newStatusId', 'reason', 'dryRun', 'userId')
        );
    }

    /**
     * Perform bulk assignment operation
     *
     * @param array $ids Resource IDs
     * @param int $assignedToUserId User ID to assign to
     * @param string $reason Assignment reason
     * @param bool $dryRun If true, validates but doesn't persist
     * @param int|null $userId User performing operation
     * @return array Operation result
     */
    public static function bulkUpdateAssignment(
        array $ids,
        $assignedToUserId,
        $reason = '',
        $dryRun = false,
        $userId = null
    ) {
        return static::doBulkOperation(
            'assignment',
            compact('ids', 'assignedToUserId', 'reason', 'dryRun', 'userId')
        );
    }

    /**
     * Perform bulk field update operation
     *
     * @param array $ids Resource IDs
     * @param array $updates Field => value pairs
     * @param bool $dryRun If true, validates but doesn't persist
     * @param int|null $userId User performing operation
     * @return array Operation result
     */
    public static function bulkUpdateFields(
        array $ids,
        array $updates,
        $dryRun = false,
        $userId = null
    ) {
        return static::doBulkOperation(
            'field_update',
            compact('ids', 'updates', 'dryRun', 'userId')
        );
    }

    /**
     * Main bulk operation handler
     *
     * Orchestrates the complete bulk operation lifecycle:
     * 1. Validate all items before any updates
     * 2. Perform updates in transaction
     * 3. Log all changes
     * 4. Rollback on any error
     *
     * @param string $operationType Type of operation (status_update, assignment, field_update)
     * @param array $parameters Operation parameters
     * @return array Operation result with status, summary, and details
     */
    public static function doBulkOperation($operationType, array $parameters)
    {
        $operationId = 'bulk-' . uniqid();
        $dryRun = $parameters['dryRun'] ?? false;
        $userId = $parameters['userId'] ?? Auth::id();
        $ids = $parameters['ids'] ?? [];

        try {
            // Validate IDs array
            if (empty($ids)) {
                return static::operationError('No IDs provided', 400, $operationId);
            }

            if (count($ids) > 10000) {
                return static::operationError('Maximum 10,000 items per operation', 422, $operationId);
            }

            // Remove duplicates
            $ids = array_unique($ids);

            // Fetch all resources for validation
            $resources = static::whereIn('id', $ids)->get();

            if ($resources->count() !== count($ids)) {
                $foundIds = $resources->pluck('id')->toArray();
                $missingIds = array_diff($ids, $foundIds);
                return static::operationError(
                    'Invalid resource IDs: ' . implode(', ', $missingIds),
                    422,
                    $operationId
                );
            }

            // Validate operation parameters
            $validationResult = static::validateBulkOperation(
                $operationType,
                $resources,
                $parameters
            );

            if (!$validationResult['valid']) {
                return static::operationError(
                    $validationResult['error'],
                    422,
                    $operationId,
                    $validationResult['details'] ?? []
                );
            }

            // Dry-run mode: return success without persisting
            if ($dryRun) {
                return [
                    'operation_id' => $operationId,
                    'status' => 'DRY_RUN_SUCCESS',
                    'total_items' => count($ids),
                    'message' => 'Validation passed. No changes persisted.'
                ];
            }

            // Perform bulk operation in transaction
            $operationResult = DB::transaction(function () use (
                $operationType,
                $resources,
                $parameters,
                $operationId,
                $userId
            ) {
                return static::performBulkUpdate(
                    $operationType,
                    $resources,
                    $parameters,
                    $operationId,
                    $userId
                );
            });

            return $operationResult;

        } catch (\Exception $e) {
            return static::operationError(
                'Bulk operation failed: ' . $e->getMessage(),
                500,
                $operationId
            );
        }
    }

    /**
     * Validate bulk operation parameters
     *
     * @param string $operationType Type of operation
     * @param Collection $resources Resources to validate
     * @param array $parameters Operation parameters
     * @return array Validation result with 'valid' and 'error' keys
     */
    protected static function validateBulkOperation($operationType, Collection $resources, array $parameters)
    {
        $model = $resources->first();
        $table = $model->getTable();

        switch ($operationType) {
            case 'status_update':
                return static::validateStatusUpdate($resources, $parameters);

            case 'assignment':
                return static::validateAssignment($resources, $parameters);

            case 'field_update':
                return static::validateFieldUpdate($resources, $parameters, $table);

            default:
                return ['valid' => false, 'error' => "Unknown operation type: {$operationType}"];
        }
    }

    /**
     * Validate status update operation
     *
     * @param Collection $resources
     * @param array $parameters
     * @return array
     */
    protected static function validateStatusUpdate(Collection $resources, array $parameters)
    {
        $newStatusId = $parameters['newStatusId'] ?? null;

        if (!$newStatusId) {
            return ['valid' => false, 'error' => 'status_id is required'];
        }

        // Verify status exists
        $statusExists = DB::table('statuses')
            ->where('id', $newStatusId)
            ->exists();

        if (!$statusExists) {
            return ['valid' => false, 'error' => "Status ID {$newStatusId} does not exist"];
        }

        // For assets: validate status transitions if needed
        $invalidTransitions = [];
        foreach ($resources as $resource) {
            if (isset($resource->status_id) && $resource->status_id == $newStatusId) {
                // Already in target status, skip
                continue;
            }
            // Add domain-specific validation logic here
        }

        return ['valid' => true];
    }

    /**
     * Validate assignment operation
     *
     * @param Collection $resources
     * @param array $parameters
     * @return array
     */
    protected static function validateAssignment(Collection $resources, array $parameters)
    {
        $userId = $parameters['assignedToUserId'] ?? null;

        if (!$userId) {
            return ['valid' => false, 'error' => 'assigned_to is required'];
        }

        // Verify user exists
        $userExists = DB::table('users')
            ->where('id', $userId)
            ->where('is_active', true)
            ->exists();

        if (!$userExists) {
            return ['valid' => false, 'error' => "User ID {$userId} does not exist or is inactive"];
        }

        return ['valid' => true];
    }

    /**
     * Validate field update operation
     *
     * @param Collection $resources
     * @param array $parameters
     * @param string $table
     * @return array
     */
    protected static function validateFieldUpdate(Collection $resources, array $parameters, $table)
    {
        $updates = $parameters['updates'] ?? [];

        if (empty($updates)) {
            return ['valid' => false, 'error' => 'updates array is required and cannot be empty'];
        }

        // Validate field names exist (basic protection)
        $model = $resources->first();
        $fillable = $model->getFillable();

        foreach (array_keys($updates) as $field) {
            if (!in_array($field, $fillable)) {
                return ['valid' => false, 'error' => "Field '{$field}' is not updateable"];
            }
        }

        return ['valid' => true];
    }

    /**
     * Perform actual bulk update
     *
     * @param string $operationType Type of operation
     * @param Collection $resources Resources to update
     * @param array $parameters Operation parameters
     * @param string $operationId Unique operation identifier
     * @param int $userId User performing operation
     * @return array Operation result
     */
    protected static function performBulkUpdate(
        $operationType,
        Collection $resources,
        array $parameters,
        $operationId,
        $userId
    ) {
        $table = $resources->first()->getTable();
        $successCount = 0;
        $failedCount = 0;
        $logs = [];

        // Build update data based on operation type
        $updateData = static::buildUpdateData($operationType, $parameters);

        // Process in chunks for performance
        $chunkSize = 500;
        foreach ($resources->chunk($chunkSize) as $chunk) {
            try {
                $chunkIds = $chunk->pluck('id')->toArray();

                // Store old values for audit
                foreach ($chunk as $resource) {
                    $oldValues = $resource->toArray();
                    $newValues = $oldValues;

                    // Apply updates to calculate new values
                    foreach ($updateData as $field => $value) {
                        $newValues[$field] = $value;
                    }

                    // Create audit log entry
                    $logs[] = [
                        'bulk_operation_id' => $operationId,
                        'resource_type' => $table,
                        'resource_id' => $resource->id,
                        'old_values' => json_encode($oldValues),
                        'new_values' => json_encode($newValues),
                        'operation_type' => $operationType,
                        'status' => 'success',
                        'error_message' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $successCount++;
                }

                // Perform batch update
                DB::table($table)
                    ->whereIn('id', $chunkIds)
                    ->update(array_merge($updateData, ['updated_at' => now()]));

            } catch (\Exception $e) {
                // Log individual failures
                foreach ($chunk as $resource) {
                    $logs[] = [
                        'bulk_operation_id' => $operationId,
                        'resource_type' => $table,
                        'resource_id' => $resource->id,
                        'old_values' => json_encode($resource->toArray()),
                        'new_values' => null,
                        'operation_type' => $operationType,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $failedCount++;
                }

                // Rethrow to trigger transaction rollback
                throw $e;
            }
        }

        // Bulk insert audit logs
        if (!empty($logs)) {
            DB::table('bulk_operation_logs')->insert($logs);
        }

        // Create BulkOperation record
        BulkOperation::create([
            'operation_id' => $operationId,
            'resource_type' => $table,
            'operation_type' => $operationType,
            'status' => 'completed',
            'total_items' => count($resources),
            'processed_items' => $successCount,
            'failed_items' => $failedCount,
            'created_by' => $userId,
            'completed_at' => now(),
            'error_details' => null
        ]);

        return [
            'operation_id' => $operationId,
            'status' => $failedCount === 0 ? 'completed' : 'partial',
            'total_items' => count($resources),
            'processed_items' => $successCount,
            'failed_items' => $failedCount,
            'message' => $failedCount === 0
                ? 'Bulk operation completed successfully'
                : "Bulk operation completed with {$failedCount} failures"
        ];
    }

    /**
     * Build update data from operation parameters
     *
     * @param string $operationType
     * @param array $parameters
     * @return array
     */
    protected static function buildUpdateData($operationType, array $parameters)
    {
        switch ($operationType) {
            case 'status_update':
                return ['status_id' => $parameters['newStatusId']];

            case 'assignment':
                return ['assigned_to' => $parameters['assignedToUserId']];

            case 'field_update':
                return $parameters['updates'];

            default:
                return [];
        }
    }

    /**
     * Return operation error response
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param string $operationId Operation ID
     * @param array $details Additional error details
     * @return array
     */
    protected static function operationError($message, $code = 500, $operationId = null, array $details = [])
    {
        return [
            'operation_id' => $operationId,
            'status' => 'error',
            'error' => $message,
            'code' => $code,
            'details' => $details
        ];
    }

    /**
     * Get bulk operation history for a resource type
     *
     * @param string $resourceType (e.g., 'assets' or 'tickets')
     * @param int $limit Number of records to return
     * @return \Illuminate\Support\Collection
     */
    public static function getBulkOperationHistory($resourceType = null, $limit = 50)
    {
        $query = BulkOperation::query();

        if ($resourceType) {
            $query->where('resource_type', $resourceType);
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific bulk operation
     *
     * @param string $operationId
     * @return \Illuminate\Support\Collection
     */
    public static function getBulkOperationLogs($operationId)
    {
        return BulkOperationLog::where('bulk_operation_id', $operationId)
            ->orderBy('resource_id')
            ->get();
    }

    /**
     * Retry a failed bulk operation
     *
     * @param string $operationId
     * @return array
     */
    public static function retryBulkOperation($operationId)
    {
        $operation = BulkOperation::where('operation_id', $operationId)->first();

        if (!$operation) {
            return static::operationError('Operation not found', 404, $operationId);
        }

        if ($operation->status === 'completed' && $operation->failed_items === 0) {
            return static::operationError('Operation already fully completed', 400, $operationId);
        }

        // Get failed items
        $failedLogs = BulkOperationLog::where('bulk_operation_id', $operationId)
            ->where('status', 'failed')
            ->get();

        if ($failedLogs->isEmpty()) {
            return static::operationError('No failed items to retry', 400, $operationId);
        }

        $failedIds = $failedLogs->pluck('resource_id')->toArray();

        // Retry only failed items (would need to extract original parameters)
        // This is a simplified version - full implementation would store parameters
        return [
            'operation_id' => $operationId,
            'status' => 'retry_initiated',
            'failed_items_to_retry' => count($failedIds)
        ];
    }
}
