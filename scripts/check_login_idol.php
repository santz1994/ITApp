<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use Illuminate\Support\Facades\Hash;

$email = 'idol@quty.co.id';
$user = User::where('email', $email)->first();
if (! $user) {
    echo "User not found: $email\n";
    exit(1);
}

echo "User id: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "Name: {$user->name}\n";
echo "Password hash: {$user->password}\n";
echo "API token: " . ($user->api_token ?? '[null]') . "\n";
echo "is_active: " . (property_exists($user, 'is_active') ? ($user->is_active ? '1' : '0') : '[n/a]') . "\n";
echo "created_at: {$user->created_at}\n";
echo "updated_at: {$user->updated_at}\n";

$check = Hash::check('123456', $user->password) ? 'OK' : 'FAIL';
echo "Password verify (123456): $check\n";

// roles
$roles = $user->getRoleNames();
echo "Roles: " . ($roles->isEmpty() ? '[none]' : $roles->implode(', ')) . "\n";
