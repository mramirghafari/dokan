<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalWorkflow extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'document_type', 'title', 'is_required', 'amount_threshold', 'isActive'];

    protected $casts = [
        'is_required' => 'boolean',
        'amount_threshold' => 'decimal:2',
        'isActive' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(ApprovalWorkflowStep::class)->orderBy('step_order');
    }
}
