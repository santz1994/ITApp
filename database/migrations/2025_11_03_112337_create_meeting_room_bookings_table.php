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
        Schema::create('meeting_room_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('room_name');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->text('purpose');
            $table->integer('attendees_count')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('director_notes')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('status');
            $table->index('start_datetime');
            $table->index(['room_name', 'start_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_room_bookings');
    }
};
