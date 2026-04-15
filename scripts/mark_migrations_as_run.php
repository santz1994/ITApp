<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Migrations to mark as run (batch 3)
$migrations = [
    '2025_10_15_100912_create_media_table',
    '2025_10_15_103655_create_activity_logs_table',
    '2025_10_15_103707_create_sla_policies_table',
    '2025_10_15_103715_create_knowledge_base_articles_table',
    '2025_10_15_103723_create_asset_lifecycle_events_table',
    '2025_10_15_112745_add_optimized_database_indexes',
    '2025_10_15_120158_seed_default_sla_policies',
];

$batch = 3;

foreach ($migrations as $migration) {
    try {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "✓ Marked: {$migration}\n";
    } catch (\Exception $e) {
        echo "✗ Error marking {$migration}: " . $e->getMessage() . "\n";
    }
}

echo "\nAll migrations marked as run!\n";
