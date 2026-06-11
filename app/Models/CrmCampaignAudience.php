<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCampaignAudience extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'crm_campaign_id',
        'customer_id',
        'crm_lead_id',
        'status',
        'sms_status',
        'sms_error',
        'provider_message_id',
        'estimated_value',
        'revenue_amount',
        'loyalty_points_awarded',
        'pishfactor_id',
        'sent_at',
        'responded_at',
        'converted_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'revenue_amount' => 'decimal:2',
        'loyalty_points_awarded' => 'integer',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public const STATUSES = [
        'planned' => 'برنامه ریزی شده',
        'sent' => 'ارسال شده',
        'responded' => 'پاسخ داده',
        'converted' => 'تبدیل شده',
        'excluded' => 'خارج شده',
    ];

    public function campaign()
    {
        return $this->belongsTo(CrmCampaign::class, 'crm_campaign_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'crm_lead_id');
    }

    public function order()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
