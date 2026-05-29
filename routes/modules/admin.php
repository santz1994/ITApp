<?php

/**
 * Admin & SuperAdmin Routes
 * 
 * System configuration, user management, and core admin routes
 * Uses permission-based middleware for database-driven RBAC.
 * Permissions are stored in the database and can be changed via admin dashboard.
 * 
 * Permission tags used:
 * - view_dashboard: Access main portal/dashboard
 * - view_management_dashboard: Access management/analytical dashboard
 * - view_kpi_dashboard: Access KPI dashboard
 * - manage_audit_logs: View and manage audit logs
 * - manage_notification_settings: Configure notification settings
 * - manage_users: Full user CRUD
 * - manage_roles: Role management
 * - view_admin_panel: Access admin configuration panel
 * - manage_system_settings: System settings management
 * - manage_menus: Menu management
 * - update_activity: Update activity status
 */

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    
    // ========================================
    // HOME/DASHBOARD (All authenticated users)
    // ========================================
    Route::get('/home', [\App\Http\Controllers\MainPortalController::class, 'index'])->name('home');
    Route::get('/portal', [\App\Http\Controllers\MainPortalController::class, 'index'])->name('portal.index');
    Route::redirect('/dashboard', '/home');
    
    // ========================================
    // MANAGEMENT DASHBOARD (TODO: Build per Project.md)
    // ========================================
    
    Route::middleware(['permission:manage_audit_logs'])->group(function () {
        Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{id}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::get('/audit-logs/export/csv', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('audit-logs.export');
        Route::post('/audit-logs/cleanup', [\App\Http\Controllers\AuditLogController::class, 'cleanup'])->name('audit-logs.cleanup');
    });
    
    // Notification Settings
    Route::middleware(['permission:manage_notification_settings'])->group(function () {
        Route::get('/admin/notification-settings', [\App\Http\Controllers\NotificationSettingController::class, 'index'])->name('notification-settings.index');
        Route::post('/admin/notification-settings', [\App\Http\Controllers\NotificationSettingController::class, 'update'])->name('notification-settings.update');
    });

    // ========================================
    // USER MANAGEMENT ROUTES
    // ========================================
    Route::middleware(['permission:manage_users'])->group(function () {
        
        // User Management Routes (with admin prefix)
        Route::prefix('admin/users')->group(function () {
            Route::get('/', [\App\Http\Controllers\UsersController::class, 'index'])->name('admin.users.index');
            Route::get('/create', [\App\Http\Controllers\UsersController::class, 'create'])->name('admin.users.create');
            Route::post('/', [\App\Http\Controllers\UsersController::class, 'store'])->name('admin.users.store');
            Route::get('/{user}/edit', [\App\Http\Controllers\UsersController::class, 'edit'])->name('admin.users.edit');
            Route::get('/{user}', [\App\Http\Controllers\UsersController::class, 'show'])->name('admin.users.show');
            Route::put('/{user}', [\App\Http\Controllers\UsersController::class, 'update'])->name('admin.users.update');
            Route::delete('/{user}', [\App\Http\Controllers\UsersController::class, 'destroy'])->name('admin.users.destroy');
        });

        // User Management Routes (without prefix)
        Route::prefix('users')->group(function () {
            Route::get('/', [\App\Http\Controllers\UsersController::class, 'index'])->name('users.index');
            Route::get('/create', [\App\Http\Controllers\UsersController::class, 'create'])->name('users.create');
            Route::get('/roles', [\App\Http\Controllers\UsersController::class, 'roles'])->name('users.roles');
            Route::post('/', [\App\Http\Controllers\UsersController::class, 'store'])->name('users.store');
            Route::post('/bulk-delete', [\App\Http\Controllers\UsersController::class, 'bulkDelete'])->name('users.bulk-delete');
            Route::get('/{user}/edit', [\App\Http\Controllers\UsersController::class, 'edit'])->name('users.edit');
            Route::get('/{user}', [\App\Http\Controllers\UsersController::class, 'show'])->name('users.show');
            Route::put('/{user}', [\App\Http\Controllers\UsersController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [\App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy');
        });
    });
    
    // ========================================
    // ADMIN CONFIGURATION PANEL
    // ========================================
    Route::middleware(['permission:view_admin_panel'])->group(function () {
        Route::get('/admin', [\App\Http\Controllers\PagesController::class, 'index'])->name('admin.config');
    });
    
    // ========================================
    // SYSTEM SETTINGS MANAGEMENT
    // ========================================
    Route::middleware(['permission:manage_system_settings'])->prefix('system-settings')->name('system-settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SystemSettingsController::class, 'index'])->name('index');
        Route::get('/divisions', [\App\Http\Controllers\SystemSettingsController::class, 'divisions'])->name('divisions');
    });

    // ========================================
    // MENU MANAGEMENT SYSTEM
    // ========================================
    Route::middleware(['permission:manage_menus'])->prefix('admin/menus')->name('admin.menus.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MenuManagementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\MenuManagementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\MenuManagementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\MenuManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\MenuManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\MenuManagementController::class, 'destroy'])->name('destroy');
        
        // Permission Management
        Route::get('/{id}/permissions', [\App\Http\Controllers\Admin\MenuManagementController::class, 'permissions'])->name('permissions');
        Route::post('/{id}/permissions', [\App\Http\Controllers\Admin\MenuManagementController::class, 'updatePermissions'])->name('permissions.update');
        
        // AJAX Operations
        Route::post('/update-order', [\App\Http\Controllers\Admin\MenuManagementController::class, 'updateOrder'])->name('update-order');
        Route::post('/{id}/toggle-active', [\App\Http\Controllers\Admin\MenuManagementController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/clear-cache', [\App\Http\Controllers\Admin\MenuManagementController::class, 'clearCache'])->name('clear-cache');
        
        // Utilities
        Route::get('/preview-role', [\App\Http\Controllers\Admin\MenuManagementController::class, 'previewForRole'])->name('preview-role');
        Route::post('/bulk-permissions', [\App\Http\Controllers\Admin\MenuManagementController::class, 'bulkPermissions'])->name('bulk-permissions');
    });
    
    // ========================================
    // ACTIVITY STATUS UPDATE (All authenticated users)
    // ========================================
    Route::post('/update-activity', [\App\Http\Controllers\ActivityController::class, 'updateActivity'])->name('update-activity');
});