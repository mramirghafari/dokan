<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalWorkflowStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['approval_workflow_id', 'step_order', 'title', 'role_id', 'user_id', 'is_required'];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }
}
