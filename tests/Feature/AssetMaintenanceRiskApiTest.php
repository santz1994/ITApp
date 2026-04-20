<?php

namespace Tests\Feature;

use App\Asset;
use App\AssetMaintenanceLog;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AssetMaintenanceRiskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_critical_maintenance_risk_analysis(): void
    {
        $user = User::factory()->create();

        $asset = Asset::factory()->create([
            'asset_tag' => 'ASTH' . random_int(1000, 9999),
            'purchase_date' => now()->subYears(6)->toDateString(),
            'warranty_months' => 12,
            'warranty_expiration_date' => now()->subDays(5)->toDateTimeString(),
            'maintenance_status' => 'in_progress',
        ]);

        AssetMaintenanceLog::create([
            'asset_id' => $asset->id,
            'performed_by' => $user->id,
            'maintenance_type' => 'repair',
            'description' => 'Repair mainboard issue',
            'status' => 'completed',
            'completed_at' => now()->subDays(80)->toDateTimeString(),
        ]);

        AssetMaintenanceLog::create([
            'asset_id' => $asset->id,
            'performed_by' => $user->id,
            'maintenance_type' => 'repair',
            'description' => 'Repair power supply issue',
            'status' => 'completed',
            'completed_at' => now()->subDays(40)->toDateTimeString(),
        ]);

        AssetMaintenanceLog::create([
            'asset_id' => $asset->id,
            'performed_by' => $user->id,
            'maintenance_type' => 'preventive',
            'description' => 'General preventive maintenance',
            'status' => 'completed',
            'completed_at' => now()->subDays(10)->toDateTimeString(),
        ]);

        AssetMaintenanceLog::create([
            'asset_id' => $asset->id,
            'performed_by' => $user->id,
            'maintenance_type' => 'repair',
            'description' => 'Open maintenance incident',
            'status' => 'in_progress',
            'scheduled_at' => now()->toDateTimeString(),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/assets/' . $asset->id . '/maintenance-risk?look_ahead_days=90');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.asset.id', $asset->id)
                ->where('data.risk.level', 'critical')
                ->where('data.risk.score', fn ($score) => is_int($score) && $score >= 80)
                ->where('data.metadata.timezone', 'Asia/Jakarta')
                ->where('data.forecast.look_ahead_days', 90)
                ->etc()
            );
    }

    public function test_endpoint_can_return_low_risk_analysis_without_reasons(): void
    {
        $user = User::factory()->create();

        $asset = Asset::factory()->create([
            'asset_tag' => 'ASTL' . random_int(1000, 9999),
            'purchase_date' => now()->subMonths(8)->toDateString(),
            'warranty_months' => 36,
            'warranty_expiration_date' => now()->addMonths(20)->toDateTimeString(),
            'maintenance_status' => 'completed',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/assets/' . $asset->id . '/maintenance-risk?look_ahead_days=120&include_reasons=0');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.risk.level', 'low')
                ->where('data.risk.score', fn ($score) => is_int($score) && $score < 35)
                ->where('data.reasons', [])
                ->where('data.forecast.look_ahead_days', 120)
                ->etc()
            );
    }

    public function test_guest_cannot_access_asset_maintenance_risk_endpoint(): void
    {
        $asset = Asset::factory()->create([
            'asset_tag' => 'ASTG' . random_int(1000, 9999),
        ]);

        $response = $this->getJson('/api/v1/assets/' . $asset->id . '/maintenance-risk');

        $response->assertStatus(401);
    }
}
