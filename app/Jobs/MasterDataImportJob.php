<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Illuminate\Support\Str;

class MasterDataImportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $userId;

    public function __construct(string $path, $userId = null)
    {
        $this->path = $path;
        $this->userId = $userId;
    }

    public function handle()
    {
        // Basic implementation: if the uploaded file is a zip, extract CSVs; if CSV, try to process.
        $storagePath = Storage::path($this->path);
        $ext = pathinfo($storagePath, PATHINFO_EXTENSION);

        $results = [];

        try {
            if (in_array(strtolower($ext), ['zip'])) {
                // Extract zip to temporary folder
                $tmpDir = storage_path('app/imports/tmp_' . Str::random(6));
                @mkdir($tmpDir, 0755, true);
                $zip = new \ZipArchive();
                if ($zip->open($storagePath) === true) {
                    $zip->extractTo($tmpDir);
                    $zip->close();

                    $files = glob($tmpDir . '/*.csv');
                    foreach ($files as $f) {
                        $results[] = $this->processCsv($f);
                    }
                }
            } elseif (in_array(strtolower($ext), ['csv', 'txt'])) {
                $full = $storagePath;
                $results[] = $this->processCsv($full);
            } else {
                $results[] = ['file' => $this->path, 'status' => 'skipped', 'message' => 'Unsupported file type'];
            }
        } catch (\Exception $e) {
            $results[] = ['file' => $this->path, 'status' => 'error', 'message' => $e->getMessage()];
        }

        // Write results to storage for later retrieval
        $out = storage_path('app/imports/results_' . time() . '.json');
        file_put_contents($out, json_encode($results));
    }

    protected function processCsv($file)
    {
        try {
            $csv = Reader::createFromPath($file, 'r');
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
            $records = iterator_to_array($csv->getRecords());

            // Very small mapping: if CSV has 'location_name' map to Location model, etc.
            $processed = 0;
            foreach ($records as $r) {
                // Simple example: create or update model by name if model key known
                if (isset($r['location_name'])) {
                    \App\Location::updateOrCreate(['location_name' => $r['location_name']], $r);
                    $processed++;
                } elseif (isset($r['type_name'])) {
                    \App\AssetType::updateOrCreate(['type_name' => $r['type_name']], $r);
                    $processed++;
                } elseif (isset($r['manufacturer_name']) || isset($r['name'])) {
                    // manufacturer or generic name -> try Manufacturer
                    if (isset($r['manufacturer_name'])) {
                        \App\Manufacturer::updateOrCreate(['name' => $r['manufacturer_name']], $r);
                        $processed++;
                    }
                } else {
                    // Unmapped row - skip
                }
            }

            return ['file' => $file, 'status' => 'done', 'processed' => $processed];
        } catch (\Exception $e) {
            return ['file' => $file, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
