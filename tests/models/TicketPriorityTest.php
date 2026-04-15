            try { if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) { (new \Database\Seeders\LocationsTableSeeder())->run(); } } catch (\Throwable $__e) {}
            try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
            try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}

class TicketPriorityTest extends TestCase
{
     use DatabaseTransactions;

     public static function setUpBeforeClass(): void
     {
          parent::setUpBeforeClass();
          try { if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) { (new \Database\Seeders\LocationsTableSeeder())->run(); } } catch (\Throwable $__e) {}
          try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
          try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}
     }

    public function testUserCannotAccessTicketPrioritiesView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-priorities')
           ->assertResponseStatus('403');
    }

    public function testAdminCannotAccessTicketPrioritiesView()
    {
      $user = User::where('name', 'Admin User')->get()->first();

      $this->actingAs($user)
           ->get('/admin/ticket-priorities')
           ->assertResponseStatus('403');
    }

    public function testTicketPrioritiesViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-priorities')
           ->see('Ticket Priorities');
    }

    public function testCreateNewTicketPriority()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-priorities')
           ->see('Create Ticket Priority')
           ->type('Random Priority', 'priority')
           ->press('Add New Ticket Priority')
           ->seePageIs('/admin/ticket-priorities')
           ->seeInDatabase('tickets_priorities', ['priority' => 'Random Priority']);
    }

    public function testEditTicketPriority()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/admin/ticket-priorities')
           ->see('Create Ticket Priority')
           ->type('Random Priority', 'priority')
           ->press('Add New Ticket Priority')
           ->seePageIs('/admin/ticket-priorities')
           ->seeInDatabase('tickets_priorities', ['priority' => 'Random Priority']);

      $ticketPriority = TicketsPriority::get()->last();

      $this->actingAs($user)
           ->visit('/admin/ticket-priorities/' . $ticketPriority->id . '/edit')
           ->see('Random Priority')
           ->type('Different Priority', 'priority')
           ->press('Edit Ticket Priority')
           ->seePageIs('/admin/ticket-priorities')
           ->see('Successfully updated')
           ->seeInDatabase('tickets_priorities', ['priority' => 'Different Priority']);
    }
}
