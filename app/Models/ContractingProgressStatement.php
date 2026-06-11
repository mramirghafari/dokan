<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractingProgressStatement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contracting_project_id',
        'voucher_id',
        'tenant_id',
        'organization_id',
        'statement_number',
        'statement_date_en',
        'statement_date_fa',
        'period_from_en',
        'period_to_en',
        'gross_amount',
        'previous_amount',
        'current_amount',
        'retention_amount',
        'advance_deduction_amount',
        'tax_amount',
        'payable_amount',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'statement_date_en' => 'date',
        'period_from_en' => 'date',
        'period_to_en' => 'date',
        'gross_amount' => 'decimal:2',
        'previous_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'advance_deduction_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(ContractingProject::class, 'contracting_project_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function items()
    {
        return $this->hasMany(ContractingProgressStatementItem::class, 'contracting_progress_statement_id');
    }
}
