<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationFilter;
use App\Traits\HasOrganizationScopes;
use App\Traits\SyncsTenantColumns;

class Store extends Model
{
    use HasFactory, HasOrganizationFilter, HasOrganizationScopes, SyncsTenantColumns;
    protected $fillable = [
        'title',
        'description',
        'code',
        'store_type',
        'stock_tracking_mode',
        'transfer_policy',
        'opening_inventory_status',
        'opening_inventory_locked_at',
        'opening_inventory_locked_by',
        'organization_id',
        'prs_rel',
        'isActive',
        'tenants_id',
        'tenant_id',
        'lat',
        'lang',
    ];

    protected $casts = [
        'opening_inventory_locked_at' => 'datetime',
    ];

    public const STORE_TYPE_LABELS = [
        'main' => 'انبار اصلی',
        'branch' => 'انبار شعبه',
        'distribution' => 'انبار پخش',
        'transit' => 'انبار بین راهی',
        'virtual' => 'انبار مجازی',
    ];

    public const STOCK_TRACKING_LABELS = [
        'tracked' => 'کنترل موجودی فعال',
        'non_tracked' => 'بدون کنترل موجودی',
    ];

    public const TRANSFER_POLICY_LABELS = [
        'in_out' => 'ورود و خروج مجاز',
        'in_only' => 'فقط ورود کالا',
        'out_only' => 'فقط خروج کالا',
        'blocked' => 'انتقال غیرفعال',
    ];

    public const OPENING_INVENTORY_STATUS_LABELS = [
        'open' => 'قابل ثبت',
        'locked' => 'قفل شده',
    ];

    public function storeTypeText(): string
    {
        return self::STORE_TYPE_LABELS[$this->store_type ?: 'main'] ?? self::STORE_TYPE_LABELS['main'];
    }

    public function stockTrackingText(): string
    {
        return self::STOCK_TRACKING_LABELS[$this->stock_tracking_mode ?: 'tracked'] ?? self::STOCK_TRACKING_LABELS['tracked'];
    }

    public function transferPolicyText(): string
    {
        return self::TRANSFER_POLICY_LABELS[$this->transfer_policy ?: 'in_out'] ?? self::TRANSFER_POLICY_LABELS['in_out'];
    }

    public function openingInventoryStatusText(): string
    {
        return self::OPENING_INVENTORY_STATUS_LABELS[$this->opening_inventory_status ?: 'open'] ?? self::OPENING_INVENTORY_STATUS_LABELS['open'];
    }

    public function organizationNamesText(): string
    {
        $organizationIds = $this->legacyOrganizationIds();

        if (empty($organizationIds)) {
            return '-';
        }

        return Organization::whereIn('id', $organizationIds)->pluck('title')->implode('، ') ?: '-';
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function tenants()
    {
        return $this->belongsTo(Tenants::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseLocations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    public function inventoryBalances()
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }


    public function firstPeriodMain()
    {
        return Depot::where('store_id', $this->id)
            ->whereHas('receipt', function ($q) {
                $q->where('type', 5); // اول دوره
            })
            ->sum('entity');
    }

    public function firstPeriodSub()
    {
        $main = $this->firstPeriodMain();
        $packItems = $this->avgPackItems(); // توضیح پایین
        return round($main / max($packItems, 1), 2);
    }

    public function inputMain()
    {
        return Depot::where('store_id', $this->id)
            ->whereHas('receipt', function ($q) {
                $q->whereIn('type', [1, 2, 3, 4]); // ورودی‌های عادی
            })
            ->sum('entity');
    }

    public function inputSub()
    {
        $main = $this->inputMain();
        $packItems = $this->avgPackItems();
        return round($main / max($packItems, 1), 2);
    }

    public function outputMain()
    {
        // محصولات فعال این انبار
        $products = Product::whereJsonContains('store_id', "{$this->id}")
            ->where('isActive', 1)
            ->get();

        $totalOut = 0;

        foreach ($products as $product) {
            $items = \App\Models\PishFactorItems::where('pr_id', $product->id)
                ->whereHas('pishfactor', function ($q) {
                    $q->whereIn('status', [1, 4]);
                })
                ->get();

            foreach ($items as $item) {
                $packCount   = intval($item->pack ?? 0);
                $singleCount = intval($item->tedad ?? 0);
                $packSize    = intval($product->pack_items ?? 1);

                $totalOut += ($packCount * $packSize) + $singleCount;
            }
        }

        return $totalOut;
    }

    public function outputSub()
    {
        $products = Product::whereJsonContains('store_id', "{$this->id}")
            ->where('isActive', 1)
            ->get();

        $totalSub = 0;

        foreach ($products as $product) {
            $items = \App\Models\PishFactorItems::where('pr_id', $product->id)
                ->whereHas('pishfactor', function ($q) {
                    $q->whereIn('status', [1, 4]);
                })
                ->get();

            foreach ($items as $item) {
                $packCount   = intval($item->pack ?? 0);
                $singleCount = intval($item->tedad ?? 0);
                $packSize    = intval($product->pack_items ?? 1);

                // خروجی اصلی این محصول
                $mainUnits = ($packCount * $packSize) + $singleCount;

                // تبدیل به واحد فرعی با استفاده از بسته‌بندی همان محصول
                $totalSub += $mainUnits / $packSize;
            }
        }

        return round($totalSub, 2);
    }

    public function currentStock()
    {
        return $this->firstPeriodMain()
            + $this->inputMain()
            - $this->outputMain();
    }

    public function currentStockSub()
    {
        $products = Product::whereJsonContains('store_id', "{$this->id}")
            ->where('isActive', 1)
            ->get();

        $totalSubStock = 0;

        foreach ($products as $product) {
            // ورودی اصلی محصول
            $inputMain = \App\Models\Depot::where('pr_id', $product->id)
                ->where('store_id', $this->id)
                ->sum('entity');

            // خروجی اصلی محصول
            $outputMain = \App\Models\PishFactorItems::where('pr_id', $product->id)
                ->whereHas('pishfactor', function ($q) {
                    $q->whereIn('status', [1, 4]);
                })
                ->get()
                ->reduce(function ($carry, $item) use ($product) {
                    $packCount   = intval($item->pack ?? 0);
                    $singleCount = intval($item->tedad ?? 0);
                    $packSize    = intval($product->pack_items ?? 1);
                    return $carry + ($packCount * $packSize) + $singleCount;
                }, 0);

            // موجودی اصلی
            $mainStock = $inputMain - $outputMain;

            // موجودی فرعی این محصول
            $subStock = $mainStock / max(intval($product->pack_items ?? 1), 1);

            $totalSubStock += $subStock;
        }

        // موجودی واحد فرعی کل انبار
        return round($totalSubStock, 2);
    }

    /**
     * میانگین pack_items محصولات این انبار
     * برای تبدیل واحد اصلی به فرعی
     */
    protected function avgPackItems()
    {
        return Product::whereJsonContains('store_id', "{$this->id}")
            ->avg('pack_items') ?: 1;
    }
}
