<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Supplier;

class SupplierTest extends TestCase
{
  use DatabaseTransactions;

    protected function setUp(): void
    {
      parent::setUp();
      // Seed locations table for supplier tests (needed for foreign key integrity)
  try { if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) { (new \Database\Seeders\LocationsTableSeeder())->run(); } } catch (\Throwable $__e) {}
    }

  public function testUserCannotAccessSuppliersView()
  {
    $user = User::where('name', 'User User')->get()->first();

    $this->actingAs($user)
         ->get('/suppliers')
         ->assertResponseStatus('403');
  }

  public function testAdminCannotAccessSuppliersView()
  {
    $user = User::where('name', 'Admin User')->get()->first();

    $this->actingAs($user)
         ->get('/suppliers')
         ->assertResponseStatus('403');
  }

  public function testSuppliersViewWithLoggedInSuperAdmin()
  {
    $user = User::where('name', 'Super Admin User')->get()->first();

    $this->actingAs($user)
         ->visit('/suppliers')
         ->see('Suppliers');
  }

  public function testCreateNewSupplier()
  {
    $user = User::where('name', 'Super Admin User')->get()->first();

    $this->actingAs($user)
         ->visit('/suppliers')
         ->see('Create New Supplier')
         ->type('Acme', 'name')
         ->press('Add New Supplier')
         ->seePageIs('/suppliers')
         ->seeInDatabase('suppliers', ['name' => 'Acme']);
  }

  public function testEditSupplier()
  {
    $user = User::where('name', 'Super Admin User')->get()->first();

    $this->actingAs($user)
         ->visit('/suppliers')
         ->see('Create New Supplier')
         ->type('Acme', 'name')
         ->press('Add New Supplier')
         ->seePageIs('/suppliers')
         ->seeInDatabase('suppliers', ['name' => 'Acme']);

    $supplier = Supplier::get()->last();

    $this->actingAs($user)
         ->visit('/suppliers/' . $supplier->id . '/edit')
         ->see('Acme')
         ->type('Spacely Space Sprockets', 'name')
         ->press('Edit Supplier')
         ->seePageIs('/suppliers')
         ->seeInDatabase('suppliers', ['name' => 'Spacely Space Sprockets']);
  }
}
