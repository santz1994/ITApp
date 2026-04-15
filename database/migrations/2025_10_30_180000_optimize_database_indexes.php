<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing FULLTEXT indexes for search capability
     * This migration adds FULLTEXT indexes that were not in the original index optimization
     */
    public function up(): void
    {
        // ===== FULLTEXT INDEXES FOR SEARCH =====
        // Add FULLTEXT indexes to enable fast text search capabilities
        
        // Assets: Search by tag, serial, notes
        if (Schema::hasTable('assets')) {
            try {
                // Check if FULLTEXT index already exists
                $existing = DB::select(
                    "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = 'assets' AND INDEX_NAME = 'assets_search_fulltext_idx'"
                );
                if (empty($existing)) {
                    DB::statement('ALTER TABLE assets ADD FULLTEXT INDEX assets_search_fulltext_idx (asset_tag, serial_number, notes)');
                }
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        }

        // Tickets: Search by subject, description
        if (Schema::hasTable('tickets')) {
            try {
                $existing = DB::select(
                    "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = 'tickets' AND INDEX_NAME = 'tickets_search_fulltext_idx'"
                );
                if (empty($existing)) {
                    DB::statement('ALTER TABLE tickets ADD FULLTEXT INDEX tickets_search_fulltext_idx (subject, description)');
                }
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        }

        // Ticket Comments: Search by comment text
        if (Schema::hasTable('ticket_comments')) {
            try {
                $existing = DB::select(
                    "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = 'ticket_comments' AND INDEX_NAME = 'ticket_comments_search_fulltext_idx'"
                );
                if (empty($existing)) {
                    DB::statement('ALTER TABLE ticket_comments ADD FULLTEXT INDEX ticket_comments_search_fulltext_idx (comment)');
                }
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        }

        // Add any missing standard indexes for daily_activities
        if (Schema::hasTable('daily_activities')) {
            Schema::table('daily_activities', function (Blueprint $table) {
                // These standard indexes complement the existing time-based indexes
                if (!$this->indexExists('daily_activities', 'daily_activities_activity_type_idx')) {
                    $table->index('activity_type', 'daily_activities_activity_type_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Remove FULLTEXT indexes
        if (Schema::hasTable('assets')) {
            try {
                DB::statement('ALTER TABLE assets DROP INDEX assets_search_fulltext_idx');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        }

        if (Schema::hasTable('tickets')) {
            try {
                DB::statement('ALTER TABLE tickets DROP INDEX tickets_search_fulltext_idx');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        }

        if (Schema::hasTable('ticket_comments')) {
            try {
                DB::statement('ALTER TABLE ticket_comments DROP INDEX ticket_comments_search_fulltext_idx');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        }

        // Remove standard indexes
        if (Schema::hasTable('daily_activities')) {
            try {
                DB::statement('ALTER TABLE daily_activities DROP INDEX daily_activities_activity_type_idx');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        }
    }

    /**
     * Helper method to check if an index exists
     */
    private function indexExists($table, $indexName): bool
    {
        try {
            $indexes = DB::select(
                "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1",
                [$table, $indexName]
            );
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
