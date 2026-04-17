<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->alignRoleAccessLevelsToProjectHierarchy();
        $this->addMeetingRoomLcdOverlapIndex();
    }

    public function down(): void
    {
        if (Schema::hasTable('meeting_room_bookings') && $this->indexExists('meeting_room_bookings', 'idx_meeting_room_lcd_overlap')) {
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->dropIndex('idx_meeting_room_lcd_overlap');
            });
        }
    }

    private function alignRoleAccessLevelsToProjectHierarchy(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasColumn('roles', 'access_level') || !Schema::hasColumn('roles', 'name')) {
            return;
        }

        // Align legacy role values to Project.md LV hierarchy.
        DB::statement("UPDATE roles SET access_level = CASE WHEN LOWER(name) = 'guest' THEN 0 WHEN LOWER(name) = 'user' THEN 1 WHEN LOWER(name) = 'receptionist' THEN 2 WHEN LOWER(name) IN ('human resources', 'human_resources', 'human-resources', 'hr') THEN 3 WHEN LOWER(name) IN ('director', 'management') THEN 8 WHEN LOWER(name) IN ('administrator', 'admin') THEN 9 WHEN LOWER(name) IN ('developer', 'super-admin', 'super_admin') THEN 10 ELSE access_level END");

        if ($this->isMysql()) {
            DB::statement("ALTER TABLE roles MODIFY access_level TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'LV hierarchy: 0=Guest,1=User,2=Receptionist,3=Human Resources,8=Director,9=Administrator,10=Developer'");
        }
    }

    private function addMeetingRoomLcdOverlapIndex(): void
    {
        if (!Schema::hasTable('meeting_room_bookings') || $this->indexExists('meeting_room_bookings', 'idx_meeting_room_lcd_overlap')) {
            return;
        }

        if ($this->columnsExist('meeting_room_bookings', ['room_id', 'status', 'start_datetime', 'end_datetime'])) {
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->index(['room_id', 'status', 'start_datetime', 'end_datetime'], 'idx_meeting_room_lcd_overlap');
            });

            return;
        }

        if ($this->columnsExist('meeting_room_bookings', ['room_name', 'status', 'start_datetime', 'end_datetime'])) {
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->index(['room_name', 'status', 'start_datetime', 'end_datetime'], 'idx_meeting_room_lcd_overlap');
            });
        }
    }

    private function columnsExist(string $tableName, array $columns): bool
    {
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }

    private function isMysql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        if ($this->isMysql()) {
            $databaseName = DB::getDatabaseName();

            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate_count FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$databaseName, $tableName, $indexName]
            );

            return ((int) ($result->aggregate_count ?? 0)) > 0;
        }

        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
};
