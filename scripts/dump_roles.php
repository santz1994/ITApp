<?php
/**
 * Database role inspection script
 * Run with: php scripts/dump_roles.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== USERS ===\n";
$users = DB::table('users')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}

echo "\n=== ROLES ===\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "ID: {$role->id}, Name: {$role->name}, Display name: " . 
        ($role->display_name ?? '[none]') . "\n";
}

echo "\n=== MODEL_HAS_ROLES ===\n";
if (Schema::hasTable('model_has_roles')) {
    $roleAssignments = DB::table('model_has_roles')->get();
    if ($roleAssignments->count() > 0) {
        foreach ($roleAssignments as $assignment) {
            $roleName = DB::table('roles')->where('id', $assignment->role_id)->value('name') ?? 'unknown';
            echo "Role ID: {$assignment->role_id} ({$roleName}), Model ID: {$assignment->model_id}, Type: {$assignment->model_type}\n";
        }
    } else {
        echo "No role assignments found!\n";
    }
} else {
    echo "Table model_has_roles does not exist!\n";
    
    // Check for legacy role_user table
    if (Schema::hasTable('role_user')) {
        echo "\n=== LEGACY ROLE_USER TABLE ===\n";
        $legacyAssignments = DB::table('role_user')->get();
        if ($legacyAssignments->count() > 0) {
            foreach ($legacyAssignments as $assignment) {
                $roleName = DB::table('roles')->where('id', $assignment->role_id)->value('name') ?? 'unknown';
                echo "Role ID: {$assignment->role_id} ({$roleName}), User ID: {$assignment->user_id}\n";
            }
        } else {
            echo "No legacy role assignments found!\n";
        }
    }
}

echo "\n=== TEST PERMISSIONS CHECK ===\n";
// Check if super admin has access to /admin/users
$superAdmin = \App\User::where('name', 'Super Admin User')->first();
if ($superAdmin) {
    echo "Checking if Super Admin User can access /admin/users:\n";
    echo "- Has role 'super-admin': " . ($superAdmin->hasRole('super-admin') ? 'YES' : 'NO') . "\n";
    
    // Get route collection and check middleware
    $routes = app('router')->getRoutes();
    $userRoute = null;
    foreach ($routes as $route) {
        if ($route->uri() === 'admin/users') {
            $userRoute = $route;
            break;
        }
    }
    
    if ($userRoute) {
        echo "- Route /admin/users exists with middleware: " . 
            (is_array($userRoute->middleware()) ? implode(', ', $userRoute->middleware()) : $userRoute->middleware()) . "\n";
    } else {
        echo "- Route /admin/users not found in route collection!\n";
    }
}

// Check role middleware
echo "\n=== ROLE MIDDLEWARE CHECK ===\n";
$middleware = app(\App\Http\Kernel::class)->getRouteMiddleware();
if (isset($middleware['role'])) {
    echo "Role middleware is registered as: " . $middleware['role'] . "\n";
} else {
    echo "Role middleware not found in route middleware registry!\n";
}

// Check seeder configuration
echo "\n=== SEEDER CONFIG ===\n";
$seeder = new DatabaseSeeder();
echo "DatabaseSeeder class: " . get_class($seeder) . "\n";
echo "Called seeders: ";
try {
    $reflection = new ReflectionClass($seeder);
    $runMethod = $reflection->getMethod('run');
    $file = $reflection->getFileName();
    $startLine = $runMethod->getStartLine();
    $endLine = $runMethod->getEndLine();
    
    $lines = file($file);
    echo "Lines {$startLine}-{$endLine} of " . basename($file) . ":\n";
    for ($i = $startLine; $i < $endLine; $i++) {
        echo trim($lines[$i]) . "\n";
    }
} catch (Exception $e) {
    echo "Failed to get seeder details: " . $e->getMessage() . "\n";
}