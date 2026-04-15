<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixRoleAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix role assignments for test users in the system';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("=== Fixing Role Assignments ===");

        // Define test users and their roles
        $testUsers = [
            'Super Admin User' => 'super-admin',
            'Admin User' => 'admin',
            'User User' => 'user'
        ];

        foreach ($testUsers as $userName => $roleName) {
            $this->info("\nProcessing user: " . $userName);
            
            // Get or create user
            $user = User::where('name', $userName)->first();
            if (!$user) {
                $this->info("- User not found, creating...");
                
                $user = new User();
                $user->name = $userName;
                $user->email = strtolower(str_replace(' ', '', $userName)) . '@terryferreira.com';
                $user->password = bcrypt(strtolower(str_replace(' ', '', $userName)));
                $user->api_token = \Illuminate\Support\Str::random(60);
                $user->save();
                
                $this->info("- Created user with ID: " . $user->id);
            } else {
                $this->info("- User found with ID: " . $user->id);
            }
            
            // Get role
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                $this->error("- Role not found: " . $roleName);
                continue;
            }
            
            $this->info("- Found role: " . $role->name . " (ID: " . $role->id . ")");
            
            // Fix legacy role_user table
            if (Schema::hasTable('role_user')) {
                // First clear any existing roles
                DB::table('role_user')->where('user_id', $user->id)->delete();
                
                // Add new role
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $role->id
                ]);
                $this->info("- Fixed role_user table");
            }
            
            // Fix Spatie model_has_roles table
            try {
                // Clear existing roles and assign the new one
                $user->syncRoles([$role]);
                $this->info("- Fixed model_has_roles table using syncRoles()");
            } catch (\Exception $e) {
                $this->error("- Error using syncRoles(): " . $e->getMessage());
                
                // Fallback to direct database insertion if needed
                if (Schema::hasTable('model_has_roles')) {
                    // Clear existing roles
                    DB::table('model_has_roles')
                        ->where('model_id', $user->id)
                        ->where('model_type', get_class($user))
                        ->delete();
                    
                    // Add new role    
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role->id,
                        'model_type' => get_class($user),
                        'model_id' => $user->id
                    ]);
                    $this->info("- Fixed model_has_roles table using direct DB operations");
                }
            }
        }

        // Ensure role guard_name is set correctly
        $this->info("\nChecking role guard_name values...");
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'guard_name')) {
            $roles = Role::all();
            foreach ($roles as $role) {
                if (empty($role->guard_name)) {
                    $role->guard_name = 'web';
                    $role->save();
                    $this->info("- Updated role '{$role->name}' with guard_name 'web'");
                } else {
                    $this->info("- Role '{$role->name}' already has guard_name '{$role->guard_name}'");
                }
            }
        }

        $this->info("\n=== Role Assignment Fix Completed ===");
    }
}