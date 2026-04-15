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
        Schema::create('bulk_operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_id')->unique()->comment('Unique operation identifier');
            $table->enum('resource_type', ['assets', 'tickets'])->comment('Type of resource being bulk updated');
            $table->enum('operation_type', ['status_update', 'assignment', 'field_update'])->comment('Type of bulk operation');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partial'])->default('pending')->comment('Operation status');
            $table->unsignedInteger('total_items')->default(0)->comment('Total items in operation');
            $table->unsignedInteger('processed_items')->default(0)->comment('Successfully processed items');
            $table->unsignedInteger('failed_items')->default(0)->comment('Failed items');
            $table->unsignedInteger('created_by')->comment('User who initiated operation');
            $table->timestamp('completed_at')->nullable()->comment('Operation completion time');
            $table->json('error_details')->nullable()->comment('Error details if operation failed');
            $table->timestamps();

            // Indexes
            $table->index('operation_id');
            $table->index('resource_type');
            $table->index('operation_type');
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_operations');
    }
};
