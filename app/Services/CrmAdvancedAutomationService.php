<?php

namespace App\Services;

use App\Models\Area;
use App\Models\CrmFollowup;
use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\CrmSalesBoardCard;
use App\Models\CrmServiceTicket;
use App\Models\Customers;
use App\Models\Organization;
use App\Models\Pishfactor;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrmAdvancedAutomationService
{
    public function run(): array
    {
        return [
            'lead_followups' => $this->createHighPriorityLeadFollowups(),
            'ticket_escalations' => $this->escalateOverdueTickets(),
            'opportunity_followups' => $this->createOpportunityFollowups(),
            'followup_escalations' => $this->escalateOverdueFollowups(),
        ];
    }

    public function policyEnabled(string $flag, ?int $tenantId = null): bool
    {
        return in_array($flag, (array) TenantSettings::get('crm_automation_policy', $tenantId, []), true);
    }

    public function requiresLostReason(?int $tenantId = null): bool
    {
        return $this->policyEnabled('lost_reason_required', $tenantId);
    }

    public function assertLostReason(?string $reason, ?int $tenantId = null): void
    {
        if (!$this->requiresLostReason($tenantId)) {
            return;
        }

        if (!trim((string) $reason)) {
            throw ValidationException::withMessages([
                'lost_reason' => 'ثبت دلیل باخت الزامی است.',
            ]);
        }
    }

    public function handleAfterInvoice(Pishfactor $pishfactor, User $actor): ?CrmFollowup
    {
        if (!$pishfactor->customer_id || !$this->policyEnabled('task_after_invoice', $pishfactor->tenant_id)) {
            return null;
        }

        if ($this->openFollowupExists(Pishfactor::class, (int) $pishfactor->id)) {
            return null;
        }

        $customer = Customers::find($pishfactor->customer_id);
        $dueDate = Carbon::tomorrow()->toDateString();

        $followup = CrmFollowup::create([
            'tenant_id' => $pishfactor->tenant_id,
            'organization_id' => $pishfactor->organization_id,
            'subject_type' => 'customer',
            'customer_id' => $pishfactor->customer_id,
            'assigned_user_id' => $pishfactor->visitor_id ?: $actor->id,
            'type' => 'followup',
            'priority' => 'normal',
            'status' => 'open',
            'source_type' => Pishfactor::class,
            'source_id' => $pishfactor->id,
            'title' => 'پیگیری پس از پیش‌فاکتور #' . $pishfactor->invoiceID,
            'due_date_en' => $dueDate,
            'due_date_fa' => verta($dueDate)->format('Y/m/d'),
            'description' => 'پیگیری خودکار بعد از ثبت پیش‌فاکتور برای ' . ($customer?->name ?: 'مشتری') . '.',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->notifyAssignee($followup, 'invoice');

        return $followup;
    }

    public function convertWonCardToPishfactor(CrmSalesBoardCard $card, User $actor): ?Pishfactor
    {
        $card->loadMissing(['customer', 'board']);

        if ($card->pishfactor_id || !$card->customer_id || $card->card_type === 'task') {
            return null;
        }

        $existing = Pishfactor::query()->where('crm_sales_board_card_id', $card->id)->first();
        if ($existing) {
            $card->update(['pishfactor_id' => $existing->id]);

            return $existing;
        }

        $customer = $card->customer ?: Customers::find($card->customer_id);
        if (!$customer) {
            return null;
        }

        $pishfactor = DB::transaction(function () use ($card, $customer, $actor) {
            $lastInvoice = (int) (DB::table('pishfactors')->max('invoiceID') ?: 0);
            $area = $customer->area ? Area::find($customer->area) : null;
            $region = $area?->region_id ? Region::find($area->region_id) : null;
            $organization = Organization::find($customer->organization_id ?: $card->organization_id ?: $actor->organization_id);

            $factor = Pishfactor::create([
                'visitor_id' => $actor->id,
                'customer_id' => $customer->id,
                'sarparast_id' => $actor->leader_id ?: $actor->id,
                'organization_id' => $customer->organization_id ?: $card->organization_id,
                'tenants_id' => $organization?->tenants_id ?: $actor->tenants_id,
                'tenant_id' => $card->tenant_id ?: $customer->tenant_id,
                'crm_sales_board_card_id' => $card->id,
                'region_id' => $region?->id ?: 0,
                'area_id' => $customer->area ?: 0,
                'city_id' => $region?->city_id ?: 0,
                ...app(SalesScenarioService::class)->initialInvoicePayload($actor),
                'invoiceID' => $lastInvoice + 1,
                'fullPrice' => (string) ((int) $card->amount),
                'pat_price' => (string) ((int) $card->amount),
                'tozihat' => 'ایجاد از کارت برنده کاریز: ' . $card->title,
            ]);

            $activityLogs = $card->activity_logs ?: [];
            $activityLogs[] = [
                'type' => 'pishfactor_created',
                'user_id' => $actor->id,
                'user_name' => $actor->name,
                'pishfactor_id' => $factor->id,
                'invoice_id' => $factor->invoiceID,
                'at' => now()->toDateTimeString(),
            ];

            $card->update([
                'pishfactor_id' => $factor->id,
                'activity_logs' => $activityLogs,
                'updated_by' => $actor->id,
            ]);

            if ($card->opportunity_id) {
                CrmOpportunity::whereKey($card->opportunity_id)->update([
                    'status' => 'won',
                    'stage' => 'won',
                    'closed_at' => now(),
                    'updated_by' => $actor->id,
                ]);
            }

            return $factor;
        });

        app(PanelNotificationService::class)->dispatch('crm_card_won_invoice_created', collect([
            $card->assigned_user_id,
            $actor->id,
        ])->filter()->unique()->all(), [
            'tenant_id' => $card->tenant_id,
            'card_title' => $card->title,
            'invoice_id' => $pishfactor->invoiceID,
            'actor_name' => $actor->name,
            'source' => 'crm_won_card',
            'severity' => 'success',
            'reference_type' => Pishfactor::class,
            'reference_id' => $pishfactor->id,
        ], $card->tenant_id);

        return $pishfactor;
    }

    private function escalateOverdueFollowups(int $days = 2): int
    {
        $cutoff = Carbon::today()->subDays($days)->toDateString();
        $escalated = 0;

        $followups = CrmFollowup::query()
            ->whereIn('status', ['open', 'in_progress'])
            ->whereDate('due_date_en', '<=', $cutoff)
            ->latest('id')
            ->limit(200)
            ->get();

        foreach ($followups as $followup) {
            $sourceKey = 'escalation_' . $followup->id;
            if ($this->openFollowupExists('crm_escalation', $followup->id)) {
                continue;
            }

            $managers = $this->managerIds($followup->tenant_id);
            if ($managers->isEmpty()) {
                continue;
            }

            app(PanelNotificationService::class)->dispatch('crm_followup_escalated', $managers->all(), [
                'tenant_id' => $followup->tenant_id,
                'followup_title' => $followup->title,
                'actor_name' => 'سیستم',
                'time' => now()->format('H:i'),
                'source' => $sourceKey,
                'severity' => 'warning',
                'reference_type' => CrmFollowup::class,
                'reference_id' => $followup->id,
            ], $followup->tenant_id);

            CrmFollowup::create([
                'tenant_id' => $followup->tenant_id,
                'organization_id' => $followup->organization_id,
                'subject_type' => $followup->subject_type,
                'customer_id' => $followup->customer_id,
                'employee_id' => $followup->employee_id,
                'assigned_user_id' => $managers->first(),
                'type' => 'followup',
                'priority' => 'high',
                'status' => 'open',
                'source_type' => 'crm_escalation',
                'source_id' => $followup->id,
                'title' => 'اسکالیشن پیگیری معوق: ' . $followup->title,
                'due_date_en' => Carbon::today()->toDateString(),
                'due_date_fa' => verta(Carbon::today())->format('Y/m/d'),
                'description' => 'پیگیری اصلی از موعد گذشته و به سرپرست ارجاع شد.',
                'created_by' => $followup->assigned_user_id ?: $followup->created_by,
                'updated_by' => $followup->assigned_user_id ?: $followup->created_by,
            ]);

            $escalated++;
        }

        return $escalated;
    }

    private function createHighPriorityLeadFollowups(): int
    {
        $created = 0;
        $leads = CrmLead::query()
            ->where('status', 'open')
            ->whereIn('priority', ['high', 'urgent'])
            ->where('created_at', '<=', now()->subHours(12))
            ->latest('id')
            ->limit(200)
            ->get();

        foreach ($leads as $lead) {
            if ($this->openFollowupExists(CrmLead::class, $lead->id)) {
                continue;
            }

            $followup = CrmFollowup::create([
                'tenant_id' => $lead->tenant_id,
                'organization_id' => $lead->organization_id,
                'subject_type' => 'customer',
                'customer_id' => $lead->customer_id,
                'assigned_user_id' => $lead->owner_user_id ?: $lead->created_by,
                'type' => 'followup',
                'priority' => $lead->priority,
                'status' => 'open',
                'source_type' => CrmLead::class,
                'source_id' => $lead->id,
                'title' => 'پیگیری خودکار سرنخ مهم ' . $lead->name,
                'due_date_en' => Carbon::tomorrow()->toDateString(),
                'due_date_fa' => verta(Carbon::tomorrow())->format('Y/m/d'),
                'description' => 'این پیگیری به صورت خودکار برای سرنخ مهم بدون اقدام سریع ساخته شده است.',
                'created_by' => $lead->created_by,
                'updated_by' => $lead->created_by,
            ]);

            $this->notifyAssignee($followup, 'lead');
            $created++;
        }

        return $created;
    }

    private function escalateOverdueTickets(): int
    {
        $escalated = 0;
        $tickets = CrmServiceTicket::query()
            ->whereIn('status', ['open', 'pending'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->latest('id')
            ->limit(200)
            ->get();

        foreach ($tickets as $ticket) {
            if ($this->openFollowupExists(CrmServiceTicket::class, $ticket->id)) {
                continue;
            }

            $followup = CrmFollowup::create([
                'tenant_id' => $ticket->tenant_id,
                'organization_id' => $ticket->organization_id,
                'subject_type' => 'customer',
                'customer_id' => $ticket->customer_id,
                'assigned_user_id' => $ticket->assigned_user_id ?: $ticket->created_by,
                'type' => 'complaint',
                'priority' => in_array($ticket->priority, ['high', 'urgent'], true) ? 'urgent' : 'high',
                'status' => 'open',
                'source_type' => CrmServiceTicket::class,
                'source_id' => $ticket->id,
                'title' => 'رسیدگی فوری به تیکت معوق ' . $ticket->code,
                'due_date_en' => Carbon::today()->toDateString(),
                'due_date_fa' => verta(Carbon::today())->format('Y/m/d'),
                'description' => 'موعد پاسخ این تیکت گذشته و برای جلوگیری از افت SLA، پیگیری فوری ساخته شد.',
                'created_by' => $ticket->created_by,
                'updated_by' => $ticket->updated_by,
            ]);

            $this->notifyAssignee($followup, 'ticket');
            $escalated++;
        }

        return $escalated;
    }

    private function createOpportunityFollowups(): int
    {
        $created = 0;
        $opportunities = CrmOpportunity::query()
            ->where('status', 'open')
            ->whereNotNull('next_action_date_en')
            ->where('next_action_date_en', '<=', Carbon::today()->toDateString())
            ->latest('id')
            ->limit(200)
            ->get();

        foreach ($opportunities as $opportunity) {
            if ($this->openFollowupExists(CrmOpportunity::class, $opportunity->id)) {
                continue;
            }

            $followup = CrmFollowup::create([
                'tenant_id' => $opportunity->tenant_id,
                'organization_id' => $opportunity->organization_id,
                'subject_type' => 'customer',
                'customer_id' => $opportunity->customer_id,
                'assigned_user_id' => $opportunity->assigned_user_id ?: $opportunity->created_by,
                'type' => 'opportunity',
                'priority' => $opportunity->priority,
                'status' => 'open',
                'source_type' => CrmOpportunity::class,
                'source_id' => $opportunity->id,
                'title' => 'اقدام بعدی فرصت ' . $opportunity->title,
                'due_date_en' => Carbon::today()->toDateString(),
                'due_date_fa' => verta(Carbon::today())->format('Y/m/d'),
                'description' => 'تاریخ اقدام بعدی این فرصت رسیده و پیگیری خودکار ساخته شد.',
                'created_by' => $opportunity->created_by,
                'updated_by' => $opportunity->updated_by,
            ]);

            $this->notifyAssignee($followup, 'opportunity');
            $created++;
        }

        return $created;
    }

    private function openFollowupExists(string $sourceType, int $sourceId): bool
    {
        return CrmFollowup::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereIn('status', ['open', 'in_progress'])
            ->exists();
    }

    private function notifyAssignee(CrmFollowup $followup, string $source): void
    {
        $userIds = collect([$followup->assigned_user_id])->merge($this->managerIds($followup->tenant_id))->filter()->unique()->values()->all();

        app(PanelNotificationService::class)->dispatch('crm_automation_followup_created', $userIds, [
            'tenant_id' => $followup->tenant_id,
            'followup_title' => $followup->title,
            'card_title' => $followup->title,
            'board_title' => 'اتوماسیون CRM',
            'actor_name' => 'سیستم',
            'time' => now()->format('H:i'),
            'source' => 'crm_advanced_automation_' . $source,
            'severity' => $followup->priority === 'urgent' ? 'warning' : 'info',
            'reference_type' => CrmFollowup::class,
            'reference_id' => $followup->id,
        ], $followup->tenant_id);
    }

    private function managerIds(?int $tenantId)
    {
        return User::query()
            ->where('isActive', 1)
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where(function ($inner) use ($tenantId) {
                    $inner->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
                });
            })
            ->where(function ($query) {
                $query->where('isAdmin', 1)->orWhere('isGod', 1);
            })
            ->limit(20)
            ->pluck('id');
    }
}
