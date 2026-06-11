<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Brand;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\History;
use App\Models\InventoryAdjustment;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\InventoryTraceBalance;
use App\Models\Log;
use App\Models\Organization;
use App\Models\ProductionFormula;
use App\Models\ProductionOrder;
use App\Models\SalesInventoryReservation;
use App\Models\Store;
use App\Models\WarehouseLocation;
use App\Services\InventoryAdjustmentService;
use App\Services\InventoryReorderReportService;
use App\Services\InventorySlowMovingReportService;
use App\Services\ProductionService;
use App\Services\InventoryValuationReportService;
use App\Services\TenantSettings;
use Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Database\Eloquent\Builder;
use App\Models\MaterialStore;
use App\Models\Material;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:stocks,user')->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_warehouse_management')) {
                Alert::warning('غیرفعال', 'مدیریت انبار و موجودی برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        });
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_multi_warehouse')) {
                Alert::warning('غیرفعال', 'انتقال بین انبارها برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['entrance_transfer', 'StockTransfer', 'storeProductsForTransfer']);
    }

    public function index()
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $stocks = Stock::where('isActive', 1)->get();
            $stores = Store::where('isActive', 1)->get();
            $Products = Product::where('isActive', 1)->get();
        } elseif ($user->isAdmin == 1) {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stocks = Stock::forOrganizations($user)->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
            $Products = Product::forOrganizations($user)->where('isActive', 1)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stocks = Stock::forOrganizations($user)->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
            $Products = Product::forOrganizations($user)->where('isActive', 1)->get();
        }

        return view('stocks.index', compact('Products', 'stocks', 'categories', 'brands', 'parents', 'stores'));
    }

    public function create()
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
            $employees = Employee::where('isActive', 1)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $employees = Employee::where('isActive', 1)->where('organization_id', $user->organization_id)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
        }

        return view('stocks.create', compact('employees', 'categories', 'stores', 'brands', 'parents', 'organizations'));
    }

    public function store(Request $request)
    {

        //dd($request->all());
        $request['user_id'] = \Auth::user()->id;
        $Product = Product::find($request->pr_id);
        $Store = Store::find($request->store_id);
        $request['organization_id'] = $Store->organization_id;

        $stock = Stock::create($request->all());
        $user = \Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'ثبت تولید و موجودی جدید محصول ایجاد شد' . '-' . $Product->title
        ]);

        History::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'entity',
            'store' => $stock->store->title,
            'description' => " برای محصول " . $Product->title . " تعداد " . $stock->entity . " عدد ثبت شد"
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Stock $stock)
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
            $employees = Employee::where('isActive', 1)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
            $employees = Employee::where('isActive', 1)->where('organization_id', $user->organization_id)->get();
        }

        return view('stocks.edit', compact('employees', 'stock', 'categories', 'stores', 'brands', 'parents', 'organizations'));
    }

    public function update(StockRequest $request, Stock $stock)
    {


        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);
        $number = $stock->entity;

        $stock->update([
            'title' => $request->title,
            'description' => $request->description,
            'entity' => $request->entity,
            'brand_id' => $request->brand_id,
            'parentCategory_id' => $request->parentCategory_id,
            'chaildCategory_id' => isset($request->chaildCategory_id) ? $request->chaildCategory_id : $stock->chaildCategory_id,
            'isActive' => $request->isActive,
            'organization_id' => $request->organization_id,
            'store_id' => isset($request->store_id) ? $request->store_id : $stock->store_id,
            'inputDate' => $request->inputDate,
            'employee_id' => isset($request->employee_id) ? $request->employee_id : $stock->employee_id,
        ]);

        $user = \Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'کالا ویرایش شد' . '-' . $stock->title
        ]);

        if ($number != $stock->entity) {
            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'editEntity',
                'store' => $stock->store->title,
                'description' => " برای کالای دست دوم " . $stock->title . " از تعداد " . $number . " به تعداد " . $stock->entity . " تغییر یافت"
            ]);
        }

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return back();
    }

    public function need_entity()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $PishFactors = Pishfactor::where('status', 1)->get();
        } else {
            $PishFactors = Pishfactor::where('status', 1)->where('organization_id', $user->organization_id)->get();
        }


        // استخراج محصولات و محاسبه مجموع tedad و pack
        $Items = $PishFactors->flatMap(function ($pishfactor) {
            return $pishfactor->items->map(function ($item) {
                return [
                    'product_id' => $item->pr_id, // شناسه محصول
                    'tedad' => $item->tedad,     // تعداد
                    'pack' => $item->pack        // تعداد بسته‌بندی
                ];
            });
        })->groupBy('product_id')->map(function ($group) {
            return [
                'total_tedad' => $group->sum('tedad'),
                'total_pack' => $group->sum('pack')
            ];
        });

        return view('stocks.need-stock', compact('Items'));
    }

    public function entrance()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
        } else {
            $Stores = Store::forOrganizations($user)->get();
        }


        return view('stocks.entrance', compact('Stores'));
    }

    public function entrance_transfer()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
        } else {
            $Stores = Store::forOrganizations($user)->get();
        }


        return view('stocks.entrance_transfer', compact('Stores'));
    }

    public function storeProducts(Store $store)
    {

        $Products =  Product::whereJsonContains('store_id', "$store->id")
            ->withSum(['depots as total_entity' => function (Builder $query) {
                $query->select(DB::raw("COALESCE(SUM(entity), 0)"));
            }], 'entity')
            ->withSum(['depots as total_entity_sub_unit' => function (Builder $query) {
                $query->select(DB::raw("COALESCE(SUM(entity_sub_unit), 0)"));
            }], 'entity_sub_unit')
            ->withSum(['pishfactorItems as total_pack' => function (Builder $query) {
                $query->select(DB::raw("COALESCE(SUM(pack), 0)"));
            }], 'pack')
            ->withSum(['pishfactorItems as total_tedad' => function (Builder $query) {
                $query->select(DB::raw("COALESCE(SUM(tedad), 0)"));
            }], 'tedad')
            ->get();
        return view('stocks.store_products', compact('store', 'Products'));
    }

    public function storeProductsForTransfer(Store $store)
    {

        $Products = Product::whereJsonContains('store_id', "$store->id")->get();
        return view('stocks.store_products_for_transfer', compact('store', 'Products'));
    }

    public function storeProductCartex(Store $store, Product $product)
    {
        // ۱) depots رو با receipt بگیر
        $Depots = Depot::with(['receipt' => function ($q) {
            $q->select('*');
        }])
            ->where('pr_id', $product->id)
            ->where('store_id', $store->id)
            ->get();


        // ۲) فروش روزانه فقط برای پیش‌فاکتورهای با status = 1 یا 4
        $salesByDay = DB::table('pish_factor_items')
            ->join('pishfactors', 'pish_factor_items.pishfactor_id', '=', 'pishfactors.id')
            ->select(
                DB::raw('DATE(pishfactors.created_at) as date'),
                DB::raw('SUM(pish_factor_items.pack) as total_pack'),
                DB::raw('SUM(pish_factor_items.tedad) as total_tedad')
            )
            ->where('pish_factor_items.pr_id', $product->id)
            ->whereIn('pishfactors.status', [1, 4])
            ->groupBy(DB::raw('DATE(pishfactors.created_at)'))
            ->get()
            ->keyBy('date');

        // ۳) شروع ساخت تایم‌لاین
        $timeline = collect();

        // اضافه کردن رکوردهای فروش
        foreach ($salesByDay as $date => $sale) {
            $timeline->push([
                'type'       => 'sale',
                'date'       => $date,
                // محاسبه کل خروجی به واحد اصلی
                'total_main' => ($sale->total_pack * ($product->pack_items ?? 1)) + $sale->total_tedad,
                'pack'       => $sale->total_pack,
                'tedad'      => $sale->total_tedad,
                'details'    => null,
            ]);
        }

        // اضافه کردن رکوردهای Depot همراه با Receipt
        foreach ($Depots as $depot) {
            $timeline->push([
                'type'       => 'depot',
                'date'       => $depot->created_at->format('Y-m-d'),
                'tedad'      => $depot->entity,
                'receipt'    => $depot->receipt,
            ]);
        }

        // ۴) سورت نهایی بر اساس تاریخ (قدیمی → جدید)
        $timeline = $timeline->sortBy('date')->values();

        return view('stocks.store_product_depot', compact('store', 'product', 'timeline'));
    }



    public function PrCartexList()
    {

        $user = \Auth::user();
        if ($user->store_id != null) {
            $stores = Store::whereIn('id', $user->store_id)->pluck('id');
        } else {
            $stores = Store::pluck('id')->toArray();
        }


        $query = Product::query();

        foreach ($stores as $id) {
            $query->orWhereJsonContains('store_id', "$id");
        }

        $Products = $query->get();
        $productBalances = InventoryBalance::query()
            ->forOrganizations($user)
            ->select('product_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(quantity_sub_unit) as quantity_sub_unit'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');


        return view('stocks.ProductListCartex', compact('Products', 'productBalances'));
    }

    public function PrCartex(Product $product)
    {
        $user = \Auth::user();
        $movements = InventoryMovement::query()
            ->with(['store', 'warehouseLocation', 'receipt'])
            ->forOrganizations($user)
            ->where('product_id', $product->id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        if ($movements->isNotEmpty()) {
            $runningBalance = 0;
            $timeline = $movements->map(function ($movement) use (&$runningBalance) {
                $quantity = (float) $movement->quantity;
                $signedQuantity = $movement->direction === 'out' ? -1 * $quantity : $quantity;
                $runningBalance += $signedQuantity;

                return [
                    'date' => optional($movement->occurred_at)->format('Y-m-d H:i') ?: optional($movement->created_at)->format('Y-m-d H:i'),
                    'store' => optional($movement->store)->title ?: '-',
                    'location' => optional($movement->warehouseLocation)->path ?: '-',
                    'document' => $movement->reference_no ?: ($movement->receipt_id ? 'رسید #' . $movement->receipt_id : '-'),
                    'type' => $this->movementTypeLabel($movement->movement_type),
                    'in_quantity' => $movement->direction === 'in' ? $quantity : 0,
                    'out_quantity' => $movement->direction === 'out' ? $quantity : 0,
                    'balance' => $runningBalance,
                    'description' => $movement->description,
                ];
            })->sortByDesc('date')->values();

            return view('stocks.productCartex', compact('timeline', 'product'));
        }

        // فرض: اینا کوئری‌های اصلی شما هستن
        $Depots = Depot::where('pr_id', $product->id)->get();

        $salesByDay = DB::table('pish_factor_items')
            ->join('pishfactors', 'pish_factor_items.pishfactor_id', '=', 'pishfactors.id')
            ->select(
                DB::raw('DATE(pishfactors.created_at) as date'),
                DB::raw('SUM(pish_factor_items.pack) as total_pack'),
                DB::raw('SUM(pish_factor_items.tedad) as total_tedad')
            )
            ->where('pish_factor_items.pr_id', $product->id)
            ->groupBy(DB::raw('DATE(pishfactors.created_at)'))
            ->get()
            ->keyBy('date');

        $timeline = collect();

        // اضافه کردن رکوردهای فروش روزانه
        foreach ($salesByDay as $date => $sale) {
            $timeline->push([
                'type' => 'sale',
                'date' => $date,
                'pack' => $sale->total_pack,
                'tedad' => $sale->total_tedad,
                'details' => null,
            ]);
        }

        // اضافه کردن رکوردهای Depot (ورودی‌ها)
        foreach ($Depots as $depot) {
            $timeline->push([
                'type' => 'depot',
                'date' => $depot->created_at->format('Y-m-d'),
                'pack' => $depot->entity_sub_unit,
                'tedad' => $depot->entity,
                'details' => $depot, // می‌تونی اطلاعات کامل depot رو اینجا نگه داری
            ]);
        }

        // سورت نهایی بر اساس تاریخ از آخر به اول
        $timeline = $timeline->sortByDesc('date')->values();

        return view('stocks.productCartex', compact('timeline', 'product'));
    }

    public function store_cartex()
    {

        $user = \Auth::user();
        if ($user->store_id != null) {
            $stores = Store::whereIn('id', $user->store_id)->get();
        } else {
            $stores = Store::all();
        }

        $storeSummaries = InventoryBalance::query()
            ->forOrganizations($user)
            ->select(
                'store_id',
                DB::raw('COUNT(DISTINCT product_id) as products_count'),
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(quantity_sub_unit) as quantity_sub_unit'),
                DB::raw('MIN(quantity) as minimum_quantity')
            )
            ->groupBy('store_id')
            ->get()
            ->keyBy('store_id');

        return view('stocks.storeCartex', compact('stores', 'storeSummaries'));
    }

    public function inventoryBalances(Request $request)
    {
        $user = \Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();

        $balances = InventoryBalance::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->forOrganizations($user)
            ->when($request->filled('store_id'), fn($query) => $query->where('store_id', $request->store_id))
            ->when($request->filled('product_id'), fn($query) => $query->where('product_id', $request->product_id))
            ->orderBy('store_id')
            ->orderBy('product_id')
            ->paginate(100)
            ->withQueryString();

        return view('stocks.inventory_balances', compact('balances', 'stores'));
    }

    public function inventoryMovements(Request $request)
    {
        $user = \Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $movements = InventoryMovement::query()
            ->with(['product', 'store', 'warehouseLocation', 'receipt'])
            ->forOrganizations($user)
            ->when($request->filled('store_id'), fn($query) => $query->where('store_id', $request->store_id))
            ->when($request->filled('product_id'), fn($query) => $query->where('product_id', $request->product_id))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(100)
            ->withQueryString();

        return view('stocks.inventory_movements', compact('movements', 'stores'));
    }

    public function inventoryAdjustments()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $adjustments = InventoryAdjustment::query()
            ->with(['store', 'user', 'items.product'])
            ->forOrganizations($user)
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('stocks.inventory_adjustments.index', compact('adjustments'));
    }

    public function inventoryTraceability(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $balances = InventoryTraceBalance::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->forOrganizations($user))
            ->when($request->filled('store_id'), fn($query) => $query->where('store_id', $request->store_id))
            ->when($request->filled('product_id'), fn($query) => $query->where('product_id', $request->product_id))
            ->when($request->filled('batch_no'), fn($query) => $query->where('batch_no', 'like', '%' . $request->batch_no . '%'))
            ->when($request->filled('lot_no'), fn($query) => $query->where('lot_no', 'like', '%' . $request->lot_no . '%'))
            ->when($request->filled('serial_no'), fn($query) => $query->where('serial_no', 'like', '%' . $request->serial_no . '%'))
            ->when($request->filled('expiry_to'), fn($query) => $query->whereDate('expiry_date', '<=', $request->expiry_to))
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->orderByDesc('last_movement_at')
            ->paginate(50)
            ->withQueryString();

        $movements = InventoryMovement::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->forOrganizations($user))
            ->when($request->filled('store_id'), fn($query) => $query->where('store_id', $request->store_id))
            ->when($request->filled('product_id'), fn($query) => $query->where('product_id', $request->product_id))
            ->when($request->filled('batch_no'), fn($query) => $query->where('batch_no', 'like', '%' . $request->batch_no . '%'))
            ->when($request->filled('lot_no'), fn($query) => $query->where('lot_no', 'like', '%' . $request->lot_no . '%'))
            ->when($request->filled('serial_no'), fn($query) => $query->where('serial_no', 'like', '%' . $request->serial_no . '%'))
            ->where(function ($query) {
                $query->whereNotNull('batch_no')
                    ->orWhereNotNull('lot_no')
                    ->orWhereNotNull('serial_no')
                    ->orWhereNotNull('expiry_date')
                    ->orWhereNotNull('color')
                    ->orWhereNotNull('size')
                    ->orWhereNotNull('quality_grade');
            })
            ->orderByDesc('occurred_at')
            ->limit(100)
            ->get();

        return view('stocks.traceability.index', compact('balances', 'movements', 'stores'));
    }

    public function inventoryReservations(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $reservations = SalesInventoryReservation::query()
            ->with(['product', 'store', 'warehouseLocation', 'pishfactor'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->forOrganizations($user))
            ->when($request->filled('store_id'), fn($query) => $query->where('store_id', $request->store_id))
            ->when($request->filled('product_id'), fn($query) => $query->where('product_id', $request->product_id))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('batch_no'), fn($query) => $query->where('batch_no', 'like', '%' . $request->batch_no . '%'))
            ->when($request->filled('serial_no'), fn($query) => $query->where('serial_no', 'like', '%' . $request->serial_no . '%'))
            ->orderByRaw("FIELD(status, 'reserved', 'consumed', 'released')")
            ->orderByDesc('reserved_at')
            ->paginate(100)
            ->withQueryString();

        $summaryQuery = SalesInventoryReservation::query()
            ->when((int) $user->isGod !== 1, fn($query) => $query->forOrganizations($user));

        $summary = [
            'reserved' => (clone $summaryQuery)->where('status', 'reserved')->sum('quantity'),
            'consumed' => (clone $summaryQuery)->where('status', 'consumed')->sum('quantity'),
            'released' => (clone $summaryQuery)->where('status', 'released')->sum('quantity'),
        ];

        return view('stocks.inventory_reservations.index', compact('reservations', 'stores', 'summary'));
    }

    public function inventoryValuation(Request $request, InventoryValuationReportService $service)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $warehouseLocations = $user->isGod == 1
            ? WarehouseLocation::where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->get()
            : WarehouseLocation::forOrganizations($user)->where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->get();

        $report = $service->build($user, $request->only(['from_date', 'to_date', 'store_id', 'warehouse_location_id', 'product_id']));

        return view('stocks.inventory_valuation.index', compact('report', 'stores', 'warehouseLocations'));
    }

    public function inventoryReorder(Request $request, InventoryReorderReportService $service)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $report = $service->build($user, $request->only(['store_id', 'product_id', 'status']));

        return view('stocks.inventory_reorder', compact('report', 'stores'));
    }

    public function inventorySlowMoving(Request $request, InventorySlowMovingReportService $service)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $report = $service->build($user, $request->only(['from_date', 'to_date', 'store_id', 'product_id', 'status', 'slow_threshold']));

        return view('stocks.inventory_slow_moving', compact('report', 'stores'));
    }

    public function createInventoryAdjustment()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $stores = $user->isGod == 1 ? Store::where('isActive', 1)->get() : Store::forOrganizations($user)->where('isActive', 1)->get();
        $warehouseLocations = $user->isGod == 1
            ? WarehouseLocation::where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->get()
            : WarehouseLocation::forOrganizations($user)->where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->get();

        return view('stocks.inventory_adjustments.create', compact('stores', 'warehouseLocations'));
    }

    public function storeInventoryAdjustment(Request $request, InventoryAdjustmentService $service)
    {
        $payload = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'date_fa' => ['nullable', 'string', 'max:20'],
            'date_en' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.warehouse_location_id' => ['nullable', 'integer'],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        $adjustment = $service->createDraft($payload, \Illuminate\Support\Facades\Auth::user());

        Alert::success('ثبت شد', 'سند انبارگردانی به صورت پیش نویس ثبت شد.');
        return redirect()->route('stocks.inventoryAdjustments')->with('created_adjustment_id', $adjustment->id);
    }

    public function approveInventoryAdjustment(InventoryAdjustment $adjustment, InventoryAdjustmentService $service)
    {
        $service->approve($adjustment, \Illuminate\Support\Facades\Auth::user());

        Alert::success('تایید شد', 'اختلاف انبارگردانی در دفتر گردش کالا ثبت شد.');
        return redirect()->back();
    }

    public function cancelInventoryAdjustment(InventoryAdjustment $adjustment, InventoryAdjustmentService $service)
    {
        $service->cancel($adjustment, \Illuminate\Support\Facades\Auth::user());

        Alert::success('ابطال شد', 'اثر اصلاحیه از دفتر گردش کالا حذف شد.');
        return redirect()->back();
    }

    public function import_stock()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
            $WarehouseLocations = WarehouseLocation::where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->orderBy('code')->get();
        } else {
            $Stores = Store::forOrganizations($user)->get();
            $WarehouseLocations = WarehouseLocation::forOrganizations($user)->where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->orderBy('code')->get();
        }


        $WarehouseLocationMode = TenantSettings::get('warehouse_location_mode', null, 'optional_locations');

        return view('stocks.importPage', compact('Stores', 'WarehouseLocations', 'WarehouseLocationMode'));
    }

    public function StockTransfer()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
            $WarehouseLocations = WarehouseLocation::where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->orderBy('code')->get();
        } else {
            $Stores = Store::forOrganizations($user)->get();
            $WarehouseLocations = WarehouseLocation::forOrganizations($user)->where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->orderBy('code')->get();
        }


        $WarehouseLocationMode = TenantSettings::get('warehouse_location_mode', null, 'optional_locations');

        return view('stocks.storeTransfer', compact('Stores', 'WarehouseLocations', 'WarehouseLocationMode'));
    }



    public function ProductionByExtraction()
    {
        $user = Auth::user();
        if ($user->isGod == 1) {
            $Stores = Store::all();
            $Materials = Product::where('isMaterial', 1)->where('isActive', 1)->get();
            $Products = Product::where('isMaterial', 0)->where('isActive', 1)->get();
            $ProductionFormulas = ProductionFormula::with(['product', 'items.materialProduct', 'items.store'])->orderByDesc('created_at')->get();
            $ProductionOrders = ProductionOrder::with(['store', 'formula.product', 'items.product', 'accountingVoucher'])->orderByDesc('created_at')->paginate(20);
        } else {
            $Stores = Store::forOrganizations($user)->get();
            $Materials = Product::forOrganizations($user)->where('isMaterial', 1)->where('isActive', 1)->get();
            $Products = Product::forOrganizations($user)->where('isMaterial', 0)->where('isActive', 1)->get();
            $ProductionFormulas = ProductionFormula::forOrganizations($user)->with(['product', 'items.materialProduct', 'items.store'])->orderByDesc('created_at')->get();
            $ProductionOrders = ProductionOrder::forOrganizations($user)->with(['store', 'formula.product', 'items.product', 'accountingVoucher'])->orderByDesc('created_at')->paginate(20);
        }

        return view('stocks.ProductionByExtraction', compact('Stores', 'Materials', 'Products', 'ProductionFormulas', 'ProductionOrders'));
    }

    public function storeProductionFormula(Request $request)
    {
        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'code' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:190'],
            'version' => ['nullable', 'string', 'max:40'],
            'base_quantity' => ['required', 'numeric', 'min:0.001'],
            'standard_waste_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'material_product_id' => ['required', 'array'],
            'material_product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'material_store_id' => ['nullable', 'array'],
            'material_store_id.*' => ['nullable', 'integer', 'exists:stores,id'],
            'quantity' => ['required', 'array'],
            'quantity.*' => ['nullable', 'numeric', 'min:0'],
            'waste_percent' => ['nullable', 'array'],
            'waste_percent.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_description' => ['nullable', 'array'],
            'item_description.*' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $productQuery = Product::query();

        if ($user->isGod != 1) {
            $productQuery->forOrganizations($user);
        }

        $product = $productQuery->whereKey($payload['product_id'])->firstOrFail();
        $items = collect($payload['material_product_id'])->map(function ($materialId, $index) use ($payload) {
            $quantity = $this->numberValue($payload['quantity'][$index] ?? 0);

            if (!$materialId || $quantity <= 0) {
                return null;
            }

            return [
                'material_product_id' => (int) $materialId,
                'store_id' => !empty($payload['material_store_id'][$index]) ? (int) $payload['material_store_id'][$index] : null,
                'quantity' => $quantity,
                'waste_percent' => $this->numberValue($payload['waste_percent'][$index] ?? 0),
                'sort_order' => $index + 1,
                'description' => $payload['item_description'][$index] ?? null,
            ];
        })->filter()->values();

        if ($items->isEmpty()) {
            return back()->withErrors(['material_product_id' => 'حداقل یک ماده اولیه با مقدار معتبر وارد کنید.'])->withInput();
        }

        DB::transaction(function () use ($payload, $product, $items, $user) {
            $formula = ProductionFormula::create([
                'tenant_id' => $product->tenant_id ?: ($user->tenant_id ?? null),
                'organization_id' => $this->firstOrganizationId($product->organization_id ?: ($user->organization_id ?? null)),
                'product_id' => $product->id,
                'code' => $payload['code'] ?? null,
                'title' => $payload['title'],
                'version' => ($payload['version'] ?? null) ?: '1',
                'base_quantity' => $this->numberValue($payload['base_quantity']),
                'standard_waste_percent' => $this->numberValue($payload['standard_waste_percent'] ?? 0),
                'is_active' => true,
                'description' => $payload['description'] ?? null,
            ]);

            foreach ($items as $item) {
                $formula->items()->create($item);
            }
        });

        Alert::success('ثبت شد', 'فرمول تولید محصول ثبت و فعال شد.');
        return redirect()->route('stocks.ProductionByExtraction');
    }

    public function createProductionFromFormula(Request $request, ProductionService $service)
    {
        $payload = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'production_formula_id' => ['required', 'integer', 'exists:production_formulas,id'],
            'actual_quantity' => ['required', 'numeric', 'min:0.001'],
            'date_fa' => ['nullable', 'string', 'max:20'],
            'date_en' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = $service->createFormulaOrder($payload, Auth::user());

        Alert::success('ثبت شد', 'تولید از فرمول، مصرف مواد، رسید محصول و سند حسابداری با شماره ' . $order->number . ' ثبت شد.');
        return redirect()->route('stocks.ProductionByExtraction')->with('created_production_order_id', $order->id);
    }

    public function toggleProductionFormula(ProductionFormula $productionFormula)
    {
        $user = Auth::user();

        if ($user->isGod != 1 && !ProductionFormula::forOrganizations($user)->whereKey($productionFormula->id)->exists()) {
            abort(403);
        }

        $productionFormula->update(['is_active' => !$productionFormula->is_active]);

        Alert::success('ثبت شد', $productionFormula->is_active ? 'فرمول فعال شد.' : 'فرمول غیرفعال شد.');
        return redirect()->route('stocks.ProductionByExtraction');
    }

    public function ProductionByExtractionProcess(Request $request, ProductionService $service)
    {
        $payload = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'material_id' => ['required', 'integer', 'exists:products,id'],
            'entity' => ['required', 'numeric', 'min:0.001'],
            'prs' => ['required', 'array', 'min:1'],
            'prs.*' => ['nullable', 'integer', 'exists:products,id'],
            'estehsal' => ['nullable', 'array'],
            'estehsal.*' => ['nullable', 'numeric', 'min:0'],
            'taamdar' => ['nullable', 'array'],
            'taamdar.*' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = $service->createExtractionOrder($payload, Auth::user());

        Alert::success('ثبت شد', 'سند تولید، گردش انبار و سند حسابداری با شماره ' . $order->number . ' ثبت شد.');
        return redirect()->route('stocks.ProductionByExtraction')->with('created_production_order_id', $order->id);
    }

    public function cancelProductionOrder(ProductionOrder $productionOrder, ProductionService $service)
    {
        $service->cancel($productionOrder, Auth::user());

        Alert::success('ابطال شد', 'اثر سند تولید از دفتر گردش کالا و سند حسابداری موقت حذف شد.');
        return redirect()->route('stocks.ProductionByExtraction');
    }

    public function ProductStock(Product $product)
    {
        echo $product->current_stock;
    }

    private function movementTypeLabel($type): string
    {
        return [
            'receipt' => 'رسید',
            'issue' => 'حواله خروج',
            'transfer_out' => 'انتقال خروج',
            'transfer_in' => 'انتقال ورود',
            'adjustment' => 'تعدیل',
            'sale' => 'فروش',
            'return' => 'برگشت',
            'opening' => 'اول دوره',
        ][$type] ?? (string) $type;
    }



    public function getEmployee($id)
    {
        $employee_id = Employee::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($employee_id);
    }

    public function getCategory($id)
    {
        $childCategory_id = Category::where('parent_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($childCategory_id);
    }

    public function getStore($id)
    {
        $store_id = Store::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($store_id);
    }

    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();
        return back();
    }

    function to_english_numbers(String $string): String
    {
        $persinaDigits1 = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $persinaDigits2 = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
        $allPersianDigits = array_merge($persinaDigits1, $persinaDigits2);
        $replaces = [...range(0, 9), ...range(0, 9)];

        return str_replace($allPersianDigits, $replaces, $string);
    }

    private function firstOrganizationId($value): ?int
    {
        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return $value ? (int) $value : null;
    }

    private function numberValue($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }
}
