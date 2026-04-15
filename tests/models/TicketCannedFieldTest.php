<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\TicketsCannedField;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Database\Seeders\LocationsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\TestUsersTableSeeder;

class TicketCannedFieldTest extends TestCase
{
     use DatabaseTransactions;

     public static function setUpBeforeClass(): void
     {
          parent::setUpBeforeClass();
            try {
                 if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) {
                      (new \Database\Seeders\LocationsTableSeeder())->run();
                 }
            } catch (\Throwable $__e) {}
            try {
                 if (class_exists(\Database\Seeders\RolesTableSeeder::class)) {
                      (new \Database\Seeders\RolesTableSeeder())->run();
                 }
            } catch (\Throwable $__e) {}
            try {
                 if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) {
                      (new \Database\Seeders\TestUsersTableSeeder())->run();
                 }
            } catch (\Throwable $__e) {}
     }

    public function testUserCannotAccessTicketCannedFieldView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-canned-fields')
           ->assertResponseStatus('403');
    }

     protected function setUp(): void
     {
          parent::setUp();

          // Ensure minimal roles exist for these tests (idempotent)
          try {
               Role::firstOrCreate(['name' => 'super-admin']);
               Role::firstOrCreate(['name' => 'admin']);
               Role::firstOrCreate(['name' => 'management']);
               Role::firstOrCreate(['name' => 'user']);
          } catch (\Throwable $__e) {
               // ignore permission table issues during quick test bootstrapping
          }

          // Ensure legacy-named users exist and have the expected roles
          $legacy = [
               ['name' => 'Super Admin User', 'email' => 'superadmin.user@test', 'role' => 'super-admin'],
               ['name' => 'Admin User', 'email' => 'admin.user@test', 'role' => 'admin'],
               ['name' => 'User User', 'email' => 'user.user@test', 'role' => 'user'],
          ];

          foreach ($legacy as $l) {
               try {
                    $u = User::firstOrCreate([
                         'email' => $l['email'],
                    ], [
                         'name' => $l['name'],
                         'password' => bcrypt('password'),
                         'api_token' => Str::random(60),
                    ]);
                    // assign role if role exists
                    try {
                         $u->assignRole($l['role']);
                    } catch (\Throwable $_) {
                         // ignore if roles/permissions table not ready
                    }
               } catch (\Throwable $_) {
                    // ignore creation errors during test bootstrap
               }
          }
     }

    public function testAdminCannotAccessTicketCannedFieldView()
    {
      $user = User::where('name', 'Admin User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-canned-fields')
           ->assertResponseStatus('403');
    }

    public function testTicketCannedFieldViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-canned-fields')
           ->see('Canned Ticket Fields');
    }

    public function testCreateNewTicketCannedField()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-canned-fields')
           ->see('Create Canned Ticket Field')
           ->select(1, 'user_id')
           ->select(1, 'location_id')
           ->select(1, 'ticket_status_id')
           ->select(1, 'ticket_type_id')
           ->select(1, 'ticket_priority_id')
           ->type('Random Subject', 'subject')
           ->type('Random Description', 'description')
           ->press('Add New Ticket Canned Fields')
           ->seePageIs('/admin/ticket-canned-fields')
           ->seeInDatabase('tickets_canned_fields', ['user_id' => 1, 'location_id' => 1, 'ticket_status_id' => 1, 'ticket_type_id' => 1, 'ticket_priority_id' => 1, 'subject' => 'Random Subject', 'description' => 'Random Description']);
    }

    public function testEditTicketCannedField()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-canned-fields')
           ->see('Create Canned Ticket Field')
           ->select(1, 'user_id')
           ->select(1, 'location_id')
           ->select(1, 'ticket_status_id')
           ->select(1, 'ticket_type_id')
           ->select(1, 'ticket_priority_id')
           ->type('Random Subject', 'subject')
           ->type('Random Description', 'description')
           ->press('Add New Ticket Canned Fields')
           ->seePageIs('/admin/ticket-canned-fields')
           ->seeInDatabase('tickets_canned_fields', ['user_id' => 1, 'location_id' => 1, 'ticket_status_id' => 1, 'ticket_type_id' => 1, 'ticket_priority_id' => 1, 'subject' => 'Random Subject', 'description' => 'Random Description']);

      $ticketCannedField = TicketsCannedField::get()->last();

      $this->actingAs($user)
           ->visit('/admin/ticket-canned-fields/' . $ticketCannedField->id . '/edit')
           ->see('Random Subject')
           ->type('Different Subject', 'subject')
           ->select($ticketCannedField->user_id, 'user_id')
           ->select(2, 'location_id')
           ->select($ticketCannedField->ticket_status_id, 'ticket_status_id')
           ->select($ticketCannedField->ticket_type_id, 'ticket_type_id')
           ->select($ticketCannedField->ticket_priority_id, 'ticket_priority_id')
           ->type('Random Description', 'description')
           ->press('Edit Ticket Canned Fields')
           ->seePageIs('/admin/ticket-canned-fields')
           ->seeInDatabase('tickets_canned_fields', ['user_id' => 1, 'location_id' => 2, 'ticket_status_id' => 1, 'ticket_type_id' => 1, 'ticket_priority_id' => 1, 'subject' => 'Different Subject', 'description' => 'Random Description']);
    }
}
