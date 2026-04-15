<?php
// Check users table
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get first asset
$asset = App\Asset::first();

echo "Asset ID: " . $asset->id . "\n";
echo "Asset Tag: " . $asset->asset_tag . "\n";
echo "Assigned To (FK): " . ($asset->assigned_to ?? 'NULL') . "\n";

if ($asset->assigned_to) {
    // Check if user exists
    $user = App\User::find($asset->assigned_to);
    if ($user) {
        echo "User Found: " . $user->name . " (ID: " . $user->id . ")\n";
    } else {
        echo "User NOT FOUND for ID: " . $asset->assigned_to . "\n";
    }
    
    // Check via relationship
    $userViaRelation = $asset->assignedTo;
    if ($userViaRelation) {
        echo "Via Relationship: " . $userViaRelation->name . "\n";
    } else {
        echo "Via Relationship: NULL\n";
    }
}

// Check assets stats
$totalAssets = App\Asset::count();
$assetsWithUsers = App\Asset::whereNotNull('assigned_to')->count();
$assetsWithValidUsers = App\Asset::whereNotNull('assigned_to')
    ->whereHas('assignedTo')
    ->count();

echo "\nStats:\n";
echo "Total Assets: $totalAssets\n";
echo "Assets with assigned_to value: $assetsWithUsers\n";
echo "Assets with valid user relationship: $assetsWithValidUsers\n";
