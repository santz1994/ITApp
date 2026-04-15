<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Services\AssetService;
use App\Asset;
use App\AssetModel;
use App\Division;
use App\Supplier;
use App\Status;

class AssetServiceCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_getAssetStatistics_cache_is_invalidated_on_model_create_via_observer()
    {
        Cache::flush();

        $service = app(AssetService::class);

        // Prime cache
        $statsBefore = $service->getAssetStatistics();
        $this->assertEquals(0, $statsBefore['total']);

        // Create related models and an asset (observer should clear cache on created)
        $model = AssetModel::factory()->create(['asset_model' => 'CacheModel']);
        $division = Division::factory()->create();
        $status = Status::factory()->create();

        Asset::factory()->create([
            'asset_tag' => 'CACHE-1',
            'model_id' => $model->id,
            'division_id' => $division->id,
            'status_id' => $status->id,
        ]);

        $statsAfter = $service->getAssetStatistics();
        $this->assertEquals(1, $statsAfter['total']);
    }

    public function test_invalidate_cache_on_service_create_and_update()
    {
        Cache::flush();
        $service = app(AssetService::class);

        $model = AssetModel::factory()->create(['asset_model' => 'SvcModel']);
        $division = Division::factory()->create();
    $status = Status::factory()->create();
    $supplier = \App\Supplier::factory()->create();

        // Prime cache
        $initial = $service->getAssetStatistics();
        $this->assertEquals(0, $initial['total']);

        // Create asset via service (should invalidate cache)
        $asset = $service->createAsset([
            'asset_tag' => 'SVC-1',
            'model_id' => $model->id,
            'division_id' => $division->id,
            'status_id' => $status->id,
            'supplier_id' => $supplier->id,
        ]);

        $afterCreate = $service->getAssetStatistics();
        $this->assertEquals(1, $afterCreate['total']);

        // Update asset via service (should also invalidate cache)
        $service->updateAsset($asset, ['notes' => 'updated']);
        $afterUpdate = $service->getAssetStatistics();
        $this->assertEquals(1, $afterUpdate['total']);
    }
}
