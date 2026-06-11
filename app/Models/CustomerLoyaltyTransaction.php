<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLoyaltyTransaction extends Model
{
    use HasFactory, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_loyalty_account_id',
        'customer_id',
        'crm_campaign_id',
        'crm_campaign_audience_id',
        'pishfactor_id',
        'type',
        'points',
        'amount',
        'reason',
        'description',
        'created_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'amount' => 'decimal:2',
    ];

    public const TYPES = [
        'earn' => 'کسب امتیاز',
        'redeem' => 'مصرف امتیاز',
        'adjust' => 'اصلاح امتیاز',
        'expire' => 'انقضای امتیاز',
    ];

    public function account()
    {
        return $this->belongsTo(CustomerLoyaltyAccount::class, 'customer_loyalty_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function campaign()
    {
        return $this->belongsTo(CrmCampaign::class, 'crm_campaign_id');
    }

    public function audience()
    {
        return $this->belongsTo(CrmCampaignAudience::class, 'crm_campaign_audience_id');
    }

    public function typeText(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
