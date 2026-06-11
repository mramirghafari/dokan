<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('receipts')) {
            return;
        }

        $this->addIndexIfMissing('receipts', ['store_id', 'id'], 'receipts_store_id_latest_index');
        $this->addIndexIfMissing('receipts', ['store_id', 'user_id', 'id'], 'receipts_store_user_latest_index');
        $this->addIndexIfMissing('receipts', ['store_id', 'document_status', 'id'], 'receipts_store_status_latest_index');
        $this->addIndexIfMissing('receipts', ['store_id', 'type', 'id'], 'receipts_store_type_latest_index');
        $this->addIndexIfMissing('receipts', ['store_id', 'date_en', 'id'], 'receipts_store_date_latest_index');
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
