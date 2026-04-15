<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use App\Traits\Auditable;
use App\Traits\SortableQuery;
use App\Traits\SearchServiceTrait;
use App\Traits\FilterBuilder;
use App\Traits\BulkOperationBuilder;
use App\Traits\ExportBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Listeners\TicketChangeLogger;
use App\TicketHistory;

class Ticket extends Model implements HasMedia
{
  use InteractsWithMedia, Auditable, SortableQuery, SearchServiceTrait, FilterBuilder, BulkOperationBuilder, ExportBuilder, HasFactory;
  
  protected $fillable = [
    'user_id', 'location_id', 'ticket_status_id', 'ticket_type_id', 
    'ticket_priority_id', 'subject', 'description', 'ticket_code',
    'assigned_to', 'assigned_at', 'assignment_type', 'sla_due',
    'first_response_at', 'resolved_at', 'asset_id'
  ];

  /**
   * Append accessors to array/JSON output
   * @var array
   */
  protected $appends = ['code'];

  /**
   * FULLTEXT searchable columns
   * @var array
   */
  protected $searchColumns = ['subject', 'description', 'ticket_code'];

  protected $dates = [
    'assigned_at', 'sla_due', 'first_response_at', 'resolved_at', 'closed'
  ];

  protected $casts = [
    'assigned_at' => 'datetime',
    'sla_due' => 'datetime',
    'first_response_at' => 'datetime',
    'resolved_at' => 'datetime',
    'closed' => 'datetime',
  ];

  /**
   * Define sortable columns for API queries
   * @return array
   */
  public function getSortableColumns()
  {
    return [
      'id' => 'id',
      'ticket_code' => 'ticket_code',
      'subject' => 'subject',
      'created_at' => 'created_at',
      'updated_at' => 'updated_at',
      'resolved_at' => 'resolved_at',
      'sla_due' => 'sla_due',
      'status_id' => 'ticket_status_id',
      'priority_id' => 'ticket_priority_id',
      'assigned_to' => 'assigned_to',
    ];
  }

  /**
   * Define relationship-based sorting (requires joins)
   * @return array
   */
  public function getSortableRelations()
  {
    return [
      // tickets_statuses stores the label in `status` column
      'status' => ['tickets_statuses', 'id', 'status'],
      'priority' => ['tickets_priorities', 'id', 'name'],
      'user' => ['users', 'id', 'name'],
      'assigned' => ['users', 'id', 'name'],  // Sort by assigned technician
    ];
  }

  protected static function boot()
  {
    parent::boot();
    
    static::creating(function ($ticket) {
      $ticket->ticket_code = self::generateTicketCode();
      // SLA is now calculated by SLALearningSystem in TicketService
      // Don't auto-calculate here to avoid overwriting intelligent SLA
    });

    /**
     * Automatically log ticket changes to ticket_history table.
     * Tracks: ticket_status_id, ticket_priority_id, assigned_to, sla_due, resolved_at
     * Uses updating() hook to capture changes before they're persisted to DB
     */
    static::updating(function ($ticket) {
      try {
        $original = $ticket->getOriginal();
        $changes = $ticket->getDirty();
        
        // List of fields to track in audit log
        $trackedFields = ['ticket_status_id', 'ticket_priority_id', 'assigned_to', 'sla_due', 'resolved_at'];
        
        foreach ($trackedFields as $field) {
          if (array_key_exists($field, $changes)) {
            $oldValue = $original[$field] ?? null;
            $newValue = $changes[$field];
            
            // Only log if value actually changed
            if ($oldValue !== $newValue) {
              TicketHistory::create([
                'ticket_id' => $ticket->id,
                'field_changed' => $field,
                'old_value' => $oldValue === null ? null : (string) $oldValue,
                'new_value' => $newValue === null ? null : (string) $newValue,
                'changed_by_user_id' => auth()->id() ?? null,
                'changed_at' => now(),
                'change_type' => 'field_change',
                'event_type' => 'update',
              ]);
            }
          }
        }
      } catch (\Exception $e) {
        // Log the error but don't throw - allow the update to proceed
        Log::error('Failed to create ticket history record', [
          'ticket_id' => $ticket->id ?? null,
          'error' => $e->getMessage(),
          'code' => $e->getCode(),
        ]);
      }
    });
  }

  public static function generateTicketCode()
  {
    $prefix = 'TKT';
    $date = now()->format('Ymd');
    $lastTicket = self::whereDate('created_at', today())
                     ->orderBy('id', 'desc')
                     ->first();
    
    $sequence = $lastTicket ? 
                (int)substr($lastTicket->ticket_code, -3) + 1 : 1;
    
    return $prefix . '-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
  }

