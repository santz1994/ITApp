<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_lifecycle_events', function (Blueprint $table) {
            $table->id();
            
            // Asset reference (foreign key will be added later)
            $table->unsignedBigInteger('asset_id');
            
            // Event type (acquisition, deployment, maintenance, transfer, retirement, disposal, etc.)
            $table->enum('event_type', [
                'acquisition',      // Asset purchased/received
                'deployment',       // Assigned to user/location
                'transfer',         // Moved between locations/users
                'maintenance',      // Maintenance performed
                'repair',           // Repair completed
                'upgrade',          // Hardware/software upgrade
                'audit',            // Physical audit/verification
                'warranty_expiry',  // Warranty expired
                'depreciation',     // Depreciation milestone
                'retirement',       // End of life
                'disposal',         // Asset disposed/sold
                'stolen',           // Asset reported stolen
                'lost',             // Asset reported lost
                'found',            // Lost asset recovered
                'damage',           // Damage reported
                'other'
            ]);
            
            // Event details
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Flexible data: from/to location, cost, etc.
            
            // Who triggered the event (foreign keys will be added later)
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Event timestamp (may differ from created_at for backdated entries)
            $table->timestamp('event_date')->useCurrent();
            
            // Optional: Link to related records
            $table->unsignedBigInteger('ticket_id')->nullable();
            
            $table->timestamps();
            
            // Indexes for querying
            $table->index('asset_id');
            $table->index('event_type');
            $table->index('event_date');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_lifecycle_events');
    }
};
