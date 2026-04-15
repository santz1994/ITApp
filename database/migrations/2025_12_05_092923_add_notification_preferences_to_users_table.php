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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_email')->default(true)->after('email');
            $table->boolean('notify_ticket_created')->default(true)->after('notify_email');
            $table->boolean('notify_ticket_assigned')->default(true)->after('notify_ticket_created');
            $table->boolean('notify_ticket_updated')->default(true)->after('notify_ticket_assigned');
            $table->boolean('notify_meeting_approved')->default(true)->after('notify_ticket_updated');
            $table->boolean('notify_meeting_rejected')->default(true)->after('notify_meeting_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_email',
                'notify_ticket_created',
                'notify_ticket_assigned',
                'notify_ticket_updated',
                'notify_meeting_approved',
                'notify_meeting_rejected'
            ]);
        });
    }
};
