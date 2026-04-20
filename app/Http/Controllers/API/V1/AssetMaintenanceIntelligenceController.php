<?php

namespace App\Http\Controllers\API\V1;

use App\Asset;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\GetAssetMaintenanceRiskRequest;
use App\Http\Resources\API\V1\AssetMaintenanceRiskResource;
use App\Services\PredictiveMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AssetMaintenanceIntelligenceController extends Controller
{
    private PredictiveMaintenanceService $predictiveMaintenanceService;

    public function __construct(PredictiveMaintenanceService $predictiveMaintenanceService)
    {
        $this->predictiveMaintenanceService = $predictiveMaintenanceService;
    }

    public function show(GetAssetMaintenanceRiskRequest $request, Asset $asset): JsonResponse
    {
        try {
            $analysis = $this->predictiveMaintenanceService->evaluateByAssetId(
                (int) $asset->id,
                (int) $request->integer('look_ahead_days', 90),
                $request->boolean('include_reasons', true)
            );

            return (new AssetMaintenanceRiskResource($analysis))
                ->response()
                ->setStatusCode(200);
        } catch (\Throwable $exception) {
            Log::error('Failed to evaluate predictive maintenance risk', [
                'asset_id' => $asset->id,
                'exception_message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PREDICTIVE_MAINTENANCE_ANALYSIS_FAILED',
                    'message' => 'Failed to generate predictive maintenance risk analysis.',
                ],
                'message' => 'Unable to process predictive maintenance request.',
            ], 500);
        }
    }
}
