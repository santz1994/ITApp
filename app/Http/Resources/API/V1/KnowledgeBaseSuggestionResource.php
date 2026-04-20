<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeBaseSuggestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'tags' => $this->tags,
            'views' => $this->views,
            'helpful_count' => $this->helpful_count,
            'not_helpful_count' => $this->not_helpful_count,
            'author' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
            ],
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
