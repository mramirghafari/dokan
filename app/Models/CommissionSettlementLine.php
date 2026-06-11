<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSettlementLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_settlement_id',
        'pishfactor_id',
        'pish_factor_item_id',
        'product_id',
        'customer_id',
        'area_id',
        'region_id',
        'quantity',
        'invoice_amount',
        'net_amount',
        'calculation_base_amount',
        'rate_percent',
        'commission_amount',
        'reason',
    ];

    public function settlement()
    {
        return $this->belongsTo(CommissionSettlement::class, 'commission_settlement_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }

    public function item()
    {
        return $this->belongsTo(PishFactorItems::class, 'pish_factor_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
