<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->alignUsersTable();
        $this->alignRolesTable();
        $this->alignRolePermissionsMapping();
        $this->alignTicketsTable();
        $this->alignMeetingRoomBookingsTable();
        $this->alignAssetsTable();
    }

    public function down(): void
    {
        if ($this->isMysql()) {
            DB::statement('DROP VIEW IF EXISTS role_permissions');
        }

        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'assigned_agent_id')) {
            if ($this->foreignKeyExists('tickets', 'tickets_assigned_agent_id_foreign')) {
                Schema::table('tickets', function (Blueprint $table) {
                    $table->dropForeign('tickets_assigned_agent_id_foreign');
                });
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
            if ($this->foreignKeyExists('users', 'users_role_id_foreign')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign('users_role_id_foreign');
                });
            }
        }

        $this->dropColumnsIfExist('assets', [
            'category',
            'asset_type',
            'brand',
            'building',
            'department',
            'code',
            'maintenance_schedule',
            'ticket_history',
            'maintenance_history',
            'disposal_history',
            'lending_history',
            'return_history',
            'location_name',
            'status_name',
        ]);

        $this->dropColumnsIfExist('meeting_room_bookings', [
            'room_id',
            'start_time',
            'end_time',
        ]);

        $this->dropColumnsIfExist('tickets', [
            'title',
            'status',
            'priority',
            'category',
            'assigned_agent_id',
            'sla_due_date',
            'status_history',
            'resolution_notes',
        ]);

        $this->dropColumnsIfExist('role_has_permissions', [
            'created_at',
            'updated_at',
        ]);

        $this->dropColumnsIfExist('roles', [
            'access_level',
        ]);

        $this->dropColumnsIfExist('users', [
            'username',
            'first_name',
            'last_name',
            'role_id',
        ]);
    }

    private function alignUsersTable(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 150)->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 120)->nullable()->after('username');
            }

            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 120)->nullable()->after('first_name');
            }

            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedInteger('role_id')->nullable()->index()->after('last_name');
            }
        });

        if (Schema::hasColumn('users', 'role_id') && Schema::hasTable('roles') && !$this->foreignKeyExists('users', 'users_role_id_foreign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'username') && Schema::hasColumn('users', 'email')) {
            if ($this->isMysql()) {
                DB::statement("UPDATE users SET username = CONCAT(LOWER(SUBSTRING_INDEX(email, '@', 1)), '_', id) WHERE (username IS NULL OR username = '') AND email IS NOT NULL");
            } else {
                DB::statement("UPDATE users SET username = LOWER(CASE WHEN instr(email, '@') > 0 THEN substr(email, 1, instr(email, '@') - 1) ELSE email END) || '_' || id WHERE (username IS NULL OR username = '') AND email IS NOT NULL");
            }
        }

        if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name') && Schema::hasColumn('users', 'name')) {
            if ($this->isMysql()) {
                DB::statement("UPDATE users SET first_name = TRIM(SUBSTRING_INDEX(name, ' ', 1)), last_name = NULLIF(TRIM(SUBSTRING(name, LENGTH(SUBSTRING_INDEX(name, ' ', 1)) + 2)), '') WHERE (first_name IS NULL OR first_name = '') AND name IS NOT NULL AND name <> ''");
            } else {
                DB::statement("UPDATE users SET first_name = name WHERE (first_name IS NULL OR first_name = '') AND name IS NOT NULL AND name <> ''");
            }
        }

        if (
            Schema::hasColumn('users', 'role_id') &&
            Schema::hasTable('model_has_roles') &&
            Schema::hasColumn('model_has_roles', 'model_id') &&
            Schema::hasColumn('model_has_roles', 'role_id') &&
            Schema::hasColumn('model_has_roles', 'model_type')
        ) {
            if ($this->isMysql()) {
                DB::statement("UPDATE users u LEFT JOIN (SELECT model_id, MIN(role_id) AS primary_role_id FROM model_has_roles WHERE model_type = 'App\\\\User' GROUP BY model_id) m ON m.model_id = u.id SET u.role_id = m.primary_role_id WHERE u.role_id IS NULL AND m.primary_role_id IS NOT NULL");
            } else {
                DB::statement("UPDATE users SET role_id = (SELECT MIN(role_id) FROM model_has_roles WHERE model_type = 'App\\User' AND model_id = users.id) WHERE role_id IS NULL");
            }
        }
    }

    private function alignRolesTable(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'access_level')) {
                $table->unsignedTinyInteger('access_level')->default(1)->index()->after('name');
            }
        });

        if (Schema::hasColumn('roles', 'access_level') && Schema::hasColumn('roles', 'name')) {
            DB::statement("UPDATE roles SET access_level = CASE WHEN LOWER(name) = 'guest' THEN 0 WHEN LOWER(name) = 'user' THEN 1 WHEN LOWER(name) = 'receptionist' THEN 2 WHEN LOWER(name) IN ('human resources', 'human_resources', 'human-resources', 'hr') THEN 3 WHEN LOWER(name) IN ('director', 'management') THEN 8 WHEN LOWER(name) IN ('administrator', 'admin') THEN 9 WHEN LOWER(name) IN ('developer', 'super-admin', 'super_admin') THEN 10 ELSE access_level END");
        }
    }

    private function alignRolePermissionsMapping(): void
    {
        if (!Schema::hasTable('role_has_permissions')) {
            return;
        }

        Schema::table('role_has_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('role_has_permissions', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('role_has_permissions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasColumn('role_has_permissions', 'created_at') && Schema::hasColumn('role_has_permissions', 'updated_at')) {
            DB::statement('UPDATE role_has_permissions SET created_at = COALESCE(created_at, NOW()), updated_at = COALESCE(updated_at, NOW())');
        }

        if ($this->isMysql() && !$this->baseTableExists('role_permissions')) {
            DB::statement('CREATE OR REPLACE VIEW role_permissions AS SELECT role_id, permission_id, created_at, updated_at FROM role_has_permissions');
        }
    }

    private function alignTicketsTable(): void
    {
        if (!Schema::hasTable('tickets')) {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'title')) {
                $table->string('title')->nullable()->after('subject');
            }

            if (!Schema::hasColumn('tickets', 'status')) {
                $table->string('status', 50)->nullable()->after('ticket_status_id');
            }

            if (!Schema::hasColumn('tickets', 'priority')) {
                $table->string('priority', 50)->nullable()->after('ticket_priority_id');
            }

            if (!Schema::hasColumn('tickets', 'category')) {
                $table->string('category', 100)->nullable()->after('ticket_type_id');
            }

            if (!Schema::hasColumn('tickets', 'assigned_agent_id')) {
                $table->unsignedInteger('assigned_agent_id')->nullable()->index()->after('assigned_to');
            }

            if (!Schema::hasColumn('tickets', 'sla_due_date')) {
                $table->timestamp('sla_due_date')->nullable()->index()->after('sla_due');
            }

            if (!Schema::hasColumn('tickets', 'status_history')) {
                $table->json('status_history')->nullable()->after('sla_due_date');
            }

            if (!Schema::hasColumn('tickets', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('description');
            }
        });

        if (Schema::hasColumn('tickets', 'assigned_agent_id') && Schema::hasTable('users') && !$this->foreignKeyExists('tickets', 'tickets_assigned_agent_id_foreign')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreign('assigned_agent_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('tickets', 'title') && Schema::hasColumn('tickets', 'subject')) {
            DB::statement("UPDATE tickets SET title = subject WHERE (title IS NULL OR title = '') AND subject IS NOT NULL");
        }

        if (Schema::hasColumn('tickets', 'assigned_agent_id') && Schema::hasColumn('tickets', 'assigned_to')) {
            DB::statement('UPDATE tickets SET assigned_agent_id = assigned_to WHERE assigned_agent_id IS NULL AND assigned_to IS NOT NULL');
        }

        if (Schema::hasColumn('tickets', 'sla_due_date') && Schema::hasColumn('tickets', 'sla_due')) {
            DB::statement('UPDATE tickets SET sla_due_date = sla_due WHERE sla_due_date IS NULL AND sla_due IS NOT NULL');
        }

        if (Schema::hasColumn('tickets', 'status') && Schema::hasColumn('tickets', 'ticket_status_id') && Schema::hasTable('tickets_statuses')) {
            DB::statement('UPDATE tickets t LEFT JOIN tickets_statuses ts ON ts.id = t.ticket_status_id SET t.status = ts.status WHERE (t.status IS NULL OR t.status = "") AND ts.status IS NOT NULL');
        }

        if (Schema::hasColumn('tickets', 'priority') && Schema::hasColumn('tickets', 'ticket_priority_id') && Schema::hasTable('tickets_priorities')) {
            DB::statement('UPDATE tickets t LEFT JOIN tickets_priorities tp ON tp.id = t.ticket_priority_id SET t.priority = tp.priority WHERE (t.priority IS NULL OR t.priority = "") AND tp.priority IS NOT NULL');
        }

        if (Schema::hasColumn('tickets', 'category') && Schema::hasColumn('tickets', 'ticket_type_id') && Schema::hasTable('tickets_types')) {
            DB::statement('UPDATE tickets t LEFT JOIN tickets_types tt ON tt.id = t.ticket_type_id SET t.category = tt.type WHERE (t.category IS NULL OR t.category = "") AND tt.type IS NOT NULL');
        }

        if (Schema::hasColumn('tickets', 'status_history') && Schema::hasTable('ticket_history')) {
            DB::statement("UPDATE tickets t LEFT JOIN (SELECT ticket_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('changed_at', changed_at, 'field_changed', field_changed, 'old_value', old_value, 'new_value', new_value, 'event_type', event_type, 'changed_by_user_id', changed_by_user_id) ORDER BY changed_at SEPARATOR ','), ']') AS history_json FROM ticket_history GROUP BY ticket_id) h ON h.ticket_id = t.id SET t.status_history = h.history_json WHERE t.status_history IS NULL AND h.history_json IS NOT NULL");
        }
    }

    private function alignMeetingRoomBookingsTable(): void
    {
        if (!Schema::hasTable('meeting_room_bookings')) {
            return;
        }

        Schema::table('meeting_room_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('meeting_room_bookings', 'room_id')) {
                $table->unsignedInteger('room_id')->nullable()->index()->after('room_name');
            }

            if (!Schema::hasColumn('meeting_room_bookings', 'start_time')) {
                $table->dateTime('start_time')->nullable()->after('start_datetime');
            }

            if (!Schema::hasColumn('meeting_room_bookings', 'end_time')) {
                $table->dateTime('end_time')->nullable()->after('end_datetime');
            }
        });

        if (Schema::hasColumn('meeting_room_bookings', 'start_time') && Schema::hasColumn('meeting_room_bookings', 'start_datetime')) {
            DB::statement('UPDATE meeting_room_bookings SET start_time = start_datetime WHERE start_time IS NULL AND start_datetime IS NOT NULL');
        }

        if (Schema::hasColumn('meeting_room_bookings', 'end_time') && Schema::hasColumn('meeting_room_bookings', 'end_datetime')) {
            DB::statement('UPDATE meeting_room_bookings SET end_time = end_datetime WHERE end_time IS NULL AND end_datetime IS NOT NULL');
        }
    }

    private function alignAssetsTable(): void
    {
        if (!Schema::hasTable('assets')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'category')) {
                $table->string('category', 100)->nullable()->after('name');
            }

            if (!Schema::hasColumn('assets', 'asset_type')) {
                $table->string('asset_type', 100)->nullable()->after('category');
            }

            if (!Schema::hasColumn('assets', 'brand')) {
                $table->string('brand', 100)->nullable()->after('asset_type');
            }

            if (!Schema::hasColumn('assets', 'building')) {
                $table->string('building', 100)->nullable()->after('purchase_date');
            }

            if (!Schema::hasColumn('assets', 'department')) {
                $table->string('department', 100)->nullable()->after('building');
            }

            if (!Schema::hasColumn('assets', 'code')) {
                $table->string('code', 128)->nullable()->index()->after('asset_tag');
            }

            if (!Schema::hasColumn('assets', 'maintenance_schedule')) {
                $table->string('maintenance_schedule', 50)->nullable()->after('warranty_months');
            }

            if (!Schema::hasColumn('assets', 'ticket_history')) {
                $table->json('ticket_history')->nullable()->after('maintenance_schedule');
            }

            if (!Schema::hasColumn('assets', 'maintenance_history')) {
                $table->json('maintenance_history')->nullable()->after('ticket_history');
            }

            if (!Schema::hasColumn('assets', 'disposal_history')) {
                $table->json('disposal_history')->nullable()->after('maintenance_history');
            }

            if (!Schema::hasColumn('assets', 'lending_history')) {
                $table->json('lending_history')->nullable()->after('disposal_history');
            }

            if (!Schema::hasColumn('assets', 'return_history')) {
                $table->json('return_history')->nullable()->after('lending_history');
            }

            // Keep relation-safe names to avoid colliding with existing Eloquent relations: location() and status().
            if (!Schema::hasColumn('assets', 'location_name')) {
                $table->string('location_name', 100)->nullable()->after('department');
            }

            if (!Schema::hasColumn('assets', 'status_name')) {
                $table->string('status_name', 100)->nullable()->after('status_id');
            }
        });

        if (Schema::hasColumn('assets', 'asset_type') && Schema::hasColumn('assets', 'category') && Schema::hasColumn('assets', 'brand')) {
            DB::statement('UPDATE assets a LEFT JOIN asset_models am ON am.id = a.model_id LEFT JOIN asset_types at ON at.id = am.asset_type_id LEFT JOIN manufacturers m ON m.id = am.manufacturer_id SET a.asset_type = COALESCE(a.asset_type, at.type_name), a.category = COALESCE(a.category, at.type_name), a.brand = COALESCE(a.brand, m.name) WHERE (a.asset_type IS NULL OR a.category IS NULL OR a.brand IS NULL)');
        }

        if (Schema::hasColumn('assets', 'building') && Schema::hasColumn('assets', 'location_name') && Schema::hasColumn('assets', 'department') && Schema::hasColumn('assets', 'status_name')) {
            DB::statement('UPDATE assets a LEFT JOIN locations l ON l.id = a.location_id LEFT JOIN divisions d ON d.id = a.division_id LEFT JOIN statuses s ON s.id = a.status_id SET a.building = COALESCE(a.building, l.building), a.location_name = COALESCE(a.location_name, l.location_name), a.department = COALESCE(a.department, d.name), a.status_name = COALESCE(a.status_name, s.name) WHERE (a.building IS NULL OR a.location_name IS NULL OR a.department IS NULL OR a.status_name IS NULL)');
        }

        if (Schema::hasColumn('assets', 'code') && Schema::hasColumn('assets', 'asset_tag')) {
            DB::statement("UPDATE assets SET code = asset_tag WHERE (code IS NULL OR code = '') AND asset_tag IS NOT NULL");
        }

        if (Schema::hasColumn('assets', 'ticket_history') && Schema::hasTable('ticket_assets') && Schema::hasTable('tickets')) {
            DB::statement("UPDATE assets a LEFT JOIN (SELECT ta.asset_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('ticket_id', t.id, 'ticket_code', t.ticket_code, 'subject', t.subject, 'status_id', t.ticket_status_id, 'priority_id', t.ticket_priority_id, 'created_at', t.created_at) ORDER BY t.created_at SEPARATOR ','), ']') AS history_json FROM ticket_assets ta INNER JOIN tickets t ON t.id = ta.ticket_id GROUP BY ta.asset_id) h ON h.asset_id = a.id SET a.ticket_history = h.history_json WHERE a.ticket_history IS NULL AND h.history_json IS NOT NULL");
        }

        if (Schema::hasColumn('assets', 'maintenance_history') && Schema::hasTable('asset_maintenance_logs')) {
            DB::statement("UPDATE assets a LEFT JOIN (SELECT aml.asset_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('id', aml.id, 'ticket_id', aml.ticket_id, 'maintenance_type', aml.maintenance_type, 'description', aml.description, 'status', aml.status, 'scheduled_at', aml.scheduled_at, 'completed_at', aml.completed_at, 'performed_by', aml.performed_by) ORDER BY aml.created_at SEPARATOR ','), ']') AS history_json FROM asset_maintenance_logs aml GROUP BY aml.asset_id) h ON h.asset_id = a.id SET a.maintenance_history = h.history_json WHERE a.maintenance_history IS NULL AND h.history_json IS NOT NULL");
        }

        if (Schema::hasColumn('assets', 'disposal_history') && Schema::hasTable('asset_lifecycle_events')) {
            DB::statement("UPDATE assets a LEFT JOIN (SELECT ale.asset_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('id', ale.id, 'event_type', ale.event_type, 'description', ale.description, 'metadata', ale.metadata, 'event_date', ale.event_date, 'user_id', ale.user_id) ORDER BY ale.event_date SEPARATOR ','), ']') AS history_json FROM asset_lifecycle_events ale WHERE ale.event_type = 'disposal' GROUP BY ale.asset_id) h ON h.asset_id = a.id SET a.disposal_history = h.history_json WHERE a.disposal_history IS NULL AND h.history_json IS NOT NULL");
        }

        if (Schema::hasColumn('assets', 'lending_history') && Schema::hasTable('asset_lifecycle_events')) {
            DB::statement("UPDATE assets a LEFT JOIN (SELECT ale.asset_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('id', ale.id, 'event_type', ale.event_type, 'description', ale.description, 'metadata', ale.metadata, 'event_date', ale.event_date, 'user_id', ale.user_id) ORDER BY ale.event_date SEPARATOR ','), ']') AS history_json FROM asset_lifecycle_events ale WHERE ale.event_type IN ('transfer', 'deployment') GROUP BY ale.asset_id) h ON h.asset_id = a.id SET a.lending_history = h.history_json WHERE a.lending_history IS NULL AND h.history_json IS NOT NULL");
        }

        if (Schema::hasColumn('assets', 'return_history') && Schema::hasTable('asset_lifecycle_events')) {
            DB::statement("UPDATE assets a LEFT JOIN (SELECT ale.asset_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('id', ale.id, 'event_type', ale.event_type, 'description', ale.description, 'metadata', ale.metadata, 'event_date', ale.event_date, 'user_id', ale.user_id) ORDER BY ale.event_date SEPARATOR ','), ']') AS history_json FROM asset_lifecycle_events ale WHERE ale.event_type = 'found' GROUP BY ale.asset_id) h ON h.asset_id = a.id SET a.return_history = h.history_json WHERE a.return_history IS NULL AND h.history_json IS NOT NULL");
        }
    }

    private function dropColumnsIfExist(string $tableName, array $columns): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $column) {
            if (Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function isMysql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        if (!$this->isMysql()) {
            return false;
        }

        $databaseName = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate_count FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = "FOREIGN KEY"',
            [$databaseName, $tableName, $constraintName]
        );

        return ((int) ($result->aggregate_count ?? 0)) > 0;
    }

    private function baseTableExists(string $tableName): bool
    {
        if (!$this->isMysql()) {
            return Schema::hasTable($tableName);
        }

        $databaseName = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate_count FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND TABLE_TYPE = "BASE TABLE"',
            [$databaseName, $tableName]
        );

        return ((int) ($result->aggregate_count ?? 0)) > 0;
    }
};
