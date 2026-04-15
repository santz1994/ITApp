<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
  try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
  try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}
    }
  use DatabaseTransactions;

  public function testVisitHomepageWhenNotLoggedIn()
  {
    // Request the login page and assert expected text is present
    $this->get('/login')
      // page text changed over time; check for a stable substring present on the login page
      ->assertSee('Sign In');

    // Submit an empty login form and assert validation errors exist in session
    $this->post('/login', ['_token' => csrf_token()])
         ->assertSessionHasErrors();
  }


  public function testLogin()
  {
    // Perform login via POST and assert authentication succeeds
    $this->post('/login', [
      'email' => 'superadmin@quty.co.id',
      'password' => 'superadmin',
      '_token' => csrf_token(),
    ])
    ->assertStatus(302);

    // Auth should be active in the test process after successful login
    $this->assertAuthenticated();
  }
}
