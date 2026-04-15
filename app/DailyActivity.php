<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class DailyActivity extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'activity',
        'title',
        'date',
        'activity_date',
        'description',
        'ticket_id',
        'type',
        'activity_type',
        'duration_minutes',
        'notes',
        'status',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('activity_date', $date);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth($query, $year, $month)
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['user', 'ticket']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('activity_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('activity_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // ========================
    // ACCESSORS & MUTATORS
    // ========================
    
    /**
     * Format description for display (title case)
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => trim($value)
        );
    }

    /**
     * Get activity type badge
     */
    protected function typeBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                $type = $this->type ?? $this->activity_type ?? 'manual';
                $badges = [
                    'auto_from_ticket' => '<span class="badge badge-info">Auto (Ticket)</span>',
                    'asset_assignment' => '<span class="badge badge-success">Asset Assignment</span>',
                    'asset_unassignment' => '<span class="badge badge-warning">Asset Return</span>',
                    'asset_disposal' => '<span class="badge badge-danger">Asset Disposal</span>',
                    'maintenance' => '<span class="badge badge-secondary">Maintenance</span>',
                    'manual' => '<span class="badge badge-primary">Manual Entry</span>',
                ];
                return $badges[$type] ?? '<span class="badge badge-light">' . ucfirst($type) . '</span>';
            }
        );
    }

    /**
     * Get formatted duration
     */
    protected function formattedDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->duration_minutes) return null;
                
                if ($this->duration_minutes < 60) {
                    return $this->duration_minutes . ' minutes';
                }
                
                $hours = floor($this->duration_minutes / 60);
                $minutes = $this->duration_minutes % 60;
                
                return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
            }
        );
    }

    /**
     * Get formatted activity date
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activity_date ? $this->activity_date->format('d M Y') : null
        );
    }

    /**
     * Check if activity is today
     */
    protected function isToday(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activity_date && $this->activity_date->isToday()
        );
    }

    /**
     * Check if activity is this week
     */
    protected function isThisWeek(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activity_date && $this->activity_date->isBetween(
                now()->startOfWeek(), 
                now()->endOfWeek()
            )
        );
    }

    // ========================
    // HELPER METHODS
    // ========================
    
    /**
     * Mark activity as completed
     */
    public function markCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    /**
     * Mark activity as in progress
     */
    public function markInProgress(): bool
    {
        return $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark activity as pending
     */
    public function markPending(): bool
    {
        return $this->update(['status' => 'pending']);
    }

    /**
     * Add duration to activity
     */
    public function addDuration(int $minutes): bool
    {
        $currentDuration = $this->duration_minutes ?? 0;
        return $this->update(['duration_minutes' => $currentDuration + $minutes]);
    }

    /**
     * Get activities statistics for dashboard
     */
    public static function getStatistics(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'total' => self::where('activity_date', '>=', $startDate)->count(),
            'today' => self::today()->count(),
            'this_week' => self::thisWeek()->count(),
            'by_type' => self::where('activity_date', '>=', $startDate)
                            ->selectRaw('COALESCE(type, activity_type, "manual") as activity_type, COUNT(*) as count')
                            ->groupBy('activity_type')
                            ->pluck('count', 'activity_type')
                            ->toArray(),
            'by_user' => self::where('activity_date', '>=', $startDate)
                            ->with('user:id,name')
                            ->selectRaw('user_id, COUNT(*) as count')
                            ->groupBy('user_id')
                            ->orderBy('count', 'desc')
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn($item) => [$item->user->name ?? 'Unknown' => $item->count])
                            ->toArray(),
            'total_duration' => self::where('activity_date', '>=', $startDate)
                                  ->sum('duration_minutes') ?? 0,
        ];
    }

    /**
     * Get user activity summary for date range
     */
    public static function getUserActivitySummary(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $activities = self::forUser($userId)
                         ->dateRange($startDate, $endDate)
                         ->get();

        return [
            'total_activities' => $activities->count(),
            'total_duration' => $activities->sum('duration_minutes') ?? 0,
            'types_breakdown' => $activities->groupBy(fn($item) => $item->type ?? $item->activity_type ?? 'manual')
                                           ->map(fn($group) => $group->count())
                                           ->toArray(),
            'daily_breakdown' => $activities->groupBy(fn($item) => $item->activity_date->format('Y-m-d'))
                                           ->map(fn($group) => [
                                               'count' => $group->count(),
                                               'duration' => $group->sum('duration_minutes') ?? 0,
                                           ])
                                           ->toArray(),
            'ticket_related' => $activities->whereNotNull('ticket_id')->count(),
        ];
    }

    // Static methods
    public static function createFromTicketCompletion(Ticket $ticket)
    {
        return self::create([
            'user_id' => $ticket->assigned_to,
            'activity_date' => now()->toDateString(),
            'description' => "Menyelesaikan tiket #{$ticket->ticket_code}: {$ticket->subject}",
            'ticket_id' => $ticket->id,
            'type' => 'auto_from_ticket',
            'duration_minutes' => $ticket->assigned_at && $ticket->resolved_at 
                                 ? $ticket->assigned_at->diffInMinutes($ticket->resolved_at) 
                                 : null,
        ]);
    }

    /**
     * Create activity from asset assignment
     */
    public static function createFromAssetAssignment(Asset $asset, User $user): self
    {
        return self::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
            'description' => "Asset assigned: {$asset->asset_tag} - " . ($asset->model->name ?? 'Unknown Model'),
            'type' => 'asset_assignment',
            'notes' => "Automated asset assignment log",
        ]);
    }

    /**
     * Create activity from asset maintenance
     */
    public static function createFromMaintenance(Asset $asset, string $description, int $userId): self
    {
        return self::create([
            'user_id' => $userId,
            'activity_date' => now()->toDateString(),
            'description' => "Asset {$asset->asset_tag}: {$description}",
            'type' => 'maintenance',
            'notes' => "Automated maintenance log entry",
        ]);
    }
}