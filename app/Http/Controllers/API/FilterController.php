<?php

namespace App\Http\Controllers\API;

use App\Division;
use App\Http\Controllers\Controller;
use App\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function filterOptions(Request $request, string $resourceType, string $filterName): JsonResponse
    {
        $options = match ($filterName) {
            'division', 'division_id', 'department', 'department_id' => $this->getDivisionOptions(),
            'location', 'location_id' => $this->getLocationOptions(),
            default => [],
        };

        return response()->json([
            'filter' => $filterName,
            'type' => $resourceType,
            'options' => $options,
        ]);
    }

    public function filterBuilder(Request $request): JsonResponse
    {
        return response()->json([
            'type' => $request->get('type', 'unknown'),
            'filters' => [],
            'endpoints' => [],
        ]);
    }

    public function filterStats(): JsonResponse
    {
        return response()->json([
            'vehicles' => ['total' => \App\Vehicle::count()],
            'inventory' => ['total' => \App\InventoryItem::count()],
        ]);
    }

    private function getDivisionOptions(): array
    {
        return Division::select('id', 'name')->orderBy('name')->get()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'count' => 0])
            ->toArray();
    }

    private function getLocationOptions(): array
    {
        return Location::select('id', 'name', 'parent_location_id')->orderBy('name')->get()
            ->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'parent_id' => $l->parent_location_id, 'count' => 0])
            ->toArray();
    }
}
