<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('daily_activities', 'duration_minutes')) {
            Schema::table('daily_activities', function (Blueprint $table) {
                // nullable integer to store minutes spent on an activity
                $table->integer('duration_minutes')->nullable()->after('type');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('daily_activities', 'duration_minutes')) {
            Schema::table('daily_activities', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }
};
