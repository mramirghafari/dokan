<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollAttendanceSummary;
use App\Models\PayrollContract;
use App\Models\PayrollRun;
use App\Models\PayrollRunPayment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function __construct(private AccountingPostingService $accountingService) {}

    public function createRun(array $payload, $user): PayrollRun
    {
        return DB::transaction(function () use ($payload, $user) {
            $tenantId = $this->tenantId($user);
            $organizationId = $this->organizationId($user);
            $date = Arr::get($payload, 'payroll_date_en') ?: now()->toDateString();
            $year = (int) (Arr::get($payload, 'period_year') ?: verta($date)->format('Y'));
            $month = (int) (Arr::get($payload, 'period_month') ?: verta($date)->format('n'));
            $rows = $this->normalizeRows($payload, $tenantId, $organizationId);

            if (empty($rows)) {
                throw ValidationException::withMessages([
                    'items' => 'برای ثبت حقوق حداقل یک ردیف معتبر لازم است.',
                ]);
            }

            $totals = $this->totals($rows);

            $run = PayrollRun::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'number' => $this->nextNumber($tenantId),
                'title' => Arr::get($payload, 'title') ?: 'حقوق ' . $year . '/' . $month,
                'period_year' => $year,
                'period_month' => $month,
                'payroll_date_en' => $date,
                'payroll_date_fa' => $this->jalaliDate($date),
                'status' => 'approved',
                'gross_salary' => $totals['gross_salary'],
                'benefits_amount' => $totals['benefits_amount'],
                'employee_insurance_amount' => $totals['employee_insurance_amount'],
                'employer_insurance_amount' => $totals['employer_insurance_amount'],
                'tax_amount' => $totals['tax_amount'],
                'other_deductions_amount' => $totals['other_deductions_amount'],
                'net_pay_amount' => $totals['net_pay_amount'],
                'payable_amount' => $totals['net_pay_amount'],
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'legal_report_json' => $this->legalReportPayload($rows, $totals),
                'created_by' => $user?->id,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'description' => Arr::get($payload, 'description'),
            ]);

            foreach ($rows as $row) {
                $components = $row['components'];
                unset($row['components']);

                $item = $run->items()->create($row);

                foreach ($components as $component) {
                    $item->components()->create(array_merge($component, [
                        'payroll_run_id' => $run->id,
                        'payroll_run_item_id' => $item->id,
                    ]));
                }
            }

            $this->accountingService->postPayrollRunVoucher($run, $user);

            return $run->fresh(['items.employee', 'items.contract', 'items.attendanceSummary', 'items.components', 'payments.voucher', 'accountingVoucher.items.account']) ?: $run;
        });
    }

    public function saveContract(array $payload, $user): PayrollContract
    {
        $employee = Employee::findOrFail((int) Arr::get($payload, 'employee_id'));
        $tenantId = $this->tenantId($user) ?: $employee->tenant_id;
        $organizationId = $this->organizationId($user) ?: $this->normalizeOrganizationId($employee->organization_id);
        $startDate = Arr::get($payload, 'start_date_en') ?: now()->toDateString();
        $endDate = Arr::get($payload, 'end_date_en');
        $contractId = Arr::get($payload, 'payroll_contract_id');

        return DB::transaction(function () use ($payload, $user, $employee, $tenantId, $organizationId, $startDate, $endDate, $contractId) {
            $attributes = [
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'employee_id' => $employee->id,
                'contract_number' => Arr::get($payload, 'contract_number') ?: $this->nextContractNumber($tenantId),
                'contract_type' => Arr::get($payload, 'contract_type', 'monthly'),
                'job_title' => Arr::get($payload, 'job_title'),
                'start_date_en' => $startDate,
                'start_date_fa' => $this->jalaliDate($startDate),
                'end_date_en' => $endDate,
                'end_date_fa' => $endDate ? $this->jalaliDate($endDate) : null,
                'base_salary' => $this->money(Arr::get($payload, 'base_salary', 0)),
                'daily_wage' => $this->money(Arr::get($payload, 'daily_wage', 0)),
                'hourly_wage' => $this->money(Arr::get($payload, 'hourly_wage', 0)),
                'fixed_allowance_amount' => $this->money(Arr::get($payload, 'fixed_allowance_amount', 0)),
                'housing_allowance_amount' => $this->money(Arr::get($payload, 'housing_allowance_amount', 0)),
                'child_allowance_amount' => $this->money(Arr::get($payload, 'child_allowance_amount', 0)),
                'tax_exemption_amount' => $this->money(Arr::get($payload, 'tax_exemption_amount', 0)),
                'tax_rate' => $this->rate(Arr::get($payload, 'tax_rate', 0)),
                'employee_insurance_rate' => $this->rate(Arr::get($payload, 'employee_insurance_rate', 0)),
                'employer_insurance_rate' => $this->rate(Arr::get($payload, 'employer_insurance_rate', 0)),
                'work_days_per_month' => $this->quantity(Arr::get($payload, 'work_days_per_month', 30)),
                'daily_work_hours' => $this->quantity(Arr::get($payload, 'daily_work_hours', 7.33)),
                'status' => Arr::get($payload, 'status', 'active'),
                'description' => Arr::get($payload, 'description'),
                'updated_by' => $user?->id,
            ];

            if ($attributes['status'] === 'active') {
                PayrollContract::where('employee_id', $employee->id)
                    ->when($contractId, fn($query) => $query->where('id', '<>', (int) $contractId))
                    ->where('status', 'active')
                    ->update(['status' => 'closed', 'updated_by' => $user?->id]);
            }

            if ($contractId) {
                $contract = PayrollContract::whereKey((int) $contractId)->firstOrFail();
                $contract->update($attributes);

                return $contract->refresh();
            }

            $attributes['created_by'] = $user?->id;

            return PayrollContract::create($attributes);
        });
    }

    public function saveAttendance(array $payload, $user): PayrollAttendanceSummary
    {
        $employee = Employee::findOrFail((int) Arr::get($payload, 'employee_id'));
        $tenantId = $this->tenantId($user) ?: $employee->tenant_id;
        $organizationId = $this->organizationId($user) ?: $this->normalizeOrganizationId($employee->organization_id);
        $year = (int) Arr::get($payload, 'period_year');
        $month = (int) Arr::get($payload, 'period_month');

        return PayrollAttendanceSummary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period_year' => $year,
                'period_month' => $month,
            ],
            [
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'work_days' => $this->quantity(Arr::get($payload, 'work_days', 0)),
                'work_hours' => $this->quantity(Arr::get($payload, 'work_hours', 0)),
                'overtime_hours' => $this->quantity(Arr::get($payload, 'overtime_hours', 0)),
                'absence_days' => $this->quantity(Arr::get($payload, 'absence_days', 0)),
                'leave_days' => $this->quantity(Arr::get($payload, 'leave_days', 0)),
                'mission_days' => $this->quantity(Arr::get($payload, 'mission_days', 0)),
                'status' => Arr::get($payload, 'status', 'approved'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]
        );
    }

    public function createPayment(PayrollRun $run, array $payload, $user): PayrollRunPayment
    {
        return DB::transaction(function () use ($run, $payload, $user) {
            $run = PayrollRun::whereKey($run->id)->lockForUpdate()->firstOrFail();

            if ($run->status === 'canceled') {
                throw ValidationException::withMessages(['payroll' => 'لیست حقوق ابطال شده قابل پرداخت نیست.']);
            }

            $amount = $this->money(Arr::get($payload, 'amount', 0));
            $payable = $this->money($run->payable_amount ?: $run->net_pay_amount);
            $paid = $this->money($run->paid_amount);
            $remaining = max(0, round($payable - $paid, 2));

            if ($amount <= 0 || $amount > $remaining) {
                throw ValidationException::withMessages(['amount' => 'مبلغ پرداخت حقوق باید بزرگتر از صفر و حداکثر برابر مانده پرداختنی باشد.']);
            }

            $date = Arr::get($payload, 'payment_date_en') ?: now()->toDateString();
            $payment = PayrollRunPayment::create([
                'payroll_run_id' => $run->id,
                'tenant_id' => $run->tenant_id,
                'organization_id' => $run->organization_id,
                'payment_number' => $this->nextPaymentNumber($run->tenant_id),
                'payment_date_en' => $date,
                'payment_date_fa' => $this->jalaliDate($date),
                'amount' => $amount,
                'payment_method' => (int) Arr::get($payload, 'payment_method', 3),
                'treasury_account_id' => Arr::get($payload, 'treasury_account_id'),
                'payment_terminal_id' => Arr::get($payload, 'payment_terminal_id'),
                'issuing_bank' => Arr::get($payload, 'issuing_bank'),
                'cheque_number' => Arr::get($payload, 'cheque_number'),
                'due_date' => Arr::get($payload, 'due_date'),
                'status' => 'approved',
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $voucher = $this->accountingService->postPayrollPaymentVoucher($payment, $payload, $user);
            $payment->update(['voucher_id' => $voucher?->id]);

            $newPaid = round($paid + $amount, 2);
            $run->update([
                'paid_amount' => $newPaid,
                'payment_status' => $newPaid >= $payable ? 'paid' : 'partial',
            ]);

            return $payment->fresh(['payrollRun', 'voucher.items.account']) ?: $payment;
        });
    }

    public function cancel(PayrollRun $run, $user): PayrollRun
    {
        return DB::transaction(function () use ($run, $user) {
            $run = $run->fresh(['accountingVoucher']) ?: $run;

            if ($run->status === 'canceled') {
                return $run;
            }

            if ((float) $run->paid_amount > 0) {
                throw ValidationException::withMessages([
                    'payroll' => 'لیست حقوق دارای پرداخت ثبت شده است و قبل از کنترل پرداخت ها قابل ابطال نیست.',
                ]);
            }

            if ($run->accountingVoucher && $run->accountingVoucher->is_permanent) {
                throw ValidationException::withMessages([
                    'payroll' => 'لیست حقوق دارای سند دائم است و از این مسیر قابل ابطال نیست.',
                ]);
            }

            $this->accountingService->removePayrollRunVoucher($run);

            $run->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'canceled_by' => $user?->id,
            ]);

            return $run->fresh(['items.employee', 'payments', 'accountingVoucher']) ?: $run;
        });
    }

    private function normalizeRows(array $payload, ?int $tenantId, ?int $organizationId): array
    {
        $employeeIds = Arr::get($payload, 'employee_id', []);
        $baseSalaries = Arr::get($payload, 'base_salary', []);
        $benefits = Arr::get($payload, 'benefits_amount', []);
        $employeeInsurances = Arr::get($payload, 'employee_insurance_amount', []);
        $employerInsurances = Arr::get($payload, 'employer_insurance_amount', []);
        $taxes = Arr::get($payload, 'tax_amount', []);
        $otherDeductions = Arr::get($payload, 'other_deductions_amount', []);
        $workDays = Arr::get($payload, 'work_days', []);
        $workHours = Arr::get($payload, 'work_hours', []);
        $overtimeHours = Arr::get($payload, 'overtime_hours', []);
        $absenceDays = Arr::get($payload, 'absence_days', []);
        $leaveDays = Arr::get($payload, 'leave_days', []);
        $overtimeAmounts = Arr::get($payload, 'overtime_amount', []);
        $bonusAmounts = Arr::get($payload, 'bonus_amount', []);
        $missionAmounts = Arr::get($payload, 'mission_amount', []);
        $loanDeductions = Arr::get($payload, 'loan_deduction_amount', []);
        $advanceDeductions = Arr::get($payload, 'advance_deduction_amount', []);
        $descriptions = Arr::get($payload, 'item_description', []);
        $rows = [];

        foreach ($employeeIds as $index => $employeeId) {
            if (!$employeeId) {
                continue;
            }

            $employee = Employee::find((int) $employeeId);
            if (!$employee) {
                continue;
            }

            $contract = $this->activeContract($employee);
            $attendance = $this->attendanceSummary($employee, (int) Arr::get($payload, 'period_year'), (int) Arr::get($payload, 'period_month'));
            $contractDays = $this->quantity($contract?->work_days_per_month ?: 30);
            $contractHours = round($contractDays * $this->quantity($contract?->daily_work_hours ?: 7.33), 2);
            $rowWorkDays = $this->quantity($workDays[$index] ?? $attendance?->work_days ?: $contractDays);
            $rowWorkHours = $this->quantity($workHours[$index] ?? $attendance?->work_hours ?: $contractHours);
            $rowOvertimeHours = $this->quantity($overtimeHours[$index] ?? $attendance?->overtime_hours ?: 0);
            $rowAbsenceDays = $this->quantity($absenceDays[$index] ?? $attendance?->absence_days ?: 0);
            $rowLeaveDays = $this->quantity($leaveDays[$index] ?? $attendance?->leave_days ?: 0);
            $baseSalary = $this->providedMoney($baseSalaries, $index, $this->baseSalaryFromContract($contract, $rowWorkDays, $contractDays));
            $fixedBenefits = $this->money($contract?->fixed_allowance_amount) + $this->money($contract?->housing_allowance_amount) + $this->money($contract?->child_allowance_amount);
            $overtimeAmount = $this->providedMoney($overtimeAmounts, $index, $this->overtimeAmount($contract, $rowOvertimeHours));
            $bonusAmount = $this->providedMoney($bonusAmounts, $index, 0);
            $missionAmount = $this->providedMoney($missionAmounts, $index, 0);
            $benefitAmount = $this->providedMoney($benefits, $index, $fixedBenefits + $overtimeAmount + $bonusAmount + $missionAmount);
            $insuranceSubjectAmount = round($baseSalary + $benefitAmount, 2);
            $taxableAmount = round(max(0, $baseSalary + $benefitAmount - $this->money($contract?->tax_exemption_amount)), 2);
            $employeeInsurance = $this->providedMoney($employeeInsurances, $index, round($insuranceSubjectAmount * $this->rate($contract?->employee_insurance_rate) / 100, 2));
            $employerInsurance = $this->providedMoney($employerInsurances, $index, round($insuranceSubjectAmount * $this->rate($contract?->employer_insurance_rate) / 100, 2));
            $taxAmount = $this->providedMoney($taxes, $index, round($taxableAmount * $this->rate($contract?->tax_rate) / 100, 2));
            $otherDeduction = $this->money($otherDeductions[$index] ?? 0);
            $loanDeduction = $this->money($loanDeductions[$index] ?? 0);
            $advanceDeduction = $this->money($advanceDeductions[$index] ?? 0);
            $grossSalary = round($baseSalary + $benefitAmount, 2);
            $netPay = round($grossSalary - $employeeInsurance - $taxAmount - $otherDeduction - $loanDeduction - $advanceDeduction, 2);

            if ($grossSalary <= 0) {
                continue;
            }

            if ($netPay < 0) {
                throw ValidationException::withMessages([
                    'items' => 'کسورات حقوق نمی تواند از حقوق ناخالص بیشتر باشد.',
                ]);
            }

            $rows[] = [
                'employee_id' => $employee->id,
                'payroll_contract_id' => $contract?->id,
                'payroll_attendance_summary_id' => $attendance?->id,
                'tenant_id' => $tenantId ?: $employee->tenant_id,
                'organization_id' => $organizationId ?: $this->normalizeOrganizationId($employee->organization_id),
                'work_days' => $rowWorkDays,
                'work_hours' => $rowWorkHours,
                'overtime_hours' => $rowOvertimeHours,
                'absence_days' => $rowAbsenceDays,
                'leave_days' => $rowLeaveDays,
                'base_salary' => $baseSalary,
                'benefits_amount' => $benefitAmount,
                'overtime_amount' => $overtimeAmount,
                'bonus_amount' => $bonusAmount,
                'mission_amount' => $missionAmount,
                'employee_insurance_amount' => $employeeInsurance,
                'employer_insurance_amount' => $employerInsurance,
                'tax_amount' => $taxAmount,
                'other_deductions_amount' => $otherDeduction,
                'loan_deduction_amount' => $loanDeduction,
                'advance_deduction_amount' => $advanceDeduction,
                'insurance_subject_amount' => $insuranceSubjectAmount,
                'taxable_amount' => $taxableAmount,
                'gross_salary' => $grossSalary,
                'net_pay_amount' => $netPay,
                'description' => $descriptions[$index] ?? null,
                'components' => $this->componentRows($employee, $tenantId ?: $employee->tenant_id, $organizationId ?: $this->normalizeOrganizationId($employee->organization_id), [
                    'base_salary' => $baseSalary,
                    'benefits_amount' => $benefitAmount,
                    'overtime_amount' => $overtimeAmount,
                    'bonus_amount' => $bonusAmount,
                    'mission_amount' => $missionAmount,
                    'employee_insurance_amount' => $employeeInsurance,
                    'tax_amount' => $taxAmount,
                    'other_deductions_amount' => $otherDeduction,
                    'loan_deduction_amount' => $loanDeduction,
                    'advance_deduction_amount' => $advanceDeduction,
                    'work_days' => $rowWorkDays,
                    'overtime_hours' => $rowOvertimeHours,
                ]),
            ];
        }

        return $rows;
    }

    private function totals(array $rows): array
    {
        return [
            'gross_salary' => round(array_sum(array_column($rows, 'gross_salary')), 2),
            'benefits_amount' => round(array_sum(array_column($rows, 'benefits_amount')), 2),
            'employee_insurance_amount' => round(array_sum(array_column($rows, 'employee_insurance_amount')), 2),
            'employer_insurance_amount' => round(array_sum(array_column($rows, 'employer_insurance_amount')), 2),
            'tax_amount' => round(array_sum(array_column($rows, 'tax_amount')), 2),
            'other_deductions_amount' => round(array_sum(array_column($rows, 'other_deductions_amount')), 2),
            'net_pay_amount' => round(array_sum(array_column($rows, 'net_pay_amount')), 2),
        ];
    }

    private function legalReportPayload(array $rows, array $totals): array
    {
        return [
            'employees_count' => count($rows),
            'work_days' => round(array_sum(array_column($rows, 'work_days')), 2),
            'work_hours' => round(array_sum(array_column($rows, 'work_hours')), 2),
            'overtime_hours' => round(array_sum(array_column($rows, 'overtime_hours')), 2),
            'insurance_subject_amount' => round(array_sum(array_column($rows, 'insurance_subject_amount')), 2),
            'taxable_amount' => round(array_sum(array_column($rows, 'taxable_amount')), 2),
            'gross_salary' => $totals['gross_salary'],
            'employee_insurance_amount' => $totals['employee_insurance_amount'],
            'employer_insurance_amount' => $totals['employer_insurance_amount'],
            'tax_amount' => $totals['tax_amount'],
            'net_pay_amount' => $totals['net_pay_amount'],
        ];
    }

    private function componentRows(Employee $employee, ?int $tenantId, ?int $organizationId, array $amounts): array
    {
        $definitions = [
            ['earning', 'base_salary', 'حقوق پایه', $amounts['base_salary'], $amounts['work_days'], 0, true, true],
            ['earning', 'benefits', 'مزایا و فوق العاده ها', $amounts['benefits_amount'], 1, 0, true, true],
            ['earning', 'overtime', 'اضافه کاری', $amounts['overtime_amount'], $amounts['overtime_hours'], 0, true, true],
            ['earning', 'bonus', 'پاداش', $amounts['bonus_amount'], 1, 0, true, true],
            ['earning', 'mission', 'ماموریت', $amounts['mission_amount'], 1, 0, false, false],
            ['deduction', 'employee_insurance', 'بیمه سهم کارمند', $amounts['employee_insurance_amount'], 1, 0, false, false],
            ['deduction', 'tax', 'مالیات حقوق', $amounts['tax_amount'], 1, 0, false, false],
            ['deduction', 'other', 'سایر کسورات', $amounts['other_deductions_amount'], 1, 0, false, false],
            ['deduction', 'loan', 'کسر وام', $amounts['loan_deduction_amount'], 1, 0, false, false],
            ['deduction', 'advance', 'کسر مساعده', $amounts['advance_deduction_amount'], 1, 0, false, false],
        ];

        return collect($definitions)
            ->filter(fn($definition) => round((float) $definition[3], 2) > 0)
            ->map(fn($definition) => [
                'employee_id' => $employee->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'component_type' => $definition[0],
                'component_code' => $definition[1],
                'title' => $definition[2],
                'quantity' => $definition[4],
                'rate' => $definition[5],
                'amount' => round((float) $definition[3], 2),
                'is_taxable' => $definition[6],
                'is_insurable' => $definition[7],
                'description' => 'فیش حقوق ' . $employee->name,
            ])
            ->values()
            ->all();
    }

    private function activeContract(Employee $employee): ?PayrollContract
    {
        return PayrollContract::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->orderByDesc('start_date_en')
            ->orderByDesc('id')
            ->first();
    }

    private function attendanceSummary(Employee $employee, int $year, int $month): ?PayrollAttendanceSummary
    {
        return PayrollAttendanceSummary::where('employee_id', $employee->id)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->first();
    }

    private function baseSalaryFromContract(?PayrollContract $contract, float $workDays, float $contractDays): float
    {
        if (!$contract) {
            return 0;
        }

        if ((float) $contract->daily_wage > 0) {
            return round((float) $contract->daily_wage * $workDays, 2);
        }

        if ((float) $contract->base_salary > 0 && $contractDays > 0) {
            return round((float) $contract->base_salary * min($workDays, $contractDays) / $contractDays, 2);
        }

        return 0;
    }

    private function overtimeAmount(?PayrollContract $contract, float $overtimeHours): float
    {
        if (!$contract || $overtimeHours <= 0) {
            return 0;
        }

        $hourlyWage = (float) $contract->hourly_wage;

        if ($hourlyWage <= 0 && (float) $contract->daily_wage > 0 && (float) $contract->daily_work_hours > 0) {
            $hourlyWage = round((float) $contract->daily_wage / (float) $contract->daily_work_hours, 4);
        }

        if ($hourlyWage <= 0 && (float) $contract->base_salary > 0 && (float) $contract->work_days_per_month > 0 && (float) $contract->daily_work_hours > 0) {
            $hourlyWage = round((float) $contract->base_salary / ((float) $contract->work_days_per_month * (float) $contract->daily_work_hours), 4);
        }

        return $hourlyWage > 0 ? round($hourlyWage * $overtimeHours * 1.4, 2) : 0;
    }

    private function providedMoney(array $values, int $index, float $default): float
    {
        if (array_key_exists($index, $values) && $values[$index] !== null && $values[$index] !== '') {
            return $this->money($values[$index]);
        }

        return round($default, 2);
    }

    private function nextNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PAY-' . $year . '-';
        $query = PayrollRun::where('number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function nextContractNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PC-' . $year . '-';
        $query = PayrollContract::where('contract_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('contract_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function nextPaymentNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PYP-' . $year . '-';
        $query = PayrollRunPayment::where('payment_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('payment_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function organizationId($user): ?int
    {
        return $this->normalizeOrganizationId($user?->organization_id);
    }

    private function normalizeOrganizationId($organizationId): ?int
    {
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function jalaliDate(string $date): string
    {
        try {
            return verta($date)->format('Y/m/d');
        } catch (\Throwable $exception) {
            return $date;
        }
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function quantity($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function rate($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 4);
    }
}
