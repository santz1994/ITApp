<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_history', function (Blueprint $table) {
            // Add changed_at column if it doesn't exist
            if (! Schema::hasColumn('ticket_history', 'changed_at')) {
                $table->timestamp('changed_at')->nullable()->after('changed_by_user_id')->index();
            }
            
            // Add change_type if missing
            if (! Schema::hasColumn('ticket_history', 'change_type')) {
                $table->string('change_type')->nullable()->index();
            }
            
            // Add reason if missing
            if (! Schema::hasColumn('ticket_history', 'reason')) {
                $table->text('reason')->nullable();
            }
        });
        
        // Now that columns exist, populate them with data
        if (Schema::hasColumn('ticket_history', 'changed_at')) {
            DB::statement('UPDATE `ticket_history` SET `changed_at` = `created_at` WHERE `changed_at` IS NULL');
        }
        
        if (Schema::hasColumn('ticket_history', 'change_type') && Schema::hasColumn('ticket_history', 'event_type')) {
            DB::statement('UPDATE `ticket_history` SET `change_type` = `event_type` WHERE `change_type` IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_history', function (Blueprint $table) {
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
};
