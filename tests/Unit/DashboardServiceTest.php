<?php

namespace Tests\Unit;

use App\Services\AssetService;
use App\Services\DashboardService;
use App\Repositories\Dashboard\DashboardRepository;
use App\Ticket;
use App\TicketsStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_build_dashboard_data_returns_expected_payload_shape_and_values(): void
    {
        Cache::flush();

        TicketsStatus::factory()->create(['status' => 'Open']);

        Ticket::factory()->create([
            'ticket_status_id' => 1,
            'sla_due' => now('Asia/Jakarta')->subHour(),
        ]);

        /** @var AssetService&\Mockery\MockInterface $assetService */
        $assetService = Mockery::mock(AssetService::class);
        $assetService->shouldReceive('getAssetStatistics')
            ->once()
            ->andReturn(['total' => 42]);
        $assetService->shouldReceive('getAssetsNeedingMaintenance')
            ->once()
            ->andReturn(collect([['id' => 1], ['id' => 2]]));

        $service = new DashboardService(app(DashboardRepository::class), $assetService);
        $payload = $service->buildDashboardData();

        $this->assertArrayHasKey('stats', $payload);
        $this->assertArrayHasKey('recentTickets', $payload);
        $this->assertArrayHasKey('assetStats', $payload);
        $this->assertArrayHasKey('maintenanceDue', $payload);

        $this->assertSame(1, $payload['stats']['open_tickets']);
        $this->assertSame(1, $payload['stats']['overdue_tickets']);
        $this->assertSame(42, $payload['stats']['total_assets']);
        $this->assertSame(2, $payload['stats']['maintenance_due']);
        $this->assertCount(1, $payload['recentTickets']);
    }
}