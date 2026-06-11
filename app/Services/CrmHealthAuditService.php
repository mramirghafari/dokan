<?php

namespace App\Services;

use App\Models\CrmCallLog;
use App\Models\CrmCollaborationComment;
use App\Models\CrmFollowup;
use App\Models\CrmHealthSnapshot;
use App\Models\CrmIntegrationSyncLog;
use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\CrmServiceTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmHealthAuditService
{
    public function state(?User $user = null): array
    {
        $audit = $this->audit($user);
        $snapshots = CrmHealthSnapshot::query()->latest('generated_at')->limit(20);

        if ($user && (int) $user->isGod !== 1) {
            $snapshots->forOrganizations($user);
        }

        return $audit + [
            'snapshots' => $snapshots->get(),
            'risk_levels' => CrmHealthSnapshot::RISK_LEVELS,
        ];
    }

    public function audit(?User $user = null): array
    {
        $summary = [
            'open_followups' => $this->scoped(CrmFollowup::query(), $user)->whereIn('status', ['open', 'in_progress'])->count(),
            'overdue_followups' => $this->scoped(CrmFollowup::query(), $user)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', '<', now()->toDateString())->count(),
            'open_opportunities' => $this->scoped(CrmOpportunity::query(), $user)->where('status', 'open')->count(),
            'stale_opportunities' => $this->scoped(CrmOpportunity::query(), $user)->where('status', 'open')->where(function ($query) {
                $query->whereNull('next_action_date_en')->orWhereDate('next_action_date_en', '<', now()->subDays(7)->toDateString());
            })->count(),
            'open_tickets' => $this->scoped(CrmServiceTicket::query(), $user)->whereIn('status', ['open', 'pending'])->count(),
            'overdue_tickets' => $this->scoped(CrmServiceTicket::query(), $user)->whereIn('status', ['open', 'pending'])->whereNotNull('due_at')->where('due_at', '<', now())->count(),
            'missed_calls_today' => $this->scoped(CrmCallLog::query(), $user)->where('direction', 'missed')->whereDate('call_started_at', now()->toDateString())->count(),
            'failed_integrations_24h' => $this->scoped(CrmIntegrationSyncLog::query(), $user)->where('status', 'failed')->where('created_at', '>=', now()->subDay())->count(),
        ];

        $issues = collect([
            $this->issue('overdue_followups', 'پیگیری های معوق', $summary['overdue_followups'], 'high', 'پیگیری های باز که تاریخ سررسیدشان گذشته باید تعیین تکلیف شوند.'),
            $this->issue('stale_opportunities', 'فرصت های بدون اقدام تازه', $summary['stale_opportunities'], 'medium', 'فرصت های باز بدون تاریخ اقدام معتبر forecast را ضعیف می کنند.'),
            $this->issue('overdue_tickets', 'تیکت های SLA گذشته', $summary['overdue_tickets'], 'high', 'تیکت های open/pending با due_at گذشته باید escalation شوند.'),
            $this->issue('failed_integrations_24h', 'خطای integration در 24 ساعت اخیر', $summary['failed_integrations_24h'], 'medium', 'خطاهای provider باید از sync log بررسی و retry شوند.'),
            $this->issue('crm_unscoped_records', 'رکوردهای CRM بدون tenant/organization', $this->unscopedRecords($user, $this->auditTables('crm_tables'), false), 'critical', 'رکورد CRM بدون scope خطر نشت داده بین پنل ها دارد.'),
            $this->issue('erp_core_unscoped_records', 'رکوردهای هسته ERP بدون tenant', $this->unscopedRecords($user, $this->auditTables('erp_core_tables'), true), 'critical', 'مشتری/فاکتور/کالا بدون tenant_id باید قبل از production scale اصلاح شوند.'),
            $this->issue('bi_unscoped_records', 'رکوردهای BI بدون tenant', $this->unscopedRecords($user, $this->auditTables('bi_tables'), true), 'high', 'خلاصه و گزارش BI بدون tenant باعث KPI اشتباه در داشبورد می شود.'),
            $this->issue('orphan_customers', 'ارجاع به مشتری حذف شده', $this->orphanCustomerReferences($user), 'high', 'رکوردهای CRM با customer_id نامعتبر باید پاکسازی یا به مشتری درست وصل شوند.'),
            $this->issue('orphan_users', 'ارجاع به کاربر حذف شده', $this->orphanUserReferences($user), 'medium', 'رکوردهای CRM بدون کاربر معتبر باعث خطای کارتابل و گزارش عملکرد می شوند.'),
            $this->issue('invalid_probability', 'احتمال فروش خارج از بازه', $this->invalidOpportunityProbability($user), 'critical', 'probability_percent باید بین 0 تا 100 باشد تا pipeline وزنی اشتباه نشود.'),
        ])->filter(fn($issue) => $issue['count'] > 0)->values()->all();

        $score = $this->score($issues);

        return [
            'summary' => $summary,
            'issues' => $issues,
            'recommendations' => $this->recommendations($issues),
            'health_score' => $score,
            'risk_level' => $this->riskLevel($score, $issues),
            'generated_at' => now(),
            'scope_label' => $this->scopeLabel($user),
        ];
    }

    public function persist(?User $user = null): CrmHealthSnapshot
    {
        $audit = $this->audit($user);

        return CrmHealthSnapshot::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'scope_label' => $audit['scope_label'],
            'health_score' => $audit['health_score'],
            'risk_level' => $audit['risk_level'],
            'summary' => $audit['summary'],
            'issues' => $audit['issues'],
            'recommendations' => $audit['recommendations'],
            'generated_at' => $audit['generated_at'],
            'generated_by' => $user?->id,
        ]);
    }

    private function scoped(Builder $query, ?User $user): Builder
    {
        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function issue(string $key, string $title, int $count, string $severity, string $recommendation): array
    {
        return compact('key', 'title', 'count', 'severity', 'recommendation');
    }

    public function tenantIsolationAudit(?User $user = null): array
    {
        $domains = [
            'crm' => $this->auditTables('crm_tables'),
            'erp_core' => $this->auditTables('erp_core_tables'),
            'bi' => $this->auditTables('bi_tables'),
        ];

        $breakdown = [];

        foreach ($domains as $domain => $tables) {
            $tenantOnly = in_array($domain, ['erp_core', 'bi'], true);
            $breakdown[$domain] = collect($tables)
                ->mapWithKeys(fn (string $table) => [$table => $this->unscopedCountForTable($table, $user, $tenantOnly)])
                ->filter(fn (int $count) => $count > 0)
                ->all();
        }

        $total = collect($breakdown)->flatten()->sum();

        return [
            'total' => (int) $total,
            'breakdown' => $breakdown,
            'passed' => $total === 0,
            'generated_at' => now(),
            'scope_label' => $this->scopeLabel($user),
        ];
    }

    private function auditTables(string $key): array
    {
        return array_values(array_filter(
            config('erp_scale.tenant_scope.audit.' . $key, []),
            fn (string $table) => Schema::hasTable($table)
        ));
    }

    private function unscopedRecords(?User $user, array $tables, bool $tenantOnly): int
    {
        return collect($tables)->sum(function ($table) use ($user, $tenantOnly) {
            return $this->unscopedCountForTable($table, $user, $tenantOnly);
        });
    }

    private function unscopedCountForTable(string $table, ?User $user, bool $tenantOnly = false): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull($table . '.deleted_at');
        }

        $this->scopeTable($query, $table, $user);

        if ($tenantOnly) {
            $hasTenantId = Schema::hasColumn($table, 'tenant_id');
            $hasLegacyTenantId = Schema::hasColumn($table, 'tenants_id');

            if (!$hasTenantId && !$hasLegacyTenantId) {
                return 0;
            }

            $query->where(function ($inner) use ($table, $hasTenantId, $hasLegacyTenantId) {
                if ($hasTenantId) {
                    $inner->whereNull($table . '.tenant_id');
                }

                if ($hasLegacyTenantId) {
                    $method = $hasTenantId ? 'whereNull' : 'whereNull';
                    $inner->{$method}($table . '.tenants_id');
                }
            });

            return (int) $query->count();
        }

        if (Schema::hasColumn($table, 'tenant_id') && Schema::hasColumn($table, 'organization_id')) {
            $query->whereNull('tenant_id')->whereNull('organization_id');
        } elseif (Schema::hasColumn($table, 'organization_id')) {
            $query->whereNull('organization_id');
        } elseif (Schema::hasColumn($table, 'tenant_id')) {
            $query->whereNull('tenant_id');
        } elseif (Schema::hasColumn($table, 'tenants_id')) {
            $query->whereNull('tenants_id');
        } else {
            return 0;
        }

        return (int) $query->count();
    }

    private function orphanCustomerReferences(?User $user): int
    {
        if (!Schema::hasTable('customers')) {
            return 0;
        }

        return collect(['crm_followups', 'crm_opportunities', 'crm_service_tickets', 'crm_call_logs', 'crm_leads'])->sum(function ($table) use ($user) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'customer_id')) {
                return 0;
            }

            $query = DB::table($table)
                ->leftJoin('customers', $table . '.customer_id', '=', 'customers.id')
                ->whereNotNull($table . '.customer_id')
                ->whereNull('customers.id');

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull($table . '.deleted_at');
            }

            $this->scopeTable($query, $table, $user);

            return (int) $query->count();
        });
    }

    private function orphanUserReferences(?User $user): int
    {
        return collect([
            ['crm_followups', 'assigned_user_id'],
            ['crm_opportunities', 'assigned_user_id'],
            ['crm_service_tickets', 'assigned_user_id'],
            ['crm_call_logs', 'assigned_user_id'],
            ['crm_leads', 'owner_user_id'],
            ['crm_collaboration_mentions', 'mentioned_user_id'],
        ])->sum(function ($pair) use ($user) {
            [$table, $column] = $pair;
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                return 0;
            }

            $query = DB::table($table)
                ->leftJoin('users', $table . '.' . $column, '=', 'users.id')
                ->whereNotNull($table . '.' . $column)
                ->whereNull('users.id');

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull($table . '.deleted_at');
            }

            $this->scopeTable($query, $table, $user);

            return (int) $query->count();
        });
    }

    private function invalidOpportunityProbability(?User $user): int
    {
        return $this->scoped(CrmOpportunity::query(), $user)
            ->where(function ($query) {
                $query->where('probability_percent', '<', 0)->orWhere('probability_percent', '>', 100);
            })
            ->count();
    }

    private function scopeTable($query, string $table, ?User $user): void
    {
        if (!$user || (int) $user->isGod === 1) {
            return;
        }

        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);

        $query->where(function ($inner) use ($table, $tenantId, $organizationId) {
            if ($organizationId && Schema::hasColumn($table, 'organization_id')) {
                $inner->orWhere($table . '.organization_id', $organizationId);
            }

            if ($tenantId && Schema::hasColumn($table, 'tenant_id')) {
                $inner->orWhere($table . '.tenant_id', $tenantId);
            }
        });
    }

    private function score(array $issues): int
    {
        $penalty = collect($issues)->sum(function ($issue) {
            $weight = ['critical' => 18, 'high' => 10, 'medium' => 5, 'low' => 2][$issue['severity']] ?? 3;

            return min(30, $weight + min((int) $issue['count'], 20));
        });

        return max(0, 100 - (int) $penalty);
    }

    private function riskLevel(int $score, array $issues): string
    {
        if (collect($issues)->contains(fn($issue) => $issue['severity'] === 'critical' && $issue['count'] > 0) || $score < 50) {
            return 'critical';
        }

        if ($score < 70) {
            return 'high';
        }

        if ($score < 90) {
            return 'medium';
        }

        return 'low';
    }

    private function recommendations(array $issues): array
    {
        return collect($issues)->pluck('recommendation')->unique()->values()->all();
    }

    private function scopeLabel(?User $user): string
    {
        if (!$user) {
            return 'system-global';
        }

        return (int) $user->isGod === 1 ? 'god-global' : 'tenant-' . ($this->tenantId($user) ?: 'none') . '-org-' . ($this->organizationId($user) ?: 'none');
    }

    private function tenantId(?User $user): ?int
    {
        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) ?: null : null;
    }

    private function organizationId(?User $user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }
}
