<?php

namespace App\Services;

use App\Models\CrmAutomationRule;
use App\Models\CrmFollowup;
use App\Models\CrmSalesBoardCard;
use App\Models\CrmSalesBoardList;
use App\Models\User;
use Illuminate\Support\Carbon;

class CrmCardAutomationService
{
    public function handleCardMoved(CrmSalesBoardCard $card, ?CrmSalesBoardList $fromList, CrmSalesBoardList $targetList, User $actor): int
    {
        if (!$this->automationPolicyAllowsCardMove($card->tenant_id)) {
            return 0;
        }

        $card->loadMissing(['board', 'list', 'customer', 'assignedUser']);

        $rules = CrmAutomationRule::query()
            ->where('board_id', $card->board_id)
            ->where('trigger_event', 'card_moved_to_list')
            ->where('action_type', 'create_followup')
            ->where('is_active', true)
            ->where(function ($query) use ($targetList) {
                $query->whereNull('list_id')->orWhere('list_id', $targetList->id);
            })
            ->where(function ($query) use ($card) {
                $query->whereNull('card_type')->orWhere('card_type', $card->card_type);
            })
            ->get();

        $created = 0;

        foreach ($rules as $rule) {
            if ($this->hasOpenGeneratedFollowup($rule, $card)) {
                continue;
            }

            $followup = $this->createFollowup($rule, $card, $fromList, $targetList, $actor);
            $this->notify($rule, $followup, $card, $fromList, $targetList, $actor);
            $this->appendCardActivity($card, $rule, $followup, $actor, $targetList);

            $rule->increment('execution_count');
            $rule->forceFill(['last_executed_at' => now(), 'updated_by' => $actor->id])->save();
            $created++;
        }

        return $created;
    }

    private function automationPolicyAllowsCardMove(?int $tenantId): bool
    {
        $policy = TenantSettings::get('crm_automation_policy', $tenantId, []);

        return in_array('task_after_card_move', (array) $policy, true);
    }

    private function hasOpenGeneratedFollowup(CrmAutomationRule $rule, CrmSalesBoardCard $card): bool
    {
        return CrmFollowup::query()
            ->where('automation_rule_id', $rule->id)
            ->where('source_type', CrmSalesBoardCard::class)
            ->where('source_id', $card->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->exists();
    }

    private function createFollowup(CrmAutomationRule $rule, CrmSalesBoardCard $card, ?CrmSalesBoardList $fromList, CrmSalesBoardList $targetList, User $actor): CrmFollowup
    {
        $dueDate = Carbon::today()->addDays(max(0, (int) $rule->due_days))->toDateString();
        $assignedUserId = $rule->assigned_user_id ?: $card->assigned_user_id ?: $card->board?->owner_user_id ?: $actor->id;

        return CrmFollowup::create([
            'tenant_id' => $card->tenant_id,
            'organization_id' => $card->organization_id,
            'subject_type' => 'customer',
            'customer_id' => $card->customer_id,
            'assigned_user_id' => $assignedUserId,
            'type' => 'followup',
            'priority' => $rule->priority ?: $card->priority ?: 'normal',
            'status' => 'open',
            'source_type' => CrmSalesBoardCard::class,
            'source_id' => $card->id,
            'automation_rule_id' => $rule->id,
            'title' => $this->render($rule->title_template, $card, $fromList, $targetList, $actor),
            'due_date_en' => $dueDate,
            'due_date_fa' => verta($dueDate)->format('Y/m/d'),
            'description' => $this->render($rule->description_template ?: 'پیگیری خودکار از کارت {card} در بورد {board}.', $card, $fromList, $targetList, $actor),
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function notify(CrmAutomationRule $rule, CrmFollowup $followup, CrmSalesBoardCard $card, ?CrmSalesBoardList $fromList, CrmSalesBoardList $targetList, User $actor): void
    {
        $recipients = collect();

        if ($rule->notify_assignee && $followup->assigned_user_id) {
            $recipients->push($followup->assigned_user_id);
        }

        if ($rule->notify_board_owner && $card->board?->owner_user_id) {
            $recipients->push($card->board->owner_user_id);
        }

        if ($rule->escalate_to_manager) {
            $recipients = $recipients->merge($this->tenantManagerIds($card->tenant_id));
        }

        $recipients = $recipients->push($actor->id)->filter()->unique()->values();

        if ($recipients->isEmpty()) {
            return;
        }

        app(PanelNotificationService::class)->dispatch('crm_automation_followup_created', $recipients->all(), [
            'tenant_id' => $card->tenant_id,
            'card_title' => $card->title,
            'board_title' => $card->board?->title,
            'from_list' => $fromList?->title,
            'to_list' => $targetList->title,
            'followup_title' => $followup->title,
            'actor_name' => $actor->name,
            'time' => now()->format('H:i'),
            'source' => 'crm_automation',
            'severity' => $rule->escalate_to_manager ? 'warning' : 'info',
            'reference_type' => CrmFollowup::class,
            'reference_id' => $followup->id,
        ], $card->tenant_id);
    }

    private function tenantManagerIds(?int $tenantId)
    {
        return User::query()
            ->where('isActive', 1)
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where(function ($inner) use ($tenantId) {
                    $inner->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
                });
            }, fn($query) => $query->where('isGod', 1))
            ->where(function ($query) {
                $query->where('isAdmin', 1)->orWhere('isGod', 1);
            })
            ->pluck('id');
    }

    private function appendCardActivity(CrmSalesBoardCard $card, CrmAutomationRule $rule, CrmFollowup $followup, User $actor, CrmSalesBoardList $targetList): void
    {
        $activityLogs = $card->activity_logs ?: [];
        $activityLogs[] = [
            'type' => 'automation_followup_created',
            'user_id' => $actor->id,
            'user_name' => $actor->name,
            'rule_id' => $rule->id,
            'followup_id' => $followup->id,
            'list_id' => $targetList->id,
            'list_title' => $targetList->title,
            'at' => now()->toDateTimeString(),
        ];

        $card->forceFill(['activity_logs' => $activityLogs])->save();
    }

    private function render(string $template, CrmSalesBoardCard $card, ?CrmSalesBoardList $fromList, CrmSalesBoardList $targetList, User $actor): string
    {
        return strtr($template, [
            '{card}' => $card->title,
            '{board}' => $card->board?->title ?: '-',
            '{customer}' => $card->customer?->name ?: '-',
            '{from_list}' => $fromList?->title ?: '-',
            '{to_list}' => $targetList->title,
            '{user}' => $actor->name,
            '{date}' => now()->format('Y-m-d'),
        ]);
    }
}
