<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTicketHistoryColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use Schema::hasColumn checks to avoid dependency on Doctrine DBAL
        if (!Schema::hasTable('ticket_history')) {
            // If table doesn't exist, create a minimal ticket_history table expected by application
            Schema::create('ticket_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->string('field_changed')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->dateTime('changed_at')->nullable();
                $table->string('change_type')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                // Foreign keys are optional; wrap in try/catch when run manually if desired
                try {
                    $table->foreign('ticket_id')->references('id')->on('tickets');
                    $table->foreign('changed_by_user_id')->references('id')->on('users');
                } catch (\Exception $e) {
                    // ignore foreign key creation failures in environments without permissions
                }
            });
            return;
        }

        Schema::table('ticket_history', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_history', 'field_changed')) {
                $table->string('field_changed')->nullable()->after('ticket_id');
            }
            if (!Schema::hasColumn('ticket_history', 'old_value')) {
                $table->text('old_value')->nullable()->after('field_changed');
            }
            if (!Schema::hasColumn('ticket_history', 'new_value')) {
                $table->text('new_value')->nullable()->after('old_value');
            }
            if (!Schema::hasColumn('ticket_history', 'changed_by_user_id')) {
                $table->unsignedBigInteger('changed_by_user_id')->nullable()->after('new_value');
            }
            if (!Schema::hasColumn('ticket_history', 'changed_at')) {
                $table->dateTime('changed_at')->nullable()->after('changed_by_user_id');
            }
            if (!Schema::hasColumn('ticket_history', 'change_type')) {
                $table->string('change_type')->nullable()->after('changed_at');
            }
            if (!Schema::hasColumn('ticket_history', 'reason')) {
                $table->text('reason')->nullable()->after('change_type');
            }
        });

        // Attempt to migrate legacy columns if they exist
        try {
            if (Schema::hasColumn('ticket_history', 'user_id') && !Schema::hasColumn('ticket_history', 'changed_by_user_id')) {
                \Illuminate\Support\Facades\DB::statement('UPDATE ticket_history SET changed_by_user_id = user_id WHERE changed_by_user_id IS NULL');
            }

            if (Schema::hasColumn('ticket_history', 'event_type') && !Schema::hasColumn('ticket_history', 'change_type')) {
                \Illuminate\Support\Facades\DB::statement('UPDATE ticket_history SET change_type = event_type WHERE change_type IS NULL');
            }
        } catch (\Exception $e) {
            // non-fatal
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_history', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_history', 'reason')) {
                $table->dropColumn('reason');
            }
            if (Schema::hasColumn('ticket_history', 'change_type')) {
                $table->dropColumn('change_type');
            }
            if (Schema::hasColumn('ticket_history', 'changed_at')) {
                $table->dropColumn('changed_at');
            }
            if (Schema::hasColumn('ticket_history', 'changed_by_user_id')) {
                $table->dropColumn('changed_by_user_id');
            }
            if (Schema::hasColumn('ticket_history', 'new_value')) {
                $table->dropColumn('new_value');
            }
            if (Schema::hasColumn('ticket_history', 'old_value')) {
                $table->dropColumn('old_value');
            }
            if (Schema::hasColumn('ticket_history', 'field_changed')) {
                $table->dropColumn('field_changed');
            }
        });
    }
}
