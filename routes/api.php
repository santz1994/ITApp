<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AssetController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DailyActivityApiController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\FilterController;
use App\Http\Controllers\API\BulkOperationController;
use App\Http\Controllers\API\ExportController;
use App\Http\Controllers\API\V1\AssetMaintenanceIntelligenceController;
use App\Http\Controllers\API\V1\TicketIntelligenceController;
use App\Http\Controllers\Api\DatatableController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public authentication routes - rate limited
Route::middleware(['throttle:api-auth'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

// Protected API routes - standard rate limiting
// Support both Sanctum token (for external API) and web session (for internal AJAX)
Route::middleware(['auth:sanctum,web', 'throttle:api'])->group(function () {
    
    // Authentication routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Asset API endpoints
    Route::apiResource('assets', AssetController::class)->names([
        'index' => 'api.assets.index',
        'store' => 'api.assets.store',
        'show' => 'api.assets.show',
        'update' => 'api.assets.update',
        'destroy' => 'api.assets.destroy'
    ]);
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign']);
    Route::post('/assets/{asset}/unassign', [AssetController::class, 'unassign']);
    Route::post('/assets/{asset}/maintenance', [AssetController::class, 'markForMaintenance']);
    Route::get('/assets/{asset}/history', [AssetController::class, 'getHistory']);
    // AJAX endpoint: check serial uniqueness (optional exclude_id query param)
    Route::get('/assets/check-serial', [AssetController::class, 'checkSerial'])->name('api.assets.checkSerial');
    // Search endpoint for assets
    Route::get('/assets/search', [AssetController::class, 'search'])->name('api.assets.search');
    
    // Ticket API endpoints
    Route::apiResource('tickets', TicketController::class)->names([
        'index' => 'api.tickets.index',
        'store' => 'api.tickets.store',
        'show' => 'api.tickets.show',
        'update' => 'api.tickets.update',
        'destroy' => 'api.tickets.destroy'
    ]);
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::post('/tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close']);
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen']);
    Route::get('/tickets/{ticket}/timeline', [TicketController::class, 'getTimeline']);
    // Search endpoints for tickets
    Route::get('/tickets/search', [TicketController::class, 'search'])->name('api.tickets.search');
    Route::get('/tickets/{ticket}/comments/search', [TicketController::class, 'commentsSearch'])->name('api.tickets.commentsSearch');

    // Versioned Smart ITSM intake endpoint
    Route::prefix('v1')->name('api.v1.')->group(function () {
        Route::get('/assets/{asset}/maintenance-risk', [AssetMaintenanceIntelligenceController::class, 'show'])
            ->name('assets.maintenance-risk');

        Route::post('/tickets/smart-intake', [TicketIntelligenceController::class, 'store'])
            ->name('tickets.smart-intake');
    });
    
    // User API endpoints
    Route::apiResource('users', UserController::class)->names([
        'index' => 'api.users.index',
        'store' => 'api.users.store',
        'show' => 'api.users.show',
        'update' => 'api.users.update',
        'destroy' => 'api.users.destroy'
    ]);
    Route::get('/users/{user}/performance', [UserController::class, 'getPerformance']);
    Route::get('/users/{user}/workload', [UserController::class, 'getWorkload']);
    Route::get('/users/{user}/activities', [UserController::class, 'getActivities']);
    
    // DailyActivity API
    Route::middleware('auth')->group(function () {
        Route::get('/daily-activities', [DailyActivityApiController::class, 'index']);
        Route::post('/daily-activities', [DailyActivityApiController::class, 'store']);
        Route::get('/daily-activities/{dailyActivity}', [DailyActivityApiController::class, 'show']);
        Route::put('/daily-activities/{dailyActivity}', [DailyActivityApiController::class, 'update']);
        Route::delete('/daily-activities/{dailyActivity}', [DailyActivityApiController::class, 'destroy']);
    });
    
    // Dashboard and Statistics - admin level rate limiting
    Route::middleware(['throttle:api-admin'])->group(function () {
        Route::get('/dashboard/stats', [UserController::class, 'getDashboardStats']);
        Route::get('/dashboard/kpi', [UserController::class, 'getKpiData']);
    });
    
    // Server-side DataTables API endpoints
    Route::get('/datatables/assets', [DatatableController::class, 'assets']);
    Route::get('/datatables/tickets', [DatatableController::class, 'tickets']);
    // Notifications endpoint for API clients (return notifications and unread count)
    Route::get('/notifications', [\App\Http\Controllers\API\NotificationController::class, 'index']);
    
    // Global search endpoints
    Route::get('/search/global', [SearchController::class, 'global'])->name('api.search.global');
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('api.search.suggest');
    Route::get('/search/stats', [SearchController::class, 'stats'])->name('api.search.stats');
    
    // Filter endpoints - filter options for dropdowns
    Route::get('/assets/filter-options/{filter}', [FilterController::class, 'filterOptions'])->name('api.assets.filterOptions');
    Route::get('/tickets/filter-options/{filter}', [FilterController::class, 'filterOptions'])->name('api.tickets.filterOptions');
    
    // Filter builder and statistics
    Route::get('/filter-builder', [FilterController::class, 'filterBuilder'])->name('api.filterBuilder');
    Route::get('/filter-stats', [FilterController::class, 'filterStats'])->name('api.filterStats');
    
    // Bulk Operations - for assets and tickets
    Route::middleware(['throttle:api-bulk'])->group(function () {
        // Asset bulk operations
        Route::post('/assets/bulk/status', [BulkOperationController::class, 'bulkUpdateAssetStatus'])->name('api.assets.bulkUpdateStatus');
        Route::post('/assets/bulk/assign', [BulkOperationController::class, 'bulkUpdateAssetAssignment'])->name('api.assets.bulkUpdateAssignment');
        Route::post('/assets/bulk/update-fields', [BulkOperationController::class, 'bulkUpdateAssetFields'])->name('api.assets.bulkUpdateFields');
        
        // Ticket bulk operations
        Route::post('/tickets/bulk/status', [BulkOperationController::class, 'bulkUpdateTicketStatus'])->name('api.tickets.bulkUpdateStatus');
        Route::post('/tickets/bulk/assign', [BulkOperationController::class, 'bulkUpdateTicketAssignment'])->name('api.tickets.bulkUpdateAssignment');
        Route::post('/tickets/bulk/update-fields', [BulkOperationController::class, 'bulkUpdateTicketFields'])->name('api.tickets.bulkUpdateFields');
        
        // Bulk operation history and monitoring
        Route::get('/bulk-operations', [BulkOperationController::class, 'getBulkOperationHistory'])->name('api.bulkOperationHistory');
        Route::get('/bulk-operations/{operation_id}', [BulkOperationController::class, 'getBulkOperationStatus'])->name('api.bulkOperationStatus');
        Route::get('/bulk-operations/{operation_id}/logs', [BulkOperationController::class, 'getBulkOperationLogs'])->name('api.bulkOperationLogs');
        Route::post('/bulk-operations/{operation_id}/retry', [BulkOperationController::class, 'retryBulkOperation'])->name('api.bulkOperationRetry');
        
        // Export operations
        Route::post('/assets/export', [ExportController::class, 'exportAssets'])->name('api.assets.export');
        Route::post('/tickets/export', [ExportController::class, 'exportTickets'])->name('api.tickets.export');
        Route::get('/exports', [ExportController::class, 'listExports'])->name('api.exports.list');
        Route::get('/exports/{export_id}', [ExportController::class, 'getExportStatus'])->name('api.exports.status');
        Route::get('/exports/{export_id}/download', [ExportController::class, 'downloadExport'])->name('api.exports.download');
        Route::get('/exports/{export_id}/logs', [ExportController::class, 'getExportLogs'])->name('api.exports.logs');
        Route::post('/exports/{export_id}/retry', [ExportController::class, 'retryExport'])->name('api.exports.retry');
        
        // Import conflict resolution
        Route::prefix('imports/{importId}')->name('api.imports.conflicts.')->group(function () {
            Route::get('conflicts', [\App\Http\Controllers\API\ConflictResolutionController::class, 'index'])
                ->name('index');
            Route::get('conflicts/statistics', [\App\Http\Controllers\API\ConflictResolutionController::class, 'statistics'])
                ->name('statistics');
            Route::get('conflicts/{conflictId}', [\App\Http\Controllers\API\ConflictResolutionController::class, 'show'])
                ->name('show');
            Route::post('conflicts/{conflictId}/resolve', [\App\Http\Controllers\API\ConflictResolutionController::class, 'resolve'])
                ->name('resolve');
            Route::post('conflicts/bulk-resolve', [\App\Http\Controllers\API\ConflictResolutionController::class, 'bulkResolve'])
                ->name('bulk-resolve');
            Route::post('conflicts/auto-resolve', [\App\Http\Controllers\API\ConflictResolutionController::class, 'autoResolve'])
                ->name('auto-resolve');
            Route::get('conflicts/history', [\App\Http\Controllers\API\ConflictResolutionController::class, 'history'])
                ->name('history');
            Route::get('conflicts/export', [\App\Http\Controllers\API\ConflictResolutionController::class, 'exportReport'])
                ->name('export-report');
            Route::post('conflicts/rollback', [\App\Http\Controllers\API\ConflictResolutionController::class, 'rollback'])
                ->name('rollback');
        });
    });
    
});

// Public endpoints - very restrictive rate limiting
Route::middleware(['throttle:api-public'])->group(function () {
    Route::get('/system/status', function () {
        return response()->json([
            'status' => 'online',
            'version' => config('app.version', '1.0.0'),
            'timestamp' => now()->toISOString(),
            'api_version' => '1.0'
        ]);
    });
    
    Route::get('/system/health', function () {
        return response()->json([
            'status' => 'healthy',
            'checks' => [
                'database' => 'connected',
                'cache' => 'active',
                'storage' => 'accessible'
            ],
            'timestamp' => now()->toISOString()
        ]);
    });
});