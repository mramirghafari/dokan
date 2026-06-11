<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contracting_projects')) {
            Schema::create('contracting_projects', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('project_code', 60)->index();
                $table->string('title');
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->string('contract_number', 80)->nullable()->index();
                $table->string('contract_type', 40)->default('construction')->index();
                $table->string('status', 30)->default('active')->index();
                $table->date('start_date_en')->nullable();
                $table->string('start_date_fa', 20)->nullable();
                $table->date('end_date_en')->nullable();
                $table->string('end_date_fa', 20)->nullable();
                $table->decimal('contract_amount', 18, 2)->default(0);
                $table->decimal('approved_budget', 18, 2)->default(0);
                $table->decimal('retention_percent', 8, 4)->default(0);
                $table->decimal('advance_payment_percent', 8, 4)->default(0);
                $table->decimal('performance_bond_percent', 8, 4)->default(0);
                $table->decimal('vat_percent', 8, 4)->default(0);
                $table->unsignedBigInteger('receivable_account_id')->nullable();
                $table->unsignedBigInteger('revenue_account_id')->nullable();
                $table->unsignedBigInteger('advance_account_id')->nullable();
                $table->unsignedBigInteger('retention_account_id')->nullable();
                $table->unsignedBigInteger('tax_account_id')->nullable();
                $table->unsignedBigInteger('cost_account_id')->nullable();
                $table->unsignedBigInteger('payable_account_id')->nullable();
                $table->unsignedBigInteger('guarantee_control_account_id')->nullable();
                $table->unsignedBigInteger('guarantee_commitment_account_id')->nullable();
                $table->unsignedBigInteger('cost_center_id')->nullable()->index();
                $table->unsignedBigInteger('project_manager_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('contracting_project_items')) {
            Schema::create('contracting_project_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contracting_project_id')->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('item_code', 80)->nullable()->index();
                $table->string('title');
                $table->string('unit', 40)->nullable();
                $table->decimal('quantity', 18, 4)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->decimal('executed_quantity', 18, 4)->default(0);
                $table->decimal('executed_amount', 18, 2)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('contracting_progress_statements')) {
            Schema::create('contracting_progress_statements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contracting_project_id')->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('statement_number', 80)->index();
                $table->date('statement_date_en')->nullable()->index();
                $table->string('statement_date_fa', 20)->nullable();
                $table->date('period_from_en')->nullable();
                $table->date('period_to_en')->nullable();
                $table->decimal('gross_amount', 18, 2)->default(0);
                $table->decimal('previous_amount', 18, 2)->default(0);
                $table->decimal('current_amount', 18, 2)->default(0);
                $table->decimal('retention_amount', 18, 2)->default(0);
                $table->decimal('advance_deduction_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('payable_amount', 18, 2)->default(0);
                $table->string('status', 30)->default('draft')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('contracting_progress_statement_items')) {
            Schema::create('contracting_progress_statement_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contracting_progress_statement_id')->index('cpsi_statement_index');
                $table->unsignedBigInteger('contracting_project_item_id')->nullable()->index('cpsi_project_item_index');
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('item_code', 80)->nullable();
                $table->string('title');
                $table->string('unit', 40)->nullable();
                $table->decimal('quantity', 18, 4)->default(0);
                $table->decimal('previous_quantity', 18, 4)->default(0);
                $table->decimal('cumulative_quantity', 18, 4)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('gross_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contracting_guarantees')) {
            Schema::create('contracting_guarantees', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contracting_project_id')->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('guarantee_number', 80)->index();
                $table->string('guarantee_type', 40)->default('performance')->index();
                $table->string('issuer')->nullable();
                $table->string('beneficiary')->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->date('issue_date_en')->nullable();
                $table->string('issue_date_fa', 20)->nullable();
                $table->date('expiry_date_en')->nullable()->index();
                $table->string('expiry_date_fa', 20)->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('contracting_cost_entries')) {
            Schema::create('contracting_cost_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contracting_project_id')->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('cost_number', 80)->index();
                $table->date('cost_date_en')->nullable()->index();
                $table->string('cost_date_fa', 20)->nullable();
                $table->string('cost_type', 40)->default('direct')->index();
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->unsignedBigInteger('cost_account_id')->nullable();
                $table->unsignedBigInteger('tax_account_id')->nullable();
                $table->unsignedBigInteger('payable_account_id')->nullable();
                $table->string('status', 30)->default('posted')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $this->addIndexIfMissing('contracting_projects', ['tenant_id', 'project_code'], 'contracting_projects_tenant_code_index');
        $this->addIndexIfMissing('contracting_progress_statements', ['contracting_project_id', 'statement_number'], 'contracting_statements_project_number_index');
        $this->addIndexIfMissing('contracting_cost_entries', ['contracting_project_id', 'cost_date_en'], 'contracting_costs_project_date_index');
    }

    public function down(): void
    {
        // Non-destructive migration: keep contract, progress, guarantee and voucher audit data intact.
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
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
