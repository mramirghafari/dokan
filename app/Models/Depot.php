<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['pr_id', 'receipt_id', 'source_depot_id', 'tenant_id', 'entity', 'entity_sub_unit', 'store_id', 'warehouse_location_id', 'batch_no', 'lot_no', 'serial_no', 'manufactured_at', 'expiry_date', 'color', 'size', 'quality_grade', 'weight', 'tracking_notes', 'return_reason', 'status', 'updated_at'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'pr_id', 'id');
    }
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function sourceDepot()
    {
        return $this->belongsTo(self::class, 'source_depot_id');
    }

    public function returnDepots()
    {
        return $this->hasMany(self::class, 'source_depot_id');
    }

    /**
     * دریافت موجودی فعلی
     *
     * @param  int  $productId  شناسه محصول
     * @param  int|null  $storeId  شناسه انبار (اختیاری)
     * @return float
     */
    public static function getStock($productId, $storeId = null)
    {
        return self::query()
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->where('pr_id', $productId)
            ->sum('entity'); // فرض می‌کنیم ستونی به نام quantity داری
    }
}
