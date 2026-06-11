<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCampaign extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'target_segment_id',
        'owner_user_id',
        'code',
        'title',
        'channel',
        'goal',
        'status',
        'dispatch_status',
        'dispatched_at',
        'failed_send_count',
        'starts_at',
        'ends_at',
        'budget_amount',
        'expected_revenue',
        'actual_revenue',
        'audience_count',
        'sent_count',
        'response_count',
        'conversion_count',
        'discount_code',
        'message_template',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'budget_amount' => 'decimal:2',
        'expected_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'audience_count' => 'integer',
        'sent_count' => 'integer',
        'response_count' => 'integer',
        'conversion_count' => 'integer',
        'failed_send_count' => 'integer',
        'dispatched_at' => 'datetime',
    ];

    public const DISPATCH_STATUSES = [
        'idle' => 'آماده',
        'queued' => 'در صف',
        'sending' => 'در حال ارسال',
        'completed' => 'ارسال شد',
        'failed' => 'خطا',
    ];

    public const CHANNELS = [
        'sms' => 'پیامک',
        'email' => 'ایمیل',
        'phone' => 'تماس تلفنی',
        'social' => 'شبکه اجتماعی',
        'in_app' => 'اعلان داخل پنل',
        'mixed' => 'چندکاناله',
    ];

    public const GOALS = [
        'acquisition' => 'جذب مشتری جدید',
        'retention' => 'حفظ مشتری',
        'winback' => 'بازگردانی مشتری',
        'upsell' => 'فروش بیشتر',
        'loyalty' => 'وفاداری و باشگاه',
    ];

    public const STATUSES = [
        'draft' => 'پیش نویس',
        'active' => 'فعال',
        'paused' => 'متوقف',
        'completed' => 'تکمیل شده',
    ];

    public function audiences()
    {
        return $this->hasMany(CrmCampaignAudience::class);
    }

    public function targetSegment()
    {
        return $this->belongsTo(CustomerSegment::class, 'target_segment_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function roiPercent(): float
    {
        $budget = (float) $this->budget_amount;
        if ($budget <= 0) {
            return (float) $this->actual_revenue > 0 ? 100.0 : 0.0;
        }

        return round((((float) $this->actual_revenue - $budget) / $budget) * 100, 2);
    }

    public function conversionRate(): float
    {
        if ((int) $this->audience_count <= 0) {
            return 0.0;
        }

        return round(((int) $this->conversion_count / (int) $this->audience_count) * 100, 2);
    }

    public function channelText(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function goalText(): string
    {
        return self::GOALS[$this->goal] ?? $this->goal;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function dispatchStatusText(): string
    {
        return self::DISPATCH_STATUSES[$this->dispatch_status ?? 'idle'] ?? ($this->dispatch_status ?? 'idle');
    }
}
