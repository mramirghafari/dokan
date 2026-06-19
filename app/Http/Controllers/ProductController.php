<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customers;
use App\Models\Tasks;
use App\Models\Brand;
use App\Models\History;
use App\Models\Log;
use App\Models\Organization;
use App\Models\Store;
use App\Models\Depot;
use App\Models\Region;
use App\Models\Area;
use App\Models\OrganizationScope;
use App\Models\PriceLog;
use App\Models\ProductPricePeriod;
use App\Models\Unit;
use App\Services\ProductListColumnService;
use App\Services\ProductListService;
use App\Services\ProductPricePeriodService;
use App\Services\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Hekmatinasser\Verta\Verta;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products,user')->only(['index', 'datatable', 'destroy', 'trashGet', 'trashPost', 'restore', 'saveListColumns']);
        $this->middleware('can:product-add,user,neworder')->only(['create', 'store', 'edit', 'update']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_multi_price')) {
                Alert::warning('غیرفعال', 'چند سطح قیمت برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['updateFees', 'update_product_fees']);
    }

    public function index(Request $request)
    {

        $categories = Category::where('isActive', 1)->where('parent_id', '')->get();
        $brands = Brand::where('isActive', 1)->get();

        //Stores
        $user = Auth::user();

        $isVisitor = false;
        $isLeader = false;
        $isManager = false;
        $isAgent = false;
        $statusFilter = $request->get('status_filter', 'active');

        $organizations = Organization::forOrganizations($user, 'id')->get();
        $stores = Store::forOrganizations($user)->where('isActive', 1)->orderBy('title')->get(['id', 'title']);
        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            if ($role->title == 'visitor') {
                $isVisitor = true;
                $products = Product::forOrganizations($user)->where('isActive', 1)->where('isMaterial', 0)->orderBy('id', 'desc')->get();
            }
            if ($role->title == 'leader') {
                $isLeader = true;
                $products = Product::forOrganizations($user)->where('isActive', 1)->where('isMaterial', 0)->orderBy('id', 'desc')->get();
            }
            if ($this->isAgentLikeRole($role)) {
                $isAgent = true;
                $products = Product::forOrganizations($user)->where('isActive', 1)->where('isMaterial', 0)->orderBy('id', 'desc')->get();
            }
            if ($role->title == 'manager') {
                $isManager = true;
            }
            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
        }

        if ($isVisitor) {
            $MyAreas = Tasks::where('user_id', auth()->user()->id)->pluck('area_id')->toArray();
            $Customers = $this->customersForNewOrder($user, true, false, false);
            if ($request->has('Task')) {
                $Task = $request->Task;
            } else {
                $Task = null;
            }

            return view('products.visitor_index', $this->newOrderViewData(compact('products', 'stores', 'categories', 'brands', 'organizations', 'Customers', 'Task', 'isAgent')));
        } elseif ($isAgent) {
            $Customers = collect();
            $Task = null;

            return view('products.visitor_index', $this->newOrderViewData(compact('products', 'stores', 'categories', 'brands', 'organizations', 'Customers', 'Task', 'isAgent')));
        } elseif ($isLeader) {
            $Customers = $this->customersForNewOrder($user, false, true, false);
            if ($request->has('Task')) {
                $Task = $request->Task;
            } else {
                $Task = null;
            }

            return view('products.visitor_index', $this->newOrderViewData(compact('products', 'stores', 'categories', 'brands', 'organizations', 'Customers', 'Task', 'isAgent')));
        } else {

            if ($request->has('neworder') && $isVisitor == false && $isLeader == false) {
                $products = Product::forOrganizations($user)->where('isActive', 1)->where('isMaterial', 0)->orderBy('id', 'desc')->get();
                $Task = null;
                $Customers = $this->customersForNewOrder($user, false, false, false);
                return view('products.visitor_index', $this->newOrderViewData(compact('products', 'stores', 'categories', 'brands', 'organizations', 'Customers', 'Task', 'isAgent')));
            }


            $service = app(ProductListService::class);
            $columnService = app(ProductListColumnService::class);
            $productsTotal = $service->count($user, $request);
            $filterValues = $service->filterValues($request);
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $warehouseModuleEnabled = $columnService->warehouseModuleEnabled($tenantId ? (int) $tenantId : null);

            return view('products.index', array_merge(compact(
                'stores',
                'categories',
                'brands',
                'organizations',
                'statusFilter',
                'productsTotal',
                'filterValues',
                'warehouseModuleEnabled',
            ), [
                'productListColumnCatalog' => $columnService->catalog($tenantId),
                'productListVisibleColumns' => $columnService->visibleKeys($tenantId),
                'productListHiddenIndexes' => $columnService->hiddenColumnIndexes($tenantId),
                'productListHeaders' => $columnService->headers($tenantId),
                'productListFixedIndexes' => $columnService->fixedColumnIndexes($tenantId),
                'productListColumnCount' => $columnService->columnCount($tenantId),
                'productListNonSortableIndexes' => array_values(array_diff(
                    range(0, max(0, $columnService->columnCount($tenantId) - 1)),
                    array_keys($columnService->sortableColumnsMap($tenantId))
                )),
            ]));
        }
    }

    public function saveListColumns(Request $request)
    {
        $data = $request->validate([
            'columns' => ['required', 'array'],
            'columns.*' => ['string', 'max:60'],
        ]);

        $user = Auth::user();
        $columnService = app(ProductListColumnService::class);
        $saved = $columnService->saveVisibleKeys($data['columns'], $user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'ستون‌های نمایشی برای این پنل ذخیره شد.',
                'columns' => $saved,
                'hidden_indexes' => $columnService->hiddenColumnIndexes($user->tenant_id ?: $user->tenants_id),
            ]);
        }

        Alert::success('ذخیره شد', 'ستون‌های نمایشی لیست محصولات برای این پنل ذخیره شد.');

        return back();
    }

    public function datatable(Request $request)
    {
        $user = Auth::user();
        $service = app(ProductListService::class);

        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 50), 10), 100);
        $orderColumn = (int) data_get($request->input('order.0.column'), 1);
        $orderDirection = data_get($request->input('order.0.dir'), 'desc') === 'asc' ? 'asc' : 'desc';

        $result = $service->datatable($user, $request, $start, $length, $orderColumn, $orderDirection);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function collection()
    {

        $user = Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::all();
            $products = Product::forOrganizations($user)->where('isActive', 1)->where('isMaterial', 0)->latest()->get();
            $productCount = Product::onlyTrashed()->count();
            $stores = Store::all();
        } else {
            // $productCount = Product::where('organization_id', $user->organization_id)->onlyTrashed()->count();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $products = Product::forOrganizations($user)->where('isMaterial', 0)->where('isActive', 1)->latest()->get();
        }
        return view('products.collection', compact('products'));
    }

    public function neworder()
    {

        $categories = Category::where('isActive', 1)->where('parent_id', '')->get();
        $brands = Brand::where('isActive', 1)->get();

        //Stores
        $user = Auth::user();

        $isVisitor = false;
        $isLeader = false;
        $isAgent = false;
        $storesUser = collect();
        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            if ($role->title == 'visitor') {
                $isVisitor = true;
            }
            if ($role->title == 'leader') {
                $isLeader = true;
            }
            if ($this->isAgentLikeRole($role)) {
                $isAgent = true;
            }
        }

        if ($user->isAdmin == 1) {
            $organizations = Organization::all();
            $products = Product::forOrganizations($user)->where('isMaterial', 0)->where('isActive', 1)->latest()->get();
            $productCount = Product::forOrganizations($user)->where('isMaterial', 0)->where('isActive', 1)->count();
            $stores = Store::all();
        } else {
            $productCount = Product::forOrganizations($user)->where('isMaterial', 0)->where('isActive', 1)->count();
            $organizations = Organization::where('id', $user->organization_id)->get();
            $products = Product::forOrganizations($user)->where('isMaterial', 0)->where('isActive', 1)->latest()->get();


            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
        }

        if ($isAgent) {
            $Customers = collect();
        } elseif ((int) $user->isAdmin === 1) {
            $Customers = $this->customersForNewOrder($user, false, false, false);
        } elseif ($isVisitor) {
            $Customers = $this->customersForNewOrder($user, true, false, false);
        } elseif ($isLeader) {
            $Customers = $this->customersForNewOrder($user, false, true, false);
        } else {
            $Customers = $this->customersForNewOrder($user, false, false, false);
        }


        return view('products.visitor_index', $this->newOrderViewData(compact('products', 'stores', 'categories', 'brands', 'organizations', 'productCount', 'Customers', 'isAgent')));
    }

    private function newOrderViewData(array $data): array
    {
        $user = Auth::user();
        $tenantId = $user?->tenants_id ?: $user?->tenant_id;
        $limitService = app(\App\Services\OrderDiscountLimitService::class);
        $products = $data['products'] ?? collect();
        $productDiscountLimits = [];

        if ($products instanceof \Illuminate\Support\Collection) {
            foreach ($products as $product) {
                $productDiscountLimits[$product->id] = $limitService->effectiveLimits($user, $product);
            }
        }

        return array_merge($data, [
            'captureInvoiceLocation' => TenantSettings::shouldCaptureInvoiceLocation($user, $tenantId ? (int) $tenantId : null),
            'featureWarehouseManagement' => TenantSettings::enabled('feature_warehouse_management', $tenantId ? (int) $tenantId : null),
            'productDiscountLimits' => $productDiscountLimits,
        ]);
    }

    private function customersForNewOrder($user, bool $isVisitor, bool $isLeader, bool $isAgent): Collection
    {
        if ($isAgent) {
            return collect();
        }

        if ($isVisitor) {
            $myAreas = Tasks::query()
                ->where('user_id', $user->id)
                ->pluck('area_id')
                ->unique()
                ->filter()
                ->values()
                ->all();

            $customers = Customers::query()
                ->whereIn('area', $myAreas)
                ->orderBy('name')
                ->get();
        } elseif ($isLeader) {
            $myRegions = Region::query()
                ->where('leader_id', $user->id)
                ->pluck('id')
                ->all();

            $myAreas = Area::query()
                ->whereIn('region_id', $myRegions)
                ->pluck('id')
                ->unique()
                ->filter()
                ->values()
                ->all();

            $customers = Customers::query()
                ->whereIn('area', $myAreas)
                ->orderBy('name')
                ->get();
        } else {
            $customers = Customers::forOrganizations($user)
                ->orderBy('name')
                ->get();
        }

        return $this->deduplicateCustomersForSelect($customers);
    }

    private function deduplicateCustomersForSelect(Collection $customers): Collection
    {
        return $customers
            ->unique('id')
            ->groupBy(function (Customers $customer) {
                $name = mb_strtolower(trim((string) ($customer->name ?? '')));
                $mobile = preg_replace('/\D+/', '', (string) ($customer->mobile ?? ''));
                $tablo = mb_strtolower(trim((string) ($customer->tablo ?? '')));

                if ($mobile !== '') {
                    return $name . '|' . $mobile;
                }

                return $name . '|' . $tablo . '|' . mb_strtolower(trim((string) ($customer->address ?? '')));
            })
            ->map(fn (Collection $group) => $group->sortByDesc('id')->first())
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function isAgentLikeRole($role): bool
    {
        return in_array($role->title, ['agent', 'reseller'], true)
            || trim((string) ($role->description ?? '')) === 'نماینده';
    }

    public function create()
    {
        $user = Auth::user();
        $products = Product::forOrganizations($user)->get();
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $units = $this->availableUnits($user);

        //Stores
        if ($user->isGod == 1) {
            $organizations = Organization::all();
            $stores = Store::all();
        } elseif ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
        }



        $productTypes = Product::PRODUCT_TYPE_LABELS;
        $stockTrackingModes = Product::STOCK_TRACKING_LABELS;
        $valuationMethods = Product::VALUATION_METHOD_LABELS;
        $productUnits = $this->availableUnits($user, Unit::SCOPE_PRODUCT);
        $featureDistribution = TenantSettings::enabled('feature_distribution');
        $shippingUnits = $featureDistribution ? $this->availableUnits($user, Unit::SCOPE_SHIPPING) : collect();
        $featureAgencySales = TenantSettings::enabled('feature_agency_sales');
        $pricePeriodTypes = ProductPricePeriod::PRICE_TYPES;

        return view('products.create', compact(
            'products',
            'stores',
            'categories',
            'brands',
            'organizations',
            'productUnits',
            'shippingUnits',
            'productTypes',
            'stockTrackingModes',
            'valuationMethods',
            'featureDistribution',
            'featureAgencySales',
            'pricePeriodTypes'
        ));
    }

    private function normalizeProductPanelScopes(Request $request, $user): bool
    {
        $organizationIds = $this->requestedIds($request->organization_id);

        if (!TenantSettings::enabled('feature_branch_management')) {
            $organizationIds = $this->defaultOrganizationIds($user);
        }

        if (empty($organizationIds)) {
            Alert::warning('خطا در ثبت', 'برای ثبت محصول باید حداقل یک واحد پخش مشخص باشد');
            return false;
        }

        $storeIds = $this->requestedIds($request->store_id);

        if (TenantSettings::enabled('feature_warehouse_management')) {
            if (empty($storeIds)) {
                Alert::warning('خطا در ثبت', 'برای این پنل انتخاب انبار برای محصول الزامی است');
                return false;
            }
        } else {
            $storeIds = [];
        }

        $request->merge([
            'organization_id' => $organizationIds,
            'store_id' => $storeIds,
        ]);

        return true;
    }

    private function normalizeProductUnits(Request $request): void
    {
        $baseUnitId = (int) $request->base_unit_id > 0 ? (int) $request->base_unit_id : null;
        $secondaryUnitId = (int) $request->secondary_unit_id > 0 ? (int) $request->secondary_unit_id : null;
        $baseUnit = $baseUnitId ? Unit::find($baseUnitId) : null;
        $secondaryUnit = $secondaryUnitId ? Unit::find($secondaryUnitId) : null;
        $conversionFactor = $request->unit_conversion_factor ?: $request->pack_items ?: 1;

        $request->merge([
            'base_unit_id' => $baseUnitId,
            'secondary_unit_id' => $secondaryUnitId,
            'pr_unit' => $baseUnit ? $baseUnit->title : $request->pr_unit,
            'pr_sub_unit' => $secondaryUnit ? $secondaryUnit->title : $request->pr_sub_unit,
            'pack_items' => $conversionFactor,
            'unit_conversion_factor' => $conversionFactor,
            'product_type' => $request->product_type ?: ($request->has('isMaterial') ? 'material' : 'goods'),
            'stock_tracking_mode' => $request->stock_tracking_mode ?: 'tracked',
            'valuation_method' => $request->valuation_method ?: 'weighted_average',
        ]);
    }

    private function normalizeOrderQuantityMode(Request $request): void
    {
        $mode = (string) $request->input('order_quantity_mode', 'main_unit');
        if (!isset(Product::ORDER_QUANTITY_MODES[$mode])) {
            $mode = 'main_unit';
        }

        $flags = Product::saleFlagsForQuantityMode($mode);

        $request->merge([
            'order_quantity_mode' => $mode,
            'item_sale_status' => $flags['item_sale_status'],
            'pack_sale_status' => $flags['pack_sale_status'],
        ]);
    }

    private function requestedIds($value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_filter(array_map('intval', $values))));
    }

    private function defaultOrganizationIds($user): array
    {
        $organizationIds = $this->requestedIds(json_decode((string) $user->organization_id, true) ?: $user->organization_id);

        if (!empty($organizationIds)) {
            return $organizationIds;
        }

        $tenantId = $user->tenant_id ?: $user->tenants_id;
        $organizationId = Organization::query()
            ->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            })
            ->value('id');

        return $organizationId ? [(int) $organizationId] : [];
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$this->normalizeProductPanelScopes($request, $user)) {
            return back()->withInput();
        }
        $this->normalizeProductUnits($request);
        $this->normalizeOrderQuantityMode($request);

        $organizationIds = $request->organization_id;
        $storeIds = $request->store_id;
        $request['user_id'] = $user->id;
        $request['organization_id'] = json_encode($organizationIds);
        $request['store_id'] = json_encode($storeIds);
        $request->merge([
            'isActive' => $request->input('isActive') === 'on' ? 1 : 0,
            'set_price' => $request->input('set_price') === 'on' ? 1 : 0,
            'isMaterial' => $request->input('isMaterial') === 'on' ? 1 : 0,
            'price' => $this->moneyInput($request->input('price'), 0),
            'purchase_price' => $this->moneyInput($request->input('purchase_price')),
            'cost_price' => $this->moneyInput($request->input('cost_price')),
            'representative_price' => $this->moneyInput($request->input('representative_price')),
            'wholesale_price' => $this->moneyInput($request->input('wholesale_price')),
            'fee_masraf' => $this->moneyInput($request->input('fee_masraf'), 0),
            'consumer_price' => $this->moneyInput($request->input('consumer_price', $request->input('fee_masraf')), 0),
            'max_discount_amount' => $this->moneyInput($request->input('max_discount_amount')),
        ]);
        $product = Product::create($request->all());
        $this->syncProductOrganizationScopes($product, $organizationIds);

        $PriceLog = new PriceLog();
        $PriceLog->tenant_id = $product->tenant_id;
        $PriceLog->organization_id = $product->organization_id;
        $PriceLog->product_id = $product->id;
        $PriceLog->pr_id = $product->id;
        $PriceLog->price = $this->moneyInput($request->price, 0);
        $PriceLog->sale_price = $this->moneyInput($request->price, 0);
        $PriceLog->purchase_price = $this->moneyInput($request->purchase_price);
        $PriceLog->cost_price = $this->moneyInput($request->cost_price);
        $PriceLog->representative_price = $this->moneyInput($request->representative_price);
        $PriceLog->wholesale_price = $this->moneyInput($request->wholesale_price);
        $PriceLog->consumer_price = $this->moneyInput($request->consumer_price, $this->moneyInput($request->fee_masraf, 0));
        $PriceLog->discount = $this->moneyInput($request->discount, 0);
        $PriceLog->tax = $this->moneyInput($request->tax, 0);
        $PriceLog->fee_masraf = $this->moneyInput($request->fee_masraf, 0);
        $PriceLog->price_from_fa = $request->price_date;
        $PriceLog->price_exp_fa = $request->price_date_exp;
        $PriceLog->change_source = 'product_create';

        if ($request->price_date != '') {
            $jalali = explode("/", $request->price_date);
            $miladi = Verta::jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
            $ym = $miladi[0];
            if (strlen($miladi[1]) == 1) {
                $mm = "0" . $miladi[1];
            } else {
                $mm = $miladi[1];
            };
            if (strlen($miladi[2]) == 1) {
                $dm = "0" . $miladi[2];
            } else {
                $dm = $miladi[2];
            };
            $price_date_en = "$ym-$mm-$dm 00:00:00";
        } else {
            $price_date_en = null;
        }


        $PriceLog->price_from_en = $price_date_en;


        if ($request->price_date_exp != '') {
            $jalali = explode("/", $request->price_date_exp);
            $miladi = Verta::jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
            $ym = $miladi[0];
            if (strlen($miladi[1]) == 1) {
                $mm = "0" . $miladi[1];
            } else {
                $mm = $miladi[1];
            };
            if (strlen($miladi[2]) == 1) {
                $dm = "0" . $miladi[2];
            } else {
                $dm = $miladi[2];
            };
            $price_exp_en = "$ym-$mm-$dm 00:00:00";
        } else {
            $price_exp_en = null;
        }


        $PriceLog->price_exp_en = $price_exp_en;
        $PriceLog->user_id = auth()->user()->id;

        $PriceLog->save();
        app(ProductPricePeriodService::class)->syncForProduct(
            $product,
            $this->buildPriceRangesFromRequest($request),
            true
        );



        if ($request->hasFile('photo')) {
            $imageName = time() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move('storage/uploads', $imageName);
            $product->update([
                'photo' => $imageName
            ]);
        }


        $user = Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'یک محصول ایجاد شد' . '-' . $product->title
        ]);

        History::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'entity',
            'store' => $product->title,
            'description' => " برای کالای " . $product->title . " موجودی اولیه " . $product->entity . " ثبت شد"
        ]);

        $product = Product::find($product->id);
        if ($request->start_date == 1) {
            $product->start_date =  verta()->format('Y/m/d');
        } elseif ($request->start_date == 2) {
            $product->start_date = verta('-1 day')->format('Y/m/d');
        } elseif ($request->start_date == 3) {
            $product->start_date = $request->select_start_date;
        }

        if ($request->exp_date == 1) {
            $product->exp_date =  verta()->format('Y/m/d');
        } elseif ($request->exp_date == 2) {
            $product->exp_date = verta('+1 day')->format('Y/m/d');
        } elseif ($request->exp_date == 3) {
            $product->exp_date = $request->select_exp_date;
        }



        $product->save();

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return redirect(route('products.edit', $product->id));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();
        $Depots = Depot::where('pr_id', $product->id)->get();
        $PriceLogs = PriceLog::where('pr_id', $product->id)->orderBy('id', 'desc')->get();
        $PriceLogLast = PriceLog::where('pr_id', $product->id)->orderBy('id', 'desc')->first();
        //Stores
        $user = Auth::user();
        $units = $this->availableUnits($user);
        $my_organ = Organization::where('id', $product->organization_id)->first();
        if ($user->isGod == 1) {
            $organizations = Organization::all();
            $stores = Store::all();
        } elseif ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
        }

        $productTypes = Product::PRODUCT_TYPE_LABELS;
        $stockTrackingModes = Product::STOCK_TRACKING_LABELS;
        $valuationMethods = Product::VALUATION_METHOD_LABELS;
        $productUnits = $this->availableUnits($user, Unit::SCOPE_PRODUCT);
        $featureDistribution = TenantSettings::enabled('feature_distribution');
        $shippingUnits = $featureDistribution ? $this->availableUnits($user, Unit::SCOPE_SHIPPING) : collect();
        $featureAgencySales = TenantSettings::enabled('feature_agency_sales');
        $pricePeriodTypes = ProductPricePeriod::PRICE_TYPES;
        $pricePeriods = $product->pricePeriods()
            ->orderBy('starts_at')
            ->orderBy('price_type')
            ->get();

        return view('products.edit', compact(
            'product',
            'categories',
            'brands',
            'parents',
            'organizations',
            'stores',
            'PriceLogs',
            'PriceLogLast',
            'my_organ',
            'Depots',
            'productUnits',
            'shippingUnits',
            'productTypes',
            'stockTrackingModes',
            'valuationMethods',
            'featureDistribution',
            'featureAgencySales',
            'pricePeriodTypes',
            'pricePeriods'
        ));
    }

    public function update(Request $request, Product $product)
    {

        //dd($request->all());
        $user = Auth::user();

        $MainCat = Category::find($product->parentCategory_id);
        if (!$this->normalizeProductPanelScopes($request, $user)) {
            return back()->withInput();
        }
        $this->normalizeProductUnits($request);
        $this->normalizeOrderQuantityMode($request);
        $request->merge([
            'price' => $this->moneyInput($request->input('price'), 0),
            'purchase_price' => $this->moneyInput($request->input('purchase_price')),
            'cost_price' => $this->moneyInput($request->input('cost_price')),
            'representative_price' => $this->moneyInput($request->input('representative_price')),
            'wholesale_price' => $this->moneyInput($request->input('wholesale_price')),
            'fee_masraf' => $this->moneyInput($request->input('fee_masraf'), 0),
            'consumer_price' => $this->moneyInput($request->input('consumer_price', $request->input('fee_masraf')), 0),
        ]);
        $my_organ = Organization::where('id', $product->organization_id)->first();

        $PriceLog = new PriceLog();
        $PriceLog->tenant_id = $product->tenant_id;
        $PriceLog->organization_id = $product->organization_id;
        $PriceLog->product_id = $product->id;
        $PriceLog->pr_id = $product->id;
        $PriceLog->price = $this->moneyInput($request->price, 0);
        $PriceLog->sale_price = $this->moneyInput($request->price, 0);
        $PriceLog->purchase_price = $this->moneyInput($request->purchase_price);
        $PriceLog->cost_price = $this->moneyInput($request->cost_price);
        $PriceLog->representative_price = $this->moneyInput($request->representative_price);
        $PriceLog->wholesale_price = $this->moneyInput($request->wholesale_price);
        $PriceLog->consumer_price = $this->moneyInput($request->consumer_price, $this->moneyInput($request->fee_masraf, 0));
        $PriceLog->discount = $this->moneyInput($request->discount, 0);
        $PriceLog->tax = $this->moneyInput($request->tax, 0);
        $PriceLog->fee_masraf = $this->moneyInput($request->fee_masraf, 0);
        $PriceLog->price_from_fa = $request->price_date;
        $PriceLog->price_exp_fa = $request->price_date_exp;
        $PriceLog->change_source = 'product_update';
        if ($request->price_date != null) {
            $jalali = explode("/", $request->price_date);
            $miladi = Verta::jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
            $ym = $miladi[0];
            if (strlen($miladi[1]) == 1) {
                $mm = "0" . $miladi[1];
            } else {
                $mm = $miladi[1];
            };
            if (strlen($miladi[2]) == 1) {
                $dm = "0" . $miladi[2];
            } else {
                $dm = $miladi[2];
            };
            $price_date_en = "$ym-$mm-$dm 00:00:00";
        } else {
            $price_date_en = null;
        }


        $PriceLog->price_from_en = $price_date_en;

        if ($request->price_date_exp != null) {
            $jalali = explode("/", $request->price_date_exp);
            $miladi = Verta::jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
            $ym = $miladi[0];
            if (strlen($miladi[1]) == 1) {
                $mm = "0" . $miladi[1];
            } else {
                $mm = $miladi[1];
            };
            if (strlen($miladi[2]) == 1) {
                $dm = "0" . $miladi[2];
            } else {
                $dm = $miladi[2];
            };
            $price_exp_en = "$ym-$mm-$dm 00:00:00";
        } else {
            $price_exp_en = null;
        }

        $PriceLog->price_exp_en = $price_exp_en;
        $PriceLog->user_id = auth()->user()->id;

        $PriceLog->save();
        app(ProductPricePeriodService::class)->syncForProduct(
            $product,
            $this->buildPriceRangesFromRequest($request),
            true
        );


        $entity = $product->entity;
        $isFreez = $request->input('isFreez') === 'on' ? 1 : 0;
        $isActive = $request->input('isActive') === 'on' ? 1 : 0;
        $isMaterial = $request->input('isMaterial') === 'on' ? 1 : 0;
        $setPrice = $request->input('set_price') === 'on' ? 1 : 0;
        $saleFlags = Product::saleFlagsForQuantityMode((string) $request->input('order_quantity_mode', $product->resolveOrderQuantityMode()));
        $product->update([
            'orderLimit' => $request->orderLimit,
            'title' => $request->title,
            'sku' => $request->sku,
            'product_type' => $request->product_type,
            'stock_tracking_mode' => $request->stock_tracking_mode,
            'valuation_method' => $request->valuation_method,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'pr_unit' => $request->pr_unit,
            'base_unit_id' => $request->base_unit_id,
            'pr_sub_unit' => $request->pr_sub_unit,
            'secondary_unit_id' => $request->secondary_unit_id,
            'pack_items' => $request->pack_items,
            'unit_conversion_factor' => $request->unit_conversion_factor,
            'pack_weight' => $request->pack_weight,
            'price' => $this->moneyInput($request->price, 0),
            'purchase_price' => $this->moneyInput($request->purchase_price),
            'cost_price' => $this->moneyInput($request->cost_price),
            'representative_price' => $this->moneyInput($request->representative_price),
            'wholesale_price' => $this->moneyInput($request->wholesale_price),
            'consumer_price' => $this->moneyInput($request->consumer_price, $this->moneyInput($request->fee_masraf, 0)),
            'discount' => $request->discount != '' ? $this->moneyInput($request->discount) : null,
            'max_discount_amount' => $this->moneyInput($request->input('max_discount_amount')),
            'tax' => $request->tax != '' ? $this->moneyInput($request->tax) : null,
            'fee_masraf' => $this->moneyInput($request->fee_masraf, 0),
            'entity' => isset($Active_Depot) ? $Active_Depot->entity : 0,
            'orderLimit' => isset($Active_Depot) ? $Active_Depot->orderLimit : 0,
            'brand_id' => $request->brand_id,
            'parentCategory_id' => $request->parentCategory_id,
            'chaildCategory_id' => isset($request->chaildCategory_id) ? $request->chaildCategory_id : $product->chaildCategory_id,
            'isActive' => $isActive,
            'item_sale_status' => $saleFlags['item_sale_status'],
            'pack_sale_status' => $saleFlags['pack_sale_status'],
            'order_quantity_mode' => $request->input('order_quantity_mode', $product->resolveOrderQuantityMode()),
            'isFreez' => $isFreez,
            'isMaterial' => $isMaterial,
            'set_price' => $setPrice,
            'depot_id' => intval($request->depot_id) > 0 ? $request->depot_id : null,
            'attrs' => isset($pr_attrs) ? json_encode($pr_attrs) : null,
            'store_id' => json_encode($request->store_id),
            'organization_id' => json_encode($request->organization_id),
            'updated_at' => now()
        ]);
        $this->syncProductOrganizationScopes($product->fresh(), $request->organization_id);

        Product::withoutGlobalScopes()
            ->where('id', '!=', $product->id)
            ->where('sku', $product->sku)
            ->where('organization_id', $product->organization_id)
            ->where('store_id', $product->store_id)
            ->update([
                'isActive' => $product->isActive,
                'updated_at' => now(),
            ]);

        if ($request->hasFile('photo')) {
            $imageName = time() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move('storage/uploads', $imageName);
            $product->update([
                'photo' => $imageName
            ]);
        }
        //Stores
        $user = Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'محصول ویرایش شد' . '-' . $product->title
        ]);

        if ($entity != $product->entity) {
            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'editEntity',
                'store' => $product->store->title,
                'description' => " کالای " . $product->title . " از تعداد " . $entity . " به تعداد " . $product->entity . " تغییر یافت"
            ]);
        }


        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect(asset('products'));
    }

    private function availableUnits($user, ?string $scope = null)
    {
        if ($user->isGod == 1) {
            $query = Unit::where('isActive', 1)->orderBy('title');
        } else {
            $query = Unit::forOrganizations($user)
                ->where('isActive', 1)
                ->orderBy('title');
        }

        if ($scope) {
            $query->where('usage_scope', $scope);
        }

        return $query->get();
    }

    private function moneyInput($value, $default = null)
    {
        $value = str_replace(',', '', trim((string) ($value ?? '')));

        return $value === '' ? $default : $value;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildPriceRangesFromRequest(Request $request): array
    {
        $ranges = [];
        $inputRows = $request->input('price_ranges', []);
        if (is_array($inputRows)) {
            foreach ($inputRows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $ranges[] = [
                    'price_type' => $row['price_type'] ?? null,
                    'amount' => $this->moneyInput($row['amount'] ?? null),
                    'starts_at' => $row['starts_at'] ?? null,
                    'ends_at' => $row['ends_at'] ?? null,
                    'priority' => $row['priority'] ?? 0,
                ];
            }
        }

        if ($ranges !== []) {
            return $ranges;
        }

        $legacyStart = $request->input('price_date');
        $legacyEnd = $request->input('price_date_exp');
        if (trim((string) $legacyStart) !== '' || trim((string) $legacyEnd) !== '') {
            return [[
                'price_type' => ProductPricePeriod::TYPE_SALE,
                'amount' => $this->moneyInput($request->input('price'), 0),
                'starts_at' => $legacyStart,
                'ends_at' => $legacyEnd,
                'priority' => 0,
            ]];
        }

        return [];
    }

    private function syncProductOrganizationScopes(Product $product, $organizationIds): void
    {
        $organizationIds = $this->requestedIds(is_string($organizationIds) ? (json_decode($organizationIds, true) ?: $organizationIds) : $organizationIds);

        OrganizationScope::where('scopeable_type', Product::class)
            ->where('scopeable_id', $product->id)
            ->delete();

        foreach (array_values($organizationIds) as $index => $organizationId) {
            OrganizationScope::create([
                'tenant_id' => $product->tenant_id,
                'organization_id' => (int) $organizationId,
                'scopeable_type' => Product::class,
                'scopeable_id' => $product->id,
                'is_primary' => $index === 0,
                'source' => 'product_form',
            ]);
        }
    }

    public function updateFees()
    {

        $user = Auth::user();
        if ($user->isGod == 1) {
            $Products = Product::forOrganizations($user)->get();
        } else {

            $Products = Product::forOrganizations($user)->get();
        }

        return view('products.products-fees', compact('Products'));
    }

    public function update_product_fees(Request $request)
    {

        $user = auth()->user();
        //dd($request->all());

        foreach ($request->all() as $key => $value) {
            if ($value != null) {
                if (str_starts_with($key, 'pr_price_')) {
                    $id = str_replace('pr_price_', '', $key);
                    $price = str_replace(',', '', $value);
                    $PriceLog = new PriceLog();
                    $PriceLog->pr_id = $id;
                    $PriceLog->price = $price;
                    $PriceLog->user_id = $user->id;
                    $PriceLog->save();

                    $Product = Product::find($id);
                    $Product->price =  str_replace(',', '', $value);
                    $Product->save();

                    History::create([
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'user_id' => $user->id,
                        'action' => 'entity',
                        'store' => $Product->title . ' ' . $Product->display_name,
                        'description' => " برای کالای " . $Product->title . ' ' . $Product->display_name . "قیمت روز ثبت شد."
                    ]);
                }
            }
        }

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'به روزرسانی قیمت روز محصولات انجام شد'
        ]);



        Alert::success('تشکر', 'قیمت ها با موفقیت به روزرسانی شد.');
        return redirect(route('products.updateFees'));
    }

    public function notify(Product $product)
    {
        $product->update([
            'isNotify' => 0
        ]);
        return back();
    }

    public function getCategory($id)
    {
        $childCategory_id = Category::where('parent_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($childCategory_id);
    }

    public function getprs($id)
    {

        $Category = Category::find($id);

        $name = str_replace(" ", "_", $Category->title);

        $url = "https://appdocs.daramino.ir/PRSApi/$name/";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $json = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($json);


        return response()->json($data->data);
    }

    public function getprInfo($title)
    {

        $title = str_replace(" ", "_", $title);
        $url = "https://appdocs.daramino.ir/GetPrInfo/$title/";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $json = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($json);


        return response()->json($data->data);
    }

    public function getStore($id)
    {
        $store_id = Store::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($store_id);
    }

    public function productsByStore($store)
    {


        $id = array();
        $Products = Product::whereJsonContains('products.store_id', $store)
            ->leftJoin('depots', function ($join) use ($store) {
                $join->on('products.id', '=', 'depots.pr_id')
                    ->where('depots.store_id', $store)
                    ->where('depots.status', 1);
            })
            ->select(
                'products.*',
                DB::raw('SUM(depots.entity) as total_entity'),
                DB::raw('SUM(depots.entity_sub_unit) as total_entity_sub_unit'),
                DB::raw('SUM(depots.entity) / NULLIF(products.pack_items, 0) as sub_unit_from_entity')
            )
            ->groupBy('products.id')
            ->get();


        return response()->json($Products);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return back();
    }
}
