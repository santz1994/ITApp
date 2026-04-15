<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateEntrustRoleUserToSpatieModelHasRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * Copies rows from `role_user` into `model_has_roles` with model_type = App\\User
     * If `model_has_roles` already exists, this will only insert non-duplicate rows.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('role_user')) {
            // Nothing to migrate
            return;
        }

        if (!Schema::hasTable('model_has_roles')) {
            // If Spatie tables are not present, bail — expect user to run spatie migration first
            return;
        }

        $rows = DB::table('role_user')->get();

        foreach ($rows as $row) {
            $exists = DB::table('model_has_roles')
                ->where('role_id', $row->role_id)
                ->where('model_id', $row->user_id)
                ->where('model_type', 'App\\User')
                ->exists();

            if (!$exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $row->role_id,
                    'model_type' => 'App\\User',
                    'model_id' => $row->user_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * This will not delete spatie rows, but could copy them back to role_user if desired.
     *
     * @return void
     */
    public function down()
    {
        // Do not automatically remove Spatie rows to avoid data loss.
    }
}
