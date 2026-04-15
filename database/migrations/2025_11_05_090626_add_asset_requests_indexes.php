<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes for asset request dashboard and filtering.
     * These optimize queries like:
     * - Filter requests by status (pending, approved, fulfilled, rejected)
     * - Filter requests by priority (high, medium, low)
     * - Pending requests dashboard sorted by priority and date
     * - User's requests filtered by status
     * 
     * Priority: LOW-MEDIUM (helpful for growth beyond 100 requests)
     */
    public function up(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            // Status filtering (most common filter in dashboard)
            // Optimizes: WHERE status = 'pending'
            $table->index('status', 'asset_requests_status_idx');
            
            // Priority filtering
            // Optimizes: WHERE priority = 'high'
            $table->index('priority', 'asset_requests_priority_idx');
            
            // Dashboard: Pending requests by priority with sorting
            // Optimizes: WHERE status = 'pending' ORDER BY priority DESC, created_at
            $table->index(['status', 'priority', 'created_at'], 
                          'asset_requests_status_priority_created_idx');
            
            // User requests with status filtering
            // Optimizes: WHERE requested_by = X AND status IN ('approved', 'fulfilled')
            $table->index(['requested_by', 'status'], 
                          'asset_requests_requested_by_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            // Drop all indexes
            $table->dropIndex('asset_requests_status_idx');
            $table->dropIndex('asset_requests_priority_idx');
            $table->dropIndex('asset_requests_status_priority_created_idx');
            $table->dropIndex('asset_requests_requested_by_status_idx');
        });
    }
};
