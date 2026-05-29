<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'division']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->has('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function show(User $user)
    {
        $user->load(['roles', 'division']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'roles' => $user->getRoleNames(),
                'division' => $user->division ? ['id' => $user->division->id, 'name' => $user->division->name] : null,
            ],
        ]);
    }

    public function getPerformance(User $user, Request $request)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getWorkload(User $user)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getActivities(User $user, Request $request)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getDashboardStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending_approvals' => \App\ApprovalInstance::where('status', 'in_progress')->count(),
                'pending_vehicle_bookings' => \App\VehicleBooking::where('status', 'pending')->count(),
                'pending_inventory_requests' => \App\InventoryRequest::where('status', 'pending')->count(),
                'low_stock_items' => \App\InventoryItem::whereColumn('current_stock', '<', 'minimum_stock')->count(),
            ],
        ]);
    }

    public function getKpiData()
    {
        return response()->json(['success' => true, 'data' => []]);
    }
}
