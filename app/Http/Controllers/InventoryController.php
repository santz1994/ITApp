<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
        $this->middleware('auth');
    }

    // ========================================
    // ITEMS MANAGEMENT
    // ========================================

    public function index(Request $request)
    {
        $filters = $request->only(['category_id', 'search', 'low_stock']);
        $items = $this->inventoryService->getAllItems($filters);
        $categories = $this->inventoryService->getAllCategories();
        $dashboardStats = $this->inventoryService->getDashboardStats();

        return view('inventory.index', compact('items', 'categories', 'filters', 'dashboardStats'));
    }

    public function create()
    {
        $categories = $this->inventoryService->getAllCategories();
        return view('inventory.create', compact('categories'));
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
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('inventory', 'public');
        }

        $this->inventoryService->createItem($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Barang inventaris berhasil ditambahkan.');
    }

    public function show($id)
    {
        $item = $this->inventoryService->getItem($id);
        $stockMovements = $this->inventoryService->getStockMovements($id);

        return view('inventory.show', compact('item', 'stockMovements'));
    }

    public function edit($id)
    {
        $item = $this->inventoryService->getItem($id);
        $categories = $this->inventoryService->getAllCategories();

        return view('inventory.edit', compact('item', 'categories'));
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
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('inventory', 'public');
        }

        $this->inventoryService->updateItem($id, $validated);

        return redirect()->route('inventory.show', $id)
            ->with('success', 'Barang inventaris berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->inventoryService->deleteItem($id);

        return redirect()->route('inventory.index')
            ->with('success', 'Barang inventaris berhasil dihapus.');
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

        $this->inventoryService->addStock($id, $validated['quantity'], $validated['notes'] ?? null);

        return redirect()->route('inventory.show', $id)
            ->with('success', 'Stok berhasil ditambahkan.');
    }

    public function reduceStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $this->inventoryService->reduceStock($id, $validated['quantity'], null, null, $validated['notes'] ?? null);

            return redirect()->route('inventory.show', $id)
                ->with('success', 'Stok berhasil dikurangi.');
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }
    }

    // ========================================
    // INVENTORY REQUESTS
    // ========================================

    public function requests(Request $request)
    {
        $filters = $request->only(['status', 'my_requests']);
        $inventoryRequests = $this->inventoryService->getAllRequests($filters);

        return view('inventory.requests', compact('inventoryRequests', 'filters'));
    }

    public function createRequest()
    {
        $items = $this->inventoryService->getAllItems();
        return view('inventory.create-request', compact('items'));
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

            return redirect()->route('inventory.request.show', $inventoryRequest->id)
                ->with('success', 'Permintaan inventaris berhasil diajukan. Menunggu persetujuan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function showRequest($id)
    {
        $inventoryRequest = $this->inventoryService->getRequest($id);
        return view('inventory.request-detail', compact('inventoryRequest'));
    }

    public function approveRequest(Request $request, $id)
    {
        try {
            $inventoryRequest = $this->inventoryService->approveRequest($id, Auth::id());

            return redirect()->route('inventory.request.show', $id)
                ->with('success', 'Permintaan inventaris berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rejectRequest(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->inventoryService->rejectRequest($id, Auth::id(), $request->rejection_reason);

            return redirect()->route('inventory.request.show', $id)
                ->with('success', 'Permintaan inventaris berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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

            return redirect()->route('inventory.request.show', $id)
                ->with('success', 'Permintaan inventaris berhasil dipenuhi.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancelRequest($id)
    {
        try {
            $this->inventoryService->cancelRequest($id);

            return redirect()->route('inventory.request.show', $id)
                ->with('success', 'Permintaan inventaris berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ========================================
    // LOW STOCK ALERT
    // ========================================

    public function lowStock()
    {
        $items = $this->inventoryService->getLowStockItems();
        return view('inventory.low-stock', compact('items'));
    }
}