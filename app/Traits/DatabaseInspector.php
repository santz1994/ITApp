<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DatabaseInspector
{
    /**
     * Return an array of table names for the current connection, DB-agnostic.
     * @return array
     */
    private function getAllTables()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            return collect($rows)->map(function ($r) { return $r->name; })->toArray();
        }

        if ($driver === 'mysql') {
            $rows = DB::select('SHOW TABLES');
            return collect($rows)->map(function ($r) {
                return head((array)$r);
            })->toArray();
        }

        // Fallback: try Doctrine if available
        try {
            return DB::connection()->getDoctrineSchemaManager()->listTableNames();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get normalized column metadata for a table.
     * Returns a collection of objects with keys: column_name, type, nullable, key, default, extra, auto_increment
     */
    private function getTableColumnsNormalized($tableName)
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $cols = DB::select("PRAGMA table_info('" . $tableName . "')");
            return collect($cols)->map(function ($c) {
                return (object)[
                    'column_name' => $c->name,
                    'type' => $c->type,
                    'nullable' => ($c->notnull == 0),
                    'key' => ($c->pk ? 'PRI' : ''),
                    'default' => $c->dflt_value,
                    'extra' => '',
                    'auto_increment' => false
                ];
            });
        }

        if ($driver === 'mysql') {
            $cols = DB::select("DESCRIBE {$tableName}");
            return collect($cols)->map(function ($column) {
                return (object)[
                    'column_name' => $column->Field,
                    'type' => $column->Type,
                    'nullable' => $column->Null === 'YES',
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra,
                    'auto_increment' => strpos($column->Extra, 'auto_increment') !== false
                ];
            });
        }

        // Fallback: use Schema facade to inspect columns
        try {
            $cols = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($tableName);
            return collect($cols)->map(function ($col) {
                return (object)[
                    'column_name' => $col->getName(),
                    'type' => (string)$col->getType(),
                    'nullable' => !$col->getNotnull(),
                    'key' => '',
                    'default' => $col->getDefault(),
                    'extra' => '',
                    'auto_increment' => $col->getAutoincrement() ?? false
                ];
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get indexes for a table in a DB-agnostic way. Returns raw DB rows where possible.
     */
    private function getTableIndexesAgnostic($tableName)
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return DB::select("PRAGMA index_list('" . $tableName . "')");
        }

        if ($driver === 'mysql') {
            return DB::select("SHOW INDEXES FROM {$tableName}");
        }

        return [];
    }

    /**
     * Get table stats (row count and size where available).
     */
    private function getTableStatsAgnostic($tableName)
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            try {
                $cnt = DB::select("SELECT COUNT(*) as cnt FROM \"{$tableName}\"");
                $rows = $cnt[0]->cnt ?? 0;
            } catch (\Exception $e) {
                $rows = 0;
            }
            return (object)[
                'row_count' => $rows,
                'data_size' => 0,
                'index_size' => 0,
                'total_size' => 0
            ];
        }

        if ($driver === 'mysql') {
            $stats = DB::select("SELECT table_rows as row_count, data_length as data_size, index_length as index_size, (data_length + index_length) as total_size FROM information_schema.tables WHERE table_schema = ? AND table_name = ?", [config('database.connections.mysql.database'), $tableName]);
            return $stats ? $stats[0] : null;
        }

        return null;
    }

    /**
     * Get database size in bytes (best-effort).
     */
    private function getDatabaseSizeAgnostic($databaseName)
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            if ($path && file_exists($path)) {
                return filesize($path);
            }
            return 0;
        }

        if ($driver === 'mysql') {
            $result = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = ?", [$databaseName]);
            return $result[0]->size ?? 0;
        }

        return 0;
    }
}
