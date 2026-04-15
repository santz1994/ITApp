<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('daily_activities')) {
            Schema::create('daily_activities', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->unsigned();
                // New columns to align with later migration and tests
                $table->string('title')->nullable();
                $table->string('activity')->nullable();
                $table->string('activity_type')->nullable();
                $table->date('activity_date')->nullable();
                $table->date('date')->nullable();

                // Backwards-compatible fields from original design
                $table->text('description')->nullable();
                $table->integer('ticket_id')->unsigned()->nullable(); // Auto-generated from ticket completion
                $table->enum('type', ['manual', 'auto_from_ticket'])->default('manual');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('set null');
                $table->index(['user_id', 'activity_date']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('daily_activities');
    }
};