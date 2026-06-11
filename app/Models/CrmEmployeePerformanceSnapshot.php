<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmEmployeePerformanceSnapshot extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'user_id',
        'employee_id',
        'period_start',
        'period_end',
        'role_scope',
        'total_score',
        'sales_score',
        'support_score',
        'followup_score',
        'call_score',
        'coaching_priority',
        'metrics',
        'strengths',
        'risks',
        'recommendation',
        'calculated_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_score' => 'decimal:2',
        'sales_score' => 'decimal:2',
        'support_score' => 'decimal:2',
        'followup_score' => 'decimal:2',
        'call_score' => 'decimal:2',
        'metrics' => 'array',
        'strengths' => 'array',
        'risks' => 'array',
        'calculated_at' => 'datetime',
    ];

    public const ROLE_SCOPES = [
        'mixed' => 'ترکیبی',
        'sales' => 'فروش',
        'support' => 'پشتیبانی',
        'followup' => 'پیگیری',
    ];

    public const PRIORITIES = [
        'low' => 'پایدار',
        'normal' => 'عادی',
        'medium' => 'نیازمند توجه',
        'high' => 'نیازمند coaching فوری',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function coachings()
    {
        return $this->hasMany(CrmEmployeeCoachingPlan::class, 'performance_snapshot_id');
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->coaching_priority] ?? $this->coaching_priority;
    }

    public function roleScopeText(): string
    {
        return self::ROLE_SCOPES[$this->role_scope] ?? $this->role_scope;
    }
}
