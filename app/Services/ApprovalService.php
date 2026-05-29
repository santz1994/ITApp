<?php

namespace App\Services;

use App\ApprovalRule;
use App\ApprovalRuleStep;
use App\ApprovalInstance;
use App\ApprovalStepInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Multi-tier Approval Workflow Engine
 * 
 * Flow:
 * 1. User submits a request (meeting room, vehicle, inventory)
 * 2. System finds matching approval_rule for the module
 * 3. System creates approval_instance + approval_step_instances
 * 4. Each step_instance is linked to a rule_step with approver resolution
 * 5. Approvers act on their step (approve/reject)
 * 6. If approved, system advances to next step
 * 7. If all steps approved → request is approved
 * 8. If any step rejected → request is rejected
 * 
 * Configuration is database-driven via approval_rules table.
 * Admin can modify rules without code changes.
 */
class ApprovalService
{
    /**
     * Start a new approval flow for a request
     * 
     * @param string $module Module name (meeting_room, vehicle, inventory)
     * @param Model $requestable The model being approved (e.g., VehicleBooking)
     * @return ApprovalInstance|null
     */
    public function startApproval($module, $requestable)
    {
        return DB::transaction(function () use ($module, $requestable) {
            // Find the active approval rule for this module
            $rule = ApprovalRule::active()
                ->forModule($module)
                ->byPriority()
                ->with('steps')
                ->first();

            if (!$rule || $rule->steps->isEmpty()) {
                Log::info("No approval rule found for module: {$module}. Auto-approving.");
                return null; // No rule = auto-approve
            }

            // Create approval instance
            $instance = ApprovalInstance::create([
                'rule_id' => $rule->id,
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'status' => 'in_progress',
                'current_step' => 1,
            ]);

            // Create step instances for each rule step
            $requesterId = $requestable->requested_by ?? Auth::id();

            foreach ($rule->steps as $step) {
                $status = 'pending';

                // Skip non-mandatory steps if approver cannot be resolved
                if (!$step->is_mandatory) {
                    $approvers = $step->resolveApprovers($requesterId);
                    if (empty($approvers)) {
                        $status = 'skipped';
                    }
                }

                ApprovalStepInstance::create([
                    'instance_id' => $instance->id,
                    'step_id' => $step->id,
                    'step_order' => $step->step_order,
                    'status' => $status,
                ]);
            }

            return $instance->load('stepInstances.ruleStep');
        });
    }

    /**
     * Process an approval action (approve or reject)
     * 
     * @param int $instanceId Approval instance ID
     * @param int $userId User performing the action
     * @param string $action 'approve' or 'reject'
     * @param string|null $comments Optional comments
     * @return ApprovalInstance
     */
    public function processApproval($instanceId, $userId, $action, $comments = null)
    {
        return DB::transaction(function () use ($instanceId, $userId, $action, $comments) {
            $instance = ApprovalInstance::lockForUpdate()
                ->with(['stepInstances.ruleStep', 'requestable'])
                ->findOrFail($instanceId);

            if (!$instance->isInProgress()) {
                throw new \Exception("Approval sudah selesai. Status: {$instance->status}");
            }

            // Find the current pending step
            $currentStepInstance = $instance->stepInstances()
                ->where('step_order', $instance->current_step)
                ->where('status', 'pending')
                ->first();

            if (!$currentStepInstance) {
                throw new \Exception('Tidak ada step yang menunggu persetujuan.');
            }

            // Verify user is authorized to approve this step
            $ruleStep = $currentStepInstance->ruleStep;
            $requesterId = $instance->requestable->requested_by ?? null;
            $approverIds = $ruleStep->resolveApprovers($requesterId);

            if (!in_array($userId, $approverIds)) {
                throw new \Exception('Anda tidak memiliki hak untuk menyetujui/menolak request ini.');
            }

            if ($action === 'approve') {
                // Mark current step as approved
                $currentStepInstance->update([
                    'status' => 'approved',
                    'approved_by' => $userId,
                    'acted_at' => now(),
                    'comments' => $comments,
                ]);

                // Move to next step or complete
                $nextStep = $instance->stepInstances()
                    ->where('step_order', '>', $instance->current_step)
                    ->where('status', 'pending')
                    ->orderBy('step_order')
                    ->first();

                if ($nextStep) {
                    // There's a next step - advance
                    $instance->update(['current_step' => $nextStep->step_order]);
                } else {
                    // All steps completed - approval is done
                    $instance->update(['status' => 'approved']);

                    // Trigger approval callback on the requestable model
                    $this->onApprovalComplete($instance, 'approved');
                }

            } elseif ($action === 'reject') {
                // Mark current step as rejected
                $currentStepInstance->update([
                    'status' => 'rejected',
                    'approved_by' => $userId,
                    'acted_at' => now(),
                    'comments' => $comments,
                ]);

                // Reject the entire instance
                $instance->update(['status' => 'rejected']);

                // Trigger rejection callback on the requestable model
                $this->onApprovalComplete($instance, 'rejected');
            }

            return $instance->fresh(['stepInstances.ruleStep']);
        });
    }

