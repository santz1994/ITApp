<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportDuplicateSerialRows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:export-duplicate-rows {--input= : Path to duplicate CSV (defaults to storage/serial_duplicates.csv)} {--output= : Path to export CSV (defaults to storage/serial_duplicates_full.csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export full asset rows for serials listed in the duplicate CSV';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $input = $this->option('input') ?: storage_path('serial_duplicates.csv');
        $output = $this->option('output') ?: storage_path('serial_duplicates_full.csv');

        if (!file_exists($input)) {
            $this->error("Input file not found: {$input}");
            return 1;
        }

        $lines = array_map('trim', file($input));
        // skip header
        $serials = [];
        foreach ($lines as $i => $line) {
            if ($i === 0) continue;
            $parts = str_getcsv($line);
            if (!empty($parts[0])) {
                $serials[] = $parts[0];
            }
        }

        if (empty($serials)) {
            $this->info('No serials found in input.');
            return 0;
        }

        // Query assets for these serials (use parameterized IN)
        $placeholders = implode(',', array_fill(0, count($serials), '?'));
        $rows = DB::select("SELECT * FROM assets WHERE serial_number IN ({$placeholders}) ORDER BY serial_number, id", $serials);

        if (empty($rows)) {
            $this->info('No matching asset rows found for serials.');
            return 0;
        }

        $handle = fopen($output, 'w');
        // header
        $cols = array_keys((array)$rows[0]);
        fputcsv($handle, $cols);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(function($v){ return is_null($v) ? '' : $v; }, (array)$row));
        }
        fclose($handle);

        $this->info('Exported ' . count($rows) . ' rows to: ' . $output);
        return 0;
    }
}
