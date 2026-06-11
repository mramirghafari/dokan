<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialPeriodClosings extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('financial_period_closings')) {
            Schema::create('financial_period_closings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('fiscal_year_id')->nullable()->index();
                $table->unsignedBigInteger('next_fiscal_year_id')->nullable()->index();
                $table->date('period_start')->nullable()->index();
                $table->date('period_end')->nullable()->index();
                $table->unsignedBigInteger('closing_voucher_id')->nullable()->index();
                $table->unsignedBigInteger('opening_voucher_id')->nullable()->index();
                $table->decimal('total_debit', 18, 2)->default(0);
                $table->decimal('total_credit', 18, 2)->default(0);
                $table->decimal('opening_total_debit', 18, 2)->default(0);
                $table->decimal('opening_total_credit', 18, 2)->default(0);
                $table->unsignedInteger('accounts_count')->default(0);
                $table->unsignedInteger('opening_accounts_count')->default(0);
                $table->string('status', 30)->default('closed')->index();
                $table->json('balances_snapshot')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable()->index();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'fiscal_year_id'], 'period_closings_tenant_year_unique');
            });
        }
    }

    public function down()
    {
        // Non-destructive rollback: closing vouchers and audit snapshots must stay intact.
    }
}
