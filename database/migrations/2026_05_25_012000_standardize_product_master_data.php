<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendUnits();
        $this->extendProducts();
        $this->backfillProductUnits();
        $this->defaultProductMasterData();
    }

    public function down()
    {
        // Intentionally left blank to preserve standardized product master-data.
    }

    private function extendUnits(): void
    {
        if (!Schema::hasTable('units')) {
            return;
        }

        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'code')) {
                $table->string('code', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('units', 'symbol')) {
                $table->string('symbol', 50)->nullable()->after('title');
            }
            if (!Schema::hasColumn('units', 'unit_type')) {
                $table->string('unit_type', 30)->nullable()->default('count')->after('symbol');
            }
            if (!Schema::hasColumn('units', 'conversion_to_parent')) {
                $table->decimal('conversion_to_parent', 18, 6)->nullable()->default(1)->after('parent_id');
            }
        });

        $this->addIndexIfMissing('units', 'code', 'units_code_index');
        $this->addIndexIfMissing('units', 'unit_type', 'units_unit_type_index');
    }

    private function extendProducts(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type', 30)->nullable()->default('goods')->after('sku');
            }
            if (!Schema::hasColumn('products', 'stock_tracking_mode')) {
                $table->string('stock_tracking_mode', 30)->nullable()->default('tracked')->after('product_type');
            }
            if (!Schema::hasColumn('products', 'valuation_method')) {
                $table->string('valuation_method', 40)->nullable()->default('weighted_average')->after('stock_tracking_mode');
            }
            if (!Schema::hasColumn('products', 'base_unit_id')) {
                $table->unsignedBigInteger('base_unit_id')->nullable()->after('pr_unit');
            }
            if (!Schema::hasColumn('products', 'secondary_unit_id')) {
                $table->unsignedBigInteger('secondary_unit_id')->nullable()->after('pr_sub_unit');
            }
            if (!Schema::hasColumn('products', 'unit_conversion_factor')) {
                $table->decimal('unit_conversion_factor', 18, 6)->nullable()->default(1)->after('pack_items');
            }
        });

        $this->addIndexIfMissing('products', 'product_type', 'products_product_type_index');
        $this->addIndexIfMissing('products', 'base_unit_id', 'products_base_unit_id_index');
        $this->addIndexIfMissing('products', 'secondary_unit_id', 'products_secondary_unit_id_index');
    }

    private function backfillProductUnits(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('units') || !Schema::hasColumn('products', 'base_unit_id')) {
            return;
        }

        DB::table('products')
            ->select('id', 'tenant_id', 'organization_id', 'pr_unit', 'pr_sub_unit', 'pack_items')
            ->orderBy('id')
            ->chunkById(500, function ($products) {
                foreach ($products as $product) {
                    $organizationId = $this->primaryOrganizationId($product->organization_id);
                    if (!$organizationId) {
                        continue;
                    }

                    $baseUnitId = $this->unitId((string) $product->pr_unit, $organizationId, $product->tenant_id, null, 1);
                    $secondaryUnitId = $this->unitId((string) $product->pr_sub_unit, $organizationId, $product->tenant_id, $baseUnitId, $product->pack_items ?: 1);

                    DB::table('products')->where('id', $product->id)->update([
                        'base_unit_id' => $baseUnitId,
                        'secondary_unit_id' => $secondaryUnitId,
                        'unit_conversion_factor' => $product->pack_items ?: 1,
                    ]);
                }
            });
    }

    private function defaultProductMasterData(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        DB::table('products')->whereNull('product_type')->update(['product_type' => 'goods']);
        DB::table('products')->whereNull('stock_tracking_mode')->update(['stock_tracking_mode' => 'tracked']);
        DB::table('products')->whereNull('valuation_method')->update(['valuation_method' => 'weighted_average']);
        DB::table('products')->whereNull('unit_conversion_factor')->update(['unit_conversion_factor' => 1]);
    }

    private function unitId(string $title, int $organizationId, $tenantId, ?int $parentId, $conversion): ?int
    {
        $title = trim($title);
        if ($title === '' || $title === '0') {
            return null;
        }

        $query = DB::table('units')
            ->where('organization_id', $organizationId)
            ->where('title', $title);

        if ($parentId) {
            $query->where(function ($unitQuery) use ($parentId) {
                $unitQuery->whereNull('parent_id')->orWhere('parent_id', $parentId);
            });
        }

        $unitId = $query->value('id');
        if ($unitId) {
            return (int) $unitId;
        }

        return (int) DB::table('units')->insertGetId([
            'tenant_id' => $tenantId ?: $this->tenantIdForOrganization($organizationId),
            'organization_id' => $organizationId,
            'parent_id' => $parentId,
            'title' => $title,
            'symbol' => $title,
            'unit_type' => 'count',
            'conversion_to_parent' => $conversion ?: 1,
            'description' => 'ساخته شده از واحد legacy کالا',
            'isActive' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function primaryOrganizationId($raw): ?int
    {
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];

        foreach ($values as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private function tenantIdForOrganization(int $organizationId): ?int
    {
        if (!Schema::hasTable('organizations')) {
            return null;
        }

        $organization = DB::table('organizations')->where('id', $organizationId)->first();
        if (!$organization) {
            return null;
        }

        return $organization->tenant_id ?? $organization->tenants_id ?? null;
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists && Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
                $table->index($columnName, $indexName);
            });
        }
    }
};
