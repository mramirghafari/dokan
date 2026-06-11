<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetDepreciation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_id',
        'policy_id',
        'tenant_id',
        'organization_id',
        'period_start_en',
        'period_end_en',
        'period_start_fa',
        'period_end_fa',
        'depreciable_amount',
        'period_amount',
        'accumulated_before',
        'accumulated_after',
        'book_value_before',
        'book_value_after',
        'voucher_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'period_start_en' => 'date',
        'period_end_en' => 'date',
        'depreciable_amount' => 'decimal:2',
        'period_amount' => 'decimal:2',
        'accumulated_before' => 'decimal:2',
        'accumulated_after' => 'decimal:2',
        'book_value_before' => 'decimal:2',
        'book_value_after' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function policy()
    {
        return $this->belongsTo(CompanyAssetDepreciationPolicy::class, 'policy_id');
    }
}
