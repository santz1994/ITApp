<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Role;

// Create permission if missing
$perm = DB::table('permissions')->where('name', 'change-role')->first();
if (! $perm) {
    $permId = DB::table('permissions')->insertGetId([
        'name' => 'change-role',
        'guard_name' => 'web',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "Inserted permission 'change-role' id={$permId}\n";
} else {
    $permId = $perm->id;
    echo "Permission already exists id={$permId}\n";
}

$role = Role::where('name','super-admin')->first();
if (! $role) {
    echo "Role super-admin not found\n";
    exit(1);
}

// Attach permission to role if not attached
$exists = DB::table('role_has_permissions')->where('role_id', $role->id)->where('permission_id', $permId)->first();
if (! $exists) {
    DB::table('role_has_permissions')->insert(['permission_id' => $permId, 'role_id' => $role->id]);
    echo "Attached permission id={$permId} to role super-admin id={$role->id}\n";
} else {
    echo "Role already has permission attached\n";
}
