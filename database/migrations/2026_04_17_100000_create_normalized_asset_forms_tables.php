<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->createAssetFormsTable();
        $this->createAssetFormItemsTable();
        $this->createAssetFormApprovalsTable();
    }

    public function down(): void
    {
        if (Schema::hasTable('asset_form_approvals')) {
            Schema::drop('asset_form_approvals');
        }

        if (Schema::hasTable('asset_form_items')) {
            Schema::drop('asset_form_items');
        }

        if (Schema::hasTable('asset_forms')) {
            Schema::drop('asset_forms');
        }
    }

    private function createAssetFormsTable(): void
    {
        if (Schema::hasTable('asset_forms')) {
            return;
        }

        $hasUsers = Schema::hasTable('users');
        $hasAssets = Schema::hasTable('assets');
        $hasDivisions = Schema::hasTable('divisions');
        $hasLocations = Schema::hasTable('locations');

        Schema::create('asset_forms', function (Blueprint $table) use ($hasUsers, $hasAssets, $hasDivisions, $hasLocations) {
            $table->increments('id');
            $table->string('form_number', 40)->unique();
            $table->enum('form_type', ['handover', 'lending', 'return', 'disposal']);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'completed', 'cancelled'])->default('draft');
            $table->unsignedInteger('requested_by')->nullable();
            $table->unsignedInteger('requested_for_user_id')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('asset_id')->nullable();
            $table->unsignedInteger('division_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('form_type', 'idx_asset_forms_type');
            $table->index('status', 'idx_asset_forms_status');
            $table->index(['form_type', 'status', 'created_at'], 'idx_asset_forms_type_status_created');
            $table->index(['requested_by', 'status'], 'idx_asset_forms_requester_status');
            $table->index(['approved_by', 'status'], 'idx_asset_forms_approver_status');

            if ($hasUsers) {
                $table->foreign('requested_by', 'af_requested_by_fk')->references('id')->on('users')->onDelete('set null');
                $table->foreign('requested_for_user_id', 'af_requested_for_user_fk')->references('id')->on('users')->onDelete('set null');
                $table->foreign('approved_by', 'af_approved_by_fk')->references('id')->on('users')->onDelete('set null');
            }

            if ($hasAssets) {
                $table->foreign('asset_id', 'af_asset_id_fk')->references('id')->on('assets')->onDelete('set null');
            }

            if ($hasDivisions) {
                $table->foreign('division_id', 'af_division_id_fk')->references('id')->on('divisions')->onDelete('set null');
            }

            if ($hasLocations) {
                $table->foreign('location_id', 'af_location_id_fk')->references('id')->on('locations')->onDelete('set null');
            }
        });
    }

    private function createAssetFormItemsTable(): void
    {
        if (Schema::hasTable('asset_form_items')) {
            return;
        }

        $hasAssets = Schema::hasTable('assets');

        Schema::create('asset_form_items', function (Blueprint $table) use ($hasAssets) {
            $table->increments('id');
            $table->unsignedInteger('asset_form_id');
            $table->unsignedInteger('asset_id')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('asset_tag', 128)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition_before', 100)->nullable();
            $table->string('condition_after', 100)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['asset_form_id', 'created_at'], 'idx_asset_form_items_form_created');
            $table->index(['asset_id', 'asset_tag'], 'idx_asset_form_items_asset_lookup');
            $table->foreign('asset_form_id', 'afi_asset_form_id_fk')->references('id')->on('asset_forms')->onDelete('cascade');

            if ($hasAssets) {
                $table->foreign('asset_id', 'afi_asset_id_fk')->references('id')->on('assets')->onDelete('set null');
            }
        });
    }

    private function createAssetFormApprovalsTable(): void
    {
        if (Schema::hasTable('asset_form_approvals')) {
            return;
        }

        $hasUsers = Schema::hasTable('users');

        Schema::create('asset_form_approvals', function (Blueprint $table) use ($hasUsers) {
            $table->increments('id');
            $table->unsignedInteger('asset_form_id');
            $table->unsignedInteger('actor_user_id')->nullable();
            $table->enum('action', ['submitted', 'approved', 'rejected', 'completed', 'cancelled', 'reopened']);
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->text('action_notes')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['asset_form_id', 'acted_at'], 'idx_asset_form_approvals_form_acted');
            $table->index(['actor_user_id', 'action', 'acted_at'], 'idx_asset_form_approvals_actor_action');
            $table->foreign('asset_form_id', 'afa_asset_form_id_fk')->references('id')->on('asset_forms')->onDelete('cascade');

            if ($hasUsers) {
                $table->foreign('actor_user_id', 'afa_actor_user_id_fk')->references('id')->on('users')->onDelete('set null');
            }
        });
    }
};
