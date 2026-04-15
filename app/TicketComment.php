<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Comments on tickets - separate from TicketsEntry for cleaner interface
 * Can be internal (staff notes) or external (visible to customers in portal)
 */
class TicketComment extends Model
{
    protected $table = 'ticket_comments';
    
    protected $fillable = ['ticket_id', 'user_id', 'comment', 'is_internal'];
    
    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================
    // RELATIONSHIPS
    // ========================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ========================
    // SCOPES
    // ========================

    /**
     * Get only internal comments (staff notes)
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Get only external comments (customer visible)
     */
    public function scopeExternal($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Get comments by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Eager load user relationship
     */
    public function scopeWithUser($query)
    {
        return $query->with('user:id,name,email');
    }

    // ========================
    // ACCESSORS
    // ========================

    /**
     * Get formatted comment type label
     */
    public function getCommentTypeAttribute()
    {
        return $this->is_internal ? 'Internal Note' : 'Public Comment';
    }

    /**
     * Get user display name with type badge
     */
    public function getUserBadgeAttribute()
    {
        $type = $this->is_internal ? '<span class="badge badge-warning">Internal</span>' : '';
        return $this->user->name . ' ' . $type;
    }
}
