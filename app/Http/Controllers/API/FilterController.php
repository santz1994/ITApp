<?php

namespace App\Http\Controllers\API;

use App\Asset;
use App\Division;
use App\Http\Controllers\Controller;
use App\Http\Resources\FilterOptionResource;
use App\Location;
use App\Manufacturer;
use App\Status;
use App\Ticket;
use App\TicketsPriority;
use App\TicketsStatus;
use App\TicketsType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FilterController
 * 
 * Provides filtering utilities for the API:
 * - Filter options for dropdown menus
 * - Filter presets (save/load/delete custom filters)
 * - Filter statistics
 * 
 * @package App\Http\Controllers\API
 */
class FilterController extends Controller
{
    /**
     * Get available options for a specific filter
     * 
     * Returns a list of options that can be used for filtering.
     * Useful for populating dropdown menus in the UI.
     * 
     * Example responses:
     * GET /api/v1/assets/filter-options/status
     * {
     *   "filter": "status",
     *   "type": "Asset",
     *   "options": [
     *     {"id": 1, "name": "Active", "count": 45},
     *     {"id": 2, "name": "Inactive", "count": 12}
     *   ]
     * }
     * 
     * @param Request $request
     * @param string $resourceType Resource type: 'asset' or 'ticket'
     * @param string $filterName Filter name
     * @return JsonResponse
     */
    public function filterOptions(Request $request, string $resourceType, string $filterName): JsonResponse
    {
        $resourceType = strtolower($resourceType);

        // Determine model class
        $model = null;
        if ($resourceType === 'asset' || $resourceType === 'assets') {
            $model = new Asset();
        } elseif ($resourceType === 'ticket' || $resourceType === 'tickets') {
            $model = new Ticket();
        } else {
            return response()->json([
                'error' => 'Invalid resource type. Must be "asset" or "ticket".',
                'supported_types' => ['asset', 'ticket'],
            ], 400);
        }

        // Get options for the filter
        $options = $this->getFilterOptionsByName($model, $filterName, $resourceType);

        if ($options === null) {
            return response()->json([
                'error' => "Invalid filter name: {$filterName}",
                'supported_filters' => array_keys($model->getAvailableFilters()),
            ], 400);
        }

        return response()->json([
            'filter' => $filterName,
            'type' => $resourceType,
            'options' => $options,
        ]);
    }

    /**
     * Get filter options by filter name
     * 
     * Maps filter names to actual queries and returns results.
     * 
     * @param object $model
     * @param string $filterName
     * @param string $resourceType
     * @return array|null
     */
    private function getFilterOptionsByName($model, string $filterName, string $resourceType): ?array
    {
        switch ($filterName) {
            case 'status':
            case 'status_id':
                return $resourceType === 'asset'
                    ? $this->getStatusOptions()
                    : $this->getTicketStatusOptions();

            case 'priority':
            case 'priority_id':
                return $this->getPriorityOptions();

            case 'division':
            case 'division_id':
                return $this->getDivisionOptions();

            case 'location':
            case 'location_id':
                return $this->getLocationOptions();

            case 'manufacturer':
            case 'manufacturer_id':
                return $this->getManufacturerOptions();

            case 'type':
            case 'type_id':
                return $resourceType === 'asset'
                    ? $this->getAssetTypeOptions()
                    : $this->getTicketTypeOptions();

            case 'assigned_to':
                return $this->getAssignedToOptions($resourceType);

            case 'department':
            case 'department_id':
                return $this->getDivisionOptions();

            default:
                return null;
        }
    }

