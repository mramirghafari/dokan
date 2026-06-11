<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payroll_contracts')) {
            Schema::create('payroll_contracts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->string('contract_number', 80)->nullable()->index();
                $table->string('contract_type', 40)->default('monthly')->index();
                $table->string('job_title', 120)->nullable();
                $table->date('start_date_en')->nullable()->index();
                $table->string('start_date_fa', 20)->nullable();
                $table->date('end_date_en')->nullable()->index();
                $table->string('end_date_fa', 20)->nullable();
                $table->decimal('base_salary', 18, 2)->default(0);
                $table->decimal('daily_wage', 18, 2)->default(0);
                $table->decimal('hourly_wage', 18, 2)->default(0);
                $table->decimal('fixed_allowance_amount', 18, 2)->default(0);
                $table->decimal('housing_allowance_amount', 18, 2)->default(0);
                $table->decimal('child_allowance_amount', 18, 2)->default(0);
                $table->decimal('tax_exemption_amount', 18, 2)->default(0);
                $table->decimal('tax_rate', 8, 4)->default(0);
                $table->decimal('employee_insurance_rate', 8, 4)->default(0);
                $table->decimal('employer_insurance_rate', 8, 4)->default(0);
                $table->decimal('work_days_per_month', 8, 2)->default(30);
                $table->decimal('daily_work_hours', 8, 2)->default(7.33);
                $table->string('status', 30)->default('active')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'employee_id', 'status'], 'payroll_contracts_employee_status_index');
            });
        }

        if (!Schema::hasTable('payroll_attendance_summaries')) {
            Schema::create('payroll_attendance_summaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->unsignedSmallInteger('period_year')->index();
                $table->unsignedTinyInteger('period_month')->index();
                $table->decimal('work_days', 8, 2)->default(0);
                $table->decimal('work_hours', 10, 2)->default(0);
                $table->decimal('overtime_hours', 10, 2)->default(0);
                $table->decimal('absence_days', 8, 2)->default(0);
                $table->decimal('leave_days', 8, 2)->default(0);
                $table->decimal('mission_days', 8, 2)->default(0);
                $table->string('status', 30)->default('approved')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['employee_id', 'period_year', 'period_month'], 'payroll_attendance_employee_period_unique');
            });
        }

        if (Schema::hasTable('payroll_runs')) {
            Schema::table('payroll_runs', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_runs', 'payable_amount')) {
                    $table->decimal('payable_amount', 18, 2)->default(0)->after('net_pay_amount');
                }

                if (!Schema::hasColumn('payroll_runs', 'paid_amount')) {
                    $table->decimal('paid_amount', 18, 2)->default(0)->after('payable_amount');
                }

                if (!Schema::hasColumn('payroll_runs', 'payment_status')) {
                    $table->string('payment_status', 30)->default('unpaid')->after('paid_amount')->index();
                }

                if (!Schema::hasColumn('payroll_runs', 'legal_report_json')) {
                    $table->json('legal_report_json')->nullable()->after('payment_status');
                }
            });
        }

        if (Schema::hasTable('payroll_run_items')) {
            Schema::table('payroll_run_items', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_run_items', 'payroll_contract_id')) {
                    $table->unsignedBigInteger('payroll_contract_id')->nullable()->after('employee_id')->index();
                }

                if (!Schema::hasColumn('payroll_run_items', 'payroll_attendance_summary_id')) {
                    $table->unsignedBigInteger('payroll_attendance_summary_id')->nullable()->after('payroll_contract_id')->index();
                }

                if (!Schema::hasColumn('payroll_run_items', 'work_days')) {
                    $table->decimal('work_days', 8, 2)->default(0)->after('organization_id');
                }

                if (!Schema::hasColumn('payroll_run_items', 'work_hours')) {
                    $table->decimal('work_hours', 10, 2)->default(0)->after('work_days');
                }

                if (!Schema::hasColumn('payroll_run_items', 'overtime_hours')) {
                    $table->decimal('overtime_hours', 10, 2)->default(0)->after('work_hours');
                }

                if (!Schema::hasColumn('payroll_run_items', 'absence_days')) {
                    $table->decimal('absence_days', 8, 2)->default(0)->after('overtime_hours');
                }

                if (!Schema::hasColumn('payroll_run_items', 'leave_days')) {
                    $table->decimal('leave_days', 8, 2)->default(0)->after('absence_days');
                }

                if (!Schema::hasColumn('payroll_run_items', 'overtime_amount')) {
                    $table->decimal('overtime_amount', 18, 2)->default(0)->after('benefits_amount');
                }

                if (!Schema::hasColumn('payroll_run_items', 'bonus_amount')) {
                    $table->decimal('bonus_amount', 18, 2)->default(0)->after('overtime_amount');
                }

                if (!Schema::hasColumn('payroll_run_items', 'mission_amount')) {
                    $table->decimal('mission_amount', 18, 2)->default(0)->after('bonus_amount');
                }

                if (!Schema::hasColumn('payroll_run_items', 'loan_deduction_amount')) {
                    $table->decimal('loan_deduction_amount', 18, 2)->default(0)->after('other_deductions_amount');
                }

                if (!Schema::hasColumn('payroll_run_items', 'advance_deduction_amount')) {
                    $table->decimal('advance_deduction_amount', 18, 2)->default(0)->after('loan_deduction_amount');
                }

                if (!Schema::hasColumn('payroll_run_items', 'insurance_subject_amount')) {
                    $table->decimal('insurance_subject_amount', 18, 2)->default(0)->after('advance_deduction_amount');
                }

                if (!Schema::hasColumn('payroll_run_items', 'taxable_amount')) {
                    $table->decimal('taxable_amount', 18, 2)->default(0)->after('insurance_subject_amount');
                }
            });
        }

        if (!Schema::hasTable('payroll_run_item_components')) {
            Schema::create('payroll_run_item_components', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_run_id')->index();
                $table->unsignedBigInteger('payroll_run_item_id')->index();
                $table->unsignedBigInteger('employee_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('component_type', 30)->index();
                $table->string('component_code', 80)->index();
                $table->string('title', 191);
                $table->decimal('quantity', 18, 4)->default(0);
                $table->decimal('rate', 18, 4)->default(0);
                $table->decimal('amount', 18, 2)->default(0);
                $table->boolean('is_taxable')->default(false);
                $table->boolean('is_insurable')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('payroll_run_payments')) {
            Schema::create('payroll_run_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_run_id')->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('payment_number', 80)->nullable()->index();
                $table->date('payment_date_en')->nullable()->index();
                $table->string('payment_date_fa', 20)->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->unsignedTinyInteger('payment_method')->default(3);
                $table->unsignedBigInteger('treasury_account_id')->nullable()->index();
                $table->unsignedBigInteger('payment_terminal_id')->nullable()->index();
                $table->string('issuing_bank', 120)->nullable();
                $table->string('cheque_number', 120)->nullable();
                $table->date('due_date')->nullable();
                $table->string('status', 30)->default('approved')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('canceled_by')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep payroll contracts, attendance, payslip, payment, and legal report traces intact.
    }
};
