<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module',
        'name',
        'description',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function steps()
    {
        return $this->hasMany(ApprovalRuleStep::class, 'rule_id')->orderBy('step_order');
    }

    public function instances()
    {
        return $this->hasMany(ApprovalInstance::class, 'rule_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}