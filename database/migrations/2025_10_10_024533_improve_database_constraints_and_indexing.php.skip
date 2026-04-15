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
        Schema::table('users', function (Blueprint $table) {
            // Ensure email column has unique constraint (it should already exist, but making sure)
            // The unique constraint should already exist from the original migration
        });

        // Add composite indexes for better query performance
        Schema::table('assets', function (Blueprint $table) {
            // Add composite index for status_id and division_id if not exists
            $table->index(['status_id', 'division_id'], 'assets_status_division_index');
            
            // Add index for search functionality
            $table->index(['name', 'asset_tag', 'serial'], 'assets_search_index');
        });

        // Add indexes for better performance on tickets
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['status_id', 'priority_id'], 'tickets_status_priority_index');
                $table->index(['assigned_to', 'status_id'], 'tickets_assigned_status_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('assets_status_division_index');
            $table->dropIndex('assets_search_index');
        });

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropIndex('tickets_status_priority_index');
                $table->dropIndex('tickets_assigned_status_index');
            });
        }
    }
};
