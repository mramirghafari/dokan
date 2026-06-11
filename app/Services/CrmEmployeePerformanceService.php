<?php

namespace App\Services;

use App\Models\CrmCallLog;
use App\Models\CrmEmployeeCoachingPlan;
use App\Models\CrmEmployeePerformanceSnapshot;
use App\Models\CrmFollowup;
use App\Models\CrmOpportunity;
use App\Models\CrmServiceTicket;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CrmEmployeePerformanceService
{
    public function state($viewer, array $filters = []): array
    {
        [$periodStart, $periodEnd] = $this->period($filters);
        $roleScope = $this->roleScope($filters['role_scope'] ?? 'mixed');
        $selectedUserId = !empty($filters['user_id']) ? (int) $filters['user_id'] : null;

        $usersQuery = $this->scopedUsers($viewer)->orderBy('name');
        $selectUsers = (clone $usersQuery)->limit(200)->get();

        if ($selectedUserId) {
            $usersQuery->whereKey($selectedUserId);
        }

        $users = $usersQuery->limit(60)->get();
        $rows = $users->map(fn(User $user) => $this->calculateForUser($user, $viewer, $periodStart, $periodEnd, $roleScope));

        $snapshotQuery = CrmEmployeePerformanceSnapshot::query()->with(['user', 'employee'])
            ->whereDate('period_start', '>=', $periodStart->toDateString())
            ->whereDate('period_end', '<=', $periodEnd->toDateString())
            ->where('role_scope', $roleScope);

        $coachingQuery = CrmEmployeeCoachingPlan::query()->with(['user', 'coach', 'snapshot'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'normal', 'low')")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->latest('id');

        if ((int) $viewer->isGod !== 1) {
            $snapshotQuery->forOrganizations($viewer);
            $coachingQuery->forOrganizations($viewer);
        }

        if ($selectedUserId) {
            $snapshotQuery->where('user_id', $selectedUserId);
            $coachingQuery->where('user_id', $selectedUserId);
        }

        return [
            'filters' => [
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'role_scope' => $roleScope,
                'user_id' => $selectedUserId,
            ],
            'role_scopes' => CrmEmployeePerformanceSnapshot::ROLE_SCOPES,
            'coaching_types' => CrmEmployeeCoachingPlan::TYPES,
            'coaching_priorities' => CrmEmployeeCoachingPlan::PRIORITIES,
            'coaching_statuses' => CrmEmployeeCoachingPlan::STATUSES,
            'users' => $selectUsers,
            'rows' => $rows->sortBy('total_score')->values(),
            'summary' => $this->summary($rows),
            'snapshots' => $snapshotQuery->orderBy('total_score')->latest('calculated_at')->limit(30)->get(),
            'coachings' => $coachingQuery->limit(30)->get(),
        ];
    }

    public function refresh($viewer, array $data): int
    {
        [$periodStart, $periodEnd] = $this->period($data);
        $roleScope = $this->roleScope($data['role_scope'] ?? 'mixed');
        $selectedUserId = !empty($data['user_id']) ? (int) $data['user_id'] : null;
        $query = $this->scopedUsers($viewer)->orderBy('id');

        if ($selectedUserId) {
            $query->whereKey($selectedUserId);
        }

        $count = 0;
        foreach ($query->limit(150)->get() as $user) {
            $row = $this->calculateForUser($user, $viewer, $periodStart, $periodEnd, $roleScope);
            $this->persistSnapshot($row, $viewer);
            $count++;
        }

        return $count;
    }

    public function createCoaching($viewer, array $data): CrmEmployeeCoachingPlan
    {
        $user = $this->resolveUser($viewer, (int) $data['user_id']);
        $snapshot = null;

        if (!empty($data['performance_snapshot_id'])) {
            $snapshotQuery = CrmEmployeePerformanceSnapshot::query()->whereKey((int) $data['performance_snapshot_id'])->where('user_id', $user->id);
            if ((int) $viewer->isGod !== 1) {
                $snapshotQuery->forOrganizations($viewer);
            }
            $snapshot = $snapshotQuery->firstOrFail();
        }

        return CrmEmployeeCoachingPlan::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'performance_snapshot_id' => $snapshot?->id,
            'user_id' => $user->id,
            'employee_id' => $snapshot?->employee_id ?: $this->employeeIdForUser($user),
            'coach_user_id' => $viewer->id,
            'type' => $data['type'],
            'priority' => $data['priority'],
            'status' => 'open',
            'title' => $data['title'],
            'target_metric' => $data['target_metric'] ?? null,
            'target_value' => $data['target_value'] ?? null,
            'due_at' => !empty($data['due_at']) ? Carbon::parse($data['due_at']) : null,
            'action_plan' => $data['action_plan'] ?? null,
            'created_by' => $viewer->id,
            'updated_by' => $viewer->id,
        ]);
    }

    public function updateCoachingStatus($viewer, CrmEmployeeCoachingPlan $plan, array $data): CrmEmployeeCoachingPlan
    {
        $this->authorizePlan($viewer, $plan);
        $status = $data['status'];

        $plan->update([
            'status' => $status,
            'outcome' => $data['outcome'] ?? $plan->outcome,
            'closed_at' => in_array($status, ['done', 'canceled'], true) ? now() : null,
            'updated_by' => $viewer->id,
        ]);

        return $plan;
    }

    public function calculateForUser(User $subjectUser, $viewer, Carbon $periodStart, Carbon $periodEnd, string $roleScope = 'mixed'): array
    {
        $periodRange = [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()];
        $today = now()->toDateString();
        $userId = $subjectUser->id;

        $followups = $this->scoped(CrmFollowup::query(), $viewer)->where('assigned_user_id', $userId)->whereBetween('created_at', $periodRange);
        $followupsTotal = (clone $followups)->count();
        $followupsDone = (clone $followups)->where('status', 'done')->count();
        $followupsOverdue = $this->scoped(CrmFollowup::query(), $viewer)
            ->where('assigned_user_id', $userId)
            ->whereIn('status', ['open', 'in_progress'])
            ->whereDate('due_date_en', '<', $today)
            ->count();

        $opportunities = $this->scoped(CrmOpportunity::query(), $viewer)->where('assigned_user_id', $userId)->whereBetween('created_at', $periodRange);
        $opportunitiesTotal = (clone $opportunities)->count();
        $wonCount = (clone $opportunities)->where('status', 'won')->count();
        $lostCount = (clone $opportunities)->where('status', 'lost')->count();
        $wonAmount = (float) (clone $opportunities)->where('status', 'won')->sum('amount');
        $weightedPipeline = (float) $this->scoped(CrmOpportunity::query(), $viewer)
            ->where('assigned_user_id', $userId)
            ->where('status', 'open')
            ->selectRaw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')
            ->value('weighted_amount');

        $tickets = $this->scoped(CrmServiceTicket::query(), $viewer)->where('assigned_user_id', $userId)->whereBetween('created_at', $periodRange);
        $ticketsTotal = (clone $tickets)->count();
        $ticketsResolved = (clone $tickets)->whereIn('status', ['resolved', 'closed'])->count();
        $ticketsOverdue = $this->scoped(CrmServiceTicket::query(), $viewer)
            ->where('assigned_user_id', $userId)
            ->whereIn('status', ['open', 'pending'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();
        $satisfactionAvg = (float) (clone $tickets)->whereNotNull('satisfaction_score')->avg('satisfaction_score');

        $calls = $this->scoped(CrmCallLog::query(), $viewer)->where('assigned_user_id', $userId)->whereBetween('created_at', $periodRange);
        $callsTotal = (clone $calls)->count();
        $callsCompleted = (clone $calls)->where('status', 'completed')->count();
        $callsMissed = (clone $calls)->where('direction', 'missed')->count();
        $callQualityAvg = (float) (clone $calls)->whereNotNull('quality_score')->avg('quality_score');
        $callDurationSeconds = (int) (clone $calls)->sum('duration_seconds');

        $followupScore = $this->followupScore($followupsTotal, $followupsDone, $followupsOverdue);
        $salesScore = $this->salesScore($opportunitiesTotal, $wonCount, $lostCount, $weightedPipeline, $wonAmount);
        $supportScore = $this->supportScore($ticketsTotal, $ticketsResolved, $ticketsOverdue, $satisfactionAvg);
        $callScore = $this->callScore($callsTotal, $callsCompleted, $callsMissed, $callQualityAvg);
        $totalScore = $this->totalScore($roleScope, $followupScore, $salesScore, $supportScore, $callScore, [
            'followups' => $followupsTotal,
            'opportunities' => $opportunitiesTotal,
            'tickets' => $ticketsTotal,
            'calls' => $callsTotal,
        ]);

        $metrics = [
            'followups_total' => $followupsTotal,
            'followups_done' => $followupsDone,
            'followups_overdue' => $followupsOverdue,
            'opportunities_total' => $opportunitiesTotal,
            'opportunities_won' => $wonCount,
            'opportunities_lost' => $lostCount,
            'won_amount' => round($wonAmount, 2),
            'weighted_pipeline' => round($weightedPipeline, 2),
            'tickets_total' => $ticketsTotal,
            'tickets_resolved' => $ticketsResolved,
            'tickets_overdue' => $ticketsOverdue,
            'satisfaction_avg' => round($satisfactionAvg, 2),
            'calls_total' => $callsTotal,
            'calls_completed' => $callsCompleted,
            'calls_missed' => $callsMissed,
            'call_quality_avg' => round($callQualityAvg, 2),
            'call_duration_minutes' => round($callDurationSeconds / 60, 1),
        ];

        [$strengths, $risks, $recommendation] = $this->coachingSignals($metrics, $totalScore, $followupScore, $salesScore, $supportScore, $callScore);

        return [
            'tenant_id' => $this->tenantId($subjectUser),
            'organization_id' => $this->organizationId($subjectUser),
            'user_id' => $subjectUser->id,
            'employee_id' => $this->employeeIdForUser($subjectUser),
            'user' => $subjectUser,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'role_scope' => $roleScope,
            'total_score' => $totalScore,
            'sales_score' => $salesScore,
            'support_score' => $supportScore,
            'followup_score' => $followupScore,
            'call_score' => $callScore,
            'coaching_priority' => $this->priority($totalScore, $risks),
            'metrics' => $metrics,
            'strengths' => $strengths,
            'risks' => $risks,
            'recommendation' => $recommendation,
            'calculated_at' => now(),
        ];
    }

    private function persistSnapshot(array $row, $viewer): CrmEmployeePerformanceSnapshot
    {
        return CrmEmployeePerformanceSnapshot::updateOrCreate(
            [
                'user_id' => $row['user_id'],
                'period_start' => $row['period_start'],
                'period_end' => $row['period_end'],
                'role_scope' => $row['role_scope'],
            ],
            [
                'tenant_id' => $row['tenant_id'],
                'organization_id' => $row['organization_id'],
                'employee_id' => $row['employee_id'],
                'total_score' => $row['total_score'],
                'sales_score' => $row['sales_score'],
                'support_score' => $row['support_score'],
                'followup_score' => $row['followup_score'],
                'call_score' => $row['call_score'],
                'coaching_priority' => $row['coaching_priority'],
                'metrics' => $row['metrics'],
                'strengths' => $row['strengths'],
                'risks' => $row['risks'],
                'recommendation' => $row['recommendation'],
                'calculated_at' => $row['calculated_at'],
                'created_by' => $viewer->id,
                'updated_by' => $viewer->id,
            ]
        );
    }

    private function period(array $filters): array
    {
        $periodStart = !empty($filters['period_start']) ? Carbon::parse($filters['period_start']) : now()->subDays(29);
        $periodEnd = !empty($filters['period_end']) ? Carbon::parse($filters['period_end']) : now();

        if ($periodStart->gt($periodEnd)) {
            [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
        }

        return [$periodStart->startOfDay(), $periodEnd->endOfDay()];
    }

    private function roleScope(?string $roleScope): string
    {
        return array_key_exists($roleScope, CrmEmployeePerformanceSnapshot::ROLE_SCOPES) ? $roleScope : 'mixed';
    }

    private function scopedUsers($viewer): Builder
    {
        $query = User::query()->select(['id', 'name', 'tenant_id', 'tenants_id', 'organization_id', 'personalID', 'isActive', 'isGod'])->where('isActive', 1);

        if ((int) $viewer->isGod !== 1) {
            $query->forOrganizations($viewer);
        }

        return $query;
    }

    private function scoped($query, $viewer)
    {
        if ((int) $viewer->isGod !== 1) {
            $query->forOrganizations($viewer);
        }

        return $query;
    }

    private function resolveUser($viewer, int $userId): User
    {
        return $this->scopedUsers($viewer)->whereKey($userId)->firstOrFail();
    }

    private function authorizePlan($viewer, CrmEmployeeCoachingPlan $plan): void
    {
        if ((int) $viewer->isGod === 1) {
            return;
        }

        abort_unless(CrmEmployeeCoachingPlan::query()->whereKey($plan->id)->forOrganizations($viewer)->exists(), 403);
    }

    private function tenantId(User $user): ?int
    {
        return $user->tenant_id ?: $user->tenants_id;
    }

    private function organizationId(User $user): ?int
    {
        $value = $user->organization_id;
        $decoded = is_string($value) ? json_decode($value, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $value !== null ? (int) $value : null;
    }

    private function employeeIdForUser(User $user): ?int
    {
        if (!$user->personalID || !Schema::hasColumn('employees', 'personalID')) {
            return null;
        }

        $query = Employee::query()->where('personalID', $user->personalID);

        if ($this->tenantId($user) && Schema::hasColumn('employees', 'tenant_id')) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        if ($this->organizationId($user)) {
            $query->where('organization_id', $this->organizationId($user));
        }

        return $query->value('id');
    }

    private function followupScore(int $total, int $done, int $overdue): float
    {
        if ($total === 0 && $overdue === 0) {
            return 0.0;
        }

        $doneRate = $total > 0 ? $done / $total : 0;
        return $this->clamp(($doneRate * 72) + min($total, 20) * 1.4 - min($overdue * 8, 40));
    }

    private function salesScore(int $total, int $won, int $lost, float $weightedPipeline, float $wonAmount): float
    {
        if ($total === 0 && $weightedPipeline <= 0) {
            return 0.0;
        }

        $closed = $won + $lost;
        $winRate = $closed > 0 ? $won / $closed : 0;
        $pipelineSignal = min($weightedPipeline / 10000000, 20);
        $wonSignal = min($won * 8, 24) + min($wonAmount / 20000000, 12);

        return $this->clamp(($winRate * 44) + $pipelineSignal + $wonSignal + min($total, 10) * 2);
    }

    private function supportScore(int $total, int $resolved, int $overdue, float $satisfactionAvg): float
    {
        if ($total === 0 && $overdue === 0) {
            return 0.0;
        }

        $resolutionRate = $total > 0 ? $resolved / $total : 0;
        $satisfactionSignal = $satisfactionAvg > 0 ? min($satisfactionAvg, 5) * 7 : 14;

        return $this->clamp(($resolutionRate * 52) + $satisfactionSignal + min($total, 12) * 1.5 - min($overdue * 10, 45));
    }

    private function callScore(int $total, int $completed, int $missed, float $qualityAvg): float
    {
        if ($total === 0) {
            return 0.0;
        }

        $completionRate = $completed / $total;
        $qualitySignal = $qualityAvg > 0 ? min($qualityAvg, 5) * 7 : 15;

        return $this->clamp(($completionRate * 46) + $qualitySignal + min($total, 18) * 1.2 - min($missed * 5, 35));
    }

    private function totalScore(string $roleScope, float $followup, float $sales, float $support, float $call, array $counts): float
    {
        $weights = match ($roleScope) {
            'sales' => ['sales' => 0.45, 'followup' => 0.25, 'call' => 0.20, 'support' => 0.10],
            'support' => ['support' => 0.45, 'call' => 0.25, 'followup' => 0.20, 'sales' => 0.10],
            'followup' => ['followup' => 0.50, 'call' => 0.20, 'sales' => 0.15, 'support' => 0.15],
            default => ['sales' => 0.30, 'support' => 0.25, 'followup' => 0.25, 'call' => 0.20],
        };

        $scoreMap = ['sales' => $sales, 'support' => $support, 'followup' => $followup, 'call' => $call];
        $countMap = ['sales' => $counts['opportunities'], 'support' => $counts['tickets'], 'followup' => $counts['followups'], 'call' => $counts['calls']];
        $weighted = 0.0;
        $activeWeights = 0.0;

        foreach ($weights as $key => $weight) {
            if ($countMap[$key] > 0 || $scoreMap[$key] > 0) {
                $weighted += $scoreMap[$key] * $weight;
                $activeWeights += $weight;
            }
        }

        return $activeWeights > 0 ? round($weighted / $activeWeights, 2) : 0.0;
    }

    private function coachingSignals(array $metrics, float $totalScore, float $followupScore, float $salesScore, float $supportScore, float $callScore): array
    {
        $strengths = [];
        $risks = [];

        if ($followupScore >= 75) {
            $strengths[] = 'پیگیری ها منظم و به موقع بسته می شوند.';
        }
        if ($salesScore >= 70) {
            $strengths[] = 'قیف فروش و نرخ برد وضعیت قابل قبول دارد.';
        }
        if ($supportScore >= 70) {
            $strengths[] = 'رسیدگی به تیکت ها و رضایت مشتریان مناسب است.';
        }
        if ($callScore >= 70) {
            $strengths[] = 'کیفیت یا تکمیل تماس ها وضعیت خوبی دارد.';
        }

        if ($metrics['followups_overdue'] > 0) {
            $risks[] = 'پیگیری معوق دارد و باید برنامه روزانه اصلاح شود.';
        }
        if ($metrics['tickets_overdue'] > 0) {
            $risks[] = 'SLA تیکت های خدماتی در خطر است.';
        }
        if ($metrics['calls_missed'] > 0) {
            $risks[] = 'تماس از دست رفته ثبت شده و نیاز به بازتماس دارد.';
        }
        if ($totalScore > 0 && $totalScore < 55) {
            $risks[] = 'امتیاز کل پایین است و جلسه coaching فوری پیشنهاد می شود.';
        }
        if (($metrics['followups_total'] + $metrics['opportunities_total'] + $metrics['tickets_total'] + $metrics['calls_total']) === 0) {
            $risks[] = 'داده عملکرد کافی برای این بازه ثبت نشده است.';
        }

        if (empty($strengths)) {
            $strengths[] = 'برای استخراج نقاط قوت، داده بیشتری از فعالیت های CRM لازم است.';
        }

        $recommendation = $this->recommendation($metrics, $totalScore, $followupScore, $salesScore, $supportScore, $callScore);

        return [$strengths, $risks, $recommendation];
    }

    private function recommendation(array $metrics, float $totalScore, float $followupScore, float $salesScore, float $supportScore, float $callScore): string
    {
        $lowest = collect([
            'followup' => $followupScore,
            'sales' => $salesScore,
            'support' => $supportScore,
            'call' => $callScore,
        ])->filter(fn($score) => $score > 0)->sort()->keys()->first();

        return match ($lowest) {
            'followup' => 'جلسه coaching روی بستن پیگیری های معوق، برنامه ریزی اقدام بعدی و ثبت outcome برگزار شود.',
            'sales' => 'روی qualification فرصت ها، next action فروش و تبدیل فرصت های باز به برد تمرکز شود.',
            'support' => 'SLA تیکت ها، پاسخ اول و ثبت resolution استاندارد بازبینی شود.',
            'call' => 'کیفیت مکالمه، بازتماس missed call و ثبت نتیجه تماس کنترل شود.',
            default => $totalScore === 0 ? 'برای این کاربر فعالیت CRM کافی ثبت نشده؛ ابتدا مسئولیت ها و ثبت رخدادها تکمیل شود.' : 'عملکرد پایدار است؛ coaching سبک برای حفظ ریتم کافی است.',
        };
    }

    private function priority(float $score, array $risks): string
    {
        if ($score > 0 && $score < 55) {
            return 'high';
        }

        if ($score < 70 || count($risks) >= 2) {
            return 'medium';
        }

        if ($score >= 85) {
            return 'low';
        }

        return 'normal';
    }

    private function summary(Collection $rows): array
    {
        return [
            'users' => $rows->count(),
            'average_score' => $rows->count() ? round((float) $rows->avg('total_score'), 2) : 0,
            'high_priority' => $rows->where('coaching_priority', 'high')->count(),
            'medium_priority' => $rows->where('coaching_priority', 'medium')->count(),
            'best_score' => $rows->max('total_score') ?: 0,
            'lowest_score' => $rows->min('total_score') ?: 0,
        ];
    }

    private function clamp(float $score): float
    {
        return round(max(0, min(100, $score)), 2);
    }
}
