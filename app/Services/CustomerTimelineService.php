<?php

namespace App\Services;

use App\Models\CrmCallLog;
use App\Models\CrmFollowup;
use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\CrmServiceTicket;
use App\Models\CustomerPortalPayment;
use App\Models\Customers;
use App\Models\Notifs;
use App\Models\Pishfactor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CustomerTimelineService
{
    public function profile(Customers $customer, ?User $user = null): array
    {
        $events = $this->timeline($customer, $user);

        return [
            'customer' => $customer,
            'events' => $events,
            'stats' => $this->stats($customer, $user),
            'quick_actions' => $this->quickActions($customer),
        ];
    }

    public function timeline(Customers $customer, ?User $user = null): array
    {
        return collect()
            ->merge($this->invoiceEvents($customer))
            ->merge($this->followupEvents($customer, $user))
            ->merge($this->opportunityEvents($customer, $user))
            ->merge($this->callEvents($customer, $user))
            ->merge($this->ticketEvents($customer, $user))
            ->merge($this->paymentEvents($customer))
            ->merge($this->smsEvents($customer))
            ->merge($this->leadEvents($customer, $user))
            ->sortByDesc(fn (array $event) => $event['occurred_at'])
            ->values()
            ->all();
    }

    private function stats(Customers $customer, ?User $user): array
    {
        $followups = $this->scoped(CrmFollowup::query(), $user)->where('customer_id', $customer->id);
        $opportunities = $this->scoped(CrmOpportunity::query(), $user)->where('customer_id', $customer->id);
        $tickets = $this->scoped(CrmServiceTicket::query(), $user)->where('customer_id', $customer->id);
        $orders = Pishfactor::query()->where('customer_id', $customer->id);

        return [
            'orders_total' => (clone $orders)->count(),
            'orders_active' => (clone $orders)->whereIn('status', [1, 4])->count(),
            'revenue_total' => (int) (clone $orders)->whereIn('status', [1, 4])->get()->sum(fn ($row) => (int) str_replace(',', '', (string) $row->fullPrice)),
            'open_followups' => (clone $followups)->whereIn('status', ['open', 'in_progress'])->count(),
            'open_opportunities' => (clone $opportunities)->where('status', 'open')->count(),
            'open_tickets' => (clone $tickets)->whereIn('status', ['open', 'pending'])->count(),
        ];
    }

    private function quickActions(Customers $customer): array
    {
        $phone = $customer->mobile ?: $customer->phone;

        return [
            [
                'key' => 'call',
                'label' => 'تماس',
                'url' => $phone ? 'tel:' . $phone : route('crm.call-center.index', ['search' => $customer->name]),
                'icon' => 'ti-phone',
            ],
            [
                'key' => 'followup',
                'label' => 'پیگیری',
                'url' => route('crm.followups.index', ['search' => $customer->name, 'customer_id' => $customer->id]),
                'icon' => 'ti-checklist',
            ],
            [
                'key' => 'ticket',
                'label' => 'تیکت',
                'url' => route('crm.service-tickets.index', ['search' => $customer->name]),
                'icon' => 'ti-ticket',
            ],
            [
                'key' => 'invoice',
                'label' => 'پیش‌فاکتور',
                'url' => route('invoices.create', ['customer_id' => $customer->id]),
                'icon' => 'ti-file-invoice',
            ],
        ];
    }

    private function invoiceEvents(Customers $customer): Collection
    {
        return Pishfactor::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn (Pishfactor $invoice) => [
                'type' => 'invoice',
                'type_label' => 'فاکتور / پیش‌فاکتور',
                'title' => 'سفارش #' . ($invoice->invoiceID ?: $invoice->id),
                'description' => 'مبلغ ' . number_format((int) str_replace(',', '', (string) $invoice->fullPrice)) . ' | وضعیت ' . $invoice->status,
                'occurred_at' => $this->carbon($invoice->created_at),
                'url' => route('pishFactorView', $invoice->id),
                'badge' => 'primary',
            ]);
    }

    private function followupEvents(Customers $customer, ?User $user): Collection
    {
        return $this->scoped(CrmFollowup::query()->with('assignedUser'), $user)
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (CrmFollowup $followup) => [
                'type' => 'followup',
                'type_label' => 'پیگیری CRM',
                'title' => $followup->title,
                'description' => trim(($followup->description ?: '') . ' ' . (optional($followup->assignedUser)->name ? 'مسئول: ' . $followup->assignedUser->name : '')),
                'occurred_at' => $this->carbon($followup->due_date_en ?: $followup->created_at),
                'url' => route('crm.followups.index', ['search' => $followup->title]),
                'badge' => in_array($followup->status, ['open', 'in_progress'], true) ? 'warning' : 'secondary',
            ]);
    }

    private function opportunityEvents(Customers $customer, ?User $user): Collection
    {
        return $this->scoped(CrmOpportunity::query(), $user)
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (CrmOpportunity $opportunity) => [
                'type' => 'opportunity',
                'type_label' => 'فرصت فروش',
                'title' => $opportunity->title,
                'description' => 'مبلغ ' . number_format((int) $opportunity->amount) . ' | مرحله ' . ($opportunity->stage ?: '-'),
                'occurred_at' => $this->carbon($opportunity->next_action_date_en ?: $opportunity->created_at),
                'url' => route('crm.opportunities.index', ['search' => $opportunity->title]),
                'badge' => $opportunity->status === 'open' ? 'info' : 'secondary',
            ]);
    }

    private function callEvents(Customers $customer, ?User $user): Collection
    {
        if (!Schema::hasTable('crm_call_logs')) {
            return collect();
        }

        return $this->scoped(CrmCallLog::query(), $user)
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->map(fn (CrmCallLog $call) => [
                'type' => 'call',
                'type_label' => 'تماس',
                'title' => CrmCallLog::DIRECTIONS[$call->direction] ?? $call->direction,
                'description' => $call->subject ?: ($call->result ?: 'تماس ثبت‌شده'),
                'occurred_at' => $this->carbon($call->call_started_at ?: $call->created_at),
                'url' => route('crm.call-center.index', ['search' => $customer->name]),
                'badge' => $call->direction === 'missed' ? 'danger' : 'success',
            ]);
    }

    private function ticketEvents(Customers $customer, ?User $user): Collection
    {
        return $this->scoped(CrmServiceTicket::query(), $user)
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->map(fn (CrmServiceTicket $ticket) => [
                'type' => 'ticket',
                'type_label' => 'تیکت خدمات',
                'title' => $ticket->subject,
                'description' => ($ticket->code ? $ticket->code . ' | ' : '') . 'وضعیت ' . $ticket->status,
                'occurred_at' => $this->carbon($ticket->due_at ?: $ticket->created_at),
                'url' => route('crm.service-tickets.index', ['search' => $ticket->subject]),
                'badge' => in_array($ticket->status, ['open', 'pending'], true) ? 'danger' : 'secondary',
            ]);
    }

    private function paymentEvents(Customers $customer): Collection
    {
        if (!Schema::hasTable('customer_portal_payments')) {
            return collect();
        }

        return CustomerPortalPayment::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(fn (CustomerPortalPayment $payment) => [
                'type' => 'payment',
                'type_label' => 'پرداخت',
                'title' => 'پرداخت ' . number_format((int) $payment->amount),
                'description' => CustomerPortalPayment::METHODS[$payment->payment_method] ?? $payment->payment_method,
                'occurred_at' => $this->carbon($payment->verified_at ?: $payment->submitted_at ?: $payment->requested_at),
                'url' => '#',
                'badge' => $payment->status === 'paid' ? 'success' : 'warning',
            ]);
    }

    private function smsEvents(Customers $customer): Collection
    {
        if (!Schema::hasTable('notifs')) {
            return collect();
        }

        return Notifs::query()
            ->where('reference_id', $customer->id)
            ->where(function ($query) {
                $query->where('reference_type', 'like', '%customer%')
                    ->orWhere('source', 'like', '%sms%');
            })
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(fn (Notifs $notif) => [
                'type' => 'sms',
                'type_label' => 'پیامک / اعلان',
                'title' => $notif->title,
                'description' => $notif->content,
                'occurred_at' => $this->carbon($notif->sent_at ?: $notif->created_at),
                'url' => '#',
                'badge' => 'dark',
            ]);
    }

    private function leadEvents(Customers $customer, ?User $user): Collection
    {
        if (!Schema::hasTable('crm_leads')) {
            return collect();
        }

        return $this->scoped(CrmLead::query(), $user)
            ->where(function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)
                    ->orWhere('duplicate_customer_id', $customer->id);
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn (CrmLead $lead) => [
                'type' => 'lead',
                'type_label' => 'سرنخ',
                'title' => $lead->name,
                'description' => $lead->company_name ?: ($lead->mobile ?: ''),
                'occurred_at' => $this->carbon($lead->created_at),
                'url' => route('crm.leads.index', ['search' => $lead->name]),
                'badge' => 'info',
            ]);
    }

    private function scoped($query, ?User $user)
    {
        if ($user && (int) $user->isGod !== 1 && method_exists($query->getModel(), 'scopeForOrganizations')) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function carbon(mixed $value): Carbon
    {
        return $value ? Carbon::parse($value) : now();
    }
}
