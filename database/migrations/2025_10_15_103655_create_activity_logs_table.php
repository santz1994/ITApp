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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject'); // subject_type, subject_id
            $table->nullableMorphs('causer', 'causer'); // causer_type, causer_id (who did it)
            $table->json('properties')->nullable(); // old/new attributes, metadata
            $table->string('event')->nullable(); // created, updated, deleted, etc.
            $table->string('batch_uuid')->nullable(); // for grouping related activities
            $table->timestamps();
            
            // Indexes for performance
            $table->index('log_name');
            $table->index('created_at');
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
