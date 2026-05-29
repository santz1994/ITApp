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
 * - manage_system: System management (cache, logs, queue, database)
 * - manage_admin_tools: Admin tools (backup, database, cache)
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
    // SYSTEM MANAGEMENT ROUTES
    // ========================================
    Route::middleware(['permission:manage_system'])->prefix('system')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\SystemController::class, 'settings'])->name('system.settings');
        Route::get('/permissions', [\App\Http\Controllers\SystemController::class, 'permissions'])->name('system.permissions');
        Route::get('/roles', [\App\Http\Controllers\SystemController::class, 'roles'])->name('system.roles');
        Route::get('/maintenance', [\App\Http\Controllers\SystemController::class, 'maintenance'])->name('system.maintenance');
        Route::get('/logs', [\App\Http\Controllers\SystemController::class, 'logs'])->name('system.logs');
        
        // Role management routes
        Route::post('/roles', [\App\Http\Controllers\SystemController::class, 'storeRole'])->name('system.roles.store');
        Route::get('/roles/{role}/edit', [\App\Http\Controllers\SystemController::class, 'editRole'])->name('system.roles.edit');
        Route::put('/roles/{role}', [\App\Http\Controllers\SystemController::class, 'updateRole'])->name('system.roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\SystemController::class, 'deleteRole'])->name('system.roles.delete');
        
        // AJAX endpoints for system management
        Route::post('/cache/clear', [\App\Http\Controllers\SystemController::class, 'clearCache'])->name('system.cache.clear');
        Route::post('/permissions/assign', [\App\Http\Controllers\SystemController::class, 'assignPermission'])->name('system.permissions.assign');
        Route::post('/permissions/remove', [\App\Http\Controllers\SystemController::class, 'removePermission'])->name('system.permissions.remove');
        Route::post('/permissions/create', [\App\Http\Controllers\SystemController::class, 'createPermission'])->name('system.permissions.create');
        Route::get('/permissions/{id}', [\App\Http\Controllers\SystemController::class, 'getPermission'])->name('system.permissions.get');
        Route::put('/permissions/{id}', [\App\Http\Controllers\SystemController::class, 'updatePermission'])->name('system.permissions.update');
        Route::delete('/permissions/{id}', [\App\Http\Controllers\SystemController::class, 'deletePermission'])->name('system.permissions.delete');
        Route::post('/logs/clear', [\App\Http\Controllers\SystemController::class, 'clearLogs'])->name('system.logs.clear');
        Route::get('/logs/download', [\App\Http\Controllers\SystemController::class, 'downloadLogs'])->name('system.logs.download');
        
        // Maintenance operations endpoints
        Route::post('/temp/clear', [\App\Http\Controllers\SystemController::class, 'clearTemp'])->name('system.temp.clear');
        Route::post('/uploads/clear', [\App\Http\Controllers\SystemController::class, 'clearUploads'])->name('system.uploads.clear');
        Route::post('/database/optimize', [\App\Http\Controllers\SystemController::class, 'optimizeDatabase'])->name('system.database.optimize');
        Route::post('/database/migrate', [\App\Http\Controllers\SystemController::class, 'runMigrations'])->name('system.database.migrate');
        Route::post('/queue/restart', [\App\Http\Controllers\SystemController::class, 'restartQueue'])->name('system.queue.restart');
        Route::post('/queue/clear', [\App\Http\Controllers\SystemController::class, 'clearQueue'])->name('system.queue.clear');
        Route::post('/queue/clear-failed', [\App\Http\Controllers\SystemController::class, 'clearFailedJobs'])->name('system.queue.clear-failed');
        Route::get('/queue/status', [\App\Http\Controllers\SystemController::class, 'queueStatus'])->name('system.queue.status');
        Route::get('/health-check', [\App\Http\Controllers\SystemController::class, 'healthCheck'])->name('system.health-check');
    });
    
    // ========================================
    // ADMIN TOOLS ROUTES
    // ========================================
    Route::middleware(['permission:manage_admin_tools'])->prefix('admin')->group(function () {
        // Admin Authentication Routes
        Route::get('/authenticate', [\App\Http\Controllers\AdminAuthController::class, 'authenticate'])->name('admin.authenticate');
        Route::post('/authenticate', [\App\Http\Controllers\AdminAuthController::class, 'processAuth'])->name('admin.process-auth');
        Route::post('/clear-auth', [\App\Http\Controllers\AdminAuthController::class, 'clearAuth'])->name('admin.clear-auth');
        
        // Admin Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // Safe Admin Operations (read-only, no password confirmation needed)
        Route::get('/cache', [\App\Http\Controllers\AdminController::class, 'cache'])->name('admin.cache');
        Route::get('/backup', [\App\Http\Controllers\AdminController::class, 'backup'])->name('admin.backup');
        Route::get('/backup/{backup}', [\App\Http\Controllers\AdminController::class, 'showBackup'])->name('admin.backup.show');
        Route::get('/backup/{backup}/download', [\App\Http\Controllers\AdminController::class, 'downloadBackup'])->name('admin.backup.download');
        
        // Database Management Routes
        Route::get('/database', [\App\Http\Controllers\DatabaseController::class, 'index'])->name('admin.database.index');
        Route::get('/database/backup', [\App\Http\Controllers\DatabaseController::class, 'backup'])->name('admin.database.backup');
        Route::get('/database/{table}', [\App\Http\Controllers\DatabaseController::class, 'showTable'])->name('admin.database.table');
        Route::get('/database/{table}/{id}', [\App\Http\Controllers\DatabaseController::class, 'show'])->name('admin.database.show');
        Route::get('/database/{table}/export/{format}', [\App\Http\Controllers\DatabaseController::class, 'export'])->name('admin.database.export');
        
        // Restricted Admin Operations
        Route::middleware(['admin.security:edit'])->group(function () {
            Route::get('/database/{table}/create', [\App\Http\Controllers\DatabaseController::class, 'create'])->name('admin.database.create');
            Route::post('/database/{table}', [\App\Http\Controllers\DatabaseController::class, 'store'])->name('admin.database.store');
            Route::get('/database/{table}/{id}/edit', [\App\Http\Controllers\DatabaseController::class, 'edit'])->name('admin.database.edit');
            Route::put('/database/{table}/{id}', [\App\Http\Controllers\DatabaseController::class, 'update'])->name('admin.database.update');
            Route::delete('/database/{table}/{id}', [\App\Http\Controllers\DatabaseController::class, 'destroy'])->name('admin.database.destroy');
            Route::post('/database/action', [\App\Http\Controllers\AdminController::class, 'databaseAction'])->name('admin.database.action');
            Route::post('/database/danger', [\App\Http\Controllers\AdminController::class, 'databaseDanger'])->name('admin.database.danger');
            
            // Cache Management (POST operations only)
            Route::post('/cache/clear', [\App\Http\Controllers\AdminController::class, 'clearCache'])->name('admin.cache.clear');
            Route::post('/cache/optimize', [\App\Http\Controllers\AdminController::class, 'optimizeCache'])->name('admin.cache.optimize');
            
            // Backup Management (Dangerous operations)
            Route::post('/backup/create', [\App\Http\Controllers\AdminController::class, 'createBackup'])->name('admin.backup.create');
            Route::post('/backup/upload', [\App\Http\Controllers\AdminController::class, 'uploadBackup'])->name('admin.backup.upload');
            Route::post('/backup/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('admin.backup.settings');
            Route::post('/backup/cleanup', [\App\Http\Controllers\AdminController::class, 'cleanupBackups'])->name('admin.backup.cleanup');
            Route::post('/backup/{backup}/restore', [\App\Http\Controllers\AdminController::class, 'restoreBackup'])->name('admin.backup.restore');
            Route::delete('/backup/{backup}', [\App\Http\Controllers\AdminController::class, 'delete'])->name('admin.backup.delete');
        });
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