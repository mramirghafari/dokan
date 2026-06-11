<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmFollowup extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'subject_type',
        'customer_id',
        'employee_id',
        'assigned_user_id',
        'type',
        'priority',
        'status',
        'source_type',
        'source_id',
        'automation_rule_id',
        'title',
        'due_date_en',
        'due_date_fa',
        'description',
        'outcome',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date_en' => 'date',
        'completed_at' => 'datetime',
    ];

    public const TYPES = [
        'followup' => 'پیگیری',
        'call' => 'تماس',
        'meeting' => 'جلسه',
        'complaint' => 'شکایت',
        'opportunity' => 'فرصت فروش',
        'hr_followup' => 'پیگیری کارمند',
        'note' => 'یادداشت',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'مهم',
        'urgent' => 'فوری',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'in_progress' => 'در حال پیگیری',
        'done' => 'انجام شده',
        'canceled' => 'لغو شده',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function subjectName(): string
    {
        if ($this->subject_type === 'employee') {
            return optional($this->employee)->name ?: 'کارمند حذف شده';
        }

        return optional($this->customer)->name ?: 'مشتری حذف شده';
    }
}
