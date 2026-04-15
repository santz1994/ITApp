<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketsPrioritiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tickets_priorities')->delete();
        DB::table('tickets_priorities')->insert([
            ['id' => 1, 'priority' => 'Low'],
            ['id' => 2, 'priority' => 'Medium'],
            ['id' => 3, 'priority' => 'High'],
        ]);
    }
}