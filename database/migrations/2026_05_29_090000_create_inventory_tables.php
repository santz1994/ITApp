<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Inventory categories (ATK, Sparepart, etc.)
        if (!Schema::hasTable('inventory_categories')) {
            Schema::create('inventory_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // ATK, Sparepart, Consumable, dll
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Inventory items master data
        if (!Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('cascade');
                $table->string('name'); // Nama barang
                $table->string('sku')->unique(); // Kode barang (Stock Keeping Unit)
                $table->text('description')->nullable();
                $table->string('unit')->default('pcs'); // Satuan (pcs, box, rim, dll)
                $table->integer('current_stock')->default(0); // Stok saat ini
                $table->integer('minimum_stock')->default(0); // Batas minimum stok
                $table->decimal('unit_price', 12, 2)->default(0); // Harga satuan
                $table->string('location')->nullable(); // Lokasi penyimpanan
                $table->string('photo')->nullable();
                $table->string('qr_code')->nullable(); // QR code untuk scanning
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Stock movements (masuk/keluar)
        if (!Schema::hasTable('inventory_stock_movements')) {
            Schema::create('inventory_stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
                $table->enum('type', ['in', 'out', 'adjustment']); // Masuk, Keluar, Penyesuaian
                $table->integer('quantity'); // Jumlah
                $table->integer('stock_before'); // Stok sebelum
                $table->integer('stock_after'); // Stok sesudah
                $table->string('reference_type')->nullable(); // Tipe referensi (request, purchase, manual)
                $table->unsignedBigInteger('reference_id')->nullable(); // ID referensi
                $table->text('notes')->nullable();
                $table->unsignedInteger('recorded_by');
                $table->timestamps();
            });
        }

        // Inventory requests (pengajuan permintaan ATK/Sparepart)
        if (!Schema::hasTable('inventory_requests')) {
            Schema::create('inventory_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number')->unique(); // Nomor permintaan
                $table->unsignedInteger('requested_by');
                $table->unsignedInteger('department_id')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', [
                    'pending',
                    'approved',
                    'rejected',
                    'partially_fulfilled',
                    'fulfilled',
                    'cancelled'
                ])->default('pending');
                $table->unsignedInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Inventory request items (detail permintaan)
        if (!Schema::hasTable('inventory_request_items')) {
            Schema::create('inventory_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('inventory_requests')->onDelete('cascade');
                $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
                $table->integer('quantity_requested'); // Jumlah diminta
                $table->integer('quantity_approved')->nullable(); // Jumlah disetujui
                $table->integer('quantity_fulfilled')->default(0); // Jumlah yang sudah diberikan
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_request_items');
        Schema::dropIfExists('inventory_requests');
        Schema::dropIfExists('inventory_stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
    }
};