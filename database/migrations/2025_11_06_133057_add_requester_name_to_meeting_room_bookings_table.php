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
        Schema::table('meeting_room_bookings', function (Blueprint $table) {
            $table->string('requester_name')->nullable()->after('user_id')
                  ->comment('Name of person requesting the booking (can be different from user account)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_room_bookings', function (Blueprint $table) {
            $table->dropColumn('requester_name');
        });
    }
};
