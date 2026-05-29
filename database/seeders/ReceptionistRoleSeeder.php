<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class ReceptionistRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates Receptionist role with permissions for:
     * - Creating and viewing own tickets
     * - Full access to meeting room bookings
     * - Creating and viewing own asset requests
     * - Limited view access to other modules
     *
     * @return void
     */
    public function run()
    {
        // Create or get Receptionist role
        $receptionistRole = Role::firstOrCreate(
            ['name' => 'receptionist'],
            ['guard_name' => 'web']
        );

        // Define permissions for Receptionist
        $permissions = [
            // TICKETS - removed (legacy)
            // ============================================
            // MEETING ROOM BOOKINGS - Full Access
            // ============================================
            'meeting-rooms.view',       // View all bookings
            'meeting-rooms.create',     // Create new bookings
            'meeting-rooms.update',     // Update any booking
            'meeting-rooms.delete',     // Delete any booking
            'meeting-rooms.calendar',   // View calendar
            'meeting-rooms.manage',     // Manage meeting rooms
            
            // ============================================
            // ASSET REQUESTS - Create and View Own
            // ============================================
            'asset-requests.create',    // Create new asset requests
            'asset-requests.view',      // View asset requests (filtered to own)
            'asset-requests.view-own',  // Explicitly view own requests
            'asset-requests.update-own',// Update own requests (before approval)
            
            // ============================================
            // DAILY ACTIVITIES - View and Create Own
            // ============================================
            'daily-activities.view',    // View daily activities
            'daily-activities.create',  // Create daily activity entries
            'daily-activities.view-own',// View own activities
            
            // ============================================
            // BASIC ACCESS - Read-only information
            // ============================================
            'assets.view',              // View assets list (read-only)
            'locations.view',           // View locations (for forms)
            'divisions.view',           // View divisions (for forms)
            'users.view-list',          // View users list (for assignments)
            
            // ============================================
            // NOTIFICATIONS
            // ============================================
            'notifications.view',       // View own notifications
            'notifications.mark-read',  // Mark notifications as read
        ];

        // Create permissions if they don't exist and assign to role
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
            
            // Assign permission to role if not already assigned
            if (!$receptionistRole->hasPermissionTo($permission)) {
                $receptionistRole->givePermissionTo($permission);
            }
        }

        // Display summary
        $this->command->info('✅ Receptionist role created/updated successfully!');
        $this->command->info('📊 Total permissions assigned: ' . count($permissions));
        $this->command->line('');
        $this->command->info('Receptionist can:');
        // Ticket-related capabilities removed (legacy)
        $this->command->line('  ✓ Full access to meeting room bookings (CRUD)');
        $this->command->line('  ✓ Create and view own asset requests');
        $this->command->line('  ✓ View and create own daily activities');
        $this->command->line('  ✓ Read-only access to assets, locations, divisions');
        $this->command->line('  ✓ View notifications');
        $this->command->line('');

        // Optionally create a test receptionist user
        $this->createTestReceptionistUser($receptionistRole);
    }

    /**
     * Create a test receptionist user
     */
    private function createTestReceptionistUser($role)
    {
        $receptionistUser = User::firstOrCreate(
            ['email' => 'receptionist@quty.co.id'],
            [
                'name' => 'Receptionist',
                'password' => bcrypt('receptionist'),
                'is_active' => 1,
            ]
        );

        // Assign role if not already assigned
        if (!$receptionistUser->hasRole($role->name)) {
            $receptionistUser->assignRole($role->name);
            $this->command->info('✅ Test user created: receptionist@quty.co.id / receptionist');
        } else {
            $this->command->info('ℹ️  Test user already exists: receptionist@quty.co.id');
        }
    }
}
