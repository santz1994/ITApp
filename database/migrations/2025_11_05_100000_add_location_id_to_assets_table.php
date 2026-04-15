<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add location_id to assets table for performance optimization.
     * This denormalizes the current location from movements table.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Add location_id column (nullable since some assets may not have movements yet)
            $table->integer('location_id')->unsigned()->nullable()->after('division_id');
            
            // Add foreign key constraint
            $table->foreign('location_id')
                  ->references('id')
                  ->on('locations')
                  ->onDelete('set null') // If location deleted, set to NULL
                  ->onUpdate('cascade');
            
            // Add index for fast location-based queries
            $table->index('location_id', 'assets_location_id_idx');
        });
        
        // Backfill location_id from movements table (most recent movement per asset)
        $this->backfillAssetLocations();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Drop foreign key and index first
            $table->dropForeign(['location_id']);
            $table->dropIndex('assets_location_id_idx');
            
            // Drop the column
            $table->dropColumn('location_id');
        });
    }
    
    /**
     * Backfill location_id for existing assets from movements table
     */
    private function backfillAssetLocations(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support UPDATE with JOIN, use subquery approach
            try {
                DB::statement("
                    UPDATE assets
                    SET location_id = (
                        SELECT m.location_id
                        FROM movements m
                        WHERE m.asset_id = assets.id
                        ORDER BY m.created_at DESC
                        LIMIT 1
                    )
                    WHERE EXISTS (
                        SELECT 1
                        FROM movements m2
                        WHERE m2.asset_id = assets.id
                    )
                ");
                
                $updatedCount = DB::table('assets')
                    ->whereNotNull('location_id')
                    ->count();
                
                \Log::info("Location denormalization: Backfilled {$updatedCount} assets with current locations (SQLite)");
            } catch (\Exception $e) {
                \Log::error("Location denormalization backfill failed (SQLite): " . $e->getMessage());
            }
            return;
        }
        
        // MySQL syntax: UPDATE with JOIN
        $sql = "
            UPDATE assets a
            LEFT JOIN (
                SELECT m1.asset_id, m1.location_id
                FROM movements m1
                INNER JOIN (
                    SELECT asset_id, MAX(created_at) as max_created_at
                    FROM movements
                    GROUP BY asset_id
                ) m2 ON m1.asset_id = m2.asset_id AND m1.created_at = m2.max_created_at
            ) latest_movement ON a.id = latest_movement.asset_id
            SET a.location_id = latest_movement.location_id
            WHERE latest_movement.location_id IS NOT NULL
        ";
        
        try {
            DB::statement($sql);
            
            // Log how many assets were updated
            $updatedCount = DB::table('assets')
                ->whereNotNull('location_id')
                ->count();
            
            \Log::info("Location denormalization: Backfilled {$updatedCount} assets with current locations");
        } catch (\Exception $e) {
            \Log::error("Location denormalization backfill failed: " . $e->getMessage());
            // Don't throw exception - this is just a data migration
        }
    }
};
