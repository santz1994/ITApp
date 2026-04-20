<?php

namespace App\Repositories\Assets;

use App\Asset;

class AssetMaintenanceRiskRepository
{
    public function findAssetWithMaintenanceContext(int $assetId): Asset
    {
        return Asset::query()
            ->with([
                'maintenanceLogs' => function ($query) {
                    $query->orderBy('completed_at', 'asc')
                        ->orderBy('scheduled_at', 'asc')
                        ->orderBy('created_at', 'asc');
                },
            ])
            ->findOrFail($assetId);
    }
}
