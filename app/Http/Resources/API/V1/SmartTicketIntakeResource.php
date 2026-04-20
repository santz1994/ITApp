<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SmartTicketIntakeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'success' => true,
            'message' => data_get($this->resource, 'message', 'Smart ticket recommendations generated successfully.'),
            'data' => [
                'recommended' => [
                    'ticket_type_id' => data_get($this->resource, 'recommended.ticket_type_id'),
                    'ticket_type_name' => data_get($this->resource, 'recommended.ticket_type_name'),
                    'ticket_priority_id' => data_get($this->resource, 'recommended.ticket_priority_id'),
                    'ticket_priority_name' => data_get($this->resource, 'recommended.ticket_priority_name'),
                    'type_confidence' => data_get($this->resource, 'recommended.type_confidence'),
                    'priority_confidence' => data_get($this->resource, 'recommended.priority_confidence'),
                ],
                'matched_signals' => data_get($this->resource, 'matched_signals', []),
                'knowledge_base_suggestions' => KnowledgeBaseSuggestionResource::collection(
                    data_get($this->resource, 'knowledge_base_suggestions', collect())
                ),
                'metadata' => data_get($this->resource, 'metadata', []),
            ],
        ];
    }
}
