<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Asset;
use App\User;
use App\Http\Requests\SearchAssetRequest;
use App\Http\Requests\AssetFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
  /**
   * Display a listing of assets with advanced filtering
   *
   * Supports:
   * - Date range: ?date_from=2025-01-01&date_to=2025-12-31
   * - Multi-select: ?status_id[]=1&status_id[]=2&status_id[]=3
   * - Location hierarchy: ?location_id=5&include_sublocation=true
   * - Range filters: ?price_min=1000&price_max=5000
   * - Complex filters: Combined with search, sorting, and pagination
   *
   * @param AssetFilterRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(AssetFilterRequest $request)
  {
    $filters = $request->getFilterParams();
    
    $query = Asset::withNestedRelations(); // Use optimized nested loading

    // Apply advanced filters
    $query->applyFilters($filters);

    // Sorting with relationship support
    $sortBy = $filters['sort_by'] ?? 'id';
    $sortOrder = $filters['sort_order'] ?? 'desc';
    $query->sortBy($sortBy, $sortOrder);

    // Pagination with validation (max 50)
    $perPage = min($filters['per_page'] ?? 15, 50);

    /** @var \Illuminate\Pagination\LengthAwarePaginator $assets */
    $assets = $query->paginate($perPage);

    // Transform data
    $assets->getCollection()->transform(function ($asset) {
      return [
        'id' => $asset->id,
        'asset_tag' => $asset->asset_tag,
        'name' => $asset->name,
        'serial_number' => $asset->serial_number,
        'mac_address' => $asset->mac_address,
        'ip_address' => $asset->ip_address,
        'purchase_date' => $asset->purchase_date,
        'warranty_expiry' => $asset->warranty_expiry,
        'location' => $asset->location->name ?? null,
        'division' => $asset->division->name ?? null,
        'status' => [
          'id' => $asset->status_id,
          'name' => $asset->status->name ?? null,
          'badge' => $asset->status_badge
        ],
        'assigned_user' => $asset->assignedTo ? [
          'id' => $asset->assignedTo->id,
          'name' => $asset->assignedTo->name,
          'email' => $asset->assignedTo->email
        ] : null,
        'asset_model' => $asset->assetModel ? [
          'id' => $asset->assetModel->id,
          'name' => $asset->assetModel->name,
          'manufacturer' => $asset->assetModel->manufacturer->name ?? null
        ] : null,
        'depreciation_percentage' => $asset->depreciation_percentage,
        'warranty_expiry_date' => $asset->warranty_expiry_date,
        'formatted_mac_address' => $asset->formatted_mac_address,
        'is_warranty_expired' => $asset->is_warranty_expired,
        'warranty_status' => $asset->warranty_status,
        'created_at' => $asset->created_at,
        'updated_at' => $asset->updated_at
      ];
    });

    return response()->json([
      'success' => true,
      'data' => $assets,
      'message' => 'Assets retrieved successfully'
    ]);
  }    /**
     * Store a newly created asset
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create assets'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'asset_tag' => 'required|string|unique:assets',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location_id' => 'required|exists:locations,id',
            'division_id' => 'required|exists:divisions,id',
            'status_id' => 'required|exists:statuses,id',
            'model_id' => 'required|exists:asset_models,id',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        // Only use fields that exist in the Asset model
        $assetData = array_intersect_key($validatedData, array_flip([
            'asset_tag', 'name', 'serial_number', 'mac_address', 'ip_address', 
            'purchase_date', 'warranty_expiry', 'location_id', 'division_id', 
            'status_id', 'model_id', 'assigned_to'
        ]));
        
        $asset = Asset::create($assetData);
        $asset->load(['status', 'division', 'location', 'assetModel', 'assignedUser']);

        // Auto-assign if assigned_to is provided
        if ($request->assigned_to) {
            $user = User::find($request->assigned_to);
            $asset->assignTo($user);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformAsset($asset),
            'message' => 'Asset created successfully'
        ], 201);
    }

    /**
     * Display the specified asset
     *
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Asset $asset)
    {
        $asset->load(['status', 'division', 'location', 'assetModel', 'assignedUser', 'tickets']);

        return response()->json([
            'success' => true,
            'data' => $this->transformAsset($asset, true),
            'message' => 'Asset retrieved successfully'
        ]);
    }

    /**
     * Update the specified asset
     *
     * @param Request $request
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Asset $asset)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update assets'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'asset_tag' => 'sometimes|string|unique:assets,asset_tag,' . $asset->id,
            'name' => 'sometimes|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $asset->id,
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location_id' => 'sometimes|exists:locations,id',
            'division_id' => 'sometimes|exists:divisions,id',
            'status_id' => 'sometimes|exists:statuses,id',
            'model_id' => 'sometimes|exists:asset_models,id',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        // Only use fields that exist in the Asset model  
        $assetData = array_intersect_key($validatedData, array_flip([
            'asset_tag', 'name', 'serial_number', 'mac_address', 'ip_address', 
            'purchase_date', 'warranty_expiry', 'location_id', 'division_id', 
            'status_id', 'model_id', 'assigned_to'
        ]));
        
        $asset->update($assetData);
        $asset->load(['status', 'division', 'location', 'assetModel', 'assignedUser']);

        return response()->json([
            'success' => true,
            'data' => $this->transformAsset($asset),
            'message' => 'Asset updated successfully'
        ]);
    }

    /**
     * Check serial number uniqueness (AJAX)
     *
     * Query params:
     * - serial (string) required
     * - exclude_id (int) optional - asset id to exclude from check (for edits)
     */
    public function checkSerial(Request $request)
    {
        $serial = $request->query('serial');
        $exclude = $request->query('exclude_id');

        if (!$serial) {
            return response()->json(['success' => false, 'message' => 'serial parameter is required'], 400);
        }

        $query = Asset::where('serial_number', $serial);
        if ($exclude) {
            $query->where('id', '!=', $exclude);
        }

        $exists = $query->exists();

        return response()->json(['success' => true, 'exists' => $exists]);
    }

    /**
     * Remove the specified asset
     *
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Asset $asset)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete assets'
            ], 403);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset deleted successfully'
        ]);
    }

    /**
     * Assign asset to user
     *
     * @param Request $request
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, Asset $asset)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to assign assets'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        $result = $asset->assignTo($user);

        return response()->json([
            'success' => true,
            'data' => $this->transformAsset($asset->fresh(['assignedUser'])),
            'message' => 'Asset assigned successfully'
        ]);
    }

    /**
     * Unassign asset from user
     *
     * @param Request $request
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassign(Request $request, Asset $asset)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to unassign assets'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $asset->unassign();

        return response()->json([
            'success' => true,
            'data' => $this->transformAsset($asset->fresh(['assignedUser'])),
            'message' => 'Asset unassigned successfully'
        ]);
    }

    /**
     * Mark asset for maintenance
     *
     * @param Request $request
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function markForMaintenance(Request $request, Asset $asset)
    {
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update assets'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $asset->markForMaintenance($request->notes);

        return response()->json([
            'success' => true,
            'data' => $this->transformAsset($asset->fresh(['status'])),
            'message' => 'Asset marked for maintenance successfully'
        ]);
    }

    /**
     * Get asset history
     *
     * @param Asset $asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Asset $asset)
    {
        $movements = $asset->movements()->with(['user', 'status'])->orderBy('created_at', 'desc')->get();
        $tickets = $asset->tickets()->with(['user', 'status', 'priority'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'movements' => $movements,
                'tickets' => $tickets
            ],
            'message' => 'Asset history retrieved successfully'
        ]);
    }

    /**
     * Transform asset data
     *
     * @param Asset $asset
     * @param bool $detailed
     * @return array
     */
    private function transformAsset(Asset $asset, $detailed = false)
    {
        $data = [
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'serial_number' => $asset->serial_number,
            'mac_address' => $asset->mac_address,
            'ip_address' => $asset->ip_address,
            'purchase_date' => $asset->purchase_date,
            'warranty_expiry' => $asset->warranty_expiry,
            'location' => $asset->location->name ?? null,
            'division' => $asset->division->name ?? null,
            'status' => [
                'id' => $asset->status_id,
                'name' => $asset->status->name ?? null,
                'badge' => $asset->status_badge
            ],
            'assigned_user' => $asset->assignedUser ? [
                'id' => $asset->assignedUser->id,
                'name' => $asset->assignedUser->name,
                'email' => $asset->assignedUser->email
            ] : null,
            'asset_model' => $asset->assetModel ? [
                'id' => $asset->assetModel->id,
                'name' => $asset->assetModel->name,
                'manufacturer' => $asset->assetModel->manufacturer->name ?? null
            ] : null,
            'depreciation_percentage' => $asset->depreciation_percentage,
            'warranty_expiry_date' => $asset->warranty_expiry_date,
            'formatted_mac_address' => $asset->formatted_mac_address,
            'is_warranty_expired' => $asset->is_warranty_expired,
            'warranty_status' => $asset->warranty_status,
            'created_at' => $asset->created_at,
            'updated_at' => $asset->updated_at
        ];

        if ($detailed) {
            $data['tickets'] = $asset->tickets->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'description' => $ticket->description,
                    'status' => $ticket->status->name ?? null,
                    'priority' => $ticket->priority->name ?? null,
                    'created_at' => $ticket->created_at
                ];
            });
        }

        return $data;
    }

    /**
     * Search assets using FULLTEXT search
     *
     * @param SearchAssetRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchAssetRequest $request)
    {
        $params = $request->validated();
        $query = $params['q'];
        $type = $params['type'] ?? 'all';
        $sort = $params['sort'] ?? 'relevance';
        $perPage = min($params['per_page'] ?? 20, 50);

        // Determine search columns based on type
        $columns = match($type) {
            'name' => ['name'],
            'tag' => ['asset_tag'],
            'serial' => ['serial_number'],
            default => ['name', 'description', 'asset_tag', 'serial_number']
        };

        // Build search query with eager loading
        $assets = Asset::withNestedRelations()
            ->fulltextSearch($query, $columns);

        // Apply additional filters if provided
        if ($request->has('status_id')) {
            $assets->byStatus($request->get('status_id'));
        }

        if ($request->has('division_id')) {
            $assets->where('division_id', $request->get('division_id'));
        }

        if ($request->has('location_id')) {
            $assets->where('location_id', $request->get('location_id'));
        }

        if ($request->has('manufacturer_id')) {
            $assets->whereHas('assetModel', function($q) {
                $q->where('manufacturer_id', request()->get('manufacturer_id'));
            });
        }

        if ($request->has('active')) {
            $assets->active();
        }

        // Apply sorting
        if ($sort === 'relevance') {
            // Add relevance score to query
            $searchTerm = Asset::parseSearchQuery($query);
            $columnString = implode(',', $columns);
            $assets = $assets->selectRaw(
                "assets.*, MATCH($columnString) AGAINST(? IN BOOLEAN MODE) as relevance_score",
                [$searchTerm]
            )->orderByDesc('relevance_score');
        } else if ($sort === 'name') {
            $assets->orderBy('name', 'asc');
        } else if ($sort === 'date') {
            $assets->orderBy('created_at', 'desc');
        }

        $results = $assets->paginate($perPage);

        // Transform results with snippets
        $results->getCollection()->transform(function ($asset) use ($query) {
            return [
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'status' => [
                    'id' => $asset->status_id,
                    'name' => $asset->status->name ?? null,
                ],
                'division' => $asset->division->name ?? null,
                'location' => $asset->location->name ?? null,
                'assigned_to' => $asset->assignedTo ? [
                    'id' => $asset->assignedTo->id,
                    'name' => $asset->assignedTo->name,
                ] : null,
                'relevance_score' => $asset->relevance_score ?? null,
                'snippet' => Asset::generateSnippet($asset->name . ' ' . ($asset->description ?? ''), $query),
            ];
        });

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
                'last_page' => $results->lastPage(),
            ]
        ]);
    }
}
