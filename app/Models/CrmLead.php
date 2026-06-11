<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmLead extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'owner_user_id',
        'customer_id',
        'opportunity_id',
        'code',
        'name',
        'company_name',
        'mobile',
        'phone',
        'email',
        'city',
        'source',
        'campaign',
        'score',
        'stage',
        'status',
        'priority',
        'duplicate_status',
        'duplicate_customer_id',
        'duplicate_lead_id',
        'notes',
        'reject_reason',
        'converted_at',
        'converted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'score' => 'integer',
        'converted_at' => 'datetime',
    ];

    public const SOURCES = [
        'manual' => 'ثبت دستی',
        'website' => 'وب سایت',
        'phone' => 'تماس تلفنی',
        'campaign' => 'کمپین',
        'referral' => 'معرفی مشتری',
        'social' => 'شبکه اجتماعی',
        'exhibition' => 'نمایشگاه',
    ];

    public const STAGES = [
        'new' => 'جدید',
        'nurturing' => 'پرورش / nurturing',
        'qualified' => 'تایید شده',
        'converted' => 'تبدیل شده',
        'rejected' => 'رد شده',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'converted' => 'تبدیل شده',
        'rejected' => 'رد شده',
        'duplicate' => 'تکراری',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'مهم',
        'urgent' => 'فوری',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(CrmOpportunity::class, 'opportunity_id');
    }

    public function duplicateCustomer()
    {
        return $this->belongsTo(Customers::class, 'duplicate_customer_id');
    }

    public function duplicateLead()
    {
        return $this->belongsTo(self::class, 'duplicate_lead_id');
    }

    public function sourceText(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
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
}
