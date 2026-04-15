<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\AssetService;
use App\Asset;
use App\Division;
use App\Status;
use App\AssetModel;

class AssetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_asset_statistics_and_assets_by_location()
    {
        $service = new AssetService();

        $division = Division::factory()->create(['name' => 'IT']);
        $model = AssetModel::factory()->create(['asset_model' => 'Test Model']);
        $status = Status::factory()->create(['name' => 'Active']);

        // Create some assets
        Asset::factory()->count(3)->create(["division_id" => $division->id, 'model_id' => $model->id, 'status_id' => $status->id]);

        $stats = $service->getAssetStatistics();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertEquals(3, $stats['total']);

        $byLocation = $service->getAssetsByLocation();
        $this->assertNotEmpty($byLocation);
        $this->assertEquals('IT', $byLocation->first()->division_name);
        $this->assertEquals(3, $byLocation->first()->count);
    }
}
