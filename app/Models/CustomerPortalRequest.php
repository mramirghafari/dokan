<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPortalRequest extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_portal_account_id',
        'customer_id',
        'pishfactor_id',
        'crm_service_ticket_id',
        'type',
        'priority',
        'status',
        'subject',
        'description',
        'response',
        'requested_amount',
        'metadata',
        'submitted_at',
        'responded_at',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'metadata' => 'array',
        'submitted_at' => 'datetime',
        'responded_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public const TYPES = [
        'support' => 'درخواست پشتیبانی',
        'complaint' => 'شکایت',
        'order_request' => 'درخواست سفارش',
        'payment_followup' => 'پیگیری پرداخت',
        'feedback' => 'بازخورد',
    ];

    public const PRIORITIES = ['low' => 'کم', 'normal' => 'عادی', 'high' => 'مهم', 'urgent' => 'فوری'];
    public const STATUSES = ['new' => 'جدید', 'in_review' => 'در حال بررسی', 'answered' => 'پاسخ داده شده', 'closed' => 'بسته شده'];

    public function account()
    {
        return $this->belongsTo(CustomerPortalAccount::class, 'customer_portal_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function ticket()
    {
        return $this->belongsTo(CrmServiceTicket::class, 'crm_service_ticket_id');
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
