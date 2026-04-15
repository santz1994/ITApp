<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class LogService
{
    /**
     * Get log entries with optional filtering
     */
    public function getLogEntries(string $filename = 'laravel.log', array $filters = []): array
    {
        $filename = basename($filename); // Sanitize
        $logFile = storage_path('logs/' . $filename);
        
        if (!File::exists($logFile)) {
            return [
                'entries' => [],
                'stats' => $this->getEmptyStats()
            ];
        }
        
        // Parse log file
        $entries = $this->parseLogFile($logFile, 100);
        
        // Apply filters
        $filteredEntries = $this->filterLogEntries(collect($entries), $filters);
        
        // Calculate statistics
        $stats = $this->getLogStatistics($filteredEntries->all());
        $stats['file_size'] = round(filesize($logFile) / 1024, 2) . ' KB';
        $stats['last_entry'] = date('Y-m-d H:i:s', filemtime($logFile));
        
        return [
            'entries' => $filteredEntries->values()->all(),
            'stats' => $stats
        ];
    }
    
    /**
     * Get available log files
     */
    public function getLogFiles(): array
    {
        $logFiles = glob(storage_path('logs/*.log'));
        $files = [];
        
        foreach ($logFiles as $file) {
            $files[] = [
                'name' => basename($file),
                'size' => round(filesize($file) / 1024, 2) . ' KB',
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        return $files;
    }
    
    /**
     * Calculate log statistics
     */
    public function getLogStatistics(array $entries): array
    {
        $stats = [
            'total' => count($entries),
            'errors' => 0,
            'warnings' => 0,
            'info' => 0
        ];
        
        foreach ($entries as $entry) {
            $level = $entry['level'] ?? 'info';
            if ($level === 'error') $stats['errors']++;
            elseif ($level === 'warning') $stats['warnings']++;
            elseif ($level === 'info') $stats['info']++;
        }
        
        return $stats;
    }
    
    /**
     * Parse log file into structured entries
     */
    public function parseLogFile(string $filepath, int $limit = 100): array
    {
        $logContent = file_get_contents($filepath);
        $logLines = preg_split('/\r\n|\r|\n/', $logContent);
        
        // Group lines into entries
        $entries = [];
        $current = '';
        
        foreach ($logLines as $line) {
            if (preg_match('/^\[?\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]?/', trim($line))) {
                if ($current !== '') {
                    $entries[] = $current;
                }
                $current = $line;
            } else {
                $current .= "\n" . $line;
            }
        }
        
        if ($current !== '') {
            $entries[] = $current;
        }
        
        // Take last N entries
        $recentEntries = array_slice($entries, -$limit);
        $recentEntries = array_reverse($recentEntries);
        
        // Structure entries
        $structured = [];
        foreach ($recentEntries as $index => $entry) {
            $timestamp = null;
            if (preg_match('/^\[?(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]?/', $entry, $m)) {
                $timestamp = $m[1];
            }
            
            $structured[] = [
                'id' => $index,
                'timestamp' => $timestamp ?? date('Y-m-d H:i:s'),
                'level' => $this->extractLogLevel($entry),
                'message' => $this->extractLogMessage($entry),
                'context' => []
            ];
        }
        
        return $structured;
    }
    
    /**
     * Extract log level from log line
     */
    public function extractLogLevel(string $line): string
    {
        if (preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY)/', $line)) return 'error';
        if (preg_match('/\.(WARNING)/', $line)) return 'warning';
        if (preg_match('/\.(INFO|NOTICE)/', $line)) return 'info';
        if (preg_match('/\.(DEBUG)/', $line)) return 'debug';
        return 'info';
    }
    
    /**
     * Extract log message from log line
     */
    public function extractLogMessage(string $line): string
    {
        if (preg_match('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*?\.(.*?)$/', $line, $matches)) {
            return trim($matches[1] ?? $line);
        }
        return $line;
    }
    
    /**
     * Filter log entries
     */
    public function filterLogEntries(Collection $entries, array $filters): Collection
    {
        return $entries->filter(function ($entry) use ($filters) {
            // Level filter
            if (isset($filters['level']) && $filters['level']) {
                if (strtolower($entry['level']) !== strtolower($filters['level'])) {
                    return false;
                }
            }
            
            // Search filter
            if (isset($filters['search']) && $filters['search']) {
                $search = $filters['search'];
                if (stripos($entry['message'], $search) === false && 
                    stripos($entry['timestamp'], $search) === false) {
                    return false;
                }
            }
            
            // Date filter
            if (isset($filters['date']) && $filters['date'] && isset($entry['timestamp'])) {
                $entryDate = Carbon::parse($entry['timestamp'])->startOfDay();
                $now = Carbon::now();
                
                switch ($filters['date']) {
                    case 'today':
                        if (!$entryDate->isSameDay($now)) return false;
                        break;
                    case 'yesterday':
                        if (!$entryDate->isSameDay($now->copy()->subDay())) return false;
                        break;
                    case 'week':
                        if ($entryDate->lt($now->copy()->startOfWeek())) return false;
                        break;
                    case 'month':
                        if ($entryDate->lt($now->copy()->startOfMonth())) return false;
                        break;
                }
            }
            
            return true;
        });
    }
    
    /**
     * Get empty stats structure
     */
    private function getEmptyStats(): array
    {
        return [
            'total' => 0,
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'file_size' => '0 KB',
            'last_entry' => 'Never'
        ];
    }

    /**
     * Clear log file
     */
    public function clearLogFile(string $filename = 'laravel.log'): array
    {
        try {
            $filename = basename($filename); // Sanitize filename
            $logPath = storage_path('logs/' . $filename);
            
            if (!File::exists($logPath)) {
                return [
                    'success' => false,
                    'message' => 'Log file not found'
                ];
            }
            
            // Get file size before clearing
            $oldSize = round(filesize($logPath) / 1024, 2);
            
            // Clear the log file content
            file_put_contents($logPath, '');
            
            return [
                'success' => true,
                'message' => "Log file '{$filename}' cleared successfully ({$oldSize} KB freed)"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error clearing log file: ' . $e->getMessage()
            ];
        }
    }
}
