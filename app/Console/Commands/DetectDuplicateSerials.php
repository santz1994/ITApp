<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DetectDuplicateSerials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:detect-duplicate-serials {--export= : Path to CSV export (defaults to storage/app/serial_duplicates.csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect duplicate serial_number values in assets table and optionally export to CSV';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $exportOption = $this->option('export');
        $exportPath = $exportOption ? base_path($exportOption) : storage_path('app/serial_duplicates.csv');

        $duplicates = DB::table('assets')
            ->select('serial_number', DB::raw('GROUP_CONCAT(id) as ids'), DB::raw('COUNT(*) as occurrences'))
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->groupBy('serial_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate serial_number values found.');
            return 0;
        }

        $this->info('Found ' . $duplicates->count() . ' duplicated serial_number(s). Exporting to: ' . $exportPath);

        $handle = fopen($exportPath, 'w');
        fputcsv($handle, ['serial_number', 'occurrences', 'ids']);
        foreach ($duplicates as $row) {
            fputcsv($handle, [$row->serial_number, $row->occurrences ?? 0, $row->ids ?? '']);
        }
        fclose($handle);

        $this->line('Export complete. Please review and clean duplicates before running the migration to add the unique index.');

        return 2; // non-zero to signal caller that duplicates exist
    }
}

