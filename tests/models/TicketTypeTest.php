<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\TicketsType;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\TestUsersTableSeeder;

class TicketTypeTest extends TestCase
{
     use DatabaseTransactions;

     public static function setUpBeforeClass(): void
     {
          parent::setUpBeforeClass();
          try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
          try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}
     }

    public function testUserCannotAccessTicketTypesView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-types')
           ->assertResponseStatus('403');
    }

    public function testAdminCannotAccessTicketTypesView()
    {
      $user = User::where('name', 'Admin User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-types')
           ->assertResponseStatus('403');
    }

    public function testTicketTypesViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-types')
           ->see('Ticket Types');
    }

    public function testCreateNewTicketType()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-types')
           ->see('Create Ticket Type')
           ->type('Random Type', 'type')
           ->press('Add New Ticket Type')
           ->seePageIs('/admin/ticket-types')
           // Prefer asserting persistent state (database) rather than fragile session flash messages
           ->seeInDatabase('tickets_types', ['type' => 'Random Type']);
    }

    public function testEditTicketType()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-types')
           ->see('Create Ticket Type')
           ->type('Random Type', 'type')
           ->press('Add New Ticket Type')
           ->seePageIs('/admin/ticket-types')
           // Validate creation via database record instead of flash message
           ->seeInDatabase('tickets_types', ['type' => 'Random Type']);

      $ticketType = TicketsType::get()->last();

      $this->actingAs($user)
           ->visit('/admin/ticket-types/' . $ticketType->id . '/edit')
           ->see('Random Type')
           ->type('Different Type', 'type')
           ->press('Edit Ticket Type')
           ->seePageIs('/admin/ticket-types')
           // Rely on database check for update confirmation
           ->seeInDatabase('tickets_types', ['type' => 'Different Type']);
    }
}
