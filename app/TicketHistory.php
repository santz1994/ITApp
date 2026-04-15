<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit log for ticket changes
 * This table never gets updated or deleted - only appended to
 */
class TicketHistory extends Model
{
    protected $table = 'ticket_history';
    
    // Immutable - no updates allowed
    protected $fillable = ['ticket_id', 'field_changed', 'old_value', 'new_value', 'changed_by_user_id', 'changed_at', 'change_type', 'event_type', 'reason'];
    
    // Cast timestamps
    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
    ];
    
    // Don't auto-manage timestamps - we set changed_at manually
    public $timestamps = false;

    // ========================
    // RELATIONSHIPS
    // ========================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    // ========================
    // SCOPES
    // ========================

    /**
     * Get history for a specific field
     */
    public function scopeForField($query, $fieldName)
    {
        return $query->where('field_changed', $fieldName);
    }

    /**
     * Get history for a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('changed_by_user_id', $userId);
    }

    /**
     * Get history within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('changed_at', [$startDate, $endDate]);
    }

    // ========================
    // MUTATORS
    // ========================

    /**
     * Prevent updates - throw exception if attempted
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception('TicketHistory is immutable - cannot update');
    }

    /**
     * Prevent deletion - throw exception if attempted
     */
    public function delete()
    {
        throw new \Exception('TicketHistory is immutable - cannot delete');
    }
}
