<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovalStepInstance extends Model
{
    protected $fillable = [
        'instance_id',
        'step_id',
        'step_order',
        'status',
        'approved_by',
        'acted_at',
        'comments',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'acted_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function instance()
    {
        return $this->belongsTo(ApprovalInstance::class, 'instance_id');
    }

    public function ruleStep()
    {
        return $this->belongsTo(ApprovalRuleStep::class, 'step_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================================
    // HELPERS
    // ========================================

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isSkipped()
    {
        return $this->status === 'skipped';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'skipped' => 'info',
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}