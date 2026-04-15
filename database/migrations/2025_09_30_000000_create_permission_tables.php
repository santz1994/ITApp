<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePermissionTables extends Migration
{
    public function up()
    {
        // Ensure base tables exist with reasonable schema if not already present.
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        // Determine whether existing id columns are bigint (Spatie default) or int (Entrust).
        // Avoid running MySQL-specific queries like SHOW COLUMNS when using SQLite (tests).
        $permissionIdIsBig = true;
        $roleIdIsBig = true;
        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                $permissionIdIsBig = false;
                $roleIdIsBig = false;
                if (Schema::hasTable('permissions')) {
                    $col = DB::select("SHOW COLUMNS FROM permissions WHERE Field = 'id'");
                    if (! empty($col) && isset($col[0]->Type) && stripos($col[0]->Type, 'bigint') !== false) {
                        $permissionIdIsBig = true;
                    }
                }
                if (Schema::hasTable('roles')) {
                    $col = DB::select("SHOW COLUMNS FROM roles WHERE Field = 'id'");
                    if (! empty($col) && isset($col[0]->Type) && stripos($col[0]->Type, 'bigint') !== false) {
                        $roleIdIsBig = true;
                    }
                }
            }
        } catch (\Exception $e) {
            // If detection fails, default to bigint which is compatible with SQLite and Spatie defaults.
            $permissionIdIsBig = true;
            $roleIdIsBig = true;
        }

        // model_has_permissions
        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) use ($permissionIdIsBig) {
                if ($permissionIdIsBig) {
                    $table->unsignedBigInteger('permission_id');
                } else {
                    $table->unsignedInteger('permission_id');
                }
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');

                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
                try {
                    if (Schema::hasTable('permissions')) {
                        $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                    }
                } catch (\Exception $e) {
                    // ignore FK creation errors (type mismatch etc.)
                }
            });
        }

        // model_has_roles
        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) use ($roleIdIsBig) {
                if ($roleIdIsBig) {
                    $table->unsignedBigInteger('role_id');
                } else {
                    $table->unsignedInteger('role_id');
                }
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');

                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
                try {
                    if (Schema::hasTable('roles')) {
                        $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                    }
                } catch (\Exception $e) {
                    // ignore FK creation errors
                }
            });
        }

        // role_has_permissions
        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) use ($permissionIdIsBig, $roleIdIsBig) {
                if ($permissionIdIsBig) {
                    $table->unsignedBigInteger('permission_id');
                } else {
                    $table->unsignedInteger('permission_id');
                }
                if ($roleIdIsBig) {
                    $table->unsignedBigInteger('role_id');
                } else {
                    $table->unsignedInteger('role_id');
                }
                try {
                    if (Schema::hasTable('permissions')) {
                        $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                    }
                    if (Schema::hasTable('roles')) {
                        $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                    }
                } catch (\Exception $e) {
                    // ignore
                }

                $table->primary(['permission_id', 'role_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
}
