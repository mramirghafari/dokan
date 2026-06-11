<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'account_category')) {
                    $table->string('account_category', 40)->nullable()->after('type');
                }
                if (!Schema::hasColumn('accounts', 'detail_type')) {
                    $table->string('detail_type', 40)->nullable()->after('account_category');
                }
                if (!Schema::hasColumn('accounts', 'is_control')) {
                    $table->boolean('is_control')->default(false)->after('detail_type');
                }
                if (!Schema::hasColumn('accounts', 'is_system')) {
                    $table->boolean('is_system')->default(false)->after('is_control');
                }
                if (!Schema::hasColumn('accounts', 'cost_center_required')) {
                    $table->boolean('cost_center_required')->default(false)->after('is_system');
                }
                if (!Schema::hasColumn('accounts', 'floating_detail_required')) {
                    $table->boolean('floating_detail_required')->default(false)->after('cost_center_required');
                }
            });
        }

        if (!Schema::hasTable('cost_centers')) {
            Schema::create('cost_centers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('code', 60)->nullable();
                $table->string('name', 180);
                $table->string('center_type', 40)->default('branch');
                $table->string('allocation_basis', 40)->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'center_type'], 'cost_centers_tenant_type_index');
                $table->index(['tenant_id', 'store_id'], 'cost_centers_tenant_store_index');
            });
        }

        if (!Schema::hasTable('expense_types')) {
            Schema::create('expense_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('account_id')->nullable();
                $table->string('code', 60)->nullable();
                $table->string('name', 180);
                $table->string('expense_group', 40)->default('operational');
                $table->string('cost_behavior', 40)->default('indirect');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'expense_group'], 'expense_types_tenant_group_index');
            });
        }

        if (!Schema::hasTable('operational_expenses')) {
            Schema::create('operational_expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedBigInteger('cost_center_id')->nullable();
                $table->unsignedBigInteger('expense_type_id')->nullable();
                $table->unsignedInteger('expense_account_id')->nullable();
                $table->unsignedInteger('settlement_account_id')->nullable();
                $table->unsignedBigInteger('voucher_id')->nullable();
                $table->string('expense_number', 60)->nullable();
                $table->date('expense_date_en')->nullable();
                $table->string('expense_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved');
                $table->string('payment_status', 30)->default('unpaid');
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->string('reference_number', 120)->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'expense_date_en'], 'operational_expenses_tenant_date_index');
                $table->index(['cost_center_id'], 'operational_expenses_cost_center_index');
                $table->index(['expense_type_id'], 'operational_expenses_type_index');
                $table->index(['voucher_id'], 'operational_expenses_voucher_index');
            });
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'cost_center_id')) {
                    $table->unsignedBigInteger('cost_center_id')->nullable()->after('account_id');
                }
                if (!Schema::hasColumn('voucher_items', 'expense_id')) {
                    $table->unsignedBigInteger('expense_id')->nullable()->after('cost_center_id');
                }
                if (!Schema::hasColumn('voucher_items', 'branch_id')) {
                    $table->unsignedInteger('branch_id')->nullable()->after('expense_id');
                }
                if (!Schema::hasColumn('voucher_items', 'project_code')) {
                    $table->string('project_code', 80)->nullable()->after('branch_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep expense, cost-center and accounting trace data intact.
    }
};
