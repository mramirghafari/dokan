<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmSalesBoardCard extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'board_id',
        'list_id',
        'tenant_id',
        'organization_id',
        'customer_id',
        'opportunity_id',
        'pishfactor_id',
        'lost_reason',
        'assigned_user_id',
        'assigned_user_ids',
        'card_type',
        'title',
        'description',
        'priority',
        'estimate_minutes',
        'status',
        'amount',
        'probability_percent',
        'expected_close_date_en',
        'expected_close_date_fa',
        'next_action_date_en',
        'next_action_date_fa',
        'labels',
        'checklist',
        'source_filter',
        'activity_logs',
        'position',
        'moved_at',
        'started_at',
        'ended_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_user_ids' => 'array',
        'amount' => 'decimal:2',
        'estimate_minutes' => 'integer',
        'probability_percent' => 'integer',
        'expected_close_date_en' => 'date',
        'next_action_date_en' => 'date',
        'labels' => 'array',
        'checklist' => 'array',
        'source_filter' => 'array',
        'activity_logs' => 'array',
        'position' => 'integer',
        'moved_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public const PRIORITIES = [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'مهم',
        'urgent' => 'فوری',
    ];

    public const STATUSES = [
        'open' => 'باز',
        'in_progress' => 'در حال انجام',
        'done' => 'انجام شده',
        'won' => 'برده شده',
        'lost' => 'از دست رفته',
        'canceled' => 'لغو شده',
    ];

    public const TYPES = [
        'task' => 'تسک',
        'customer' => 'مشتری',
        'opportunity' => 'فرصت فروش',
    ];

    public const LABELS = [
        'force' => ['title' => 'فورس', 'color' => 'danger'],
        'hold' => ['title' => 'هولد', 'color' => 'warning'],
        'followup' => ['title' => 'پیگیری', 'color' => 'info'],
        'vip' => ['title' => 'VIP', 'color' => 'primary'],
        'blocked' => ['title' => 'مسدود', 'color' => 'dark'],
        'waiting' => ['title' => 'منتظر پاسخ', 'color' => 'secondary'],
    ];

    public function board()
    {
        return $this->belongsTo(CrmSalesBoard::class, 'board_id');
    }

    public function list()
    {
        return $this->belongsTo(CrmSalesBoardList::class, 'list_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(CrmOpportunity::class, 'opportunity_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function checklistItems()
    {
        return $this->hasMany(CrmSalesBoardCardChecklistItem::class, 'card_id')->orderBy('position')->orderBy('id');
    }

    public function comments()
    {
        return $this->hasMany(CrmSalesBoardCardComment::class, 'card_id')->latest();
    }

    public function attachments()
    {
        return $this->hasMany(CrmSalesBoardCardAttachment::class, 'card_id')->latest();
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function typeText(): string
    {
        return self::TYPES[$this->card_type] ?? $this->card_type;
    }

    public function weightedAmount(): float
    {
        return round(((float) $this->amount * (int) $this->probability_percent) / 100, 2);
    }

    public function estimateText(): string
    {
        if (!$this->estimate_minutes) {
            return '-';
        }

        $hours = intdiv((int) $this->estimate_minutes, 60);
        $minutes = (int) $this->estimate_minutes % 60;

        return trim(($hours ? $hours . ' ساعت ' : '') . ($minutes ? $minutes . ' دقیقه' : ''));
    }
}
