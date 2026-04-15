<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * HIGH PRIORITY FIX: Change user_id foreign keys from RESTRICT to SET NULL.
     * This allows user deletion without blocking, while preserving historical records.
     * 
     * Tables affected:
     * 1. assets.assigned_to → users (allow unassignment when user deleted)
     * 2. tickets.user_id → users (preserve ticket, mark creator as NULL)
     * 3. movements.user_id → users (preserve movement history)
     * 
     * Why SET NULL instead of RESTRICT:
     * - Users may leave the company
     * - Historical data must be preserved
     * - Blocking user deletion creates operational issues
     * - NULL clearly indicates "user no longer exists"
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // For SQLite (testing) we cannot drop or modify foreign keys in-place.
        // Make columns nullable where needed, but skip dropping/adding FKs.
        if ($driver === 'sqlite') {
            // Make tickets.user_id and movements.user_id nullable; skip FK changes
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedInteger('user_id')->nullable()->change();
            });

            Schema::table('movements', function (Blueprint $table) {
                $table->unsignedInteger('user_id')->nullable()->change();
            });

            // assets.assigned_to is already nullable in most schemas; skip FK changes
            return;
        }

        // 1. Fix assets.assigned_to constraint (already nullable)
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);

            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });

        // 2. Fix tickets.user_id - make nullable and add FK with SET NULL
        Schema::table('tickets', function (Blueprint $table) {
            // Make column nullable
            $table->unsignedInteger('user_id')->nullable()->change();
        });

        // Drop existing foreign key if exists, then add new one with SET NULL
        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }

        Schema::table('tickets', function (Blueprint $table) {
            // Add FK with SET NULL
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });

        // Note: tickets.assigned_to already has SET NULL - no change needed

        // 3. Fix movements.user_id constraint - drop then recreate with SET NULL
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('movements', function (Blueprint $table) {
            // Make column nullable
            $table->unsignedInteger('user_id')->nullable()->change();

            // Add FK with SET NULL
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Revert back to RESTRICT constraints (original state).
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // On SQLite in testing, foreign keys aren't modified -- make columns nullable -> revert not possible
            return;
        }

        // 1. Revert assets.assigned_to
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);

            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });

        // 2. Revert tickets.user_id
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });

        // 3. Revert movements.user_id
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }
};
