<?php

namespace Database\Seeders;

use App\Asset;
use App\AssetModel;
use App\AssetRequest;
use App\AssetType;
use App\DailyActivity;
use App\Division;
use App\Location;
use App\Manufacturer;
use App\Status;
use App\Supplier;
use App\Ticket;
use App\TicketsPriority;
use App\TicketsStatus;
use App\TicketsType;
use App\User;
use App\WarrantyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Output info message (works whether called directly or from another seeder)
     */
    private function info($message)
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    /**
     * Run the database seeds to populate the database with realistic dummy data.
     *
     * @return void
     */
    public function run()
    {
        $this->info('🌱 Starting Dummy Data Seeding...');

        // 1. Create Locations (15 locations across multiple cities)
        $this->info('Creating locations...');
        $locations = Location::factory()->count(15)->create();
        $this->command->info('✅ Created 15 locations');

        // 2. Create Divisions (8 business divisions)
        $this->command->info('Creating divisions...');
        $divisions = Division::factory()->count(8)->create();
        $this->command->info('✅ Created 8 divisions');

        // 3. Create Users (20 additional users with various roles)
        $this->command->info('Creating additional users...');
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $userRole = DB::table('roles')->where('name', 'user')->first();

        // Create 5 admin users
        $adminUsers = User::factory()->count(5)->create()->each(function ($user) use ($adminRole) {
            if ($adminRole) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $adminRole->id,
                    'model_type' => 'App\\User',
                    'model_id' => $user->id
                ]);
            }
        });

        // Create 15 regular users
        $regularUsers = User::factory()->count(15)->create()->each(function ($user) use ($userRole) {
            if ($userRole) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $userRole->id,
                    'model_type' => 'App\\User',
                    'model_id' => $user->id
                ]);
            }
        });

        $allUsers = User::all();
        $this->command->info('✅ Created 20 additional users (5 admins, 15 regular users)');

        // 4. Create Supporting Data for Assets
        $this->command->info('Creating manufacturers...');
        $manufacturers = Manufacturer::factory()->count(10)->create();
        $this->command->info('✅ Created 10 manufacturers');

        $this->command->info('Creating suppliers...');
        $suppliers = Supplier::factory()->count(8)->create();
        $this->command->info('✅ Created 8 suppliers');

        $this->command->info('Creating warranty types...');
        $warrantyTypes = WarrantyType::factory()->count(5)->create();
        $this->command->info('✅ Created 5 warranty types');

        $this->command->info('Creating asset types...');
        $assetTypes = AssetType::factory()->count(12)->create();
        $this->command->info('✅ Created 12 asset types');

        $this->command->info('Creating asset models...');
        $assetModels = AssetModel::factory()->count(25)->create([
            'manufacturer_id' => fn() => $manufacturers->random()->id,
            'asset_type_id' => fn() => $assetTypes->random()->id,
        ]);
        $this->command->info('✅ Created 25 asset models');

        $this->command->info('Creating statuses...');
        $statuses = Status::factory()->count(8)->create();
        $this->command->info('✅ Created 8 asset statuses');

        // 5. Create Assets (100 assets across different types and statuses)
        $this->command->info('Creating assets...');
        $assets = Asset::factory()->count(100)->create([
            'model_id' => fn() => $assetModels->random()->id,
            'supplier_id' => fn() => $suppliers->random()->id,
            'status_id' => fn() => $statuses->random()->id,
            'assigned_to' => fn() => rand(0, 1) ? $allUsers->random()->id : null,
        ]);
        $this->command->info('✅ Created 100 assets');

        // 6. Get Existing Ticket Supporting Data (created by core seeders)
        $this->command->info('Retrieving existing ticket statuses...');
        $ticketStatuses = TicketsStatus::all();
        if ($ticketStatuses->count() === 0) {
            // If no statuses exist at all, run the proper seeder
            $this->call(\TicketsStatusesTableSeeder::class);
            $ticketStatuses = TicketsStatus::all();
        }
        $this->command->info('✅ Using ' . $ticketStatuses->count() . ' ticket statuses');

        $this->command->info('Retrieving existing ticket types...');
        $ticketTypes = TicketsType::all();
        if ($ticketTypes->count() === 0) {
            // If no types exist at all, run the proper seeder
            $this->call(\TicketsTypesTableSeeder::class);
            $ticketTypes = TicketsType::all();
        }
        $this->command->info('✅ Using ' . $ticketTypes->count() . ' ticket types');

        $this->command->info('Retrieving existing ticket priorities...');
        $ticketPriorities = TicketsPriority::all();
        if ($ticketPriorities->count() === 0) {
            // If no priorities exist at all, run the proper seeder
            $this->call(\TicketsPrioritiesTableSeeder::class);
            $ticketPriorities = TicketsPriority::all();
        }
        $this->command->info('✅ Using ' . $ticketPriorities->count() . ' ticket priorities');

        // 7. Create Tickets (50 tickets with various statuses and priorities)
        $this->command->info('Creating tickets...');
        $tickets = Ticket::factory()->count(50)->create([
            'user_id' => fn() => $allUsers->random()->id,
            'assigned_to' => fn() => $adminUsers->random()->id,
            'ticket_status_id' => fn() => $ticketStatuses->random()->id,
            'ticket_type_id' => fn() => $ticketTypes->random()->id,
            'ticket_priority_id' => fn() => $ticketPriorities->random()->id,
            'location_id' => fn() => $locations->random()->id,
        ]);
        $this->command->info('✅ Created 50 tickets');

        // 8. Create Asset Requests (30 asset requests with various statuses)
        $this->command->info('Creating asset requests...');
        $assetRequests = collect();
        
        // Create 15 pending requests
        $assetRequests = $assetRequests->merge(AssetRequest::factory()->count(15)->create([
            'requested_by' => fn() => $allUsers->random()->id,
            'asset_type_id' => fn() => $assetTypes->random()->id,
        ]));
        
        // Create 10 approved requests
        $assetRequests = $assetRequests->merge(AssetRequest::factory()->count(10)->approved()->create([
            'requested_by' => fn() => $allUsers->random()->id,
            'asset_type_id' => fn() => $assetTypes->random()->id,
            'approved_by' => fn() => $adminUsers->random()->id,
        ]));
        
        // Create 5 rejected requests
        $assetRequests = $assetRequests->merge(AssetRequest::factory()->count(5)->rejected()->create([
            'requested_by' => fn() => $allUsers->random()->id,
            'asset_type_id' => fn() => $assetTypes->random()->id,
            'approved_by' => fn() => $adminUsers->random()->id,
        ]));
        
        $this->command->info('✅ Created 30 asset requests (15 pending, 10 approved, 5 rejected)');

        // 9. Daily Activities (skipped - no factory available)
        // TODO: Create DailyActivityFactory if needed
        $this->command->info('⚠️  Skipping daily activities (no factory available)');

        // Summary
        $this->command->info('');
        $this->command->info('🎉 Dummy Data Seeding Complete!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 Summary:');
        $this->command->info('   • 15 Locations');
        $this->command->info('   • 8 Divisions');
        $this->command->info('   • 20 Additional Users (5 admins, 15 users)');
        $this->command->info('   • 10 Manufacturers');
        $this->command->info('   • 8 Suppliers');
        $this->command->info('   • 5 Warranty Types');
        $this->command->info('   • 12 Asset Types');
        $this->command->info('   • 25 Asset Models');
        $this->command->info('   • 8 Asset Statuses');
        $this->command->info('   • 100 Assets');
        $this->command->info('   • ' . $ticketStatuses->count() . ' Ticket Statuses (existing)');
        $this->command->info('   • ' . $ticketTypes->count() . ' Ticket Types (existing)');
        $this->command->info('   • ' . $ticketPriorities->count() . ' Ticket Priorities (existing)');
        $this->command->info('   • 50 Tickets');
        $this->command->info('   • 30 Asset Requests (15 pending, 10 approved, 5 rejected)');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✨ Your database is now populated with realistic dummy data!');
    }
}
