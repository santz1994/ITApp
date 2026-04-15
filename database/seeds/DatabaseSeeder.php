<?php

use Illuminate\Database\Seeder;
use Database\Seeders\TicketsStatusesTableSeeder;
use Database\Seeders\TicketsTypesTableSeeder;
use Database\Seeders\TicketsPrioritiesTableSeeder;
use Database\Seeders\StatusesTableSeeder;
use Database\Seeders\WarrantyTypesTableSeeder;
use Database\Seeders\AssetTypesTableSeeder;
use Database\Seeders\ManufacturersTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\AddPermissionsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AssignRolesTableSeeder;
use Database\Seeders\LocationsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /**
       * These DB Seeds will create your default data you will require.
       */
      $this->call(TicketsStatusesTableSeeder::class);
      $this->call(TicketsTypesTableSeeder::class);
      $this->call(TicketsPrioritiesTableSeeder::class);
      $this->call(StatusesTableSeeder::class);
      $this->call(WarrantyTypesTableSeeder::class);
      $this->call(AssetTypesTableSeeder::class);
      $this->call(ManufacturersTableSeeder::class);
      $this->call(RolesTableSeeder::class);
      $this->call(PermissionsTableSeeder::class);
      $this->call(AddPermissionsTableSeeder::class);
      $this->call(UsersTableSeeder::class);
      $this->call(AssignRolesTableSeeder::class);

      /**
       * The commented lines are those that can be used to generate data for testing.
       * Uncomment those you'd like to seed before running 'artisan db:seed'.
       */

    $this->call(\Database\Seeders\TestUsersTableSeeder::class);
    $this->call(\Database\Seeders\ReceptionistRoleSeeder::class); // Receptionist role with permissions
      // $this->call(TestAssignRolesTableSeeder::class);
      // $this->call(DivisionsTableSeeder::class);
    $this->call(LocationsTableSeeder::class);
      // $this->call(SuppliersTableSeeder::class);
      // $this->call(PcspecsTableSeeder::class);
      // $this->call(AssetModelsTableSeeder::class);
    
    // Note: Run DummyDataSeeder separately if needed
    // $this->call(\Database\Seeders\DummyDataSeeder::class);
    }
}
