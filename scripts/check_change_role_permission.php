<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Role;

$perm = DB::table('permissions')->where('name', 'change-role')->first();
echo "Permission 'change-role': " . ($perm ? 'FOUND (id=' . $perm->id . ')' : 'NOT FOUND') . PHP_EOL;

$role = Role::where('name','super-admin')->first();
if (! $role) {
    echo "Role 'super-admin' not found.\n";
    exit(0);
}

echo "Role super-admin id={$role->id}\n";
$has = DB::table('role_has_permissions')->where('role_id', $role->id)
        ->join('permissions','role_has_permissions.permission_id','=','permissions.id')
        ->select('permissions.id','permissions.name')->get();

if ($has->isEmpty()) {
    echo "Role super-admin has NO permissions attached.\n";
} else {
    echo "Role super-admin permissions:\n";
    foreach ($has as $h) {
        echo " - {$h->name} (id={$h->id})\n";
    }
}

// List model_has_roles entries for users
$mh = DB::table('model_has_roles')->where('model_type', App\User::class)->get();
echo "model_has_roles entries (user roles count): " . $mh->count() . PHP_EOL;

// Check user idol
$user = DB::table('users')->where('email', 'idol@quty.co.id')->first();
if ($user) {
    echo "Found user idol id={$user->id}\n";
    $uRoles = DB::table('model_has_roles')->where('model_type', App\User::class)->where('model_id', $user->id)
              ->join('roles','model_has_roles.role_id','=','roles.id')->select('roles.id','roles.name')->get();
    if ($uRoles->isEmpty()) {
        echo "User idol has NO roles attached.\n";
    } else {
        echo "User idol roles:\n";
        foreach ($uRoles as $r) echo " - {$r->name} (id={$r->id})\n";
    }
} else {
    echo "User idol not found in DB.\n";
}
