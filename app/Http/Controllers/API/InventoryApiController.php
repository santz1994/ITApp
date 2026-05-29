<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryApiController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    // ========================================
    // ITEMS
    // ========================================

    public function index(Request $request)
    {
        $filters = $request->only(['category_id', 'search', 'low_stock']);
        $items = $this->inventoryService->getAllItems($filters);
        $categories = $this->inventoryService->getAllCategories();
        $dashboardStats = $this->inventoryService->getDashboardStats();

        return response()->json([
            'success' => true,
            'data' => $items,
            'categories' => $categories,
            'stats' => $dashboardStats,
        ]);
    }

    public function show($id)
    {
        $item = $this->inventoryService->getItem($id);
        $stockMovements = $this->inventoryService->getStockMovements($id);

        return response()->json([
            'success' => true,
            'data' => $item,
            'stock_movements' => $stockMovements,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:inventory_items,sku',
            'description' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:20',
            'current_stock' => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $item = $this->inventoryService->createItem($validated);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan.',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:inventory_items,sku,' . $id,
            'description' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:20',
            'minimum_stock' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $item = $this->inventoryService->updateItem($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui.',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        $this->inventoryService->deleteItem($id);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus.',
        ]);
    }

    // ========================================
    // STOCK MANAGEMENT
    // ========================================

    public function addStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $movement = $this->inventoryService->addStock($id, $validated['quantity'], $validated['notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil ditambahkan.',
            'data' => $movement,
        ]);
    }

    public function reduceStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $movement = $this->inventoryService->reduceStock($id, $validated['quantity'], null, null, $validated['notes'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil dikurangi.',
                'data' => $movement,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ========================================
    // INVENTORY REQUESTS
    // ========================================

    public function requests(Request $request)
    {
        $filters = $request->only(['status', 'my_requests']);
        $inventoryRequests = $this->inventoryService->getAllRequests($filters);

        return response()->json([
            'success' => true,
            'data' => $inventoryRequests,
        ]);
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:divisions,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        try {
            $inventoryRequest = $this->inventoryService->createRequest($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan inventaris berhasil diajukan.',
                'data' => $inventoryRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function showRequest($id)
    {
        $inventoryRequest = $this->inventoryService->getRequest($id);

        return response()->json([
            'success' => true,
            'data' => $inventoryRequest,
        ]);
    }

    public function approveRequest(Request $request, $id)
    {
        try {
            $inventoryRequest = $this->inventoryService->approveRequest($id, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil disetujui.',
                'data' => $inventoryRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function rejectRequest(Request $request, $id)
    {
        try {
            $this->inventoryService->rejectRequest($id, Auth::id(), $request->rejection_reason);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil ditolak.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function fulfillRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'fulfilled_items' => 'required|array',
            'fulfilled_items.*' => 'integer|min:0',
        ]);

        try {
            $this->inventoryService->fulfillRequest($id, $validated['fulfilled_items']);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil dipenuhi.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancelRequest($id)
    {
        try {
            $this->inventoryService->cancelRequest($id);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil dibatalkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ========================================
    // LOW STOCK
    // ========================================

    public function lowStock()
    {
        $items = $this->inventoryService->getLowStockItems();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    // ========================================
    // CATEGORIES
    // ========================================

    public function categories()
    {
        $categories = $this->inventoryService->getAllCategories();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}