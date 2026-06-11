<?php

namespace App\Services;

use App\Models\ErpScaleAuditSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ErpScaleHardeningService
{
    public function state(?User $user = null): array
    {
        $audit = Cache::remember($this->cacheKey($user), now()->addMinutes(5), fn() => $this->audit($user));
        $snapshots = ErpScaleAuditSnapshot::query()
            ->when($user && (int) $user->isGod !== 1, fn($query) => $query->forOrganizations($user))
            ->latest('generated_at')
            ->limit(20)
            ->get();

        return $audit + [
            'snapshots' => $snapshots,
            'risk_levels' => ErpScaleAuditSnapshot::RISK_LEVELS,
            'lookup_entities' => array_keys($this->lookupDefinitions()),
        ];
    }

    public function audit(?User $user = null): array
    {
        $profiles = $this->tableProfiles($user);
        $checks = $this->checks($profiles);
        $summary = $this->summary($profiles, $checks);
        $recommendations = collect($checks)
            ->whereIn('severity', ['critical', 'high', 'medium'])
            ->pluck('recommendation')
            ->unique()
            ->values()
            ->all();

        $score = max(0, 100
            - (collect($checks)->where('severity', 'critical')->count() * 20)
            - (collect($checks)->where('severity', 'high')->count() * 12)
            - (collect($checks)->where('severity', 'medium')->count() * 6));

        return [
            'readiness_score' => $score,
            'risk_level' => $this->riskLevel($score, $checks),
            'scope_label' => $this->scopeLabel($user),
            'summary' => $summary,
            'checks' => $checks,
            'table_profiles' => $profiles,
            'recommendations' => $recommendations,
            'generated_at' => now(),
        ];
    }

    public function persist(?User $user = null): ErpScaleAuditSnapshot
    {
        $audit = $this->audit($user);

        return ErpScaleAuditSnapshot::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'scope_label' => $audit['scope_label'],
            'readiness_score' => $audit['readiness_score'],
            'risk_level' => $audit['risk_level'],
            'summary' => $audit['summary'],
            'checks' => $audit['checks'],
            'table_profiles' => $audit['table_profiles'],
            'recommendations' => $audit['recommendations'],
            'generated_at' => now(),
            'generated_by' => $user?->id,
        ]);
    }

    public function supportedLookupEntities(): array
    {
        return array_keys($this->lookupDefinitions());
    }

    public function remoteLookup(?User $user, string $entity, ?string $term = null, int $limit = 20, array $filters = []): array
    {
        $definition = $this->lookupDefinitions()[$entity] ?? null;
        if (!$definition || !Schema::hasTable($definition['table'])) {
            return [];
        }

        $limit = min(max($limit, 5), 50);
        $term = trim((string) $term);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $query = $this->baseLookupQuery($user, $entity, $definition, $filters);

        $searchColumns = array_values(array_filter($definition['search'], fn ($column) => Schema::hasColumn($definition['table'], $column)));
        if (empty($searchColumns)) {
            return [];
        }

        $query->where(function ($where) use ($searchColumns, $term) {
            foreach ($searchColumns as $column) {
                $where->orWhere($column, 'like', '%' . $term . '%');
            }
        });

        return $this->mapLookupRows(
            $query->select($this->lookupSelectColumns($definition))
                ->orderByDesc('id')
                ->limit($limit)
                ->get(),
            $entity,
            $definition
        );
    }

    public function resolveByIds(?User $user, string $entity, array $ids, array $filters = []): array
    {
        $definition = $this->lookupDefinitions()[$entity] ?? null;
        if (!$definition || !Schema::hasTable($definition['table'])) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        $query = $this->baseLookupQuery($user, $entity, $definition, $filters)
            ->whereIn($definition['table'] . '.id', $ids);

        return $this->mapLookupRows(
            $query->select($this->lookupSelectColumns($definition))->get(),
            $entity,
            $definition
        );
    }

    private function baseLookupQuery(?User $user, string $entity, array $definition, array $filters)
    {
        $query = DB::table($definition['table']);
        $this->applyScope($query, $definition['table'], $user);
        $this->applyLookupFilters($query, $definition['table'], $entity, $filters);

        if (Schema::hasColumn($definition['table'], 'deleted_at')) {
            $query->whereNull($definition['table'] . '.deleted_at');
        }

        return $query;
    }

    private function applyLookupFilters($query, string $table, string $entity, array $filters): void
    {
        if ($entity === 'products') {
            if (array_key_exists('is_active', $filters) && Schema::hasColumn($table, 'isActive')) {
                $query->where($table . '.isActive', (int) $filters['is_active']);
            }

            if (array_key_exists('is_material', $filters) && Schema::hasColumn($table, 'isMaterial')) {
                $query->where($table . '.isMaterial', (int) $filters['is_material']);
            }
        }

        if ($entity === 'employees' && array_key_exists('is_active', $filters) && Schema::hasColumn($table, 'isActive')) {
            $query->where($table . '.isActive', (int) $filters['is_active']);
        }

        if ($entity === 'users' && array_key_exists('is_active', $filters) && Schema::hasColumn($table, 'isActive')) {
            $query->where($table . '.isActive', (int) $filters['is_active']);
        }
    }

    private function lookupSelectColumns(array $definition): array
    {
        return array_values(array_unique(array_merge(['id'], $definition['search'], $definition['label'])));
    }

    private function mapLookupRows($rows, string $entity, array $definition): array
    {
        return collect($rows)
            ->map(fn ($row) => [
                'id' => $row->id,
                'text' => $this->lookupText($row, $definition),
                'entity' => $entity,
            ])
            ->values()
            ->all();
    }

    private function tableProfiles(?User $user): array
    {
        return collect($this->tableDefinitions())->map(function ($definition) use ($user) {
            $table = $definition['table'];
            if (!Schema::hasTable($table)) {
                return $definition + [
                    'exists' => false,
                    'row_count' => 0,
                    'index_coverage' => 0,
                    'missing_indexes' => $definition['indexes'],
                    'has_scope_columns' => false,
                    'has_date_column' => false,
                ];
            }

            $query = DB::table($table);
            $this->applyScope($query, $table, $user);

            $indexes = $this->indexesForTable($table);
            $missingIndexes = collect($definition['indexes'])
                ->reject(fn($index) => in_array($index, $indexes, true))
                ->values()
                ->all();

            return $definition + [
                'exists' => true,
                'row_count' => (clone $query)->count(),
                'index_coverage' => count($definition['indexes']) ? round(((count($definition['indexes']) - count($missingIndexes)) / count($definition['indexes'])) * 100, 2) : 100,
                'missing_indexes' => $missingIndexes,
                'has_scope_columns' => Schema::hasColumn($table, 'tenant_id') || Schema::hasColumn($table, 'organization_id'),
                'has_date_column' => collect($definition['date_columns'])->contains(fn($column) => Schema::hasColumn($table, $column)),
            ];
        })->values()->all();
    }

    private function checks(array $profiles): array
    {
        $missingIndexTables = collect($profiles)->where('exists', true)->filter(fn($profile) => count($profile['missing_indexes']) > 0);
        $heavyTables = collect($profiles)->where('exists', true)->where('row_count', '>=', 10000)->values();
        $heavyTablesMissingIndexes = $heavyTables->filter(fn($profile) => count($profile['missing_indexes']) > 0)->values();
        $summaryRows = Schema::hasTable('bi_daily_summaries') ? DB::table('bi_daily_summaries')->count() : 0;
        $coldRows = $this->coldDataCandidates();
        $slowQueryLog = $this->slowQueryLogStatus();
        $archivePolicyReady = (bool) config('erp_scale.archive.enabled', true);

        return [
            $this->check('composite_indexes', 'Indexهای ترکیبی scope/date', $missingIndexTables->count(), $missingIndexTables->isEmpty() ? 'low' : 'medium', 'migration سبک سازی باید روی جدول های پرتکرار index ترکیبی tenant/organization/date/status داشته باشد.'),
            $this->check('server_side_lookup', 'انتخاب از راه دور برای selectهای سنگین', count($this->lookupDefinitions()), 'low', 'برای customer/product/user/account/store endpoint محدود JSON فعال است و فرم های جدید باید به جای select حجیم از آن استفاده کنند.'),
            $this->check('summary_cache', 'summary/cache داشبورد و BI', $summaryRows, $summaryRows > 0 ? 'low' : 'medium', 'BI data mart و summaryهای روزانه باید قبل از گزارش های مدیریتی refresh شوند.'),
            $this->check('slow_query_log', 'پایش query کند دیتابیس', $slowQueryLog['enabled'] ? 1 : 0, $slowQueryLog['enabled'] ? 'low' : 'medium', 'در محیط production slow query log باید فعال باشد تا queryهای سنگین پنل های بزرگ پیدا شوند.'),
            $this->check('cold_data_candidates', 'کاندیداهای آرشیو داده سرد', $coldRows, $archivePolicyReady ? 'low' : 'medium', 'لاگ ها و اعلان های قدیمی بعد از دوره نگهداری باید به archive منتقل یا purge کنترل شده شوند.'),
            $this->check('heavy_table_pressure', 'فشار جدول های بزرگ', $heavyTables->count(), $heavyTablesMissingIndexes->isEmpty() ? 'low' : 'medium', 'جدول های بالای 10000 ردیف باید فقط با pagination/server-side و select محدود خوانده شوند.'),
        ];
    }

    private function summary(array $profiles, array $checks): array
    {
        $existing = collect($profiles)->where('exists', true);
        $largest = $existing->sortByDesc('row_count')->first();

        return [
            'tables_audited' => $existing->count(),
            'rows_profiled' => $existing->sum('row_count'),
            'composite_indexes_ready' => $existing->filter(fn($profile) => empty($profile['missing_indexes']))->count(),
            'remote_lookup_entities' => count($this->lookupDefinitions()),
            'bi_summary_rows' => Schema::hasTable('bi_daily_summaries') ? DB::table('bi_daily_summaries')->count() : 0,
            'cold_data_candidates' => $this->coldDataCandidates(),
            'open_recommendations' => collect($checks)->whereIn('severity', ['critical', 'high', 'medium'])->count(),
            'largest_table' => $largest ? $largest['table'] . ':' . $largest['row_count'] : '-',
        ];
    }

    private function tableDefinitions(): array
    {
        return [
            ['table' => 'customers', 'label' => 'مشتریان', 'indexes' => ['customers_scope_status_updated_idx', 'customers_scope_mobile_idx'], 'date_columns' => ['updated_at', 'created_at']],
            ['table' => 'products', 'label' => 'کالاها', 'indexes' => ['products_scope_store_active_idx', 'products_scope_sku_idx'], 'date_columns' => ['updated_at', 'created_at']],
            ['table' => 'pishfactors', 'label' => 'فاکتور/سفارش فروش', 'indexes' => ['pishfactors_scope_customer_status_idx', 'pishfactors_scope_created_idx'], 'date_columns' => ['created_at', 'recive_date_en']],
            ['table' => 'pish_factor_items', 'label' => 'اقلام فروش', 'indexes' => ['pf_items_scope_product_idx'], 'date_columns' => ['created_at']],
            ['table' => 'vouchers', 'label' => 'اسناد حسابداری', 'indexes' => ['vouchers_scope_date_idx'], 'date_columns' => ['voucher_date_en', 'created_at']],
            ['table' => 'voucher_items', 'label' => 'آرتیکل حسابداری', 'indexes' => ['voucher_items_scope_account_idx'], 'date_columns' => ['created_at']],
            ['table' => 'receipts', 'label' => 'رسید/حواله انبار', 'indexes' => ['receipts_scope_store_status_idx'], 'date_columns' => ['receipt_date_en', 'created_at']],
            ['table' => 'depots', 'label' => 'اقلام انبار', 'indexes' => ['depots_scope_product_idx'], 'date_columns' => ['created_at']],
            ['table' => 'inventory_movements', 'label' => 'گردش انبار', 'indexes' => ['inv_movements_scope_store_product_idx', 'inv_movements_scope_date_idx'], 'date_columns' => ['movement_date', 'created_at']],
            ['table' => 'inventory_balances', 'label' => 'مانده موجودی', 'indexes' => ['inv_balances_scope_store_product_idx'], 'date_columns' => ['updated_at']],
            ['table' => 'users', 'label' => 'کاربران', 'indexes' => ['users_scope_active_idx'], 'date_columns' => ['updated_at']],
            ['table' => 'notifs', 'label' => 'اعلان ها', 'indexes' => ['notifs_scope_user_status_idx'], 'date_columns' => ['created_at']],
            ['table' => 'bi_daily_summaries', 'label' => 'BI summary', 'indexes' => ['bi_summaries_scope_date_domain_idx'], 'date_columns' => ['summary_date']],
            ['table' => 'crm_followups', 'label' => 'پیگیری CRM', 'indexes' => ['crm_followups_scope_status_due_idx'], 'date_columns' => ['due_date_en', 'created_at']],
            ['table' => 'crm_opportunities', 'label' => 'فرصت CRM', 'indexes' => ['crm_opps_scope_status_next_idx'], 'date_columns' => ['next_action_date_en', 'created_at']],
            ['table' => 'crm_service_tickets', 'label' => 'تیکت CRM', 'indexes' => ['crm_tickets_scope_status_due_idx'], 'date_columns' => ['due_at', 'created_at']],
            ['table' => 'crm_integration_sync_logs', 'label' => 'لاگ integration CRM', 'indexes' => ['crm_sync_logs_scope_status_created_idx'], 'date_columns' => ['created_at']],
        ];
    }

    private function lookupDefinitions(): array
    {
        return [
            'customers' => ['table' => 'customers', 'search' => ['name', 'mobile', 'phone', 'customer_code', 'national_id', 'tablo'], 'label' => ['name', 'tablo', 'mobile']],
            'products' => ['table' => 'products', 'search' => ['title', 'display_name', 'sku'], 'label' => ['title', 'sku']],
            'users' => ['table' => 'users', 'search' => ['name', 'username', 'mobile'], 'label' => ['name', 'mobile']],
            'employees' => ['table' => 'employees', 'search' => ['name', 'personalID'], 'label' => ['name', 'personalID']],
            'accounts' => ['table' => 'accounts', 'search' => ['name', 'code', 'account_number'], 'label' => ['code', 'name']],
            'stores' => ['table' => 'stores', 'search' => ['title', 'code'], 'label' => ['title', 'code']],
        ];
    }

    private function check(string $key, string $title, int $count, string $severity, string $recommendation): array
    {
        return compact('key', 'title', 'count', 'severity', 'recommendation');
    }

    private function indexesForTable(string $table): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        return DB::table('information_schema.statistics')
            ->selectRaw('DISTINCT INDEX_NAME as index_name')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->pluck('index_name')
            ->all();
    }

    private function coldDataCandidates(): int
    {
        return app(ErpColdDataArchiveService::class)->candidateCount();
    }

    private function slowQueryLogStatus(): array
    {
        try {
            $row = DB::selectOne("SHOW VARIABLES LIKE 'slow_query_log'");
            $value = strtoupper((string) ($row->Value ?? $row->value ?? 'OFF'));

            return ['enabled' => in_array($value, ['ON', '1'], true) || (bool) config('erp_scale.slow_query.enabled', true), 'value' => $value];
        } catch (\Throwable $exception) {
            return ['enabled' => (bool) config('erp_scale.slow_query.enabled', true), 'value' => 'application'];
        }
    }

    private function applyScope($query, string $table, ?User $user): void
    {
        if (!$user || (int) $user->isGod === 1) {
            return;
        }

        $tenantId = $this->tenantId($user);
        if ($tenantId && Schema::hasColumn($table, 'tenant_id')) {
            $query->where($table . '.tenant_id', $tenantId);
        } elseif ($tenantId && Schema::hasColumn($table, 'tenants_id')) {
            $query->where($table . '.tenants_id', $tenantId);
        }

        $organizationIds = $this->organizationIds($user);
        if (!empty($organizationIds) && Schema::hasColumn($table, 'organization_id')) {
            $query->whereIn($table . '.organization_id', $organizationIds);
        }
    }

    private function lookupText($row, array $definition): string
    {
        $parts = [];
        foreach ($definition['label'] as $column) {
            if (isset($row->{$column}) && $row->{$column} !== '') {
                $parts[] = $row->{$column};
            }
        }

        return implode(' - ', $parts) ?: ('#' . $row->id);
    }

    private function riskLevel(int $score, array $checks): string
    {
        if (collect($checks)->where('severity', 'critical')->isNotEmpty() || $score < 50) {
            return 'critical';
        }

        if ($score < 70) {
            return 'high';
        }

        return $score < 90 ? 'medium' : 'low';
    }

    private function scopeLabel(?User $user): string
    {
        return $user && (int) $user->isGod !== 1 ? 'tenant' : 'global';
    }

    private function tenantId(?User $user): ?int
    {
        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) ?: null : null;
    }

    private function organizationId(?User $user): ?int
    {
        $ids = $this->organizationIds($user);

        return $ids[0] ?? null;
    }

    private function organizationIds(?User $user): array
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return array_values(array_filter(array_map('intval', $decoded)));
        }

        return $organizationId ? [(int) $organizationId] : [];
    }

    private function cacheKey(?User $user): string
    {
        return 'erp_scale_hardening:' . ($user?->id ?: 'system') . ':' . ($this->tenantId($user) ?: 'global') . ':' . ($this->organizationId($user) ?: 'all');
    }
}
