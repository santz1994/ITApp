<?php

namespace App\Services;

use App\AssetRequest;
use App\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PurchaseRequestApprovalWorkflowService
{
    /**
     * Cache column existence checks to avoid repetitive schema calls.
     *
     * @var array<string, bool>
     */
    private array $columnExistsCache = [];

    /**
     * Approve a purchase request.
     */
    public function approve(AssetRequest $assetRequest, int $approverId, ?string $notes = null): AssetRequest
    {
        return DB::transaction(function () use ($assetRequest, $approverId, $notes) {
            $this->guardTransition($assetRequest, ['fulfilled']);

            $oldValues = $this->auditValues($assetRequest);

            $updateData = [
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ];

            if ($notes !== null && $this->hasColumn('asset_requests', 'approval_notes')) {
                $updateData['approval_notes'] = $notes;
            }

            $assetRequest->fill($updateData);
            $assetRequest->save();

            $this->writeAuditLog(
                'purchase-request-approve',
                $assetRequest,
                $oldValues,
                $this->auditValues($assetRequest),
                'Purchase request approved via workflow service'
            );

            return $assetRequest->fresh();
        });
    }

    /**
     * Reject a purchase request.
     */
    public function reject(AssetRequest $assetRequest, int $approverId, ?string $notes = null): AssetRequest
    {
        return DB::transaction(function () use ($assetRequest, $approverId, $notes) {
            $this->guardTransition($assetRequest, ['fulfilled']);

            $oldValues = $this->auditValues($assetRequest);

            $updateData = [
                'status' => 'rejected',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ];

            if ($notes !== null && $this->hasColumn('asset_requests', 'approval_notes')) {
                $updateData['approval_notes'] = $notes;
            }

            $assetRequest->fill($updateData);
            $assetRequest->save();

            $this->writeAuditLog(
                'purchase-request-reject',
                $assetRequest,
                $oldValues,
                $this->auditValues($assetRequest),
                'Purchase request rejected via workflow service'
            );

            return $assetRequest->fresh();
        });
    }

    /**
     * Mark a purchase request as fulfilled.
     */
    public function fulfill(
        AssetRequest $assetRequest,
        int $fulfillerId,
        ?string $notes = null,
        ?int $fulfilledAssetId = null
    ): AssetRequest {
        return DB::transaction(function () use ($assetRequest, $fulfillerId, $notes, $fulfilledAssetId) {
            $this->guardTransition($assetRequest, ['rejected', 'fulfilled']);

            $oldValues = $this->auditValues($assetRequest);

            $updateData = [
                'status' => 'fulfilled',
                'fulfilled_at' => now(),
            ];

            if ($fulfilledAssetId !== null && $this->hasColumn('asset_requests', 'fulfilled_asset_id')) {
                $updateData['fulfilled_asset_id'] = $fulfilledAssetId;
            }

            // Some legacy datasets store fulfillment comments in approval_notes.
            if ($notes !== null && $this->hasColumn('asset_requests', 'approval_notes')) {
                $existingNotes = (string) ($assetRequest->approval_notes ?? '');
                $prefix = $existingNotes !== '' ? $existingNotes . "\n" : '';
                $updateData['approval_notes'] = trim($prefix . 'Fulfillment by user #' . $fulfillerId . ': ' . $notes);
            }

            $assetRequest->fill($updateData);
            $assetRequest->save();

            $this->writeAuditLog(
                'purchase-request-fulfill',
                $assetRequest,
                $oldValues,
                $this->auditValues($assetRequest),
                'Purchase request fulfilled via workflow service'
            );

            return $assetRequest->fresh();
        });
    }

    /**
     * Prevent invalid status transitions.
     *
     * @param array<int, string> $blockedStatuses
     */
    private function guardTransition(AssetRequest $assetRequest, array $blockedStatuses): void
    {
        $currentStatus = strtolower((string) ($assetRequest->status ?? 'pending'));

        if (in_array($currentStatus, $blockedStatuses, true)) {
            throw new \RuntimeException('Status transition is not allowed from current state: ' . $currentStatus);
        }
    }

    /**
     * Determine if a table column exists and memoize the result.
     */
    private function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        try {
            $this->columnExistsCache[$cacheKey] = Schema::hasColumn($table, $column);
        } catch (\Throwable $exception) {
            Log::warning('Column existence check failed in PurchaseRequestApprovalWorkflowService', [
                'table' => $table,
                'column' => $column,
                'error' => $exception->getMessage(),
            ]);
            $this->columnExistsCache[$cacheKey] = false;
        }

        return $this->columnExistsCache[$cacheKey];
    }

    /**
     * Normalize audit payload for consistent log snapshots.
     */
    private function auditValues(AssetRequest $assetRequest): array
    {
        return [
            'id' => $assetRequest->id,
            'status' => $assetRequest->status,
            'requested_by' => $assetRequest->requested_by,
            'approved_by' => $assetRequest->approved_by,
            'approved_at' => $assetRequest->approved_at,
            'fulfilled_asset_id' => $assetRequest->fulfilled_asset_id,
            'fulfilled_at' => $assetRequest->fulfilled_at,
            'approval_notes' => $assetRequest->approval_notes,
        ];
    }

    /**
     * Persist audit metadata, but never block the main transaction if logging fails.
     */
    private function writeAuditLog(
        string $action,
        AssetRequest $assetRequest,
        array $oldValues,
        array $newValues,
        string $description
    ): void {
        try {
            AuditLog::logAction(
                $action,
                AssetRequest::class,
                $assetRequest->id,
                $oldValues,
                $newValues,
                $description,
                'model'
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to write purchase request audit log', [
                'asset_request_id' => $assetRequest->id,
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
