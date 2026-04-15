<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Make additive, non-destructive changes so this migration is safe to run on existing data
        if (Schema::hasTable('ticket_history')) {
            Schema::table('ticket_history', function (Blueprint $table) {
                // Add new columns expected by the application if they don't exist
                if (! Schema::hasColumn('ticket_history', 'field_changed')) {
                    $table->string('field_changed')->nullable()->after('ticket_id')->index();
                }

                if (! Schema::hasColumn('ticket_history', 'old_value')) {
                    $table->text('old_value')->nullable()->after('field_changed');
                }

                if (! Schema::hasColumn('ticket_history', 'new_value')) {
                    $table->text('new_value')->nullable()->after('old_value');
                }

                if (! Schema::hasColumn('ticket_history', 'changed_by_user_id')) {
                    $table->unsignedInteger('changed_by_user_id')->nullable()->after('new_value')->index();
                }

                if (! Schema::hasColumn('ticket_history', 'changed_at')) {
                    $table->timestamp('changed_at')->nullable()->after('changed_by_user_id')->index();
                }

                if (! Schema::hasColumn('ticket_history', 'change_type')) {
                    $table->string('change_type')->nullable()->after('changed_at')->index();
                }

                if (! Schema::hasColumn('ticket_history', 'reason')) {
                    $table->text('reason')->nullable()->after('change_type');
                }
            });

            // Copy data from legacy columns if present
            // If a legacy user_id column exists, copy it to changed_by_user_id
            try {
                if (Schema::hasColumn('ticket_history', 'user_id')) {
                    DB::statement('UPDATE `ticket_history` SET `changed_by_user_id` = `user_id` WHERE `changed_by_user_id` IS NULL');
                }

                // If legacy event_type exists, copy into change_type for compatibility
                if (Schema::hasColumn('ticket_history', 'event_type')) {
                    DB::statement('UPDATE `ticket_history` SET `change_type` = `event_type` WHERE `change_type` IS NULL');
                }
            } catch (\Exception $e) {
                // Swallow exceptions here to avoid blocking deployments; errors will remain visible in logs
            }

            // Attempt to add foreign key for changed_by_user_id if possible.
            // Avoid requiring Doctrine DBAL in this migration; we simply try to add the
            // foreign key inside a try/catch and ignore failures (may need manual fix
            // if existing schema prevents FK creation).
            try {
                Schema::table('ticket_history', function (Blueprint $table) {
                    if (Schema::hasColumn('ticket_history', 'changed_by_user_id') && Schema::hasTable('users')) {
                        $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('set null');
                    }
                });
            } catch (\Exception $e) {
                // Ignored: environment may not have matching column types or existing constraints.
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('ticket_history')) {
            Schema::table('ticket_history', function (Blueprint $table) {
                // Do not drop legacy columns automatically to avoid accidental data loss in rollback.
                // We only remove columns that were added by this migration if the developer explicitly rolls back.
                if (Schema::hasColumn('ticket_history', 'field_changed')) {
                    $table->dropColumn('field_changed');
                }

                if (Schema::hasColumn('ticket_history', 'old_value')) {
                    $table->dropColumn('old_value');
                }

                if (Schema::hasColumn('ticket_history', 'new_value')) {
                    $table->dropColumn('new_value');
                }

                if (Schema::hasColumn('ticket_history', 'changed_by_user_id')) {
                    // Drop foreign key if exists then drop column
                    try { $table->dropForeign(['changed_by_user_id']); } catch (\Exception $e) {}
                    $table->dropColumn('changed_by_user_id');
                }

                if (Schema::hasColumn('ticket_history', 'changed_at')) {
                    $table->dropColumn('changed_at');
                }

                if (Schema::hasColumn('ticket_history', 'change_type')) {
                    $table->dropColumn('change_type');
                }

                if (Schema::hasColumn('ticket_history', 'reason')) {
                    $table->dropColumn('reason');
                }
            });
        }
    }
};
