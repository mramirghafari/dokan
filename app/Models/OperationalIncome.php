<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationalIncome extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'revenue_center_id',
        'income_type_id',
        'income_account_id',
        'receipt_account_id',
        'voucher_id',
        'income_number',
        'income_date_en',
        'income_date_fa',
        'status',
        'receipt_status',
        'amount',
        'reference_number',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'income_date_en' => 'date',
        'amount' => 'decimal:2',
    ];

    public function revenueCenter()
    {
        return $this->belongsTo(RevenueCenter::class);
    }

    public function incomeType()
    {
        return $this->belongsTo(IncomeType::class);
    }

    public function incomeAccount()
    {
        return $this->belongsTo(Accounts::class, 'income_account_id');
    }

    public function receiptAccount()
    {
        return $this->belongsTo(Accounts::class, 'receipt_account_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function financialAttachments()
    {
        return $this->morphMany(FinancialAttachment::class, 'attachable')->latest();
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