    /**
     * Cancel an approval flow
     */
    public function cancelApproval($instanceId)
    {
        $instance = ApprovalInstance::lockForUpdate()->findOrFail($instanceId);

        if (!$instance->isInProgress()) {
            throw new \Exception('Approval sudah selesai. Status: ' . $instance->status);
        }

        // Cancel all pending steps
        $instance->stepInstances()
            ->where('status', 'pending')
            ->update(['status' => 'skipped']);

        $instance->update(['status' => 'cancelled']);

        return $instance;
    }

    /**
     * Get approval status for a requestable model
     */
    public function getApprovalStatus($requestableType, $requestableId)
    {
        return ApprovalInstance::where('requestable_type', $requestableType)
            ->where('requestable_id', $requestableId)
            ->with(['stepInstances.ruleStep', 'rule'])
            ->latest()
            ->first();
    }

    /**
     * Check if a user can approve for a given requestable
     */
    public function canUserApprove($requestableType, $requestableId, $userId)
    {
        $instance = $this->getApprovalStatus($requestableType, $requestableId);

        if (!$instance || !$instance->isInProgress()) {
            return false;
        }

        return $instance->canUserApprove($userId);
    }

    /**
     * Get pending approvals for a specific user
     */
    public function getPendingApprovalsForUser($userId)
    {
        $user = \App\User::find($userId);
        $userRoles = $user->roles->pluck('name')->toArray();

        return ApprovalInstance::where('status', 'in_progress')
            ->whereHas('stepInstances', function ($query) use ($userId, $userRoles) {
                $query->where('status', 'pending')
                    ->where('step_order', DB::raw('`approval_instances`.`current_step`'));
            })
            ->with(['stepInstances.ruleStep', 'requestable'])
            ->get()
            ->filter(function ($instance) use ($userId) {
                return $instance->canUserApprove($userId);
            });
    }

    /**
     * Callback after approval/rejection - update the requestable model status
     */
    protected function onApprovalComplete($instance, $status)
    {
        $requestable = $instance->requestable;

        if (!$requestable) {
            return;
        }

        // Update the requestable model's status based on approval result
        if ($status === 'approved' && method_exists($requestable, 'onApprovalApproved')) {
            $requestable->onApprovalApproved();
        } elseif ($status === 'rejected' && method_exists($requestable, 'onApprovalRejected')) {
            $requestable->onApprovalRejected();
        }

        Log::info("Approval {$status} for " . get_class($requestable) . "#{$requestable->id}");
    }

    // ========================================
    // APPROVAL RULE MANAGEMENT (Admin)
    // ========================================

    public function getAllRules()
    {
        return ApprovalRule::with(['steps' => function ($query) {
            $query->orderBy('step_order');
        }])->orderBy('module')->orderBy('priority', 'desc')->get();
    }

    public function getRule($id)
    {
        return ApprovalRule::with(['steps' => function ($query) {
            $query->orderBy('step_order');
        }])->findOrFail($id);
    }

    public function createRule(array $data)
    {
        return DB::transaction(function () use ($data) {
            $rule = ApprovalRule::create([
                'module' => $data['module'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['steps'])) {
                foreach ($data['steps'] as $index => $stepData) {
                    ApprovalRuleStep::create([
                        'rule_id' => $rule->id,
                        'step_order' => $stepData['step_order'] ?? ($index + 1),
                        'approval_type' => $stepData['approval_type'],
                        'approver_id' => $stepData['approver_id'] ?? null,
                        'approver_reference' => $stepData['approver_reference'] ?? null,
                        'is_mandatory' => $stepData['is_mandatory'] ?? true,
                        'any_of_group' => $stepData['any_of_group'] ?? false,
                    ]);
                }
            }

            return $rule->load('steps');
        });
    }

    public function updateRule($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $rule = ApprovalRule::findOrFail($id);
            $rule->update([
                'module' => $data['module'] ?? $rule->module,
                'name' => $data['name'] ?? $rule->name,
                'description' => $data['description'] ?? $rule->description,
                'priority' => $data['priority'] ?? $rule->priority,
                'is_active' => $data['is_active'] ?? $rule->is_active,
            ]);

            // If steps provided, replace all steps
            if (isset($data['steps'])) {
                $rule->steps()->delete();

                foreach ($data['steps'] as $index => $stepData) {
                    ApprovalRuleStep::create([
                        'rule_id' => $rule->id,
                        'step_order' => $stepData['step_order'] ?? ($index + 1),
                        'approval_type' => $stepData['approval_type'],
                        'approver_id' => $stepData['approver_id'] ?? null,
                        'approver_reference' => $stepData['approver_reference'] ?? null,
                        'is_mandatory' => $stepData['is_mandatory'] ?? true,
                        'any_of_group' => $stepData['any_of_group'] ?? false,
                    ]);
                }
            }

            return $rule->fresh('steps');
        });
    }

    public function deleteRule($id)
    {
        $rule = ApprovalRule::findOrFail($id);
        return $rule->delete();
    }

    public function toggleRuleActive($id)
    {
        $rule = ApprovalRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active]);
        return $rule;
    }
}