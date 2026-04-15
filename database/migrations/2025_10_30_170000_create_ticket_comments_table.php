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
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ticket_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->text('comment');
            $table->boolean('is_internal')->default(false);  // false = customer visible, true = internal staff note
            $table->timestamps();

            // Foreign keys
            $table->foreign('ticket_id')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');  // Don't allow deleting user with comments

            // Indexes for common queries
            $table->index(['ticket_id', 'is_internal']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
