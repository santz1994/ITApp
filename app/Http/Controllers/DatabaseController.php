<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\DatabaseInspector;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseController extends Controller
{
    use DatabaseInspector;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin'); // Only super-admin can access database management
    }

    /**
     * Display database overview
     */
    public function index()
    {
        try {
            $pageTitle = 'Database Management';
            
            // Get database info
            $databaseName = config('database.connections.' . config('database.default') . '.database');
            $connectionName = config('database.default');
            
            // Get all tables
            $tables = $this->getAllTables();
            
            // Get database size
            $databaseSize = $this->getDatabaseSize($databaseName);
            
            // Get table statistics
            $tableStats = [];
            foreach ($tables as $table) {
                $tableStats[$table] = $this->getTableStats($table);
            }
            
            // Recent database operations log (if exists)
            $recentOperations = $this->getRecentOperations();
            
            $stats = [
                'total_tables' => count($tables),
                'database_size' => $databaseSize,
                'connection' => $connectionName,
                'database_name' => $databaseName,
            ];
            
            return view('admin.database.index', compact(
                'pageTitle', 'tables', 'tableStats', 'stats', 'recentOperations'
            ));
            
        } catch (\Exception $e) {
            Log::error('Database management error: ' . $e->getMessage());
            return back()->with('error', 'Error loading database information: ' . $e->getMessage());
        }
    }

    /**
     * Show table structure and data
     */
    public function showTable(Request $request, $tableName)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            $pageTitle = "Table: {$tableName}";
            
            // Get table structure
            $columns = $this->getTableColumns($tableName);
            $indexes = $this->getTableIndexes($tableName);
            
            // Get table data with pagination
            $perPage = $request->input('per_page', 25);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            
            $query = DB::table($tableName);
            
            if ($search) {
                // Search across all text-like columns (varchar, text, char)
                $textColumns = collect($columns)->filter(function($col) {
                    $type = strtolower($col->type ?? '');
                    return strpos($type, 'varchar') !== false || strpos($type, 'text') !== false || strpos($type, 'char') !== false;
                })->pluck('column_name');

                if ($textColumns->isNotEmpty()) {
                    $query->where(function($q) use ($textColumns, $search) {
                        foreach ($textColumns as $column) {
                            $q->orWhere($column, 'like', "%{$search}%");
                        }
                    });
                }
            }
            
            $totalRecords = $query->count();
            $records = $query->paginate($perPage);
            
            // Get table stats
            $tableStats = $this->getTableStats($tableName);
            
            return view('admin.database.table', compact(
                'pageTitle', 'tableName', 'columns', 'indexes', 'records', 
                'tableStats', 'search', 'totalRecords'
            ));
            
        } catch (\Exception $e) {
            Log::error('Table view error: ' . $e->getMessage());
            return back()->with('error', 'Error loading table: ' . $e->getMessage());
        }
    }

    /**
     * Wrapper for legacy routes that expect `show(Request $request, $table, $id)`
     * Delegates to showTable for backwards compatibility.
     */
    public function show(Request $request, $tableName, $id = null)
    {
        // Note: some legacy routes expected an {id} parameter; showTable handles
        // displaying the table and paginated rows. If an id is provided we
        // forward it via the request so showTable can use the search/pagination
        // normally (or you can extend behaviour later to show a single record).
        if ($id !== null) {
            // If needed, attach id to request as a search parameter for quick lookup
            $request->merge(['search' => $id]);
        }

        return $this->showTable($request, $tableName);
    }

    /**
     * Show create record form
     */
    public function create($tableName)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            $pageTitle = "Add Record to {$tableName}";
            $columns = $this->getTableColumns($tableName);
            
            // Remove auto-increment and timestamp columns from form
            $editableColumns = collect($columns)->filter(function($column) {
                return !$column->auto_increment && 
                       !in_array($column->column_name, ['created_at', 'updated_at']);
            });
            
            return view('admin.database.create', compact(
                'pageTitle', 'tableName', 'editableColumns'
            ));
            
        } catch (\Exception $e) {
            Log::error('Create form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading create form: ' . $e->getMessage());
        }
    }

    /**
     * Store new record
     */
    public function store(Request $request, $tableName)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            $data = $request->except(['_token', '_method']);
            
            // Remove empty values and prepare data
            $insertData = [];
            foreach ($data as $key => $value) {
                if ($value !== null && $value !== '') {
                    $insertData[$key] = $value;
                }
            }
            
            // Add timestamps if table has them
            if ($this->hasTimestamps($tableName)) {
                $insertData['created_at'] = Carbon::now();
                $insertData['updated_at'] = Carbon::now();
            }
            
            // Pre-check unique indexes to avoid duplicate key DB errors (e.g. UNIQUE(user_id))
            $indexes = $this->getTableIndexes($tableName);
            $indexMap = [];
            foreach ($indexes as $idx) {
                // MySQL SHOW INDEX returns Key_name, Column_name, Non_unique
                $key = $idx->Key_name ?? $idx->Key_name ?? null;
                $col = $idx->Column_name ?? $idx->Column_name ?? null;
                $nonUnique = isset($idx->Non_unique) ? (int)$idx->Non_unique : null;

                if (!$key || !$col) continue;

                if (!isset($indexMap[$key])) {
                    $indexMap[$key] = ['non_unique' => $nonUnique, 'columns' => []];
                }
                $indexMap[$key]['columns'][] = $col;
            }

            foreach ($indexMap as $keyName => $info) {
                // Only consider unique indexes (non_unique == 0) and skip primary
                if (isset($info['non_unique']) && (int)$info['non_unique'] === 0 && strtoupper($keyName) !== 'PRIMARY') {
                    $cols = $info['columns'];
                    // Check if all columns for this unique index are present in insert data
                    $allPresent = true;
                    foreach ($cols as $c) {
                        if (!array_key_exists($c, $insertData)) { $allPresent = false; break; }
                    }

                    if ($allPresent) {
                        $q = DB::table($tableName);
                        foreach ($cols as $c) {
                            $q->where($c, $insertData[$c]);
                        }
                        if ($q->exists()) {
                            // Return friendly error pointing out which unique index prevented insertion
                            return back()->withInput()->with('error', "A record with the same values for unique index '{$keyName}' already exists.");
                        }
                    }
                }
            }

            $id = DB::table($tableName)->insertGetId($insertData);
            
            $this->logOperation('INSERT', $tableName, $id, $insertData);
            
            return redirect()->route('admin.database.table', $tableName)
                ->with('success', 'Record created successfully.');
                
        } catch (\Exception $e) {
            Log::error('Record creation error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error creating record: ' . $e->getMessage());
        }
    }

    /**
     * Show edit record form
     */
    public function edit($tableName, $id)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            $pageTitle = "Edit Record in {$tableName}";
            $columns = $this->getTableColumns($tableName);
            
            // Get the record
            $record = DB::table($tableName)->where('id', $id)->first();
            
            if (!$record) {
                return back()->with('error', 'Record not found.');
            }
            
            // Remove auto-increment and certain system columns from form
            $editableColumns = collect($columns)->filter(function($column) {
                return !$column->auto_increment && 
                       !in_array($column->column_name, ['created_at']);
            });
            
            return view('admin.database.edit', compact(
                'pageTitle', 'tableName', 'editableColumns', 'record', 'id'
            ));
            
        } catch (\Exception $e) {
            Log::error('Edit form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update record
     */
    public function update(Request $request, $tableName, $id)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            $data = $request->except(['_token', '_method']);
            
            // Remove empty values and prepare data
            $updateData = [];
            foreach ($data as $key => $value) {
                if ($key !== 'id') { // Don't update ID
                    $updateData[$key] = $value;
                }
            }
            
            // Add updated_at if table has it
            if ($this->hasTimestamps($tableName) && !isset($updateData['updated_at'])) {
                $updateData['updated_at'] = Carbon::now();
            }
            
            $affected = DB::table($tableName)->where('id', $id)->update($updateData);
            
            if ($affected > 0) {
                $this->logOperation('UPDATE', $tableName, $id, $updateData);
                return redirect()->route('admin.database.table', $tableName)
                    ->with('success', 'Record updated successfully.');
            } else {
                return back()->with('warning', 'No changes were made.');
            }
                
        } catch (\Exception $e) {
            Log::error('Record update error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error updating record: ' . $e->getMessage());
        }
    }

    /**
     * Delete record
     */
    public function destroy($tableName, $id)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            // Get record details before deletion for logging
            $record = DB::table($tableName)->where('id', $id)->first();
            
            if (!$record) {
                return back()->with('error', 'Record not found.');
            }
            
            $deleted = DB::table($tableName)->where('id', $id)->delete();
            
            if ($deleted > 0) {
                $this->logOperation('DELETE', $tableName, $id, (array)$record);
                return back()->with('success', 'Record deleted successfully.');
            } else {
                return back()->with('error', 'Failed to delete record.');
            }
                
        } catch (\Exception $e) {
            Log::error('Record deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting record: ' . $e->getMessage());
        }
    }

    /**
     * Truncate table
     */
    public function truncate($tableName)
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table($tableName)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->logOperation('TRUNCATE', $tableName, null, []);
            
            return back()->with('success', "Table {$tableName} has been truncated successfully.");
                
        } catch (\Exception $e) {
            Log::error('Table truncate error: ' . $e->getMessage());
            return back()->with('error', 'Error truncating table: ' . $e->getMessage());
        }
    }

    /**
     * Export table data
     */
    public function export($tableName, $format = 'csv')
    {
        try {
            if (!$this->tableExists($tableName)) {
                return back()->with('error', 'Table does not exist.');
            }

            $data = DB::table($tableName)->get();
            $filename = "{$tableName}_export_" . date('Y-m-d_H-i-s') . ".{$format}";
            
            if ($format === 'csv') {
                return $this->exportToCsv($data, $filename, $tableName);
            } elseif ($format === 'sql') {
                return $this->exportToSql($tableName, $filename);
            }
            
            return back()->with('error', 'Unsupported export format.');
                
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Database backup
     */
    public function backup()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Use mysqldump command
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            $backupPath = storage_path('app/backups/');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $fullPath = $backupPath . $filename;
            
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$fullPath}";
            
            exec($command, $output, $return_var);
            
            if ($return_var === 0) {
                $this->logOperation('BACKUP', 'database', null, ['file' => $filename]);
                
                return response()->download($fullPath)->deleteFileAfterSend(false);
            } else {
                return back()->with('error', 'Backup failed. Please check server configuration.');
            }
                
        } catch (\Exception $e) {
            Log::error('Backup error: ' . $e->getMessage());
            return back()->with('error', 'Error creating backup: ' . $e->getMessage());
        }
    }

    // Helper Methods (trait provides DB-agnostic implementations)

    private function tableExists($tableName)
    {
        return Schema::hasTable($tableName);
    }

    private function getTableColumns($tableName)
    {
        return $this->getTableColumnsNormalized($tableName);
    }

    private function getTableIndexes($tableName)
    {
        return $this->getTableIndexesAgnostic($tableName);
    }

    private function getTableStats($tableName)
    {
        return $this->getTableStatsAgnostic($tableName);
    }

    private function getDatabaseSize($databaseName)
    {
        return $this->getDatabaseSizeAgnostic($databaseName);
    }

    private function hasTimestamps($tableName)
    {
        $columns = $this->getTableColumns($tableName);
        $columnNames = $columns->pluck('column_name')->toArray();
        
        return in_array('created_at', $columnNames) && in_array('updated_at', $columnNames);
    }

    private function logOperation($operation, $tableName, $recordId, $data)
    {
        Log::info("Database operation: {$operation}", [
            'table' => $tableName,
            'record_id' => $recordId,
            'data' => $data,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
    }

    private function getRecentOperations()
    {
        // This would require a separate logging table in a production environment
        // For now, return empty array
        return [];
    }

    private function exportToCsv($data, $filename, $tableName)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if ($data->count() > 0) {
                $firstRow = $data->first();
                fputcsv($file, array_keys((array)$firstRow));
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, array_values((array)$row));
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToSql($tableName, $filename)
    {
        $structure = DB::select("SHOW CREATE TABLE {$tableName}");
        $data = DB::table($tableName)->get();
        
        $sql = "-- Export for table: {$tableName}\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Add table structure
        $sql .= $structure[0]->{'Create Table'} . ";\n\n";
        
        // Add data
        if ($data->count() > 0) {
            $sql .= "-- Data for table: {$tableName}\n";
            
            foreach ($data as $row) {
                $values = collect((array)$row)->map(function($value) {
                    return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                })->implode(', ');
                
                $columns = implode(', ', array_keys((array)$row));
                $sql .= "INSERT INTO {$tableName} ({$columns}) VALUES ({$values});\n";
            }
        }
        
        $headers = [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        return response($sql, 200, $headers);
    }
}