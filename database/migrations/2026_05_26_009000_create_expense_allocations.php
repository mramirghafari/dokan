<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operational_expenses')) {
            Schema::table('operational_expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('operational_expenses', 'allocation_basis')) {
                    $table->string('allocation_basis', 40)->nullable()->index()->after('allocation_target_id');
                }

                if (!Schema::hasColumn('operational_expenses', 'allocation_note')) {
                    $table->text('allocation_note')->nullable()->after('workflow_note');
                }
            });
        }

        if (!Schema::hasTable('expense_allocations')) {
            Schema::create('expense_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('operational_expense_id')->nullable();
                $table->unsignedBigInteger('voucher_id')->nullable();
                $table->unsignedBigInteger('voucher_item_id')->nullable();
                $table->unsignedBigInteger('cost_center_id')->nullable();
                $table->unsignedBigInteger('expense_type_id')->nullable();
                $table->string('allocation_basis', 40)->default('direct');
                $table->string('allocation_target_type', 50)->default('manual');
                $table->unsignedBigInteger('allocation_target_id')->nullable();
                $table->string('target_type', 50)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('project_code', 100)->nullable();
                $table->string('contract_code', 120)->nullable();
                $table->decimal('basis_quantity', 18, 4)->nullable();
                $table->decimal('basis_value', 18, 4)->nullable();
                $table->decimal('allocation_percent', 8, 4)->default(100);
                $table->decimal('allocated_amount', 18, 2)->default(0);
                $table->decimal('amount', 18, 2)->default(0);
                $table->text('note')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'allocation_target_type'], 'expense_allocations_tenant_target_index');
                $table->index(['tenant_id', 'target_type'], 'expense_allocations_tenant_legacy_target_index');
                $table->index(['operational_expense_id'], 'expense_allocations_expense_index');
                $table->index(['voucher_id'], 'expense_allocations_voucher_index');
                $table->index(['product_id'], 'expense_allocations_product_index');
                $table->index(['project_code'], 'expense_allocations_project_index');
            });
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'expense_allocation_id')) {
                    $table->unsignedBigInteger('expense_allocation_id')->nullable()->index()->after('expense_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: expense allocation trace is financial audit data.
    }
};
