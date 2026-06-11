<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Store;
use App\Models\Region;
use App\Models\Area;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\Tasks;
use App\Models\Role;
use App\Models\Log;
use App\Models\City;
use App\Services\TenantSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:areas,user')->only(['index', 'store', 'edit', 'update']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_route_management')) {
                Alert::warning('غیرفعال', 'مدیریت مسیرها برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {

        $user = Auth::user();
        $roles = Role::all();
        if ($user->isAdmin == 1) {
            $Regions = Region::all();
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
            $Areas = Area::all();
        } else {
            $isAdmin = 0;
            $isVisitor = false;
            $isLeader = false;
            $storesUser = collect();
            $organizations = Organization::forOrganizations($user, 'id')->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
                $select_role = Role::find($role->id);
                if ($select_role->title == 'visitor') {
                    $isVisitor = true;
                }
                if ($select_role->title == 'leader') {
                    $isLeader = true;
                }
            }
            if ($isVisitor == true) {
                $Regions = Region::forOrganizations($user)->where('leader_id', $user->leader_id)->get();
                $Regions_ids = Region::forOrganizations($user)->where('leader_id', $user->leader_id)->pluck('id');
            }
            if ($isLeader == true) {
                $Regions = Region::forOrganizations($user)->where('leader_id', $user->id)->get();
                $Regions_ids = Region::forOrganizations($user)->where('leader_id', $user->id)->pluck('id');
            } else {
                $Regions = Region::forOrganizations($user)->get();
                $Regions_ids = Region::forOrganizations($user)->pluck('id');
            }

            $Areas = Area::whereIn('region_id', $Regions_ids)->get();
        }
        $Leaders = $this->getLeaderUsersForOrganization($user->organization_id);

        return view('areas.index', compact('Regions', 'Areas', 'Leaders'));
    }

    public function store(Request $request)
    {
        $data = $this->areaData($request);
        $Area = Area::create($data);
        $user = Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'منطقه ایجاد شد' . '-' . $Area->name
        ]);

        Alert::success('تشکر', 'ناحیه با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Area $area)
    {
        $Cur_Region = Region::find($area->region_id);
        $user = Auth::user();
        $roles = Role::all();
        if ($user->isAdmin == 1) {
            $Regions = Region::all();
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
            $Areas = Area::all();
        } else {
            $isAdmin = 0;
            $isVisitor = false;
            $isLeader = false;
            $storesUser = collect();
            $organizations = Organization::where('id', $user->organization_id)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
                $select_role = Role::find($role->id);
                if ($select_role->title == 'visitor') {
                    $isVisitor = true;
                }
                if ($select_role->title == 'leader') {
                    $isLeader = true;
                }
            }
            if ($isVisitor == true) {
                $Regions = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->leader_id)->get();
                $Regions_ids = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->leader_id)->pluck('id');
            }
            if ($isLeader == true) {
                $Regions = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->id)->get();
                $Regions_ids = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->id)->pluck('id');
            } else {
                $Regions = Region::whereIn('store_id', $storesUser)->get();
                $Regions_ids = Region::whereIn('store_id', $storesUser)->pluck('id');
            }

            $Areas = Area::whereIn('region_id', $Regions_ids)->get();
        }


        $Leaders = $this->getLeaderUsersForOrganization($Cur_Region ? $Cur_Region->organization_id : $user->organization_id);

        return view('areas.edit', compact('Areas', 'area', 'Cur_Region', 'Regions', 'Leaders'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Area $area)
    {
        $Regions = Region::all();
        $Areas = Area::all();
        $Cur_Region = Region::find($area->region_id);

        $area->update($this->areaData($request));
        $user = Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'مسیر ویرایش شد' . '-' . $area->title
        ]);

        Alert::success('تشکر', 'مسیر با موفقیت ویرایش شد');
        return redirect()->route('areas.edit', compact('Areas', 'area', 'Cur_Region', 'Regions'));
    }

    private function getLeaderUsersForOrganization($organizationId)
    {
        $leaderRoleId = Role::where('title', 'leader')->value('id');

        if (!$leaderRoleId) {
            return collect();
        }

        $leaderUserIds = DB::table('role_user')
            ->where('role_id', $leaderRoleId)
            ->pluck('user_id')
            ->toArray();

        $query = User::whereIn('id', $leaderUserIds)
            ->where('isActive', 1);

        if ($organizationId) {
            $organizationFilterUser = new User();
            $organizationFilterUser->organization_id = $organizationId;
            $query->forOrganizations($organizationFilterUser);
        }

        return $query->get();
    }

    private function areaData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'visit_days' => ['nullable', 'array'],
            'visit_days.*' => ['string', 'in:' . implode(',', array_keys(Area::VISIT_DAY_LABELS))],
            'visit_frequency' => ['nullable', 'string', 'in:' . implode(',', array_keys(Area::VISIT_FREQUENCY_LABELS))],
        ]);

        $region = Region::find($data['region_id']);
        $user = Auth::user();

        $data['leader_id'] = $data['leader_id'] ?? null;
        $data['visit_days'] = array_values($data['visit_days'] ?? []);
        $data['visit_frequency'] = $data['visit_frequency'] ?? 'weekly';
        $data['organization_id'] = $region?->organization_id;
        $data['tenant_id'] = $region?->tenant_id ?: ($user?->tenant_id ?: $user?->tenants_id);

        return $data;
    }

    public function CustomersList($area_id)
    {
        $Area = Area::find($area_id);
        $CustomersCount = $Area->customersCount();
        $Customers = Customers::where('area', $area_id)->get();
        $Customers_ids = Customers::where('area', $area_id)->pluck('id');
        $customersWithPurchaseCount = $Area->activeCustomersCount();
        $customersPurchaseCount = $Area->activeOrdersSum();

        return view('areas.customersList', compact('Area', 'Customers', 'CustomersCount', 'customersWithPurchaseCount', 'customersPurchaseCount'));
    }

    public function activeCustomersList($area_id)
    {
        $Area = Area::find($area_id);
        $Customers = $Area->activeCustomers();
        $CustomersCount = $Area->customersCount();
        $customersWithPurchaseCount = $Area->activeCustomersCount();
        $customersPurchaseCount = $Area->activeOrdersSum();

        return view('areas.customersList', compact('Area', 'Customers', 'CustomersCount', 'customersWithPurchaseCount', 'customersPurchaseCount'));
    }

    public function invoiceList($area_id)
    {

        $isVisitor = false;
        $isManager = false;
        $isLeader = false;

        $user = Auth::user();

        $Area = Area::find($area_id);
        $PishFactors = $Area->activeOrders();
        $Cities = City::forOrganizations($user)->get();

        return view('invoices.PishFactors', compact('PishFactors', 'Cities', 'isManager', 'isLeader', 'isVisitor'));
    }

    public function getAreasByRegion($region_id)
    {

        $isVisitor = false;
        $isManager = false;
        $isLeader = false;

        $user = Auth::user();
        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            $select_role = Role::find($role->id);
            if ($select_role->title == 'visitor') {
                $isVisitor = true;
            }
            if ($select_role->title == 'leader') {
                $isLeader = true;
            }
        }

        if ($isVisitor) {
            $areaIds = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->pluck('area_id')->unique();
            $Areas = Area::whereIn('id', $areaIds)->where('region_id', $region_id)->get();
        } else {
            $Region = Region::find($region_id);
            $Areas = Area::where('region_id', $region_id)->get();
        }


        return response()->json($Areas);
    }

    public function destroy($id)
    {

        $user = Auth::user();
        $Area = Area::findOrFail($id);
        $Area->delete();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'delete',
            'description' => ' مسیر توسط ' . $user->name . ' حذف شد' . '-' . $Area->title
        ]);


        return back();
    }
}
