<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('assets') && !Schema::hasColumn('assets', 'name')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->string('name')->nullable()->after('asset_tag');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'name')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
