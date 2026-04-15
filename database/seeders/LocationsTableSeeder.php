<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear locations table and reset auto-increment
        DB::table('locations')->delete();
        // Insert location with id = 1
        DB::table('locations')->insert([
            'id' => 1,
            'building' => 'Main',
            'office' => 'HQ',
            'location_name' => 'Default Location',
            'storeroom' => 0
        ]);
        // Insert a second location used by tests (id = 2)
        DB::table('locations')->insert([
            'id' => 2,
            'building' => 'Annex',
            'office' => 'Branch',
            'location_name' => 'Secondary Location',
            'storeroom' => 0
        ]);
        // Optionally add more locations if needed
    }
}
