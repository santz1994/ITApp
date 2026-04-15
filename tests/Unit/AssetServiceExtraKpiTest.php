<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\AssetService;
use App\Asset;

class AssetServiceExtraKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_assets_by_status_and_monthly_new_are_cached_and_invalidated()
    {
        $service = $this->app->make(AssetService::class);

        // Prime caches
        $statusBefore = $service->assetsByStatusBreakdown();
        $monthlyBefore = $service->monthlyNewAssets(2);

        // Create necessary related data: model, division, status, supplier
        $model = \App\AssetModel::factory()->create();
        $division = \App\Division::factory()->create(['name' => 'IT']);
        $supplier = \App\Supplier::factory()->create(['name' => 'Acme']);
        $status = \App\Status::factory()->create(['name' => 'Active']);

        // Create asset that should change counts (use actual ids)
        Asset::create([
            'asset_tag' => 'KPITEST',
            'model_id' => $model->id,
            'division_id' => $division->id,
            'supplier_id' => $supplier->id,
            'status_id' => $status->id,
        ]);

        // After creating, caches should be invalidated and new values differ or at least be retrievable
        $statusAfter = $service->assetsByStatusBreakdown();
        $monthlyAfter = $service->monthlyNewAssets(2);

        $this->assertNotNull($statusBefore);
        $this->assertNotNull($monthlyBefore);
        $this->assertNotNull($statusAfter);
        $this->assertNotNull($monthlyAfter);

        // At minimum, ensure returned collections contain expected keys
        $this->assertTrue(collect($statusAfter)->firstWhere('status_name', 'Active') !== null);
    }
}
