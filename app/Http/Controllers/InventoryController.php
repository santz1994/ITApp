<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Inventory\{
    ChangeAssetStatusRequest,
    ApproveAssetRequestRequest,
    RejectAssetRequestRequest,
    FulfillAssetRequestRequest
};
use App\Asset;
use App\AssetType;
use App\Status;
use App\Location;
use App\Division;
use App\AssetRequest;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrator|developer|director');
    }

    /**
     * Enhanced inventory management dashboard
     */
    public function index(Request $request)
    {
        // Build query with filters
        $query = Asset::with(['assetModel.assetType', 'status', 'division', 'assignedUser', 'movement.location']);

        // Apply filters
        if ($request->filled('category')) {
            $query->whereHas('assetModel.assetType', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }

        if ($request->filled('location')) {
            $query->whereHas('movement.location', function($q) use ($request) {
                $q->where('id', $request->location);
            });
        }

        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->paginate(25);

        // Get filter options
        $categories = \App\Services\CacheService::getAssetTypes();
        $statuses = \App\Services\CacheService::getStatuses();
        $locations = \App\Services\CacheService::getLocations();
        $divisions = \App\Services\CacheService::getDivisions();

        // Statistics
        $stats = [
            'total_assets' => Asset::count(),
            'active_assets' => Asset::whereHas('status', function($q) {
                $q->where('name', 'Active');
            })->count(),
            'maintenance_assets' => Asset::whereHas('status', function($q) {
                $q->where('name', 'like', '%repair%');
            })->count(),
            'pending_requests' => AssetRequest::where('status', 'pending')->count()
        ];

        // Category statistics
        $categoryStats = $this->getCategoryStatistics();
        $pageTitle = 'Enhanced Inventory Management';

        return view('inventory.index', compact(
            'assets', 'categories', 'statuses', 'locations', 'divisions', 
            'stats', 'categoryStats', 'pageTitle'
        ));
    }

    /**
     * Change asset status
     */
    public function changeStatus(ChangeAssetStatusRequest $request, Asset $asset)
    {
        // Map status names to status IDs (you might need to adjust these based on your status table)
        $statusMapping = [
            'active' => 1,      // Assuming Active status has ID 1
            'maintenance' => 2, // Assuming In Repair status has ID 2
            'retired' => 3      // Assuming Retired status has ID 3
        ];

        $statusId = $statusMapping[$request->status] ?? 1;

        $asset->update([
            'status_id' => $statusId
        ]);

        // Log activity
        if (method_exists($asset, 'logMaintenanceActivity')) {
            $statusName = ucfirst($request->status);
            $asset->logMaintenanceActivity("Status changed to: {$statusName}", Auth::id());
        }

        return redirect()->back()->with('success', 'Asset status updated successfully!');
    }

    /**
     * Asset categories overview
     */
    public function categories()
    {
        $categories = AssetType::withCount('assets')->get();
        $totalAssets = Asset::count();

        $categoryData = $categories->map(function($category) use ($totalAssets) {
            return [
                'id' => $category->id,
                'name' => $category->type_name,
                'count' => $category->assets_count,
                'percentage' => $totalAssets > 0 ? round(($category->assets_count / $totalAssets) * 100, 1) : 0,
                'icon' => $this->getCategoryIcon($category->type_name)
            ];
        });

        return view('inventory.categories', compact('categoryData', 'totalAssets'));
    }

    /**
     * Asset requests workflow
     */
    public function requests()
    {
        $requests = AssetRequest::with(['requestedBy', 'assetType', 'approvedBy', 'fulfilledAsset'])
                               ->orderBy('created_at', 'desc')
                               ->paginate(20);

        return view('inventory.requests', compact('requests'));
    }

    /**
     * Approve asset request
     */
    public function approveRequest(ApproveAssetRequestRequest $request, AssetRequest $assetRequest)
    {

        $assetRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes
        ]);

        return redirect()->back()->with('success', 'Asset request approved successfully!');
    }

    /**
     * Reject asset request
     */
    public function rejectRequest(RejectAssetRequestRequest $request, AssetRequest $assetRequest)
    {

        $assetRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes
        ]);

        return redirect()->back()->with('success', 'Asset request rejected.');
    }

    /**
     * Fulfill asset request
     */
    public function fulfillRequest(FulfillAssetRequestRequest $request, AssetRequest $assetRequest)
    {

        $assetRequest->update([
            'status' => 'fulfilled',
            'fulfilled_asset_id' => $request->asset_id,
            'fulfilled_at' => now()
        ]);

        // Update asset assignment
        $asset = Asset::find($request->asset_id);
        $asset->update([
            'assigned_to' => $assetRequest->requested_by,
            'status_id' => 1 // Active
        ]);

        return redirect()->back()->with('success', 'Asset request fulfilled successfully!');
    }

    /**
     * Get category statistics
     */
    private function getCategoryStatistics()
    {
        $categories = AssetType::withCount('assets')->get();
        $totalAssets = Asset::count();

        return $categories->map(function($category) use ($totalAssets) {
            return [
                'name' => $category->type_name,
                'count' => $category->assets_count,
                'percentage' => $totalAssets > 0 ? round(($category->assets_count / $totalAssets) * 100, 1) : 0,
                'icon' => $this->getCategoryIcon($category->type_name)
            ];
        });
    }

    /**
     * Get icon for category
     */
    private function getCategoryIcon($typeName)
    {
        $icons = [
            'Desktop' => 'desktop',
            'Laptop' => 'laptop',
            'Server' => 'server',
            'Printer' => 'print',
            'Network' => 'wifi',
            'Mobile' => 'mobile',
            'Tablet' => 'tablet',
            'Monitor' => 'tv',
            'Keyboard' => 'keyboard-o',
            'Mouse' => 'mouse-pointer'
        ];

        return $icons[$typeName] ?? 'cube';
    }
}