<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'driver: ' . \Illuminate\Support\Facades\DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) . PHP_EOL;
echo 'database: ' . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . PHP_EOL;
