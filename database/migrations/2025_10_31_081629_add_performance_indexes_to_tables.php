<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add performance indexes to frequently queried columns
     * This will significantly improve query performance for:
     * - Asset lookups by status, location, division, assigned user
     * - Ticket lookups by status, priority, assigned user, SLA due date
     * - User lookups by status, role
     * - Audit log searches by date and user
     */
    public function up(): void
    {
        // ============================================
        // ASSETS TABLE INDEXES
        // ============================================
        Schema::table('assets', function (Blueprint $table) {
            // Note: serial_number already has UNIQUE index
            // Note: Foreign keys already have indexes (model_id, division_id, supplier_id, etc.)
            
            // Status index - for filtering by status (only if column exists)
            if (Schema::hasColumn('assets', 'status_id') && !$this->hasIndex('assets', 'assets_status_id_index')) {
                $table->index('status_id', 'assets_status_id_index');
            }
            
            // Assigned to index - for "My Assets" and assignment queries (only if column exists)
            if (Schema::hasColumn('assets', 'assigned_to') && !$this->hasIndex('assets', 'assets_assigned_to_index')) {
                $table->index('assigned_to', 'assets_assigned_to_index');
            }
            
            // Created at index - for date range queries and sorting
            if (!$this->hasIndex('assets', 'assets_created_at_index')) {
                $table->index('created_at', 'assets_created_at_index');
            }
            
            // Composite index for common dashboard queries (status + created_at) - only if status_id exists
            if (Schema::hasColumn('assets', 'status_id') && !$this->hasIndex('assets', 'assets_status_created_composite')) {
                $table->index(['status_id', 'created_at'], 'assets_status_created_composite');
            }
            
            // Composite index for assignment queries (assigned_to + status) - only if both columns exist
            if (Schema::hasColumn('assets', 'assigned_to') && Schema::hasColumn('assets', 'status_id') && !$this->hasIndex('assets', 'assets_assigned_status_composite')) {
                $table->index(['assigned_to', 'status_id'], 'assets_assigned_status_composite');
            }
        });

        // ============================================
        // TICKETS TABLE INDEXES
        // ============================================
        Schema::table('tickets', function (Blueprint $table) {
            // Note: ticket_status_id, ticket_priority_id, user_id, location_id already have indexes from FK constraints
            
            // Assigned to index - for "My Tickets" queries (only if column exists)
            if (Schema::hasColumn('tickets', 'assigned_to') && !$this->hasIndex('tickets', 'tickets_assigned_to_index')) {
                $table->index('assigned_to', 'tickets_assigned_to_index');
            }
            
            // SLA due date index - for SLA monitoring and alerts (only if column exists)
            if (Schema::hasColumn('tickets', 'sla_due') && !$this->hasIndex('tickets', 'tickets_sla_due_index')) {
                $table->index('sla_due', 'tickets_sla_due_index');
            }
            
            // Created at index - for date range queries
            if (!$this->hasIndex('tickets', 'tickets_created_at_index')) {
                $table->index('created_at', 'tickets_created_at_index');
            }
            
            // Updated at index - for activity tracking
            if (!$this->hasIndex('tickets', 'tickets_updated_at_index')) {
                $table->index('updated_at', 'tickets_updated_at_index');
            }
            
            // Composite index for dashboard queries (status + priority + created_at)
            if (!$this->hasIndex('tickets', 'tickets_status_priority_created_composite')) {
                $table->index(['ticket_status_id', 'ticket_priority_id', 'created_at'], 'tickets_status_priority_created_composite');
            }
            
            // Composite index for assignment + status queries (only if assigned_to exists)
            if (Schema::hasColumn('tickets', 'assigned_to') && !$this->hasIndex('tickets', 'tickets_assigned_status_composite')) {
                $table->index(['assigned_to', 'ticket_status_id'], 'tickets_assigned_status_composite');
            }
            
            // Composite index for SLA breach detection (sla_due + status) - only if sla_due exists
            if (Schema::hasColumn('tickets', 'sla_due') && !$this->hasIndex('tickets', 'tickets_sla_status_composite')) {
                $table->index(['sla_due', 'ticket_status_id'], 'tickets_sla_status_composite');
            }
        });

        // ============================================
        // USERS TABLE INDEXES
        // ============================================
        Schema::table('users', function (Blueprint $table) {
            // Email already has UNIQUE index
            
            // Active status index - for filtering active users
            if (!$this->hasIndex('users', 'users_is_active_index')) {
                $table->index('is_active', 'users_is_active_index');
            }
            
            // Created at index - for user registration tracking
            if (!$this->hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });

        // ============================================
        // AUDIT_LOGS TABLE INDEXES
        // ============================================
        // Note: audit_logs table already has comprehensive indexes created during table creation:
        // - idx_audit_user (user_id)
        // - idx_audit_model_type (model_type)
        // - idx_audit_model_id (model_id)
        // - idx_audit_action (action)
        // - idx_audit_model_composite (model_type, model_id)
        // - idx_audit_created (created_at)
        // No additional indexes needed for audit_logs table

        // ============================================
        // DAILY_ACTIVITIES TABLE INDEXES
        // ============================================
        if (Schema::hasTable('daily_activities')) {
            Schema::table('daily_activities', function (Blueprint $table) {
                // User index - for user activity tracking
                if (!$this->hasIndex('daily_activities', 'daily_activities_user_id_index')) {
                    $table->index('user_id', 'daily_activities_user_id_index');
                }
                
                // Ticket index - for ticket time tracking
                if (!$this->hasIndex('daily_activities', 'daily_activities_ticket_id_index')) {
                    $table->index('ticket_id', 'daily_activities_ticket_id_index');
                }
                
                // Activity date index - for date range queries
                if (!$this->hasIndex('daily_activities', 'daily_activities_activity_date_index')) {
                    $table->index('activity_date', 'daily_activities_activity_date_index');
                }
                
                // Composite index for user activity by date
                if (!$this->hasIndex('daily_activities', 'daily_activities_user_date_composite')) {
                    $table->index(['user_id', 'activity_date'], 'daily_activities_user_date_composite');
                }
            });
        }

        // ============================================
        // NOTIFICATIONS TABLE INDEXES
        // ============================================
        // Note: notifications table already has comprehensive indexes created during table creation:
        // - (user_id, is_read)
        // - (user_id, created_at)
        // - (type, created_at)
        // No additional indexes needed for notifications table

        // ============================================
        // TICKET_HISTORY TABLE INDEXES
        // ============================================
        // Note: ticket_history table already has indexes on:
        // - ticket_id (from table creation)
        // - user_id (from table creation)
        // - event_type (from table creation)
        // Only add created_at index if missing
        if (Schema::hasTable('ticket_history')) {
            Schema::table('ticket_history', function (Blueprint $table) {
                // Created at index - for chronological sorting
                if (!$this->hasIndex('ticket_history', 'ticket_history_created_at_index')) {
                    $table->index('created_at', 'ticket_history_created_at_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        
        if (Schema::hasTable('ticket_history')) {
            Schema::table('ticket_history', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'ticket_history_created_at_index');
            });
        }

        // No notifications indexes to drop (they were created with the table)

        if (Schema::hasTable('daily_activities')) {
            Schema::table('daily_activities', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'daily_activities_user_date_composite');
                $this->dropIndexIfExists($table, 'daily_activities_activity_date_index');
                $this->dropIndexIfExists($table, 'daily_activities_ticket_id_index');
                $this->dropIndexIfExists($table, 'daily_activities_user_id_index');
            });
        }

        // No audit_logs indexes to drop (they were created with the table)

        Schema::table('users', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'users_created_at_index');
            $this->dropIndexIfExists($table, 'users_is_active_index');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'tickets_sla_status_composite');
            $this->dropIndexIfExists($table, 'tickets_assigned_status_composite');
            $this->dropIndexIfExists($table, 'tickets_status_priority_created_composite');
            $this->dropIndexIfExists($table, 'tickets_updated_at_index');
            $this->dropIndexIfExists($table, 'tickets_created_at_index');
            $this->dropIndexIfExists($table, 'tickets_sla_due_index');
            $this->dropIndexIfExists($table, 'tickets_assigned_to_index');
        });

        Schema::table('assets', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'assets_assigned_status_composite');
            $this->dropIndexIfExists($table, 'assets_status_created_composite');
            $this->dropIndexIfExists($table, 'assets_created_at_index');
            $this->dropIndexIfExists($table, 'assets_assigned_to_index');
            $this->dropIndexIfExists($table, 'assets_status_id_index');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return isset($indexes[$index]);
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(Blueprint $table, string $index): void
    {
        $tableName = $table->getTable();
        if ($this->hasIndex($tableName, $index)) {
            $table->dropIndex($index);
        }
    }
};
