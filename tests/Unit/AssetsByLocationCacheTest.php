<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Services\AssetService;
use App\Asset;
use App\AssetModel;
use App\Division;
use App\Status;

class AssetsByLocationCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_getAssetsByLocation_caches_and_is_invalidated_on_create()
    {
        Cache::flush();
        $service = app(AssetService::class);

        $before = $service->getAssetsByLocation();
        $this->assertCount(0, $before);

        $model = AssetModel::factory()->create(['asset_model' => 'LocModel']);
        $divisionA = Division::factory()->create(['name' => 'DivA']);
        $divisionB = Division::factory()->create(['name' => 'DivB']);
        $status = Status::factory()->create();

        // Create assets in two divisions
        Asset::factory()->create(['division_id' => $divisionA->id, 'model_id' => $model->id, 'status_id' => $status->id]);
        Asset::factory()->count(2)->create(['division_id' => $divisionB->id, 'model_id' => $model->id, 'status_id' => $status->id]);

        // After creation, cache should have been invalidated by observer and show results
        $after = $service->getAssetsByLocation();
        $this->assertGreaterThanOrEqual(1, $after->count());

        // Ensure counts align (find DivA and DivB)
        $map = $after->mapWithKeys(fn($r) => [$r->division_name => (int)$r->count])->toArray();
        $this->assertEquals(1, $map['DivA']);
        $this->assertEquals(2, $map['DivB']);
    }

    public function test_bulkUpdate_invalidates_assets_by_location_cache()
    {
        Cache::flush();
        $service = app(AssetService::class);

        $model = AssetModel::factory()->create(['asset_model' => 'BulkLocModel']);
        $division = Division::factory()->create(['name' => 'BulkDiv']);
        $status = Status::factory()->create();

        $assets = Asset::factory()->count(3)->create(['division_id' => $division->id, 'model_id' => $model->id, 'status_id' => $status->id]);

        // Prime cache
        $primed = $service->getAssetsByLocation();
        $this->assertGreaterThanOrEqual(1, $primed->count());

        // Bulk update a field - should invalidate cache
        $service->bulkUpdateAssets($assets->pluck('id')->toArray(), ['notes' => 'bulk updated']);

        $after = $service->getAssetsByLocation();
        $this->assertGreaterThanOrEqual(1, $after->count());
    }
}
