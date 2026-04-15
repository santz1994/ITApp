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
        Schema::create('meeting_room_lcd_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('rooms_per_slide')->default(2);
            $table->unsignedInteger('slide_interval_seconds')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_room_lcd_settings');
    }
};
