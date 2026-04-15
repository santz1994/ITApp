<?php

namespace App\Traits;

use App\AuditLog;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     *
     * @return void
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            if (auth()->check()) {
                try {
                    AuditLog::logAction(
                        'create',
                        get_class($model),
                        $model->id,
                        null,
                        $model->getAuditableAttributes(),
                        static::generateAuditDescription('created', $model),
                        'model'
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to log model creation: ' . $e->getMessage());
                }
            }
        });

        static::updated(function ($model) {
            if (auth()->check()) {
                try {
                    $oldValues = $model->getOriginal();
                    $newValues = $model->getAttributes();
                    $changes = $model->getDirty();

                    if (!empty($changes)) {
                        // Filter to only auditable attributes
                        $auditableAttributes = $model->getAuditableAttributes();
                        $filteredOldValues = array_intersect_key($oldValues, $auditableAttributes);
                        $filteredNewValues = array_intersect_key($newValues, $auditableAttributes);

                        AuditLog::logAction(
                            'update',
                            get_class($model),
                            $model->id,
                            $filteredOldValues,
                            $filteredNewValues,
                            static::generateAuditDescription('updated', $model),
                            'model'
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to log model update: ' . $e->getMessage());
                }
            }
        });

        static::deleted(function ($model) {
            if (auth()->check()) {
                try {
                    AuditLog::logAction(
                        'delete',
                        get_class($model),
                        $model->id,
                        $model->getAuditableAttributes(),
                        null,
                        static::generateAuditDescription('deleted', $model),
                        'model'
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to log model deletion: ' . $e->getMessage());
                }
            }
        });

        // If using soft deletes
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                if (auth()->check()) {
                    try {
                        AuditLog::logAction(
                            'restore',
                            get_class($model),
                            $model->id,
                            null,
                            $model->getAuditableAttributes(),
                            static::generateAuditDescription('restored', $model),
                            'model'
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to log model restoration: ' . $e->getMessage());
                    }
                }
            });
        }
    }

    /**
     * Get the attributes to be audited.
     *
     * @return array
     */
    public function getAuditableAttributes()
    {
        // If the model has $auditableAttributes defined, use those
        if (property_exists($this, 'auditableAttributes')) {
            return array_intersect_key(
                $this->getAttributes(),
                array_flip($this->auditableAttributes)
            );
        }

        // If the model has $excludeFromAudit defined, exclude those
        if (property_exists($this, 'excludeFromAudit')) {
            return array_diff_key(
                $this->getAttributes(),
                array_flip($this->excludeFromAudit)
            );
        }

        // Default: exclude timestamps and common non-auditable fields
        $defaultExclusions = [
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
            'password',
            'api_token',
        ];

        return array_diff_key(
            $this->getAttributes(),
            array_flip($defaultExclusions)
        );
    }

    /**
     * Generate audit description for the model.
     *
     * @param string $action
     * @param mixed $model
     * @return string
     */
    protected static function generateAuditDescription($action, $model)
    {
        $modelName = class_basename(get_class($model));
        $identifier = static::getModelIdentifier($model);

        $actionMap = [
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
        ];

        $actionText = $actionMap[$action] ?? $action;

        return "{$modelName} {$identifier} was {$actionText}";
    }

    /**
     * Get a model identifier (name, title, id, etc.).
     *
     * @param mixed $model
     * @return string
     */
    protected static function getModelIdentifier($model)
    {
        // Try common identifier fields
        $identifierFields = ['name', 'title', 'subject', 'asset_name', 'email', 'username'];

        foreach ($identifierFields as $field) {
            if (isset($model->$field)) {
                return "'{$model->$field}'";
            }
        }

        return "#{$model->id}";
    }

    /**
     * Check if auditing is enabled for this model instance.
     *
     * @return bool
     */
    public function isAuditingEnabled()
    {
        // Check if the model has disabled auditing
        if (property_exists($this, 'auditingEnabled')) {
            return $this->auditingEnabled;
        }

        return true;
    }

    /**
     * Temporarily disable auditing for this model instance.
     *
     * @return $this
     */
    public function disableAuditing()
    {
        $this->auditingEnabled = false;
        return $this;
    }

    /**
     * Enable auditing for this model instance.
     *
     * @return $this
     */
    public function enableAuditing()
    {
        $this->auditingEnabled = true;
        return $this;
    }
}
