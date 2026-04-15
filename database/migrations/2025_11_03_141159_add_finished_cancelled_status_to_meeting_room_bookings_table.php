<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we're using SQLite (for testing) or MySQL (for production)
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite approach: Create new table, copy data, rename
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->timestamp('finished_at')->nullable();
                // SQLite doesn't have ENUM, uses CHECK constraint instead
                // The status column should already exist as text
            });
        } else {
            // MySQL/MariaDB approach: Use ENUM
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->timestamp('finished_at')->nullable()->after('approved_at');
            });
            
            // Modify status column to include 'finished' and 'cancelled' 
            DB::statement("ALTER TABLE meeting_room_bookings MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled', 'finished') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: Just drop the finished_at column
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->dropColumn('finished_at');
            });
        } else {
            // MySQL/MariaDB
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->dropColumn('finished_at');
            });
            
            // Revert status to original values
            DB::statement("ALTER TABLE meeting_room_bookings MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
