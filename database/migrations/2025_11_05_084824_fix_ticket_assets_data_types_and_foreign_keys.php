<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL FIX: ticket_assets table has data type mismatch preventing FK creation.
     * 
     * Problem:
     * - tickets.id = unsigned integer (32-bit)
     * - assets.id = unsigned integer (32-bit)
     * - ticket_assets.ticket_id = unsigned bigint (64-bit) ❌ WRONG!
     * - ticket_assets.asset_id = unsigned bigint (64-bit) ❌ WRONG!
     * 
     * Solution:
     * 1. Backup existing data
     * 2. Drop and recreate table with correct data types
     * 3. Restore data
     * 4. Add foreign key constraints (will work now!)
     */
    public function up(): void
    {
        // Step 1: Backup existing data
        $existingData = DB::table('ticket_assets')->get();
        
        // Step 2: Drop existing table
        Schema::dropIfExists('ticket_assets');
        
        // Step 3: Recreate with CORRECT data types (unsignedInteger, not unsignedBigInteger)
        Schema::create('ticket_assets', function (Blueprint $table) {
            $table->id(); // bigint auto-increment for this table's PK is fine
            
            // ✅ FIXED: Use unsignedInteger to match tickets.id and assets.id
            $table->unsignedInteger('ticket_id');
            $table->unsignedInteger('asset_id');
            
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('ticket_id');
            $table->index('asset_id');
            
            // Add unique constraint
            $table->unique(['ticket_id', 'asset_id']);
            
            // ✅ NOW foreign keys will work (matching data types!)
            $table->foreign('ticket_id')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->foreign('asset_id')
                  ->references('id')
                  ->on('assets')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
        
        // Step 4: Restore data
        foreach ($existingData as $row) {
            DB::table('ticket_assets')->insert([
                'id' => $row->id,
                'ticket_id' => $row->ticket_id,
                'asset_id' => $row->asset_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
        
        // Step 5: Log success
        Log::info('ticket_assets table recreated with correct data types and FK constraints');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data
        $existingData = DB::table('ticket_assets')->get();
        
        // Drop table
        Schema::dropIfExists('ticket_assets');
        
        // Recreate with OLD (wrong) data types to match original state
        Schema::create('ticket_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ticket_id')->index(); // Wrong type (original)
            $table->unsignedBigInteger('asset_id')->index(); // Wrong type (original)
            $table->timestamps();
            $table->unique(['ticket_id', 'asset_id']);
            // No FK constraints (original state)
        });
        
        // Restore data
        foreach ($existingData as $row) {
            DB::table('ticket_assets')->insert([
                'id' => $row->id,
                'ticket_id' => $row->ticket_id,
                'asset_id' => $row->asset_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }
};
