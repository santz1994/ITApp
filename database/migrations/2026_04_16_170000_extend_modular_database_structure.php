<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->alignLocationsTable();
        $this->alignAssetTypesTable();
        $this->createAssetCategoriesTable();
        $this->createDepartmentsTable();
        $this->createMeetingRoomsTable();
        $this->extendAssetsTable();
        $this->extendAssetRequestsTable();
        $this->alignMeetingRoomBookingsWithRooms();
        $this->addPerformanceIndexes();
    }

    public function down(): void
    {
        if (Schema::hasTable('meeting_room_bookings') && Schema::hasColumn('meeting_room_bookings', 'room_id')) {
            if ($this->foreignKeyExists('meeting_room_bookings', 'meeting_room_bookings_room_id_foreign')) {
                Schema::table('meeting_room_bookings', function (Blueprint $table) {
                    $table->dropForeign('meeting_room_bookings_room_id_foreign');
                });
            }
        }

        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'assigned_user_id')) {
            if ($this->foreignKeyExists('assets', 'assets_assigned_user_id_foreign')) {
                Schema::table('assets', function (Blueprint $table) {
                    $table->dropForeign('assets_assigned_user_id_foreign');
                });
            }
        }

        $this->dropIndexIfExists('users', 'idx_users_role_active_login');
        $this->dropIndexIfExists('tickets', 'idx_tickets_status_priority_sla');
        $this->dropIndexIfExists('tickets', 'idx_tickets_user_status_created');
        $this->dropIndexIfExists('tickets', 'idx_tickets_assigned_agent_status');
        $this->dropIndexIfExists('assets', 'idx_assets_lookup_filters');
        $this->dropIndexIfExists('assets', 'idx_assets_assigned_maintenance');
        $this->dropIndexIfExists('asset_requests', 'idx_asset_requests_requester_status_created');
        $this->dropIndexIfExists('asset_requests', 'idx_asset_requests_procurement');
        $this->dropIndexIfExists('meeting_room_bookings', 'idx_meeting_room_booking_window');
        $this->dropIndexIfExists('meeting_rooms', 'idx_meeting_rooms_status');

        $this->dropColumnsIfExist('assets', [
            'rfid_tag',
            'warranty_expiration_date',
            'supplier_name',
            'cost',
            'depreciation_schedule',
            'location_history',
            'assigned_user_id',
            'maintenance_status',
            'maintenance_notes',
            'disposal_status',
            'disposal_notes',
            'lending_status',
            'lending_notes',
            'return_status',
            'return_notes',
            'image',
        ]);

        $this->dropColumnsIfExist('asset_requests', [
            'item_name',
            'category',
            'quantity',
            'approval_history',
            'vendor',
            'estimated_cost',
            'actual_cost',
            'delivery_date',
            'receipt_image',
            'purchase_notes',
            'purchase_history',
        ]);

        $this->dropColumnsIfExist('locations', [
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);

        $this->dropColumnsIfExist('asset_types', [
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);

        if (Schema::hasTable('meeting_rooms')) {
            Schema::drop('meeting_rooms');
        }

        if (Schema::hasTable('departments')) {
            Schema::drop('departments');
        }

        if (Schema::hasTable('asset_categories')) {
            Schema::drop('asset_categories');
        }
    }

    private function alignLocationsTable(): void
    {
        if (!Schema::hasTable('locations')) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'name')) {
                $table->string('name', 120)->nullable()->index()->after('location_name');
            }

            if (!Schema::hasColumn('locations', 'description')) {
                $table->string('description', 255)->nullable()->after('name');
            }

            if (!Schema::hasColumn('locations', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('locations', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (!$this->isMysql()) {
            return;
        }

        DB::statement("UPDATE locations SET name = COALESCE(name, location_name) WHERE name IS NULL OR name = ''");
        DB::statement("UPDATE locations SET description = COALESCE(description, CONCAT('Building: ', building, ', Office: ', office)) WHERE (description IS NULL OR description = '') AND building IS NOT NULL AND office IS NOT NULL");
        DB::statement("UPDATE locations SET created_at = COALESCE(created_at, NOW()), updated_at = COALESCE(updated_at, NOW())");
    }

    private function alignAssetTypesTable(): void
    {
        if (!Schema::hasTable('asset_types')) {
            return;
        }

        Schema::table('asset_types', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_types', 'name')) {
                $table->string('name', 100)->nullable()->index()->after('type_name');
            }

            if (!Schema::hasColumn('asset_types', 'description')) {
                $table->string('description', 255)->nullable()->after('name');
            }

            if (!Schema::hasColumn('asset_types', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('asset_types', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (!$this->isMysql()) {
            return;
        }

        DB::statement("UPDATE asset_types SET name = COALESCE(name, type_name) WHERE name IS NULL OR name = ''");
        DB::statement("UPDATE asset_types SET created_at = COALESCE(created_at, NOW()), updated_at = COALESCE(updated_at, NOW())");
    }

    private function createAssetCategoriesTable(): void
    {
        if (!Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('code', 10)->unique();
                $table->string('description', 255)->nullable();
                $table->timestamps();
                $table->unique('name', 'asset_categories_name_unique');
            });
        }

        DB::table('asset_categories')->upsert([
            [
                'name' => 'Asset',
                'code' => 'AST',
                'description' => 'Primary company assets and equipment.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sparepart',
                'code' => 'SPR',
                'description' => 'Replacement parts for assets and devices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Consumable',
                'code' => 'CNS',
                'description' => 'Consumable office and IT materials.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tools',
                'code' => 'TOL',
                'description' => 'Operational and maintenance tools.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'License',
                'code' => 'LCE',
                'description' => 'Software or service licenses.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vendor',
                'code' => 'VND',
                'description' => 'Vendor and procurement category reference.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['code'], ['name', 'description', 'updated_at']);
    }

    private function createDepartmentsTable(): void
    {
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->string('code', 10)->nullable()->unique();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('divisions') && Schema::hasColumn('divisions', 'name')) {
            $divisionNames = DB::table('divisions')->select('name')->whereNotNull('name')->pluck('name')->unique();

            foreach ($divisionNames as $divisionName) {
                DB::table('departments')->upsert([
                    [
                        'name' => $divisionName,
                        'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $divisionName), 0, 3)) ?: null,
                        'description' => 'Synced from divisions table.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ], ['name'], ['code', 'description', 'updated_at']);
            }
        }
    }

    private function createMeetingRoomsTable(): void
    {
        if (!Schema::hasTable('meeting_rooms')) {
            Schema::create('meeting_rooms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->unique();
                $table->string('code', 50)->nullable()->unique();
                $table->unsignedInteger('location_id')->nullable()->index();
                $table->unsignedSmallInteger('capacity')->nullable();
                $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('locations') && Schema::hasColumn('locations', 'id') && !$this->foreignKeyExists('meeting_rooms', 'meeting_rooms_location_id_foreign')) {
            Schema::table('meeting_rooms', function (Blueprint $table) {
                $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            });
        }

        if (Schema::hasTable('meeting_room_bookings') && Schema::hasColumn('meeting_room_bookings', 'room_name')) {
            $roomNames = DB::table('meeting_room_bookings')
                ->whereNotNull('room_name')
                ->where('room_name', '!=', '')
                ->select('room_name')
                ->distinct()
                ->pluck('room_name')
                ->values();

            $counter = 1;
            foreach ($roomNames as $roomName) {
                $existing = DB::table('meeting_rooms')->where('name', $roomName)->exists();
                if ($existing) {
                    continue;
                }

                DB::table('meeting_rooms')->insert([
                    'name' => $roomName,
                    'code' => sprintf('MR-%03d', $counter),
                    'status' => 'active',
                    'description' => 'Auto-generated from existing bookings.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $counter++;
            }
        }
    }

    private function extendAssetsTable(): void
    {
        if (!Schema::hasTable('assets')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'rfid_tag')) {
                $table->string('rfid_tag', 100)->nullable()->unique()->after('qr_code');
            }

            if (!Schema::hasColumn('assets', 'warranty_expiration_date')) {
                $table->dateTime('warranty_expiration_date')->nullable()->index()->after('warranty_months');
            }

            if (!Schema::hasColumn('assets', 'supplier_name')) {
                $table->string('supplier_name', 255)->nullable()->after('supplier_id');
            }

            if (!Schema::hasColumn('assets', 'cost')) {
                $table->decimal('cost', 15, 2)->nullable()->after('supplier_name');
            }

            if (!Schema::hasColumn('assets', 'depreciation_schedule')) {
                $table->json('depreciation_schedule')->nullable()->after('cost');
            }

            if (!Schema::hasColumn('assets', 'location_history')) {
                $table->json('location_history')->nullable()->after('return_history');
            }

            if (!Schema::hasColumn('assets', 'assigned_user_id')) {
                $table->unsignedInteger('assigned_user_id')->nullable()->index()->after('assigned_to');
            }

            if (!Schema::hasColumn('assets', 'maintenance_status')) {
                $table->enum('maintenance_status', ['scheduled', 'in_progress', 'completed'])->nullable()->index()->after('maintenance_schedule');
            }

            if (!Schema::hasColumn('assets', 'maintenance_notes')) {
                $table->text('maintenance_notes')->nullable()->after('maintenance_status');
            }

            if (!Schema::hasColumn('assets', 'disposal_status')) {
                $table->enum('disposal_status', ['pending', 'approved', 'completed'])->nullable()->index()->after('disposal_history');
            }

            if (!Schema::hasColumn('assets', 'disposal_notes')) {
                $table->text('disposal_notes')->nullable()->after('disposal_status');
            }

            if (!Schema::hasColumn('assets', 'lending_status')) {
                $table->enum('lending_status', ['pending', 'approved', 'completed'])->nullable()->index()->after('lending_history');
            }

            if (!Schema::hasColumn('assets', 'lending_notes')) {
                $table->text('lending_notes')->nullable()->after('lending_status');
            }

            if (!Schema::hasColumn('assets', 'return_status')) {
                $table->enum('return_status', ['pending', 'approved', 'completed'])->nullable()->index()->after('return_history');
            }

            if (!Schema::hasColumn('assets', 'return_notes')) {
                $table->text('return_notes')->nullable()->after('return_status');
            }

            if (!Schema::hasColumn('assets', 'image')) {
                $table->string('image', 255)->nullable()->after('return_notes');
            }
        });

        if (Schema::hasTable('users') && !$this->foreignKeyExists('assets', 'assets_assigned_user_id_foreign')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!$this->isMysql()) {
            return;
        }

        DB::statement('UPDATE assets SET assigned_user_id = assigned_to WHERE assigned_user_id IS NULL AND assigned_to IS NOT NULL');

        if (Schema::hasTable('suppliers')) {
            DB::statement("UPDATE assets a LEFT JOIN suppliers s ON s.id = a.supplier_id SET a.supplier_name = s.name WHERE (a.supplier_name IS NULL OR a.supplier_name = '') AND s.name IS NOT NULL");
        }

        DB::statement('UPDATE assets SET warranty_expiration_date = DATE_ADD(CONCAT(purchase_date, " 00:00:00"), INTERVAL warranty_months MONTH) WHERE warranty_expiration_date IS NULL AND purchase_date IS NOT NULL AND warranty_months IS NOT NULL');

        if (Schema::hasTable('movements')) {
            DB::statement("UPDATE assets a LEFT JOIN (SELECT m.asset_id, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('location_id', m.location_id, 'status_id', m.status_id, 'user_id', m.user_id, 'changed_at', m.created_at) ORDER BY m.created_at SEPARATOR ','), ']') AS history_json FROM movements m GROUP BY m.asset_id) h ON h.asset_id = a.id SET a.location_history = h.history_json WHERE a.location_history IS NULL AND h.history_json IS NOT NULL");
        }

        if (Schema::hasTable('asset_maintenance_logs')) {
            DB::statement("UPDATE assets a INNER JOIN (SELECT aml.asset_id, aml.status, aml.notes FROM asset_maintenance_logs aml INNER JOIN (SELECT asset_id, MAX(id) AS max_id FROM asset_maintenance_logs GROUP BY asset_id) latest ON latest.max_id = aml.id) m ON m.asset_id = a.id SET a.maintenance_status = CASE WHEN m.status = 'planned' THEN 'scheduled' WHEN m.status = 'in_progress' THEN 'in_progress' WHEN m.status = 'completed' THEN 'completed' ELSE a.maintenance_status END, a.maintenance_notes = COALESCE(a.maintenance_notes, m.notes) WHERE a.maintenance_status IS NULL OR a.maintenance_notes IS NULL");
        }

        if (Schema::hasTable('asset_lifecycle_events')) {
            DB::statement("UPDATE assets a INNER JOIN (SELECT ale.asset_id, ale.description FROM asset_lifecycle_events ale INNER JOIN (SELECT asset_id, MAX(id) AS max_id FROM asset_lifecycle_events WHERE event_type = 'disposal' GROUP BY asset_id) latest ON latest.max_id = ale.id) d ON d.asset_id = a.id SET a.disposal_status = COALESCE(a.disposal_status, 'completed'), a.disposal_notes = COALESCE(a.disposal_notes, d.description)");

            DB::statement("UPDATE assets a INNER JOIN (SELECT ale.asset_id, ale.description FROM asset_lifecycle_events ale INNER JOIN (SELECT asset_id, MAX(id) AS max_id FROM asset_lifecycle_events WHERE event_type IN ('transfer', 'deployment') GROUP BY asset_id) latest ON latest.max_id = ale.id) l ON l.asset_id = a.id SET a.lending_status = COALESCE(a.lending_status, 'completed'), a.lending_notes = COALESCE(a.lending_notes, l.description)");

            DB::statement("UPDATE assets a INNER JOIN (SELECT ale.asset_id, ale.description FROM asset_lifecycle_events ale INNER JOIN (SELECT asset_id, MAX(id) AS max_id FROM asset_lifecycle_events WHERE event_type = 'found' GROUP BY asset_id) latest ON latest.max_id = ale.id) r ON r.asset_id = a.id SET a.return_status = COALESCE(a.return_status, 'completed'), a.return_notes = COALESCE(a.return_notes, r.description)");
        }
    }

    private function extendAssetRequestsTable(): void
    {
        if (!Schema::hasTable('asset_requests')) {
            return;
        }

        Schema::table('asset_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_requests', 'item_name')) {
                $table->string('item_name', 255)->nullable()->after('asset_type_id');
            }

            if (!Schema::hasColumn('asset_requests', 'category')) {
                $table->string('category', 100)->nullable()->after('item_name');
            }

            if (!Schema::hasColumn('asset_requests', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('category');
            }

            if (!Schema::hasColumn('asset_requests', 'approval_history')) {
                $table->json('approval_history')->nullable()->after('approval_notes');
            }

            if (!Schema::hasColumn('asset_requests', 'vendor')) {
                $table->string('vendor', 255)->nullable()->after('approval_history');
            }

            if (!Schema::hasColumn('asset_requests', 'estimated_cost')) {
                $table->decimal('estimated_cost', 15, 2)->nullable()->after('vendor');
            }

            if (!Schema::hasColumn('asset_requests', 'actual_cost')) {
                $table->decimal('actual_cost', 15, 2)->nullable()->after('estimated_cost');
            }

            if (!Schema::hasColumn('asset_requests', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('actual_cost');
            }

            if (!Schema::hasColumn('asset_requests', 'receipt_image')) {
                $table->string('receipt_image', 255)->nullable()->after('delivery_date');
            }

            if (!Schema::hasColumn('asset_requests', 'purchase_notes')) {
                $table->text('purchase_notes')->nullable()->after('receipt_image');
            }

            if (!Schema::hasColumn('asset_requests', 'purchase_history')) {
                $table->json('purchase_history')->nullable()->after('purchase_notes');
            }
        });

        if (!$this->isMysql()) {
            return;
        }

        DB::statement('UPDATE asset_requests SET user_id = requested_by WHERE user_id IS NULL AND requested_by IS NOT NULL');
        DB::statement('UPDATE asset_requests SET quantity = 1 WHERE quantity IS NULL OR quantity < 1');

        if (Schema::hasTable('asset_types')) {
            DB::statement("UPDATE asset_requests ar LEFT JOIN asset_types at ON at.id = ar.asset_type_id SET ar.item_name = COALESCE(ar.item_name, at.type_name), ar.category = COALESCE(ar.category, at.type_name) WHERE (ar.item_name IS NULL OR ar.item_name = '' OR ar.category IS NULL OR ar.category = '')");
        }

        DB::statement("UPDATE asset_requests SET approval_history = CONCAT('[', JSON_OBJECT('approver_id', approved_by, 'status', status, 'approved_at', approved_at, 'notes', approval_notes), ']') WHERE approval_history IS NULL AND approved_by IS NOT NULL");
        DB::statement('UPDATE asset_requests SET purchase_notes = approval_notes WHERE (purchase_notes IS NULL OR purchase_notes = "") AND approval_notes IS NOT NULL');
        DB::statement("UPDATE asset_requests SET purchase_history = CONCAT('[', JSON_OBJECT('status', status, 'timestamp', updated_at), ']') WHERE purchase_history IS NULL");
    }

    private function alignMeetingRoomBookingsWithRooms(): void
    {
        if (!Schema::hasTable('meeting_room_bookings') || !Schema::hasTable('meeting_rooms')) {
            return;
        }

        if (!$this->isMysql()) {
            return;
        }

        if (Schema::hasColumn('meeting_room_bookings', 'room_id') && Schema::hasColumn('meeting_room_bookings', 'room_name')) {
            DB::statement('UPDATE meeting_room_bookings b INNER JOIN meeting_rooms r ON r.name = b.room_name SET b.room_id = r.id WHERE b.room_id IS NULL AND b.room_name IS NOT NULL');
        }

        if (Schema::hasColumn('meeting_room_bookings', 'room_id') && !$this->foreignKeyExists('meeting_room_bookings', 'meeting_room_bookings_room_id_foreign')) {
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->foreign('room_id')->references('id')->on('meeting_rooms')->nullOnDelete();
            });
        }
    }

    private function addPerformanceIndexes(): void
    {
        if (Schema::hasTable('users') && $this->columnsExist('users', ['role_id', 'is_active', 'last_login_at']) && !$this->indexExists('users', 'idx_users_role_active_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role_id', 'is_active', 'last_login_at'], 'idx_users_role_active_login');
            });
        }

        if (Schema::hasTable('tickets') && $this->columnsExist('tickets', ['status', 'priority', 'sla_due_date']) && !$this->indexExists('tickets', 'idx_tickets_status_priority_sla')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['status', 'priority', 'sla_due_date'], 'idx_tickets_status_priority_sla');
            });
        }

        if (Schema::hasTable('tickets') && $this->columnsExist('tickets', ['user_id', 'status', 'created_at']) && !$this->indexExists('tickets', 'idx_tickets_user_status_created')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['user_id', 'status', 'created_at'], 'idx_tickets_user_status_created');
            });
        }

        if (Schema::hasTable('tickets') && $this->columnsExist('tickets', ['assigned_agent_id', 'status']) && !$this->indexExists('tickets', 'idx_tickets_assigned_agent_status')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['assigned_agent_id', 'status'], 'idx_tickets_assigned_agent_status');
            });
        }

        if (Schema::hasTable('assets') && $this->columnsExist('assets', ['category', 'asset_type', 'brand', 'status_name']) && !$this->indexExists('assets', 'idx_assets_lookup_filters')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->index(['category', 'asset_type', 'brand', 'status_name'], 'idx_assets_lookup_filters');
            });
        }

        if (Schema::hasTable('assets') && $this->columnsExist('assets', ['assigned_user_id', 'maintenance_status', 'warranty_expiration_date']) && !$this->indexExists('assets', 'idx_assets_assigned_maintenance')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->index(['assigned_user_id', 'maintenance_status', 'warranty_expiration_date'], 'idx_assets_assigned_maintenance');
            });
        }

        if (Schema::hasTable('asset_requests') && $this->columnsExist('asset_requests', ['requested_by', 'status', 'created_at']) && !$this->indexExists('asset_requests', 'idx_asset_requests_requester_status_created')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                $table->index(['requested_by', 'status', 'created_at'], 'idx_asset_requests_requester_status_created');
            });
        }

        if (Schema::hasTable('asset_requests') && $this->columnsExist('asset_requests', ['category', 'vendor', 'delivery_date']) && !$this->indexExists('asset_requests', 'idx_asset_requests_procurement')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                $table->index(['category', 'vendor', 'delivery_date'], 'idx_asset_requests_procurement');
            });
        }

        if (Schema::hasTable('meeting_room_bookings') && $this->columnsExist('meeting_room_bookings', ['room_id', 'status', 'start_time', 'end_time']) && !$this->indexExists('meeting_room_bookings', 'idx_meeting_room_booking_window')) {
            Schema::table('meeting_room_bookings', function (Blueprint $table) {
                $table->index(['room_id', 'status', 'start_time', 'end_time'], 'idx_meeting_room_booking_window');
            });
        }

        if (Schema::hasTable('meeting_rooms') && $this->columnsExist('meeting_rooms', ['status']) && !$this->indexExists('meeting_rooms', 'idx_meeting_rooms_status')) {
            Schema::table('meeting_rooms', function (Blueprint $table) {
                $table->index('status', 'idx_meeting_rooms_status');
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

    private function indexExists(string $tableName, string $indexName): bool
    {
        if (!$this->isMysql()) {
            return false;
        }

        $databaseName = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate_count FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$databaseName, $tableName, $indexName]
        );

        return ((int) ($result->aggregate_count ?? 0)) > 0;
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if ($this->indexExists($tableName, $indexName)) {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
