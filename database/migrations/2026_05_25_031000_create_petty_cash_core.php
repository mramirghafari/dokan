<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('petty_cash_funds')) {
            Schema::create('petty_cash_funds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('account_id')->index();
                $table->unsignedBigInteger('custodian_user_id')->nullable()->index();
                $table->string('fund_code', 60)->nullable()->index();
                $table->string('title', 180);
                $table->string('custodian_name')->nullable();
                $table->decimal('ceiling_amount', 18, 2)->default(0);
                $table->string('status', 30)->default('active')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status'], 'petty_cash_funds_tenant_status_index');
            });
        }

        if (!Schema::hasTable('petty_cash_transactions')) {
            Schema::create('petty_cash_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('petty_cash_fund_id')->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedBigInteger('expense_id')->nullable()->index();
                $table->unsignedBigInteger('counter_account_id')->nullable()->index();
                $table->unsignedBigInteger('cost_center_id')->nullable()->index();
                $table->unsignedBigInteger('expense_type_id')->nullable()->index();
                $table->string('transaction_type', 30)->index();
                $table->date('transaction_date_en')->nullable()->index();
                $table->string('transaction_date_fa', 20)->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->string('status', 30)->default('approved')->index();
                $table->string('reference_number', 120)->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['petty_cash_fund_id', 'transaction_type'], 'petty_cash_tx_fund_type_index');
                $table->index(['tenant_id', 'transaction_date_en'], 'petty_cash_tx_tenant_date_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep petty cash operational and accounting history intact.
    }
};
