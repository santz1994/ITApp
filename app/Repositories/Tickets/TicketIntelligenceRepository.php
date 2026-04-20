<?php

namespace App\Repositories\Tickets;

use App\KnowledgeBaseArticle;
use App\TicketsPriority;
use App\TicketsType;
use Illuminate\Support\Collection;

class TicketIntelligenceRepository
{
    /**
     * Fetch ticket types for NLP mapping.
     */
    public function getTicketTypes(): Collection
    {
        return TicketsType::query()
            ->select(['id', 'type'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Fetch ticket priorities for NLP mapping.
     */
    public function getTicketPriorities(): Collection
    {
        return TicketsPriority::query()
            ->select(['id', 'priority'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Fetch published knowledge-base suggestions using keyword matching.
     */
    public function findKnowledgeBaseSuggestions(array $tokens, int $limit = 5): Collection
    {
        if (empty($tokens)) {
            return collect();
        }

        return KnowledgeBaseArticle::query()
            ->select([
                'id',
                'title',
                'slug',
                'category',
                'tags',
                'helpful_count',
                'not_helpful_count',
                'views',
                'published_at',
                'author_id',
            ])
            ->with('author:id,name')
            ->published()
            ->where(function ($query) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%' . $token . '%';

                    $query->orWhere('title', 'like', $like)
                        ->orWhere('content', 'like', $like)
                        ->orWhere('category', 'like', $like);
                }
            })
            ->orderByDesc('helpful_count')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }
}
