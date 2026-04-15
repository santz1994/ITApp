<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ticket;
use App\Asset;
use App\User;
use App\Location;
use App\KnowledgeBaseArticle;

class SearchController extends Controller
{
    /**
     * Global search across multiple entities
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'type' => 'nullable|in:ticket,asset,user,location,knowledge_base,all',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $perPage = $request->input('per_page', 10);

        $results = [];

        // Search Tickets
        if ($type === 'ticket' || $type === 'all') {
            $tickets = $this->searchTickets($query, $type === 'ticket' ? $perPage : 5);
            $results['tickets'] = $tickets;
        }

        // Search Assets
        if ($type === 'asset' || $type === 'all') {
            $assets = $this->searchAssets($query, $type === 'asset' ? $perPage : 5);
            $results['assets'] = $assets;
        }

        // Search Users
        if ($type === 'user' || $type === 'all') {
            $users = $this->searchUsers($query, $type === 'user' ? $perPage : 5);
            $results['users'] = $users;
        }

        // Search Locations
        if ($type === 'location' || $type === 'all') {
            $locations = $this->searchLocations($query, $type === 'location' ? $perPage : 5);
            $results['locations'] = $locations;
        }

        // Search Knowledge Base
        if ($type === 'knowledge_base' || $type === 'all') {
            $articles = $this->searchKnowledgeBase($query, $type === 'knowledge_base' ? $perPage : 5);
            $results['knowledge_base'] = $articles;
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'total_count' => $this->getTotalCount($results),
        ]);
    }

    /**
     * Search tickets
     */
    private function searchTickets($query, $limit = 10)
    {
        return Ticket::with(['user', 'ticket_status', 'ticket_priority'])
            ->where(function ($q) use ($query) {
                $q->where('ticket_code', 'like', "%{$query}%")
                  ->orWhere('subject', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($ticket) {
                return [
                    'entity_type' => 'ticket',
                    'id' => $ticket->id,
                    'title' => $ticket->subject,
                    'subtitle' => $ticket->ticket_code,
                    'description' => \Illuminate\Support\Str::limit($ticket->description, 100),
                    'url' => route('tickets.show', $ticket->id),
                    'status' => $ticket->ticket_status->name ?? 'Unknown',
                    'status_color' => $ticket->ticket_status->color ?? 'default',
                    'priority' => $ticket->ticket_priority->name ?? 'Unknown',
                    'created_by' => $ticket->user->name ?? 'Unknown',
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                    'icon' => 'fa-ticket',
                ];
            });
    }

    /**
     * Search assets
     */
    private function searchAssets($query, $limit = 10)
    {
        return Asset::with(['model', 'status', 'location', 'assignedUser'])
            ->where(function ($q) use ($query) {
                $q->where('asset_tag', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%")
                  ->orWhere('serial_number', 'like', "%{$query}%")
                  ->orWhere('ip_address', 'like', "%{$query}%")
                  ->orWhere('mac_address', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($asset) {
                return [
                    'entity_type' => 'asset',
                    'id' => $asset->id,
                    'title' => $asset->name,
                    'subtitle' => $asset->asset_tag,
                    'description' => ($asset->model->asset_model ?? 'Unknown Model') . ' - ' . ($asset->serial_number ?? 'N/A'),
                    'url' => route('assets.show', $asset->id),
                    'status' => $asset->status->name ?? 'Unknown',
                    'status_color' => $asset->status->deployable == 1 ? 'success' : 'warning',
                    'location' => $asset->location->name ?? 'Unassigned',
                    'assigned_to' => $asset->assignedUser->name ?? 'Unassigned',
                    'created_at' => $asset->created_at->format('Y-m-d H:i'),
                    'icon' => 'fa-laptop',
                ];
            });
    }

    /**
     * Search users
     */
    private function searchUsers($query, $limit = 10)
    {
        return User::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('division', 'like', "%{$query}%");
            })
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'entity_type' => 'user',
                    'id' => $user->id,
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'description' => $user->division ?? 'No division assigned',
                    'url' => route('users.show', $user->id),
                    'status' => $user->deleted_at ? 'Inactive' : 'Active',
                    'status_color' => $user->deleted_at ? 'danger' : 'success',
                    'created_at' => $user->created_at->format('Y-m-d H:i'),
                    'icon' => 'fa-user',
                ];
            });
    }

    /**
     * Search locations
     */
    private function searchLocations($query, $limit = 10)
    {
        return Location::where('name', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($location) {
                // Count assets at this location
                $assetCount = Asset::where('location_id', $location->id)->count();
                
                return [
                    'entity_type' => 'location',
                    'id' => $location->id,
                    'title' => $location->name,
                    'subtitle' => $location->address ?? 'No address',
                    'description' => "{$assetCount} asset(s) at this location",
                    'url' => route('locations.show', $location->id),
                    'asset_count' => $assetCount,
                    'created_at' => $location->created_at->format('Y-m-d H:i'),
                    'icon' => 'fa-map-marker',
                ];
            });
    }

    /**
     * Search knowledge base articles
     */
    private function searchKnowledgeBase($query, $limit = 10)
    {
        return KnowledgeBaseArticle::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($article) {
                return [
                    'entity_type' => 'knowledge_base',
                    'id' => $article->id,
                    'title' => $article->title,
                    'subtitle' => $article->category ?? 'Uncategorized',
                    'description' => \Illuminate\Support\Str::limit(strip_tags($article->content), 150),
                    'url' => route('knowledge-base.show', $article->slug),
                    'views' => $article->views,
                    'helpful_percentage' => $article->helpfulness_percentage,
                    'author' => $article->author->name ?? 'Unknown',
                    'published_at' => $article->published_at->format('Y-m-d H:i'),
                    'icon' => 'fa-book',
                ];
            });
    }

    /**
     * Calculate total count across all result types
     */
    private function getTotalCount($results)
    {
        $total = 0;
        foreach ($results as $entityResults) {
            $total += count($entityResults);
        }
        return $total;
    }

    /**
     * Quick search for autocomplete (lighter version)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickSearch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->input('q');
        $results = [];

        // Quick ticket search (only ticket_code and subject)
        $tickets = Ticket::select('id', 'ticket_code', 'subject')
            ->where('ticket_code', 'like', "%{$query}%")
            ->orWhere('subject', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'type' => 'ticket',
                    'id' => $ticket->id,
                    'label' => $ticket->ticket_code . ' - ' . $ticket->subject,
                    'url' => route('tickets.show', $ticket->id),
                ];
            });

        // Quick asset search (only asset_tag and name)
        $assets = Asset::select('id', 'asset_tag', 'name')
            ->where('asset_tag', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($asset) {
                return [
                    'type' => 'asset',
                    'id' => $asset->id,
                    'label' => $asset->asset_tag . ' - ' . $asset->name,
                    'url' => route('assets.show', $asset->id),
                ];
            });

        // Quick user search (only name and email)
        $users = User::select('id', 'name', 'email')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'id' => $user->id,
                    'label' => $user->name . ' (' . $user->email . ')',
                    'url' => route('users.show', $user->id),
                ];
            });

        $results = array_merge(
            $tickets->toArray(),
            $assets->toArray(),
            $users->toArray()
        );

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $results,
        ]);
    }
}
