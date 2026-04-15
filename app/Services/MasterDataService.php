<?php

namespace App\Services;

use App\Jobs\MasterDataImportJob;
use Illuminate\Support\Facades\Bus;

class MasterDataService
{
    /**
     * Dispatch an import job for the provided uploaded file path.
     * Returns a job identifier (timestamp-based) for quick reference.
     */
    public function dispatchImportJob(string $path, $user = null)
    {
        $job = new MasterDataImportJob($path, $user ? $user->id : null);
        // Dispatch job synchronously for now (queue can be configured later)
        dispatch($job);
        return now()->timestamp;
    }
}
