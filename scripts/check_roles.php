<?php
// scripts/check_roles.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'daniel@quty.co.id';
$user = App\User::where('email', $email)->first();
if (! $user) {
    echo "USER_NOT_FOUND: $email\n";
    exit(2);
}
$roles = $user->getRoleNames()->toArray();
if (empty($roles)) {
    echo "NO_ROLES\n";
} else {
    echo "ROLES: " . implode(',', $roles) . "\n";
}
