<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendTenantsTable();

        $this->addTenantIdColumn('organizations', 'tenants_id');
        $this->addTenantIdColumn('users', 'tenants_id');
        $this->addTenantIdColumn('stores', 'tenants_id');
        $this->addTenantIdColumn('products', 'organization_id');
        $this->addTenantIdColumn('customers', 'organization_id');
        $this->addTenantIdColumn('pishfactors', 'tenants_id');
        $this->addTenantIdColumn('accounts', 'tenants_id');
        $this->addTenantIdColumn('payment_terminals', 'tenants_id');
        $this->addTenantIdColumn('cities', 'organization_id');
        $this->addTenantIdColumn('regions', 'organization_id');
        $this->addTenantIdColumn('tasks', 'organization_id');
        $this->addTenantIdColumn('targets', 'organization_id');
        $this->addTenantIdColumn('shipments', 'organization_id');
        $this->addTenantIdColumn('materials', 'organization_id');
        $this->addTenantIdColumn('material_stores', 'organization_id');
        $this->addTenantIdColumn('roles', 'isActive');
        $this->addTenantIdColumn('permissions', 'isActive');
        $this->addTenantIdColumn('settings', 'id');

        $this->copyTenantsIdToTenantId('organizations');
        $this->copyTenantsIdToTenantId('users');
        $this->copyTenantsIdToTenantId('stores');
        $this->copyTenantsIdToTenantId('pishfactors');
        $this->copyTenantsIdToTenantId('accounts');
        $this->copyTenantsIdToTenantId('payment_terminals');

        $this->backfillTenantIdFromOrganization('users');
        $this->backfillTenantIdFromOrganization('stores');
        $this->backfillTenantIdFromOrganization('products');
        $this->backfillTenantIdFromOrganization('customers');
        $this->backfillTenantIdFromOrganization('pishfactors');
        $this->backfillTenantIdFromOrganization('accounts');
        $this->backfillTenantIdFromOrganization('payment_terminals');
        $this->backfillTenantIdFromOrganization('cities');
        $this->backfillTenantIdFromOrganization('regions');
        $this->backfillTenantIdFromOrganization('tasks');
        $this->backfillTenantIdFromOrganization('targets');
        $this->backfillTenantIdFromOrganization('shipments');
        $this->backfillTenantIdFromOrganization('materials');
        $this->backfillTenantIdFromOrganization('material_stores');
    }

    public function down()
    {
        // Intentionally left blank to avoid removing tenant scope data from production tables.
    }

    private function extendTenantsTable(): void
    {
        if (!Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'code')) {
                $table->string('code', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('tenants', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('tenants', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('display_name');
            }
            if (!Schema::hasColumn('tenants', 'economic_number')) {
                $table->string('economic_number', 50)->nullable()->after('legal_name');
            }
            if (!Schema::hasColumn('tenants', 'national_id')) {
                $table->string('national_id', 50)->nullable()->after('economic_number');
            }
            if (!Schema::hasColumn('tenants', 'phone')) {
                $table->string('phone', 50)->nullable()->after('national_id');
            }
            if (!Schema::hasColumn('tenants', 'mobile')) {
                $table->string('mobile', 50)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->text('address')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('tenants', 'unit_order')) {
                $table->string('unit_order', 55)->nullable()->after('customer_group_status');
            }
            if (!Schema::hasColumn('tenants', 'sub_order')) {
                $table->string('sub_order', 55)->nullable()->after('unit_order');
            }
            if (!Schema::hasColumn('tenants', 'currency_type')) {
                $table->string('currency_type', 20)->nullable()->after('sub_order');
            }
            if (!Schema::hasColumn('tenants', 'fiscal_year_start')) {
                $table->date('fiscal_year_start')->nullable()->after('currency_type');
            }
            if (!Schema::hasColumn('tenants', 'fiscal_year_end')) {
                $table->date('fiscal_year_end')->nullable()->after('fiscal_year_start');
            }
            if (!Schema::hasColumn('tenants', 'tozihat')) {
                $table->text('tozihat')->nullable()->after('fiscal_year_end');
            }
            if (!Schema::hasColumn('tenants', 'settings')) {
                $table->json('settings')->nullable()->after('tozihat');
            }
            if (!Schema::hasColumn('tenants', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('settings');
            }
        });
    }

    private function addTenantIdColumn(string $tableName, ?string $afterColumn = null): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'tenant_id')) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn) {
                $column = $table->unsignedInteger('tenant_id')->nullable();
                if ($afterColumn && Schema::hasColumn($tableName, $afterColumn)) {
                    $column->after($afterColumn);
                }
            });
        }

        $this->addIndexIfMissing($tableName, 'tenant_id', $tableName . '_tenant_id_index');
    }

    private function copyTenantsIdToTenantId(string $tableName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'tenant_id') || !Schema::hasColumn($tableName, 'tenants_id')) {
            return;
        }

        DB::table($tableName)
            ->whereNull('tenant_id')
            ->whereNotNull('tenants_id')
            ->update(['tenant_id' => DB::raw('tenants_id')]);
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
                    $organizationIds = $this->extractOrganizationIds($row->organization_id);
                    $tenantId = $this->tenantIdForOrganizations($organizationIds);

                    if ($tenantId) {
                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update(['tenant_id' => $tenantId]);
                    }
                }
            });
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

    private function extractOrganizationIds($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];
        $ids = [];

        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($this->extractOrganizationIds(json_encode($value)) as $nestedId) {
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
