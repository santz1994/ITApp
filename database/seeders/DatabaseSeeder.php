<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,
            ComprehensivePermissionsSeeder::class,
            CompanyDivisionsSeeder::class,
            LocationsTableSeeder::class,
            NewUsersSeeder::class,
            MenuSeeder::class,
            ApprovalRulesSeeder::class,
        ]);
    }
}
