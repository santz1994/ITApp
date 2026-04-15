<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportConflictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_conflicts', function (Blueprint $table) {
            $table->id();
            
            // Relationship
            $table->uuid('import_id');
            $table->foreign('import_id')
                  ->references('import_id')
                  ->on('imports')
                  ->onDelete('cascade');
            
            // Conflict information
            $table->integer('row_number');
            $table->enum('conflict_type', [
                'duplicate_key',
                'duplicate_record',
                'foreign_key_not_found',
                'invalid_data',
                'business_rule_violation'
            ]);
            
            // Record references
            $table->integer('existing_record_id')->nullable();
            $table->json('new_record_data')->nullable();
            
            // Resolution
            $table->enum('suggested_resolution', ['skip', 'create_new', 'update_existing', 'merge'])->default('skip');
            $table->enum('user_resolution', ['skip', 'create_new', 'update_existing', 'merge'])->nullable();
            $table->integer('resolution_choice_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('import_id');
            $table->index(['import_id', 'conflict_type']);
            $table->index('row_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_conflicts');
    }
}
