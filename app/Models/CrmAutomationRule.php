<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmAutomationRule extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'board_id',
        'list_id',
        'tenant_id',
        'organization_id',
        'trigger_event',
        'card_type',
        'action_type',
        'assigned_user_id',
        'due_days',
        'priority',
        'title_template',
        'description_template',
        'notify_assignee',
        'notify_board_owner',
        'escalate_to_manager',
        'is_active',
        'execution_count',
        'last_executed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_days' => 'integer',
        'notify_assignee' => 'boolean',
        'notify_board_owner' => 'boolean',
        'escalate_to_manager' => 'boolean',
        'is_active' => 'boolean',
        'execution_count' => 'integer',
        'last_executed_at' => 'datetime',
    ];

    public const CARD_TYPES = [
        '' => 'همه کارت ها',
        'task' => 'تسک',
        'customer' => 'مشتری',
        'opportunity' => 'فرصت فروش',
    ];

    public const ACTION_TYPES = [
        'create_followup' => 'ساخت پیگیری CRM',
    ];

    public function board()
    {
        return $this->belongsTo(CrmSalesBoard::class, 'board_id');
    }

    public function list()
    {
        return $this->belongsTo(CrmSalesBoardList::class, 'list_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function actionText(): string
    {
        return self::ACTION_TYPES[$this->action_type] ?? $this->action_type;
    }

    public function cardTypeText(): string
    {
        return self::CARD_TYPES[$this->card_type ?: ''] ?? $this->card_type;
    }
}
