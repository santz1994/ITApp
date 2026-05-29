<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalApiController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function pendingApprovals()
    {
        $approvals = $this->approvalService->getPendingApprovalsForUser(Auth::id());

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    public function approve(Request $request, $id)
    {
        try {
            $instance = $this->approvalService->processApproval($id, Auth::id(), 'approve', $request->comments);

            return response()->json([
                'success' => true,
                'message' => 'Request berhasil disetujui.',
                'data' => $instance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['comments' => 'required|string|max:500']);

        try {
            $instance = $this->approvalService->processApproval($id, Auth::id(), 'reject', $request->comments);

            return response()->json([
                'success' => true,
                'message' => 'Request berhasil ditolak.',
                'data' => $instance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id)
    {
        $instance = $this->approvalService->getApprovalStatus(null, $id);

        return response()->json([
            'success' => true,
            'data' => $instance,
        ]);
    }

    // Admin: Rule Management
    public function rules()
    {
        $rules = $this->approvalService->getAllRules();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.step_order' => 'required|integer|min:1',
            'steps.*.approval_type' => 'required|in:role,specific_user,department_manager',
            'steps.*.approver_id' => 'nullable|integer',
            'steps.*.approver_reference' => 'nullable|string|max:100',
            'steps.*.is_mandatory' => 'boolean',
            'steps.*.any_of_group' => 'boolean',
        ]);

        $rule = $this->approvalService->createRule($validated);

        return response()->json([
            'success' => true,
            'message' => 'Approval rule berhasil dibuat.',
            'data' => $rule,
        ], 201);
    }

    public function updateRule(Request $request, $id)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.step_order' => 'required|integer|min:1',
            'steps.*.approval_type' => 'required|in:role,specific_user,department_manager',
            'steps.*.approver_id' => 'nullable|integer',
            'steps.*.approver_reference' => 'nullable|string|max:100',
            'steps.*.is_mandatory' => 'boolean',
            'steps.*.any_of_group' => 'boolean',
        ]);

        $rule = $this->approvalService->updateRule($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Approval rule berhasil diperbarui.',
            'data' => $rule,
        ]);
    }

    public function destroyRule($id)
    {
        $this->approvalService->deleteRule($id);

        return response()->json([
            'success' => true,
            'message' => 'Approval rule berhasil dihapus.',
        ]);
    }

    public function toggleRule($id)
    {
        $rule = $this->approvalService->toggleRuleActive($id);

        return response()->json([
            'success' => true,
            'message' => 'Status approval rule berhasil diubah.',
            'data' => $rule,
        ]);
    }
}