<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep-days=30 : Number of days to keep old backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Starting database backup...');
            
            // Get database configuration
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPassword = config('database.connections.mysql.password');
            
            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
                $this->info('Created backup directory: ' . $backupPath);
            }
            
            // Generate backup filename with timestamp
            $filename = 'backup_' . $dbName . '_' . Carbon::now()->format('Y-m-d_His') . '.sql';
            $fullPath = $backupPath . '/' . $filename;
            
            // Determine the path to mysqldump
            $mysqlDumpPath = $this->getMysqlDumpPath();
            
            if (!$mysqlDumpPath) {
                $this->error('mysqldump not found. Please ensure MySQL is properly installed.');
                return 1;
            }
            
            // Build mysqldump command
            $command = sprintf(
                '"%s" --user=%s --host=%s --port=%s %s > "%s"',
                $mysqlDumpPath,
                escapeshellarg($dbUser),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbName),
                $fullPath
            );
            
            // Add password if set
            if (!empty($dbPassword)) {
                $command = sprintf(
                    '"%s" --user=%s --password=%s --host=%s --port=%s %s > "%s"',
                    $mysqlDumpPath,
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPassword),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbName),
                    $fullPath
                );
            }
            
            // Execute backup
            $this->info('Executing backup command...');
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($fullPath)) {
                $fileSize = filesize($fullPath);
                $this->info('Backup completed successfully!');
                $this->info('File: ' . $filename);
                $this->info('Size: ' . $this->formatBytes($fileSize));
                $this->info('Location: ' . $fullPath);
                
                // Cleanup old backups
                $this->cleanupOldBackups($backupPath, $this->option('keep-days'));
                
                // Log the backup
                \Log::info('Database backup created', [
                    'filename' => $filename,
                    'size' => $fileSize,
                    'path' => $fullPath
                ]);
                
                return 0;
            } else {
                $this->error('Backup failed!');
                $this->error('Command: ' . $command);
                if (!empty($output)) {
                    $this->error('Output: ' . implode("\n", $output));
                }
                
                \Log::error('Database backup failed', [
                    'command' => $command,
                    'output' => $output,
                    'return_code' => $returnCode
                ]);
                
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            \Log::error('Database backup exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Find the mysqldump executable path
     *
     * @return string|null
     */
    protected function getMysqlDumpPath()
    {
        // Common paths for mysqldump
        $possiblePaths = [
            'Z:\mysql\bin\mysqldump.exe',  // XAMPP on Z drive
            base_path('../mysql/bin/mysqldump.exe'),
            'C:\xampp\mysql\bin\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump',  // System PATH
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Try to find using 'where' command on Windows
        exec('where mysqldump', $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            return $output[0];
        }
        
        // Try to find using 'which' command on Unix
        exec('which mysqldump', $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            return $output[0];
        }
        
        return null;
    }
    
    /**
     * Clean up old backup files
     *
     * @param string $backupPath
     * @param int $keepDays
     * @return void
     */
    protected function cleanupOldBackups($backupPath, $keepDays)
    {
        $this->info("Cleaning up backups older than {$keepDays} days...");
        
        $files = glob($backupPath . '/backup_*.sql');
        $deletedCount = 0;
        $cutoffDate = Carbon::now()->subDays($keepDays);
        
        foreach ($files as $file) {
            $fileTime = Carbon::createFromTimestamp(filemtime($file));
            
            if ($fileTime->lt($cutoffDate)) {
                if (unlink($file)) {
                    $deletedCount++;
                    $this->info('Deleted old backup: ' . basename($file));
                }
            }
        }
        
        if ($deletedCount > 0) {
            $this->info("Cleaned up {$deletedCount} old backup(s).");
        } else {
            $this->info('No old backups to clean up.');
        }
    }
    
    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
