<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketsTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tickets_types')->delete();
        DB::table('tickets_types')->insert([
            ['id' => 1, 'type' => 'Incident'],
            ['id' => 2, 'type' => 'Problem'],
            ['id' => 3, 'type' => 'Loan'],
        ]);
    }
}