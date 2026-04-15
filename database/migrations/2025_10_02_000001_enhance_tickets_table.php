<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Add ticket unique code
            $table->string('ticket_code', 50)->unique()->after('id');
            
            // Add assignment fields
            $table->integer('assigned_to')->unsigned()->nullable()->after('user_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->enum('assignment_type', ['auto', 'manual', 'super_admin'])->default('auto')->after('assigned_at');
            
            // Add SLA fields
            $table->timestamp('sla_due')->nullable()->after('assignment_type');
            $table->timestamp('first_response_at')->nullable()->after('sla_due');
            $table->timestamp('resolved_at')->nullable()->after('first_response_at');
            
            // Add asset relationship
            $table->integer('asset_id')->unsigned()->nullable()->after('location_id');
            
            // Add foreign keys
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['asset_id']);
            $table->dropColumn([
                'ticket_code', 'assigned_to', 'assigned_at', 'assignment_type',
                'sla_due', 'first_response_at', 'resolved_at', 'asset_id'
            ]);
        });
    }
};