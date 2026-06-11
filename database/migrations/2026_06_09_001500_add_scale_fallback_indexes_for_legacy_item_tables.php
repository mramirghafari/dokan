<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfPossible('pish_factor_items', ['tenant_id', 'pr_id'], 'pf_items_scope_product_idx');
        $this->addIndexIfPossible('depots', ['tenant_id', 'store_id', 'pr_id'], 'depots_scope_product_idx');
    }

    public function down(): void
    {
        // Non-destructive: fallback indexes support legacy high-volume item tables.
    }

    private function addIndexIfPossible(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        $availableColumns = array_values(array_filter($columns, fn($column) => Schema::hasColumn($table, $column)));
        if (count($availableColumns) < 2) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($availableColumns, $name) {
            $tableBlueprint->index($availableColumns, $name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};
