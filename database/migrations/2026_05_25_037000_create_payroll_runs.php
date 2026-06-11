<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payroll_runs')) {
            Schema::create('payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('number', 80)->index();
                $table->string('title', 191)->nullable();
                $table->unsignedSmallInteger('period_year')->index();
                $table->unsignedTinyInteger('period_month')->index();
                $table->date('payroll_date_en')->nullable()->index();
                $table->string('payroll_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved')->index();
                $table->decimal('gross_salary', 18, 2)->default(0);
                $table->decimal('benefits_amount', 18, 2)->default(0);
                $table->decimal('employee_insurance_amount', 18, 2)->default(0);
                $table->decimal('employer_insurance_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('other_deductions_amount', 18, 2)->default(0);
                $table->decimal('net_pay_amount', 18, 2)->default(0);
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->unsignedInteger('canceled_by')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'period_year', 'period_month'], 'payroll_runs_period_index');
            });
        }

        if (!Schema::hasTable('payroll_run_items')) {
            Schema::create('payroll_run_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_run_id')->index();
                $table->unsignedBigInteger('employee_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->decimal('base_salary', 18, 2)->default(0);
                $table->decimal('benefits_amount', 18, 2)->default(0);
                $table->decimal('employee_insurance_amount', 18, 2)->default(0);
                $table->decimal('employer_insurance_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('other_deductions_amount', 18, 2)->default(0);
                $table->decimal('gross_salary', 18, 2)->default(0);
                $table->decimal('net_pay_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: payroll and accounting audit data must remain intact.
    }
};
