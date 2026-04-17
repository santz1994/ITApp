<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FormManagementSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_management_tables_exist_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('asset_forms'));
        $this->assertTrue(Schema::hasTable('asset_form_items'));
        $this->assertTrue(Schema::hasTable('asset_form_approvals'));

        $this->assertTrue(Schema::hasColumns('asset_forms', [
            'id',
            'form_number',
            'form_type',
            'status',
            'requested_by',
            'requested_for_user_id',
            'approved_by',
            'asset_id',
            'division_id',
            'location_id',
            'requested_at',
            'approved_at',
            'completed_at',
            'purpose',
            'notes',
            'rejection_reason',
            'metadata',
        ]));

        $this->assertTrue(Schema::hasColumns('asset_form_items', [
            'id',
            'asset_form_id',
            'asset_id',
            'item_name',
            'asset_tag',
            'serial_number',
            'quantity',
            'condition_before',
            'condition_after',
            'notes',
            'metadata',
        ]));

        $this->assertTrue(Schema::hasColumns('asset_form_approvals', [
            'id',
            'asset_form_id',
            'actor_user_id',
            'action',
            'old_status',
            'new_status',
            'action_notes',
            'snapshot',
            'acted_at',
        ]));
    }

    public function test_form_child_tables_define_cascade_foreign_keys_to_asset_forms(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $itemForeignKeys = DB::select("PRAGMA foreign_key_list('asset_form_items')");
            $approvalForeignKeys = DB::select("PRAGMA foreign_key_list('asset_form_approvals')");

            $itemCascadeFound = false;
            foreach ($itemForeignKeys as $foreignKey) {
                if (
                    ($foreignKey->table ?? null) === 'asset_forms' &&
                    ($foreignKey->from ?? null) === 'asset_form_id' &&
                    strtolower((string) ($foreignKey->on_delete ?? '')) === 'cascade'
                ) {
                    $itemCascadeFound = true;
                    break;
                }
            }

            $approvalCascadeFound = false;
            foreach ($approvalForeignKeys as $foreignKey) {
                if (
                    ($foreignKey->table ?? null) === 'asset_forms' &&
                    ($foreignKey->from ?? null) === 'asset_form_id' &&
                    strtolower((string) ($foreignKey->on_delete ?? '')) === 'cascade'
                ) {
                    $approvalCascadeFound = true;
                    break;
                }
            }

            $this->assertTrue($itemCascadeFound);
            $this->assertTrue($approvalCascadeFound);

            return;
        }

        $databaseName = DB::getDatabaseName();

        $itemForeignKey = DB::selectOne(
            "SELECT rc.DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS rc
             INNER JOIN information_schema.KEY_COLUMN_USAGE kcu
                 ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                 AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                 AND rc.TABLE_NAME = kcu.TABLE_NAME
             WHERE kcu.TABLE_SCHEMA = ?
               AND kcu.TABLE_NAME = 'asset_form_items'
               AND kcu.COLUMN_NAME = 'asset_form_id'
               AND kcu.REFERENCED_TABLE_NAME = 'asset_forms'
             LIMIT 1",
            [$databaseName]
        );

        $approvalForeignKey = DB::selectOne(
            "SELECT rc.DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS rc
             INNER JOIN information_schema.KEY_COLUMN_USAGE kcu
                 ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                 AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                 AND rc.TABLE_NAME = kcu.TABLE_NAME
             WHERE kcu.TABLE_SCHEMA = ?
               AND kcu.TABLE_NAME = 'asset_form_approvals'
               AND kcu.COLUMN_NAME = 'asset_form_id'
               AND kcu.REFERENCED_TABLE_NAME = 'asset_forms'
             LIMIT 1",
            [$databaseName]
        );

        $this->assertNotNull($itemForeignKey);
        $this->assertNotNull($approvalForeignKey);
        $this->assertSame('CASCADE', strtoupper((string) $itemForeignKey->DELETE_RULE));
        $this->assertSame('CASCADE', strtoupper((string) $approvalForeignKey->DELETE_RULE));
    }
}
