<?php
// one-off script to create superadmin users using the app's DB config and models
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Role;

$users = [
    ['name' => 'Idol', 'email' => 'idol@quty.co.id', 'password' => '123456'],
    ['name' => 'Daniel', 'email' => 'daniel@quty.co.id', 'password' => '123456'],
];

$role = Role::where('name', 'super-admin')->first();
if (! $role) {
    echo "Super-admin role not found.\n";
    exit(1);
}

foreach ($users as $u) {
    $existing = User::where('email', $u['email'])->first();
    if ($existing) {
        echo "User {$u['email']} already exists as id={$existing->id}. Attaching role...\n";
        $existing->roles()->syncWithoutDetaching([$role->id]);
        continue;
    }

    // Create via attributes (avoid mass-assigning api_token which is not in $fillable)
    $user = new User();
    $user->name = $u['name'];
    $user->email = $u['email'];
    // The User model defines a password mutator, so assigning plain text will hash it
    $user->password = $u['password'];
    // Ensure a unique api_token is set (app requires non-null unique api_token)
    $user->api_token = bin2hex(random_bytes(16));
    $user->save();

    $user->roles()->attach($role->id);
    echo "Created user {$u['email']} id={$user->id} and attached role {$role->name} (id={$role->id}).\n";
}

echo "Done.\n";
