<?php

namespace App\Services;

use App\Models\CrmFollowup;
use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\CrmSalesBoardCard;
use App\Models\Pishfactor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmDashboardService
{
    public function forUser($user): array
    {
        $cards = $this->scope(CrmSalesBoardCard::query(), $user);
        $opportunities = $this->scope(CrmOpportunity::query(), $user);
        $followups = $this->scope(CrmFollowup::query(), $user);
        $leads = $this->scope(CrmLead::query(), $user);

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $openCards = (clone $cards)->whereIn('status', ['open', 'in_progress'])->count();
        $overdueCards = (clone $cards)->whereIn('status', ['open', 'in_progress'])->whereDate('next_action_date_en', '<', $today)->count();
        $weightedPipeline = (float) (clone $opportunities)->where('status', 'open')
            ->selectRaw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')
            ->value('weighted_amount');
        $openOpportunities = (clone $opportunities)->where('status', 'open')->count();
        $wonAmount = (float) (clone $opportunities)->where('status', 'won')->whereDate('closed_at', '>=', $monthStart)->sum('amount');
        $wonCount = (clone $opportunities)->where('status', 'won')->whereDate('closed_at', '>=', $monthStart)->count();
        $lostCount = (clone $opportunities)->where('status', 'lost')->whereDate('closed_at', '>=', $monthStart)->count();
        $closedCount = $wonCount + $lostCount;

        $stageRows = $this->stageRows($opportunities);
        $stageWeightedSum = round($stageRows->sum('weighted_amount'), 2);

        $openFollowups = (clone $followups)->whereIn('status', ['open', 'in_progress'])->count();
        $todayFollowups = (clone $followups)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', $today)->count();
        $overdueFollowups = (clone $followups)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', '<', $today)->count();
        $doneFollowups = (clone $followups)->where('status', 'done')->whereDate('completed_at', '>=', $monthStart)->count();

        $forecast = $this->monthlyForecast($opportunities, $wonAmount);
        $forecast['reconcile'] = $this->forecastReconcile($weightedPipeline, $stageWeightedSum, $forecast);

        $userRows = $this->userRows($cards, $user);

        return [
            'summary' => [
                'open_cards' => $openCards,
                'overdue_cards' => $overdueCards,
                'weighted_pipeline' => $weightedPipeline,
                'open_opportunities' => $openOpportunities,
                'won_amount' => $wonAmount,
                'win_rate' => $closedCount > 0 ? round(($wonCount / $closedCount) * 100, 1) : 0,
                'open_followups' => $openFollowups,
                'today_followups' => $todayFollowups,
                'overdue_followups' => $overdueFollowups,
                'done_followups' => $doneFollowups,
                'forecast_total' => $forecast['forecast_total'],
                'forecast_committed' => $forecast['committed_won'],
                'forecast_weighted_closing' => $forecast['weighted_closing_month'],
            ],
            'forecast' => $forecast,
            'stage_rows' => $stageRows,
            'stage_aging' => $this->stageAging($opportunities),
            'lead_sources' => $this->leadSources($leads, $opportunities),
            'ltv' => $this->simpleLtv($opportunities, $user),
            'user_rows' => $userRows,
            'alerts' => $this->alerts($overdueCards, $overdueFollowups, $openOpportunities, $forecast),
            'drilldowns' => $this->drilldowns(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function stageRows($opportunities)
    {
        return (clone $opportunities)
            ->where('status', 'open')
            ->select(
                'stage',
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(amount), 0) as amount'),
                DB::raw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount'),
                DB::raw('AVG(DATEDIFF(CURDATE(), COALESCE(updated_at, created_at))) as avg_days_in_stage')
            )
            ->groupBy('stage')
            ->get()
            ->map(function ($row) {
                return [
                    'stage' => $row->stage,
                    'title' => CrmOpportunity::STAGES[$row->stage] ?? $row->stage,
                    'count' => (int) $row->count,
                    'amount' => (float) $row->amount,
                    'weighted_amount' => (float) $row->weighted_amount,
                    'avg_days_in_stage' => round((float) $row->avg_days_in_stage, 1),
                    'url' => route('crm.opportunities.index', ['status' => 'open', 'stage' => $row->stage]),
                ];
            })
            ->sortBy(fn (array $row) => array_search($row['stage'], array_keys(CrmOpportunity::STAGES), true) ?: 99)
            ->values();
    }

    private function monthlyForecast($opportunities, float $committedWon): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $closingQuery = (clone $opportunities)
            ->where('status', 'open')
            ->whereBetween('expected_close_date_en', [$monthStart, $monthEnd]);

        $weightedClosingMonth = (float) (clone $closingQuery)
            ->selectRaw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')
            ->value('weighted_amount');

        $rawClosingMonth = (float) (clone $closingQuery)->sum('amount');
        $closingCount = (clone $closingQuery)->count();

        $trend = collect(range(5, 0))->map(function (int $monthsAgo) use ($opportunities) {
            $month = now()->subMonths($monthsAgo);
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            $won = (float) (clone $opportunities)
                ->where('status', 'won')
                ->whereBetween('closed_at', [$start, $end . ' 23:59:59'])
                ->sum('amount');

            $weighted = (float) (clone $opportunities)
                ->where('status', 'open')
                ->whereBetween('expected_close_date_en', [$start, $end])
                ->selectRaw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')
                ->value('weighted_amount');

            $isCurrent = $monthsAgo === 0;

            return [
                'label' => verta($start)->format('Y/m'),
                'won' => $won,
                'weighted_pipeline' => $weighted,
                'forecast' => $isCurrent ? round($won + $weighted, 2) : round($won, 2),
            ];
        })->values()->all();

        return [
            'month_label' => verta($monthStart)->format('Y F'),
            'committed_won' => $committedWon,
            'weighted_closing_month' => $weightedClosingMonth,
            'raw_closing_month' => $rawClosingMonth,
            'closing_count' => $closingCount,
            'forecast_total' => round($committedWon + $weightedClosingMonth, 2),
            'trend' => $trend,
        ];
    }

    private function forecastReconcile(float $openWeighted, float $stageWeightedSum, array $forecast): array
    {
        $pipelineDelta = round($openWeighted - $stageWeightedSum, 2);
        $openRaw = $forecast['raw_closing_month'] ?? 0;
        $weightedClosing = $forecast['weighted_closing_month'] ?? 0;
        $coverage = $openRaw > 0 ? round(($weightedClosing / $openRaw) * 100, 1) : 0;

        return [
            'open_weighted_total' => $openWeighted,
            'stage_weighted_sum' => $stageWeightedSum,
            'pipeline_delta' => $pipelineDelta,
            'pipeline_aligned' => abs($pipelineDelta) < 1,
            'month_coverage_percent' => $coverage,
            'message' => abs($pipelineDelta) < 1
                ? 'ارزش وزنی قیف با جمع مراحل هم‌خوان است.'
                : 'اختلاف جزئی بین pipeline کل و جمع مراحل — احتمالاً فرصت بدون stage باز است.',
        ];
    }

    private function stageAging($opportunities): array
    {
        $rows = (clone $opportunities)
            ->where('status', 'open')
            ->get(['stage', 'updated_at', 'created_at']);

        $buckets = ['0_7' => 'تا ۷ روز', '8_14' => '۸–۱۴ روز', '15_30' => '۱۵–۳۰ روز', '30_plus' => 'بیش از ۳۰ روز'];
        $byStage = [];

        foreach (array_keys(CrmOpportunity::STAGES) as $stage) {
            if (in_array($stage, ['won', 'lost'], true)) {
                continue;
            }

            $byStage[$stage] = [
                'stage' => $stage,
                'title' => CrmOpportunity::STAGES[$stage],
                'buckets' => array_fill_keys(array_keys($buckets), 0),
                'total' => 0,
                'avg_days' => 0,
                'url' => route('crm.opportunities.index', ['status' => 'open', 'stage' => $stage]),
            ];
        }

        $stageDays = [];

        foreach ($rows as $row) {
            if (!isset($byStage[$row->stage])) {
                continue;
            }

            $days = (int) Carbon::parse($row->updated_at ?: $row->created_at)->diffInDays(now());
            $bucket = match (true) {
                $days <= 7 => '0_7',
                $days <= 14 => '8_14',
                $days <= 30 => '15_30',
                default => '30_plus',
            };

            $byStage[$row->stage]['buckets'][$bucket]++;
            $byStage[$row->stage]['total']++;
            $stageDays[$row->stage][] = $days;
        }

        $stages = collect($byStage)
            ->filter(fn (array $row) => $row['total'] > 0)
            ->map(function (array $row) use ($stageDays) {
                $days = $stageDays[$row['stage']] ?? [];
                $row['avg_days'] = count($days) > 0 ? round(array_sum($days) / count($days), 1) : 0;
                $row['stale_count'] = ($row['buckets']['15_30'] ?? 0) + ($row['buckets']['30_plus'] ?? 0);

                return $row;
            })
            ->values()
            ->all();

        return [
            'bucket_labels' => $buckets,
            'stages' => $stages,
        ];
    }

    private function leadSources($leads, $opportunities): array
    {
        $leadRows = (clone $leads)
            ->select('source', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        $oppByLead = (clone $opportunities)
            ->whereNotNull('source_lead_id')
            ->where('status', 'won')
            ->select('source_lead_id', DB::raw('COALESCE(SUM(amount), 0) as won_amount'))
            ->groupBy('source_lead_id')
            ->pluck('won_amount', 'source_lead_id');

        $leadIdsBySource = (clone $leads)
            ->whereIn('status', ['converted', 'open'])
            ->get(['id', 'source'])
            ->groupBy('source');

        return $leadRows->map(function ($row) use ($leadIdsBySource, $oppByLead) {
            $source = $row->source ?: 'unknown';
            $ids = ($leadIdsBySource[$source] ?? collect())->pluck('id');
            $wonAmount = $ids->sum(fn ($id) => (float) ($oppByLead[$id] ?? 0));

            return [
                'source' => $source,
                'title' => CrmLead::SOURCES[$source] ?? ($source === 'unknown' ? 'نامشخص' : $source),
                'total' => (int) $row->total,
                'converted' => (int) $row->converted,
                'conversion_rate' => $row->total > 0 ? round(($row->converted / $row->total) * 100, 1) : 0,
                'won_amount' => $wonAmount,
                'url' => route('crm.leads.index', ['source' => $source]),
            ];
        })->values()->all();
    }

    private function simpleLtv($opportunities, $user): array
    {
        $customerIds = (clone $opportunities)
            ->where('status', 'won')
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        if ($customerIds->isEmpty()) {
            return [
                'customer_count' => 0,
                'avg_ltv' => 0,
                'median_ltv' => 0,
                'total_revenue' => 0,
                'message' => 'هنوز مشتری با فرصت برده‌شده برای محاسبه LTV وجود ندارد.',
            ];
        }

        $invoiceQuery = Pishfactor::query()->whereIn('customer_id', $customerIds);

        if ((int) $user->isGod !== 1) {
            $invoiceQuery->forOrganizations($user);
        }

        if (Schema::hasColumn('pishfactors', 'status')) {
            $invoiceQuery->whereIn('status', [1, 4]);
        }

        $perCustomer = $invoiceQuery
            ->select('customer_id', DB::raw($this->numericSumExpression('fullPrice') . ' as revenue'))
            ->groupBy('customer_id')
            ->pluck('revenue')
            ->map(fn ($value) => (float) $value)
            ->filter(fn ($value) => $value > 0)
            ->values();

        if ($perCustomer->isEmpty()) {
            return [
                'customer_count' => $customerIds->count(),
                'avg_ltv' => 0,
                'median_ltv' => 0,
                'total_revenue' => 0,
                'message' => 'فرصت برده‌شده دارید اما فاکتور تاییدشده‌ای برای LTV یافت نشد.',
            ];
        }

        $sorted = $perCustomer->sort()->values();
        $count = $sorted->count();
        $median = $count % 2 === 1
            ? $sorted[intdiv($count, 2)]
            : ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2;

        return [
            'customer_count' => $customerIds->count(),
            'paying_customers' => $count,
            'avg_ltv' => round($sorted->avg(), 0),
            'median_ltv' => round($median, 0),
            'total_revenue' => round($sorted->sum(), 0),
            'message' => 'میانگین درآمد فاکتورهای تاییدشده برای مشتریان با فرصت برده‌شده.',
        ];
    }

    private function userRows($cards, $user)
    {
        $userRows = (clone $cards)
            ->select(
                'assigned_user_id',
                DB::raw('COUNT(*) as cards_count'),
                DB::raw('COALESCE(SUM(CASE WHEN status IN ("done", "won") THEN 1 ELSE 0 END), 0) as done_count'),
                DB::raw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')
            )
            ->whereNotNull('assigned_user_id')
            ->groupBy('assigned_user_id')
            ->orderByDesc('weighted_amount')
            ->limit(8)
            ->get();

        $userNames = User::query()->whereIn('id', $userRows->pluck('assigned_user_id'))->pluck('name', 'id');

        return $userRows->map(function ($row) use ($userNames) {
            return [
                'user_id' => (int) $row->assigned_user_id,
                'name' => $userNames[$row->assigned_user_id] ?? 'کاربر حذف شده',
                'cards_count' => (int) $row->cards_count,
                'done_count' => (int) $row->done_count,
                'weighted_amount' => (float) $row->weighted_amount,
                'url' => route('crm.opportunities.index', ['status' => 'open', 'assigned_user_id' => $row->assigned_user_id]),
            ];
        });
    }

    private function drilldowns(): array
    {
        return [
            'open_cards' => route('crm.sales-boards.index'),
            'overdue_cards' => route('crm.sales-boards.index'),
            'weighted_pipeline' => route('crm.opportunities.index', ['status' => 'open']),
            'open_opportunities' => route('crm.opportunities.index', ['status' => 'open']),
            'today_followups' => route('crm.followups.index', ['due_bucket' => 'today']),
            'overdue_followups' => route('crm.followups.index', ['due_bucket' => 'overdue']),
            'open_followups' => route('crm.followups.index', ['status' => 'open']),
            'won_month' => route('crm.opportunities.index', ['status' => 'won']),
            'forecast_month' => route('crm.opportunities.index', ['status' => 'open', 'close_month' => 'current']),
            'stale_opportunities' => route('crm.opportunities.index', ['status' => 'open', 'stale' => '1']),
        ];
    }

    private function scope($query, $user)
    {
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function alerts(int $overdueCards, int $overdueFollowups, int $openOpportunities, array $forecast): array
    {
        $alerts = collect();

        if ($overdueCards > 0) {
            $alerts->push([
                'type' => 'danger',
                'title' => 'کارت‌های عقب‌افتاده',
                'body' => number_format($overdueCards) . ' کارت کاریز از تاریخ اقدام بعدی عبور کرده است.',
                'url' => route('crm.sales-boards.index'),
            ]);
        }

        if ($overdueFollowups > 0) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'پیگیری‌های معوق',
                'body' => number_format($overdueFollowups) . ' پیگیری CRM هنوز بسته نشده است.',
                'url' => route('crm.followups.index', ['due_bucket' => 'overdue']),
            ]);
        }

        if ($openOpportunities === 0) {
            $alerts->push([
                'type' => 'info',
                'title' => 'قیف فروش خالی',
                'body' => 'فرصت فروش باز برای forecast دیده نشد.',
                'url' => route('crm.opportunities.index'),
            ]);
        } elseif (($forecast['closing_count'] ?? 0) === 0) {
            $alerts->push([
                'type' => 'info',
                'title' => 'تاریخ بستن ماه جاری خالی است',
                'body' => 'برای forecast دقیق‌تر، تاریخ بستن پیش‌بینی‌شده فرصت‌های باز را تکمیل کنید.',
                'url' => route('crm.opportunities.index', ['status' => 'open']),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => 'وضعیت CRM',
                'body' => 'هشدار بحرانی برای کارت‌ها و پیگیری‌های باز دیده نشد.',
                'url' => null,
            ]);
        }

        return $alerts->all();
    }

    private function numericSumExpression(string $column): string
    {
        return "SUM(CAST(REPLACE(COALESCE({$column}, '0'), ',', '') AS DECIMAL(18,2)))";
    }
}
