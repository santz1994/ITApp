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
        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('ticket_id')->nullable(); // Bisa null untuk maintenance manual
            $table->unsignedBigInteger('performed_by'); // User yang melakukan maintenance
            $table->string('maintenance_type'); // 'repair', 'preventive', 'upgrade', etc.
            $table->text('description');
            $table->string('part_name')->nullable();
            $table->text('parts_used')->nullable(); // JSON string untuk multiple parts
            $table->decimal('cost', 10, 2)->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraints will be added later due to potential type mismatch

            // Indexes for better performance
            $table->index('asset_id');
            $table->index('ticket_id');
            $table->index('performed_by');
            $table->index('maintenance_type');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
    }
};
