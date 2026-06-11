<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmOpportunity extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_id',
        'assigned_user_id',
        'source_followup_id',
        'source_lead_id',
        'code',
        'title',
        'stage',
        'priority',
        'status',
        'amount',
        'probability_percent',
        'expected_close_date_en',
        'expected_close_date_fa',
        'next_action_date_en',
        'next_action_date_fa',
        'description',
        'outcome',
        'lost_reason',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'probability_percent' => 'integer',
        'expected_close_date_en' => 'date',
        'next_action_date_en' => 'date',
        'closed_at' => 'datetime',
    ];

    public const STAGES = [
        'new' => 'سرنخ جدید',
        'qualified' => 'تایید نیاز',
        'proposal' => 'ارسال پیشنهاد',
        'negotiation' => 'مذاکره',
        'won' => 'برده شده',
        'lost' => 'از دست رفته',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'won' => 'برده شده',
        'lost' => 'از دست رفته',
        'canceled' => 'لغو شده',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'مهم',
        'urgent' => 'فوری',
    ];

    public const LOST_REASONS = [
        'price' => 'قیمت بالا',
        'competitor' => 'انتخاب رقیب',
        'no_budget' => 'بودجه ندارد',
        'no_need' => 'نیاز ندارد',
        'no_response' => 'پاسخ نداد',
        'timing' => 'زمان‌بندی نامناسب',
        'other' => 'سایر',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function sourceFollowup()
    {
        return $this->belongsTo(CrmFollowup::class, 'source_followup_id');
    }

    public function sourceLead()
    {
        return $this->belongsTo(CrmLead::class, 'source_lead_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stageText(): string
    {
        return self::STAGES[$this->stage] ?? $this->stage;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function weightedAmount(): float
    {
        return round(((float) $this->amount * (int) $this->probability_percent) / 100, 2);
    }
}
