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
            Schema::table('vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('vouchers', 'organization_id')) {
                    $table->unsignedInteger('organization_id')->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('vouchers', 'document_type')) {
                    $table->string('document_type', 40)->default('manual')->after('method');
                }
                if (!Schema::hasColumn('vouchers', 'status')) {
                    $table->string('status', 30)->default('draft')->after('document_type');
                }
                if (!Schema::hasColumn('vouchers', 'is_permanent')) {
                    $table->boolean('is_permanent')->default(false)->after('status');
                }
                if (!Schema::hasColumn('vouchers', 'source_type')) {
                    $table->string('source_type', 80)->nullable()->after('is_permanent');
                }
                if (!Schema::hasColumn('vouchers', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
                }
                if (!Schema::hasColumn('vouchers', 'fiscal_year')) {
                    $table->string('fiscal_year', 10)->nullable()->after('source_id');
                }
                if (!Schema::hasColumn('vouchers', 'total_debit')) {
                    $table->decimal('total_debit', 18, 2)->default(0)->after('amount');
                }
                if (!Schema::hasColumn('vouchers', 'total_credit')) {
                    $table->decimal('total_credit', 18, 2)->default(0)->after('total_debit');
                }
                if (!Schema::hasColumn('vouchers', 'posted_at')) {
                    $table->timestamp('posted_at')->nullable()->after('description');
                }
                if (!Schema::hasColumn('vouchers', 'approved_by')) {
                    $table->unsignedInteger('approved_by')->nullable()->after('posted_at');
                }
                if (!Schema::hasColumn('vouchers', 'created_by')) {
                    $table->unsignedInteger('created_by')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('vouchers', 'updated_by')) {
                    $table->unsignedInteger('updated_by')->nullable()->after('created_by');
                }
            });

            $this->addIndexIfMissing('vouchers', ['tenant_id', 'status'], 'vouchers_tenant_status_index');
            $this->addIndexIfMissing('vouchers', ['voucher_number'], 'vouchers_voucher_number_index');
            $this->addIndexIfMissing('vouchers', ['source_type', 'source_id'], 'vouchers_source_index');
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'organization_id')) {
                    $table->unsignedInteger('organization_id')->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('voucher_items', 'debit_amount')) {
                    $table->decimal('debit_amount', 18, 2)->default(0)->after('amount');
                }
                if (!Schema::hasColumn('voucher_items', 'credit_amount')) {
                    $table->decimal('credit_amount', 18, 2)->default(0)->after('debit_amount');
                }
                if (!Schema::hasColumn('voucher_items', 'description')) {
                    $table->text('description')->nullable()->after('cheque_photo');
                }
            });

            $this->addIndexIfMissing('voucher_items', ['tenant_id', 'account_id'], 'voucher_items_tenant_account_index');
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep accounting audit data intact.
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($tableName) || $this->indexExists($tableName, $indexName)) {
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
