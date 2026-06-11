<?php

namespace App\Services;

use App\Models\OperationalExpense;
use App\Models\Pishfactor;
use App\Models\PurchaseOrder;
use App\Models\TreasuryInstrument;
use App\Models\VoucherItems;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TreasuryCashForecastService
{
    public function build($user, Collection $accounts, array $filters = []): array
    {
        $baseDate = Carbon::parse($filters['base_date'] ?? now()->toDateString())->startOfDay();
        $days = max(1, min(180, (int) ($filters['days'] ?? 30)));
        $toDate = $baseDate->copy()->addDays($days)->endOfDay();
        $accountIds = $accounts->pluck('id')->filter()->values();

        $openingBalance = $this->ledgerBalance($user, $accountIds, $baseDate);
        $dueCheques = $this->openCheques($user, $baseDate, $toDate)->get();
        $overdueCheques = $this->overdueCheques($user, $baseDate)->get();
        $events = $this->forecastEvents($user, $dueCheques, $baseDate, $toDate);
        $overdueEvents = $this->overdueEvents($user, $overdueCheques, $baseDate);
        $dailyRows = $this->dailyRows($events, $baseDate, $toDate, $openingBalance);
        $sourceSummary = $this->sourceSummary($events);

        return [
            'base_date' => $baseDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'days' => $days,
            'opening_balance' => $openingBalance,
            'projected_balance' => (float) ($dailyRows->last()['projected_balance'] ?? $openingBalance),
            'expected_inflow' => (float) $events->where('direction', 'incoming')->sum('amount'),
            'expected_outflow' => (float) $events->where('direction', 'outgoing')->sum('amount'),
            'overdue_count' => $overdueEvents->count(),
            'overdue_amount' => (float) $overdueEvents->sum('amount'),
            'daily_rows' => $dailyRows,
            'source_summary' => $sourceSummary,
            'forecast_events' => $events,
            'overdue_events' => $overdueEvents,
            'due_cheques' => $dueCheques,
            'overdue_cheques' => $overdueCheques,
        ];
    }

    private function ledgerBalance($user, Collection $accountIds, Carbon $baseDate): float
    {
        if ($accountIds->isEmpty()) {
            return 0;
        }

        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereIn('voucher_items.account_id', $accountIds->all())
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->whereDate('vouchers.voucher_date_en', '<=', $baseDate->toDateString());

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        $totals = $query->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit, COALESCE(SUM(voucher_items.credit_amount), 0) as credit')->first();

        return round((float) $totals->debit - (float) $totals->credit, 2);
    }

    private function openCheques($user, Carbon $baseDate, Carbon $toDate)
    {
        return $this->openChequeBase($user)
            ->whereBetween('due_date', [$baseDate->toDateString(), $toDate->toDateString()])
            ->orderBy('due_date')
            ->orderByDesc('amount');
    }

    private function overdueCheques($user, Carbon $baseDate)
    {
        return $this->openChequeBase($user)
            ->whereDate('due_date', '<', $baseDate->toDateString())
            ->orderBy('due_date')
            ->orderByDesc('amount');
    }

    private function openChequeBase($user)
    {
        $query = TreasuryInstrument::with(['counterAccount', 'currentHolderAccount'])
            ->where('instrument_type', 'cheque')
            ->whereNotNull('due_date')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('direction', 'incoming')
                        ->whereIn('status', ['received', 'deposited']);
                })->orWhere(function ($query) {
                    $query->where('direction', 'outgoing')
                        ->whereIn('status', ['issued']);
                });
            });

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query;
    }

    private function forecastEvents($user, Collection $dueCheques, Carbon $baseDate, Carbon $toDate): Collection
    {
        return collect()
            ->merge($this->chequeEvents($dueCheques))
            ->merge($this->purchasePayableEvents($user, $baseDate, $toDate, false))
            ->merge($this->salesReceivableEvents($user, $baseDate, $toDate, false))
            ->merge($this->expensePayableEvents($user, $baseDate, $toDate, false))
            ->sortBy([['date', 'asc'], ['amount', 'desc']])
            ->values();
    }

    private function overdueEvents($user, Collection $overdueCheques, Carbon $baseDate): Collection
    {
        return collect()
            ->merge($this->chequeEvents($overdueCheques, true))
            ->merge($this->purchasePayableEvents($user, $baseDate, $baseDate, true))
            ->merge($this->salesReceivableEvents($user, $baseDate, $baseDate, true))
            ->merge($this->expensePayableEvents($user, $baseDate, $baseDate, true))
            ->sortBy([['date', 'asc'], ['amount', 'desc']])
            ->values();
    }

    private function chequeEvents(Collection $cheques, bool $overdue = false): Collection
    {
        return $cheques->map(function ($cheque) use ($overdue) {
            return [
                'date' => optional($cheque->due_date)->format('Y-m-d'),
                'direction' => $cheque->direction,
                'source' => 'cheque',
                'source_label' => 'چک خزانه',
                'title' => $cheque->cheque_number ?: ('چک #' . $cheque->id),
                'status' => $cheque->status,
                'amount' => (float) $cheque->amount,
                'counterparty' => optional($cheque->counterAccount)->name ?: '-',
                'reference_type' => 'treasury_instrument',
                'reference_id' => $cheque->id,
                'is_overdue' => $overdue,
            ];
        });
    }

    private function purchasePayableEvents($user, Carbon $baseDate, Carbon $toDate, bool $overdue): Collection
    {
        $query = PurchaseOrder::with('supplier')
            ->whereIn('status', ['approved', 'partial_received', 'received'])
            ->where(function ($query) {
                $query->whereNull('payment_status')->orWhereIn('payment_status', ['unpaid', 'partial']);
            })
            ->whereNotNull('order_date_en')
            ->when($overdue, fn($query) => $query->whereDate('order_date_en', '<', $baseDate->toDateString()), fn($query) => $query->whereBetween('order_date_en', [$baseDate->toDateString(), $toDate->toDateString()]))
            ->orderBy('order_date_en');

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query->get()
            ->map(function ($order) use ($overdue) {
                return [
                    'date' => optional($order->order_date_en)->format('Y-m-d'),
                    'direction' => 'outgoing',
                    'source' => 'purchase_payable',
                    'source_label' => 'خرید پرداخت نشده',
                    'title' => $order->order_number ?: ('سفارش خرید #' . $order->id),
                    'status' => $order->payment_status,
                    'amount' => (float) $order->remaining_amount,
                    'counterparty' => optional($order->supplier)->name ?: '-',
                    'reference_type' => 'purchase_order',
                    'reference_id' => $order->id,
                    'is_overdue' => $overdue,
                ];
            })
            ->filter(fn($event) => (float) $event['amount'] > 0)
            ->values();
    }

    private function salesReceivableEvents($user, Carbon $baseDate, Carbon $toDate, bool $overdue): Collection
    {
        $query = Pishfactor::with('customer')
            ->whereIn('status', [1, 4])
            ->where(function ($query) {
                $query->whereNull('settlement_status')->orWhereNotIn('settlement_status', ['settled', 'paid']);
            })
            ->whereNotNull('recive_date_en')
            ->when($overdue, fn($query) => $query->whereDate('recive_date_en', '<', $baseDate->toDateString()), fn($query) => $query->whereBetween('recive_date_en', [$baseDate->toDateString(), $toDate->toDateString()]))
            ->orderBy('recive_date_en');

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query->get()
            ->map(function ($factor) use ($overdue) {
                return [
                    'date' => Carbon::parse($factor->recive_date_en)->toDateString(),
                    'direction' => 'incoming',
                    'source' => 'sales_receivable',
                    'source_label' => 'فروش وصول نشده',
                    'title' => $factor->invoiceID ?: ('سفارش فروش #' . $factor->id),
                    'status' => $factor->settlement_status,
                    'amount' => $this->money($factor->fullPrice),
                    'counterparty' => optional($factor->customer)->name ?: '-',
                    'reference_type' => 'pishfactor',
                    'reference_id' => $factor->id,
                    'is_overdue' => $overdue,
                ];
            })
            ->filter(fn($event) => (float) $event['amount'] > 0)
            ->values();
    }

    private function expensePayableEvents($user, Carbon $baseDate, Carbon $toDate, bool $overdue): Collection
    {
        $query = OperationalExpense::with('expenseType')
            ->where('status', 'approved')
            ->whereIn('payment_status', ['unpaid', 'pending', 'partial'])
            ->whereNotNull('expense_date_en')
            ->when($overdue, fn($query) => $query->whereDate('expense_date_en', '<', $baseDate->toDateString()), fn($query) => $query->whereBetween('expense_date_en', [$baseDate->toDateString(), $toDate->toDateString()]))
            ->orderBy('expense_date_en');

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query->get()
            ->map(function ($expense) use ($overdue) {
                return [
                    'date' => optional($expense->expense_date_en)->format('Y-m-d'),
                    'direction' => 'outgoing',
                    'source' => 'operational_expense',
                    'source_label' => 'هزینه پرداخت نشده',
                    'title' => $expense->expense_number ?: ('هزینه #' . $expense->id),
                    'status' => $expense->payment_status,
                    'amount' => (float) $expense->total_amount,
                    'counterparty' => optional($expense->expenseType)->title ?: '-',
                    'reference_type' => 'operational_expense',
                    'reference_id' => $expense->id,
                    'is_overdue' => $overdue,
                ];
            })
            ->filter(fn($event) => (float) $event['amount'] > 0)
            ->values();
    }

    private function sourceSummary(Collection $events): Collection
    {
        return $events->groupBy('source')
            ->map(function ($items) {
                return [
                    'source' => $items->first()['source'],
                    'source_label' => $items->first()['source_label'],
                    'incoming_count' => $items->where('direction', 'incoming')->count(),
                    'incoming_amount' => (float) $items->where('direction', 'incoming')->sum('amount'),
                    'outgoing_count' => $items->where('direction', 'outgoing')->count(),
                    'outgoing_amount' => (float) $items->where('direction', 'outgoing')->sum('amount'),
                    'net' => round((float) $items->where('direction', 'incoming')->sum('amount') - (float) $items->where('direction', 'outgoing')->sum('amount'), 2),
                ];
            })
            ->values();
    }

    private function dailyRows(Collection $events, Carbon $baseDate, Carbon $toDate, float $openingBalance): Collection
    {
        $projectedBalance = $openingBalance;
        $rows = collect();
        $groups = $events->groupBy('date');

        foreach ($groups->sortKeys() as $date => $items) {
            if (!$date || Carbon::parse($date)->lt($baseDate) || Carbon::parse($date)->gt($toDate)) {
                continue;
            }

            $inflow = (float) $items->where('direction', 'incoming')->sum('amount');
            $outflow = (float) $items->where('direction', 'outgoing')->sum('amount');
            $net = round($inflow - $outflow, 2);
            $projectedBalance = round($projectedBalance + $net, 2);

            $rows->push([
                'date' => $date,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'net' => $net,
                'projected_balance' => $projectedBalance,
                'incoming_count' => $items->where('direction', 'incoming')->count(),
                'outgoing_count' => $items->where('direction', 'outgoing')->count(),
                'sources' => $items->pluck('source_label')->unique()->values()->implode('، '),
            ]);
        }

        return $rows;
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}
