<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovalRuleStep extends Model
{
    protected $fillable = [
        'rule_id',
        'step_order',
        'approval_type',
        'approver_id',
        'approver_reference',
        'is_mandatory',
        'any_of_group',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'is_mandatory' => 'boolean',
        'any_of_group' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function rule()
    {
        return $this->belongsTo(ApprovalRule::class, 'rule_id');
    }

    public function stepInstances()
    {
        return $this->hasMany(ApprovalStepInstance::class, 'step_id');
    }

    // ========================================
    // HELPERS
    // ========================================

    /**
     * Resolve the actual approver(s) for this step
     */
    public function resolveApprovers($requesterId = null)
    {
        switch ($this->approval_type) {
            case 'role':
                // Find users with the specified role
                $role = Role::where('name', $this->approver_reference)->first();
                if ($role) {
                    return $role->users()->pluck('users.id')->toArray();
                }
                return [];

            case 'specific_user':
                return $this->approver_id ? [$this->approver_id] : [];

            case 'department_manager':
                // Find the manager of the requester's department
                if ($requesterId) {
                    $requester = User::find($requesterId);
                    if ($requester && $requester->division_id) {
                        return User::where('division_id', $requester->division_id)
                            ->whereHas('roles', function ($q) {
                                $q->where('name', 'like', '%manager%');
                            })
                            ->pluck('id')
                            ->toArray();
                    }
                }
                return [];

            default:
                return [];
        }
    }
}