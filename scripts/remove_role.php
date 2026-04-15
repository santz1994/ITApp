<?php
// scripts/remove_role.php
// Usage: php scripts/remove_role.php <email> <role>
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'daniel@quty.co.id';
$roleToRemove = $argv[2] ?? 'admin';

$user = App\User::where('email', $email)->first();
if (! $user) {
    echo "USER_NOT_FOUND: $email\n";
    exit(2);
}

$before = $user->getRoleNames()->toArray();
try {
    if ($user->hasRole($roleToRemove)) {
        $user->removeRole($roleToRemove);
        echo "REMOVED role '$roleToRemove' from {$user->email}\n";
    } else {
        echo "USER_DID_NOT_HAVE_ROLE: {$user->email} did not have role '$roleToRemove'\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(3);
}

// Refresh and print roles after change
$user = App\User::where('email', $email)->first();
$after = $user->getRoleNames()->toArray();

echo "ROLES_BEFORE: " . implode(',', $before) . "\n";
echo "ROLES_AFTER: " . (empty($after) ? 'NO_ROLES' : implode(',', $after)) . "\n";
