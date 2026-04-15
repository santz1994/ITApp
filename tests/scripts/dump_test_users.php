<?php
// Dump users from the application database to help triage why tests can't find seeded users.
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\User::all()->map(function($u) {
    return [
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'roles' => method_exists($u, 'getRoleNames') ? $u->getRoleNames()->toArray() : []
    ];
});

echo json_encode($users->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
