<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('income_types')) {
            Schema::create('income_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('account_id')->nullable();
                $table->string('code', 60)->nullable();
                $table->string('name', 180);
                $table->string('income_group', 50)->default('operational');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'income_group'], 'income_types_tenant_group_index');
            });
        }

        if (!Schema::hasTable('operational_incomes')) {
            Schema::create('operational_incomes', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedBigInteger('revenue_center_id')->nullable();
                $table->unsignedBigInteger('income_type_id')->nullable();
                $table->unsignedInteger('income_account_id')->nullable();
                $table->unsignedInteger('receipt_account_id')->nullable();
                $table->unsignedBigInteger('voucher_id')->nullable();
                $table->string('income_number', 60)->nullable();
                $table->date('income_date_en')->nullable();
                $table->string('income_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved');
                $table->string('receipt_status', 30)->default('registered');
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('reference_number', 120)->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'income_date_en'], 'operational_incomes_tenant_date_index');
                $table->index(['revenue_center_id'], 'operational_incomes_revenue_center_index');
                $table->index(['income_type_id'], 'operational_incomes_type_index');
                $table->index(['voucher_id'], 'operational_incomes_voucher_index');
            });
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'income_id')) {
                    $table->unsignedBigInteger('income_id')->nullable()->after('expense_id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: income records and voucher attribution are financial audit data.
    }
};
