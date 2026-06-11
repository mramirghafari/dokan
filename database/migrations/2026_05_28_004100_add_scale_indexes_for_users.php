<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $this->addIndexIfMissing('users', ['isActive', 'id'], 'users_active_latest_index');

        if (Schema::hasColumn('users', 'organization_id')) {
            $this->addIndexIfMissing('users', ['organization_id', 'isActive', 'id'], 'users_org_active_latest_index');
        }

        if (Schema::hasColumn('users', 'tenants_id')) {
            $this->addIndexIfMissing('users', ['tenants_id', 'isActive', 'id'], 'users_tenant_active_latest_index');
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep performance indexes in production databases.
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
