<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ticket_assets')) {
            Schema::create('ticket_assets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ticket_id')->index();
                $table->unsignedBigInteger('asset_id')->index();
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
                $table->unique(['ticket_id', 'asset_id']);
            });
        }

        // Backfill existing ticket.asset_id values into pivot (insertOrIgnore to be safe)
        if (Schema::hasTable('tickets')) {
            $rows = \DB::table('tickets')->whereNotNull('asset_id')->select('id', 'asset_id')->get();
            foreach ($rows as $r) {
                try {
                    \DB::table('ticket_assets')->insertOrIgnore([
                        'ticket_id' => $r->id,
                        'asset_id' => $r->asset_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    // ignore any duplicate or FK errors during migration
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('ticket_assets');
    }
};
