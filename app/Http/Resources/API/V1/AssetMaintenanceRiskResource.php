<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetMaintenanceRiskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'success' => true,
            'message' => data_get($this->resource, 'message', 'Predictive maintenance risk analysis generated successfully.'),
            'data' => [
                'asset' => data_get($this->resource, 'asset', []),
                'risk' => data_get($this->resource, 'risk', []),
                'signals' => data_get($this->resource, 'signals', []),
                'reasons' => data_get($this->resource, 'reasons', []),
                'forecast' => data_get($this->resource, 'forecast', []),
                'metadata' => data_get($this->resource, 'metadata', []),
            ],
        ];
    }
}
