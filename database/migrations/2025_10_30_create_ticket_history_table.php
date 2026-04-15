<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates an immutable audit log for tracking ticket changes.
     * Records every change to: status, priority, assignment, SLA fields
     * Essential for compliance, SLA tracking, and change audit trails.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ticket_history')) {
            Schema::create('ticket_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id')->index();
                $table->string('field_changed', 100)->comment('Field name: status, priority, assigned_to, etc.');
                $table->text('old_value')->nullable()->comment('Previous value (JSON-safe)');
                $table->text('new_value')->nullable()->comment('New value (JSON-safe)');
                $table->unsignedBigInteger('changed_by_user_id')->nullable()->index();
                $table->timestamp('changed_at')->useCurrent();
                
                // Additional context
                $table->string('change_type', 50)->default('update')->comment('update, status_change, assignment, escalation, etc.');
                $table->text('reason')->nullable()->comment('Why the change was made');
                
                // Indexes for efficient queries
                $table->index(['ticket_id', 'changed_at'], 'idx_ticket_history_ticket_date');
                $table->index('changed_by_user_id', 'idx_ticket_history_user');
                $table->index('change_type', 'idx_ticket_history_type');
                
                // Foreign keys
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ticket_history')) {
            Schema::dropIfExists('ticket_history');
        }
    }
};
