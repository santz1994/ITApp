<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Import extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imports';

    /**
     * The primary key associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'import_id',
        'resource_type',
        'import_format',
        'import_status',
        'file_name',
        'file_path',
        'file_size',
        'total_rows',
        'validated_rows',
        'imported_rows',
        'failed_rows',
        'conflicted_rows',
        'created_by',
        'completed_at',
        'expires_at',
        'error_summary',
        'conflict_summary',
        'column_mapping',
        'import_strategy',
        'auto_resolve_conflicts',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'validated_rows' => 'integer',
        'imported_rows' => 'integer',
        'failed_rows' => 'integer',
        'conflicted_rows' => 'integer',
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'error_summary' => 'json',
        'conflict_summary' => 'json',
        'column_mapping' => 'json',
        'auto_resolve_conflicts' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_VALIDATING = 'validating';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_ROLLED_BACK = 'rolled_back';

    /**
     * Format constants
     */
    const FORMAT_CSV = 'csv';
    const FORMAT_EXCEL = 'excel';
    const FORMAT_JSON = 'json';

    /**
     * Resource type constants
     */
    const RESOURCE_ASSETS = 'assets';
    const RESOURCE_TICKETS = 'tickets';

    /**
     * Import strategy constants
     */
    const STRATEGY_CREATE = 'create';
    const STRATEGY_UPDATE = 'update';
    const STRATEGY_CREATE_IF_NOT_EXISTS = 'create_if_not_exists';
    const STRATEGY_MANUAL_REVIEW = 'manual_review';

    /**
     * Get the user who initiated the import.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Get the import logs for this import.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(\App\ImportLog::class, 'import_id', 'import_id');
    }

    /**
     * Get the import conflicts for this import.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conflicts()
    {
        return $this->hasMany(\App\ImportConflict::class, 'import_id', 'import_id');
    }

    /**
     * Get the resolution choices for this import.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resolutionChoices()
    {
        return $this->hasMany(\App\ResolutionChoice::class, 'import_id', 'import_id');
    }

    /**
     * Get successful imports only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('import_status', self::STATUS_COMPLETED)
                     ->where('failed_rows', 0);
    }

    /**
     * Get failed imports only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('import_status', self::STATUS_FAILED);
    }

    /**
     * Get expired imports (older than 30 days).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Get imports by resource type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByResourceType($query, $type)
    {
        return $query->where('resource_type', $type);
    }

    /**
     * Get imports by format.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $format
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('import_format', $format);
    }

    /**
     * Get imports by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('import_status', $status);
    }

    /**
     * Get imports by creator.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get recent imports.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Find an import by its UUID.
     *
     * @param string $importId
     * @return \App\Import|null
     */
    public static function findByImportId($importId)
    {
        return static::where('import_id', $importId)->first();
    }

    /**
     * Calculate success rate.
     *
     * @return float
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->imported_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Check if import is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->import_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if import failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->import_status === self::STATUS_FAILED;
    }

    /**
     * Check if import has expired.
     *
     * @return bool
     */
    public function hasExpired()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Get import duration in seconds.
     *
     * @return int
     */
    public function getDurationSeconds()
    {
        if (!$this->completed_at) {
            return 0;
        }

        return $this->completed_at->diffInSeconds($this->created_at);
    }

    /**
     * Mark import as validating.
     *
     * @return void
     */
    public function markAsValidating()
    {
        $this->update(['import_status' => self::STATUS_VALIDATING]);
        ImportLog::createValidationStarted($this->import_id);
    }

    /**
     * Mark import as validated.
     *
     * @return void
     */
    public function markAsValidated()
    {
        $this->update(['import_status' => self::STATUS_VALIDATED]);
        ImportLog::createValidationComplete($this->import_id);
    }

    /**
     * Mark import as processing.
     *
     * @return void
     */
    public function markAsProcessing()
    {
        $this->update(['import_status' => self::STATUS_PROCESSING]);
        ImportLog::createProcessingStarted($this->import_id);
    }

    /**
     * Mark import as completed.
     *
     * @param int $importedRows
     * @param int $failedRows
     * @return void
     */
    public function markAsCompleted($importedRows, $failedRows = 0)
    {
        $this->update([
            'import_status' => self::STATUS_COMPLETED,
            'imported_rows' => $importedRows,
            'failed_rows' => $failedRows,
            'completed_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
        ImportLog::createProcessingComplete($this->import_id, $importedRows);
    }

    /**
     * Mark import as failed.
     *
     * @param string $errorMessage
     * @return void
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'import_status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_summary' => ['message' => $errorMessage],
        ]);
        ImportLog::createProcessingFailed($this->import_id, $errorMessage);
    }

    /**
     * Mark import as rolled back.
     *
     * @return void
     */
    public function markAsRolledBack()
    {
        $this->update(['import_status' => self::STATUS_ROLLED_BACK]);
        ImportLog::createRolledBack($this->import_id);
    }
}
