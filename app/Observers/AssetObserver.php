<?php

namespace App\Observers;

use App\Asset;
use App\Status;
use App\Events\AssetStatusChanged;
use Illuminate\Support\Facades\Auth;
use App\Services\AssetService;

class AssetObserver
{
    public function updating(Asset $asset)
    {
        if ($asset->isDirty('status_id')) {
            $oldStatus = Status::find($asset->getOriginal('status_id'));
            $newStatus = Status::find($asset->status_id);
            $changedBy = Auth::user();
            $notes = $asset->notes ?? null;
            event(new AssetStatusChanged($asset, $oldStatus, $newStatus, $changedBy, $notes));
        }
        // Clear KPI cache when an asset is updated
        try {
            app(AssetService::class)->invalidateKpiCache();
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function created(Asset $asset)
    {
        try {
            app(AssetService::class)->invalidateKpiCache();
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function deleted(Asset $asset)
    {
        try {
            app(AssetService::class)->invalidateKpiCache();
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
