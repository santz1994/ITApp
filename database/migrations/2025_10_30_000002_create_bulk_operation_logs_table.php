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
        Schema::create('bulk_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('bulk_operation_id')->comment('FK to bulk_operations.operation_id');
            $table->enum('resource_type', ['assets', 'tickets'])->comment('Type of resource updated');
            $table->unsignedBigInteger('resource_id')->comment('ID of the resource updated');
            $table->enum('operation_type', ['status_update', 'assignment', 'field_update'])->comment('Type of operation');
            $table->json('old_values')->nullable()->comment('JSON snapshot of values before update');
            $table->json('new_values')->nullable()->comment('JSON snapshot of values after update');
            $table->enum('status', ['success', 'failed'])->default('success')->comment('Result of update for this item');
            $table->text('error_message')->nullable()->comment('Error message if update failed');
            $table->timestamps();

            // Indexes
            $table->index('bulk_operation_id');
            $table->index(['bulk_operation_id', 'resource_id']);
            $table->index('resource_type');
            $table->index('resource_id');
            $table->index('status');
            $table->index('operation_type');
            $table->index('created_at');

            // Foreign key
            $table->foreign('bulk_operation_id')->references('operation_id')->on('bulk_operations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_operation_logs');
    }
};
