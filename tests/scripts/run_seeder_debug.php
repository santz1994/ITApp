<?php
putenv('APP_ENV=testing');
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    if (class_exists('\Database\Seeders\TestUsersTableSeeder')) {
        echo "Class exists: Database\\Seeders\\TestUsersTableSeeder\n";
        $s = new \Database\Seeders\TestUsersTableSeeder();
        echo "Instantiated seeder\n";
        $s->run();
        echo "Seeder run OK\n";
    } else {
        echo "Namespaced seeder class does not exist\n";
    }
} catch (Throwable $e) {
    echo "Seeder threw: " . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
