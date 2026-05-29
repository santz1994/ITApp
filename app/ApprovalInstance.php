<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovalInstance extends Model
{
    protected $fillable = [
        'rule_id',
        'requestable_type',
        'requestable_id',
        'status',
        'current_step',
    ];

    protected $casts = [
        'current_step' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function rule()
    {
        return $this->belongsTo(ApprovalRule::class, 'rule_id');
    }

    public function requestable()
    {
        return $this->morphTo();
    }

    public function stepInstances()
    {
        return $this->hasMany(ApprovalStepInstance::class, 'instance_id')->orderBy('step_order');
    }

    public function currentStepInstance()
    {
        return $this->hasOne(ApprovalStepInstance::class, 'instance_id')
            ->where('step_order', $this->current_step)
            ->where('status', 'pending');
    }

    // ========================================
    // HELPERS
    // ========================================

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if a specific user can approve the current step
     */
    public function canUserApprove($userId)
    {
        $currentStep = $this->currentStepInstance;
        if (!$currentStep) {
            return false;
        }

        $ruleStep = $currentStep->ruleStep;
        $approverIds = $ruleStep->resolveApprovers(
            $this->requestable->requested_by ?? null
        );

        return in_array($userId, $approverIds);
    }

    /**
     * Get overall progress percentage
     */
    public function getProgressAttribute()
    {
        $totalSteps = $this->stepInstances->count();
        if ($totalSteps === 0) return 0;

        $completedSteps = $this->stepInstances->whereIn('status', ['approved', 'skipped'])->count();
        return round(($completedSteps / $totalSteps) * 100);
    }
}