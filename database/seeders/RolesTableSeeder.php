<?php
namespace Database\Seeders;

use App\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hasGuardName = \Illuminate\Support\Facades\Schema::hasColumn('roles', 'guard_name');
        $hasAccessLevel = \Illuminate\Support\Facades\Schema::hasColumn('roles', 'access_level');
        $roleLevels = Role::projectRoleLevels();

        $definitions = [
            'guest' => [
                'display_name' => 'Guest',
                'description' => 'Unauthenticated role for LV 0 public access context.',
            ],
            'user' => [
                'display_name' => 'User',
                'description' => 'Default authenticated user role with basic module access.',
            ],
            'receptionist' => [
                'display_name' => 'Receptionist',
                'description' => 'Receptionist role for meeting room management and support operations.',
            ],
            'human-resources' => [
                'display_name' => 'Human Resources',
                'description' => 'Human resources role for user management and profile operations.',
            ],
            'director' => [
                'display_name' => 'Director',
                'description' => 'Director role for supervision-level access and approvals.',
            ],
            'administrator' => [
                'display_name' => 'Administrator',
                'description' => 'Administrator role (IT Support Staff) for platform operations.',
            ],
            'developer' => [
                'display_name' => 'Developer',
                'description' => 'Developer role (IT Programmer Staff) with full platform capabilities.',
            ],
        ];

        foreach (Role::canonicalNames() as $roleName) {
            $definition = $definitions[$roleName] ?? [
                'display_name' => ucwords(str_replace(['-', '_'], ' ', $roleName)),
                'description' => 'Canonical role: ' . $roleName,
            ];

            if ($hasGuardName) {
                $payload = [
                    'display_name' => $definition['display_name'],
                    'description' => $definition['description'],
                ];

                if ($hasAccessLevel) {
                    $payload['access_level'] = $roleLevels[$roleName] ?? 1;
                }

                Role::updateOrCreate(
                    [
                        'name' => $roleName,
                        'guard_name' => config('auth.defaults.guard', 'web'),
                    ],
                    $payload
                );
                continue;
            }

            $payload = [
                'display_name' => $definition['display_name'],
                'description' => $definition['description'],
                'updated_at' => now(),
                'created_at' => now(),
            ];

            if ($hasAccessLevel) {
                $payload['access_level'] = $roleLevels[$roleName] ?? 1;
            }

            \Illuminate\Support\Facades\DB::table('roles')->updateOrInsert(['name' => $roleName], $payload);
        }
    }
}
