<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function global(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:200',
            'types' => 'nullable|string',
            'limit' => 'nullable|integer|between:1,20',
        ]);

        return response()->json([
            'query' => $validated['q'],
            'results' => [],
            'summary' => ['total_count' => 0],
        ]);
    }

    public function suggest(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:50',
            'type' => 'nullable|string',
            'limit' => 'nullable|integer|between:1,10',
        ]);

        return response()->json([
            'query' => $validated['q'],
            'suggestions' => [],
            'count' => 0,
        ]);
    }

    public function stats()
    {
        return response()->json([
            'capabilities' => [
                'vehicles' => ['count' => \App\Vehicle::count()],
                'inventory' => ['count' => \App\InventoryItem::count()],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