  public static function calculateSLADue($priorityId)
  {
    $slaHours = [
      1 => 4,   // Urgent < 4 hours
      2 => 24,  // High < 1 day
      3 => 72,  // Medium < 3 days
      4 => 168, // Low < 1 week
    ];
    
    return now()->addHours($slaHours[$priorityId] ?? 72);
  }

  /**
   * Register media collections
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('attachments')
         ->acceptsMimeTypes([
           'image/jpeg', 'image/png', 'image/gif', 'image/webp',
           'application/pdf', 'application/msword', 
           'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
           'application/vnd.ms-excel',
           'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
           'text/plain', 'text/csv'
         ]);
    
    $this->addMediaCollection('screenshots')
         ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
  }

  // Relationships
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  public function location()
  {
    return $this->belongsTo(Location::class);
  }

  public function asset()
  {
    return $this->belongsTo(Asset::class);
  }

  /**
   * Many-to-many relation to assets via pivot table 'ticket_assets'.
   * We keep the existing single 'asset' relation for backwards compatibility.
   */
  public function assets()
  {
    return $this->belongsToMany(Asset::class, 'ticket_assets', 'ticket_id', 'asset_id')->withTimestamps();
  }

  public function ticket_status()
  {
    return $this->belongsTo(TicketsStatus::class, 'ticket_status_id');
  }

  public function ticket_priority()
  {
    return $this->belongsTo(TicketsPriority::class, 'ticket_priority_id');
  }

  public function ticket_type()
  {
    return $this->belongsTo(TicketsType::class, 'ticket_type_id');
  }

  /**
   * Immutable audit trail for all ticket changes
   */
  public function history()
  {
    return $this->hasMany(TicketHistory::class)->orderBy('changed_at', 'desc');
  }

  /**
   * Comments on this ticket (separate from ticket entries for cleaner interface)
   */
  public function comments()
  {
    return $this->hasMany(TicketComment::class)->orderBy('created_at', 'desc');
  }

  // ========================
  // ACCESSORS & MUTATORS
  // ========================
  
