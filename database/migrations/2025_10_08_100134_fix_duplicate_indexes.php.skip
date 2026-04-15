<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing problematic indexes if they exist
        Schema::table('tickets', function (Blueprint $table) {
            try {
                $table->dropIndex('tickets_status_created_idx');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            
            try {
                $table->dropIndex('tickets_user_status_idx');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            
            try {
                $table->dropIndex('tickets_assigned_status_idx');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
        });

        // Remove movement_id column from assets if it exists
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'movement_id')) {
                $table->dropForeign(['movement_id']);
                $table->dropColumn('movement_id');
            }
        });

        // Add necessary indexes that don't exist
        Schema::table('assets', function (Blueprint $table) {
            if (!$this->hasIndex('assets', 'asset_tag')) {
                $table->index('asset_tag');
            }
            if (!$this->hasIndex('assets', 'serial_number')) {
                $table->index('serial_number');
            }
            if (!$this->hasIndex('assets', 'status_id')) {
                $table->index('status_id');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (!$this->hasIndex('tickets', 'user_id')) {
                $table->index('user_id');
            }
            if (!$this->hasIndex('tickets', 'assigned_to')) {
                $table->index('assigned_to');
            }
            if (!$this->hasIndex('tickets', 'ticket_status_id')) {
                $table->index('ticket_status_id');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex($table, $column)
    {
        $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"));
        return $indexes->contains('Column_name', $column);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
