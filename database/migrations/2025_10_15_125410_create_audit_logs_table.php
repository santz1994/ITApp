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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->comment('User who performed the action');
            $table->string('action', 100)->comment('Action performed: create, update, delete, login, logout, etc.');
            $table->string('model_type', 100)->nullable()->comment('Model class name (e.g., App\Ticket)');
            $table->unsignedBigInteger('model_id')->nullable()->comment('ID of the affected model');
            $table->text('old_values')->nullable()->comment('JSON of old values before change');
            $table->text('new_values')->nullable()->comment('JSON of new values after change');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent string');
            $table->text('description')->nullable()->comment('Human-readable description of the action');
            $table->string('event_type', 50)->default('model')->comment('Event type: model, auth, system');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id', 'idx_audit_user');
            $table->index('model_type', 'idx_audit_model_type');
            $table->index('model_id', 'idx_audit_model_id');
            $table->index('action', 'idx_audit_action');
            $table->index(['model_type', 'model_id'], 'idx_audit_model_composite');
            $table->index('created_at', 'idx_audit_created');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
