<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'division_id' => 'nullable|exists:divisions,id',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if (!empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $user->assignRole($role);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load(['roles', 'division']),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'division_id' => 'nullable|exists:divisions,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if (!empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $user->syncRoles([$role]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh()->load(['roles', 'division']),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account'], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No user IDs provided'], 400);
        }

        $deleted = User::whereIn('id', $ids)->where('id', '!=', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} users deleted successfully",
            'deleted' => $deleted,
        ]);
    }

    public function roles(): JsonResponse
    {
        $roles = Role::query()
            ->canonical()
            ->with(['permissions'])
            ->get()
            ->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name'),
            ]);

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }
}
