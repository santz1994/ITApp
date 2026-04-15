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
     * Add comprehensive database indexes for performance optimization.
     * Focus on frequently queried columns and composite indexes for common query patterns.
     */
    public function up(): void
    {
        // ===================================================================
        // TICKETS TABLE - High-traffic table for support tickets
        // ===================================================================
        
        // Composite index for searching tickets by status and priority
        // Used in: Dashboard filters, ticket list views
        $this->addIndexIfNotExists('tickets', 'tickets_status_priority_idx', ['ticket_status_id', 'ticket_priority_id']);
        
        // Composite index for finding tickets by type and status
        // Used in: Maintenance tracking, ticket filtering
        $this->addIndexIfNotExists('tickets', 'tickets_type_status_idx', ['ticket_type_id', 'ticket_status_id']);
        
        // Composite index for user's assigned tickets with status
        // Used in: "My Tickets" view, workload reports
        $this->addIndexIfNotExists('tickets', 'tickets_assigned_status_created_idx', ['assigned_to', 'ticket_status_id', 'created_at']);
        
        // Index for finding tickets by creator and date
        // Used in: User activity reports, ticket history
        $this->addIndexIfNotExists('tickets', 'tickets_user_created_idx', ['user_id', 'created_at']);
        
        // Index for location-based ticket queries
        // Used in: Location dashboard, reports by location
        $this->addIndexIfNotExists('tickets', 'tickets_location_status_idx', ['location_id', 'ticket_status_id']);
        
        // Index for asset-related tickets with status
        // Used in: Asset maintenance history, asset detail pages
        $this->addIndexIfNotExists('tickets', 'tickets_asset_status_idx', ['asset_id', 'ticket_status_id']);
        
        // Index for searching tickets by code (partial matches)
        // Used in: Global search, quick ticket lookup
        $this->addIndexIfNotExists('tickets', 'tickets_code_idx', ['ticket_code']);
        
        // Index for finding tickets by closed date
        // Used in: SLA reports, resolution time analytics
        $this->addIndexIfNotExists('tickets', 'tickets_closed_idx', ['closed']);
        
        
        // ===================================================================
        // ASSETS TABLE - Core inventory management table
        // ===================================================================
        
        // Composite index for status + assigned user queries
        // Used in: "Assets I Manage" view, assignment reports
        $this->addIndexIfNotExists('assets', 'assets_status_assigned_idx', ['status_id', 'assigned_to']);
        
        // Composite index for division + status queries
        // Used in: Department asset reports, division dashboards
        $this->addIndexIfNotExists('assets', 'assets_division_status_idx', ['division_id', 'status_id']);
        
        // Composite index for model + status queries
        // Used in: Model-specific reports, inventory by model
        $this->addIndexIfNotExists('assets', 'assets_model_status_idx', ['model_id', 'status_id']);
        
        // Index for supplier queries
        // Used in: Supplier reports, vendor management
        $this->addIndexIfNotExists('assets', 'assets_supplier_idx', ['supplier_id']);
        
        // Index for serial number lookups (unique identifier)
        // Used in: Asset lookup, validation checks
        $this->addIndexIfNotExists('assets', 'assets_serial_number_idx', ['serial_number']);
        
        // Index for IP address lookups
        // Used in: Network management, IP conflict detection
        $this->addIndexIfNotExists('assets', 'assets_ip_address_idx', ['ip_address']);
        
        // Index for MAC address lookups
        // Used in: Network management, device identification
        $this->addIndexIfNotExists('assets', 'assets_mac_address_idx', ['mac_address']);
        
        // Index for warranty tracking
        // Used in: Warranty expiration reports, maintenance planning
        $this->addIndexIfNotExists('assets', 'assets_warranty_type_idx', ['warranty_type_id']);
        
        // Composite index for purchase date and warranty months
        // Used in: Warranty expiration calculations, age reports
        $this->addIndexIfNotExists('assets', 'assets_purchase_warranty_idx', ['purchase_date', 'warranty_months']);
        
        // Index for QR code lookups
        // Used in: Mobile scanning, quick asset lookup
        $this->addIndexIfNotExists('assets', 'assets_qr_code_idx', ['qr_code']);
        
        
        // ===================================================================
        // USERS TABLE - Authentication and user management
        // ===================================================================
        
        // Index for email lookups (already unique, but explicit index helps)
        // Used in: Login, user search, validation
        $this->addIndexIfNotExists('users', 'users_email_idx', ['email']);
        
        // Index for name searches
        // Used in: User search, autocomplete, assignment selectors
        $this->addIndexIfNotExists('users', 'users_name_idx', ['name']);
        
        // Index for API token lookups
        // Used in: API authentication
        $this->addIndexIfNotExists('users', 'users_api_token_idx', ['api_token']);
        
        // Note: division_id and is_active columns are added in a later migration (2025_10_15_150922)
        // These indexes will be created after that migration runs
        // Skipping for now to avoid errors
        
        // Index for created_at for user registration reports
        // Used in: User growth reports, registration analytics
        $this->addIndexIfNotExists('users', 'users_created_at_idx', ['created_at']);
        
        
        // ===================================================================
        // ASSET_MODELS TABLE - Asset model management
        // ===================================================================
        
        // Index for manufacturer queries
        // Used in: Filtering models by manufacturer
        $this->addIndexIfNotExists('asset_models', 'asset_models_manufacturer_idx', ['manufacturer_id']);
        
        // Index for asset type queries
        // Used in: Filtering models by type (laptop, desktop, etc.)
        $this->addIndexIfNotExists('asset_models', 'asset_models_type_idx', ['asset_type_id']);
        
        // Composite index for manufacturer + type
        // Used in: Model selection dropdowns, inventory categorization
        $this->addIndexIfNotExists('asset_models', 'asset_models_mfg_type_idx', ['manufacturer_id', 'asset_type_id']);
        
        
        // ===================================================================
        // NOTIFICATIONS TABLE - User notifications
        // ===================================================================
        
        // Composite index for user's unread notifications
        // Used in: Notification dropdown, bell icon badge count
        $this->addIndexIfNotExists('notifications', 'notifications_user_read_idx', ['user_id', 'is_read']);
        
        // Index for notification type
        // Used in: Filtering notifications by type
        $this->addIndexIfNotExists('notifications', 'notifications_type_idx', ['type']);
        
        // Index for created_at for ordering notifications
        // Used in: Notification list ordering
        $this->addIndexIfNotExists('notifications', 'notifications_created_at_idx', ['created_at']);
        
        
        // ===================================================================
        // ACTIVITY_LOGS TABLE - Audit trail
        // ===================================================================
        
        // Composite index for causer activity logs (who did the action)
        // Used in: User activity reports, audit trails
        $this->addIndexIfNotExists('activity_logs', 'activity_logs_causer_created_idx', ['causer_id', 'created_at']);
        
        // Composite index for model activity logs
        // Used in: Tracking changes to specific records
        $this->addIndexIfNotExists('activity_logs', 'activity_logs_subject_idx', ['subject_type', 'subject_id']);
        
        // Index for event type (replaces 'action')
        // Used in: Filtering logs by event (created, updated, deleted)
        $this->addIndexIfNotExists('activity_logs', 'activity_logs_event_idx', ['event']);
        
        // Composite index for causer (who made the change)
        // Used in: User accountability reports
        $this->addIndexIfNotExists('activity_logs', 'activity_logs_causer_idx', ['causer_type', 'causer_id']);
        
        
        // ===================================================================
        // FILE_ATTACHMENTS TABLE - File management
        // ===================================================================
        
        // Composite index for attachable polymorphic relationship
        // Used in: Loading attachments for tickets/assets
        $this->addIndexIfNotExists('file_attachments', 'file_attachments_attachable_idx', ['attachable_type', 'attachable_id']);
        
        // Index for uploaded_by user
        // Used in: User file upload reports
        $this->addIndexIfNotExists('file_attachments', 'file_attachments_uploaded_by_idx', ['uploaded_by']);
        
        // Index for file type filtering
        // Used in: Filtering attachments by type (image, document, etc.)
        $this->addIndexIfNotExists('file_attachments', 'file_attachments_file_type_idx', ['file_type']);
        
        
        // ===================================================================
        // ASSET_MAINTENANCE_LOGS TABLE - Maintenance tracking
        // ===================================================================
        
        // Composite index for asset maintenance history (uses completed_at, not performed_at)
        // Used in: Asset maintenance timeline
        $this->addIndexIfNotExists('asset_maintenance_logs', 'maintenance_logs_asset_completed_idx', ['asset_id', 'completed_at']);
        
        // Index for maintenance type
        // Used in: Filtering by maintenance type (preventive, corrective, etc.)
        $this->addIndexIfNotExists('asset_maintenance_logs', 'maintenance_logs_type_idx', ['maintenance_type']);
        
        // Index for maintenance status
        // Used in: Finding scheduled/in-progress/completed maintenance
        $this->addIndexIfNotExists('asset_maintenance_logs', 'maintenance_logs_status_idx', ['status']);
        
        // Index for performed_by user
        // Used in: Technician workload reports
        $this->addIndexIfNotExists('asset_maintenance_logs', 'maintenance_logs_performed_by_idx', ['performed_by']);
        
        // Composite index for ticket-related maintenance
        // Used in: Linking maintenance to support tickets
        $this->addIndexIfNotExists('asset_maintenance_logs', 'maintenance_logs_ticket_idx', ['ticket_id', 'status']);
        
        // Index for scheduled maintenance date
        // Used in: Upcoming maintenance schedules
        $this->addIndexIfNotExists('asset_maintenance_logs', 'maintenance_logs_scheduled_date_idx', ['scheduled_at']);
        
        
        // ===================================================================
        // LOCATIONS TABLE - Physical locations
        // ===================================================================
        
        // Index for location name searches (column is 'location_name', not 'name')
        // Used in: Location dropdowns, search
        $this->addIndexIfNotExists('locations', 'locations_name_idx', ['location_name']);
        
        
        // ===================================================================
        // DIVISIONS TABLE - Organizational units
        // ===================================================================
        
        // Index for division name searches
        // Used in: Division dropdowns, filtering
        $this->addIndexIfNotExists('divisions', 'divisions_name_idx', ['name']);
        
        
        // ===================================================================
        // SLA_POLICIES TABLE - Service Level Agreements
        // ===================================================================
        
        // Index for active SLA policies
        // Used in: Finding applicable SLA rules
        $this->addIndexIfNotExists('sla_policies', 'sla_policies_is_active_idx', ['is_active']);
        
        // Composite index for priority-based SLA lookup
        // Used in: SLA policy matching
        $this->addIndexIfNotExists('sla_policies', 'sla_policies_priority_active_idx', ['priority_id', 'is_active']);
        
        
        // ===================================================================
        // TICKETS_ENTRIES TABLE - Ticket comments/updates
        // ===================================================================
        
        // Composite index for ticket entries with timestamp
        // Used in: Loading ticket conversation history
        $this->addIndexIfNotExists('tickets_entries', 'tickets_entries_ticket_created_idx', ['ticket_id', 'created_at']);
        
        // Index for entries by user
        // Used in: User activity reports
        $this->addIndexIfNotExists('tickets_entries', 'tickets_entries_user_idx', ['user_id']);
        
        
        // ===================================================================
        // MODEL_HAS_ROLES TABLE - Spatie Permission (User Roles)
        // ===================================================================
        
        // Composite index for finding user roles
        // Used in: Permission checks, role assignments
        $this->addIndexIfNotExists('model_has_roles', 'model_has_roles_model_idx', ['model_type', 'model_id']);
        
        // Index for role_id lookups
        // Used in: Finding all users with a specific role
        $this->addIndexIfNotExists('model_has_roles', 'model_has_roles_role_idx', ['role_id']);
        
        
        // ===================================================================
        // MODEL_HAS_PERMISSIONS TABLE - Spatie Permission (User Permissions)
        // ===================================================================
        
        // Composite index for finding user permissions
        // Used in: Permission checks
        $this->addIndexIfNotExists('model_has_permissions', 'model_has_permissions_model_idx', ['model_type', 'model_id']);
        
        
        // ===================================================================
        // SESSIONS TABLE - User sessions
        // ===================================================================
        
        // Index for session lookup by user
        // Used in: Finding active user sessions
        $this->addIndexIfNotExists('sessions', 'sessions_user_id_idx', ['user_id']);
        
        // Index for session cleanup (expired sessions)
        // Used in: Session garbage collection
        $this->addIndexIfNotExists('sessions', 'sessions_last_activity_idx', ['last_activity']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all indexes created in up() method
        
        // Tickets
        $this->dropIndexIfExists('tickets', 'tickets_status_priority_idx');
        $this->dropIndexIfExists('tickets', 'tickets_type_status_idx');
        $this->dropIndexIfExists('tickets', 'tickets_assigned_status_created_idx');
        $this->dropIndexIfExists('tickets', 'tickets_user_created_idx');
        $this->dropIndexIfExists('tickets', 'tickets_location_status_idx');
        $this->dropIndexIfExists('tickets', 'tickets_asset_status_idx');
        $this->dropIndexIfExists('tickets', 'tickets_code_idx');
        $this->dropIndexIfExists('tickets', 'tickets_closed_idx');
        
        // Assets
        $this->dropIndexIfExists('assets', 'assets_status_assigned_idx');
        $this->dropIndexIfExists('assets', 'assets_division_status_idx');
        $this->dropIndexIfExists('assets', 'assets_model_status_idx');
        $this->dropIndexIfExists('assets', 'assets_supplier_idx');
        $this->dropIndexIfExists('assets', 'assets_serial_number_idx');
        $this->dropIndexIfExists('assets', 'assets_ip_address_idx');
        $this->dropIndexIfExists('assets', 'assets_mac_address_idx');
        $this->dropIndexIfExists('assets', 'assets_warranty_type_idx');
        $this->dropIndexIfExists('assets', 'assets_purchase_warranty_idx');
        $this->dropIndexIfExists('assets', 'assets_qr_code_idx');
        
        // Users
        $this->dropIndexIfExists('users', 'users_email_idx');
        $this->dropIndexIfExists('users', 'users_name_idx');
        $this->dropIndexIfExists('users', 'users_api_token_idx');
        // division_id indexes skipped (added in later migration)
        $this->dropIndexIfExists('users', 'users_created_at_idx');
        
        // Asset Models
        $this->dropIndexIfExists('asset_models', 'asset_models_manufacturer_idx');
        $this->dropIndexIfExists('asset_models', 'asset_models_type_idx');
        $this->dropIndexIfExists('asset_models', 'asset_models_mfg_type_idx');
        
        // Notifications
        $this->dropIndexIfExists('notifications', 'notifications_user_read_idx');
        $this->dropIndexIfExists('notifications', 'notifications_type_idx');
        $this->dropIndexIfExists('notifications', 'notifications_created_at_idx');
        
        // Activity Logs
        $this->dropIndexIfExists('activity_logs', 'activity_logs_causer_created_idx');
        $this->dropIndexIfExists('activity_logs', 'activity_logs_subject_idx');
        $this->dropIndexIfExists('activity_logs', 'activity_logs_event_idx');
        $this->dropIndexIfExists('activity_logs', 'activity_logs_causer_idx');
        
        // File Attachments
        $this->dropIndexIfExists('file_attachments', 'file_attachments_attachable_idx');
        $this->dropIndexIfExists('file_attachments', 'file_attachments_uploaded_by_idx');
        $this->dropIndexIfExists('file_attachments', 'file_attachments_file_type_idx');
        
        // Maintenance Logs
        $this->dropIndexIfExists('asset_maintenance_logs', 'maintenance_logs_asset_completed_idx');
        $this->dropIndexIfExists('asset_maintenance_logs', 'maintenance_logs_type_idx');
        $this->dropIndexIfExists('asset_maintenance_logs', 'maintenance_logs_status_idx');
        $this->dropIndexIfExists('asset_maintenance_logs', 'maintenance_logs_performed_by_idx');
        $this->dropIndexIfExists('asset_maintenance_logs', 'maintenance_logs_ticket_idx');
        $this->dropIndexIfExists('asset_maintenance_logs', 'maintenance_logs_scheduled_date_idx');
        
        // Locations
        $this->dropIndexIfExists('locations', 'locations_name_idx');
        
        // Divisions
        $this->dropIndexIfExists('divisions', 'divisions_name_idx');
        
        // SLA Policies
        $this->dropIndexIfExists('sla_policies', 'sla_policies_is_active_idx');
        $this->dropIndexIfExists('sla_policies', 'sla_policies_priority_active_idx');
        
        // Tickets Entries
        $this->dropIndexIfExists('tickets_entries', 'tickets_entries_ticket_created_idx');
        $this->dropIndexIfExists('tickets_entries', 'tickets_entries_user_idx');
        
        // Spatie Permissions
        $this->dropIndexIfExists('model_has_roles', 'model_has_roles_model_idx');
        $this->dropIndexIfExists('model_has_roles', 'model_has_roles_role_idx');
        $this->dropIndexIfExists('model_has_permissions', 'model_has_permissions_model_idx');
        
        // Sessions
        $this->dropIndexIfExists('sessions', 'sessions_user_id_idx');
        $this->dropIndexIfExists('sessions', 'sessions_last_activity_idx');
    }
    
    /**
     * Add index only if it doesn't already exist
     * Prevents errors when running migration multiple times
     */
    private function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        try {
            // Check if table exists
            if (!Schema::hasTable($table)) {
                echo "⚠️  Table '{$table}' does not exist. Skipping index '{$indexName}'.\n";
                return;
            }
            
            // Check if index already exists - database-agnostic approach
            $exists = false;
            
            if (DB::connection()->getDriverName() === 'sqlite') {
                // SQLite: Use PRAGMA index_list
                $indexes = DB::select("PRAGMA index_list({$table})");
                $exists = collect($indexes)->contains('name', $indexName);
            } else {
                // MySQL/MariaDB: Use SHOW INDEX
                $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
                $exists = !empty($indexes);
            }
            
            if (!$exists) {
                Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                    $table->index($columns, $indexName);
                });
                echo "✅ Added index: {$indexName}\n";
            } else {
                echo "⏭️  Index '{$indexName}' already exists. Skipping.\n";
            }
        } catch (\Exception $e) {
            echo "❌ Could not add index '{$indexName}' on table '{$table}': " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Drop index only if it exists
     * Prevents errors during rollback
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            // Check if table exists
            if (!Schema::hasTable($table)) {
                return;
            }
            
            // Check if index exists - database-agnostic approach
            $exists = false;
            
            if (DB::connection()->getDriverName() === 'sqlite') {
                // SQLite: Use PRAGMA index_list
                $indexes = DB::select("PRAGMA index_list({$table})");
                $exists = collect($indexes)->contains('name', $indexName);
            } else {
                // MySQL/MariaDB: Use SHOW INDEX
                $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
                $exists = !empty($indexes);
            }
            
            if ($exists) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
                echo "✅ Dropped index: {$indexName}\n";
            }
        } catch (\Exception $e) {
            // Silently ignore errors during rollback
        }
    }
};
