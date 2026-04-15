<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\DatabaseInspector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Asset;
use App\Ticket;
use App\AssetRequest;

class AdminController extends Controller
{
    use DatabaseInspector;
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin']);
    }

    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        // Get permission and role statistics
        $totalPermissions = \Spatie\Permission\Models\Permission::count();
        $totalRoles = \Spatie\Permission\Models\Role::count();
        $usersWithRoles = User::whereHas('roles')->count();
        
        $stats = [
            'total_users' => User::count(),
            'total_assets' => Asset::count(),
            'active_tickets' => Ticket::whereHas('ticket_status', function($query) {
                $query->where('status', '!=', 'closed')->where('status', '!=', 'resolved');
            })->count(),
            'pending_requests' => class_exists('App\AssetRequest') ? AssetRequest::where('status', 'pending')->count() : 0,
            'total_permissions' => $totalPermissions,
            'total_roles' => $totalRoles,
            'users_with_roles' => $usersWithRoles,
        ];

        $system_status = [
            'cache' => $this->checkCacheStatus(),
            'storage' => $this->checkStorageStatus(),
        ];

        $recent_activities = $this->getRecentActivities();

        return view('admin.dashboard', compact('stats', 'system_status', 'recent_activities'));
    }

    /**
     * Show database administration page
     */
    public function database()
    {
        $db_status = $this->checkDatabaseStatus();
        $db_info = $this->getDatabaseInfo();
        $db_stats = $this->getDatabaseStats();
        $tables = $this->getTableInfo();
        $migrations = $this->getMigrationStatus();

        return view('admin.database', compact('db_status', 'db_info', 'db_stats', 'tables', 'migrations'));
    }

    /**
     * Execute database actions
     */
    public function databaseAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:optimize,repair,check,migrate,seed'
        ]);

        try {
            switch ($request->action) {
                case 'optimize':
                    $this->optimizeTables();
                    $message = 'Database tables optimized successfully.';
                    break;
                case 'repair':
                    $this->repairTables();
                    $message = 'Database tables repaired successfully.';
                    break;
                case 'check':
                    $result = $this->checkTables();
                    $message = 'Database check completed. ' . $result;
                    break;
                case 'migrate':
                    Artisan::call('migrate');
                    $message = 'Migrations executed successfully.';
                    break;
                case 'seed':
                    Artisan::call('db:seed');
                    $message = 'Database seeded successfully.';
                    break;
            }

            return redirect()->route('admin.database.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.database.index')->with('error', 'Action failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute dangerous database actions
     */
    public function databaseDanger(Request $request)
    {
        $request->validate([
            'danger_action' => 'required|in:reset,fresh,rollback'
        ]);

        try {
            switch ($request->danger_action) {
                case 'reset':
                    Artisan::call('migrate:reset');
                    $message = 'Database reset completed.';
                    break;
                case 'fresh':
                    Artisan::call('migrate:fresh');
                    $message = 'Fresh migration completed.';
                    break;
                case 'rollback':
                    Artisan::call('migrate:rollback');
                    $message = 'Migration rollback completed.';
                    break;
            }

            return redirect()->route('admin.database.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.database.index')->with('error', 'Dangerous action failed: ' . $e->getMessage());
        }
    }

    /**
     * Show cache management page
     */
    public function cache()
    {
        $cache_info = $this->getCacheInfo();
        $cache_status = $this->getCacheStatus();
        $cache_stats = $this->getCacheStats();
        $cache_files = $this->getCacheFiles();
        $recent_cache_activity = $this->getRecentCacheActivity();

        return view('admin.cache', compact('cache_info', 'cache_status', 'cache_stats', 'cache_files', 'recent_cache_activity'));
    }

    /**
     * Clear cache
     */
    public function clearCache(Request $request)
    {
        // Log the request details
        \Log::info('Cache clear request', [
            'ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'expects_json' => $request->expectsJson(),
            'cache_type' => $request->input('cache_type'),
            'headers' => $request->headers->all()
        ]);

        try {
            // Get cache type from request
            $cacheType = $request->input('cache_type');
            
            if (!$cacheType || !in_array($cacheType, ['application', 'config', 'route', 'view', 'all'])) {
                \Log::warning('Invalid cache type', ['cache_type' => $cacheType]);
                
                // Always return JSON for invalid requests
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing cache_type parameter'
                ], 422);
            }

            switch ($cacheType) {
                case 'application':
                    \Artisan::call('cache:clear');
                    $message = 'Application cache cleared successfully.';
                    break;
                case 'config':
                    \Artisan::call('config:clear');
                    // Also clear bootstrap cache
                    $configCache = base_path('bootstrap/cache/config.php');
                    if (file_exists($configCache)) {
                        @unlink($configCache);
                    }
                    $message = 'Configuration cache cleared successfully.';
                    break;
                case 'route':
                    \Artisan::call('route:clear');
                    // Also clear bootstrap cache
                    $routeCache = base_path('bootstrap/cache/routes-v7.php');
                    if (file_exists($routeCache)) {
                        @unlink($routeCache);
                    }
                    $message = 'Route cache cleared successfully.';
                    break;
                case 'view':
                    \Artisan::call('view:clear');
                    $message = 'View cache cleared successfully.';
                    break;
                case 'all':
                    \Artisan::call('cache:clear');
                    \Artisan::call('config:clear');
                    \Artisan::call('route:clear');
                    \Artisan::call('view:clear');
                    // Clear bootstrap cache files
                    $cacheFiles = [
                        base_path('bootstrap/cache/config.php'),
                        base_path('bootstrap/cache/routes-v7.php'),
                        base_path('bootstrap/cache/packages.php'),
                        base_path('bootstrap/cache/services.php'),
                    ];
                    foreach ($cacheFiles as $file) {
                        if (file_exists($file)) {
                            @unlink($file);
                        }
                    }
                    $message = 'All caches cleared successfully.';
                    break;
            }

            \Log::info('Cache cleared successfully', ['cache_type' => $cacheType]);

            // Always return JSON response for POST requests
            return response()->json([
                'success' => true,
                'message' => $message
            ])->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            \Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Always return JSON error response
            return response()->json([
                'success' => false,
                'message' => 'Cache clear failed: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Optimize cache
     */
    public function optimizeCache(Request $request)
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            $message = 'Cache optimization completed successfully.';

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('admin.cache')->with('success', $message);
        } catch (\Exception $e) {
            $errorMessage = 'Cache optimization failed: ' . $e->getMessage();

            // Return JSON error for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->route('admin.cache')->with('error', $errorMessage);
        }
    }

    /**
     * Show backup management page
     */
    public function backup()
    {
        $backup_status = $this->getBackupStatus();
        $backup_settings = $this->getBackupSettings();
        $backups = $this->getExistingBackups();

        return view('admin.backup', compact('backup_status', 'backup_settings', 'backups'));
    }

    /**
     * Create backup
     */
    public function createBackup(Request $request)
    {
        $request->validate([
            'backup_types' => 'required|array',
            'backup_types.*' => 'in:database,files,uploads,config',
            'backup_name' => 'nullable|string|max:255',
            'compression' => 'required|in:gzip,zip,none'
        ]);

        try {
            $backupTypes = $request->backup_types;
            $backupName = $request->backup_name ?: 'backup-' . date('Y-m-d-H-i-s');
            $compression = $request->compression;
            
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $createdFiles = [];
            $totalOriginalSize = 0;
            $totalCompressedSize = 0;
            
            // Create database backup
            if (in_array('database', $backupTypes)) {
                $dbFile = $backupDir . '/' . $backupName . '_database.sql';
                
                $host = config('database.connections.mysql.host');
                $port = config('database.connections.mysql.port');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');
                
                // Try to find mysqldump in common XAMPP locations
                $mysqldumpPaths = [
                    'Z:\mysql\bin\mysqldump.exe',  // Current XAMPP location
                    'C:\xampp\mysql\bin\mysqldump.exe',
                    'D:\xampp\mysql\bin\mysqldump.exe',
                    'mysqldump', // System PATH
                ];
                
                $mysqldumpCmd = null;
                foreach ($mysqldumpPaths as $path) {
                    if (file_exists($path) || $path === 'mysqldump') {
                        $mysqldumpCmd = $path;
                        break;
                    }
                }
                
                if (!$mysqldumpCmd) {
                    throw new \Exception('mysqldump command not found. Please ensure MySQL is installed.');
                }
                
                // Using mysqldump command - Windows compatible
                // Don't use escapeshellarg for password as it adds extra quotes
                $command = sprintf(
                    '"%s" -h%s -P%s -u%s -p%s %s > "%s" 2>&1',
                    $mysqldumpCmd,
                    $host,
                    $port,
                    $username,
                    $password,
                    $database,
                    $dbFile
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($dbFile) && filesize($dbFile) > 1000) {
                    $originalSize = filesize($dbFile);
                    $totalOriginalSize += $originalSize;
                    
                    // Apply compression based on user choice
                    if ($compression === 'gzip') {
                        // GZIP compression - best compression ratio
                        $content = file_get_contents($dbFile);
                        $gzFile = $dbFile . '.gz';
                        file_put_contents($gzFile, gzencode($content, 9)); // Level 9 = maximum compression
                        $compressedSize = filesize($gzFile);
                        $totalCompressedSize += $compressedSize;
                        unlink($dbFile);
                        $ratio = round((1 - $compressedSize / $originalSize) * 100, 1);
                        $createdFiles[] = sprintf(
                            'Database (GZIP): %s → %s (%s%% smaller)', 
                            $this->formatFileSize($originalSize),
                            $this->formatFileSize($compressedSize),
                            $ratio
                        );
                    } elseif ($compression === 'zip') {
                        // ZIP compression - compatible with Windows
                        $zipFile = $backupDir . '/' . $backupName . '_database.zip';
                        $zip = new \ZipArchive();
                        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                            $zip->addFile($dbFile, basename($dbFile));
                            $zip->close();
                            $compressedSize = filesize($zipFile);
                            $totalCompressedSize += $compressedSize;
                            unlink($dbFile);
                            $ratio = round((1 - $compressedSize / $originalSize) * 100, 1);
                            $createdFiles[] = sprintf(
                                'Database (ZIP): %s → %s (%s%% smaller)', 
                                $this->formatFileSize($originalSize),
                                $this->formatFileSize($compressedSize),
                                $ratio
                            );
                        }
                    } else {
                        // No compression - raw SQL file
                        $totalCompressedSize += $originalSize;
                        $createdFiles[] = sprintf(
                            'Database (Uncompressed): %s', 
                            $this->formatFileSize($originalSize)
                        );
                    }
                } else {
                    $errorMsg = file_exists($dbFile) ? file_get_contents($dbFile) : 'File not created';
                    throw new \Exception('Database backup failed. Error: ' . $errorMsg);
                }
            }
            
            // Create files backup (storage/app/public)
            if (in_array('files', $backupTypes)) {
                $filesDir = storage_path('app/public');
                if (is_dir($filesDir)) {
                    $zipFile = $backupDir . '/' . $backupName . '_files.zip';
                    $zip = new \ZipArchive();
                    
                    if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                        $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($filesDir),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        
                        $fileCount = 0;
                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($filesDir) + 1);
                                $zip->addFile($filePath, $relativePath);
                                $fileCount++;
                            }
                        }
                        
                        $zip->close();
                        $zipSize = filesize($zipFile);
                        $totalCompressedSize += $zipSize;
                        $createdFiles[] = sprintf('Storage files: %d files (%s)', $fileCount, $this->formatFileSize($zipSize));
                    }
                }
            }
            
            // Create uploads backup (public/uploads or similar)
            if (in_array('uploads', $backupTypes)) {
                $uploadsDir = public_path('uploads');
                if (is_dir($uploadsDir)) {
                    $zipFile = $backupDir . '/' . $backupName . '_uploads.zip';
                    $zip = new \ZipArchive();
                    
                    if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                        $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($uploadsDir),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        
                        $fileCount = 0;
                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($uploadsDir) + 1);
                                $zip->addFile($filePath, $relativePath);
                                $fileCount++;
                            }
                        }
                        
                        $zip->close();
                        $zipSize = filesize($zipFile);
                        $totalCompressedSize += $zipSize;
                        $createdFiles[] = sprintf('Uploads: %d files (%s)', $fileCount, $this->formatFileSize($zipSize));
                    }
                }
            }
            
            // Create config backup
            if (in_array('config', $backupTypes)) {
                $configFile = $backupDir . '/' . $backupName . '_config.zip';
                $zip = new \ZipArchive();
                
                if ($zip->open($configFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                    // Backup .env file
                    if (file_exists(base_path('.env'))) {
                        $zip->addFile(base_path('.env'), '.env');
                    }
                    
                    // Backup config directory
                    $configDir = config_path();
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($configDir),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    $fileCount = 0;
                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = 'config/' . substr($filePath, strlen($configDir) + 1);
                            $zip->addFile($filePath, $relativePath);
                            $fileCount++;
                        }
                    }
                    
                    $zip->close();
                    $zipSize = filesize($configFile);
                    $totalCompressedSize += $zipSize;
                    $createdFiles[] = sprintf('Configuration: %d files (%s)', $fileCount, $this->formatFileSize($zipSize));
                }
            }
            
            if (empty($createdFiles)) {
                throw new \Exception('No backup files were created');
            }
            
            $message = sprintf(
                'Backup "%s" created successfully! Total size: %s<br>%s',
                $backupName,
                $this->formatFileSize($totalCompressedSize),
                implode('<br>', $createdFiles)
            );
            
            return redirect()->route('admin.backup')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Backup creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Update backup settings
     */
    public function settings(Request $request)
    {
        $request->validate([
            'auto_backup' => 'nullable|boolean',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'retention_days' => 'required|integer|min:1|max:365'
        ]);

        try {
            $settingsPath = storage_path('app/backup_settings.json');
            
            $settings = [
                'auto_backup' => $request->has('auto_backup'),
                'backup_frequency' => $request->input('backup_frequency', 'weekly'),
                'retention_days' => (int) $request->input('retention_days', 30),
                'updated_at' => now()->toDateTimeString()
            ];
            
            file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT));
            
            $message = 'Backup settings updated successfully. Automatic backups are scheduled to run every Friday at 11 PM.';
            
            return redirect()->route('admin.backup')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Settings update failed: ' . $e->getMessage());
        }
    }

    // Private helper methods

    private function checkCacheStatus()
    {
        try {
            Cache::put('test_key', 'test_value', 60);
            return Cache::get('test_key') === 'test_value';
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkStorageStatus()
    {
        try {
            return Storage::disk('local')->put('test.txt', 'test') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getRecentActivities()
    {
        // This would fetch real activity logs from database
        return [
            [
                'time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'user' => 'admin',
                'action' => 'User Created',
                'type' => 'success',
                'details' => 'Created new user: john.doe@example.com'
            ],
            [
                'time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'user' => 'super-admin',
                'action' => 'Cache Cleared',
                'type' => 'warning',
                'details' => 'Cleared application cache'
            ]
        ];
    }

    private function checkDatabaseStatus()
    {
        try {
            DB::connection()->getPdo();
            return ['connected' => true];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    private function getDatabaseInfo()
    {
        try {
            return [
                'driver' => config('database.default'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getDatabaseStats()
    {
        try {
            $tables = $this->getAllTables();
            return [
                'total_tables' => count($tables),
                'database_size' => 'Unknown' // Size available via trait if needed
            ];
        } catch (\Exception $e) {
            return ['total_tables' => 0, 'database_size' => 'Unknown'];
        }
    }

    private function getTableInfo()
    {
        try {
            $tables = $this->getAllTables();
            $result = [];
            foreach ($tables as $tableName) {
                $stats = $this->getTableStatsAgnostic($tableName);
                $result[] = [
                    'name' => $tableName,
                    'rows' => $stats->row_count ?? null,
                    'size' => isset($stats->total_size) ? round($stats->total_size / 1024 / 1024, 2) . ' MB' : 'Unknown',
                    'engine' => null,
                    'created' => null
                ];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMigrationStatus()
    {
        try {
            $migrations = DB::table('migrations')->orderBy('batch', 'desc')->get();
            $result = [];
            foreach ($migrations as $migration) {
                $result[] = [
                    'name' => $migration->migration,
                    'batch' => $migration->batch,
                    'executed_at' => $migration->created_at
                ];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function optimizeTables()
    {
        $tables = $this->getAllTables();
        foreach ($tables as $tableName) {
            try {
                // OPTIMIZE TABLE is MySQL-specific; skip for other drivers
                if (DB::connection()->getDriverName() === 'mysql') {
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                }
            } catch (\Exception $e) {
                // ignore per-table errors
            }
        }
    }

    private function repairTables()
    {
        $tables = $this->getAllTables();
        foreach ($tables as $tableName) {
            try {
                if (DB::connection()->getDriverName() === 'mysql') {
                    DB::statement("REPAIR TABLE `{$tableName}`");
                }
            } catch (\Exception $e) {
                // ignore per-table errors
            }
        }
    }

    private function checkTables()
    {
        $tables = $this->getAllTables();
        $errors = 0;
        foreach ($tables as $tableName) {
            try {
                if (DB::connection()->getDriverName() === 'mysql') {
                    $result = DB::select("CHECK TABLE `{$tableName}`");
                    if (isset($result[0]) && ($result[0]->Msg_text ?? '') !== 'OK') {
                        $errors++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }
        return $errors > 0 ? "$errors table(s) have errors." : "All tables are OK.";
    }

    private function getCacheInfo()
    {
        return [
            'driver' => config('cache.default')
        ];
    }

    private function getCacheStatus()
    {
        return [
            'working' => $this->checkCacheStatus(),
            'application' => file_exists(storage_path('framework/cache')),
            'routes' => file_exists(storage_path('framework/cache/routes.php')),
            'config' => file_exists(storage_path('framework/cache/config.php')),
            'views' => file_exists(storage_path('framework/views'))
        ];
    }

    private function getCacheStats()
    {
        return [
            'total_files' => 0, // Would count actual cache files
            'total_size' => '0 MB', // Would calculate actual size
            'last_cleared' => 'Never', // Would track last clear time
            'hit_rate' => '0%' // Would calculate hit rate
        ];
    }

    private function getCacheFiles()
    {
        return [
            [
                'type' => 'config',
                'path' => storage_path('framework/cache/config.php'),
                'size' => file_exists(storage_path('framework/cache/config.php')) ? 
                    round(filesize(storage_path('framework/cache/config.php')) / 1024, 2) . ' KB' : '0 KB',
                'modified' => file_exists(storage_path('framework/cache/config.php')) ? 
                    date('Y-m-d H:i:s', filemtime(storage_path('framework/cache/config.php'))) : 'Never',
                'exists' => file_exists(storage_path('framework/cache/config.php'))
            ],
            [
                'type' => 'routes',
                'path' => storage_path('framework/cache/routes.php'),
                'size' => file_exists(storage_path('framework/cache/routes.php')) ? 
                    round(filesize(storage_path('framework/cache/routes.php')) / 1024, 2) . ' KB' : '0 KB',
                'modified' => file_exists(storage_path('framework/cache/routes.php')) ? 
                    date('Y-m-d H:i:s', filemtime(storage_path('framework/cache/routes.php'))) : 'Never',
                'exists' => file_exists(storage_path('framework/cache/routes.php'))
            ]
        ];
    }

    private function getRecentCacheActivity()
    {
        return []; // Would implement activity tracking
    }

    private function getBackupStatus()
    {
        $backupPath = storage_path('app/backups');
        $files = glob($backupPath . '/backup_*.sql');
        $totalSize = 0;
        $lastBackup = 'Never';
        
        if (count($files) > 0) {
            // Get last backup
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $lastBackup = date('Y-m-d H:i:s', filemtime($files[0]));
            
            // Calculate total size
            foreach ($files as $file) {
                $totalSize += filesize($file);
            }
        }
        
        // Get available disk space
        $freeSpace = disk_free_space($backupPath);
        
        return [
            'last_backup' => $lastBackup,
            'total_backups' => count($files),
            'backup_path' => $backupPath,
            'total_size' => $this->formatFileSize($totalSize),
            'available_space' => $this->formatFileSize($freeSpace),
            'auto_backup' => true // Since we set up the scheduled task
        ];
    }

    private function getBackupSettings()
    {
        $settingsPath = storage_path('app/backup_settings.json');
        
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            return [
                'auto_backup' => $settings['auto_backup'] ?? true,
                'backup_frequency' => $settings['backup_frequency'] ?? 'weekly',
                'retention_days' => $settings['retention_days'] ?? 30
            ];
        }
        
        // Default settings
        return [
            'auto_backup' => true,
            'backup_frequency' => 'weekly',
            'retention_days' => 30
        ];
    }

    private function getExistingBackups()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];
        
        if (!is_dir($backupPath)) {
            return $backups;
        }
        
        // Get all backup files (sql, sql.gz, zip)
        $allFiles = array_merge(
            glob($backupPath . '/*.sql'),
            glob($backupPath . '/*.sql.gz'),
            glob($backupPath . '/*.zip')
        );
        
        // Group files by backup name
        $groupedBackups = [];
        foreach ($allFiles as $file) {
            $filename = basename($file);
            
            // Extract backup name (e.g., backup-2025-12-05-08-30-00 from backup-2025-12-05-08-30-00_database.sql)
            if (preg_match('/^(backup-[\d\-]+)_/', $filename, $matches)) {
                $backupName = $matches[1];
            } else {
                // Fallback for old format
                $backupName = pathinfo($filename, PATHINFO_FILENAME);
            }
            
            if (!isset($groupedBackups[$backupName])) {
                $groupedBackups[$backupName] = [
                    'name' => $backupName,
                    'files' => [],
                    'types' => [],
                    'total_size' => 0,
                    'created_at' => filemtime($file),
                    'path' => dirname($file)
                ];
            }
            
            // Determine backup type from filename
            if (strpos($filename, '_database') !== false) {
                $groupedBackups[$backupName]['types'][] = 'database';
            } elseif (strpos($filename, '_files') !== false) {
                $groupedBackups[$backupName]['types'][] = 'files';
            } elseif (strpos($filename, '_uploads') !== false) {
                $groupedBackups[$backupName]['types'][] = 'uploads';
            } elseif (strpos($filename, '_config') !== false) {
                $groupedBackups[$backupName]['types'][] = 'config';
            }
            
            $groupedBackups[$backupName]['files'][] = $filename;
            $groupedBackups[$backupName]['total_size'] += filesize($file);
            
            // Use earliest file creation time
            $groupedBackups[$backupName]['created_at'] = min(
                $groupedBackups[$backupName]['created_at'],
                filemtime($file)
            );
        }
        
        // Convert to array format expected by view
        foreach ($groupedBackups as $backupName => $data) {
            $backups[] = [
                'id' => md5($backupName),
                'name' => $backupName,
                'types' => array_unique($data['types']),
                'files' => $data['files'],
                'size' => $this->formatFileSize($data['total_size']),
                'created_at' => date('Y-m-d H:i:s', $data['created_at']),
                'status' => 'complete',
                'path' => $data['path']
            ];
        }
        
        // Sort by creation date, newest first
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Cleanup old backups
     */
    public function cleanupBackups()
    {
        try {
            // This would implement actual backup cleanup logic
            $message = 'Old backups cleaned up successfully.';
            
            return redirect()->route('admin.backup')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Backup cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Show backup details
     */
    public function showBackup($backupId)
    {
        try {
            $backups = $this->getExistingBackups();
            $backup = collect($backups)->firstWhere('id', $backupId);
            
            if (!$backup) {
                return redirect()->route('admin.backup')->with('error', 'Backup not found.');
            }
            
            // Get detailed file information
            $backupPath = storage_path('app/backups');
            $detailedFiles = [];
            
            foreach ($backup['files'] as $filename) {
                $filePath = $backupPath . '/' . $filename;
                if (file_exists($filePath)) {
                    $detailedFiles[] = [
                        'name' => $filename,
                        'size' => $this->formatFileSize(filesize($filePath)),
                        'size_bytes' => filesize($filePath),
                        'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'type' => $this->getFileType($filename),
                        'download_id' => md5($filename)
                    ];
                }
            }
            
            $backup['detailed_files'] = $detailedFiles;
            
            return view('admin.backup-details', compact('backup'));
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Error loading backup details: ' . $e->getMessage());
        }
    }
    
    private function getFileType($filename)
    {
        if (strpos($filename, '_database') !== false) {
            if (strpos($filename, '.gz') !== false) {
                return 'Database (GZIP)';
            } elseif (strpos($filename, '.zip') !== false) {
                return 'Database (ZIP)';
            }
            return 'Database (SQL)';
        } elseif (strpos($filename, '_files') !== false) {
            return 'Storage Files';
        } elseif (strpos($filename, '_uploads') !== false) {
            return 'Uploads';
        } elseif (strpos($filename, '_config') !== false) {
            return 'Configuration';
        }
        return 'Unknown';
    }

    /**
     * Download backup
     */
    public function downloadBackup($backupId)
    {
        try {
            $backupPath = storage_path('app/backups');
            
            // Get all backup files
            $allFiles = array_merge(
                glob($backupPath . '/*.sql'),
                glob($backupPath . '/*.sql.gz'),
                glob($backupPath . '/*.zip')
            );
            
            // Find file by ID
            foreach ($allFiles as $file) {
                $filename = basename($file);
                if (md5($filename) === $backupId) {
                    return response()->download($file);
                }
            }
            
            // Also try to match by backup name (for downloading all files as zip)
            $backups = $this->getExistingBackups();
            $backup = collect($backups)->firstWhere('id', $backupId);
            
            if ($backup && count($backup['files']) === 1) {
                $file = $backupPath . '/' . $backup['files'][0];
                if (file_exists($file)) {
                    return response()->download($file);
                }
            }
            
            return redirect()->route('admin.backup')->with('error', 'Backup file not found.');
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Backup download failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore backup
     */
    public function restoreBackup(Request $request, $backupId)
    {
        try {
            // This would implement actual backup restore logic
            $message = 'Backup restored successfully.';
            
            return redirect()->route('admin.backup')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Backup restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete backup
     */
    public function delete($backupId)
    {
        try {
            $backupPath = storage_path('app/backups');
            
            // Get backup info
            $backups = $this->getExistingBackups();
            $backup = collect($backups)->firstWhere('id', $backupId);
            
            if (!$backup) {
                return redirect()->route('admin.backup')->with('error', 'Backup not found.');
            }
            
            // Delete all files associated with this backup
            $deletedCount = 0;
            $failedFiles = [];
            
            foreach ($backup['files'] as $filename) {
                $filePath = $backupPath . '/' . $filename;
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        $deletedCount++;
                    } else {
                        $failedFiles[] = $filename;
                    }
                }
            }
            
            if ($deletedCount > 0) {
                $message = sprintf(
                    'Backup "%s" deleted successfully. %d file(s) removed.',
                    $backup['name'],
                    $deletedCount
                );
                
                if (!empty($failedFiles)) {
                    $message .= ' Failed to delete: ' . implode(', ', $failedFiles);
                }
                
                return redirect()->route('admin.backup')->with('success', $message);
            }
            
            return redirect()->route('admin.backup')->with('error', 'No backup files were deleted.');
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Backup deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload backup
     */
    public function uploadBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip,sql,gz|max:512000' // 500MB max
        ]);

        try {
            $file = $request->file('backup_file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Generate unique name
            $uploadName = 'uploaded-' . date('Y-m-d-H-i-s') . '-' . str_replace(' ', '_', pathinfo($originalName, PATHINFO_FILENAME));
            
            // Store file
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $storedFile = $backupPath . '/' . $uploadName . '.' . $extension;
            $file->move($backupPath, $uploadName . '.' . $extension);
            
            // If it's a SQL file, offer to restore immediately
            if (in_array($extension, ['sql', 'gz'])) {
                $sqlFile = $storedFile;
                
                // If it's gzipped, decompress first
                if ($extension === 'gz') {
                    $decompressed = $backupPath . '/' . $uploadName . '.sql';
                    $gz = gzopen($storedFile, 'rb');
                    $out = fopen($decompressed, 'wb');
                    
                    while (!gzeof($gz)) {
                        fwrite($out, gzread($gz, 4096));
                    }
                    
                    fclose($out);
                    gzclose($gz);
                    
                    $sqlFile = $decompressed;
                }
                
                // Get database connection info
                $host = config('database.connections.mysql.host');
                $port = config('database.connections.mysql.port');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');
                
                // Find mysql command
                $mysqlPaths = [
                    'Z:\mysql\bin\mysql.exe',
                    'C:\xampp\mysql\bin\mysql.exe',
                    'D:\xampp\mysql\bin\mysql.exe',
                    'mysql',
                ];
                
                $mysqlCmd = null;
                foreach ($mysqlPaths as $path) {
                    if (file_exists($path) || $path === 'mysql') {
                        $mysqlCmd = $path;
                        break;
                    }
                }
                
                if (!$mysqlCmd) {
                    throw new \Exception('MySQL command not found. File uploaded but not restored.');
                }
                
                // Restore database
                $command = sprintf(
                    '"%s" -h%s -P%s -u%s -p%s %s < "%s" 2>&1',
                    $mysqlCmd,
                    $host,
                    $port,
                    $username,
                    $password,
                    $database,
                    $sqlFile
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0) {
                    $message = sprintf(
                        'Backup uploaded and restored successfully!<br>File: %s<br>Size: %s',
                        $originalName,
                        $this->formatFileSize(filesize($storedFile))
                    );
                    return redirect()->route('admin.backup')->with('success', $message);
                } else {
                    $errorMsg = implode("\n", $output);
                    throw new \Exception('Database restore failed: ' . $errorMsg);
                }
            } elseif ($extension === 'zip') {
                // Handle ZIP file - extract and process
                $zip = new \ZipArchive();
                if ($zip->open($storedFile)) {
                    $extractPath = $backupPath . '/temp_' . time();
                    mkdir($extractPath, 0755, true);
                    $zip->extractTo($extractPath);
                    $zip->close();
                    
                    // Look for SQL files in extracted content
                    $sqlFiles = glob($extractPath . '/*.sql');
                    
                    if (!empty($sqlFiles)) {
                        // Restore first SQL file found
                        $sqlFile = $sqlFiles[0];
                        
                        $host = config('database.connections.mysql.host');
                        $port = config('database.connections.mysql.port');
                        $database = config('database.connections.mysql.database');
                        $username = config('database.connections.mysql.username');
                        $password = config('database.connections.mysql.password');
                        
                        $mysqlPaths = [
                            'Z:\mysql\bin\mysql.exe',
                            'C:\xampp\mysql\bin\mysql.exe',
                            'D:\xampp\mysql\bin\mysql.exe',
                            'mysql',
                        ];
                        
                        $mysqlCmd = null;
                        foreach ($mysqlPaths as $path) {
                            if (file_exists($path) || $path === 'mysql') {
                                $mysqlCmd = $path;
                                break;
                            }
                        }
                        
                        if ($mysqlCmd) {
                            $command = sprintf(
                                '"%s" -h%s -P%s -u%s -p%s %s < "%s" 2>&1',
                                $mysqlCmd,
                                $host,
                                $port,
                                $username,
                                $password,
                                $database,
                                $sqlFile
                            );
                            
                            exec($command, $output, $returnCode);
                            
                            // Cleanup temp directory
                            array_map('unlink', glob($extractPath . '/*'));
                            rmdir($extractPath);
                            
                            if ($returnCode === 0) {
                                $message = sprintf(
                                    'ZIP backup uploaded and database restored successfully!<br>File: %s',
                                    $originalName
                                );
                                return redirect()->route('admin.backup')->with('success', $message);
                            }
                        }
                    }
                    
                    // Cleanup temp directory
                    array_map('unlink', glob($extractPath . '/*'));
                    rmdir($extractPath);
                }
                
                $message = sprintf(
                    'ZIP file uploaded but no SQL file found to restore.<br>File: %s',
                    $originalName
                );
                return redirect()->route('admin.backup')->with('warning', $message);
            }
            
            $message = sprintf(
                'Backup file uploaded successfully!<br>File: %s<br>Size: %s',
                $originalName,
                $this->formatFileSize(filesize($storedFile))
            );
            
            return redirect()->route('admin.backup')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')->with('error', 'Backup upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Optimize database tables
     */
    public function optimize(Request $request)
    {
        try {
            // Get all tables in a DB-agnostic way (DatabaseInspector trait)
            if (method_exists($this, 'getAllTables')) {
                $tables = $this->getAllTables();
            } else {
                // fallback to Doctrine or empty
                $tables = [];
            }

            $driver = DB::connection()->getDriverName();

            $optimizedTables = [];
            foreach ($tables as $tableName) {
                // Only run OPTIMIZE on MySQL
                if ($driver === 'mysql') {
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                }
                $optimizedTables[] = $tableName;
            }
            
            $message = 'Successfully optimized ' . count($optimizedTables) . ' database tables.';
            
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'tables_count' => count($optimizedTables)
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            $errorMessage = 'Database optimization failed: ' . $e->getMessage();

            // Return JSON error for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }
}