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
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Time in minutes for response and resolution
            $table->integer('response_time')->comment('Minutes to first response');
            $table->integer('resolution_time')->comment('Minutes to resolution');
            
            // Optional: Link to ticket priority (foreign key will be added later after all tables exist)
            $table->unsignedInteger('priority_id')->nullable();
            
            // Optional: Business hours only (8am-5pm) or 24/7
            $table->boolean('business_hours_only')->default(true);
            
            // Optional: Escalation settings
            $table->integer('escalation_time')->nullable()->comment('Minutes before escalation');
            $table->unsignedBigInteger('escalate_to_user_id')->nullable();
            
            // Active/Inactive
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index('priority_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
