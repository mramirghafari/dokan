<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractingCostEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contracting_project_id',
        'voucher_id',
        'tenant_id',
        'organization_id',
        'cost_number',
        'cost_date_en',
        'cost_date_fa',
        'cost_type',
        'supplier_id',
        'amount',
        'tax_amount',
        'total_amount',
        'cost_account_id',
        'tax_account_id',
        'payable_account_id',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'cost_date_en' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(ContractingProject::class, 'contracting_project_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
