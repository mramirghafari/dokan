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

        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'document_status')) {
                $table->string('document_status', 30)->default('approved')->after('tozihat');
            }
            if (!Schema::hasColumn('receipts', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('document_status');
            }
            if (!Schema::hasColumn('receipts', 'approved_by')) {
                $table->unsignedInteger('approved_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('receipts', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('receipts', 'canceled_by')) {
                $table->unsignedInteger('canceled_by')->nullable()->after('canceled_at');
            }
            if (!Schema::hasColumn('receipts', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('canceled_by');
            }
        });

        $this->addIndexIfMissing('receipts', ['tenant_id', 'document_status'], 'receipts_tenant_document_status_index');
    }

    public function down(): void
    {
        // Non-destructive migration: keep warehouse document audit status intact.
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
