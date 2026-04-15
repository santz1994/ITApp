<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SyncLegacyAndSpatieRoles extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // First ensure all roles have guard_name
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'guard_name')) {
            DB::table('roles')
                ->whereNull('guard_name')
                ->update(['guard_name' => 'web']);
        }

        // Make sure both role tables exist before attempting sync
        if (!Schema::hasTable('model_has_roles') || !Schema::hasTable('role_user')) {
            return;
        }

        // 1. First sync from model_has_roles to role_user
        $spatieRoles = DB::table('model_has_roles')
            ->where('model_type', 'App\\User')
            ->get();

        foreach ($spatieRoles as $role) {
            // Check if entry exists in legacy table
            $existingEntry = DB::table('role_user')
                ->where('user_id', $role->model_id)
                ->where('role_id', $role->role_id)
                ->first();
            
            // If not, add it
            if (!$existingEntry) {
                DB::table('role_user')->insert([
                    'user_id' => $role->model_id,
                    'role_id' => $role->role_id
                ]);
            }
        }

        // 2. Then sync from role_user to model_has_roles
        $legacyRoles = DB::table('role_user')->get();

        foreach ($legacyRoles as $role) {
            // Check if entry exists in Spatie table
            $existingEntry = DB::table('model_has_roles')
                ->where('model_id', $role->user_id)
                ->where('role_id', $role->role_id)
                ->where('model_type', 'App\\User')
                ->first();
            
            // If not, add it
            if (!$existingEntry) {
                DB::table('model_has_roles')->insert([
                    'model_id' => $role->user_id,
                    'role_id' => $role->role_id,
                    'model_type' => 'App\\User'
                ]);
            }
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        // This is a data sync operation, no need to undo it
    }
}