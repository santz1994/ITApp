<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportConflict extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'import_conflicts';

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
        'row_number',
        'conflict_type',
        'existing_record_id',
        'new_record_data',
        'suggested_resolution',
        'user_resolution',
        'resolution_choice_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'row_number' => 'integer',
        'new_record_data' => 'json',
        'existing_record_id' => 'integer',
        'resolution_choice_id' => 'integer',
    ];

    /**
     * Conflict type constants
     */
    const CONFLICT_DUPLICATE_KEY = 'duplicate_key';
    const CONFLICT_DUPLICATE_RECORD = 'duplicate_record';
    const CONFLICT_FOREIGN_KEY_NOT_FOUND = 'foreign_key_not_found';
    const CONFLICT_INVALID_DATA = 'invalid_data';
    const CONFLICT_BUSINESS_RULE = 'business_rule_violation';

    /**
     * Resolution constants
     */
    const RESOLUTION_SKIP = 'skip';
    const RESOLUTION_CREATE_NEW = 'create_new';
    const RESOLUTION_UPDATE_EXISTING = 'update_existing';
    const RESOLUTION_MERGE = 'merge';

    /**
     * Get the import this conflict belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function import()
    {
        return $this->belongsTo(\App\Import::class, 'import_id', 'import_id');
    }

    /**
     * Scope to get conflicts for a specific import.
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
     * Scope to get unresolved conflicts only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('user_resolution');
    }

    /**
     * Scope to get resolved conflicts only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResolved($query)
    {
        return $query->whereNotNull('user_resolution');
    }

    /**
     * Scope to filter by conflict type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByConflictType($query, $type)
    {
        return $query->where('conflict_type', $type);
    }

    /**
     * Get human-readable conflict type label.
     *
     * @return string
     */
    public function getConflictTypeLabel()
    {
        $labels = [
            self::CONFLICT_DUPLICATE_KEY => 'Duplicate Key',
            self::CONFLICT_DUPLICATE_RECORD => 'Duplicate Record',
            self::CONFLICT_FOREIGN_KEY_NOT_FOUND => 'Foreign Key Not Found',
            self::CONFLICT_INVALID_DATA => 'Invalid Data',
            self::CONFLICT_BUSINESS_RULE => 'Business Rule Violation',
        ];

        return $labels[$this->conflict_type] ?? $this->conflict_type;
    }

    /**
     * Get human-readable resolution label.
     *
     * @return string
     */
    public function getResolutionLabel()
    {
        $labels = [
            self::RESOLUTION_SKIP => 'Skip This Row',
            self::RESOLUTION_CREATE_NEW => 'Create New Record',
            self::RESOLUTION_UPDATE_EXISTING => 'Update Existing Record',
            self::RESOLUTION_MERGE => 'Merge Records',
        ];

        return $labels[$this->user_resolution] ?? $this->user_resolution;
    }

    /**
     * Mark conflict as resolved with chosen resolution.
     *
     * @param string $resolution
     * @param int|null $choiceId
     * @return void
     */
    public function resolveWith($resolution, $choiceId = null)
    {
        $this->update([
            'user_resolution' => $resolution,
            'resolution_choice_id' => $choiceId,
        ]);
    }

    /**
     * Create duplicate key conflict.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param int $existingRecordId
     * @param array $newRecordData
     * @return \App\ImportConflict
     */
    public static function createDuplicateKey($importId, $rowNumber, $existingRecordId, $newRecordData)
    {
        return static::create([
            'import_id' => $importId,
            'row_number' => $rowNumber,
            'conflict_type' => self::CONFLICT_DUPLICATE_KEY,
            'existing_record_id' => $existingRecordId,
            'new_record_data' => $newRecordData,
            'suggested_resolution' => self::RESOLUTION_UPDATE_EXISTING,
        ]);
    }

    /**
     * Create duplicate record conflict.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param int $existingRecordId
     * @param array $newRecordData
     * @return \App\ImportConflict
     */
    public static function createDuplicateRecord($importId, $rowNumber, $existingRecordId, $newRecordData)
    {
        return static::create([
            'import_id' => $importId,
            'row_number' => $rowNumber,
            'conflict_type' => self::CONFLICT_DUPLICATE_RECORD,
            'existing_record_id' => $existingRecordId,
            'new_record_data' => $newRecordData,
            'suggested_resolution' => self::RESOLUTION_SKIP,
        ]);
    }

    /**
     * Create foreign key not found conflict.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param array $newRecordData
     * @return \App\ImportConflict
     */
    public static function createForeignKeyNotFound($importId, $rowNumber, $newRecordData)
    {
        return static::create([
            'import_id' => $importId,
            'row_number' => $rowNumber,
            'conflict_type' => self::CONFLICT_FOREIGN_KEY_NOT_FOUND,
            'new_record_data' => $newRecordData,
            'suggested_resolution' => self::RESOLUTION_SKIP,
        ]);
    }

    /**
     * Create invalid data conflict.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param array $newRecordData
     * @return \App\ImportConflict
     */
    public static function createInvalidData($importId, $rowNumber, $newRecordData)
    {
        return static::create([
            'import_id' => $importId,
            'row_number' => $rowNumber,
            'conflict_type' => self::CONFLICT_INVALID_DATA,
            'new_record_data' => $newRecordData,
            'suggested_resolution' => self::RESOLUTION_SKIP,
        ]);
    }

    /**
     * Create business rule violation conflict.
     *
     * @param string $importId
     * @param int $rowNumber
     * @param array $newRecordData
     * @return \App\ImportConflict
     */
    public static function createBusinessRuleViolation($importId, $rowNumber, $newRecordData)
    {
        return static::create([
            'import_id' => $importId,
            'row_number' => $rowNumber,
            'conflict_type' => self::CONFLICT_BUSINESS_RULE,
            'new_record_data' => $newRecordData,
            'suggested_resolution' => self::RESOLUTION_SKIP,
        ]);
    }
}
