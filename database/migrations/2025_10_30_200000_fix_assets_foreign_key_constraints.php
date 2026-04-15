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
     * Fix foreign key constraints on assets table to have proper onDelete rules.
     * This ensures data integrity and prevents orphaned records.
     */
    public function up()
    {
        // Get existing foreign keys (cross-database compatible)
        $driver = DB::connection()->getDriverName();
        $foreignKeys = [];
        
        if ($driver === 'mysql') {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'assets'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
        } elseif ($driver === 'sqlite') {
            // SQLite: Parse PRAGMA foreign_key_list
            $fks = DB::select("PRAGMA foreign_key_list(assets)");
            // SQLite foreign keys don't have constraint names, use column names
            $foreignKeys = collect($fks)->map(function($fk) {
                return (object)['CONSTRAINT_NAME' => 'assets_' . $fk->from . '_foreign'];
            })->all();
        } elseif ($driver === 'pgsql') {
            $foreignKeys = DB::select("
                SELECT constraint_name AS CONSTRAINT_NAME
                FROM information_schema.table_constraints
                WHERE table_name = 'assets'
                AND constraint_type = 'FOREIGN KEY'
            ");
        }
        
        $existingFKs = array_column($foreignKeys, 'CONSTRAINT_NAME');
        
        // Drop existing foreign keys (skip for SQLite - doesn't support dropping FKs)
        if ($driver !== 'sqlite') {
            Schema::table('assets', function (Blueprint $table) use ($existingFKs) {
                // Drop old FKs if they exist
                if (in_array('assets_model_id_foreign', $existingFKs)) {
                    $table->dropForeign(['model_id']);
                }
                
                if (in_array('assets_division_id_foreign', $existingFKs)) {
                    $table->dropForeign(['division_id']);
                }
                
                if (in_array('assets_supplier_id_foreign', $existingFKs)) {
                    $table->dropForeign(['supplier_id']);
                }
                
                if (in_array('assets_assigned_to_foreign', $existingFKs)) {
                    $table->dropForeign(['assigned_to']);
                }
                
                if (in_array('assets_purchase_order_id_foreign', $existingFKs)) {
                    $table->dropForeign(['purchase_order_id']);
                }
                
                if (in_array('assets_warranty_type_id_foreign', $existingFKs)) {
                    $table->dropForeign(['warranty_type_id']);
                }
                
                if (in_array('assets_invoice_id_foreign', $existingFKs)) {
                    $table->dropForeign(['invoice_id']);
                }
            });
        }

        // First, ensure warranty_type_id and invoice_id are unsigned if they exist
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'warranty_type_id')) {
                // Change to unsigned integer to match warranty_types.id
                $table->unsignedInteger('warranty_type_id')->nullable()->change();
            }
            
            if (Schema::hasColumn('assets', 'invoice_id')) {
                // Change to unsigned integer to match invoices.id
                $table->unsignedInteger('invoice_id')->nullable()->change();
            }
        });
        
        // Add new FKs with proper onDelete rules
        Schema::table('assets', function (Blueprint $table) {
            // RESTRICT - Prevent deletion if assets exist
            $table->foreign('model_id')
                  ->references('id')
                  ->on('asset_models')
                  ->onDelete('restrict');
            
            $table->foreign('division_id')
                  ->references('id')
                  ->on('divisions')
                  ->onDelete('restrict');
            
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('restrict');
            
            // If assigned_to exists, add FK with RESTRICT
            if (Schema::hasColumn('assets', 'assigned_to')) {
                $table->foreign('assigned_to')
                      ->references('id')
                      ->on('users')
                      ->onDelete('restrict');
            }
            
            // SET NULL - Allow cleanup of optional relations
            if (Schema::hasColumn('assets', 'purchase_order_id')) {
                $table->foreign('purchase_order_id')
                      ->references('id')
                      ->on('purchase_orders')
                      ->onDelete('set null');
            }
            
            if (Schema::hasColumn('assets', 'warranty_type_id')) {
                $table->foreign('warranty_type_id')
                      ->references('id')
                      ->on('warranty_types')
                      ->onDelete('restrict');
            }
            
            if (Schema::hasColumn('assets', 'invoice_id')) {
                $table->foreign('invoice_id')
                      ->references('id')
                      ->on('invoices')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // SQLite doesn't support dropping foreign keys
        $driver = DB::connection()->getDriverName();
        
        if ($driver !== 'sqlite') {
            // Drop the new FKs
            Schema::table('assets', function (Blueprint $table) {
                $table->dropForeign(['model_id']);
                $table->dropForeign(['division_id']);
                $table->dropForeign(['supplier_id']);
                
                if (Schema::hasColumn('assets', 'assigned_to')) {
                    $table->dropForeign(['assigned_to']);
                }
                
                if (Schema::hasColumn('assets', 'purchase_order_id')) {
                    $table->dropForeign(['purchase_order_id']);
                }
                
                if (Schema::hasColumn('assets', 'warranty_type_id')) {
                    $table->dropForeign(['warranty_type_id']);
                }
                
                if (Schema::hasColumn('assets', 'invoice_id')) {
                    $table->dropForeign(['invoice_id']);
                }
            });

            // Re-add old FKs without onDelete rules
            Schema::table('assets', function (Blueprint $table) {
                $table->foreign('model_id')->references('id')->on('asset_models');
                $table->foreign('division_id')->references('id')->on('divisions');
                $table->foreign('supplier_id')->references('id')->on('suppliers');
            });
        }
        // For SQLite: Foreign keys are defined during table creation and cannot be modified
        // This migration will be skipped on rollback for SQLite databases
    }
};
