<?php

namespace App\Services;

use App\InventoryCategory;
use App\InventoryItem;
use App\InventoryRequest;
use App\InventoryRequestItem;
use App\InventoryStockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    // ========================================
    // CATEGORY MANAGEMENT
    // ========================================

    public function getAllCategories()
    {
        return InventoryCategory::withCount('items')->orderBy('name')->get();
    }

    public function createCategory(array $data)
    {
        if (!isset($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }
        return InventoryCategory::create($data);
    }

    public function updateCategory($id, array $data)
    {
        $category = InventoryCategory::findOrFail($id);
        $category->update($data);
        return $category;
    }

    // ========================================
    // ITEM MANAGEMENT
    // ========================================

    public function getAllItems($filters = [])
    {
        $query = InventoryItem::with('category')->active();

        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }

        return $query->orderBy('name')->get();
    }

    public function getItem($id)
    {
        return InventoryItem::with(['category', 'stockMovements' => function ($query) {
            $query->with('recorder')->latest()->limit(20);
        }])->findOrFail($id);
    }

    public function createItem(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = InventoryItem::create($data);

            // If initial stock provided, record stock movement
            if (isset($data['current_stock']) && $data['current_stock'] > 0) {
                $this->recordStockMovement($item->id, 'in', $data['current_stock'], 'Stok awal', 'initial', null);
            }

            return $item;
        });
    }

    public function updateItem($id, array $data)
    {
        $item = InventoryItem::findOrFail($id);
        $item->update($data);
        return $item;
    }

    public function deleteItem($id)
    {
        $item = InventoryItem::findOrFail($id);
        return $item->delete();
    }

    // ========================================
    // STOCK MANAGEMENT
    // ========================================

    public function addStock($itemId, $quantity, $notes = null)
    {
        return DB::transaction(function () use ($itemId, $quantity, $notes) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);
            $stockBefore = $item->current_stock;
            $item->update(['current_stock' => $stockBefore + $quantity]);

            return $this->recordStockMovement($itemId, 'in', $quantity, $notes ?? 'Stok masuk manual', 'manual', null);
        });
    }

    public function reduceStock($itemId, $quantity, $referenceType = null, $referenceId = null, $notes = null)
    {
        return DB::transaction(function () use ($itemId, $quantity, $referenceType, $referenceId, $notes) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);

            if ($item->current_stock < $quantity) {
                throw new \Exception("Stok tidak mencukupi. Stok saat ini: {$item->current_stock}, diminta: {$quantity}");
            }

            $stockBefore = $item->current_stock;
            $item->update(['current_stock' => $stockBefore - $quantity]);

            return $this->recordStockMovement($itemId, 'out', $quantity, $notes ?? 'Stok keluar', $referenceType, $referenceId);
        });
    }

    public function adjustStock($itemId, $newQuantity, $notes = null)
    {
        return DB::transaction(function () use ($itemId, $newQuantity, $notes) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);
            $stockBefore = $item->current_stock;
            $item->update(['current_stock' => $newQuantity]);

            return $this->recordStockMovement($itemId, 'adjustment', $newQuantity - $stockBefore, $notes ?? 'Penyesuaian stok', 'adjustment', null);
        });
    }

    protected function recordStockMovement($itemId, $type, $quantity, $notes, $referenceType = null, $referenceId = null)
    {
        $item = InventoryItem::findOrFail($itemId);
        $stockBefore = $item->current_stock - ($type === 'in' ? $quantity : ($type === 'out' ? -$quantity : 0));
        $stockAfter = $item->current_stock;

        return InventoryStockMovement::create([
            'item_id' => $itemId,
            'type' => $type,
            'quantity' => abs($quantity),
            'stock_before' => $type === 'out' ? ($stockBefore + $quantity) : ($stockBefore),
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'recorded_by' => Auth::id(),
        ]);
    }

    public function getStockMovements($itemId, $limit = 50)
    {
        return InventoryStockMovement::where('item_id', $itemId)
            ->with('recorder')
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ========================================
    // INVENTORY REQUESTS
    // ========================================

    public function createRequest(array $data)
    {
        return DB::transaction(function () use ($data) {
            $request = InventoryRequest::create([
                'request_number' => InventoryRequest::generateRequestNumber(),
                'requested_by' => Auth::id(),
                'department_id' => $data['department_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $itemData) {
                InventoryRequestItem::create([
                    'request_id' => $request->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity_requested'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $request->load('items.item');
        });
    }

    public function getAllRequests($filters = [])
    {
        $query = InventoryRequest::with(['requester', 'department', 'items.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['my_requests'])) {
            $query->where('requested_by', Auth::id());
        }

        return $query->latest()->get();
    }

    public function getRequest($id)
    {
        return InventoryRequest::with(['requester', 'approver', 'department', 'items.item.category'])
            ->findOrFail($id);
    }

    public function approveRequest($id, $approverId, $approvedItems = [])
    {
        return DB::transaction(function () use ($id, $approverId, $approvedItems) {
            $request = InventoryRequest::lockForUpdate()->findOrFail($id);

            if (!$request->isPending()) {
                throw new \Exception('Request tidak dapat disetujui. Status: ' . $request->status);
            }

            // Update approved quantities for each item
            if (!empty($approvedItems)) {
                foreach ($approvedItems as $itemId => $approvedQty) {
                    $requestItem = InventoryRequestItem::where('request_id', $id)
                        ->where('item_id', $itemId)
                        ->first();
                    if ($requestItem) {
                        $requestItem->update(['quantity_approved' => $approvedQty]);
                    }
                }
            }

            $request->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            return $request->load('items.item');
        });
    }

    public function rejectRequest($id, $approverId, $reason = null)
    {
        return DB::transaction(function () use ($id, $approverId, $reason) {
            $request = InventoryRequest::findOrFail($id);

            if (!$request->isPending()) {
                throw new \Exception('Request tidak dapat ditolak. Status: ' . $request->status);
            }

            $request->update([
                'status' => 'rejected',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $request;
        });
    }

    public function fulfillRequest($id, array $fulfilledItems)
    {
        return DB::transaction(function () use ($id, $fulfilledItems) {
            $request = InventoryRequest::lockForUpdate()->findOrFail($id);

            if (!$request->isApproved()) {
                throw new \Exception('Request belum disetujui. Status: ' . $request->status);
            }

            foreach ($fulfilledItems as $itemId => $fulfilledQty) {
                $requestItem = InventoryRequestItem::where('request_id', $id)
                    ->where('item_id', $itemId)
                    ->first();

                if ($requestItem && $fulfilledQty > 0) {
                    // Reduce stock
                    $this->reduceStock($itemId, $fulfilledQty, 'inventory_request', $id, "Pemenuhan request {$request->request_number}");

                    // Update fulfilled quantity
                    $requestItem->update([
                        'quantity_fulfilled' => $requestItem->quantity_fulfilled + $fulfilledQty,
                    ]);
                }
            }

            // Check if all items are fully fulfilled
            $allFulfilled = $request->items->every(function ($item) {
                return $item->isFullyFulfilled();
            });

            $anyFulfilled = $request->items->sum('quantity_fulfilled') > 0;

            $request->update([
                'status' => $allFulfilled ? 'fulfilled' : ($anyFulfilled ? 'partially_fulfilled' : 'approved'),
            ]);

            return $request->load('items.item');
        });
    }

    public function cancelRequest($id)
    {
        $request = InventoryRequest::findOrFail($id);

        if (!$request->canBeCancelled()) {
            throw new \Exception('Request tidak dapat dibatalkan. Status: ' . $request->status);
        }

        $request->update(['status' => 'cancelled']);
        return $request;
    }

    // ========================================
    // REPORTING & DASHBOARD
    // ========================================

    public function getLowStockItems()
    {
        return InventoryItem::lowStock()->with('category')->orderBy('current_stock')->get();
    }

    public function getDashboardStats()
    {
        return [
            'total_items' => InventoryItem::active()->count(),
            'total_categories' => InventoryCategory::active()->count(),
            'low_stock_items' => InventoryItem::lowStock()->count(),
            'pending_requests' => InventoryRequest::pending()->count(),
            'total_stock_value' => InventoryItem::active()->get()->sum('total_value'),
        ];
    }

    public function getItemUsageReport($itemId, $startDate = null, $endDate = null)
    {
        $query = InventoryStockMovement::where('item_id', $itemId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $movements = $query->get();

        return [
            'total_incoming' => $movements->where('type', 'in')->sum('quantity'),
            'total_outgoing' => $movements->where('type', 'out')->sum('quantity'),
            'total_adjustments' => $movements->where('type', 'adjustment')->sum('quantity'),
            'movements' => $movements,
        ];
    }
}