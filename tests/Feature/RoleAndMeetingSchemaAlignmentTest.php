<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleAndMeetingSchemaAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_table_supports_project_lv_10_access_level(): void
    {
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasColumn('roles', 'access_level'));

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'lv10-test-role',
            'guard_name' => 'web',
            'access_level' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $roleId,
            'access_level' => 10,
        ]);
    }

    public function test_meeting_room_lcd_overlap_index_exists(): void
    {
        $this->assertTrue(Schema::hasTable('meeting_room_bookings'));
        $this->assertTrue($this->indexExists('meeting_room_bookings', 'idx_meeting_room_lcd_overlap'));
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $databaseName = DB::getDatabaseName();

            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate_count FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$databaseName, $tableName, $indexName]
            );

            return ((int) ($result->aggregate_count ?? 0)) > 0;
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
}
