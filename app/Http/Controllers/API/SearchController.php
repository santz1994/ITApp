<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Asset;
use App\Ticket;
use App\TicketsEntry;
use Illuminate\Http\Request;

/**
 * SearchController
 * 
 * Handles global search across multiple resource types (assets, tickets, comments)
 */
class SearchController extends Controller
{
    /**
     * Global search endpoint
     *
     * Searches across assets, tickets, and comments in a single request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function global(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:200',
            'types' => 'nullable|string', // comma-separated: assets,tickets,comments
            'limit' => 'nullable|integer|between:1,20', // results per type
        ]);

        $query = $validated['q'];
        $types = $validated['types'] ? explode(',', $validated['types']) : ['assets', 'tickets', 'comments'];
        $limit = min($validated['limit'] ?? 5, 20); // Max 20 per type

        $results = [];

        // Search assets
        if (in_array('assets', $types)) {
            $assets = Asset::withNestedRelations()
                ->fulltextSearch($query, ['name', 'description', 'asset_tag', 'serial_number'])
                ->selectRaw(
                    "assets.*, MATCH(name, description, asset_tag, serial_number) AGAINST(? IN BOOLEAN MODE) as relevance_score",
                    [Asset::parseSearchQuery($query)]
                )
                ->orderByDesc('relevance_score')
                ->limit($limit)
                ->get();

            $results['assets'] = $assets->map(function ($asset) use ($query) {
                return [
                    'type' => 'asset',
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'name' => $asset->name,
                    'serial_number' => $asset->serial_number,
                    'status' => [
                        'id' => $asset->status_id,
                        'name' => $asset->status->name ?? null,
                    ],
                    'division' => $asset->division->name ?? null,
                    'relevance_score' => $asset->relevance_score ?? null,
                    'snippet' => Asset::generateSnippet(
                        $asset->name . ' ' . ($asset->description ?? ''),
                        $query,
                        80
                    ),
                ];
            })->values()->all();
        }

        // Search tickets
        if (in_array('tickets', $types)) {
            $tickets = Ticket::withNestedRelations()
                ->fulltextSearch($query, ['subject', 'description', 'ticket_code'])
                ->open() // Only open tickets in global search by default
                ->selectRaw(
                    "tickets.*, MATCH(subject, description, ticket_code) AGAINST(? IN BOOLEAN MODE) as relevance_score",
                    [Ticket::parseSearchQuery($query)]
                )
                ->orderByDesc('relevance_score')
                ->limit($limit)
                ->get();

            $results['tickets'] = $tickets->map(function ($ticket) use ($query) {
                return [
                    'type' => 'ticket',
                    'id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'subject' => $ticket->subject,
                    'priority' => [
                        'id' => $ticket->ticket_priority_id,
                        'name' => $ticket->priority->name ?? null,
                    ],
                    'status' => [
                        'id' => $ticket->ticket_status_id,
                        'name' => $ticket->status->name ?? null,
                    ],
                    'created_at' => $ticket->created_at,
                    'relevance_score' => $ticket->relevance_score ?? null,
                    'snippet' => Ticket::generateSnippet(
                        $ticket->subject . ' ' . ($ticket->description ?? ''),
                        $query,
                        80
                    ),
                ];
            })->values()->all();
        }

        // Search comments
        if (in_array('comments', $types)) {
            $comments = TicketsEntry::with(['user', 'ticket'])
                ->fulltextSearch($query, ['description'])
                ->selectRaw(
                    "tickets_entries.*, MATCH(description) AGAINST(? IN BOOLEAN MODE) as relevance_score",
                    [TicketsEntry::parseSearchQuery($query)]
                )
                ->orderByDesc('relevance_score')
                ->limit($limit)
                ->get();

            $results['comments'] = $comments->map(function ($comment) use ($query) {
                return [
                    'type' => 'comment',
                    'id' => $comment->id,
                    'content' => $comment->description,
                    'ticket_id' => $comment->ticket_id,
                    'ticket_code' => $comment->ticket->ticket_code ?? null,
                    'author' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ] : null,
                    'created_at' => $comment->created_at,
                    'relevance_score' => $comment->relevance_score ?? null,
                    'snippet' => TicketsEntry::generateSnippet($comment->description, $query, 80),
                ];
            })->values()->all();
        }

        return response()->json([
            'query' => $query,
            'results' => $results,
            'summary' => [
                'assets_count' => count($results['assets'] ?? []),
                'tickets_count' => count($results['tickets'] ?? []),
                'comments_count' => count($results['comments'] ?? []),
                'total_count' => count($results['assets'] ?? []) + count($results['tickets'] ?? []) + count($results['comments'] ?? []),
            ]
        ]);
    }

    /**
     * Autocomplete suggestions for search
     *
     * Returns suggestions based on partial input
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggest(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:50',
            'type' => 'nullable|string|in:assets,tickets', // Only autocomplete for these
            'limit' => 'nullable|integer|between:1,10',
        ]);

        $query = $validated['q'] . '*'; // Add wildcard for prefix matching
        $type = $validated['type'] ?? 'all';
        $limit = min($validated['limit'] ?? 5, 10);

        $suggestions = [];

        // Asset suggestions
        if (in_array($type, ['all', 'assets'])) {
            $assets = Asset::where('name', 'like', '%' . $validated['q'] . '%')
                ->orWhere('asset_tag', 'like', '%' . $validated['q'] . '%')
                ->limit($limit)
                ->pluck('name', 'asset_tag');

            foreach ($assets as $tag => $name) {
                $suggestions[] = [
                    'type' => 'asset',
                    'label' => "$name ($tag)",
                    'value' => "asset:$name",
                ];
            }
        }

        // Ticket suggestions
        if (in_array($type, ['all', 'tickets'])) {
            $tickets = Ticket::where('subject', 'like', '%' . $validated['q'] . '%')
                ->orWhere('ticket_code', 'like', '%' . $validated['q'] . '%')
                ->limit($limit)
                ->pluck('subject', 'ticket_code');

            foreach ($tickets as $code => $subject) {
                $suggestions[] = [
                    'type' => 'ticket',
                    'label' => "$subject ($code)",
                    'value' => "ticket:$subject",
                ];
            }
        }

        return response()->json([
            'query' => $validated['q'],
            'suggestions' => $suggestions,
            'count' => count($suggestions),
        ]);
    }

    /**
     * Get search statistics/analytics
     *
     * Returns information about search capability
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        return response()->json([
            'capabilities' => [
                'assets' => [
                    'count' => Asset::count(),
                    'searchable_columns' => ['name', 'description', 'asset_tag', 'serial_number'],
                    'fulltext_available' => true,
                ],
                'tickets' => [
                    'count' => Ticket::count(),
                    'searchable_columns' => ['subject', 'description', 'ticket_code'],
                    'fulltext_available' => true,
                ],
                'comments' => [
                    'count' => TicketsEntry::count(),
                    'searchable_columns' => ['description'],
                    'fulltext_available' => true,
                ],
            ],
            'search_configuration' => [
                'minimum_query_length' => 2,
                'maximum_query_length' => 200,
                'results_limit' => 50,
                'mode' => 'BOOLEAN', // FULLTEXT search mode
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
