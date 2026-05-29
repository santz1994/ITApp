<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Approval rules - Dynamic approval workflow configuration
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // 'meeting_room', 'vehicle', 'inventory', etc.
            $table->string('name'); // Nama rule (e.g., "Staff -> Manager -> HRD-GA")
            $table->text('description')->nullable();
            $table->integer('priority')->default(0); // Urutan rule (higher = checked first)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Approval rule steps - Each rule has multiple steps
        Schema::create('approval_rule_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('approval_rules')->onDelete('cascade');
            $table->integer('step_order'); // Urutan step (1, 2, 3, ...)
            $table->string('approval_type'); // 'role', 'specific_user', 'department_manager'
            $table->unsignedBigInteger('approver_id')->nullable(); // ID role atau user
            $table->string('approver_reference')->nullable(); // Role name atau condition
            $table->boolean('is_mandatory')->default(true); // Wajib atau optional
            $table->boolean('any_of_group')->default(false); // True = cukup 1 dari group approve
            $table->timestamps();
        });

        // Approval instances - Track actual approval flow for each request
        Schema::create('approval_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('approval_rules')->onDelete('cascade');
            $table->string('requestable_type'); // Model type (MeetingRoomBooking, VehicleBooking, etc.)
            $table->unsignedBigInteger('requestable_id'); // Model ID
            $table->enum('status', ['in_progress', 'approved', 'rejected', 'cancelled'])->default('in_progress');
            $table->integer('current_step')->default(1); // Step saat ini
            $table->timestamps();

            $table->index(['requestable_type', 'requestable_id']);
        });

        // Approval step instances - Track each step's approval status
        Schema::create('approval_step_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('approval_instances')->onDelete('cascade');
            $table->foreignId('step_id')->constrained('approval_rule_steps')->onDelete('cascade');
            $table->integer('step_order');
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_step_instances');
        Schema::dropIfExists('approval_instances');
        Schema::dropIfExists('approval_rule_steps');
        Schema::dropIfExists('approval_rules');
    }
};