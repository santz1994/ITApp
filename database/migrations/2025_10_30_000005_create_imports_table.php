<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            
            // Import identification
            $table->uuid('import_id')->unique();
            $table->enum('resource_type', ['assets', 'tickets']);
            $table->enum('import_format', ['csv', 'excel', 'json']);
            $table->enum('import_status', ['validating', 'validated', 'processing', 'completed', 'failed', 'rolled_back'])->default('validating');
            
            // File information
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable();
            
            // Row counters
            $table->integer('total_rows')->default(0);
            $table->integer('validated_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('conflicted_rows')->default(0);
            
            // Configuration
            $table->json('column_mapping')->nullable();
            $table->enum('import_strategy', ['create', 'update', 'create_if_not_exists', 'manual_review'])->default('create');
            $table->boolean('auto_resolve_conflicts')->default(false);
            
            // User attribution
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Tracking
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Error and conflict tracking
            $table->json('error_summary')->nullable();
            $table->json('conflict_summary')->nullable();
            
            $table->timestamps();
            
            // Indexes for query optimization
            $table->index('import_id');
            $table->index(['resource_type', 'import_status']);
            $table->index(['created_by', 'created_at']);
            $table->index('expires_at');
            $table->index(['import_status', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imports');
    }
}
