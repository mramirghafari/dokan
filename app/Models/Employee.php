<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Employee extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = [
        'name',
        'isActive',
        'organization_id',
        'tenant_id',
        'personalID',
        'parentUnit_id',
        'childUnit_id',
        'unit_id',
        'national_code',
        'personnel_code',
        'father_name',
        'mobile',
        'job_title',
        'employment_type',
        'hire_date_en',
        'hire_date_fa',
        'insurance_number',
        'bank_name',
        'bank_account',
        'sheba',
        'marital_status',
        'children_count',
        'military_status',
        'employment_status',
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'hire_date_en' => 'date',
        'children_count' => 'integer',
    ];

    public const EMPLOYMENT_TYPES = [
        'official' => 'رسمی',
        'contractual' => 'قراردادی',
        'daily' => 'روزمزد',
        'hourly' => 'ساعتی',
    ];

    public const MARITAL_STATUSES = [
        'single' => 'مجرد',
        'married' => 'متاهل',
    ];

    public const MILITARY_STATUSES = [
        'done' => 'پایان خدمت',
        'exempt' => 'معاف',
        'not_required' => 'مشمول نمی‌شود',
    ];

    public const EMPLOYMENT_STATUSES = [
        'active' => 'شاغل',
        'suspended' => 'تعلیق',
        'terminated' => 'خاتمه همکاری',
    ];

    public function getEmploymentTypeLabelAttribute(): string
    {
        return self::EMPLOYMENT_TYPES[$this->employment_type] ?? '-';
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return self::EMPLOYMENT_STATUSES[$this->employment_status] ?? 'شاغل';
    }

    public function parentUnit()
    {
        return $this->belongsTo(Unit::class, 'parentUnit_id');
    }

    public function childUnit()
    {
        return $this->belongsTo(Unit::class, 'childUnit_id');
    }

    public function organization()
    {
        return $this->BelongsTo(Organization::class);
    }

    public function payrollContracts()
    {
        return $this->hasMany(PayrollContract::class);
    }

    public function activePayrollContract()
    {
        return $this->hasOne(PayrollContract::class)->where('status', 'active')->latestOfMany();
    }

    public function payrollAttendanceSummaries()
    {
        return $this->hasMany(PayrollAttendanceSummary::class);
    }
}
