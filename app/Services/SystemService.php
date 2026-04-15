<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SystemService
{
    /**
     * Clear application caches by type
     */
    public function clearCache(string $type = 'all'): array
    {
        $cleared = [];
        
        switch ($type) {
            case 'all':
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                $cleared = ['cache', 'config', 'view', 'route'];
                break;
                
            case 'cache':
                Artisan::call('cache:clear');
                $cleared = ['cache'];
                break;
                
            case 'config':
                Artisan::call('config:clear');
                $cleared = ['config'];
                break;
                
            case 'view':
                Artisan::call('view:clear');
                $cleared = ['view'];
                break;
                
            case 'route':
                Artisan::call('route:clear');
                $cleared = ['route'];
                break;
        }
        
        return [
            'success' => true,
            'cleared' => $cleared,
            'message' => 'Cache cleared successfully'
        ];
    }
    
    /**
     * Clear temporary files
     */
    public function clearTempFiles(): array
    {
        $count = 0;
        $size = 0;
        
        $tempPath = storage_path('temp');
        
        if (File::exists($tempPath)) {
            $files = File::files($tempPath);
            foreach ($files as $file) {
                $size += $file->getSize();
                File::delete($file->getPathname());
                $count++;
            }
        }
        
        return [
            'success' => true,
            'files_deleted' => $count,
            'space_freed' => $this->formatBytes($size)
        ];
    }
    
    /**
     * Clear old uploads (older than specified days)
     */
    public function clearOldUploads(int $olderThanDays = 90): array
    {
        $uploadsPath = public_path('uploads');
        $deleted = 0;
        $cutoffTimestamp = time() - ($olderThanDays * 24 * 60 * 60);
        
        if (!File::exists($uploadsPath)) {
            return [
                'success' => true,
                'files_deleted' => 0,
                'message' => 'Uploads directory does not exist'
            ];
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoffTimestamp) {
                File::delete($file->getRealPath());
                $deleted++;
            }
        }
        
        return [
            'success' => true,
            'files_deleted' => $deleted,
            'message' => "Deleted {$deleted} old upload files successfully!"
        ];
    }
    
    /**
     * Get queue status
     */
    public function getQueueStatus(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            
            return [
                'success' => true,
                'pending' => $pending,
                'failed' => $failed,
                'workers' => 0, // This would require checking running processes
                'message' => 'Queue status retrieved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting queue status: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheInfo(): array
    {
        $cacheDriver = config('cache.default');
        $cacheSize = 0;
        
        // Estimate cache size based on driver
        if ($cacheDriver === 'file') {
            $cachePath = storage_path('framework/cache/data');
            if (File::exists($cachePath)) {
                $cacheSize = $this->getDirSize($cachePath);
            }
        }
        
        return [
            'driver' => $cacheDriver,
            'size' => $this->formatBytes($cacheSize),
            'size_bytes' => $cacheSize
        ];
    }
    
    /**
     * Get disk usage information
     */
    public function getDiskUsage(): array
    {
        $storageSize = 0;
        $publicSize = 0;
        
        if (File::exists(storage_path())) {
            $storageSize = $this->getDirSize(storage_path());
        }
        
        if (File::exists(public_path())) {
            $publicSize = $this->getDirSize(public_path());
        }
        
        return [
            'storage' => [
                'size' => $this->formatBytes($storageSize),
                'size_bytes' => $storageSize
            ],
            'public' => [
                'size' => $this->formatBytes($publicSize),
                'size_bytes' => $publicSize
            ],
            'total' => [
                'size' => $this->formatBytes($storageSize + $publicSize),
                'size_bytes' => $storageSize + $publicSize
            ]
        ];
    }
    
    /**
     * Get system information
     */
    public function getSystemInfo(): array
    {
        return [
            'app_version' => config('app.version', '2.0'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
        ];
    }
    
    /**
     * Get log file information
     */
    public function getLogInfo(): array
    {
        $logPath = storage_path('logs');
        $totalSize = 0;
        $fileCount = 0;
        
        if (File::exists($logPath)) {
            $files = File::files($logPath);
            $fileCount = count($files);
            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
        }
        
        return [
            'file_count' => $fileCount,
            'total_size' => $this->formatBytes($totalSize),
            'total_size_bytes' => $totalSize,
            'path' => $logPath
        ];
    }
    
    /**
     * Calculate directory size recursively
     */
    private function getDirSize(string $path): int
    {
        $size = 0;
        
        if (!File::exists($path)) {
            return 0;
        }
        
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    /**
     * Format bytes to human-readable format
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Optimize database
     */
    public function optimizeDatabase(): array
    {
        try {
            Artisan::call('optimize');
            return [
                'success' => true,
                'message' => 'Database optimized successfully!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error optimizing database: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Run database migrations
     */
    public function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            return [
                'success' => true,
                'message' => 'Migrations completed successfully!',
                'output' => $output
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error running migrations: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Restart queue workers
     */
    public function restartQueue(): array
    {
        try {
            Artisan::call('queue:restart');
            return [
                'success' => true,
                'message' => 'Queue workers will restart after completing current jobs!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error restarting queue: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Clear all queued jobs
     */
    public function clearQueue(): array
    {
        try {
            Artisan::call('queue:flush');
            return [
                'success' => true,
                'message' => 'Queue cleared successfully!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error clearing queue: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Run system health check
     */
    public function healthCheck(): array
    {
        $health = [
            'database' => true,
            'cache' => true,
            'storage' => is_writable(storage_path()),
            'logs' => is_writable(storage_path('logs')),
        ];
        
        // Test database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $health['database'] = false;
        }
        
        // Test cache
        try {
            Cache::put('health_check', true, 10);
            Cache::get('health_check');
        } catch (\Exception $e) {
            $health['cache'] = false;
        }
        
        $allHealthy = !in_array(false, $health);
        
        return [
            'success' => $allHealthy,
            'health' => $health,
            'message' => $allHealthy ? 'All systems operational!' : 'Some systems have issues'
        ];
    }
}
