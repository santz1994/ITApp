<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'access_level',
    ];

    protected $casts = [
        'access_level' => 'integer',
    ];

    /**
     * Default canonical roles from Project.md.
     *
     * @var array<int, string>
     */
    private const DEFAULT_CANONICAL_ROLES = [
        'guest',
        'user',
        'receptionist',
        'human-resources',
        'director',
        'administrator',
        'developer',
    ];

    /**
     * Default legacy alias mapping.
     *
     * @var array<string, string>
     */
    private const DEFAULT_ROLE_ALIASES = [
        'super-admin' => 'developer',
        'super_admin' => 'developer',
        'admin' => 'administrator',
        'management' => 'director',
        'human resources' => 'human-resources',
        'human_resources' => 'human-resources',
        'hr' => 'human-resources',
    ];

    /**
     * Default LV mapping from Project.md.
     *
     * @var array<string, int>
     */
    private const DEFAULT_ROLE_LEVELS = [
        'guest' => 0,
        'user' => 1,
        'receptionist' => 2,
        'human-resources' => 3,
        'director' => 8,
        'administrator' => 9,
        'developer' => 10,
    ];

    /**
     * Canonical role names allowed by application policy.
     *
     * @return array<int, string>
     */
    public static function canonicalNames(): array
    {
        $configured = config('itquty.canonical_roles', self::DEFAULT_CANONICAL_ROLES);

        if (!is_array($configured) || empty($configured)) {
            $configured = self::DEFAULT_CANONICAL_ROLES;
        }

        $canonical = array_map(static function ($roleName): string {
            return self::normalizeName((string) $roleName);
        }, $configured);

        return array_values(array_unique(array_filter($canonical, static function ($roleName): bool {
            return $roleName !== '';
        })));
    }

    /**
     * Names assignable to authenticated users (exclude guest role).
     *
     * @return array<int, string>
     */
    public static function assignableNames(): array
    {
        return array_values(array_filter(self::canonicalNames(), static function (string $roleName): bool {
            return $roleName !== 'guest';
        }));
    }

    /**
     * Legacy alias map => canonical role names.
     *
     * @return array<string, string>
     */
    public static function aliasToCanonicalMap(): array
    {
        $configured = config('itquty.role_aliases', []);
        $configured = is_array($configured) ? $configured : [];

        $map = array_merge(self::DEFAULT_ROLE_ALIASES, $configured);
        $normalizedMap = [];

        foreach ($map as $alias => $canonical) {
            $aliasKey = strtolower(trim((string) $alias));
            $canonicalName = strtolower(trim((string) $canonical));

            if ($aliasKey === '' || $canonicalName === '') {
                continue;
            }

            $normalizedMap[$aliasKey] = $canonicalName;
        }

        return $normalizedMap;
    }

    /**
     * Normalize role name into canonical form.
     */
    public static function normalizeName(string $roleName): string
    {
        $normalized = strtolower(trim($roleName));

        if ($normalized === '') {
            return '';
        }

        $aliasMap = self::aliasToCanonicalMap();

        return $aliasMap[$normalized] ?? $normalized;
    }

    /**
     * Resolve equivalent role names for compatibility checks.
     *
     * @return array<int, string>
     */
    public static function equivalentNames(string $roleName): array
    {
        $rawName = strtolower(trim($roleName));

        if ($rawName === '') {
            return [];
        }

        $canonical = self::normalizeName($rawName);
        $equivalents = [$canonical, $rawName];

        foreach (self::aliasToCanonicalMap() as $alias => $targetCanonical) {
            if ($targetCanonical === $canonical) {
                $equivalents[] = $alias;
            }
        }

        return array_values(array_unique(array_filter($equivalents, static function (string $name): bool {
            return $name !== '';
        })));
    }

    /**
     * Expand multiple role names into their equivalent names.
     *
     * @param array<int, string> $roleNames
     * @return array<int, string>
     */
    public static function expandNames(array $roleNames): array
    {
        $expanded = [];

        foreach ($roleNames as $roleName) {
            $expanded = array_merge($expanded, self::equivalentNames((string) $roleName));
        }

        return array_values(array_unique($expanded));
    }

    /**
     * Access level definitions from Project.md.
     *
     * @return array<string, int>
     */
    public static function projectRoleLevels(): array
    {
        $levels = config('itquty.role_levels', self::DEFAULT_ROLE_LEVELS);

        if (!is_array($levels) || empty($levels)) {
            return self::DEFAULT_ROLE_LEVELS;
        }

        $normalizedLevels = [];

        foreach ($levels as $roleName => $accessLevel) {
            $normalizedRoleName = self::normalizeName((string) $roleName);
            if ($normalizedRoleName === '') {
                continue;
            }

            $normalizedLevels[$normalizedRoleName] = (int) $accessLevel;
        }

        return $normalizedLevels;
    }

    /**
     * Scope query to canonical roles.
     */
    public function scopeCanonical(Builder $query): Builder
    {
        return $query->whereIn('name', self::canonicalNames());
    }

    /**
     * Scope query to canonical roles assignable to authenticated users.
     */
    public function scopeAssignable(Builder $query): Builder
    {
        return $query->whereIn('name', self::assignableNames());
    }

    /**
     * Check whether this role is canonical.
     */
    public function isCanonical(): bool
    {
        return in_array($this->name, self::canonicalNames(), true);
    }

    /**
     * Menus that are accessible by this role
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_role')
                    ->withPivot('can_view')
                    ->withTimestamps();
    }
}
