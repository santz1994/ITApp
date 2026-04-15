<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$email = 'idol@quty.co.id';

$hash = Hash::make('123456');
$token = bin2hex(random_bytes(16));

$updated = DB::table('users')->where('email', $email)->update([
    'password' => $hash,
    'api_token' => $token,
    'updated_at' => date('Y-m-d H:i:s'),
]);

if ($updated) {
    echo "Updated password and api_token for $email\n";
} else {
    echo "No rows updated for $email (user may not exist)\n";
}
