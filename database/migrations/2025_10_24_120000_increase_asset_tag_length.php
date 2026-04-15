<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Increase `asset_tag` length from 10 to 64 so long tags (from imports/backups)
     * do not collide due to truncation of the unique index.
     */
    public function up(): void
    {
        // Use raw SQL to avoid depending on doctrine/dbal for the `change()` method.
        // This preserves existing unique/index definitions while altering the column size.
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE `assets` MODIFY `asset_tag` VARCHAR(64) NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite requires a table rebuild; keep a safe approach by recreating table
            // only when necessary in local/dev. For tests using sqlite this may be fine.
            // We'll skip automatic rewriting here to avoid complexity; developers can
            // recreate the test DB or run the appropriate sqlite migration manually.
            // Log a friendly notice instead.
            echo "⚠️  SQLite detected: please recreate test database or manually alter `asset_tag` length to 64 chars.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE `assets` MODIFY `asset_tag` VARCHAR(10) NOT NULL");
        } else {
            echo "⚠️  SQLite detected: manual rollback may be required (asset_tag length).\n";
        }
    }
};
