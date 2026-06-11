<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vouchers')) {
            $this->addIndexIfMissing('vouchers', ['tenant_id', 'voucher_date_en', 'id'], 'acct_review_vouchers_date_index');
            $this->addIndexIfMissing('vouchers', ['source_type', 'source_id', 'document_type'], 'acct_review_vouchers_source_doc_index');
        }

        if (Schema::hasTable('voucher_items')) {
            $this->addIndexIfMissing('voucher_items', ['voucher_id'], 'acct_review_items_voucher_index');
            $this->addIndexIfMissing('voucher_items', ['account_id'], 'acct_review_items_account_index');
        }

        if (Schema::hasTable('receipts')) {
            $this->addIndexIfMissing('receipts', ['tenant_id', 'document_status', 'date_en', 'id'], 'acct_review_receipts_status_date_index');
            $this->addIndexIfMissing('receipts', ['tenant_id', 'return_source_receipt_id', 'id'], 'acct_review_receipts_return_index');
        }

        if (Schema::hasTable('inventory_balances')) {
            $this->addIndexIfMissing('inventory_balances', ['tenant_id', 'quantity'], 'acct_review_balances_qty_index');
            $this->addIndexIfMissing('inventory_balances', ['tenant_id', 'quantity_sub_unit'], 'acct_review_balances_sub_qty_index');
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep review performance indexes in production databases.
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
