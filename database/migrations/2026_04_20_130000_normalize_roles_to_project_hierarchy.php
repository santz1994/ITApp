<?php

use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasColumn('roles', 'name')) {
            return;
        }

        DB::transaction(function (): void {
            $definitions = $this->canonicalRoleDefinitions();
            $canonicalNames = array_keys($definitions);
            $hasAccessLevel = Schema::hasColumn('roles', 'access_level');

            $this->ensureCanonicalRolesExist($definitions);

            $canonicalIds = DB::table('roles')
                ->whereIn('name', $canonicalNames)
                ->orderBy('id')
                ->pluck('id', 'name')
                ->toArray();

            $selectColumns = ['id', 'name'];
            if ($hasAccessLevel) {
                $selectColumns[] = 'access_level';
            }

            $roles = DB::table('roles')
                ->select($selectColumns)
                ->orderBy('id')
                ->get();

            foreach ($roles as $role) {
                $sourceRoleId = (int) $role->id;
                $normalizedName = Role::normalizeName((string) $role->name);
                $accessLevel = $hasAccessLevel ? (int) ($role->access_level ?? 1) : 1;

                $targetName = in_array($normalizedName, $canonicalNames, true)
                    ? $normalizedName
                    : $this->fallbackRoleByAccessLevel($accessLevel);

                $targetRoleId = (int) ($canonicalIds[$targetName] ?? 0);

                if ($targetRoleId <= 0 || $targetRoleId === $sourceRoleId) {
                    continue;
                }

                $this->mergeRoleIntoTarget($sourceRoleId, $targetRoleId);
                DB::table('roles')->where('id', $sourceRoleId)->delete();
            }

            $leftoverRoleIds = DB::table('roles')
                ->whereNotIn('name', $canonicalNames)
                ->pluck('id')
                ->all();

            $fallbackRoleId = (int) ($canonicalIds['user'] ?? 0);
            foreach ($leftoverRoleIds as $leftoverRoleId) {
                $sourceRoleId = (int) $leftoverRoleId;

                if ($fallbackRoleId > 0 && $sourceRoleId !== $fallbackRoleId) {
                    $this->mergeRoleIntoTarget($sourceRoleId, $fallbackRoleId);
                }

                DB::table('roles')->where('id', $sourceRoleId)->delete();
            }

            $this->applyCanonicalRoleAttributes($definitions);
        });

        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $exception) {
            // Ignore permission cache reset failures during migration bootstrap.
        }
    }

    public function down(): void
    {
        // This migration performs data normalization and is intentionally non-reversible.
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    private function canonicalRoleDefinitions(): array
    {
        return [
            'guest' => [
                'display_name' => 'Guest',
                'description' => 'Unauthenticated users with limited public access only.',
                'access_level' => 0,
            ],
            'user' => [
                'display_name' => 'User',
                'description' => 'Default authenticated user role with basic feature access.',
                'access_level' => 1,
            ],
            'receptionist' => [
                'display_name' => 'Receptionist',
                'description' => 'Receptionist role for meeting room and support workflows.',
                'access_level' => 2,
            ],
            'human-resources' => [
                'display_name' => 'Human Resources',
                'description' => 'Human resources role for user management and profile workflows.',
                'access_level' => 3,
            ],
            'director' => [
                'display_name' => 'Director',
                'description' => 'Director role for supervision, approvals, and KPI visibility.',
                'access_level' => 8,
            ],
            'administrator' => [
                'display_name' => 'Administrator',
                'description' => 'Administrator role for IT support operations and settings.',
                'access_level' => 9,
            ],
            'developer' => [
                'display_name' => 'Developer',
                'description' => 'Developer role for full platform functionality and engineering tooling.',
                'access_level' => 10,
            ],
        ];
    }

    /**
     * @param array<string, array<string, int|string>> $definitions
     */
    private function ensureCanonicalRolesExist(array $definitions): void
    {
        $hasGuardName = Schema::hasColumn('roles', 'guard_name');
        $hasDisplayName = Schema::hasColumn('roles', 'display_name');
        $hasDescription = Schema::hasColumn('roles', 'description');
        $hasAccessLevel = Schema::hasColumn('roles', 'access_level');
        $guardName = config('auth.defaults.guard', 'web');

        foreach ($definitions as $roleName => $definition) {
            $exists = DB::table('roles')->where('name', $roleName)->exists();
            if ($exists) {
                continue;
            }

            $insert = [
                'name' => $roleName,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasGuardName) {
                $insert['guard_name'] = $guardName;
            }

            if ($hasDisplayName) {
                $insert['display_name'] = (string) $definition['display_name'];
            }

            if ($hasDescription) {
                $insert['description'] = (string) $definition['description'];
            }

            if ($hasAccessLevel) {
                $insert['access_level'] = (int) $definition['access_level'];
            }

            DB::table('roles')->insert($insert);
        }
    }

    /**
     * @param array<string, array<string, int|string>> $definitions
     */
    private function applyCanonicalRoleAttributes(array $definitions): void
    {
        $hasDisplayName = Schema::hasColumn('roles', 'display_name');
        $hasDescription = Schema::hasColumn('roles', 'description');
        $hasAccessLevel = Schema::hasColumn('roles', 'access_level');

        foreach ($definitions as $roleName => $definition) {
            $update = ['updated_at' => now()];

            if ($hasDisplayName) {
                $update['display_name'] = (string) $definition['display_name'];
            }

            if ($hasDescription) {
                $update['description'] = (string) $definition['description'];
            }

            if ($hasAccessLevel) {
                $update['access_level'] = (int) $definition['access_level'];
            }

            DB::table('roles')->where('name', $roleName)->update($update);
        }
    }

    private function fallbackRoleByAccessLevel(int $accessLevel): string
    {
        if ($accessLevel >= 10) {
            return 'developer';
        }

        if ($accessLevel >= 9) {
            return 'administrator';
        }

        if ($accessLevel >= 8) {
            return 'director';
        }

        if ($accessLevel >= 3) {
            return 'human-resources';
        }

        if ($accessLevel >= 2) {
            return 'receptionist';
        }

        if ($accessLevel >= 1) {
            return 'user';
        }

        return 'guest';
    }

    private function mergeRoleIntoTarget(int $sourceRoleId, int $targetRoleId): void
    {
        if ($sourceRoleId === $targetRoleId) {
            return;
        }

        $this->mergeModelHasRoles($sourceRoleId, $targetRoleId);
        $this->mergeRoleHasPermissions($sourceRoleId, $targetRoleId);
        $this->mergeLegacyRoleUser($sourceRoleId, $targetRoleId);
        $this->mergeMenuRole($sourceRoleId, $targetRoleId);
        $this->mergeUsersPrimaryRole($sourceRoleId, $targetRoleId);
    }

    private function mergeModelHasRoles(int $sourceRoleId, int $targetRoleId): void
    {
        if (!Schema::hasTable('model_has_roles')) {
            return;
        }

        $rows = DB::table('model_has_roles')->where('role_id', $sourceRoleId)->get();

        foreach ($rows as $row) {
            $exists = DB::table('model_has_roles')
                ->where('role_id', $targetRoleId)
                ->where('model_type', $row->model_type)
                ->where('model_id', $row->model_id)
                ->exists();

            if (!$exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $targetRoleId,
                    'model_type' => $row->model_type,
                    'model_id' => $row->model_id,
                ]);
            }
        }

        DB::table('model_has_roles')->where('role_id', $sourceRoleId)->delete();
    }

    private function mergeRoleHasPermissions(int $sourceRoleId, int $targetRoleId): void
    {
        if (!Schema::hasTable('role_has_permissions')) {
            return;
        }

        $rows = DB::table('role_has_permissions')->where('role_id', $sourceRoleId)->get();

        foreach ($rows as $row) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $targetRoleId)
                ->where('permission_id', $row->permission_id)
                ->exists();

            if (!$exists) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $targetRoleId,
                    'permission_id' => $row->permission_id,
                ]);
            }
        }

        DB::table('role_has_permissions')->where('role_id', $sourceRoleId)->delete();
    }

    private function mergeLegacyRoleUser(int $sourceRoleId, int $targetRoleId): void
    {
        if (!Schema::hasTable('role_user')) {
            return;
        }

        $rows = DB::table('role_user')->where('role_id', $sourceRoleId)->get();

        foreach ($rows as $row) {
            $exists = DB::table('role_user')
                ->where('role_id', $targetRoleId)
                ->where('user_id', $row->user_id)
                ->exists();

            if (!$exists) {
                DB::table('role_user')->insert([
                    'role_id' => $targetRoleId,
                    'user_id' => $row->user_id,
                ]);
            }
        }

        DB::table('role_user')->where('role_id', $sourceRoleId)->delete();
    }

    private function mergeMenuRole(int $sourceRoleId, int $targetRoleId): void
    {
        if (!Schema::hasTable('menu_role')) {
            return;
        }

        $hasCanView = Schema::hasColumn('menu_role', 'can_view');
        $hasCreatedAt = Schema::hasColumn('menu_role', 'created_at');
        $hasUpdatedAt = Schema::hasColumn('menu_role', 'updated_at');

        $rows = DB::table('menu_role')->where('role_id', $sourceRoleId)->get();

        foreach ($rows as $row) {
            $existing = DB::table('menu_role')
                ->where('menu_id', $row->menu_id)
                ->where('role_id', $targetRoleId)
                ->first();

            if (!$existing) {
                $insert = [
                    'menu_id' => $row->menu_id,
                    'role_id' => $targetRoleId,
                ];

                if ($hasCanView) {
                    $insert['can_view'] = (bool) ($row->can_view ?? true);
                }

                if ($hasCreatedAt) {
                    $insert['created_at'] = $row->created_at ?? now();
                }

                if ($hasUpdatedAt) {
                    $insert['updated_at'] = $row->updated_at ?? now();
                }

                DB::table('menu_role')->insert($insert);
                continue;
            }

            if ($hasCanView && !(bool) ($existing->can_view ?? false) && (bool) ($row->can_view ?? false)) {
                DB::table('menu_role')
                    ->where('menu_id', $row->menu_id)
                    ->where('role_id', $targetRoleId)
                    ->update(['can_view' => true]);
            }
        }

        DB::table('menu_role')->where('role_id', $sourceRoleId)->delete();
    }

    private function mergeUsersPrimaryRole(int $sourceRoleId, int $targetRoleId): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role_id')) {
            return;
        }

        DB::table('users')
            ->where('role_id', $sourceRoleId)
            ->update(['role_id' => $targetRoleId]);
    }
};
