<?php

/**
 * Approval Workflow Routes
 * 
 * Handles multi-tier approval flow management.
 * Uses permission-based middleware for database-driven RBAC.
 * 
 * Permission tags used:
 * - view_pending_approvals: View approvals pending for current user
 * - approve_requests: Approve/reject requests
 * - manage_approval_rules: CRUD approval rules (admin)
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApprovalController;

Route::middleware(['auth'])->group(function () {

    // ========================================
    // MY PENDING APPROVALS (All authenticated users)
    // ========================================
    Route::get('/approvals', [ApprovalController::class, 'pendingApprovals'])->name('approvals.pending');

    // ========================================
    // APPROVAL ACTIONS
    // ========================================
    Route::middleware(['permission:approve_requests'])->group(function () {
        Route::post('/approvals/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // ========================================
    // APPROVAL STATUS VIEW
    // ========================================
    Route::get('/approvals/{id}', [ApprovalController::class, 'show'])->name('approvals.show');

    // ========================================
    // APPROVAL RULE MANAGEMENT (Admin only)
    // ========================================
    Route::middleware(['permission:manage_approval_rules'])->prefix('admin/approvals')->name('approvals.')->group(function () {
        Route::get('/rules', [ApprovalController::class, 'rules'])->name('rules');
        Route::get('/rules/create', [ApprovalController::class, 'createRule'])->name('rules.create');
        Route::post('/rules', [ApprovalController::class, 'storeRule'])->name('rules.store');
        Route::get('/rules/{id}/edit', [ApprovalController::class, 'editRule'])->name('rules.edit');
        Route::put('/rules/{id}', [ApprovalController::class, 'updateRule'])->name('rules.update');
        Route::delete('/rules/{id}', [ApprovalController::class, 'deleteRule'])->name('rules.delete');
        Route::post('/rules/{id}/toggle', [ApprovalController::class, 'toggleRule'])->name('rules.toggle');
    });
});