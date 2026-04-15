<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audit_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
        'event_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the affected model (polymorphic).
     */
    public function model()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Get human-readable action name.
     *
     * @return string
     */
    public function getActionNameAttribute()
    {
        $actionMap = [
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'failed_login' => 'Failed Login Attempt',
            'restore' => 'Restored',
            'force_delete' => 'Permanently Deleted',
        ];

        return $actionMap[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get the model name in human-readable format.
     *
     * @return string
     */
    public function getModelNameAttribute()
    {
        if (!$this->model_type) {
            return 'N/A';
        }

        $className = class_basename($this->model_type);
        return ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $className));
    }

    /**
     * Get changes as a formatted array.
     *
     * @return array
     */
    public function getChangesAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        $oldValues = is_array($this->old_values) ? $this->old_values : json_decode($this->old_values, true);
        $newValues = is_array($this->new_values) ? $this->new_values : json_decode($this->new_values, true);

        if (!$oldValues || !$newValues) {
            return [];
        }

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by model type.
     */
    public function scopeByModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Static method to log an action.
     *
     * @param string $action
     * @param string|null $modelType
     * @param int|null $modelId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @param string $eventType
     * @return AuditLog
     */
    public static function logAction(
        $action,
        $modelType = null,
        $modelId = null,
        $oldValues = null,
        $newValues = null,
        $description = null,
        $eventType = 'model'
    ) {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => $modelType ? class_basename($modelType) : ($modelType ?? null),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
            'event_type' => $eventType,
        ]);
    }
}
