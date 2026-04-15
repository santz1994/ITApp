<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'import_logs';

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'import_id',
        'event',
        'message',
        'row_number',
        'data',
        'error_message',
        'resolution',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'row_number' => 'integer',
        'data' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * Event constants
     */
    const EVENT_FILE_UPLOADED = 'file_uploaded';
    const EVENT_VALIDATION_STARTED = 'validation_started';
    const EVENT_VALIDATION_COMPLETE = 'validation_complete';
    const EVENT_PROCESSING_STARTED = 'processing_started';
    const EVENT_ROW_IMPORTED = 'row_imported';
    const EVENT_ROW_FAILED = 'row_failed';
    const EVENT_ROW_CONFLICT = 'row_conflict';
    const EVENT_PROCESSING_COMPLETE = 'processing_complete';
    const EVENT_IMPORT_FAILED = 'import_failed';
    const EVENT_ROLLED_BACK = 'rolled_back';

    /**
     * Get the import this log belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function import()
    {
        return $this->belongsTo(\App\Import::class, 'import_id', 'import_id');
    }

    /**
     * Check if event represents success.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return in_array($this->event, [
            self::EVENT_ROW_IMPORTED,
            self::EVENT_VALIDATION_COMPLETE,
            self::EVENT_PROCESSING_COMPLETE,
        ]);
    }

    /**
     * Check if event represents failure.
     *
     * @return bool
     */
    public function isFailed()
    {
        return in_array($this->event, [
            self::EVENT_ROW_FAILED,
            self::EVENT_IMPORT_FAILED,
        ]);
    }

    /**
     * Get human-readable event label.
     *
     * @return string
     */
    public function getEventLabel()
    {
        $labels = [
            self::EVENT_FILE_UPLOADED => 'File Uploaded',
            self::EVENT_VALIDATION_STARTED => 'Validation Started',
            self::EVENT_VALIDATION_COMPLETE => 'Validation Complete',
            self::EVENT_PROCESSING_STARTED => 'Processing Started',
            self::EVENT_ROW_IMPORTED => 'Row Imported',
            self::EVENT_ROW_FAILED => 'Row Failed',
            self::EVENT_ROW_CONFLICT => 'Row Conflict',
            self::EVENT_PROCESSING_COMPLETE => 'Processing Complete',
            self::EVENT_IMPORT_FAILED => 'Import Failed',
            self::EVENT_ROLLED_BACK => 'Rolled Back',
        ];

        return $labels[$this->event] ?? $this->event;
    }

    /**
     * Scope to get logs for a specific import.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $importId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForImport($query, $importId)
    {
        return $query->where('import_id', $importId);
    }

    /**
     * Scope to get successful events only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('event', [
            self::EVENT_ROW_IMPORTED,
            self::EVENT_VALIDATION_COMPLETE,
            self::EVENT_PROCESSING_COMPLETE,
        ]);
    }

    /**
     * Scope to get failed events only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('event', [
            self::EVENT_ROW_FAILED,
            self::EVENT_IMPORT_FAILED,
        ]);
    }

    /**
     * Scope to filter by event type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $event
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Create file uploaded event.
     *
     * @param string $importId
     * @param string $fileName
     * @param int $fileSize
     * @return \App\ImportLog
     */
    public static function createFileUploaded($importId, $fileName, $fileSize)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_FILE_UPLOADED,
            'message' => "File uploaded: {$fileName} ({$fileSize} bytes)",
            'created_at' => now(),
        ]);
    }

    /**
     * Create validation started event.
     *
     * @param string $importId
     * @return \App\ImportLog
     */
    public static function createValidationStarted($importId)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_VALIDATION_STARTED,
            'message' => 'Validation started',
            'created_at' => now(),
        ]);
    }

    /**
     * Create validation complete event.
     *
     * @param string $importId
     * @param int $validatedRows
     * @return \App\ImportLog
     */
    public static function createValidationComplete($importId, $validatedRows = 0)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_VALIDATION_COMPLETE,
            'message' => "Validation complete. {$validatedRows} rows ready to import.",
            'row_number' => $validatedRows,
            'created_at' => now(),
        ]);
    }

    /**
     * Create processing started event.
     *
     * @param string $importId
     * @return \App\ImportLog
     */
    public static function createProcessingStarted($importId)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_PROCESSING_STARTED,
            'message' => 'Processing started',
            'created_at' => now(),
        ]);
    }

    /**
     * Create row imported event.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param array $data
     * @return \App\ImportLog
     */
    public static function createRowImported($importId, $rowNumber, $data = [])
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_ROW_IMPORTED,
            'message' => "Row {$rowNumber} imported successfully",
            'row_number' => $rowNumber,
            'data' => $data,
            'created_at' => now(),
        ]);
    }

    /**
     * Create row failed event.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param string $errorMessage
     * @param array $data
     * @return \App\ImportLog
     */
    public static function createRowFailed($importId, $rowNumber, $errorMessage, $data = [])
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_ROW_FAILED,
            'message' => "Row {$rowNumber} failed validation",
            'row_number' => $rowNumber,
            'error_message' => $errorMessage,
            'data' => $data,
            'created_at' => now(),
        ]);
    }

    /**
     * Create row conflict event.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param string $conflictType
     * @param array $data
     * @return \App\ImportLog
     */
    public static function createRowConflict($importId, $rowNumber, $conflictType, $data = [])
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_ROW_CONFLICT,
            'message' => "Row {$rowNumber} has conflict: {$conflictType}",
            'row_number' => $rowNumber,
            'error_message' => $conflictType,
            'data' => $data,
            'created_at' => now(),
        ]);
    }

    /**
     * Create processing complete event.
     *
     * @param string $importId
     * @param int $importedRows
     * @return \App\ImportLog
     */
    public static function createProcessingComplete($importId, $importedRows)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_PROCESSING_COMPLETE,
            'message' => "Processing complete. {$importedRows} rows imported successfully.",
            'row_number' => $importedRows,
            'created_at' => now(),
        ]);
    }

    /**
     * Create processing failed event.
     *
     * @param string $importId
     * @param string $errorMessage
     * @return \App\ImportLog
     */
    public static function createProcessingFailed($importId, $errorMessage)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_IMPORT_FAILED,
            'message' => 'Processing failed',
            'error_message' => $errorMessage,
            'created_at' => now(),
        ]);
    }

    /**
     * Create rolled back event.
     *
     * @param string $importId
     * @return \App\ImportLog
     */
    public static function createRolledBack($importId)
    {
        return static::create([
            'import_id' => $importId,
            'event' => self::EVENT_ROLLED_BACK,
            'message' => 'Import rolled back',
            'created_at' => now(),
        ]);
    }
}
