<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmEmployeeCoachingPlan extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'performance_snapshot_id',
        'user_id',
        'employee_id',
        'coach_user_id',
        'type',
        'priority',
        'status',
        'title',
        'target_metric',
        'target_value',
        'due_at',
        'action_plan',
        'outcome',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public const TYPES = [
        'general' => 'عمومی',
        'sales' => 'فروش',
        'support' => 'پشتیبانی',
        'followup' => 'پیگیری',
        'quality' => 'کیفیت مکالمه',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'medium' => 'مهم',
        'high' => 'فوری',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'in_progress' => 'در حال coaching',
        'done' => 'انجام شده',
        'canceled' => 'لغو شده',
    ];

    public function snapshot()
    {
        return $this->belongsTo(CrmEmployeePerformanceSnapshot::class, 'performance_snapshot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    public function typeText(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
