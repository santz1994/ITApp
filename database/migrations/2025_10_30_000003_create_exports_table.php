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
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('export_id')->unique()->comment('Unique export identifier');
            $table->enum('resource_type', ['assets', 'tickets'])->comment('Type of resource being exported');
            $table->enum('export_format', ['csv', 'excel', 'json'])->comment('Export file format');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'expired'])->default('pending')->comment('Export status');
            $table->unsignedBigInteger('total_items')->default(0)->comment('Total items to export');
            $table->unsignedBigInteger('exported_items')->default(0)->comment('Items successfully exported');
            $table->unsignedBigInteger('failed_items')->default(0)->comment('Items that failed to export');
            $table->unsignedBigInteger('file_size')->nullable()->comment('Export file size in bytes');
            $table->string('file_path')->nullable()->comment('Storage path to exported file');
            $table->json('filter_config')->nullable()->comment('Applied filters configuration');
            $table->json('column_config')->nullable()->comment('Selected columns configuration');
            $table->unsignedInteger('created_by')->comment('User who created the export');
            $table->timestamp('completed_at')->nullable()->comment('Timestamp when export completed');
            $table->timestamp('expires_at')->nullable()->comment('Timestamp when export file expires');
            $table->unsignedBigInteger('download_count')->default(0)->comment('Number of times exported file was downloaded');
            $table->json('error_details')->nullable()->comment('Error details if export failed');
            $table->timestamp('email_sent_at')->nullable()->comment('Timestamp when completion email was sent');
            $table->timestamps();

            // Indexes
            $table->index('export_id');
            $table->index(['resource_type', 'status']);
            $table->index(['created_by', 'created_at']);
            $table->index('expires_at');
            $table->index(['status', 'completed_at']);

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
