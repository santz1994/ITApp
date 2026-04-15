<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\User;
use Spatie\Permission\Models\Role;

class TestUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // Clear users table (delete, not truncate, to avoid FK errors)
        DB::table('users')->delete();

        // Helper to create or get user and return the model instance
        $createUser = function ($name, $email, $password) use ($now) {
            // Use direct DB operations to ensure required columns like api_token
            // are always set regardless of Eloquent mass-assignment rules.
            $existing = DB::table('users')->where('email', $email)->first();
            $nowStr = $now->toDateTimeString();
            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update([
                    'name' => $name,
                    'password' => bcrypt($password),
                    'api_token' => Str::random(60),
                    'updated_at' => $nowStr,
                ]);
                return User::find($existing->id);
            }

            $id = DB::table('users')->insertGetId([
                'email' => $email,
                'name' => $name,
                'password' => bcrypt($password),
                'api_token' => Str::random(60),
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
            ]);
            return User::find($id);
        };

        // Create deterministic users used by tests
        $daniel = $createUser('Daniel', 'daniel@quty.co.id', '123456');
        $idol = $createUser('Idol', 'idol@quty.co.id', '123456');
        $ridwan = $createUser('Ridwan', 'ridwan@quty.co.id', '123456');
        $management = $createUser('Management', 'management@quty.co.id', 'management');
        $superAdmin = $createUser('Super Admin', 'superadmin@quty.co.id', 'superadmin');
        $admin = $createUser('Admin', 'admin@quty.co.id', 'admin');
        $user = $createUser('User', 'user@quty.co.id', 'user');

        // Legacy-named users expected by older tests (exact display names)
        $legacySuperAdmin = $createUser('Super Admin User', 'superadmin.user@test', 'password');
        $legacyAdmin = $createUser('Admin User', 'admin.user@test', 'password');
        $legacyUser = $createUser('User User', 'user.user@test', 'password');

        // Assign roles using Spatie assignRole (idempotent and respects guard_name)
        $superRole = Role::where('name', 'super-admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managementRole = Role::where('name', 'management')->first();
        $userRole = Role::where('name', 'user')->first();

        if ($superRole) {
            $daniel->assignRole($superRole->name);
            $idol->assignRole($superRole->name);
            $ridwan->assignRole($superRole->name);
            $superAdmin->assignRole($superRole->name);
            $legacySuperAdmin->assignRole($superRole->name);
        }
        if ($managementRole) {
            $management->assignRole($managementRole->name);
        }
        if ($adminRole) {
            $admin->assignRole($adminRole->name);
            $legacyAdmin->assignRole($adminRole->name);
        }
        if ($userRole) {
            $user->assignRole($userRole->name);
            $legacyUser->assignRole($userRole->name);
        }
    }
}
