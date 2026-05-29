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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama kendaraan (e.g., "Avanza Putih")
            $table->string('plate_number')->unique(); // Nomor plat
            $table->string('brand'); // Merek (Toyota, Honda, dll)
            $table->string('model'); // Model kendaraan
            $table->integer('year')->nullable(); // Tahun pembuatan
            $table->string('color')->nullable(); // Warna
            $table->integer('capacity')->default(4); // Kapasitas penumpang
            $table->enum('status', ['available', 'in_use', 'maintenance', 'retired'])->default('available');
            $table->string('fuel_type')->nullable(); // Jenis bahan bakar
            $table->string('insurance_expiry')->nullable(); // Masa berlaku asuransi
            $table->string('stnk_expiry')->nullable(); // Masa berlaku STNK
            $table->text('notes')->nullable();
            $table->string('photo')->nullable(); // Path foto kendaraan
            $table->decimal('current_mileage', 10, 2)->default(0); // Kilometer saat ini
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vehicle_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->unsignedInteger('requested_by');
            $table->string('purpose'); // Tujuan penggunaan
            $table->string('destination'); // Lokasi tujuan
            $table->decimal('estimated_distance', 10, 2)->nullable(); // Estimasi jarak (km)
            $table->dateTime('start_datetime'); // Waktu mulai
            $table->dateTime('end_datetime'); // Waktu selesai
            $table->integer('passengers')->default(1); // Jumlah penumpang
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('actual_distance', 10, 2)->nullable(); // Jarak aktual (km)
            $table->decimal('actual_fuel_cost', 12, 2)->nullable(); // Biaya bahan bakar aktual
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vehicle_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('maintenance_type'); // Servis berkala, perbaikan, dll
            $table->text('description');
            $table->decimal('cost', 12, 2)->nullable();
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->decimal('mileage_at_service', 10, 2)->nullable();
            $table->string('service_provider')->nullable(); // Bengkel/vendor
            $table->unsignedInteger('recorded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_maintenance_logs');
        Schema::dropIfExists('vehicle_bookings');
        Schema::dropIfExists('vehicles');
    }
};