<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        foreach (
            [
                'brands',
                'categories',
                'units',
                'employees',
                'stocks',
                'deliveries',
                'abortions',
                'repairs',
                'transfers',
                'areas',
                'receipts',
                'depots',
                'price_logs',
                'pish_factor_items',
                'target_products',
                'factor_makers',
                'histories',
                'vouchers',
                'voucher_items',
                'invoices',
                'details',
            ] as $tableName
        ) {
            $this->addTenantIdColumn($tableName);
        }

        foreach (['brands', 'categories', 'units', 'employees', 'stocks', 'deliveries', 'abortions', 'repairs', 'factor_makers'] as $tableName) {
            $this->backfillTenantIdFromOrganization($tableName);
        }

        $this->backfillTransfersFromOrganization();
        $this->backfillAreasFromRegion();
        $this->backfillTasksFromArea();
        $this->backfillReceiptsFromStore();
        $this->backfillDepotsFromStore();
        $this->backfillPriceLogsFromProduct();
        $this->backfillPishFactorItemsFromFactor();
        $this->backfillTargetProductsFromTarget();
        $this->backfillHistoriesFromUser();
        $this->backfillVouchersFromFactor();
        $this->backfillVoucherItemsFromVoucher();
        $this->backfillInvoicesFromUser();
        $this->backfillDetailsFromInvoice();
    }

    public function down()
    {
        // Intentionally left blank to avoid removing tenant scope data from production tables.
    }

    private function addTenantIdColumn(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('tenant_id')->nullable()->after('id');
            });
        }

        $this->addIndexIfMissing($tableName, 'tenant_id', $tableName . '_tenant_id_index');
    }

    private function backfillTenantIdFromOrganization(string $tableName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'tenant_id') || !Schema::hasColumn($tableName, 'organization_id')) {
            return;
        }

        DB::table($tableName)
            ->select('id', 'organization_id')
            ->whereNull('tenant_id')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($tableName) {
                foreach ($rows as $row) {
                    $tenantId = $this->tenantIdForOrganizations($this->extractIds($row->organization_id));

                    if ($tenantId) {
                        DB::table($tableName)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
                    }
                }
            });
    }

    private function backfillTransfersFromOrganization(): void
    {
        if (!Schema::hasTable('transfers') || !Schema::hasColumn('transfers', 'tenant_id') || !Schema::hasColumn('transfers', 'fromOrganization')) {
            return;
        }

        DB::table('transfers')
            ->select('id', 'fromOrganization')
            ->whereNull('tenant_id')
            ->whereNotNull('fromOrganization')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $tenantId = $this->tenantIdForOrganizations([(int) $row->fromOrganization]);
                    if ($tenantId) {
                        DB::table('transfers')->where('id', $row->id)->update(['tenant_id' => $tenantId]);
                    }
                }
            });
    }

    private function backfillAreasFromRegion(): void
    {
        $this->backfillFromJoinedTable('areas', 'region_id', 'regions', 'id', 'tenant_id');
    }

    private function backfillTasksFromArea(): void
    {
        if (!Schema::hasTable('tasks') || !Schema::hasColumn('tasks', 'tenant_id') || !Schema::hasTable('areas')) {
            return;
        }

        DB::table('tasks')
            ->join('areas', 'tasks.area_id', '=', 'areas.id')
            ->whereNull('tasks.tenant_id')
            ->whereNotNull('areas.tenant_id')
            ->update(['tasks.tenant_id' => DB::raw('areas.tenant_id')]);
    }

    private function backfillReceiptsFromStore(): void
    {
        $this->backfillFromJoinedTable('receipts', 'store_id', 'stores', 'id', 'tenant_id');
    }

    private function backfillDepotsFromStore(): void
    {
        $this->backfillFromJoinedTable('depots', 'store_id', 'stores', 'id', 'tenant_id');
    }

    private function backfillPriceLogsFromProduct(): void
    {
        $this->backfillFromJoinedTable('price_logs', 'pr_id', 'products', 'id', 'tenant_id');
    }

    private function backfillPishFactorItemsFromFactor(): void
    {
        $this->backfillFromJoinedTable('pish_factor_items', 'pishfactor_id', 'pishfactors', 'id', 'tenant_id');
    }

    private function backfillTargetProductsFromTarget(): void
    {
        $this->backfillFromJoinedTable('target_products', 'target_id', 'targets', 'id', 'tenant_id');
    }

    private function backfillHistoriesFromUser(): void
    {
        $this->backfillFromJoinedTable('histories', 'user_id', 'users', 'id', 'tenant_id');
    }

    private function backfillVouchersFromFactor(): void
    {
        $this->backfillFromJoinedTable('vouchers', 'factor_id', 'pishfactors', 'id', 'tenant_id');
    }

    private function backfillVoucherItemsFromVoucher(): void
    {
        $this->backfillFromJoinedTable('voucher_items', 'voucher_id', 'vouchers', 'id', 'tenant_id');
    }

    private function backfillInvoicesFromUser(): void
    {
        $this->backfillFromJoinedTable('invoices', 'user_id', 'users', 'id', 'tenant_id');
    }

    private function backfillDetailsFromInvoice(): void
    {
        $this->backfillFromJoinedTable('details', 'invoice_id', 'invoices', 'id', 'tenant_id');
    }

    private function backfillFromJoinedTable(string $tableName, string $localColumn, string $sourceTable, string $sourceColumn, string $sourceTenantColumn): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasTable($sourceTable) || !Schema::hasColumn($tableName, 'tenant_id') || !Schema::hasColumn($tableName, $localColumn) || !Schema::hasColumn($sourceTable, $sourceTenantColumn)) {
            return;
        }

        DB::table($tableName)
            ->join($sourceTable, $tableName . '.' . $localColumn, '=', $sourceTable . '.' . $sourceColumn)
            ->whereNull($tableName . '.tenant_id')
            ->whereNotNull($sourceTable . '.' . $sourceTenantColumn)
            ->update([$tableName . '.tenant_id' => DB::raw($sourceTable . '.' . $sourceTenantColumn)]);
    }

    private function tenantIdForOrganizations(array $organizationIds): ?int
    {
        if (empty($organizationIds) || !Schema::hasTable('organizations')) {
            return null;
        }

        $query = DB::table('organizations')->whereIn('id', $organizationIds);

        if (Schema::hasColumn('organizations', 'tenant_id')) {
            $tenantId = (clone $query)->whereNotNull('tenant_id')->value('tenant_id');
            if ($tenantId) {
                return (int) $tenantId;
            }
        }

        if (Schema::hasColumn('organizations', 'tenants_id')) {
            $tenantId = (clone $query)->whereNotNull('tenants_id')->value('tenants_id');
            if ($tenantId) {
                return (int) $tenantId;
            }
        }

        return null;
    }

    private function extractIds($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];
        $ids = [];

        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($this->extractIds(json_encode($value)) as $nestedId) {
                    $ids[] = $nestedId;
                }
                continue;
            }

            if (is_numeric($value)) {
                $ids[] = (int) $value;
                continue;
            }

            if (is_string($value) && preg_match_all('/\d+/', $value, $matches)) {
                foreach ($matches[0] as $match) {
                    $ids[] = (int) $match;
                }
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
                $table->index($columnName, $indexName);
            });
        }
    }
};
