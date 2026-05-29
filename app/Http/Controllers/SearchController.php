<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Location;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'type' => 'nullable|in:user,location,all',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $perPage = $request->input('per_page', 10);

        $results = [];

        if ($type === 'user' || $type === 'all') {
            $results['users'] = $this->searchUsers($query, $type === 'user' ? $perPage : 5);
        }

        if ($type === 'location' || $type === 'all') {
            $results['locations'] = $this->searchLocations($query, $type === 'location' ? $perPage : 5);
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'total_count' => $this->getTotalCount($results),
        ]);
    }

    private function searchUsers($query, $limit = 10)
    {
        return User::where(fn($q) => $q->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'entity_type' => 'user',
                'id' => $user->id,
                'title' => $user->name,
                'subtitle' => $user->email,
                'url' => route('users.show', $user->id),
                'icon' => 'fa-user',
            ]);
    }

    private function searchLocations($query, $limit = 10)
    {
        return Location::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($location) => [
                'entity_type' => 'location',
                'id' => $location->id,
                'title' => $location->name,
                'subtitle' => $location->address ?? '',
                'url' => '#',
                'icon' => 'fa-map-marker',
            ]);
    }

    private function getTotalCount($results)
    {
        $total = 0;
        foreach ($results as $entityResults) {
            $total += count($entityResults);
        }
        return $total;
    }

    public function quickSearch(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $query = $request->input('q');

        $users = User::select('id', 'name', 'email')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($user) => [
                'type' => 'user',
                'id' => $user->id,
                'label' => $user->name . ' (' . $user->email . ')',
                'url' => route('users.show', $user->id),
            ]);

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $users->toArray(),
        ]);
    }
}
