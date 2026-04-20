<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SystemService;
use App\Services\PermissionService;
use App\Services\LogService;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SystemController extends Controller
{
    public function __construct(
        private SystemService $systemService,
        private PermissionService $permissionService,
        private LogService $logService,
        private RoleService $roleService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show system settings page
     */
    public function settings(): View
    {
        $systemInfo = $this->systemService->getSystemInfo();
        return view('system.settings', compact('systemInfo'));
    }

    /**
     * Show permissions management page
     */
    public function permissions(): View
    {
        $permissions = $this->permissionService->getAllPermissions();
        $roles = Role::query()
            ->whereIn('name', \App\Role::canonicalNames())
            ->with('permissions')
            ->withCount('permissions')
            ->get();
        
        return view('system.permissions', compact('permissions', 'roles'));
    }

    /**
     * Show roles management page
     */
    public function roles(): View
    {
        $this->authorize('edit-settings');
        
        $roles = $this->roleService->getAllRoles();
        $permissions = Permission::all();
        $users = User::with('roles')->get();
        
        return view('system.roles', compact('roles', 'permissions', 'users'));
    }

    /**
     * Show system maintenance page
     */
    public function maintenance(): View
    {
        $this->authorize('edit-settings');
        
        $diskUsage = $this->systemService->getDiskUsage();
        $cacheInfo = $this->systemService->getCacheInfo();
        $logInfo = $this->systemService->getLogInfo();
        
        return view('system.maintenance', compact('diskUsage', 'cacheInfo', 'logInfo'));
    }

    /**
     * Show system logs
     */
    public function logs(Request $request): View
    {
        $this->authorize('view-system-settings');
        
        $requestedFile = $request->get('file', 'laravel.log');
        
        $filters = [
            'search' => $request->get('search'),
            'level' => $request->get('level'),
            'date' => $request->get('date'),
        ];
        
        $result = $this->logService->getLogEntries($requestedFile, $filters);
        $log_files = $this->logService->getLogFiles();
        
        return view('system.logs', [
            'logs' => $result['entries'],
            'stats' => $result['stats'],
            'log_files' => $log_files
        ]);
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->clearCache($request->input('type', 'all'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new permission
     */
    public function createPermission(Request $request): RedirectResponse
    {
        $this->authorize('edit-settings');
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'required|string|in:web,api'
        ]);
        
        try {
            $permission = $this->permissionService->createPermission($request->all());
            
            return redirect()->route('system.permissions')
                ->with('success', "Permission '{$permission->name}' created successfully");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating permission: ' . $e->getMessage());
        }
    }

    /**
     * Assign permission to role
     */
    public function assignPermission(Request $request): RedirectResponse
    {
        $this->authorize('edit-settings');
        
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id'
        ]);
        
        try {
            $this->permissionService->assignPermissionToRole(
                $request->role_id,
                $request->permission_id
            );
            
            $role = Role::findById($request->role_id);
            $permission = Permission::findById($request->permission_id);
            
            return redirect()->route('system.permissions')
                ->with('success', "Permission '{$permission->name}' assigned to role '{$role->name}'");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error assigning permission: ' . $e->getMessage());
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermission(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id'
        ]);
        
        try {
            $this->permissionService->removePermissionFromRole(
                $request->role_id,
                $request->permission_id
            );
            
            $role = Role::findById($request->role_id);
            $permission = Permission::findById($request->permission_id);
            
            return response()->json([
                'success' => true,
                'message' => "Permission '{$permission->name}' removed from role '{$role->name}'"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permission details
     */
    public function getPermission($id): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $permission = $this->permissionService->getPermission($id);
            
            return response()->json([
                'success' => true,
                'permission' => $permission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update permission
     */
    public function updatePermission(Request $request, $id): JsonResponse
    {
        $this->authorize('edit-settings');
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'guard_name' => 'required|string|in:web,api',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);
        
        try {
            $permission = $this->permissionService->updatePermission($id, $request->all());
            
            // Handle role assignments if provided
            $rolesUpdated = false;
            $rolesAdded = [];
            $rolesRemoved = [];
            
            if ($request->has('roles')) {
                $currentRoleIds = $permission->roles->pluck('id')->toArray();
                $newRoleIds = array_map('intval', $request->roles);
                
                $toAdd = array_diff($newRoleIds, $currentRoleIds);
                $toRemove = array_diff($currentRoleIds, $newRoleIds);
                
                if (!empty($toAdd) || !empty($toRemove)) {
                    $rolesUpdated = true;
                    
                    foreach ($toRemove as $roleId) {
                        $this->permissionService->removePermissionFromRole($roleId, $id);
                        $rolesRemoved[] = Role::findById($roleId)->name;
                    }
                    
                    foreach ($toAdd as $roleId) {
                        $this->permissionService->assignPermissionToRole($roleId, $id);
                        $rolesAdded[] = Role::findById($roleId)->name;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Permission '{$permission->name}' updated successfully",
                'roles_updated' => $rolesUpdated,
                'roles_added' => $rolesAdded,
                'roles_removed' => $rolesRemoved
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete permission
     */
    public function deletePermission($id): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $permission = Permission::findOrFail($id);
            
            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission that is assigned to roles. Remove from roles first.'
                ], 400);
            }
            
            $permissionName = $permission->name;
            $this->permissionService->deletePermission($id);
            
            return response()->json([
                'success' => true,
                'message' => "Permission '{$permissionName}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear temporary files
     */
    public function clearTemp(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->clearTempFiles();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing temp files: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear old uploads (older than 90 days)
     */
    public function clearUploads(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->clearOldUploads(90);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing uploads: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get queue status
     */
    public function queueStatus(Request $request): JsonResponse
    {
        $this->authorize('view-system-settings');
        
        try {
            $result = $this->systemService->getQueueStatus();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting queue status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize database tables
     */
    public function optimizeDatabase(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->optimizeDatabase();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error optimizing database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run database migrations
     */
    public function runMigrations(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->runMigrations();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running migrations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart queue workers
     */
    public function restartQueue(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->restartQueue();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restarting queue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all queued jobs
     */
    public function clearQueue(Request $request): JsonResponse
    {
        $this->authorize('edit-settings');
        
        try {
            $result = $this->systemService->clearQueue();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing queue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear failed jobs (delegates to clearQueue)
     */
    public function clearFailedJobs(Request $request): JsonResponse
    {
        return $this->clearQueue($request);
    }

    /**
     * Run system health check
     */
    public function healthCheck(Request $request): JsonResponse
    {
        $this->authorize('view-system-settings');
        
        try {
            $result = $this->systemService->healthCheck();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running health check: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role
     */
    public function storeRole(\App\Http\Requests\CreateRoleRequest $request): RedirectResponse
    {
        try {
            $role = $this->roleService->createRole($request->all());

            return redirect()->route('system.roles')
                ->with('success', "Role \"{$role->display_name}\" created successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating role: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified role
     */
    public function updateRole(\App\Http\Requests\UpdateRoleRequest $request, \App\Role $role): RedirectResponse
    {
        try {
            $updatedRole = $this->roleService->updateRole($role, $request->all());

            return redirect()->route('system.roles')
                ->with('success', "Role \"{$updatedRole->display_name}\" updated successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified role
     */
    public function deleteRole(\App\Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        try {
            $result = $this->roleService->deleteRole($role);
            
            if ($result['success']) {
                return redirect()->route('system.roles')
                    ->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting role: ' . $e->getMessage());
        }
    }
    
    /**
     * Get role data for editing (AJAX endpoint)
     */
    public function editRole(\App\Role $role): JsonResponse
    {
        $this->authorize('update', $role);
        
        $roleData = $this->roleService->getRole($role);
        
        return response()->json([
            'id' => $roleData->id,
            'name' => $roleData->name,
            'display_name' => $roleData->display_name,
            'description' => $roleData->description,
            'permissions' => $roleData->permissions
        ]);
    }

    /**
     * Clear system logs
     */
    public function clearLogs(Request $request)
    {
        $this->authorize('edit-settings');
        
        try {
            $logFile = $request->input('file', 'laravel.log');
            $result = $this->logService->clearLogFile($logFile);
            
            // If AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($result);
            }
            
            // Otherwise redirect back with flash message
            if ($result['success']) {
                return redirect()->route('system.logs', ['file' => $logFile])
                    ->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing logs: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error clearing logs: ' . $e->getMessage());
        }
    }

    /**
     * Download system logs
     */
    public function downloadLogs(Request $request)
    {
        $this->authorize('view-system-settings');
        
        try {
            $logFile = $request->input('file', 'laravel.log');
            $logPath = storage_path('logs/' . $logFile);
            
            if (!file_exists($logPath)) {
                return redirect()->back()->with('error', 'Log file not found');
            }
            
            return response()->download($logPath, $logFile, [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error downloading logs: ' . $e->getMessage());
        }
    }
}
