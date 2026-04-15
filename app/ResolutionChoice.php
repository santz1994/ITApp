<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResolutionChoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'resolution_choices';

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
        'conflict_id',
        'user_id',
        'choice',
        'choice_details',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'choice_details' => 'json',
    ];

    /**
     * Choice constants
     */
    const CHOICE_SKIP = 'skip';
    const CHOICE_CREATE_NEW = 'create_new';
    const CHOICE_UPDATE_EXISTING = 'update_existing';
    const CHOICE_MERGE = 'merge';

    /**
     * Get the import this resolution belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function import()
    {
        return $this->belongsTo(\App\Import::class, 'import_id', 'import_id');
    }

    /**
     * Get the conflict this resolution is for.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conflict()
    {
        return $this->belongsTo(\App\ImportConflict::class, 'conflict_id');
    }

    /**
     * Get the user who made this choice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * Scope to get choices for a specific import.
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
     * Scope to get choices for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by choice type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $choice
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByChoice($query, $choice)
    {
        return $query->where('choice', $choice);
    }

    /**
     * Get human-readable choice label.
     *
     * @return string
     */
    public function getChoiceLabel()
    {
        $labels = [
            self::CHOICE_SKIP => 'Skip This Row',
            self::CHOICE_CREATE_NEW => 'Create New Record',
            self::CHOICE_UPDATE_EXISTING => 'Update Existing Record',
            self::CHOICE_MERGE => 'Merge Records',
        ];

        return $labels[$this->choice] ?? $this->choice;
    }
}
