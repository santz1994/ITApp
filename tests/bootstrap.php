<?php

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load Laravel's bootstrap (for legacy TestCase which requires it)
require __DIR__ . '/../bootstrap/autoload.php';

// Ensure TestCase is loaded
require __DIR__ . '/TestCase.php';

// Create a global alias for legacy tests that reference TestCase in the global namespace
if (!class_exists('TestCase') && class_exists(\Tests\TestCase::class)) {
	class_alias(\Tests\TestCase::class, 'TestCase');
}

// You can set up any test environment initialization here
// Bootstrap the application and run migrations/seeds so tests have expected data.
$cacheServices = __DIR__ . '/../bootstrap/cache/services.php';
$cachePackages = __DIR__ . '/../bootstrap/cache/packages.php';
if (file_exists($cacheServices)) { @unlink($cacheServices); }
if (file_exists($cachePackages)) { @unlink($cachePackages); }

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run a fresh migration and seed to ensure the testing database has expected records.
// WARNING: This will drop all tables in the configured testing database. It's intended
// for local test runs. phpunit.xml should point to a disposable database for tests.
try {
	// Ensure the sqlite testing DB file exists when DB_DATABASE in phpunit.xml points to a path.
	$testingDb = env('DB_DATABASE', database_path('testing.sqlite'));
	// If sqlite is used and the file doesn't exist, create it so migrations can run.
	if (env('DB_CONNECTION') === 'sqlite') {
		if (!empty($testingDb) && !file_exists($testingDb)) {
			// create an empty sqlite file so migrations can run
			@touch($testingDb);
		}
	}
	
	// If MySQL is configured for tests, attempt to create the database if it
	// doesn't already exist. This is a best-effort step to make local test runs
	// easier; it will not throw on failure so PHPUnit can show a clear error
	// if the DB couldn't be created or connected to.
	if (env('DB_CONNECTION') === 'mysql') {
		$host = env('DB_HOST', '127.0.0.1');
		$port = env('DB_PORT', '3306');
		$db = env('DB_DATABASE', 'itquty');
		$user = env('DB_USERNAME', 'root');
		$pass = env('DB_PASSWORD', '');
		try {
			// Try using mysqli (widely available) to create the database.
			$mysqli = @mysqli_init();
			if ($mysqli) {
				$connected = @mysqli_real_connect($mysqli, $host, $user, $pass, null, (int)$port);
				if ($connected) {
					$query = "CREATE DATABASE IF NOT EXISTS `" . addslashes($db) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
					@mysqli_query($mysqli, $query);
					@mysqli_close($mysqli);
				}
			}
		} catch (\Exception $e) {
			// ignore - best effort
		}
	}

	// Run migrations and required seeders for foreign key tables.
	// Use direct seeder invocation here to avoid the Console ConfirmableTrait
	// which calls the container environment access and can trigger resolution
	// issues in the PHPUnit runtime. This is a best-effort bootstrap for tests.
	$kernel->call('migrate:fresh', ['--force' => true]);

	try {
		// Directly instantiate and run the seeders that tests expect.
		$locationsSeeder = new \Database\Seeders\LocationsTableSeeder();
		if (method_exists($locationsSeeder, 'run')) { $locationsSeeder->run(); }

		$ticketsStatusesSeeder = new \Database\Seeders\TicketsStatusesTableSeeder();
		if (method_exists($ticketsStatusesSeeder, 'run')) { $ticketsStatusesSeeder->run(); }

		$ticketsTypesSeeder = new \Database\Seeders\TicketsTypesTableSeeder();
		if (method_exists($ticketsTypesSeeder, 'run')) { $ticketsTypesSeeder->run(); }

		$ticketsPrioritiesSeeder = new \Database\Seeders\TicketsPrioritiesTableSeeder();
		if (method_exists($ticketsPrioritiesSeeder, 'run')) { $ticketsPrioritiesSeeder->run(); }

		// Seed permissions and roles used by tests (conservative list)
		$permSeeder = new \Database\Seeders\PermissionsAndRolesSeeder();
		if (method_exists($permSeeder, 'run')) { $permSeeder->run(); }

		// Clear Spatie permission cache so tests see fresh permissions
		if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
			$app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		}

	} catch (Exception $e) {
		// ignore seeder errors in test bootstrap; let tests fail with clearer messages
	}
} catch (Exception $e) {
	// If migrations/seeds fail, let PHPUnit report the error in test runs instead of dying here.
}
// Temporary: dump roles table to storage/logs/test_shim_debug.log so we can see seeded role id mapping
try {
	$dbConnection = env('DB_CONNECTION', 'mysql');
	if ($dbConnection === 'mysql' || $dbConnection === 'sqlite') {
		// Build DSN for PDO based on connection
		if ($dbConnection === 'mysql') {
			$host = env('DB_HOST', '127.0.0.1');
			$port = env('DB_PORT', '3306');
			$db = env('DB_DATABASE', 'itquty');
			$user = env('DB_USERNAME', 'root');
			$pass = env('DB_PASSWORD', '');
			$dsn = "mysql:host={$host};port={$port};dbname={$db}";
		} else {
			$dbFile = env('DB_DATABASE', database_path('testing.sqlite'));
			$dsn = "sqlite:" . $dbFile;
			$user = null;
			$pass = null;
		}

		$pdo = new PDO($dsn, $user, $pass);
		$stmt = $pdo->query('SELECT id, name FROM roles');
		$roles = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
		$logPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'test_shim_debug.log';
		$dump = "[roles-dump] " . json_encode($roles) . PHP_EOL;
		file_put_contents($logPath, $dump, FILE_APPEND | LOCK_EX);
	}
} catch (Exception $e) {
	// non-fatal; leave tests to proceed
}

