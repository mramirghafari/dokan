<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRunItemComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_run_id',
        'payroll_run_item_id',
        'employee_id',
        'tenant_id',
        'organization_id',
        'component_type',
        'component_code',
        'title',
        'quantity',
        'rate',
        'amount',
        'is_taxable',
        'is_insurable',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_insurable' => 'boolean',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function payrollRunItem()
    {
        return $this->belongsTo(PayrollRunItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
