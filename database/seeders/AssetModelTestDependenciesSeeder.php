<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetModelTestDependenciesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        // Asset Types
        DB::table('asset_types')->delete();
        DB::table('asset_types')->insert([
            ['id' => 1, 'type_name' => 'Type 1', 'abbreviation' => 'T1', 'spare' => 0],
            ['id' => 2, 'type_name' => 'Type 2', 'abbreviation' => 'T2', 'spare' => 1],
        ]);
        // Manufacturers
        DB::table('manufacturers')->delete();
        DB::table('manufacturers')->insert([
            ['id' => 1, 'name' => 'Manufacturer 1'],
            ['id' => 2, 'name' => 'Manufacturer 2'],
        ]);
        // PC Specs
        DB::table('pcspecs')->delete();
        DB::table('pcspecs')->insert([
            ['id' => 1, 'cpu' => 'Intel i5', 'ram' => '8GB', 'hdd' => '500GB'],
            ['id' => 2, 'cpu' => 'Intel i7', 'ram' => '16GB', 'hdd' => '1TB'],
        ]);
    }
}
