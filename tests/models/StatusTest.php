<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Status;

class StatusTest extends TestCase
{
    use DatabaseTransactions;

    public function testUserCannotAccessStatusView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/assets-statuses')
           ->assertResponseStatus('403');
    }

    public function testAdminCannotAccessStatusView()
    {
      $user = User::where('name', 'Admin User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/assets-statuses')
           ->assertResponseStatus('403');
    }

    public function testStatusViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/assets-statuses')
           ->see('Statuses');
    }

    public function testCreateNewStatus()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/assets-statuses')
           ->see('Create New Status')
           ->type('Random Status', 'name')
           ->press('Add New Status')
           ->seeInDatabase('statuses', ['name' => 'Random Status']);
    }

    public function testEditStatus()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/assets-statuses')
           ->see('Create New Status')
           ->type('Random Status', 'name')
           ->press('Add New Status')
           ->seeInDatabase('statuses', ['name' => 'Random Status']);

      $status = Status::get()->last();

      $this->actingAs($user)
           ->visit('/admin/assets-statuses/' . $status->id . '/edit')
           ->see('Random Status')
           ->type('Another Status', 'name')
           ->press('Edit Status')
           ->seePageIs('/admin/assets-statuses')
           ->see('Successfully updated')
           ->seeInDatabase('statuses', ['name' => 'Another Status']);
    }
}
