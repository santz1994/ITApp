<?php

namespace App\Services;

use App\Asset;
use App\Repositories\Assets\AssetMaintenanceRiskRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PredictiveMaintenanceService
{
    private AssetMaintenanceRiskRepository $repository;

    public function __construct(AssetMaintenanceRiskRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate predictive maintenance risk analysis for one asset.
     *
     * @return array<string, mixed>
     */
    public function evaluateByAssetId(int $assetId, int $lookAheadDays = 90, bool $includeReasons = true): array
    {
        $asset = $this->repository->findAssetWithMaintenanceContext($assetId);

        return $this->evaluateAsset($asset, $lookAheadDays, $includeReasons);
    }

    /**
     * @return array<string, mixed>
     */
    private function evaluateAsset(Asset $asset, int $lookAheadDays, bool $includeReasons): array
    {
        $now = now('Asia/Jakarta');

        $completedLogs = $asset->maintenanceLogs
            ->filter(function ($log) {
                return $log->status === 'completed' && $log->completed_at !== null;
            })
            ->sortBy('completed_at')
            ->values();

        $completedLast90Days = $completedLogs
            ->filter(function ($log) use ($now) {
                return $log->completed_at->gte($now->copy()->subDays(90));
            })
            ->count();

        $pendingMaintenanceCount = $asset->maintenanceLogs
            ->filter(function ($log) {
                return in_array((string) $log->status, ['planned', 'in_progress'], true);
            })
            ->count();

        $lastCompletedAt = $completedLogs->last()?->completed_at;
        $daysSinceLastCompleted = $lastCompletedAt ? $lastCompletedAt->diffInDays($now) : null;

        $purchaseDate = $asset->purchase_date;
        $hasPurchaseDate = $purchaseDate !== null;
        $ageMonths = $hasPurchaseDate ? $purchaseDate->diffInMonths($now) : 0;

        $usefulLifeMonths = $this->resolveUsefulLifeMonths($asset);
        $lifeUsedRatio = $usefulLifeMonths > 0 ? round($ageMonths / $usefulLifeMonths, 2) : 0.0;

        $warrantyExpirationDate = $asset->warranty_expiration_date;
        $warrantyDaysRemaining = $warrantyExpirationDate
            ? $now->diffInDays($warrantyExpirationDate, false)
            : null;

        $maintenanceStatus = $asset->maintenance_status ? strtolower((string) $asset->maintenance_status) : null;

        $componentScores = [
            'age' => $this->scoreAgeRisk($lifeUsedRatio, $hasPurchaseDate),
            'maintenance_frequency' => $this->scoreMaintenanceFrequency($completedLast90Days),
            'pending_maintenance' => $this->scorePendingMaintenance($pendingMaintenanceCount),
            'warranty' => $this->scoreWarrantyRisk($warrantyDaysRemaining, $lookAheadDays),
            'maintenance_status' => $this->scoreMaintenanceStatus($maintenanceStatus),
            'staleness' => $this->scoreStalenessRisk($daysSinceLastCompleted),
        ];

        $riskScore = array_sum(array_map(function (array $component): int {
            return $component['score'];
        }, $componentScores));
        $riskScore = max(0, min(100, $riskScore));

        $riskLevel = $this->resolveRiskLevel($riskScore);
        $nextMaintenanceDate = $this->predictNextMaintenanceDate($completedLogs, $riskLevel, $now);

        $reasons = collect($componentScores)
            ->pluck('reason')
            ->filter(function ($reason) {
                return is_string($reason) && $reason !== '';
            })
            ->values()
            ->all();

        return [
            'message' => 'Predictive maintenance risk analysis generated successfully.',
            'asset' => [
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'status_id' => $asset->status_id,
                'maintenance_status' => $asset->maintenance_status,
                'purchase_date' => $purchaseDate ? $purchaseDate->toDateString() : null,
                'warranty_expiration_date' => $warrantyExpirationDate ? $warrantyExpirationDate->toDateString() : null,
            ],
            'risk' => [
                'score' => $riskScore,
                'level' => $riskLevel,
                'recommendation' => $this->buildRecommendation($riskLevel),
            ],
            'signals' => [
                'age_months' => $ageMonths,
                'useful_life_months' => $usefulLifeMonths,
                'life_used_ratio' => $lifeUsedRatio,
                'completed_maintenance_last_90_days' => $completedLast90Days,
                'pending_maintenance_count' => $pendingMaintenanceCount,
                'days_since_last_completed_maintenance' => $daysSinceLastCompleted,
                'warranty_days_remaining' => $warrantyDaysRemaining,
                'maintenance_status' => $maintenanceStatus,
                'score_components' => [
                    'age' => $componentScores['age']['score'],
                    'maintenance_frequency' => $componentScores['maintenance_frequency']['score'],
                    'pending_maintenance' => $componentScores['pending_maintenance']['score'],
                    'warranty' => $componentScores['warranty']['score'],
                    'maintenance_status' => $componentScores['maintenance_status']['score'],
                    'staleness' => $componentScores['staleness']['score'],
                ],
            ],
            'reasons' => $includeReasons ? $reasons : [],
            'forecast' => [
                'predicted_next_maintenance_date' => $nextMaintenanceDate->toDateString(),
                'look_ahead_days' => $lookAheadDays,
            ],
            'metadata' => [
                'api_version' => 'v1',
                'model_version' => 'predictive-maintenance-heuristic-v1',
                'timezone' => 'Asia/Jakarta',
                'generated_at' => $now->toIso8601String(),
            ],
        ];
    }

    private function resolveUsefulLifeMonths(Asset $asset): int
    {
        $explicitUsefulLifeMonths = (int) data_get($asset, 'useful_life_months', 0);
        if ($explicitUsefulLifeMonths > 0) {
            return $explicitUsefulLifeMonths;
        }

        $warrantyMonths = (int) ($asset->warranty_months ?? 0);
        if ($warrantyMonths > 0) {
            return max(24, $warrantyMonths * 2);
        }

        return 60;
    }

    /**
     * @return array{score:int,reason:string}
     */
    private function scoreAgeRisk(float $lifeUsedRatio, bool $hasPurchaseDate): array
    {
        if (! $hasPurchaseDate) {
            return [
                'score' => 10,
                'reason' => 'Purchase date is unavailable; baseline risk raised.',
            ];
        }

        if ($lifeUsedRatio >= 1.0) {
            return [
                'score' => 35,
                'reason' => 'Asset age has exceeded estimated useful life.',
            ];
        }

        if ($lifeUsedRatio >= 0.85) {
            return [
                'score' => 25,
                'reason' => 'Asset age is approaching estimated useful life.',
            ];
        }

        if ($lifeUsedRatio >= 0.65) {
            return [
                'score' => 15,
                'reason' => 'Asset age is in moderate risk zone.',
            ];
        }

        return [
            'score' => 3,
            'reason' => 'Asset age contribution is currently low.',
        ];
    }

    /**
     * @return array{score:int,reason:string}
     */
    private function scoreMaintenanceFrequency(int $completedLast90Days): array
    {
        if ($completedLast90Days >= 3) {
            return [
                'score' => 25,
                'reason' => 'Frequent maintenance observed in the last 90 days.',
            ];
        }

        if ($completedLast90Days === 2) {
            return [
                'score' => 15,
                'reason' => 'Recurring maintenance observed in the last 90 days.',
            ];
        }

        if ($completedLast90Days === 1) {
            return [
                'score' => 8,
                'reason' => 'One maintenance event recorded in the last 90 days.',
            ];
        }

        return [
            'score' => 0,
            'reason' => 'No completed maintenance in the last 90 days.',
        ];
    }

    /**
     * @return array{score:int,reason:string}
     */
    private function scorePendingMaintenance(int $pendingMaintenanceCount): array
    {
        if ($pendingMaintenanceCount >= 3) {
            return [
                'score' => 20,
                'reason' => 'Multiple pending maintenance tasks detected.',
            ];
        }

        if ($pendingMaintenanceCount === 2) {
            return [
                'score' => 14,
                'reason' => 'Two pending maintenance tasks detected.',
            ];
        }

        if ($pendingMaintenanceCount === 1) {
            return [
                'score' => 8,
                'reason' => 'One pending maintenance task detected.',
            ];
        }

        return [
            'score' => 0,
            'reason' => 'No pending maintenance tasks detected.',
        ];
    }

    /**
     * @return array{score:int,reason:string}
     */
    private function scoreWarrantyRisk(?int $warrantyDaysRemaining, int $lookAheadDays): array
    {
        if ($warrantyDaysRemaining === null) {
            return [
                'score' => 6,
                'reason' => 'Warranty expiry is unavailable; moderate risk baseline added.',
            ];
        }

        if ($warrantyDaysRemaining < 0) {
            return [
                'score' => 20,
                'reason' => 'Warranty has expired.',
            ];
        }

        if ($warrantyDaysRemaining <= $lookAheadDays) {
            return [
                'score' => 12,
                'reason' => 'Warranty will expire within the configured look-ahead window.',
            ];
        }

        if ($warrantyDaysRemaining <= ($lookAheadDays * 2)) {
            return [
                'score' => 6,
                'reason' => 'Warranty expiry is approaching in the medium term.',
            ];
        }

        return [
            'score' => 0,
            'reason' => 'Warranty horizon contribution is low.',
        ];
    }

    /**
     * @return array{score:int,reason:string}
     */
    private function scoreMaintenanceStatus(?string $maintenanceStatus): array
    {
        if ($maintenanceStatus === 'in_progress') {
            return [
                'score' => 15,
                'reason' => 'Asset is currently in active maintenance.',
            ];
        }

        if ($maintenanceStatus === 'scheduled') {
            return [
                'score' => 8,
                'reason' => 'Asset already has scheduled maintenance workload.',
            ];
        }

        return [
            'score' => 0,
            'reason' => 'Maintenance status contribution is low.',
        ];
    }

    /**
     * @return array{score:int,reason:string}
     */
    private function scoreStalenessRisk(?int $daysSinceLastCompleted): array
    {
        if ($daysSinceLastCompleted === null) {
            return [
                'score' => 8,
                'reason' => 'No completed maintenance history found.',
            ];
        }

        if ($daysSinceLastCompleted > 365) {
            return [
                'score' => 15,
                'reason' => 'Last completed maintenance is older than 365 days.',
            ];
        }

        if ($daysSinceLastCompleted > 180) {
            return [
                'score' => 8,
                'reason' => 'Last completed maintenance is older than 180 days.',
            ];
        }

        return [
            'score' => 0,
            'reason' => 'Maintenance recency contribution is low.',
        ];
    }

    private function resolveRiskLevel(int $riskScore): string
    {
        if ($riskScore >= 80) {
            return 'critical';
        }

        if ($riskScore >= 60) {
            return 'high';
        }

        if ($riskScore >= 35) {
            return 'medium';
        }

        return 'low';
    }

    private function buildRecommendation(string $riskLevel): string
    {
        $recommendations = [
            'critical' => 'Escalate immediate maintenance planning and assign technician within 24 hours.',
            'high' => 'Schedule maintenance in the next 7 days and monitor usage closely.',
            'medium' => 'Plan preventive maintenance in the next 30 days and review trend monthly.',
            'low' => 'Continue routine monitoring with standard preventive maintenance cycle.',
        ];

        return $recommendations[$riskLevel] ?? $recommendations['medium'];
    }

    private function predictNextMaintenanceDate(Collection $completedLogs, string $riskLevel, Carbon $now): Carbon
    {
        if ($completedLogs->count() >= 2) {
            $completedDates = $completedLogs
                ->pluck('completed_at')
                ->filter()
                ->map(function ($value) {
                    return $value instanceof Carbon
                        ? $value->copy()->timezone('Asia/Jakarta')
                        : Carbon::parse((string) $value, 'Asia/Jakarta');
                })
                ->values();

            $intervals = [];
            for ($index = 1; $index < $completedDates->count(); $index++) {
                $intervals[] = max(1, $completedDates[$index - 1]->diffInDays($completedDates[$index]));
            }

            if (! empty($intervals)) {
                $averageInterval = (int) round(collect($intervals)->avg());
                $averageInterval = max(7, min(180, $averageInterval));

                $predictedDate = $completedDates->last()->copy()->addDays($averageInterval);
                if ($predictedDate->lessThanOrEqualTo($now)) {
                    return $now->copy()->addDays(max(7, min(45, (int) ceil($averageInterval / 2))));
                }

                return $predictedDate;
            }
        }

        $fallbackDaysByRisk = [
            'critical' => 7,
            'high' => 14,
            'medium' => 30,
            'low' => 60,
        ];

        return $now->copy()->addDays($fallbackDaysByRisk[$riskLevel] ?? 30);
    }
}
