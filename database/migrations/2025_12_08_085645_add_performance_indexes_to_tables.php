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
        // Tickets table indexes for dashboard queries
        Schema::table('tickets', function (Blueprint $table) {
            // Speed up monthly ticket trend queries (HomeController)
            // Used in: whereYear('created_at') + whereMonth('created_at')
            $table->index('created_at', 'idx_tickets_created_at');
            
            // Speed up ticket status filtering (HomeController)
            // Used in: groupBy('ticket_status_id')
            $table->index('ticket_status_id', 'idx_tickets_status_id');
            
            // Composite index for resolved ticket queries
            // Used in: WHERE created_at + WHERE ticket_status_id
            $table->index(['created_at', 'ticket_status_id'], 'idx_tickets_created_status');
        });
        
        // Assets table indexes for dashboard queries
        Schema::table('assets', function (Blueprint $table) {
            // Speed up asset status grouping (HomeController)
            $table->index('status_id', 'idx_assets_status_id');
            
            // Speed up asset model joins (HomeController)
            $table->index('model_id', 'idx_assets_model_id');
        });
        
        // Asset movements table indexes
        Schema::table('movements', function (Blueprint $table) {
            // Speed up recent movements queries (common in reports)
            $table->index('created_at', 'idx_movements_created_at');
            
            // Speed up asset movement history lookups
            $table->index('asset_id', 'idx_movements_asset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tickets indexes
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_created_at');
            $table->dropIndex('idx_tickets_status_id');
            $table->dropIndex('idx_tickets_created_status');
        });
        
        // Drop assets indexes
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('idx_assets_status_id');
            $table->dropIndex('idx_assets_model_id');
        });
        
        // Drop movements indexes
        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_created_at');
            $table->dropIndex('idx_movements_asset_id');
        });
    }
};
