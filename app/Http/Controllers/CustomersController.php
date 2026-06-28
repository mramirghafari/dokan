<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customers;
use App\Models\Brand;
use App\Models\History;
use App\Models\Organization;
use App\Models\Store;
use App\Models\City;
use App\Models\Region;
use App\Models\Area;
use App\Models\Tasks;
use App\Models\Role;
use App\Models\Pishfactor;
use App\Models\CustomerSegment;
use App\Models\CrmFollowup;
use App\Models\CrmOpportunity;
use App\Services\CustomerListColumnService;
use App\Services\CustomerListService;
use App\Services\CustomerListSummaryService;
use App\Services\CustomerProfilePageService;
use App\Services\CustomerTimelineService;
use App\Services\PishFactorListService;
use App\Services\TenantSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CustomersController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'datatable', 'destroy', 'trashGet', 'trashPost', 'restore', 'profile360', 'saveListColumns']);
        $this->middleware('can:customers-add,user')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('can:customers-edit,user')->only(['edit', 'update']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $filterContext = $this->customerIndexFilterContext($user);
        $summaryMetrics = app(CustomerListSummaryService::class)->getIndexMetrics($user, $request);
        $customersTotal = $summaryMetrics['customers_total'];
        $customersWithPurchaseCount = $summaryMetrics['customers_with_purchase'];
        $restrictedCustomers = $summaryMetrics['restricted_customers'];
        $bannedCustomers = $summaryMetrics['banned_customers'];

        $codename = $request->filled('codename') ? trim((string) $request->codename) : null;
        $area_id = $request->filled('area_id') && (int) $request->area_id !== 0 ? (int) $request->area_id : null;
        $leader_id = $request->filled('leader_id') && (int) $request->leader_id !== 0 ? (int) $request->leader_id : null;
        $visitor_id = $request->filled('visitor_id') && (int) $request->visitor_id !== 0 ? (int) $request->visitor_id : null;
        $status = $request->filled('status') ? (int) $request->status : null;
        $columnService = app(CustomerListColumnService::class);
        $tenantId = $user->tenant_id ?: $user->tenants_id;

        return view('customers.index', array_merge($filterContext, compact(
            'customersTotal',
            'customersWithPurchaseCount',
            'restrictedCustomers',
            'bannedCustomers',
            'codename',
            'area_id',
            'leader_id',
            'visitor_id',
            'status',
        ), [
            'customerListColumnCatalog' => $columnService->catalog($tenantId),
            'customerListVisibleColumns' => $columnService->visibleKeys($tenantId),
            'customerListHiddenIndexes' => $columnService->hiddenColumnIndexes($tenantId),
            'customerListHeaders' => $columnService->headers($tenantId),
            'customerListFixedIndexes' => $columnService->fixedColumnIndexes($tenantId),
            'customerListColumnCount' => $columnService->columnCount($tenantId),
            'customerListNonSortableIndexes' => array_values(array_diff(
                range(0, max(0, $columnService->columnCount($tenantId) - 1)),
                array_keys($columnService->sortableColumnsMap($tenantId))
            )),
            'customerListIsSubscriptionPanel' => $columnService->isSubscriptionPanel($tenantId),
        ]));
    }

    public function saveListColumns(Request $request)
    {
        $data = $request->validate([
            'columns' => ['required', 'array'],
            'columns.*' => ['string', 'max:60'],
        ]);

        $user = Auth::user();
        $columnService = app(CustomerListColumnService::class);
        $saved = $columnService->saveVisibleKeys($data['columns'], $user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'ستون‌های نمایشی برای این پنل ذخیره شد.',
                'columns' => $saved,
                'hidden_indexes' => $columnService->hiddenColumnIndexes($user->tenant_id ?: $user->tenants_id),
            ]);
        }

        Alert::success('ذخیره شد', 'ستون‌های نمایشی لیست مشتریان برای این پنل ذخیره شد.');

        return back();
    }

    public function datatable(Request $request)
    {
        $user = Auth::user();
        $listService = app(CustomerListService::class);
        $summaryService = app(CustomerListSummaryService::class);
        $columnService = app(CustomerListColumnService::class);
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 50), 10), 100);

        $scopedQuery = $listService->scopedQuery($user);
        $recordsTotal = $summaryService->scopedTotal($user);
        $filteredQuery = $listService->applyFilters(clone $scopedQuery, $request, $user);
        $recordsFiltered = $summaryService->filteredTotal($user, $request);

        $orderColumn = (int) data_get($request->input('order.0.column'), 0);
        $orderDirection = data_get($request->input('order.0.dir'), 'desc') === 'asc' ? 'asc' : 'desc';
        $sortableColumns = $columnService->sortableColumnsMap($user->tenant_id ?: $user->tenants_id);

        $customers = $listService->appendListMetricSelects(clone $filteredQuery)
            ->with([
                'region:id,name',
                'Area:id,name',
                'leader:id,name',
                'creator:id,name',
                'latestPishfactor.visitor:id,name',
            ])
            ->withCount('activeOrders as active_orders_count')
            ->withSum('activeOrders as active_orders_sum', 'fullPrice')
            ->withCount('pishfactors as purchases_count')
            ->withSum('pishfactors as purchases_sum', 'fullPrice')
            ->when(isset($sortableColumns[$orderColumn]), function (Builder $query) use ($sortableColumns, $orderColumn, $orderDirection) {
                $query->orderBy($sortableColumns[$orderColumn], $orderDirection);
            }, function (Builder $query) {
                $query->orderByDesc('customers.id');
            })
            ->skip($start)
            ->take($length)
            ->get();

        $canDelete = (int) $user->isAdmin === 1;
        $csrf = csrf_token();
        $data = $customers->values()->map(function (Customers $customer, int $index) use ($start, $canDelete, $csrf, $columnService, $user) {
            $showUrl = route('customers.show', $customer->id);
            $ordersUrl = route('customers.orders', $customer->id);

            $actions = '<a href="' . $showUrl . '" style="font-size:20px;float:right;margin-left:15px;color:#04a9f5;display:inline-flex">'
                . \App\Support\UiIcon::html('fa-edit') . '</a>';
            if ($canDelete && (int) ($customer->active_orders_count ?? 0) === 0) {
                $destroyUrl = route('customers.destroy', $customer->id);
                $actions .= '<form action="' . $destroyUrl . '" method="POST" onsubmit="return confirm(\'آیا از این مشتری اطمینان دارید؟\');" style="display:inline">'
                    . '<input type="hidden" name="_method" value="delete">'
                    . '<input type="hidden" name="_token" value="' . $csrf . '">'
                    . '<button type="submit" style="font-size:20px;border:none;background-color:transparent;float:right;color:#dc3545;display:inline-flex">'
                    . \App\Support\UiIcon::html('fa-trash') . '</button>'
                    . '</form>';
            }

            return $columnService->buildDatatableRow(
                $customer,
                $start + $index + 1,
                $showUrl,
                $ordersUrl,
                $actions,
                $user->tenant_id ?: $user->tenants_id
            );
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create()
    {

        $user = \Auth::user();
        $usesAreaWorkflow = $this->usesAreaWorkflow();
        $usesRouteWorkflow = $this->usesRouteWorkflow();
        $requiresAreaWorkflow = $this->requiresAreaWorkflow();
        $requiresRouteWorkflow = $this->requiresRouteWorkflow();
        $roles = Role::all();
        $isManager = false;
        $isVisitor = false;
        foreach ($user->roles as $role) {
            if ($role->title == 'visitor') {
                $isVisitor = true;
            } else {
                $isVisitor = false;
            }
            if ($role->title == 'expert') {
                $isManager = true;
            }
        }

        if ($user->isAdmin == 1) {
            $Regions = Region::forOrganizations($user)->get();
            $Areas = null;
        } elseif ($isManager) {
            $Regions = Region::forOrganizations($user)->get();
            $Areas = null;
        } else {
            $Regions = Region::where('leader_id', $user->id)->get();
            $Areas = null;
        }

        if ($isVisitor) {
            $regionIds = Tasks::where('user_id', auth()->id())
                ->where('status', 1)
                ->whereHas('area.region') // شرط اینکه ناحیه و ریجن وجود داشته باشند
                ->with('area.region')     // برای جلوگیری از N+1
                ->get()
                ->pluck('area.region_id') // استخراج region_id از رابطه‌ها
                ->unique()                // حذف شناسه‌های تکراری
                ->values();               // مرتب‌سازی ایندکس‌ها
            $MyTasks = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->get();
            $areaIds = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->pluck('area_id')->unique();
            $Regions = Region::whereIn('id', $regionIds)->get();
            $Areas = Area::whereIn('id', $areaIds)->get();
        } else {
            $MyTasks = null;
            $Areas = null;
        }

        $customerGroups = $this->customerSegments($user, 'customer_group');
        $salesChannels = $this->customerSegments($user, 'sales_channel');
        $customerStatuses = $this->customerSegments($user, 'customer_status');

        session()->put('backlink', asset('/customers'));
        return view('customers.create', compact('Regions', 'isVisitor', 'MyTasks', 'Areas', 'usesAreaWorkflow', 'usesRouteWorkflow', 'requiresAreaWorkflow', 'requiresRouteWorkflow', 'customerGroups', 'salesChannels', 'customerStatuses'));
    }

    public function profile360(Customers $customer, CustomerTimelineService $timelineService)
    {
        $profile = $timelineService->profile($customer, Auth::user());

        return view('customers.profile_360', $profile);
    }

    public function show($Customer, $task = null)
    {
        $Customer = Customers::find($Customer);
        abort_if(!$Customer, 404);

        $MyTask = $task !== null ? Tasks::find($task) : null;
        $profile = app(CustomerProfilePageService::class)->build($Customer, Auth::user(), $MyTask);

        session()->put('backlink', asset('/customers'));

        return view('customers.show', array_merge($profile, [
            'Customer' => $profile['customer'],
            'Factors' => $profile['orders'],
            'FactorsPriceCount' => $profile['metrics']['revenue_total'],
            'FactorsAccepted' => collect(),
            'MyTask' => $MyTask,
            'CrmFollowups' => $profile['crm']['followups'],
            'CrmOpportunities' => $profile['crm']['opportunities'],
            'CrmStats' => $profile['crm']['stats'],
            'profileService' => app(CustomerProfilePageService::class),
        ]));
    }

    public function search(Request $request)
    {
        if ($request->isMethod('post')) {
            return redirect()->route('customers.index', $request->only([
                'codename',
                'area_id',
                'leader_id',
                'visitor_id',
                'status',
            ]));
        }

        return redirect()->route('customers.index');
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        $usesAreaWorkflow = $this->usesAreaWorkflow();
        $usesRouteWorkflow = $this->usesRouteWorkflow();
        $requiresAreaWorkflow = $this->requiresAreaWorkflow();
        $requiresRouteWorkflow = $this->requiresRouteWorkflow();
        $isVisitor = false;
        $rand = null;




        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            if ($role->title == 'visitor') {
                $isVisitor = true;
            } else {
                $isVisitor = false;
            }
        }
        if ($isVisitor) {
            $request['leader_id'] = \Auth::user()->leader_id;
        } else {
            $request['leader_id'] = \Auth::user()->id;
        }


        $request['created_by'] = \Auth::user()->id;
        if ($request->customer_code == '') {
            $rand = rand(1000000000, 9999999999);
            $Selecte_rand = Customers::where('customer_code', $rand)->count();
            if ($Selecte_rand > 0) {
                $rand = rand(1000000000, 9999999999);
                $Selecte_rand = Customers::where('customer_code', $rand)->count();
                if ($Selecte_rand > 0) {
                    $rand = rand(1000000000, 9999999999);
                    $Selecte_rand = Customers::where('customer_code', $rand)->count();
                }
            }
        }

        if ($requiresAreaWorkflow && (!$request->filled('region_id') || (int) $request->region_id === 0)) {
            Alert::warning('خطا در ثبت', 'انتخاب منطقه برای این پنل الزامی است');
            return back()->withInput();
        }

        if ($requiresRouteWorkflow && (!$request->filled('area') || (int) $request->area === 0)) {
            Alert::warning('خطا در ثبت', 'انتخاب مسیر برای این پنل الزامی است');
            return back()->withInput();
        }

        $regionId = $usesAreaWorkflow && $request->filled('region_id') ? (int) $request->region_id : 0;
        $areaId = $usesRouteWorkflow && $request->filled('area') ? (int) $request->area : 0;
        $organizationId = $this->primaryOrganizationId($user);

        if ($usesAreaWorkflow) {
            $Cur_Region = Region::find($regionId);
            $organizationId = $Cur_Region ? $Cur_Region->organization_id : $organizationId;
        }

        $request->merge([
            'region_id' => $regionId,
            'area' => $areaId,
            'organization_id' => $organizationId,
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'customer_code' => $request->customer_code != '' ? $request->customer_code : $rand,
        ]);
        $this->normalizeCustomerSegments($request, $organizationId, $user->tenant_id ?: $user->tenants_id);

        $Customer = Customers::create($request->all());
        $user = \Auth::user();

        $Customer->save();

        ActivityLogService::safeLogModel('create', 'یک مشتری ایجاد شد' . '-' . $Customer->name, $Customer, ['section' => 'crm', 'event_key' => 'customer.create']);

        Alert::success('تشکر', 'مشتری با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Customers $customer)
    {
        $user = \Auth::user();
        $usesAreaWorkflow = $this->usesAreaWorkflow();
        $usesRouteWorkflow = $this->usesRouteWorkflow();
        $requiresAreaWorkflow = $this->requiresAreaWorkflow();
        $requiresRouteWorkflow = $this->requiresRouteWorkflow();


        $isManager = false;
        $isVisitor = false;
        foreach ($user->roles as $role) {
            if ($role->title == 'visitor') {
                $isVisitor = true;
            } else {
                $isVisitor = false;
            }
            if ($role->title == 'expert') {
                $isManager = true;
            }
        }
        if ($user->isAdmin == 1) {
            $Regions = Region::forOrganizations($user)->get();
            $Areas = null;
        } elseif ($isManager) {
            $Regions = Region::forOrganizations($user)->get();
            $Areas = null;
        } else {
            $Regions = Region::where('leader_id', $user->id)->get();
            $Areas = null;
        }

        if ($isVisitor) {
            $regionIds = Tasks::where('user_id', auth()->id())
                ->where('status', 1)
                ->whereHas('area.region') // شرط اینکه ناحیه و ریجن وجود داشته باشند
                ->with('area.region')     // برای جلوگیری از N+1
                ->get()
                ->pluck('area.region_id') // استخراج region_id از رابطه‌ها
                ->unique()                // حذف شناسه‌های تکراری
                ->values();               // مرتب‌سازی ایندکس‌ها
            $MyTasks = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->get();
            $areaIds = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->pluck('area_id')->unique();
            $Regions = Region::whereIn('id', $regionIds)->get();
            $Areas = Area::whereIn('id', $areaIds)->get();
        } else {
            $MyTasks = null;
            $Areas = null;
        }
        $Cur_Area = $usesRouteWorkflow ? Area::find($customer->area) : null;
        $Cur_Region = $Cur_Area ? Region::find($Cur_Area->region_id) : Region::find($customer->region_id);
        $areaIds = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->pluck('area_id')->unique();
        $This_areas = $Cur_Region ? Area::whereIn('id', $areaIds)->where('region_id', $Cur_Region->id)->get() : collect();
        $customerGroups = $this->customerSegments($user, 'customer_group');
        $salesChannels = $this->customerSegments($user, 'sales_channel');
        $customerStatuses = $this->customerSegments($user, 'customer_status');

        session()->put('backlink', asset("/customers/$customer->id"));
        return view('customers.edit', compact('Regions', 'customer', 'Cur_Region', 'This_areas', 'Areas', 'usesAreaWorkflow', 'usesRouteWorkflow', 'requiresAreaWorkflow', 'requiresRouteWorkflow', 'customerGroups', 'salesChannels', 'customerStatuses'));
    }

    public function update(Request $request, Customers $customer)
    {

        // dd($request->all());
        $usesAreaWorkflow = $this->usesAreaWorkflow();
        $usesRouteWorkflow = $this->usesRouteWorkflow();
        $requiresAreaWorkflow = $this->requiresAreaWorkflow();
        $requiresRouteWorkflow = $this->requiresRouteWorkflow();

        if ($requiresAreaWorkflow && (!$request->filled('region_id') || (int) $request->region_id === 0)) {
            Alert::warning('خطا در ثبت', 'انتخاب منطقه برای این پنل الزامی است');
            return back()->withInput();
        }

        if ($requiresRouteWorkflow && (!$request->filled('area') || (int) $request->area === 0)) {
            Alert::warning('خطا در ثبت', 'انتخاب مسیر برای این پنل الزامی است');
            return back()->withInput();
        }

        $this->normalizeCustomerSegments($request, $customer->organization_id, $customer->tenant_id);


        $customer->update([
            'name' => $request->name,
            'national_id' => $request->national_id,
            'economic_number' => $request->economic_number,
            'customer_code' => $request->customer_code,
            'max_purchase_amount' => $this->moneyInput($request->input('max_purchase_amount')),
            'max_discount_amount' => $this->moneyInput($request->input('max_discount_amount')),
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'tablo' => $request->tablo,
            'senf' => $request->senf,
            'customer_group_id' => $request->customer_group_id,
            'channel' => $request->channel,
            'sales_channel_id' => $request->sales_channel_id,
            'status' => $request->status,
            'customer_status_id' => $request->customer_status_id,
            'area' => $usesRouteWorkflow && $request->filled('area') ? (int) $request->area : 0,
            'region_id' => $usesAreaWorkflow && $request->filled('region_id') ? (int) $request->region_id : 0,
            'mapcode' => $request->mapcode,
            'address' => $request->address,
            'store_address' => $request->store_address,
            'shop_lat' => $request->shop_lat,
            'shop_lng' => $request->shop_lng,
            'store_lat' => $request->store_lat,
            'store_lng' => $request->store_lng,
            'updated_at' => now()
        ]);


        //Stores
        $user = \Auth::user();
        ActivityLogService::safeLogModel('update', 'مشتری ویرایش شد' . '-' . $customer->name, $customer, ['section' => 'crm', 'event_key' => 'customer.update']);

        Alert::success('تشکر', 'مشتری با موفقیت ویرایش شد');
        return back();
    }

    public function update_customer_info_by_visitor(Request $request, Customers $Customer)
    {
        $this->normalizeCustomerSegments($request, $Customer->organization_id, $Customer->tenant_id);

        $Customer->update([
            'name' => $request->name,
            'national_id' => $request->national_id,
            'economic_number' => $request->economic_number,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'tablo' => $request->tablo,
            'senf' => $request->senf,
            'customer_group_id' => $request->customer_group_id,
            'channel' => $request->channel,
            'sales_channel_id' => $request->sales_channel_id,
            'status' => $request->status,
            'customer_status_id' => $request->customer_status_id,
            'address' => $request->address,
            'store_address' => $request->store_address,
            'updated_at' => now()
        ]);
        //Stores
        $user = \Auth::user();
        ActivityLogService::safeLogModel('update', 'مشتری ویرایش شد' . '-' . $Customer->name, $Customer, ['section' => 'crm', 'event_key' => 'customer.update']);

        Alert::success('تشکر', 'مشتری با موفقیت ویرایش شد');
        return back();
    }

    public function activeCustomers(Request $request)
    {
        $request->merge(['status' => 1]);

        return $this->index($request);
    }

    public function createdByMe()
    {

        $user = \Auth::user();
        $Customers = Customers::where('created_by', $user->id)->get();
        // تعداد مشتریانی که خرید کرده‌اند
        $customersWithPurchaseCount = Pishfactor::distinct('customer_id')->count('customer_id');


        $codename = null;
        $area_id = null;
        $leader_id = null;
        $visitor_id = null;
        $status = null;


        return view('customers.index', compact('Customers', 'customersWithPurchaseCount', 'codename', 'area_id', 'leader_id', 'visitor_id'));
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

        $url = "http://daramino.localhost:8000/PRSApi/$name/";

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

    public function update_customer_loc(Customers $Customer, Request $request)
    {

        if ($request->shop_lat != '' || $request->shop_lat != null || !empty($request->shop_lat)) {
            $Customer->shop_lat = $request->shop_lat;
        }
        if ($request->shop_lng != '' || $request->shop_lng != null || !empty($request->shop_lng)) {
            $Customer->shop_lng = $request->shop_lng;
        }

        if ($request->sotre_lat != '' || $request->sotre_lat != null || !empty($request->sotree_lat)) {
            $Customer->store_lat = $request->sotre_lat;
        }
        if ($request->sotre_lng != '' || $request->sotre_lng != null || !empty($request->sotre_lng)) {
            $Customer->store_llng = $request->sotre_lng;
        }
        $Customer->update();

        $user = \Auth::user();
        $username = $user->name;
        ActivityLogService::safeLogModel('update', "موقعیت مکانی مشتری توسط $username ویرایش شد شد" . '-' . $Customer->name, $Customer, ['section' => 'crm', 'event_key' => 'customer.update']);

        Alert::success('تشکر', 'موقعیت مشتری با موفقیت به روزرسانی شد');
        return back();
    }

    public function getStore($id)
    {
        $store_id = Store::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($store_id);
    }

    public function CustomerOrders(Request $request, $customer_id)
    {
        $user = Auth::user();
        $service = app(PishFactorListService::class);
        $roles = $service->resolvePageContext($user);
        $showStoreColumn = (int) $user->isAdmin === 1;
        $showVisitorColumn = empty($roles['isLeader']);

        $request->merge(['customer_id' => (int) $customer_id]);

        return view('invoices.PishFactors', array_merge($roles, [
            'listKey' => PishFactorListService::LIST_CUSTOMER,
            'pishFactorsTotal' => $service->count($user, PishFactorListService::LIST_CUSTOMER, $request, $roles),
            'filterValues' => $service->filterValues($request),
            'showStoreColumn' => $showStoreColumn,
            'showVisitorColumn' => $showVisitorColumn,
            'datatableColumnCount' => $service->datatableColumnCount($showStoreColumn, $showVisitorColumn),
        ]));
    }



    public function destroy($id)
    {

        $user = \Auth::user();
        $Customer = Customers::findOrFail($id);
        $Customer->delete();

        ActivityLogService::safeLogModel('delete', "مشتری $Customer->name توسط حساب $user->name حذف شد. ", $Customer, ['section' => 'crm', 'event_key' => 'customer.delete']);

        Alert::success('تشکر', 'مشتری با موفقیت حذف شد');
        return back();
    }

    private function customerIndexFilterContext(User $user): array
    {
        if ($user->isGod == 1) {
            $Cities = City::all();
            $visitorRole = Role::where('title', 'visitor')->first();
            $VisitorUsers = $visitorRole
                ? DB::table('role_user')->where('role_id', $visitorRole->id)->pluck('user_id')->toArray()
                : [];
            $Visitors = User::whereIn('id', $VisitorUsers)->where('leader_id', $user->id)->where('isActive', 1)->get();

            $leaderRole = Role::where('title', 'leader')->first();
            $LeaderUsers = $leaderRole
                ? DB::table('role_user')->where('role_id', $leaderRole->id)->pluck('user_id')->toArray()
                : [];
            $Leaders = User::whereIn('id', $LeaderUsers)->where('leader_id', $user->id)->where('isActive', 1)->get();
            $Regions = Region::with('areas:id,region_id,name')->get();
        } elseif ($user->isAdmin == 1) {
            $Cities = City::forOrganizations($user)->get();
            $visitorRole = Role::where('title', 'visitor')->first();
            $VisitorUsers = $visitorRole
                ? DB::table('role_user')->where('role_id', $visitorRole->id)->pluck('user_id')->toArray()
                : [];
            $Visitors = User::whereIn('id', $VisitorUsers)->where('leader_id', $user->id)->where('isActive', 1)->get();

            $leaderRole = Role::where('title', 'leader')->first();
            $LeaderUsers = $leaderRole
                ? DB::table('role_user')->where('role_id', $leaderRole->id)->pluck('user_id')->toArray()
                : [];
            $Leaders = User::whereIn('id', $LeaderUsers)->where('leader_id', $user->id)->where('isActive', 1)->get();
            $Regions = Region::forOrganizations($user)->with('areas:id,region_id,name')->get();
        } else {
            $Cities = City::forOrganizations($user)->get();
            $visitorRole = Role::where('title', 'visitor')->first();
            $VisitorUsers = $visitorRole
                ? DB::table('role_user')->where('role_id', $visitorRole->id)->pluck('user_id')->toArray()
                : [];
            $Visitors = User::whereIn('id', $VisitorUsers)->where('leader_id', $user->id)->where('isActive', 1)->get();

            $leaderRole = Role::where('title', 'leader')->first();
            $LeaderUsers = $leaderRole
                ? DB::table('role_user')->where('role_id', $leaderRole->id)->pluck('user_id')->toArray()
                : [];
            $Leaders = User::whereIn('id', $LeaderUsers)->where('leader_id', $user->id)->where('isActive', 1)->get();
            $Regions = Region::forOrganizations($user)->with('areas:id,region_id,name')->get();
        }

        return compact('Cities', 'Leaders', 'Visitors', 'Regions');
    }

    private function usesAreaWorkflow(): bool
    {
        return TenantSettings::enabled('feature_area_management')
            && in_array('route_based', $this->customerCreationModes(), true);
    }

    private function usesRouteWorkflow(): bool
    {
        return $this->usesAreaWorkflow() && TenantSettings::enabled('feature_route_management');
    }

    private function requiresAreaWorkflow(): bool
    {
        return $this->usesAreaWorkflow() && !$this->usesDirectCustomerWorkflow();
    }

    private function requiresRouteWorkflow(): bool
    {
        return $this->usesRouteWorkflow() && !$this->usesDirectCustomerWorkflow();
    }

    private function usesDirectCustomerWorkflow(): bool
    {
        return in_array('direct', $this->customerCreationModes(), true)
            && TenantSettings::enabled('feature_direct_customer_registration');
    }

    private function customerCreationModes(): array
    {
        $modes = TenantSettings::get('customer_creation_mode', null, ['route_based']);

        if (is_string($modes)) {
            $decoded = json_decode($modes, true);
            $modes = json_last_error() === JSON_ERROR_NONE ? $decoded : [$modes];
        }

        if (!is_array($modes)) {
            $modes = ['route_based'];
        }

        $allowedModes = ['route_based', 'direct', 'agency_based'];
        $modes = array_values(array_intersect(array_map('strval', $modes), $allowedModes));

        return $modes ?: ['route_based'];
    }

    private function primaryOrganizationId(User $user): ?int
    {
        $rawOrganizationId = $user->organization_id;

        if (empty($rawOrganizationId)) {
            return Organization::where('tenant_id', $user->tenant_id ?: $user->tenants_id)
                ->orWhere('tenants_id', $user->tenants_id ?: $user->tenant_id)
                ->value('id');
        }

        $decodedOrganizationIds = json_decode($rawOrganizationId, true);

        if (is_array($decodedOrganizationIds)) {
            return (int) reset($decodedOrganizationIds);
        }

        return (int) $rawOrganizationId;
    }

    private function customerSegments($user, string $type)
    {
        $query = CustomerSegment::where('type', $type)->where('isActive', 1);

        if (!$user || $user->isGod != 1) {
            $query->forOrganizations($user);
        }

        return $query->orderBy('sort_order')->orderBy('title')->get();
    }

    private function normalizeCustomerSegments(Request $request, ?int $organizationId, $tenantId): void
    {
        $group = $this->resolveCustomerSegment('customer_group', $request->customer_group_id, $request->senf, $organizationId, $tenantId);
        $channel = $this->resolveCustomerSegment('sales_channel', $request->sales_channel_id, $request->channel, $organizationId, $tenantId);
        $status = $this->resolveCustomerSegment('customer_status', $request->customer_status_id, $request->status ?: 'فعال', $organizationId, $tenantId, true);

        $request->merge([
            'customer_group_id' => $group ? $group->id : null,
            'senf' => $group ? $group->title : $request->senf,
            'sales_channel_id' => $channel ? $channel->id : null,
            'channel' => $channel ? $channel->title : $request->channel,
            'customer_status_id' => $status ? $status->id : null,
            'status' => $status ? $this->legacyCustomerStatus($status->title) : ($request->status ?: 1),
        ]);
    }

    private function resolveCustomerSegment(string $type, $id, $title, ?int $organizationId, $tenantId, bool $isDefault = false): ?CustomerSegment
    {
        if ((int) $id > 0) {
            return CustomerSegment::where('type', $type)->find((int) $id);
        }

        $title = trim((string) $title);
        if ($title === '' || $title === '0') {
            return null;
        }

        return CustomerSegment::firstOrCreate(
            ['type' => $type, 'organization_id' => $organizationId, 'title' => $title],
            [
                'tenant_id' => $tenantId,
                'code' => substr($type . '_' . md5($title), 0, 60),
                'is_default' => $isDefault,
                'isActive' => 1,
            ]
        );
    }

    private function legacyCustomerStatus(string $title): int
    {
        return in_array($title, ['غیرفعال', 'مسدود'], true) ? 0 : 1;
    }

    private function moneyInput($value, $default = null)
    {
        $value = str_replace(',', '', trim((string) ($value ?? '')));

        return $value === '' ? $default : $value;
    }
}
