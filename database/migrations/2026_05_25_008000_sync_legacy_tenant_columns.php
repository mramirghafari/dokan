<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        foreach ($this->tablesWithTenantColumns() as $tableName) {
            $this->copyWhenTargetIsEmpty($tableName, 'tenant_id', 'tenants_id');
            $this->copyWhenTargetIsEmpty($tableName, 'tenants_id', 'tenant_id');
        }
    }

    public function down()
    {
        // Intentionally left blank to avoid removing compatibility tenant data.
    }

    private function tablesWithTenantColumns(): array
    {
        return DB::table('information_schema.COLUMNS')
            ->select('TABLE_NAME')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('COLUMN_NAME', ['tenant_id', 'tenants_id'])
            ->groupBy('TABLE_NAME')
            ->havingRaw('COUNT(DISTINCT COLUMN_NAME) = 2')
            ->pluck('TABLE_NAME')
            ->map(fn($tableName) => (string) $tableName)
            ->all();
    }

    private function copyWhenTargetIsEmpty(string $tableName, string $sourceColumn, string $targetColumn): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $sourceColumn) || !Schema::hasColumn($tableName, $targetColumn)) {
            return;
        }

        DB::table($tableName)
            ->whereNotNull($sourceColumn)
            ->where($sourceColumn, '<>', 0)
            ->where(function ($query) use ($targetColumn) {
                $query->whereNull($targetColumn)->orWhere($targetColumn, 0);
            })
            ->update([$targetColumn => DB::raw($sourceColumn)]);
    }
};
