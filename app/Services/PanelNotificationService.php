<?php

namespace App\Services;

use App\Models\Log as OperationLog;
use App\Models\Notifs;
use App\Models\Tenants;
use App\Models\User;
use Illuminate\Support\Facades\Log as Logger;
use Kavenegar\Laravel\Facade as Kavenegar;
use Throwable;

class PanelNotificationService
{
    public const EVENTS = [
        'crm_card_created' => 'ثبت کارت کاریز',
        'crm_customer_card_created' => 'افزودن مشتری به کاریز',
        'crm_card_moved' => 'جابجایی کارت کاریز',
        'crm_automation_followup_created' => 'پیگیری خودکار CRM',
        'crm_task_started' => 'شروع تسک',
        'crm_task_finished' => 'پایان تسک',
        'crm_comment_mention' => 'منشن در CRM',
        'system_operation_logged' => 'عملیات سیستم',
    ];

    public function dispatch(string $event, array $userIds, array $payload = [], ?int $tenantId = null): void
    {
        $userIds = collect($userIds)->filter()->map(fn($id) => (int) $id)->unique()->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $tenantId = $tenantId ?: (int) ($payload['tenant_id'] ?? 0) ?: null;
        $notificationEvents = TenantSettings::get('notification_events_enabled', $tenantId, array_keys(self::EVENTS));

        if (!in_array($event, (array) $notificationEvents, true)) {
            return;
        }

        $title = self::EVENTS[$event] ?? 'اعلان سیستم';
        $content = $this->renderMessage($event, $payload, false);

        foreach ($userIds as $userId) {
            Notifs::create([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'title' => $title,
                'content' => $content,
                'status' => 0,
                'source' => $payload['source'] ?? 'system_operation',
                'severity' => $payload['severity'] ?? 'info',
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'alert_key' => $event,
                'sent_at' => now(),
            ]);
        }

        $smsEvents = TenantSettings::get('sms_events_enabled', $tenantId, []);

        if (in_array($event, (array) $smsEvents, true)) {
            $this->sendSms($event, $userIds->all(), $payload, $tenantId);
        }
    }

    public function dispatchOperationLog(OperationLog $operationLog): void
    {
        $actor = $operationLog->user_id ? User::find($operationLog->user_id) : null;
        $tenantId = $operationLog->tenant_id ?: (int) ($actor->tenant_id ?? $actor->tenants_id ?? 0) ?: null;
        $userIds = $this->operationRecipients($operationLog, $actor, $tenantId);

        if (empty($userIds)) {
            return;
        }

        $description = $operationLog->getRawOriginal('description') ?: $operationLog->description;

        $this->dispatch('system_operation_logged', $userIds, [
            'tenant_id' => $tenantId,
            'operation' => $description,
            'action' => $this->actionText($operationLog->action),
            'section' => $operationLog->section ?: $operationLog->event_key ?: 'عمومی',
            'actor_name' => optional($actor)->name ?: 'سیستم',
            'time' => now()->format('H:i'),
            'source' => 'operation_log',
            'severity' => in_array($operationLog->action, ['delete', 'forceDelete'], true) ? 'warning' : 'info',
            'reference_type' => OperationLog::class,
            'reference_id' => $operationLog->id,
        ], $tenantId);
    }

    private function sendSms(string $event, array $userIds, array $payload, ?int $tenantId): void
    {
        $sender = trim((string) TenantSettings::get('sms_sender_number', $tenantId, ''));
        $template = trim((string) TenantSettings::get('sms_template_' . $event, $tenantId, ''));

        if ($sender === '' || $template === '') {
            return;
        }

        $tenant = $tenantId ? Tenants::find($tenantId) : null;
        $unitPrice = (float) ($tenant->sms_unit_price_toman ?? TenantSettings::get('sms_unit_price_toman', $tenantId, 0));
        $message = $this->replaceTokens($template, $payload);
        $users = User::query()->whereIn('id', $userIds)->whereNotNull('mobile')->get(['id', 'mobile']);

        foreach ($users as $user) {
            if ($unitPrice > 0 && $tenant && (float) $tenant->wallet_balance < $unitPrice) {
                return;
            }

            try {
                Kavenegar::Send($sender, $user->mobile, $message);

                if ($unitPrice > 0 && $tenant) {
                    $tenant->decrement('wallet_balance', $unitPrice);
                    $tenant->refresh();
                }
            } catch (Throwable $exception) {
                Logger::warning('Kavenegar SMS dispatch failed', [
                    'event' => $event,
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function renderMessage(string $event, array $payload, bool $sms): string
    {
        $defaults = [
            'crm_card_created' => 'کارت {card} در بورد {board} ثبت شد.',
            'crm_customer_card_created' => 'مشتری {card} به بورد {board} اضافه شد.',
            'crm_card_moved' => 'کارت {card} از {from_list} به {to_list} منتقل شد.',
            'crm_automation_followup_created' => 'پیگیری خودکار {followup} برای کارت {card} ساخته شد.',
            'crm_task_started' => 'تسک {card} از ساعت {time} شروع شد.',
            'crm_task_finished' => 'تسک {card} در ساعت {time} پایان یافت.',
            'crm_comment_mention' => '{user} شما را روی {record} منشن کرد: {comment}',
            'system_operation_logged' => '{operation} توسط {user} در بخش {section} ثبت شد.',
        ];

        return $this->replaceTokens($defaults[$event] ?? 'عملیات جدید در پنل ثبت شد.', $payload);
    }

    private function operationRecipients(OperationLog $operationLog, ?User $actor, ?int $tenantId): array
    {
        $mode = TenantSettings::get('notification_operation_recipients', $tenantId, 'panel_admins_actor');
        $actorId = $actor ? (int) $actor->id : null;

        if ($mode === 'actor_only') {
            return array_values(array_filter([$actorId]));
        }

        $usersQuery = User::query()->where('isActive', 1);

        if ($tenantId) {
            $usersQuery->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        } else {
            $usersQuery->where('isGod', 1);
        }

        if ($mode !== 'all_active_users') {
            $usersQuery->where(function ($query) {
                $query->where('isAdmin', 1)->orWhere('isGod', 1);
            });
        }

        $userIds = $usersQuery->pluck('id')->map(fn($id) => (int) $id);

        if ($mode === 'panel_admins_actor' && $actorId) {
            $userIds->push($actorId);
        }

        return $userIds->filter()->unique()->values()->all();
    }

    private function actionText(?string $action): string
    {
        return [
            'create' => 'ایجاد',
            'update' => 'ویرایش',
            'delete' => 'حذف',
            'restore' => 'بازگردانی',
            'forceDelete' => 'حذف دائم',
            'login' => 'ورود',
            'logout' => 'خروج',
        ][$action] ?? ($action ?: 'عملیات');
    }

    private function replaceTokens(string $template, array $payload): string
    {
        $tokens = [
            '{operation}' => $payload['operation'] ?? '-',
            '{action}' => $payload['action'] ?? '-',
            '{section}' => $payload['section'] ?? '-',
            '{card}' => $payload['card_title'] ?? '-',
            '{record}' => $payload['record_title'] ?? '-',
            '{comment}' => $payload['comment_body'] ?? '-',
            '{followup}' => $payload['followup_title'] ?? '-',
            '{board}' => $payload['board_title'] ?? '-',
            '{from_list}' => $payload['from_list'] ?? '-',
            '{to_list}' => $payload['to_list'] ?? '-',
            '{user}' => $payload['actor_name'] ?? '-',
            '{time}' => $payload['time'] ?? now()->format('H:i'),
        ];

        return strtr($template, $tokens);
    }
}
