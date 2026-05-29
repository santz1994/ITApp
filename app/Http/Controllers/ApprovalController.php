<?php

namespace App\Http\Controllers;

use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
        $this->middleware('auth');
    }

    // ========================================
    // MY PENDING APPROVALS
    // ========================================

    public function pendingApprovals()
    {
        $approvals = $this->approvalService->getPendingApprovalsForUser(Auth::id());
        return view('approvals.pending', compact('approvals'));
    }

    // ========================================
    // APPROVAL ACTIONS
    // ========================================

    public function approve(Request $request, $id)
    {
        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        try {
            $instance = $this->approvalService->processApproval(
                $id,
                Auth::id(),
                'approve',
                $request->comments
            );

            return back()->with('success', 'Request berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'comments' => 'required|string|max:500',
        ]);

        try {
            $instance = $this->approvalService->processApproval(
                $id,
                Auth::id(),
                'reject',
                $request->comments
            );

            return back()->with('success', 'Request berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ========================================
    // APPROVAL STATUS VIEW
    // ========================================

    public function show($id)
    {
        $instance = $this->approvalService->getApprovalStatus(null, $id);
        return view('approvals.show', compact('instance'));
    }

    // ========================================
    // APPROVAL RULE MANAGEMENT (Admin)
    // ========================================

    public function rules()
    {
        $rules = $this->approvalService->getAllRules();
        return view('approvals.rules', compact('rules'));
    }

    public function createRule()
    {
        return view('approvals.create-rule');
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

        return redirect()->route('approvals.rules')
            ->with('success', 'Approval rule berhasil dibuat.');
    }

    public function editRule($id)
    {
        $rule = $this->approvalService->getRule($id);
        return view('approvals.edit-rule', compact('rule'));
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

        return redirect()->route('approvals.rules')
            ->with('success', 'Approval rule berhasil diperbarui.');
    }

    public function deleteRule($id)
    {
        $this->approvalService->deleteRule($id);

        return redirect()->route('approvals.rules')
            ->with('success', 'Approval rule berhasil dihapus.');
    }

    public function toggleRule($id)
    {
        $rule = $this->approvalService->toggleRuleActive($id);

        return back()->with('success', 'Status approval rule berhasil diubah.');
    }
}