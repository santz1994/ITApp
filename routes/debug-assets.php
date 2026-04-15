<?php
// Debug route to test asset loading
Route::get('/debug-assets', function() {
    $assets = App\Asset::with(['model', 'assignedTo', 'division'])
                      ->orderBy('asset_tag')
                      ->get();
    
    echo "<h1>Asset Debug</h1>";
    echo "<p>Total assets: " . $assets->count() . "</p>";
    
    echo "<h2>First 5 Assets:</h2>";
    foreach($assets->take(5) as $asset) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<strong>ID:</strong> " . $asset->id . "<br>";
        echo "<strong>Tag:</strong> " . $asset->asset_tag . "<br>";
        echo "<strong>Model ID:</strong> " . ($asset->model_id ?? 'NULL') . "<br>";
        echo "<strong>Model Loaded:</strong> " . ($asset->relationLoaded('model') ? 'YES' : 'NO') . "<br>";
        echo "<strong>Model Name:</strong> " . ($asset->model ? $asset->model->asset_model : 'NULL') . "<br>";
        echo "<strong>Assigned To ID:</strong> " . ($asset->assigned_to ?? 'NULL') . "<br>";
        echo "<strong>AssignedTo Loaded:</strong> " . ($asset->relationLoaded('assignedTo') ? 'YES' : 'NO') . "<br>";
        echo "<strong>User Name:</strong> " . ($asset->assignedTo ? $asset->assignedTo->name : 'NULL') . "<br>";
        echo "<strong>Division ID:</strong> " . ($asset->division_id ?? 'NULL') . "<br>";
        echo "<strong>Division Loaded:</strong> " . ($asset->relationLoaded('division') ? 'YES' : 'NO') . "<br>";
        echo "<strong>Division Name:</strong> " . ($asset->division ? ($asset->division->name ?? 'NO NAME') : 'NULL') . "<br>";
        
        echo "<hr>";
        echo "<strong>Dropdown text:</strong> ";
        echo ($asset->model ? $asset->model->asset_model : 'Unknown Model') . " (" . $asset->asset_tag . ") - (";
        echo ($asset->assignedTo ? $asset->assignedTo->name : 'Not Assigned') . ") ";
        echo ($asset->division ? ($asset->division->name ?? $asset->division->division_name) : 'No Division');
        
        echo "</div>";
    }
})->middleware('auth');
