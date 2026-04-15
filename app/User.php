<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * App\User
 * 
 * @mixin \Spatie\Permission\Traits\HasRoles
 * @method bool hasRole($roles)
 * @method bool hasAnyRole($roles)
 * @method bool hasAllRoles($roles)
 * @method \Illuminate\Support\Collection getRoleNames()
 * @method mixed assignRole($roles)
 * @method mixed removeRole($roles)
 * @method mixed syncRoles($roles)
 * @method bool hasPermissionTo($permission)
 * @method bool hasAnyPermission($permissions)
 * @method bool hasAllPermissions($permissions)
 * @method mixed givePermissionTo($permissions)
 * @method mixed revokePermissionTo($permissions)
 * @method mixed syncPermissions($permissions)
 */
use App\Traits\Auditable;

class User extends Authenticatable
{
  use HasFactory, HasRoles, HasApiTokens, Notifiable, Auditable;
  
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'name', 
      'email', 
      'password', 
      'division_id', 
      'location_id', 
      'phone',
      // Notification preferences
      'notify_email',
      'notify_ticket_created',
      'notify_ticket_assigned',
      'notify_ticket_updated',
      'notify_meeting_approved',
      'notify_meeting_rejected',
  ];

  /**
   * The attributes that are not mass assignable.
   * 
   * These fields are protected to prevent unauthorized modifications
   * via mass assignment vulnerabilities.
   *
   * @var array
   */
  protected $guarded = [
      'is_active', 'api_token', 'email_verified_at', 'last_login_at'
  ];

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = [
      'password', 'remember_token', 'api_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'email_verified_at' => 'datetime',
      'last_login_at' => 'datetime',
      'is_active' => 'boolean',
  ];

  public function movement()
  {
    return $this->hasOne(Movement::class);
  }

  /**
   * Tickets created by this user
   */
  public function createdTickets()
  {
    return $this->hasMany(Ticket::class, 'user_id');
  }

  /**
   * Tickets assigned to this user
   */
  public function assignedTickets()
  {
    return $this->hasMany(Ticket::class, 'assigned_to');
  }

  /**
   * Assets assigned to this user
   */
  public function assignedAssets()
  {
    return $this->hasMany(Asset::class, 'assigned_to');
  }

  // Legacy alias for backward compatibility
  public function ticket()
  {
    return $this->createdTickets();
  }

  // Legacy alias for backward compatibility
  public function assets()
  {
    return $this->assignedAssets();
  }

  public function dailyActivities()
  {
    return $this->hasMany(DailyActivity::class);
  }

  public function notifications()
  {
    // Use Laravel's DatabaseNotification to support built-in notifications
    return $this->morphMany(DatabaseNotification::class, 'notifiable');
  }

  public function division()
  {
    return $this->belongsTo(Division::class);
  }

  public function location()
  {
    return $this->belongsTo(\App\Location::class);
  }

  public function adminOnlineStatus()
  {
    return $this->hasOne(AdminOnlineStatus::class);
  }

  /**
   * Menus with user-specific permissions
   */
  public function menus()
  {
    return $this->belongsToMany(Menu::class, 'menu_user')
                ->withPivot('can_view')
                ->withTimestamps();
  }

  // Scopes
  public function scopeWithRoles($query)
  {
    return $query->with('roles');
  }

  public function scopeAdmins($query)
  {
    return $query->whereHas('roles', function($q) {
      $q->whereIn('name', ['admin', 'super-admin']);
    });
  }

  public function scopeActiveUsers($query)
  {
    return $query->where('is_active', true);
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  public function scopeInactive($query)
  {
    return $query->where('is_active', false);
  }

  public function scopeByRole($query, $roleName)
  {
    return $query->whereHas('roles', function($q) use ($roleName) {
      $q->where('name', $roleName);
    });
  }

  // ========================
  // ACCESSORS & MUTATORS
  // ========================
  
  /**
   * Format name for display (title case)
   */
  protected function name(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => ucwords(strtolower($value)),
      set: fn ($value) => ucwords(strtolower(trim($value)))
    );
  }

  /**
   * Set password with automatic hashing
   */
  protected function password(): Attribute
  {
    return Attribute::make(
      set: fn ($value) => Hash::make($value)
    );
  }

  /**
   * Get user's initials for avatar
   */
  protected function initials(): Attribute
  {
    return Attribute::make(
      get: function () {
        $names = explode(' ', $this->name);
        if (count($names) >= 2) {
          return strtoupper(substr($names[0], 0, 1) . substr($names[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
      }
    );
  }

  /**
   * Get user's primary role name
   */
  protected function primaryRole(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->roles->first()?->name ?? 'User'
    );
  }

  /**
   * Backwards-compatibility accessor for legacy code that expects a
   * string $user->role property. Returns the first assigned role name
   * (if any) or null.
   */
  public function getRoleAttribute()
  {
    return $this->roles->first()?->name ?? null;
  }

  /**
   * Get user's role color for UI
   */
  protected function roleColor(): Attribute
  {
    return Attribute::make(
      get: function () {
        $role = $this->primary_role;
        $colors = [
          'super-admin' => 'danger',
          'admin' => 'warning', 
          'management' => 'info',
          'user' => 'success',
        ];
        return $colors[$role] ?? 'secondary';
      }
    );
  }

  /**
   * Check if user was online recently (within 5 minutes)
   */
  protected function isOnline(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->last_login_at && $this->last_login_at->gt(now()->subMinutes(5))
    );
  }

  /**
   * Get formatted last login time
   */
  protected function lastLoginFormatted(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Never'
    );
  }

  // ========================
  // HELPER METHODS
  // ========================
  
  /**
   * Check if user can manage other users
   */
  public function canManageUsers(): bool
  {
    return $this->hasAnyRole(['super-admin', 'admin']);
  }

  /**
   * Check if user can view management dashboard
   */
  public function canViewManagementDashboard(): bool
  {
    return $this->hasAnyRole(['super-admin', 'admin', 'management']);
  }

  /**
   * Check if user can manage assets
   */
  public function canManageAssets(): bool
  {
    return $this->hasAnyRole(['super-admin', 'admin']);
  }

  /**
   * Get user's workload (active tickets assigned)
   */
  public function getWorkload(): int
  {
    return $this->assignedTickets()
                ->whereNull('resolved_at')
                ->count();
  }

  /**
   * Get user's performance metrics
   */
  public function getPerformanceMetrics(int $days = 30): array
  {
    $startDate = now()->subDays($days);
    
    $assignedTickets = $this->assignedTickets()
                            ->where('assigned_at', '>=', $startDate);

    $resolvedTickets = $assignedTickets->whereNotNull('resolved_at')->get();
    $totalAssigned = $assignedTickets->count();

    $avgResponseTime = null;
    $avgResolutionTime = null;

    if ($resolvedTickets->count() > 0) {
      $totalResponseMinutes = $resolvedTickets->sum(function($ticket) {
        return $ticket->assigned_at && $ticket->first_response_at 
               ? $ticket->assigned_at->diffInMinutes($ticket->first_response_at) 
               : 0;
      });

      $totalResolutionMinutes = $resolvedTickets->sum(function($ticket) {
        return $ticket->assigned_at && $ticket->resolved_at 
               ? $ticket->assigned_at->diffInMinutes($ticket->resolved_at) 
               : 0;
      });

      $avgResponseTime = round($totalResponseMinutes / $resolvedTickets->count());
      $avgResolutionTime = round($totalResolutionMinutes / $resolvedTickets->count());
    }

    return [
      'period_days' => $days,
      'assigned_tickets' => $totalAssigned,
      'resolved_tickets' => $resolvedTickets->count(),
      'resolution_rate' => $totalAssigned > 0 ? round(($resolvedTickets->count() / $totalAssigned) * 100, 1) : 0,
      'avg_response_time_minutes' => $avgResponseTime,
      'avg_resolution_time_minutes' => $avgResolutionTime,
      'current_workload' => $this->getWorkload(),
    ];
  }

  /**
   * Get user's recent activities
   */
  public function getRecentActivities(int $days = 7)
  {
    return $this->dailyActivities()
                ->where('activity_date', '>=', now()->subDays($days))
                ->orderBy('activity_date', 'desc')
                ->get();
  }

  /**
   * Get user's asset assignments
   */
  public function getAssetAssignments()
  {
    return $this->assets()
                ->with(['model', 'status'])
                ->orderBy('created_at', 'desc')
                ->get();
  }

  /**
   * Update last login timestamp
   */
  public function updateLastLogin(): void
  {
    $this->update(['last_login_at' => now()]);
  }

  /**
   * Activate user account
   */
  public function activate(): bool
  {
    return $this->update(['is_active' => true]);
  }

  /**
   * Deactivate user account
   */  
  public function deactivate(): bool
  {
    return $this->update(['is_active' => false]);
  }

  /**
   * Get user statistics for dashboard
   */
  public static function getStatistics(): array
  {
    return [
      'total' => self::count(),
      'active' => self::active()->count(),
      'inactive' => self::inactive()->count(),
      'online' => self::where('last_login_at', '>', now()->subMinutes(5))->count(),
      'admins' => self::whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin', 'admin']))->count(),
      'never_logged_in' => self::whereNull('last_login_at')->count(),
    ];
  }

  /**
   * Get top performers based on ticket resolution
   */
  public static function getTopPerformers(int $days = 30, int $limit = 5)
  {
    return self::withCount(['assignedTickets as resolved_tickets_count' => function($q) use ($days) {
                 $q->whereNotNull('resolved_at')
                   ->where('assigned_at', '>=', now()->subDays($days));
               }])
               ->having('resolved_tickets_count', '>', 0)
               ->orderBy('resolved_tickets_count', 'desc')
               ->limit($limit)
               ->get();
  }
}
