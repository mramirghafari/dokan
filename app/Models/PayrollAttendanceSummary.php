<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollAttendanceSummary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'employee_id',
        'period_year',
        'period_month',
        'work_days',
        'work_hours',
        'overtime_hours',
        'absence_days',
        'leave_days',
        'mission_days',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'work_days' => 'decimal:2',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'absence_days' => 'decimal:2',
        'leave_days' => 'decimal:2',
        'mission_days' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
