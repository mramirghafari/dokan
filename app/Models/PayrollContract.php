<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'employee_id',
        'contract_number',
        'contract_type',
        'job_title',
        'start_date_en',
        'start_date_fa',
        'end_date_en',
        'end_date_fa',
        'base_salary',
        'daily_wage',
        'hourly_wage',
        'fixed_allowance_amount',
        'housing_allowance_amount',
        'child_allowance_amount',
        'tax_exemption_amount',
        'tax_rate',
        'employee_insurance_rate',
        'employer_insurance_rate',
        'work_days_per_month',
        'daily_work_hours',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date_en' => 'date',
        'end_date_en' => 'date',
        'base_salary' => 'decimal:2',
        'daily_wage' => 'decimal:2',
        'hourly_wage' => 'decimal:2',
        'fixed_allowance_amount' => 'decimal:2',
        'housing_allowance_amount' => 'decimal:2',
        'child_allowance_amount' => 'decimal:2',
        'tax_exemption_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'employee_insurance_rate' => 'decimal:4',
        'employer_insurance_rate' => 'decimal:4',
        'work_days_per_month' => 'decimal:2',
        'daily_work_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
