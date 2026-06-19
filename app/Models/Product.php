<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder; // کلاس درست
use App\Traits\HasOrganizationFilter;
use App\Traits\HasOrganizationScopes;
use Illuminate\Support\Facades\DB;


class Product extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes, HasOrganizationFilter, HasOrganizationScopes;
    protected $fillable = ['title', 'display_name', 'orderLimit', 'category_id', 'entity', 'brand_id', 'isActive', 'user_id', 'description', 'organization_id', 'tenant_id', 'store_id', 'sku', 'product_type', 'stock_tracking_mode', 'traceability_mode', 'requires_expiry_tracking', 'requires_serial_tracking', 'valuation_method', 'parentCategory_id', 'childCategory_id', 'start_date', 'exp_date', 'pr_unit', 'base_unit_id', 'pr_sub_unit', 'secondary_unit_id', 'pr_weight', 'pr_weight_txt', 'pack_items', 'unit_conversion_factor', 'pr_weight', 'pr_weight_unit', 'pack_weight', 'pack_weight_unit', 'pack_weight_txt', 'price', 'purchase_price', 'cost_price', 'representative_price', 'wholesale_price', 'consumer_price', 'discount', 'max_discount_amount', 'tax', 'fee_masraf', 'photo', 'attrs', 'item_sale_status', 'pack_sale_status', 'order_quantity_mode', 'isFreez', 'isNotify', 'set_price', 'isMaterial', 'depot_id', 'updated_at'];

    public const ORDER_QUANTITY_MODES = [
        'none' => 'تک‌فروشی (تعداد ثابت ۱)',
        'main_unit' => 'فقط واحد اصلی',
        'secondary_unit' => 'فقط واحد فرعی',
        'both' => 'هر دو واحد',
    ];

    protected $casts = [
        'unit_conversion_factor' => 'decimal:6',
        'purchase_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'representative_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'consumer_price' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'requires_expiry_tracking' => 'boolean',
        'requires_serial_tracking' => 'boolean',
    ];

    public const PRODUCT_TYPE_LABELS = [
        'goods' => 'کالای قابل فروش',
        'material' => 'مواد اولیه',
        'service' => 'خدمت',
        'bundle' => 'بسته/کیت',
    ];

    public const STOCK_TRACKING_LABELS = [
        'tracked' => 'کنترل موجودی فعال',
        'non_tracked' => 'بدون کنترل موجودی',
    ];

    public const TRACEABILITY_MODE_LABELS = [
        'none' => 'بدون ردیابی',
        'batch' => 'ردیابی batch/lot',
        'serial' => 'ردیابی سریال',
        'attributes' => 'ردیابی ویژگی ها',
    ];

    public const VALUATION_METHOD_LABELS = [
        'weighted_average' => 'میانگین موزون',
        'manual' => 'نرخ دستی',
    ];

    public function productTypeText(): string
    {
        return self::PRODUCT_TYPE_LABELS[$this->product_type ?: 'goods'] ?? self::PRODUCT_TYPE_LABELS['goods'];
    }

    public function resolveOrderQuantityMode(): string
    {
        $mode = (string) ($this->order_quantity_mode ?? '');

        if ($mode !== '' && isset(self::ORDER_QUANTITY_MODES[$mode])) {
            return $mode;
        }

        $pack = (bool) $this->pack_sale_status;
        $item = (bool) $this->item_sale_status;

        return match (true) {
            $pack && $item => 'both',
            $pack => 'secondary_unit',
            $item => 'main_unit',
            default => 'none',
        };
    }

    /**
     * @return array{item_sale_status: int, pack_sale_status: int}
     */
    public static function saleFlagsForQuantityMode(string $mode): array
    {
        return match ($mode) {
            'none' => ['item_sale_status' => 0, 'pack_sale_status' => 0],
            'main_unit' => ['item_sale_status' => 1, 'pack_sale_status' => 0],
            'secondary_unit' => ['item_sale_status' => 0, 'pack_sale_status' => 1],
            'both' => ['item_sale_status' => 1, 'pack_sale_status' => 1],
            default => ['item_sale_status' => 1, 'pack_sale_status' => 0],
        };
    }

    /**
     * @return array{pack: int, tedad: int}
     */
    public function fixedOrderQuantities(): array
    {
        if ($this->usesDurationEncodedQuantity()) {
            return [
                'pack' => max(1, (int) $this->pack_items),
                'tedad' => 1,
            ];
        }

        return [
            'pack' => 0,
            'tedad' => 1,
        ];
    }

    public function usesDurationEncodedQuantity(): bool
    {
        $unit = trim((string) $this->pr_unit);

        return ($this->product_type ?? '') === 'service'
            && in_array($unit, ['ماه', 'month'], true)
            && (int) ($this->pack_items ?? 0) >= 1;
    }

    public function stockTrackingText(): string
    {
        return self::STOCK_TRACKING_LABELS[$this->stock_tracking_mode ?: 'tracked'] ?? self::STOCK_TRACKING_LABELS['tracked'];
    }

    public function traceabilityModeText(): string
    {
        return self::TRACEABILITY_MODE_LABELS[$this->traceability_mode ?: 'none'] ?? self::TRACEABILITY_MODE_LABELS['none'];
    }

    public function valuationMethodText(): string
    {
        return self::VALUATION_METHOD_LABELS[$this->valuation_method ?: 'weighted_average'] ?? self::VALUATION_METHOD_LABELS['weighted_average'];
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parentCategory_id');
    }

    public function childCategory()
    {
        return $this->belongsTo(Category::class, 'childCategory_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function secondaryUnit()
    {
        return $this->belongsTo(Unit::class, 'secondary_unit_id');
    }

    public function priceListItems()
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function pricePeriods()
    {
        return $this->hasMany(ProductPricePeriod::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->belongsToMany(Detail::class);
    }

    public function depots()
    {
        return $this->hasMany(Depot::class, 'pr_id', 'id');
    }

    public function inventoryBalances()
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function pishfactorItems()
    {
        return $this->hasMany(PishFactorItems::class, 'pr_id');
    }


    public function currentStock($storeId = null)
    {
        // 1- ورودی‌ها (Depot)
        $totalIn = Depot::where('pr_id', $this->id)
            ->when($storeId, function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->sum('entity');

        // 2- خروجی‌ها (PishFactorItems با status = 1 یا 4)
        $soldItems = \App\Models\PishFactorItems::where('pr_id', $this->id)
            ->when($storeId, function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->whereHas('pishfactor', function ($q) {
                $q->whereIn('status', [1, 4]);
            })
            ->get();

        // 3- محاسبه تعداد فروخته شده به واحد اصلی محصول
        $totalOut = $soldItems->reduce(function ($carry, $item) {
            $packCount = $item->pack ?? 0;
            $singleCount = $item->tedad ?? 0;
            $packSize = $this->pack_items ?? 1; // از مدل Product
            return intval($carry) + (intval($packCount) * intval($packSize)) + intval($singleCount);
        }, 0);

        // 4- موجودی فعلی
        return $totalIn - $totalOut;
    }

    public function getCreatedAtAttribute($created_at)
    {
        $v1 = new \Hekmatinasser\Verta\Verta($created_at);
        $v1 = $v1->format('H:m:s - Y/m/d');
        return $v1;
    }

    public function getUpdatedAtAttribute($updated_at)
    {
        $v1 = new \Hekmatinasser\Verta\Verta($updated_at);
        $v1 = $v1->format('H:m:s - Y/m/d');
        return $v1;
    }

    public function getFirstPeriodMain($storeId)
    {
        return $this->depots()
            ->where('store_id', $storeId)
            ->whereHas('receipt', function ($q) {
                $q->where('type', 5);
            })
            ->sum('entity');
    }

    public function getFirstPeriodSub($storeId)
    {
        $packItems = $this->pack_items ?: 1;

        return $this->depots()
            ->where('store_id', $storeId)
            ->whereHas('receipt', function ($q) {
                $q->where('type', 5);
            })
            ->sum('entity') / $packItems;
    }

    public function getInputMain($storeId)
    {
        return $this->depots()
            ->where('store_id', $storeId)
            ->whereHas('receipt', function ($q) {
                $q->whereIn('type', [1, 2, 3, 4]);
            })
            ->sum('entity');
    }

    public function getInputSub($storeId)
    {
        $packItems = $this->pack_items ?: 1;

        return $this->depots()
            ->where('store_id', $storeId)
            ->whereHas('receipt', function ($q) {
                $q->whereIn('type', [1, 2, 3, 4]);
            })
            ->sum('entity') / $packItems;
    }

    public function getOutputMain()
    {
        $packItems = $this->pack_items ?: 1;

        return $this->pishfactorItems()
            ->whereHas('pishfactor', function ($q) {
                $q->whereIn('status', [1, 4]);
            })
            ->get()
            ->reduce(function ($carry, $item) use ($packItems) {
                $packCount  = intval($item->pack) * $packItems;
                $singleCount = intval($item->tedad);
                return $carry + $packCount + $singleCount;
            }, 0);
    }

    public function getOutputSub()
    {
        $packItems = $this->pack_items ?: 1;
        $totalMain = $this->getOutputMain();

        return round($totalMain / $packItems, 2);
    }

    public function getCurrentStock($storeId)
    {
        return $this->getFirstPeriodMain($storeId)
            + $this->getInputMain($storeId)
            - $this->getOutputMain();
    }

    protected static function booted()
    {
        static::addGlobalScope('withCurrentStock', function (Builder $builder) {
            $builder
                ->select('products.*')

                // مجموع ورودی‌ها
                ->selectSub(function ($q) {
                    $q->from('depots')
                        ->selectRaw('COALESCE(SUM(entity),0)')
                        ->whereColumn('depots.pr_id', 'products.id');
                }, 'total_in')

                // مجموع خروجی‌ها
                ->selectSub(function ($q) {
                    $q->from('pish_factor_items')
                        ->join('pishfactors', 'pish_factor_items.pishfactor_id', '=', 'pishfactors.id')
                        ->whereColumn('pish_factor_items.pr_id', 'products.id')
                        ->whereIn('pishfactors.status', [1, 4])
                        ->selectRaw(
                            'COALESCE(SUM((pish_factor_items.pack * products.pack_items) + pish_factor_items.tedad),0)'
                        );
                }, 'total_out')

                // موجودی فعلی
                ->selectSub(function ($q) {
                    $q->from('depots as d')
                        ->selectRaw('
                            COALESCE(SUM(d.entity),0) -
                            COALESCE((
                                SELECT SUM((pfi.pack * products.pack_items) + pfi.tedad)
                                FROM pish_factor_items pfi
                                JOIN pishfactors pf ON pfi.pishfactor_id = pf.id
                                WHERE pfi.pr_id = products.id
                                AND pf.status IN (1,4)
                            ),0)
                        ')
                        ->whereColumn('d.pr_id', 'products.id');
                }, 'current_stock');
        });
    }

    public function scopeWithSalesReport(
        $query,
        $startDate = null,         // تاریخ ثبت شروع
        $endDate = null,           // تاریخ ثبت پایان
        $deliveryStart = null,     // تاریخ تحویل شروع
        $deliveryEnd = null        // تاریخ تحویل پایان
    ) {
        $totalInSQL = "(SELECT COALESCE(SUM(entity), 0)
                    FROM depots
                    WHERE depots.pr_id = products.id)";

        $totalOutSQL = "(SELECT COALESCE(SUM((pish_factor_items.pack * products.pack_items) + pish_factor_items.tedad), 0)
                     FROM pish_factor_items
                     JOIN pishfactors ON pish_factor_items.pishfactor_id = pishfactors.id
                     WHERE pish_factor_items.pr_id = products.id
                     AND pishfactors.status IN (1, 4))";
        $pishfactorSourceType = DB::connection()->getPdo()->quote(Pishfactor::class);
        $costOfGoodsSoldSQL = "SELECT im.product_id, COALESCE(SUM(im.total_cost), 0) as cost_of_goods_sold
                    FROM inventory_movements im
                    JOIN pishfactors im_pf ON im_pf.id = im.source_id
                WHERE im.source_type = {$pishfactorSourceType}
                    AND im.movement_type = 'sale'
                    AND im.direction = 'out'
                    AND im_pf.status IN (1, 4)";

        if ($startDate && $endDate) {
            $costOfGoodsSoldSQL .= " AND im_pf.created_at BETWEEN " . DB::connection()->getPdo()->quote($startDate) . " AND " . DB::connection()->getPdo()->quote($endDate);
        }

        if ($deliveryStart && $deliveryEnd) {
            $costOfGoodsSoldSQL .= " AND im_pf.recive_date BETWEEN " . DB::connection()->getPdo()->quote($deliveryStart) . " AND " . DB::connection()->getPdo()->quote($deliveryEnd);
        }

        $costOfGoodsSoldSQL .= " GROUP BY im.product_id";

        $query->select('products.*')
            ->addSelect(DB::raw('COUNT(DISTINCT pfi.pishfactor_id) as invoice_count'))
            ->addSelect(DB::raw('SUM((pfi.pack * products.pack_items) + pfi.tedad) as total_qty'))
            ->addSelect(DB::raw("
            SUM(
                (
                    ((pfi.pack * products.pack_items) + pfi.tedad)
                    * pfi.price
                )
                * (1 - (COALESCE(pfi.discount,0) / 100))
                * (1 + (COALESCE(products.tax,0) / 100))
            ) as total_amount
        "))
            ->addSelect(DB::raw("COALESCE(cogs.cost_of_goods_sold, 0) as cost_of_goods_sold"))
            ->addSelect(DB::raw("$totalInSQL as total_in"))
            ->addSelect(DB::raw("$totalOutSQL as total_out"))
            ->addSelect(DB::raw("($totalInSQL - $totalOutSQL) as current_stock"))
            ->addSelect(DB::raw("(($totalInSQL - $totalOutSQL) * products.price) as stock_value"))
            ->leftJoin('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->leftJoin('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->leftJoin(DB::raw("({$costOfGoodsSoldSQL}) as cogs"), 'products.id', '=', 'cogs.product_id')
            ->whereIn('pf.status', [1, 4])
            ->groupBy('products.id');

        // فیلتر تاریخ ثبت فاکتور
        if ($startDate && $endDate) {
            $query->whereBetween('pf.created_at', [$startDate, $endDate]);
        }

        // فیلتر تاریخ تحویل، اگر هر دو مقدار باشه
        if ($deliveryStart && $deliveryEnd) {
            $query->whereBetween('pf.recive_date', [$deliveryStart, $deliveryEnd]);
        }

        return $query;
    }

    public function scopeWithSalesReportOnlySales($query, $startDate = null, $endDate = null)
    {
        $query->select('products.*')
            ->addSelect(DB::raw('COUNT(DISTINCT pfi.pishfactor_id) as invoice_count'))
            ->addSelect(DB::raw('SUM((pfi.pack * products.pack_items) + pfi.tedad) as total_qty'))
            ->addSelect(DB::raw("
            SUM(
                (
                    ((pfi.pack * products.pack_items) + pfi.tedad)
                    * pfi.price
                )
                * (1 - (pfi.discount / 100))
                * (1 + (products.tax / 100))
            ) as total_amount
        "))
            ->leftJoin('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->leftJoin('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->whereIn('pf.status', [1, 4])
            ->groupBy('products.id');

        // فیلتر تاریخ اگر داده شده باشد
        if ($startDate && $endDate) {
            $query->whereBetween('pf.created_at', [$startDate, $endDate]);
        }

        return $query;
    }
}
