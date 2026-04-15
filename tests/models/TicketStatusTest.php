<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\TicketsStatus;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\TestUsersTableSeeder;

class TicketStatusTest extends TestCase
{
     use DatabaseTransactions;

     public static function setUpBeforeClass(): void
     {
          parent::setUpBeforeClass();
              try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
              try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}
     }

    public function testUserCannotAccessTicketStatusesView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-statuses')
           ->assertResponseStatus('403');
    }

    public function testAdminCannotAccessTicketStatusesView()
    {
      $user = User::where('name', 'Admin User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-statuses')
           ->assertResponseStatus('403');
    }

    public function testTicketStatusesViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-statuses')
           ->see('Ticket Statuses');
    }

    public function testCreateNewTicketStatus()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-statuses')
           ->see('Create Ticket Status')
           ->type('Random Status', 'status')
           ->press('Add New Ticket Status')
           ->seePageIs('/admin/ticket-statuses')
           // Prefer asserting persistent state (database) rather than fragile session flash messages
           ->seeInDatabase('tickets_statuses', ['status' => 'Random Status']);
    }

    public function testEditTicketStatus()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-statuses')
           ->see('Create Ticket Status')
           ->type('Random Status', 'status')
           ->press('Add New Ticket Status')
           ->seePageIs('/admin/ticket-statuses')
           // Validate creation via database record instead of flash message
           ->seeInDatabase('tickets_statuses', ['status' => 'Random Status']);

      $ticketStatus = TicketsStatus::get()->last();

      $this->actingAs($user)
           ->visit('/admin/ticket-statuses/' . $ticketStatus->id . '/edit')
           ->see('Random Status')
           ->type('Different Status', 'status')
           ->press('Edit Ticket Status')
           ->seePageIs('/admin/ticket-statuses')
           // Rely on database check for update confirmation
           ->seeInDatabase('tickets_statuses', ['status' => 'Different Status']);
    }
}
