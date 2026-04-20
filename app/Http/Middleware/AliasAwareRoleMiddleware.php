<?php

namespace App\Http\Middleware;

use App\Role;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AliasAwareRoleMiddleware
{
    /**
     * Handle role-based authorization with canonical-role alias support.
     *
     * This prevents runtime failures when legacy role names (admin/super-admin/
     * management/hr) are still used in old middleware strings.
     */
    public function handle($request, Closure $next, $role, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $user = $authGuard->user();
        $requestedRoles = $this->toRoleList($role);

        if (!$user || !method_exists($user, 'getRoleNames')) {
            throw UnauthorizedException::forRoles($requestedRoles);
        }

        $allowedCanonicalRoles = $this->expandCanonicalRoles($requestedRoles);
        $userCanonicalRoles = collect($user->getRoleNames()->all())
            ->map(static function ($roleName): string {
                return Role::normalizeName((string) $roleName);
            })
            ->filter(static function (string $roleName): bool {
                return $roleName !== '';
            })
            ->unique()
            ->values();

        if ($userCanonicalRoles->intersect($allowedCanonicalRoles)->isEmpty()) {
            throw UnauthorizedException::forRoles($requestedRoles);
        }

        return $next($request);
    }

    /**
     * @param mixed $roles
     * @return array<int, string>
     */
    private function toRoleList($roles): array
    {
        $items = is_array($roles) ? $roles : explode('|', (string) $roles);

        $normalized = array_map(static function ($roleName): string {
            return strtolower(trim((string) $roleName));
        }, $items);

        return array_values(array_unique(array_filter($normalized, static function (string $roleName): bool {
            return $roleName !== '';
        })));
    }

    /**
     * @param array<int, string> $roleNames
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function expandCanonicalRoles(array $roleNames): Collection
    {
        $expanded = [];

        foreach ($roleNames as $roleName) {
            foreach (Role::equivalentNames($roleName) as $equivalent) {
                $canonical = Role::normalizeName((string) $equivalent);
                if ($canonical !== '') {
                    $expanded[] = $canonical;
                }
            }
        }

        return collect($expanded)->unique()->values();
    }
}
