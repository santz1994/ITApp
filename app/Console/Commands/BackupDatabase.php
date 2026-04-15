<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--compress : Compress the backup file}
                            {--keep=30 : Number of days to keep backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the MySQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');
        
        $config = config('database.connections.' . config('database.default'));
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        // Create backup directory
        $backupPath = storage_path('backups/database');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_{$database}_{$timestamp}.sql";
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;
        
        // Build mysqldump command
        $mysqldumpPath = $this->findMysqldump();
        if (!$mysqldumpPath) {
            $this->error('mysqldump not found! Please install MySQL client tools.');
            return 1;
        }
        
        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --events %s > "%s" 2>&1',
            $mysqldumpPath,
            $host,
            $port,
            $username,
            $password,
            $database,
            $filepath
        );
        
        // Execute backup
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $size = $this->formatBytes(filesize($filepath));
            $this->info("✓ Backup created: {$filename} ({$size})");
            
            // Compress if requested
            if ($this->option('compress')) {
                $this->info('Compressing backup...');
                $this->compressFile($filepath);
                $compressedSize = $this->formatBytes(filesize($filepath . '.gz'));
                $this->info("✓ Compressed to: {$filename}.gz ({$compressedSize})");
            }
            
            // Clean old backups
            $keepDays = (int) $this->option('keep');
            $this->cleanOldBackups($backupPath, $keepDays);
            
            // Log backup
            $this->logBackup($filename, $filepath);
            
            $this->info('✓ Database backup completed successfully!');
            return 0;
        } else {
            $this->error('✗ Backup failed!');
            $this->error(implode("\n", $output));
            return 1;
        }
    }
    
    /**
     * Find mysqldump executable
     */
    protected function findMysqldump(): ?string
    {
        $possiblePaths = [
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\mysql\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Try which/where command
        $command = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump' : 'which mysqldump';
        exec($command, $output, $returnCode);
        
        return $returnCode === 0 ? trim($output[0] ?? '') : null;
    }
    
    /**
     * Compress file using gzip
     */
    protected function compressFile(string $filepath): void
    {
        $gzFilepath = $filepath . '.gz';
        
        $fp = fopen($filepath, 'rb');
        $gz = gzopen($gzFilepath, 'wb9');
        
        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 1024 * 1024)); // 1MB chunks
        }
        
        fclose($fp);
        gzclose($gz);
        
        // Remove original
        unlink($filepath);
    }
    
    /**
     * Clean old backups
     */
    protected function cleanOldBackups(string $path, int $keepDays): void
    {
        $cutoffTime = Carbon::now()->subDays($keepDays)->timestamp;
        $files = glob($path . '/backup_*.sql*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->info("✓ Cleaned {$deleted} old backup(s)");
        }
    }
    
    /**
     * Log backup to database
     */
    protected function logBackup(string $filename, string $filepath): void
    {
        try {
            DB::table('backup_logs')->insert([
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize(file_exists($filepath) ? $filepath : $filepath . '.gz'),
                'status' => 'success',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Table might not exist, skip logging
        }
    }
    
    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
