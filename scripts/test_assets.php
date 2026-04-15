<?php
// Quick test script to check asset data
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get first asset with relationships
$asset = App\Asset::with(['model', 'assignedTo', 'division'])->first();

if ($asset) {
    echo "Asset ID: " . $asset->id . "\n";
    echo "Asset Tag: " . $asset->asset_tag . "\n";
    echo "Model ID: " . $asset->model_id . "\n";
    echo "Assigned To: " . $asset->assigned_to . "\n";
    echo "Division ID: " . $asset->division_id . "\n";
    echo "\n";
    
    echo "Model relationship loaded: " . ($asset->relationLoaded('model') ? 'YES' : 'NO') . "\n";
    if ($asset->model) {
        echo "Model Name: " . $asset->model->asset_model . "\n";
    } else {
        echo "Model: NULL\n";
    }
    
    echo "\nAssignedTo relationship loaded: " . ($asset->relationLoaded('assignedTo') ? 'YES' : 'NO') . "\n";
    if ($asset->assignedTo) {
        echo "User Name: " . $asset->assignedTo->name . "\n";
    } else {
        echo "User: NULL\n";
    }
    
    echo "\nDivision relationship loaded: " . ($asset->relationLoaded('division') ? 'YES' : 'NO') . "\n";
    if ($asset->division) {
        echo "Division Name: " . ($asset->division->name ?? $asset->division->division_name ?? 'NO NAME') . "\n";
    } else {
        echo "Division: NULL\n";
    }
} else {
    echo "No assets found!\n";
}
