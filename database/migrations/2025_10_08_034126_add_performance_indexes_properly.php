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
        // Add indexes only if they don't exist to prevent duplicate key errors
        $this->addIndexIfNotExists('tickets', 'tickets_priority_sla_idx', ['ticket_priority_id', 'sla_due']);
        $this->addIndexIfNotExists('tickets', 'tickets_assigned_status_idx', ['assigned_to', 'ticket_status_id']);
        $this->addIndexIfNotExists('tickets', 'tickets_asset_created_idx', ['asset_id', 'created_at']);
        
        $this->addIndexIfNotExists('assets', 'assets_status_division_idx', ['status_id', 'division_id']);
        $this->addIndexIfNotExists('assets', 'assets_assigned_status_idx', ['assigned_to', 'status_id']);
        $this->addIndexIfNotExists('assets', 'assets_purchase_warranty_idx', ['purchase_date', 'warranty_months']);
        
        $this->addIndexIfNotExists('daily_activities', 'daily_activities_user_date_idx', ['user_id', 'activity_date']);
        $this->addIndexIfNotExists('daily_activities', 'daily_activities_ticket_type_idx', ['ticket_id', 'activity_type']);
        
        $this->addIndexIfNotExists('users', 'users_created_at_idx', ['created_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('tickets', 'tickets_priority_sla_idx');
        $this->dropIndexIfExists('tickets', 'tickets_assigned_status_idx');
        $this->dropIndexIfExists('tickets', 'tickets_asset_created_idx');
        
        $this->dropIndexIfExists('assets', 'assets_status_division_idx');
        $this->dropIndexIfExists('assets', 'assets_assigned_status_idx');
        $this->dropIndexIfExists('assets', 'assets_purchase_warranty_idx');
        
        $this->dropIndexIfExists('daily_activities', 'daily_activities_user_date_idx');
        $this->dropIndexIfExists('daily_activities', 'daily_activities_ticket_type_idx');
        
        $this->dropIndexIfExists('users', 'users_created_at_idx');
    }

    private function addIndexIfNotExists($table, $indexName, $columns)
    {
        try {
            // Check if index exists - compatible with both MySQL and SQLite
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'sqlite') {
                // SQLite: Query sqlite_master table
                $exists = DB::select(
                    "SELECT name FROM sqlite_master WHERE type='index' AND name=?",
                    [$indexName]
                );
            } else {
                // MySQL: Use SHOW INDEX
                $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
            }
            
            if (empty($exists)) {
                Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                    $table->index($columns, $indexName);
                });
                echo "Added index: {$indexName}\n";
            } else {
                echo "Index already exists: {$indexName}\n";
            }
        } catch (\Exception $e) {
            echo "Could not add index {$indexName}: " . $e->getMessage() . "\n";
        }
    }

    private function dropIndexIfExists($table, $indexName)
    {
        try {
            // Check if index exists - compatible with both MySQL and SQLite
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'sqlite') {
                $exists = DB::select(
                    "SELECT name FROM sqlite_master WHERE type='index' AND name=?",
                    [$indexName]
                );
            } else {
                $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
            }
            
            if (!empty($exists)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            // Ignore errors when dropping indexes
        }
    }
};
