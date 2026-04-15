<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            
            // Relationship
            $table->uuid('import_id');
            $table->foreign('import_id')
                  ->references('import_id')
                  ->on('imports')
                  ->onDelete('cascade');
            
            // Event information
            $table->enum('event', [
                'file_uploaded',
                'validation_started',
                'validation_complete',
                'processing_started',
                'row_imported',
                'row_failed',
                'row_conflict',
                'processing_complete',
                'import_failed',
                'rolled_back'
            ]);
            $table->text('message')->nullable();
            
            // Row tracking
            $table->integer('row_number')->nullable();
            $table->json('data')->nullable();
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->string('resolution')->nullable();
            
            // Timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('import_id');
            $table->index(['import_id', 'event']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_logs');
    }
}
