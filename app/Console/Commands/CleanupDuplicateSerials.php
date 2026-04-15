<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateSerials extends Command
{
    protected $signature = 'assets:cleanup-duplicates {--keep=earliest : keep "earliest" (min id) or "latest" (max id)} {--apply : actually modify rows (otherwise dry-run)}';

    protected $description = 'Backup and cleanup duplicate serial_number values. By default does a dry-run. Use --apply to change data.';

    public function handle()
    {
        $keep = $this->option('keep') ?: 'earliest';
        $apply = $this->option('apply') ? true : false;

        $this->info("Running duplicate cleanup (keep={$keep}, apply=" . ($apply ? 'yes' : 'no') . ")");

        // Find duplicates
        $dups = DB::table('assets')
            ->select('serial_number', DB::raw('count(*) as occurrences'))
            ->whereNotNull('serial_number')
            ->groupBy('serial_number')
            ->having('occurrences', '>', 1)
            ->get();

        if ($dups->isEmpty()) {
            $this->info('No duplicates found.');
            return 0;
        }

        $serials = $dups->pluck('serial_number')->toArray();

        $placeholders = implode(',', array_fill(0, count($serials), '?'));
        $rows = DB::select("SELECT * FROM assets WHERE serial_number IN ({$placeholders}) ORDER BY serial_number, id", $serials);

        $reportPath = storage_path('serial_cleanup_report.csv');
        $handle = fopen($reportPath, $apply ? 'w' : 'w');
        fputcsv($handle, ['action', 'serial_number', 'asset_id', 'asset_tag', 'old_serial']);

        // Create backup table (DDL outside transaction to avoid DB errors)
        try {
            DB::statement('CREATE TABLE IF NOT EXISTS asset_serial_backup (asset_id BIGINT PRIMARY KEY, old_serial TEXT, backup_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
        } catch (\Exception $e) {
            $this->error('Failed creating backup table: ' . $e->getMessage());
            fclose($handle);
            return 1;
        }

        // Group rows by serial
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r->serial_number][] = (array)$r;
        }

        try {
            foreach ($grouped as $serial => $items) {
                // determine keep id
                $ids = array_column($items, 'id');
                $keepId = ($keep === 'latest') ? max($ids) : min($ids);

                foreach ($items as $it) {
                    $assetId = $it['id'];
                    if ($assetId == $keepId) {
                        // keep
                        fputcsv($handle, ['keep', $serial, $assetId, $it['asset_tag'] ?? '', $it['serial_number']]);
                        continue;
                    }
                    // backup
                    DB::table('asset_serial_backup')->updateOrInsert(
                        ['asset_id' => $assetId],
                        ['old_serial' => $it['serial_number']]
                    );
                    if ($apply) {
                        DB::table('assets')->where('id', $assetId)->update(['serial_number' => null]);
                        fputcsv($handle, ['nullified', $serial, $assetId, $it['asset_tag'] ?? '', $it['serial_number']]);
                    } else {
                        fputcsv($handle, ['would_nullify', $serial, $assetId, $it['asset_tag'] ?? '', $it['serial_number']]);
                    }
                }
            }

            $this->info(($apply ? 'Applied cleanup.' : 'Dry-run completed.') . ' Report written to: ' . $reportPath);
            fclose($handle);
            return 0;
        } catch (\Exception $e) {
            fclose($handle);
            $this->error('Error during cleanup: ' . $e->getMessage());
            return 1;
        }
    }
}
