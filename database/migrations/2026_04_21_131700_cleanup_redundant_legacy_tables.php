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
     * Phase 1: Database Cleanup - Remove redundant legacy tables after Spatie Permission migration
     * 
     * Tables to drop:
     * - permission_role (empty, legacy Entrust, replaced by role_has_permissions)
     * - role_user (empty, legacy Entrust, replaced by model_has_roles)
     * - role_permissions (has 154 records, migrated to role_has_permissions)
     * - tickets_entries (empty, legacy ticket system)
     * - pcspecs (empty, unused feature)
     * - push_subscriptions (empty, feature not implemented)
     * - meeting_room_lcd_settings (empty, replaced by meeting_room_display_settings)
     *
     * @return void
     */
    public function up()
    {
        // Safety check: Verify Spatie migration completed before dropping legacy tables
        // Only perform check if both tables exist (MySQL production) to avoid SQLite test failures
        if (Schema::hasTable('role_has_permissions') && Schema::hasTable('role_permissions')) {
            $spatieCount = DB::table('role_has_permissions')->count();
            $legacyCount = DB::table('role_permissions')->count();
            
            if ($spatieCount < $legacyCount) {
                throw new \Exception(
                    "Safety abort: role_has_permissions ($spatieCount) has fewer records than role_permissions ($legacyCount). " .
                    "Please verify Spatie migration completed successfully before dropping legacy tables."
                );
            }
        }
        
        // Drop legacy Entrust permission tables (replaced by Spatie Permission)
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('role_permissions');
        
        // Drop unused/empty legacy feature tables
        Schema::dropIfExists('tickets_entries');
        Schema::dropIfExists('pcspecs');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('meeting_room_lcd_settings');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This down() method cannot fully restore the original data.
     * If rollback is needed, restore from backup: database/pre_phase1_cleanup_20260421_131623.sql
     *
     * @return void
     */
    public function down()
    {
        // Recreate legacy Entrust tables structure (without data restoration)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->primary(['permission_id', 'role_id']);
        });
        
        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->primary(['role_id', 'user_id']);
        });
        
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->timestamps();
        });
        
        // Recreate unused legacy tables structure
        Schema::create('tickets_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        Schema::create('pcspecs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cpu', 100)->nullable();
            $table->string('ram', 50)->nullable();
            $table->string('hdd', 50)->nullable();
            $table->timestamps();
        });
        
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('endpoint');
            $table->text('keys')->nullable();
            $table->timestamps();
        });
        
        Schema::create('meeting_room_lcd_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('meeting_room_id')->unsigned();
            $table->text('settings')->nullable();
            $table->timestamps();
        });
    }
};
