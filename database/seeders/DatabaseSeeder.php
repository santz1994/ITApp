<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Defer to the legacy global DatabaseSeeder which exists for backward compatibility.
        if (class_exists('\DatabaseSeeder')) {
            $legacy = new \DatabaseSeeder();
            $legacy->run();
            return;
        }

        // Fallback: require old DatabaseSeeder and call it if present
        if (file_exists(database_path('seeds/DatabaseSeeder.php'))) {
            require_once database_path('seeds/DatabaseSeeder.php');
            if (class_exists('\DatabaseSeeder')) {
                $legacy = new \DatabaseSeeder();
                $legacy->run();
            }
        }
    }
}
