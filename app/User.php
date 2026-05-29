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
  use HasFactory, HasApiTokens, Notifiable, Auditable, HasRoles {
    HasRoles::hasRole as protected spatieHasRole;
    HasRoles::hasAnyRole as protected spatieHasAnyRole;
    HasRoles::assignRole as protected spatieAssignRole;
    HasRoles::syncRoles as protected spatieSyncRoles;
    HasRoles::scopeRole as protected spatieScopeRole;
  }
  
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'username',
      'first_name',
      'last_name',
      'name', 
      'email', 
      'password', 
      'role_id',
      'division_id', 
      'location_id', 
      'phone',
      'portal_preferences',
      'profile_picture',
      // Notification preferences
      'notify_email',
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
      'role_id' => 'integer',
      'portal_preferences' => 'array',
  ];

  public function movement()
  {
    return $this->hasOne(Movement::class);
  }


  /**
   * Assets assigned to this user
   */
  public function assignedAssets()
  {
    return $this->hasMany(Asset::class, 'assigned_to');
  }

  // Legacy ticket relations removed (module deleted)

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

  /**
   * Optional primary role reference for quick LV lookups.
   * Canonical RBAC remains many-to-many via Spatie roles.
   */
  public function primaryRoleEntity()
  {
    return $this->belongsTo(Role::class, 'role_id');
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
      $q->whereIn('name', Role::expandNames(['administrator', 'developer']));
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
    $expanded = Role::equivalentNames((string) $roleName);

    return $query->whereHas('roles', function($q) use ($expanded) {
      $q->whereIn('name', $expanded);
    });
  }

  /**
   * Compatibility wrapper for role query scope.
   *
   * Allows legacy role names (e.g. admin/super-admin/management) to be used
   * in query builders without triggering Spatie role-not-found exceptions.
   */
  public function scopeRole($query, $roles, $guard = null)
  {
    $normalizedRoles = $this->normalizeRoleScopeInput($roles);

    if (empty($normalizedRoles)) {
      return $this->spatieScopeRole($query, $roles, $guard);
    }

    return $this->spatieScopeRole($query, $normalizedRoles, $guard);
  }

  /**
   * Compatibility wrapper for without-role query scope.
   */
  public function scopeWithoutRole($query, $roles, $guard = null)
  {
    $normalizedRoles = $this->normalizeRoleScopeInput($roles);
    $roleNames = !empty($normalizedRoles)
      ? $normalizedRoles
      : $this->extractRoleNamesForChecks($roles);

    if (empty($roleNames)) {
      return $query;
    }

    $expandedRoleNames = Role::expandNames($roleNames);

    return $query->whereDoesntHave('roles', function ($roleQuery) use ($expandedRoleNames, $guard) {
      $roleQuery->whereIn('name', $expandedRoleNames);

      if ($guard !== null) {
        $roleQuery->where('guard_name', $guard);
      }
    });
  }

  /**
   * Compatibility wrapper for role checks.
   * Legacy names (super-admin/admin/management) are mapped to canonical roles.
   */
  public function hasRole($roles, ?string $guard = null): bool
  {
    if (is_string($roles) || is_numeric($roles)) {
      foreach (Role::equivalentNames((string) $roles) as $roleName) {
        if ($this->spatieHasRole($roleName, $guard)) {
          return true;
        }
      }

      return false;
    }

    if ($roles instanceof \Illuminate\Support\Collection) {
      $roleNames = $this->extractRoleNamesForChecks($roles->all());
      if (!empty($roleNames)) {
        foreach (Role::expandNames($roleNames) as $roleName) {
          if ($this->spatieHasRole($roleName, $guard)) {
            return true;
          }
        }

        return false;
      }
    }

    if (is_array($roles)) {
      $roleNames = $this->extractRoleNamesForChecks($roles);
      if (!empty($roleNames)) {
        foreach (Role::expandNames($roleNames) as $roleName) {
          if ($this->spatieHasRole($roleName, $guard)) {
            return true;
          }
        }

        return false;
      }
    }

    return $this->spatieHasRole($roles, $guard);
  }

  /**
   * Compatibility wrapper for multi-role checks.
   */
  public function hasAnyRole(...$roles): bool
  {
    $roleNames = $this->extractRoleNamesForChecks($roles);

    if (empty($roleNames)) {
      return $this->spatieHasAnyRole(...$roles);
    }

    return $this->spatieHasAnyRole(...Role::expandNames($roleNames));
  }

  /**
   * Normalize role assignment payload to canonical names.
   */
  public function assignRole(...$roles)
  {
    return $this->spatieAssignRole(...$this->normalizeRolePayload($roles));
  }

  /**
   * Normalize role sync payload to canonical names.
   */
  public function syncRoles(...$roles)
  {
    return $this->spatieSyncRoles(...$this->normalizeRolePayload($roles));
  }

  /**
   * @param mixed $roles
   * @return array<int, string>
   */
  private function extractRoleNamesForChecks($roles): array
  {
    if ($roles instanceof \Illuminate\Support\Collection) {
      $roles = $roles->all();
    }

    $list = is_array($roles) ? $roles : [$roles];
    $names = [];

    foreach ($list as $role) {
      if (is_array($role)) {
        foreach ($this->extractRoleNamesForChecks($role) as $nestedName) {
          $names[] = $nestedName;
        }
        continue;
      }

      if ($role instanceof \Illuminate\Support\Collection) {
        foreach ($role->all() as $nestedRole) {
          $name = $this->extractRoleName($nestedRole);
          if ($name !== null) {
            $names[] = $name;
          }
        }
        continue;
      }

      $name = $this->extractRoleName($role);
      if ($name !== null) {
        $names[] = $name;
      }
    }

    return array_values(array_unique(array_filter($names, function (string $name): bool {
      return $name !== '';
    })));
  }

  /**
   * @param array<int, mixed> $roles
   * @return array<int, string>
   */
  private function normalizeRolePayload(array $roles): array
  {
    $normalized = [];

    foreach ($this->extractRoleNamesForChecks($roles) as $roleName) {
      $canonicalRoleName = Role::normalizeName($roleName);
      if ($canonicalRoleName !== '') {
        $normalized[] = $canonicalRoleName;
      }
    }

    return array_values(array_unique($normalized));
  }

  /**
   * @param mixed $roles
   * @return array<int, string>
   */
  private function normalizeRoleScopeInput($roles): array
  {
    $normalized = [];

    foreach ($this->extractRoleNamesForChecks($roles) as $roleName) {
      $canonicalRoleName = Role::normalizeName($roleName);
      if ($canonicalRoleName !== '') {
        $normalized[] = $canonicalRoleName;
      }
    }

    return array_values(array_unique($normalized));
  }

  /**
   * @param mixed $role
   */
  private function extractRoleName($role): ?string
  {
    if (is_string($role) || is_numeric($role)) {
      return strtolower(trim((string) $role));
    }

    if (is_object($role)) {
      if (method_exists($role, 'getAttribute')) {
        $attributeName = $role->getAttribute('name');
        if (is_string($attributeName) || is_numeric($attributeName)) {
          return strtolower(trim((string) $attributeName));
        }
      }

      if (isset($role->name) && (is_string($role->name) || is_numeric($role->name))) {
        return strtolower(trim((string) $role->name));
      }

      if (property_exists($role, 'name') && (is_string($role->name) || is_numeric($role->name))) {
        return strtolower(trim((string) $role->name));
      }
    }

    return null;
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
          'developer' => 'danger',
          'administrator' => 'warning',
          'director' => 'info',
          'human-resources' => 'primary',
          'receptionist' => 'info',
          'user' => 'success',
          'guest' => 'secondary',
        ];

        return $colors[Role::normalizeName((string) $role)] ?? 'secondary';
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
    return $this->hasAnyRole(['developer', 'administrator']);
  }

  /**
   * Check if user can view management dashboard
   */
  public function canViewManagementDashboard(): bool
  {
    return $this->hasAnyRole(['developer', 'administrator', 'director']);
  }

  /**
   * Check if user can manage assets
   */
  public function canManageAssets(): bool
  {
    return $this->hasAnyRole(['developer', 'administrator']);
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
      'admins' => self::whereHas('roles', fn($q) => $q->whereIn('name', Role::expandNames(['developer', 'administrator'])))->count(),
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
