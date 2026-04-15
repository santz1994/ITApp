<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = \App\TicketsStatus::all();
echo "tickets_statuses rows:\n";
foreach ($rows as $r) {
    echo "{$r->id} | {$r->status}\n";
}

$schema = \Illuminate\Support\Facades\DB::select('SHOW CREATE TABLE tickets_statuses');
print_r($schema);
