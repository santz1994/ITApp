<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasColumn('assets', 'purchase_order_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->unsignedInteger('purchase_order_id')->nullable()->after('invoice_id')->index();
                // Add FK if purchase_orders table exists
                if (Schema::hasTable('purchase_orders')) {
                    $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('assets', 'purchase_order_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                // Try drop foreign if exists
                try { $table->dropForeign(['purchase_order_id']); } catch (\Exception $e) {}
                $table->dropColumn('purchase_order_id');
            });
        }
    }
};
