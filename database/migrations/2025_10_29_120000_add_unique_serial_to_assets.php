<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniqueSerialToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Safety: detect duplicates before creating the unique index
        $duplicates = DB::table('assets')
            ->select('serial_number', DB::raw('count(*) as occurrences'))
            ->whereNotNull('serial_number')
            ->groupBy('serial_number')
            ->having('occurrences', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            $msg = "Cannot add unique index: found " . $duplicates->count() . " duplicate serial_number values. Run `php artisan assets:detect-duplicate-serials` and resolve duplicates first.`";
            throw new \Exception($msg);
        }

        // Add unique index if not exists
        Schema::table('assets', function ($table) {
            // Some DB engines require naming
            $table->unique('serial_number', 'assets_serial_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function ($table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            // Attempt to drop index by name; if it doesn't exist, ignore
            try {
                $table->dropUnique('assets_serial_number_unique');
            } catch (\Exception $e) {
                // best-effort: ignore
            }
        });
    }
}
