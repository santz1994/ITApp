<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add composite indexes for movement history queries.
     * These optimize queries like:
     * - Asset movement history with timeline sorting
     * - Location transfer history with timeline sorting
     * 
     * Priority: LOW (optimization for future growth beyond 1000 movements)
     */
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            // Asset movement history with efficient date sorting
            // Optimizes: WHERE asset_id = X ORDER BY created_at DESC
            $table->index(['asset_id', 'created_at'], 'movements_asset_created_idx');
            
            // Location transfer history with efficient date sorting
            // Optimizes: WHERE location_id = X ORDER BY created_at DESC
            $table->index(['location_id', 'created_at'], 'movements_location_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            // Drop composite indexes
            $table->dropIndex('movements_asset_created_idx');
            $table->dropIndex('movements_location_created_idx');
        });
    }
};
