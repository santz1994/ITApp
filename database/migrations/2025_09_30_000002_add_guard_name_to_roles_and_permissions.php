<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuardNameToRolesAndPermissions extends Migration
{
    public function up()
    {
        if (Schema::hasTable('roles') && ! Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('guard_name')->default(config('auth.defaults.guard', 'web'))->after('name');
            });
        }

        if (Schema::hasTable('permissions') && ! Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('guard_name')->default(config('auth.defaults.guard', 'web'))->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }
    }
}
