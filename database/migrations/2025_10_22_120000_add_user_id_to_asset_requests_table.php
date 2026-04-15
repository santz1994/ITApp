<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('asset_requests')) {
            if (!Schema::hasColumn('asset_requests', 'user_id')) {
                Schema::table('asset_requests', function (Blueprint $table) {
                    $table->integer('user_id')->unsigned()->nullable()->after('requested_by');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('asset_requests') && Schema::hasColumn('asset_requests', 'user_id')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
