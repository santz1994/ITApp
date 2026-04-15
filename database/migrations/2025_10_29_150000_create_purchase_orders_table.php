<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->increments('id');
                $table->string('po_number')->unique();
                $table->unsignedInteger('supplier_id')->nullable();
                $table->date('order_date')->nullable();
                $table->decimal('total_cost', 15, 2)->nullable();
                $table->timestamps();

                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
            });
            Schema::dropIfExists('purchase_orders');
        }
    }
};
