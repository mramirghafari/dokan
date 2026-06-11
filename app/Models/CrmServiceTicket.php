<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmServiceTicket extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_id',
        'assigned_user_id',
        'code',
        'type',
        'channel',
        'priority',
        'status',
        'subject',
        'contact_name',
        'contact_phone',
        'description',
        'resolution',
        'due_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'satisfaction_score',
        'satisfaction_note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'satisfaction_score' => 'integer',
    ];

    public const TYPES = [
        'support' => 'درخواست پشتیبانی',
        'complaint' => 'شکایت',
        'service' => 'درخواست سرویس',
        'warranty' => 'گارانتی',
        'question' => 'پرسش مشتری',
    ];

    public const CHANNELS = [
        'manual' => 'ثبت دستی',
        'phone' => 'تماس تلفنی',
        'in_person' => 'حضوری',
        'website' => 'وب سایت',
        'social' => 'شبکه اجتماعی',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'مهم',
        'urgent' => 'فوری',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'pending' => 'در انتظار',
        'resolved' => 'حل شده',
        'closed' => 'بسته شده',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function typeText(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function channelText(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isOverdue(): bool
    {
        return $this->due_at && in_array($this->status, ['open', 'pending'], true) && $this->due_at->lt(now());
    }
}
