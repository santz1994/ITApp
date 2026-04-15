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
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('export_id')->comment('Reference to export');
            $table->enum('event', ['initiated', 'processing', 'progress', 'completed', 'failed', 'expired', 'deleted'])->comment('Event type');
            $table->text('message')->comment('Event message');
            $table->unsignedBigInteger('processed_items')->nullable()->comment('Items processed in this batch');
            $table->unsignedBigInteger('current_batch')->nullable()->comment('Current batch number');
            $table->unsignedBigInteger('total_batches')->nullable()->comment('Total batches to process');
            $table->text('error_message')->nullable()->comment('Error message if failed');
            $table->unsignedBigInteger('duration_ms')->nullable()->comment('Duration in milliseconds');
            $table->timestamp('created_at')->nullable()->comment('Event timestamp');

            // Indexes
            $table->index('export_id');
            $table->index(['export_id', 'event']);
            $table->index('created_at');

            // Foreign keys
            $table->foreign('export_id')->references('export_id')->on('exports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};
