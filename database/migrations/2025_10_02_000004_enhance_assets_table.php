<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            // Enhanced fields for better asset management
            $table->string('qr_code', 100)->unique()->nullable()->after('asset_tag');
            $table->integer('status_id')->unsigned()->default(1)->after('movement_id');
            $table->integer('assigned_to')->unsigned()->nullable()->after('status_id');
            $table->text('notes')->nullable()->after('assigned_to');
            $table->string('ip_address', 45)->nullable()->after('notes'); // Existing in another migration
            $table->string('mac_address', 17)->nullable()->after('ip_address'); // Existing in another migration
            
            // Foreign keys
            $table->foreign('status_id')->references('id')->on('statuses');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'qr_code', 'status_id', 'assigned_to', 'notes'
            ]);
        });
    }
};