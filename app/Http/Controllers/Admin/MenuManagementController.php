<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Menu;
use App\Role;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuManagementController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
        
        // Only Super Admin can manage menus
        $this->middleware('permission:manage-menus');
    }

    /**
     * Display menu management interface
     */
    public function index()
    {
        // Get all menus with their children relationships loaded
        $menus = Menu::with('children.children.children')
                    ->whereNull('parent_id')
                    ->ordered()
                    ->get();
        
        $totalMenus = Menu::count();
        $activeMenus = Menu::where('is_active', true)->count();
        
        return view('admin.menus.index', compact('menus', 'totalMenus', 'activeMenus'));
    }

    /**
     * Show form to create new menu
     */
    public function create()
    {
        $parentMenus = Menu::whereNull('parent_id')
                          ->active()
                          ->ordered()
                          ->get();
        
        return view('admin.menus.create', compact('parentMenus'));
    }

    /**
     * Store new menu
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:menus,id',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_external' => 'boolean',
            'target' => 'nullable|in:_self,_blank,_parent,_top',
            'css_class' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Either route or URL must be provided
        if (!$request->route && !$request->url) {
            return back()->withErrors([
                'route' => 'Either Route Name or URL must be provided'
            ])->withInput();
        }

        $menu = $this->menuService->createMenu($request->all());

        return redirect()->route('admin.menus.index')
                        ->with('success', 'Menu item created successfully!');
    }

    /**
     * Show form to edit menu
     */
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $parentMenus = Menu::whereNull('parent_id')
                          ->where('id', '!=', $id) // Can't be parent of itself
                          ->active()
                          ->ordered()
                          ->get();
        
        return view('admin.menus.edit', compact('menu', 'parentMenus'));
    }

    /**
     * Update menu
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:menus,id',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_external' => 'boolean',
            'target' => 'nullable|in:_self,_blank,_parent,_top',
            'css_class' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for circular reference (menu can't be its own parent)
        if ($request->parent_id == $id) {
            return back()->withErrors([
                'parent_id' => 'Menu cannot be its own parent'
            ])->withInput();
        }

        $menu = $this->menuService->updateMenu($id, $request->all());

        return redirect()->route('admin.menus.index')
                        ->with('success', 'Menu item updated successfully!');
    }

    /**
     * Delete menu
     */
    public function destroy($id)
    {
        try {
            $this->menuService->deleteMenu($id);
            
            return redirect()->route('admin.menus.index')
                           ->with('success', 'Menu item deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete menu: ' . $e->getMessage());
        }
    }

    /**
     * Update menu order via AJAX (drag & drop)
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id',
            'parent_id' => 'nullable|exists:menus,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->menuService->updateMenuOrder(
                $request->menu_ids,
                $request->parent_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Menu order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu order'
            ], 500);
        }
    }

    /**
     * Show permission management for a menu
     */
    public function permissions($id)
    {
        $menu = Menu::with(['roles', 'parent'])->findOrFail($id);
        $allRoles = Role::query()->canonical()->orderBy('name')->get();
        $permissionMatrix = $this->menuService->getMenuPermissionMatrix($id);
        
        return view('admin.menus.permissions', compact('menu', 'allRoles', 'permissionMatrix'));
    }

    /**
     * Update menu permissions for roles
     */
    public function updatePermissions(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $menu = Menu::findOrFail($id);
            
            // Sync permissions
            $roleIds = $request->input('role_ids', []);
            $syncData = [];
            
            foreach ($roleIds as $roleId) {
                $syncData[$roleId] = ['can_view' => true];
            }
            
            $menu->roles()->sync($syncData);
            
            // Clear all menu caches
            $this->menuService->clearAllCache();

            return redirect()->route('admin.menus.permissions', $id)
                           ->with('success', 'Menu permissions updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update permissions: ' . $e->getMessage());
        }
    }

    /**
     * Bulk permission assignment
     */
    public function bulkPermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'action' => 'required|in:grant,revoke',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            foreach ($request->role_ids as $roleId) {
                if ($request->action === 'grant') {
                    $this->menuService->syncRolePermissions($roleId, $request->menu_ids);
                } else {
                    // Revoke - remove menu permissions
                    $role = Role::findOrFail($roleId);
                    $role->menus()->detach($request->menu_ids);
                }
            }

            $this->menuService->clearAllCache();

            return back()->with('success', 'Bulk permissions updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update bulk permissions: ' . $e->getMessage());
        }
    }

    /**
     * Toggle menu active status
     */
    public function toggleActive($id)
    {
        try {
            $menu = Menu::findOrFail($id);
            $menu->is_active = !$menu->is_active;
            $menu->save();

            $this->menuService->clearAllCache();

            return response()->json([
                'success' => true,
                'is_active' => $menu->is_active,
                'message' => 'Menu status updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Preview menu for a specific role
     */
    public function previewForRole(Request $request)
    {
        $roleId = $request->get('role_id');
        
        if (!$roleId) {
            return back()->with('error', 'Please select a role');
        }

        $role = Role::findOrFail($roleId);
        
        // Get a test user with this role (or create mock user)
        $testUser = User::role($role->name)->first();
        
        if (!$testUser) {
            return back()->with('error', 'No users found with this role');
        }

        $menus = $this->menuService->getMenusForUser($testUser, false);
        
        return view('admin.menus.preview', compact('menus', 'role'));
    }

    /**
     * Clear all menu cache
     */
    public function clearCache()
    {
        try {
            $this->menuService->clearAllCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Menu cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
