<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRunItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'payroll_contract_id',
        'payroll_attendance_summary_id',
        'tenant_id',
        'organization_id',
        'work_days',
        'work_hours',
        'overtime_hours',
        'absence_days',
        'leave_days',
        'base_salary',
        'benefits_amount',
        'overtime_amount',
        'bonus_amount',
        'mission_amount',
        'employee_insurance_amount',
        'employer_insurance_amount',
        'tax_amount',
        'other_deductions_amount',
        'loan_deduction_amount',
        'advance_deduction_amount',
        'insurance_subject_amount',
        'taxable_amount',
        'gross_salary',
        'net_pay_amount',
        'description',
    ];

    protected $casts = [
        'work_days' => 'decimal:2',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'absence_days' => 'decimal:2',
        'leave_days' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'benefits_amount' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'mission_amount' => 'decimal:2',
        'employee_insurance_amount' => 'decimal:2',
        'employer_insurance_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'other_deductions_amount' => 'decimal:2',
        'loan_deduction_amount' => 'decimal:2',
        'advance_deduction_amount' => 'decimal:2',
        'insurance_subject_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_pay_amount' => 'decimal:2',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract()
    {
        return $this->belongsTo(PayrollContract::class, 'payroll_contract_id');
    }

    public function attendanceSummary()
    {
        return $this->belongsTo(PayrollAttendanceSummary::class, 'payroll_attendance_summary_id');
    }

    public function components()
    {
        return $this->hasMany(PayrollRunItemComponent::class);
    }
}
