<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('asset_requests') && ! Schema::hasColumn('asset_requests', 'priority')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                // Add priority as enum to match application-level values. Make it nullable to avoid breaking existing rows.
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable()->after('justification');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('asset_requests') && Schema::hasColumn('asset_requests', 'priority')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }
};
