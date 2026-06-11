<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCallLog extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_id',
        'service_ticket_id',
        'assigned_user_id',
        'code',
        'direction',
        'channel',
        'status',
        'result',
        'priority',
        'subject',
        'phone_number',
        'contact_name',
        'call_started_at',
        'call_ended_at',
        'duration_seconds',
        'next_action_at',
        'quality_score',
        'recording_url',
        'notes',
        'outcome',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'call_started_at' => 'datetime',
        'call_ended_at' => 'datetime',
        'next_action_at' => 'datetime',
        'duration_seconds' => 'integer',
        'quality_score' => 'integer',
    ];

    public const DIRECTIONS = [
        'inbound' => 'ورودی',
        'outbound' => 'خروجی',
        'missed' => 'از دست رفته',
        'internal' => 'داخلی',
    ];

    public const CHANNELS = [
        'phone' => 'تلفن',
        'voip' => 'VoIP',
        'mobile' => 'موبایل',
        'social' => 'شبکه اجتماعی',
        'manual' => 'ثبت دستی',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'completed' => 'تکمیل شده',
        'failed' => 'ناموفق',
        'needs_followup' => 'نیازمند پیگیری',
    ];

    public const RESULTS = [
        'answered' => 'پاسخ داده شد',
        'no_answer' => 'بدون پاسخ',
        'busy' => 'اشغال',
        'complaint' => 'شکایت',
        'sale_opportunity' => 'فرصت فروش',
        'support_needed' => 'نیازمند پشتیبانی',
        'resolved' => 'حل شد',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'مهم',
        'urgent' => 'فوری',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function serviceTicket()
    {
        return $this->belongsTo(CrmServiceTicket::class, 'service_ticket_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function directionText(): string
    {
        return self::DIRECTIONS[$this->direction] ?? $this->direction;
    }

    public function channelText(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function resultText(): string
    {
        return $this->result ? (self::RESULTS[$this->result] ?? $this->result) : '-';
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }
}
