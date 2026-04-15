<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminSecurityMiddleware
{
    /**
     * Handle an incoming request for admin security
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $level  'edit' for edit operations, null for general access
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $level = null)
    {
        $user = auth()->user();
        
        // Check if user has super-admin role
        if (!$user->hasRole('super-admin')) {
            // For AJAX/JSON requests, return JSON error
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Super admin privileges required.'
                ], 403);
            }
            
            return redirect()->route('home')
                ->with('error', 'Access denied. Super admin privileges required.');
        }
        
        // For edit-level operations, restrict to daniel@quty.co.id
        if ($level === 'edit') {
            if ($user->email !== 'daniel@quty.co.id') {
                // For AJAX/JSON requests, return JSON error
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Only daniel@quty.co.id can perform administrative modifications.'
                    ], 403);
                }
                
                return redirect()->back()
                    ->with('error', 'Access denied. Only daniel@quty.co.id can perform administrative modifications.');
            }
            
            // Check if password confirmation is required for sensitive operations
            $sensitiveActions = [
                'store', 'update', 'destroy', 'delete', 'truncate', 
                'create', 'edit', 'databaseAction', 'databaseDanger',
                'processDelete', 'bulkAction'
            ];
            
            $currentAction = $request->route()->getActionMethod();
            $isPost = in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH']);
            
            if (in_array($currentAction, $sensitiveActions) || $isPost) {
                // Check password confirmation session
                if (!session('admin_password_confirmed') || 
                    session('admin_password_confirmed') < now()->subMinutes(30)) {
                    
                    // For AJAX/JSON requests, return JSON error instead of redirect
                    if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Password confirmation required. Please refresh the page and confirm your password.',
                            'requires_auth' => true,
                            'auth_url' => route('admin.authenticate', [
                                'intended' => $request->fullUrl(),
                                'action' => $currentAction,
                                'module' => $this->getModuleName($request)
                            ])
                        ], 403);
                    }
                    
                    return redirect()->route('admin.authenticate', [
                        'intended' => $request->fullUrl(),
                        'action' => $currentAction,
                        'module' => $this->getModuleName($request)
                    ]);
                }
            }
        }
        
        // Log admin access
        Log::info('Admin access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'route' => $request->route()->getName(),
            'action' => $request->route()->getActionMethod(),
            'ip' => $request->ip(),
            'level' => $level
        ]);
        
        return $next($request);
    }
    
    /**
     * Determine the module name from the request
     */
    private function getModuleName($request)
    {
        $route = $request->route()->getName();
        
        if (strpos($route, 'database') !== false) {
            return 'Database Management';
        } elseif (strpos($route, 'users') !== false) {
            return 'User Management';
        } elseif (strpos($route, 'roles') !== false) {
            return 'Role Management';
        } elseif (strpos($route, 'permissions') !== false) {
            return 'Permission Management';
        } elseif (strpos($route, 'system') !== false) {
            return 'System Settings';
        } else {
            return 'Admin Panel';
        }
    }
}