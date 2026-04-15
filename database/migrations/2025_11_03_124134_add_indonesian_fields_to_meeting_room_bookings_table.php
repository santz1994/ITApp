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
            // Indonesian form fields
            $table->string('department')->nullable()->after('room_name'); // Bagian/Departemen
            $table->string('requester_position')->nullable()->after('user_id'); // Jabatan Pemohon
            $table->text('meeting_description')->nullable()->after('purpose'); // Deskripsi/Keterangan Rapat
            $table->text('meeting_needs')->nullable()->after('meeting_description'); // Keperluan Rapat
            
            // Manager approval (Mengetahui)
            $table->unsignedInteger('manager_id')->nullable()->after('approved_by'); // Manager who acknowledged
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('manager_approved_at')->nullable()->after('approved_at'); // Manager acknowledgment timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_room_bookings', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['manager_id']);
            
            // Drop columns
            $table->dropColumn([
                'department',
                'requester_position',
                'meeting_description',
                'meeting_needs',
                'manager_id',
                'manager_approved_at'
            ]);
        });
    }
};
