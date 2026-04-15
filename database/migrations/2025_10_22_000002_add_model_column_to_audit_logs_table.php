<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        if (!Schema::hasColumn('audit_logs', 'model')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('model', 100)->nullable()->after('action')->comment('Human-friendly model name (e.g., Ticket)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'model')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('model');
            });
        }
    }
};