    /**
     * Get asset status options
     */
    private function getStatusOptions(): array
    {
        $statuses = Status::select('id', 'name')
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return $statuses->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'count' => $s->assets_count,
        ])->toArray();
    }

    /**
     * Get ticket status options
     */
    private function getTicketStatusOptions(): array
    {
        // tickets_statuses stores the label in `status` column
        $statuses = TicketsStatus::select('id', DB::raw('status as status_name'))
            ->withCount('tickets')
            ->orderBy('status')
            ->get();

        return $statuses->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->status_name,
            'count' => $s->tickets_count,
        ])->toArray();
    }

    /**
     * Get priority options
     */
    private function getPriorityOptions(): array
    {
        // tickets_priorities stores label in `priority` column
        $priorities = TicketsPriority::select('id', DB::raw('priority as priority_name'))
            ->withCount('tickets')
            ->orderBy('priority')
            ->get();

        return $priorities->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->priority_name,
            'count' => $p->tickets_count,
        ])->toArray();
    }

    /**
     * Get division options
     */
    private function getDivisionOptions(): array
    {
        $divisions = Division::select('id', 'name')
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return $divisions->map(fn($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'count' => $d->assets_count,
        ])->toArray();
    }

    /**
     * Get location options with hierarchy
     */
    private function getLocationOptions(): array
    {
        $locations = Location::select('id', 'name', 'parent_location_id')
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return $locations->map(fn($l) => [
            'id' => $l->id,
            'name' => $l->name,
            'parent_id' => $l->parent_location_id,
            'count' => $l->assets_count,
        ])->toArray();
    }

    /**
     * Get manufacturer options
     */
    private function getManufacturerOptions(): array
    {
        $manufacturers = Manufacturer::select('id', 'name')
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return $manufacturers->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'count' => $m->assets_count,
        ])->toArray();
    }

    /**
     * Get asset type options
     */
    private function getAssetTypeOptions(): array
    {
        // This would depend on your actual AssetType model
        try {
            if (class_exists('App\AssetType')) {
                $types = \App\AssetType::select('id', 'name')
                    ->withCount('assets')
                    ->orderBy('name')
                    ->get();

                return $types->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'count' => $t->assets_count,
                ])->toArray();
            }
        } catch (\Exception $e) {
            // Asset type not available
        }

        return [];
    }

    /**
     * Get ticket type options
     */
    private function getTicketTypeOptions(): array
    {
        $types = TicketsType::select('id', 'name')
            ->withCount('tickets')
            ->orderBy('name')
            ->get();

        return $types->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'count' => $t->tickets_count,
        ])->toArray();
    }

    /**
     * Get assigned to (users) options
     */
    private function getAssignedToOptions(string $resourceType): array
    {
        $users = \App\User::select('id', 'name', 'email')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return $users->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
        ])->toArray();
    }

    /**
     * Get filter builder information
     * 
     * Returns available filters and their types for dynamic UI generation.
     * 
     * GET /api/v1/filter-builder
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function filterBuilder(Request $request): JsonResponse
    {
        $resourceType = $request->get('type', 'asset');

        $model = $resourceType === 'ticket' ? new Ticket() : new Asset();
        $filters = $model->getAvailableFilters();

        return response()->json([
            'type' => $resourceType,
            'filters' => $filters,
            'endpoints' => [
                'options' => "/api/v1/{$resourceType}s/filter-options/{filter}",
                'list' => "/api/v1/{$resourceType}s",
            ],
        ]);
    }

    /**
     * Get filter statistics
     * 
     * Returns statistics about filters:
     * - Total assets/tickets
     * - Distribution by status, priority, etc.
     * 
     * GET /api/v1/filter-stats
     * 
     * @return JsonResponse
     */
    public function filterStats(): JsonResponse
    {
        return response()->json([
            'assets' => [
                'total' => Asset::count(),
                'by_status' => Asset::select('status_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('status_id')
                    ->get()
                    ->toArray(),
                'by_division' => Asset::select('division_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('division_id')
                    ->get()
                    ->toArray(),
                'by_location' => Asset::select('location_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('location_id')
                    ->get()
                    ->toArray(),
            ],
            'tickets' => [
                'total' => Ticket::count(),
                'by_status' => Ticket::select('status_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('status_id')
                    ->get()
                    ->toArray(),
                'by_priority' => Ticket::select('priority_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('priority_id')
                    ->get()
                    ->toArray(),
                'by_type' => Ticket::select('type_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('type_id')
                    ->get()
                    ->toArray(),
            ],
        ]);
    }
}
