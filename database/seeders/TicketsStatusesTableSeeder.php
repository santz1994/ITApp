<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketsStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tickets_statuses')->delete();
        DB::table('tickets_statuses')->insert([
            ['id' => 1, 'status' => 'Open'],
            ['id' => 2, 'status' => 'Pending'],
            ['id' => 3, 'status' => 'Resolved'],
        ]);
    }
}