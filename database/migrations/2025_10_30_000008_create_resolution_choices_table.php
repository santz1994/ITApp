<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResolutionChoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resolution_choices', function (Blueprint $table) {
            $table->id();
            
            // Relationship
            $table->uuid('import_id');
            $table->foreign('import_id')
                  ->references('import_id')
                  ->on('imports')
                  ->onDelete('cascade');
            
            $table->unsignedBigInteger('conflict_id');
            $table->foreign('conflict_id')
                  ->references('id')
                  ->on('import_conflicts')
                  ->onDelete('cascade');
            
            // User information
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Choice details
            $table->enum('choice', ['skip', 'create_new', 'update_existing', 'merge']);
            $table->json('choice_details')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('import_id');
            $table->index('conflict_id');
            $table->index('user_id');
            $table->index(['import_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resolution_choices');
    }
}
