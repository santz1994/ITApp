<?php
// assign_admin.php - run from project root: php scripts/assign_admin.php <email>
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

try {
    $user->assignRole('super-admin');
    echo "ASSIGNED: {$user->email}\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(3);
}
