<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantBackfillService
{
    public function run(bool $dryRun = false, ?string $onlyTable = null): array
    {
        $reports = [];

        foreach ($this->tablePlans($onlyTable) as $table => $plan) {
            $reports[$table] = $this->backfillTable($table, $plan, $dryRun);
        }

        return [
            'dry_run' => $dryRun,
            'tables' => $reports,
            'updated' => collect($reports)->sum('updated'),
        ];
    }

    private function tablePlans(?string $onlyTable): array
    {
        $plans = (array) config('erp_scale.tenant_backfill.tables', []);

        if ($onlyTable) {
            return isset($plans[$onlyTable]) ? [$onlyTable => $plans[$onlyTable]] : [];
        }

        return array_filter($plans, fn ($plan, $table) => Schema::hasTable($table), ARRAY_FILTER_USE_BOTH);
    }

    private function backfillTable(string $table, array $plan, bool $dryRun): array
    {
        $strategies = (array) ($plan['strategies'] ?? []);
        $updated = 0;
        $details = [];

        foreach ($strategies as $strategy) {
            $result = $this->applyStrategy($table, $strategy, $dryRun);
            $updated += $result['updated'];
            if ($result['updated'] > 0) {
                $details[] = $result;
            }
        }

        return [
            'updated' => $updated,
            'details' => $details,
        ];
    }

    private function applyStrategy(string $table, array $strategy, bool $dryRun): array
    {
        $name = (string) ($strategy['name'] ?? '');
        $limit = max(1, (int) config('erp_scale.tenant_backfill.chunk_size', 500));

        return match ($name) {
            'from_organization' => $this->fromOrganization($table, $strategy, $dryRun, $limit),
            'from_user_column' => $this->fromUserColumn($table, $strategy, $dryRun, $limit),
            'from_customer' => $this->fromCustomer($table, $strategy, $dryRun, $limit),
            'sync_legacy_tenant_column' => $this->syncLegacyTenantColumn($table, $dryRun, $limit),
            'from_customer_organization' => $this->fromCustomerOrganization($table, $strategy, $dryRun, $limit),
            default => ['name' => $name, 'updated' => 0],
        };
    }

    private function fromOrganization(string $table, array $strategy, bool $dryRun, int $limit): array
    {
        if (!Schema::hasColumn($table, 'tenant_id') || !Schema::hasColumn($table, 'organization_id')) {
            return ['name' => 'from_organization', 'updated' => 0];
        }

        $updated = 0;

        while (true) {
            $rows = DB::table($table)
                ->whereNull('tenant_id')
                ->whereNotNull('organization_id')
                ->limit($limit)
                ->get(['id', 'organization_id']);

            if ($rows->isEmpty()) {
                break;
            }

            $batchUpdated = 0;

            foreach ($rows as $row) {
                $tenantId = $this->tenantIdFromOrganizationValue($row->organization_id);
                if (!$tenantId) {
                    continue;
                }

                if (!$dryRun) {
                    DB::table($table)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
                }

                $batchUpdated++;
                $updated++;
            }

            if ($batchUpdated === 0) {
                break;
            }
        }

        return ['name' => 'from_organization', 'updated' => $updated];
    }

    private function fromUserColumn(string $table, array $strategy, bool $dryRun, int $limit): array
    {
        $column = (string) ($strategy['column'] ?? 'created_by');

        if (!Schema::hasColumn($table, 'tenant_id') || !Schema::hasColumn($table, $column) || !Schema::hasTable('users')) {
            return ['name' => 'from_user_column', 'updated' => 0];
        }

        $updated = 0;

        while (true) {
            $rows = DB::table($table . ' as target')
                ->join('users', 'users.id', '=', 'target.' . $column)
                ->whereNull('target.tenant_id')
                ->where(function ($query) {
                    $query->whereNotNull('users.tenant_id')->orWhereNotNull('users.tenants_id');
                })
                ->limit($limit)
                ->get(['target.id as id', 'users.tenant_id as user_tenant_id', 'users.tenants_id as user_tenants_id']);

            if ($rows->isEmpty()) {
                break;
            }

            $batchUpdated = 0;

            foreach ($rows as $row) {
                $tenantId = (int) ($row->user_tenant_id ?: $row->user_tenants_id ?: 0);
                if ($tenantId <= 0) {
                    continue;
                }

                if (!$dryRun) {
                    DB::table($table)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
                }

                $batchUpdated++;
                $updated++;
            }

            if ($batchUpdated === 0) {
                break;
            }
        }

        return ['name' => 'from_user_column:' . $column, 'updated' => $updated];
    }

    private function fromCustomer(string $table, array $strategy, bool $dryRun, int $limit): array
    {
        if (!Schema::hasColumn($table, 'tenant_id') || !Schema::hasColumn($table, 'customer_id') || !Schema::hasTable('customers')) {
            return ['name' => 'from_customer', 'updated' => 0];
        }

        $updated = 0;
        $syncOrg = (bool) ($strategy['sync_organization'] ?? false);

        while (true) {
            $select = ['target.id as id', 'customers.tenant_id as customer_tenant_id'];
            if ($syncOrg) {
                $select[] = 'customers.organization_id as customer_organization_id';
            }

            $rows = DB::table($table . ' as target')
                ->join('customers', 'customers.id', '=', 'target.customer_id')
                ->whereNull('target.tenant_id')
                ->whereNotNull('customers.tenant_id')
                ->limit($limit)
                ->get($select);

            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                $payload = ['tenant_id' => (int) $row->customer_tenant_id];

                if ($syncOrg && Schema::hasColumn($table, 'organization_id') && $row->customer_organization_id) {
                    $payload['organization_id'] = $row->customer_organization_id;
                }

                if (!$dryRun) {
                    DB::table($table)->where('id', $row->id)->update($payload);
                }

                $updated++;
            }
        }

        return ['name' => 'from_customer', 'updated' => $updated];
    }

    private function fromCustomerOrganization(string $table, array $strategy, bool $dryRun, int $limit): array
    {
        if (!Schema::hasColumn($table, 'organization_id') || !Schema::hasColumn($table, 'customer_id') || !Schema::hasTable('customers')) {
            return ['name' => 'from_customer_organization', 'updated' => 0];
        }

        $updated = 0;

        while (true) {
            $rows = DB::table($table . ' as target')
                ->join('customers', 'customers.id', '=', 'target.customer_id')
                ->whereNull('target.organization_id')
                ->whereNotNull('customers.organization_id')
                ->limit($limit)
                ->get(['target.id as id', 'customers.organization_id as customer_organization_id']);

            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                if (!$dryRun) {
                    DB::table($table)->where('id', $row->id)->update([
                        'organization_id' => $row->customer_organization_id,
                    ]);
                }

                $updated++;
            }
        }

        return ['name' => 'from_customer_organization', 'updated' => $updated];
    }

    private function syncLegacyTenantColumn(string $table, bool $dryRun, int $limit): array
    {
        if (!Schema::hasColumn($table, 'tenant_id') || !Schema::hasColumn($table, 'tenants_id')) {
            return ['name' => 'sync_legacy_tenant_column', 'updated' => 0];
        }

        $updated = 0;

        $rows = DB::table($table)
            ->whereNotNull('tenant_id')
            ->where(function ($query) {
                $query->whereNull('tenants_id')->orWhereColumn('tenants_id', '!=', 'tenant_id');
            })
            ->limit($limit)
            ->pluck('tenant_id', 'id');

        foreach ($rows as $id => $tenantId) {
            if (!$dryRun) {
                DB::table($table)->where('id', $id)->update(['tenants_id' => $tenantId]);
            }

            $updated++;
        }

        $reverse = DB::table($table)
            ->whereNull('tenant_id')
            ->whereNotNull('tenants_id')
            ->limit($limit)
            ->pluck('tenants_id', 'id');

        foreach ($reverse as $id => $tenantId) {
            if (!$dryRun) {
                DB::table($table)->where('id', $id)->update(['tenant_id' => $tenantId]);
            }

            $updated++;
        }

        return ['name' => 'sync_legacy_tenant_column', 'updated' => $updated];
    }

    private function tenantIdFromOrganizationValue(mixed $organizationId): ?int
    {
        if ($organizationId === null || $organizationId === '') {
            return null;
        }

        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;
        $orgId = is_array($decoded) ? (int) ($decoded[0] ?? 0) : (int) $organizationId;

        if ($orgId <= 0 || !Schema::hasTable('organizations')) {
            return null;
        }

        $organization = DB::table('organizations')->where('id', $orgId)->first(['tenant_id', 'tenants_id']);

        if (!$organization) {
            return null;
        }

        return (int) ($organization->tenant_id ?: $organization->tenants_id ?: 0) ?: null;
    }
}
