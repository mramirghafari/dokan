<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRun extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'number',
        'title',
        'period_year',
        'period_month',
        'payroll_date_en',
        'payroll_date_fa',
        'status',
        'gross_salary',
        'benefits_amount',
        'employee_insurance_amount',
        'employer_insurance_amount',
        'tax_amount',
        'other_deductions_amount',
        'net_pay_amount',
        'payable_amount',
        'paid_amount',
        'payment_status',
        'legal_report_json',
        'created_by',
        'approved_by',
        'approved_at',
        'canceled_at',
        'canceled_by',
        'description',
    ];

    protected $casts = [
        'payroll_date_en' => 'date',
        'gross_salary' => 'decimal:2',
        'benefits_amount' => 'decimal:2',
        'employee_insurance_amount' => 'decimal:2',
        'employer_insurance_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'other_deductions_amount' => 'decimal:2',
        'net_pay_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'legal_report_json' => 'array',
        'approved_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PayrollRunItem::class);
    }

    public function accountingVoucher()
    {
        return $this->hasOne(Voucher::class, 'source_id')->where('source_type', self::class)->where('document_type', 'payroll_accrual');
    }

    public function payments()
    {
        return $this->hasMany(PayrollRunPayment::class);
    }

    public function components()
    {
        return $this->hasMany(PayrollRunItemComponent::class);
    }
}
