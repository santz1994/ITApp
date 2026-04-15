<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;

class AdminPageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
  parent::setUpBeforeClass();
  try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
  try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}
    }
    use DatabaseTransactions;

    public function testUserCannotAccessAdminView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->get('/admin')
           ->assertResponseStatus('403');
    }

    public function testAdminCanAccessAdminView()
    {
      $user = User::where('name', 'Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin')
           ->see('User Management')
           ->see('Dashboard');
    }

    public function testAdminViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin')
           ->see('User Management')
           ->see('Ticket Configurations');
    }
}
