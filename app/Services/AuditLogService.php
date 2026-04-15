<?php

namespace App\Services;

use App\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log a model action (create, update, delete).
     *
     * @param string $action
     * @param Model $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return AuditLog|null
     */
    public function logModelAction(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ?AuditLog {
        try {
            return AuditLog::logAction(
                $action,
                get_class($model),
                $model->id,
                $oldValues,
                $newValues,
                $description ?? $this->generateDescription($action, $model),
                'model'
            );
        } catch (\Exception $e) {
            Log::error('Failed to create audit log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log authentication action (login, logout, failed login).
     *
     * @param string $action
     * @param int|null $userId
     * @param string|null $description
     * @return AuditLog|null
     */
    public function logAuthAction(
        string $action,
        ?int $userId = null,
        ?string $description = null
    ): ?AuditLog {
        try {
            $auditLog = new AuditLog([
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'model_type' => null,
                'model_id' => null,
                'old_values' => null,
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'description' => $description ?? $this->generateAuthDescription($action),
                'event_type' => 'auth',
            ]);
            
            $auditLog->save();
            return $auditLog;
        } catch (\Exception $e) {
            Log::error('Failed to create auth audit log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log system action (settings change, maintenance, etc.).
     *
     * @param string $action
     * @param string $description
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return AuditLog|null
     */
    public function logSystemAction(
        string $action,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ?AuditLog {
        try {
            return AuditLog::logAction(
                $action,
                null,
                null,
                $oldValues,
                $newValues,
                $description,
                'system'
            );
        } catch (\Exception $e) {
            Log::error('Failed to create system audit log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a ticket action with specific details.
     *
     * @param string $action
     * @param \App\Ticket $ticket
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return AuditLog|null
     */
    public function logTicketAction(
        string $action,
        $ticket,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ?AuditLog {
        if (!$description) {
            $description = $this->generateTicketDescription($action, $ticket, $oldValues, $newValues);
        }

        return $this->logModelAction($action, $ticket, $oldValues, $newValues, $description);
    }

    /**
     * Log an asset action with specific details.
     *
     * @param string $action
     * @param \App\Asset $asset
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return AuditLog|null
     */
    public function logAssetAction(
        string $action,
        $asset,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ?AuditLog {
        if (!$description) {
            $description = $this->generateAssetDescription($action, $asset, $oldValues, $newValues);
        }

        return $this->logModelAction($action, $asset, $oldValues, $newValues, $description);
    }

    /**
     * Generate a description for model action.
     *
     * @param string $action
     * @param Model $model
     * @return string
     */
    protected function generateDescription(string $action, Model $model): string
    {
        $modelName = class_basename(get_class($model));
        $identifier = $this->getModelIdentifier($model);

        $actionMap = [
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            'restore' => 'restored',
            'force_delete' => 'permanently deleted',
        ];

        $actionText = $actionMap[$action] ?? $action;

        return ucfirst($modelName) . " {$identifier} was {$actionText}";
    }

    /**
     * Generate a description for auth action.
     *
     * @param string $action
     * @return string
     */
    protected function generateAuthDescription(string $action): string
    {
        $actionMap = [
            'login' => 'User logged in successfully',
            'logout' => 'User logged out',
            'failed_login' => 'Failed login attempt',
            'password_reset' => 'Password was reset',
            'password_change' => 'Password was changed',
        ];

        return $actionMap[$action] ?? "Auth action: {$action}";
    }

    /**
     * Generate a description for ticket action.
     *
     * @param string $action
     * @param \App\Ticket $ticket
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return string
     */
    protected function generateTicketDescription(
        string $action,
        $ticket,
        ?array $oldValues = null,
        ?array $newValues = null
    ): string {
        $ticketId = $ticket->id ?? 'Unknown';
        $ticketTitle = isset($ticket->subject) ? " ('{$ticket->subject}')" : '';

        if ($action === 'update' && $oldValues && $newValues) {
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                $oldValue = $oldValues[$key] ?? null;
                if ($oldValue != $newValue) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }
            
            if (!empty($changes)) {
                $changedFields = implode(', ', $changes);
                return "Ticket #{$ticketId}{$ticketTitle} was updated: {$changedFields} changed";
            }
        }

        $actionMap = [
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            'assign' => 'assigned',
            'close' => 'closed',
            'reopen' => 'reopened',
        ];

        $actionText = $actionMap[$action] ?? $action;
        return "Ticket #{$ticketId}{$ticketTitle} was {$actionText}";
    }

    /**
     * Generate a description for asset action.
     *
     * @param string $action
     * @param \App\Asset $asset
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return string
     */
    protected function generateAssetDescription(
        string $action,
        $asset,
        ?array $oldValues = null,
        ?array $newValues = null
    ): string {
        $assetId = $asset->id ?? 'Unknown';
        $assetName = isset($asset->asset_name) ? " ('{$asset->asset_name}')" : '';

        if ($action === 'update' && $oldValues && $newValues) {
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                $oldValue = $oldValues[$key] ?? null;
                if ($oldValue != $newValue) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }
            
            if (!empty($changes)) {
                $changedFields = implode(', ', $changes);
                return "Asset #{$assetId}{$assetName} was updated: {$changedFields} changed";
            }
        }

        $actionMap = [
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            'checkout' => 'checked out',
            'checkin' => 'checked in',
        ];

        $actionText = $actionMap[$action] ?? $action;
        return "Asset #{$assetId}{$assetName} was {$actionText}";
    }

    /**
     * Get a model identifier (name, title, id, etc.).
     *
     * @param Model $model
     * @return string
     */
    protected function getModelIdentifier(Model $model): string
    {
        // Try common identifier fields
        $identifierFields = ['name', 'title', 'subject', 'asset_name', 'email', 'id'];

        foreach ($identifierFields as $field) {
            if (isset($model->$field)) {
                return "'{$model->$field}'";
            }
        }

        return "#{$model->id}";
    }

    /**
     * Get audit logs for a specific model.
     *
     * @param Model $model
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getModelAuditLogs(Model $model, int $limit = 50)
    {
        return AuditLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserAuditLogs(int $userId, int $limit = 50)
    {
        return AuditLog::where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old audit logs.
     *
     * @param int $daysToKeep
     * @return int Number of deleted records
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }
}