  /**
   * Format ticket subject for display (uppercase first letter)
   */
  protected function subject(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => ucfirst($value),
      set: fn ($value) => strtolower(trim($value))
    );
  }

  /**
   * Map 'code' attribute to 'ticket_code' column for backwards compatibility
   * Allows accessing $ticket->code instead of $ticket->ticket_code
   */
  protected function code(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => $this->ticket_code
    );
  }

  /**
   * Get human-readable status badge
   */
  protected function statusBadge(): Attribute
  {
    return Attribute::make(
      get: function () {
        $status = $this->ticket_status->name ?? 'Unknown';
        $badges = [
          'Open' => '<span class="badge badge-info">Open</span>',
          'In Progress' => '<span class="badge badge-warning">In Progress</span>',
          'Resolved' => '<span class="badge badge-success">Resolved</span>',
          'Closed' => '<span class="badge badge-secondary">Closed</span>',
          'Pending' => '<span class="badge badge-secondary">Pending</span>',
        ];
        return $badges[$status] ?? '<span class="badge badge-light">' . $status . '</span>';
      }
    );
  }

  /**
   * Get priority color class
   */
  protected function priorityColor(): Attribute
  {
    return Attribute::make(
      get: function () {
        $priority = $this->ticket_priority->name ?? 'Normal';
        $colors = [
          'Urgent' => 'text-danger',
          'High' => 'text-warning',
          'Normal' => 'text-info',
          'Low' => 'text-success',
        ];
        return $colors[$priority] ?? 'text-muted';
      }
    );
  }

  /**
   * Check if ticket is overdue
   */
  protected function isOverdue(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->sla_due && now()->gt($this->sla_due) && !$this->resolved_at
    );
  }

  /**
   * Get time remaining until SLA due
   */
  protected function timeToSla(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->sla_due) return null;
        
        $now = now();
        if ($now->gt($this->sla_due)) {
          return 'Overdue by ' . $this->sla_due->diffForHumans($now, true);
        }
        return $this->sla_due->diffForHumans($now);
      }
    );
  }

  /**
   * Get response time in hours
   */
  protected function responseTimeHours(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->first_response_at) return null;
        return round($this->created_at->diffInHours($this->first_response_at), 2);
      }
    );
  }

  /**
   * Get resolution time in hours
   */
  protected function resolutionTimeHours(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->resolved_at) return null;
        return round($this->created_at->diffInHours($this->resolved_at), 2);
      }
    );
  }

  // ========================
  // HELPER METHODS
  // ========================
  
  /**
   * Check if ticket can be assigned
   */
  public function canBeAssigned(): bool
  {
    return in_array($this->ticket_status->name ?? '', ['Open', 'Pending']);
  }

  /**
   * Check if ticket can be resolved
   */
  public function canBeResolved(): bool
  {
    return in_array($this->ticket_status->name ?? '', ['Open', 'In Progress', 'Pending']);
  }

  /**
   * Check if ticket can be reopened
   */
  public function canBeReopened(): bool
  {
    return in_array($this->ticket_status->name ?? '', ['Resolved', 'Closed']);
  }

  /**
   * Assign ticket to user
   */
  public function assignTo(User $user, string $assignmentType = 'manual'): bool
  {
    if (!$this->canBeAssigned()) {
      return false;
    }

    $this->update([
      'assigned_to' => $user->id,
      'assigned_at' => now(),
      'assignment_type' => $assignmentType,
    ]);

    // Update status to 'In Progress' if currently 'Open'
    if ($this->ticket_status->name === 'Open') {
      $inProgressStatus = TicketsStatus::where('status', 'In Progress')->first();
      if ($inProgressStatus) {
        $this->update(['ticket_status_id' => $inProgressStatus->id]);
      }
    }

    // Send notification for assignment using Laravel notification system
    try {
      $assignedBy = Auth::user() ?? User::find($this->user_id);
      $user->notify(new \App\Notifications\TicketAssigned($this, $assignedBy));
    } catch (\Exception $e) {
      Log::error('Failed to send ticket assignment notification', [
        'ticket_id' => $this->id,
        'user_id' => $user->id,
        'error' => $e->getMessage()
      ]);
    }

    return true;
  }

  /**
   * Mark ticket as first responded
   */
  public function markFirstResponse(): bool
  {
    if ($this->first_response_at) {
      return false; // Already has first response
    }

    $this->update(['first_response_at' => now()]);
    return true;
  }

  /**
   * Resolve ticket
   */
  public function resolve(?string $resolution = null): bool
  {
    if (!$this->canBeResolved()) {
      return false;
    }

  $resolvedStatus = TicketsStatus::where('status', 'Resolved')->first();
    if (!$resolvedStatus) {
      return false;
    }

    $updateData = [
      'ticket_status_id' => $resolvedStatus->id,
      'resolved_at' => now(),
    ];

    if ($resolution) {
      $updateData['resolution'] = $resolution;
    }

    $this->update($updateData);
    
    // Log daily activity for ticket resolution
    if ($this->assigned_to && $this->resolved_at) {
      $durationMinutes = $this->created_at->diffInMinutes($this->resolved_at);
      DailyActivity::create([
        'user_id' => $this->assigned_to,
        'ticket_id' => $this->id,
        'activity_type' => 'ticket_resolution',
        'type' => 'ticket_resolution',
        'title' => 'Ticket Resolved: ' . $this->subject,
        'description' => 'Ticket #' . $this->ticket_code . ' resolved after ' . $durationMinutes . ' minutes',
        'duration_minutes' => $durationMinutes,
        'activity_date' => $this->resolved_at->toDateString(),
        'notes' => $resolution,
      ]);
    }
    
    return true;
  }

  /**
   * Close ticket
   * @param string|null $closureNotes Optional closure notes
   */
  public function close(?string $closureNotes = null): bool
  {
  $closedStatus = TicketsStatus::where('status', 'Closed')->first();
    if (!$closedStatus) {
      return false;
    }

    $updateData = [
      'ticket_status_id' => $closedStatus->id,
      'closed' => now(),
    ];
    
    if ($closureNotes) {
      $updateData['closure_notes'] = $closureNotes;
    }
    
    $this->update($updateData);
    
    // Log daily activity for ticket closure
    if ($this->assigned_to && $this->closed) {
      $durationMinutes = $this->created_at->diffInMinutes($this->closed);
      DailyActivity::create([
        'user_id' => $this->assigned_to,
        'ticket_id' => $this->id,
        'activity_type' => 'ticket_close',
        'type' => 'ticket_close',
        'title' => 'Ticket Closed: ' . $this->subject,
        'description' => 'Ticket #' . $this->ticket_code . ' closed after ' . $durationMinutes . ' minutes',
        'duration_minutes' => $durationMinutes,
        'activity_date' => $this->closed->toDateString(),
      ]);
    }

    return true;
  }

  /**
   * Reopen ticket
   * @param string|null $reopenReason Optional reason for reopening
   */
  public function reopen(?string $reopenReason = null): bool
  {
    if (!$this->canBeReopened()) {
      return false;
    }

  $openStatus = TicketsStatus::where('status', 'Open')->first();
    if (!$openStatus) {
      return false;
    }

    $updateData = [
      'ticket_status_id' => $openStatus->id,
      'resolved_at' => null,
      'closed' => null,
    ];
    
    if ($reopenReason) {
      $updateData['reopen_reason'] = $reopenReason;
    }
    
    $this->update($updateData);

    return true;
  }

  /**
   * Sync assets for this ticket via many-to-many pivot table
   * @param array $assetIds Array of asset IDs to attach
   * @param bool $detach Whether to detach existing assets first
   * @return void
   */
  public function syncAssets(array $assetIds, bool $detach = true): void
  {
    try {
      if ($detach) {
        // Replace all assets
        $this->assets()->sync(array_values($assetIds));
      } else {
        // Add without removing existing
        $this->assets()->syncWithoutDetaching(array_values($assetIds));
      }
    } catch (\Exception $e) {
      Log::error('Failed to sync ticket assets', [
        'ticket_id' => $this->id,
        'asset_ids' => $assetIds,
        'error' => $e->getMessage()
      ]);
      throw $e;
    }
  }

  /**
   * Get tickets statistics for dashboard
   */
  public static function getStatistics(): array
  {
    return [
      'total' => self::count(),
  'open' => self::whereHas('ticket_status', fn($q) => $q->where('status', 'Open'))->count(),
  'in_progress' => self::whereHas('ticket_status', fn($q) => $q->where('status', 'In Progress'))->count(),
  'resolved' => self::whereHas('ticket_status', fn($q) => $q->where('status', 'Resolved'))->count(),
  'closed' => self::whereHas('ticket_status', fn($q) => $q->where('status', 'Closed'))->count(),
      'overdue' => self::where('sla_due', '<', now())
                      ->whereNull('resolved_at')
                      ->count(),
  'pending_response' => self::whereNull('first_response_at')
           ->whereHas('ticket_status', fn($q) => $q->whereIn('status', ['Open', 'In Progress']))
           ->count(),
    ];
  }

  /**
   * Get overdue tickets
   */
  public static function getOverdueTickets()
  {
    return self::with(['user', 'ticket_status', 'ticket_priority', 'assignedUser'])
               ->where('sla_due', '<', now())
               ->whereNull('resolved_at')
               ->orderBy('sla_due', 'asc')
               ->get();
  }

  /**
   * Get tickets requiring first response
   */
  public static function getPendingResponseTickets()
  {
    return self::with(['user', 'ticket_status', 'ticket_priority', 'assignedUser'])
               ->whereNull('first_response_at')
               ->whereHas('ticket_status', fn($q) => $q->whereIn('name', ['Open', 'In Progress']))
               ->orderBy('created_at', 'asc')
               ->get();
  }

  public function ticket_entries()
  {
    return $this->hasMany(TicketsEntry::class);
  }

  public function daily_activities()
  {
    return $this->hasMany(DailyActivity::class);
  }

  /**
   * Get notifications related to this ticket
   * Using Laravel's DatabaseNotification model
   */
  public function getNotifications()
  {
    return \Illuminate\Notifications\DatabaseNotification::whereJsonContains('data->ticket_id', $this->id)->get();
  }

  // Scopes
  public function scopeUnassigned($query)
  {
    return $query->whereNull('assigned_to');
  }

  public function scopeOverdue($query)
  {
    return $query->where('sla_due', '<', now())
                     ->whereHas('ticket_status', function($q) {
                         $q->whereNotIn('status', ['Closed', 'Resolved']);
                     });
  }

  public function scopeNearDeadline($query, $hours = 2)
  {
    return $query->where('sla_due', '<=', now()->addHours($hours))
                 ->where('sla_due', '>', now())
                     ->whereHas('ticket_status', function($q) {
                         $q->whereNotIn('status', ['Closed', 'Resolved']);
                     });
  }

  public function scopeByStatus($query, $statusId)
  {
    return $query->where('ticket_status_id', $statusId);
  }

  public function scopeByPriority($query, $priorityId)
  {
    return $query->where('ticket_priority_id', $priorityId);
  }

  public function scopeByType($query, $typeId)
  {
    return $query->where('ticket_type_id', $typeId);
  }

  public function scopeAssignedTo($query, $userId)
  {
    return $query->where('assigned_to', $userId);
  }

  public function scopeAssigned($query)
  {
    return $query->whereNotNull('assigned_to');
  }

  public function scopeForUser($query, $userId)
  {
    return $query->where('user_id', $userId);
  }

  public function scopeOpen($query)
  {
        return $query->whereHas('ticket_status', function($q) {
            $q->whereNotIn('status', ['Closed', 'Resolved']);
        });
  }

  public function scopeResolved($query)
  {
        return $query->whereHas('ticket_status', function($q) {
            $q->whereIn('status', ['Resolved', 'Closed']);
        });
  }

  public function scopeClosed($query)
  {
        return $query->whereHas('ticket_status', function($q) {
            $q->whereIn('status', ['Closed', 'Resolved']);
        });
  }

  public function scopeUrgent($query)
  {
    return $query->where('ticket_priority_id', 1); // Assuming 1 = Urgent
  }

  public function scopeHigh($query)
  {
    return $query->where('ticket_priority_id', 2); // Assuming 2 = High
  }

  public function scopeCreatedBetween($query, $startDate, $endDate)
  {
    return $query->whereBetween('created_at', [$startDate, $endDate]);
  }

  public function scopeResolvedBetween($query, $startDate, $endDate)
  {
    return $query->whereBetween('resolved_at', [$startDate, $endDate])
                 ->whereNotNull('resolved_at');
  }

  public function scopeWithAllRelations($query)
  {
    return $query->with([
      'user', 'assignedTo', 'location', 'asset', 
      'ticket_status', 'ticket_priority', 'ticket_type'
    ]);
  }

  /**
   * Enhanced eager loading with nested relationships
   * Includes nested user data and asset information
   */
  public function scopeWithNestedRelations($query)
  {
    return $query->with([
      'user',           // Creator
      'assignedTo',     // Assigned technician
      'location',
      'asset.status',   // Asset status (nested)
      'assets',         // Many-to-many assets
      'ticket_status',
      'ticket_priority',
      'ticket_type',
      'comments.user'   // Comments with author info
    ]);
  }

  /**
   * Eager load all data including audit trail
   * For detail views and reports
   */
  public function scopeWithAllData($query)
  {
    return $query->with([
      'user',
      'assignedTo',
      'location',
      'asset',
      'assets.division',          // Assets with division info
      'ticket_status',
      'ticket_priority',
      'ticket_type',
      'comments.user',
      'history',
      'dailyActivities'           // Activity tracking
    ]);
  }

  public function scopeWithRelations($query)
  {
    return $query->with([
      'user', 'assignedTo', 'location', 'asset', 
      'ticket_status', 'ticket_priority', 'ticket_type'
    ]);
  }

  // Accessors
  public function getIsOverdueAttribute()
  {
    return $this->sla_due && $this->sla_due->isPast() && 
           !in_array($this->ticket_status_id, [3, 4]);
  }

  public function getTimeRemainingAttribute()
  {
    if (!$this->sla_due) return null;
    
    $diff = now()->diffInHours($this->sla_due, false);
    return $diff > 0 ? $diff : 0;
  }

  public function getSlaStatusAttribute()
  {
    if (!$this->sla_due) return 'No SLA';
    
    if ($this->isOverdue) return 'Overdue';
    
    $hoursRemaining = $this->time_remaining;
    if ($hoursRemaining <= 2) return 'Critical';
    if ($hoursRemaining <= 8) return 'Warning';
    
    return 'On Track';
  }

  public function getSlaStatusColorAttribute()
  {
    $colors = [
      'Overdue' => 'red',
      'Critical' => 'orange', 
      'Warning' => 'yellow',
      'On Track' => 'green',
      'No SLA' => 'gray'
    ];
    
    return $colors[$this->sla_status] ?? 'gray';
  }

  // Methods (duplicate methods removed - using enhanced versions above)

  public function markResolved()
  {
    $this->update([
      'resolved_at' => now(),
      'ticket_status_id' => 3 // Assuming 3 = Resolved
    ]);
    
    // Auto-create daily activity
    DailyActivity::createFromTicketCompletion($this);
  }

  public function getMaintenanceHistoryAttribute()
  {
    if (!$this->asset_id) return collect();
    
    return self::where('asset_id', $this->asset_id)
               ->where('id', '!=', $this->id)
               ->with(['ticket_status', 'ticket_priority', 'assignedTo'])
               ->orderBy('created_at', 'desc')
               ->get();
  }
}
