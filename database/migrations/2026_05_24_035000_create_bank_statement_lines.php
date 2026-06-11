<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bank_statement_lines')) {
            Schema::create('bank_statement_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('account_id')->nullable();
                $table->unsignedInteger('voucher_id')->nullable();
                $table->unsignedInteger('voucher_item_id')->nullable();
                $table->date('statement_date')->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->decimal('debit_amount', 18, 2)->default(0);
                $table->decimal('credit_amount', 18, 2)->default(0);
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('status', 30)->default('imported');
                $table->text('description')->nullable();
                $table->timestamp('matched_at')->nullable();
                $table->unsignedInteger('matched_by')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'account_id'], 'bank_statement_tenant_account_index');
                $table->index(['tenant_id', 'status'], 'bank_statement_tenant_status_index');
                $table->index(['voucher_id'], 'bank_statement_voucher_index');
                $table->index(['statement_date'], 'bank_statement_date_index');
            });

            return;
        }

        Schema::table('bank_statement_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_statement_lines', 'tenant_id')) {
                $table->unsignedInteger('tenant_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'organization_id')) {
                $table->unsignedInteger('organization_id')->nullable()->after('tenant_id');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'account_id')) {
                $table->unsignedInteger('account_id')->nullable()->after('organization_id');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'voucher_id')) {
                $table->unsignedInteger('voucher_id')->nullable()->after('account_id');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'voucher_item_id')) {
                $table->unsignedInteger('voucher_item_id')->nullable()->after('voucher_id');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'statement_date')) {
                $table->date('statement_date')->nullable()->after('voucher_item_id');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'reference_no')) {
                $table->string('reference_no', 100)->nullable()->after('statement_date');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'debit_amount')) {
                $table->decimal('debit_amount', 18, 2)->default(0)->after('reference_no');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'credit_amount')) {
                $table->decimal('credit_amount', 18, 2)->default(0)->after('debit_amount');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'amount')) {
                $table->decimal('amount', 18, 2)->default(0)->after('credit_amount');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'status')) {
                $table->string('status', 30)->default('imported')->after('amount');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'matched_at')) {
                $table->timestamp('matched_at')->nullable()->after('description');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'matched_by')) {
                $table->unsignedInteger('matched_by')->nullable()->after('matched_at');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('matched_by');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('bank_statement_lines', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        $this->addIndexIfMissing('bank_statement_lines', ['tenant_id', 'account_id'], 'bank_statement_tenant_account_index');
        $this->addIndexIfMissing('bank_statement_lines', ['tenant_id', 'status'], 'bank_statement_tenant_status_index');
        $this->addIndexIfMissing('bank_statement_lines', ['voucher_id'], 'bank_statement_voucher_index');
        $this->addIndexIfMissing('bank_statement_lines', ['statement_date'], 'bank_statement_date_index');
    }

    public function down(): void
    {
        // Non-destructive migration: keep bank reconciliation audit data intact.
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
