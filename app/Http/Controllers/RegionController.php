<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenants;
use App\Models\Organization;
use App\Models\Area;
use App\Models\City;
use App\Models\Store;
use App\Models\Region;
use App\Models\Role;
use App\Services\TenantSettings;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:regions,user')->only(['index', 'store', 'edit', 'update']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_area_management')) {
                Alert::warning('غیرفعال', 'مدیریت مناطق برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $user = \Auth::user();
        $roles = Role::all();
        $Tenants = Tenants::all();
        $isVisitor = false;
        $isLeader = false;
        $isManager = false;
        if ($user->isGod == 1) {
            $Cities = City::all();
            $Regions = Region::all();
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
        } elseif ($user->isAdmin == 1) {
            $Cities = City::forOrganizations($user)->get();
            $Regions = Region::forOrganizations($user)->get();
            $isAdmin = 1;
            $organizations = Organization::forOrganizations($user, 'id')->get();
            $stores = Store::forOrganizations($user)->get();
        } else {
            $isAdmin = 0;

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
                if ($select_role->title == 'expert') {
                    $isManager = true;
                }
            }

            $stores = Store::forOrganizations($user)->get();
            $Cities = City::forOrganizations($user)->get();
            if ($isVisitor == true) {
                $Regions = Region::where('leader_id', $user->leader_id)->get();
                $Regions_ids = Region::where('leader_id', $user->leader_id)->pluck('id');
            }
            if ($isLeader == true) {
                $Regions = Region::where('leader_id', $user->id)->get();
                $Regions_ids = Region::where('leader_id', $user->id)->pluck('id');
            } else {
                $Regions = Region::forOrganizations($user)->get();
            }
        }

        $Users = $this->getLeaderUsersForOrganization($user->organization_id);

        return view('regions.index', compact('Cities', 'Regions', 'roles', 'isAdmin', 'Users', 'isManager', 'organizations', 'stores', 'Tenants'));
    }

    public function store(Request $request)
    {
        $Region = Region::create($request->all());
        $Region->leader_id = json_encode($request->leader_ids);
        $Region->save();
        $Region->roles()->sync($request->roles);
        $user = \Auth::user();
        ActivityLogService::safeLogModel('create', 'منطقه ایجاد شد' . '-' . $Region->title, $Region, ['section' => 'sales', 'event_key' => 'region.create']);

        Alert::success('تشکر', 'منطقه با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Region $region)
    {
        $roles = Role::all();

        $user = \Auth::user();
        $roles = Role::all();
        $isVisitor = false;
        $isLeader = false;
        $isManager = false;
        if ($user->isGod == 1) {
            $Cities = City::all();
            $Regions = Region::all();
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
        } elseif ($user->isAdmin == 1) {
            $Cities = City::where('organization_id', $user->organization_id)->get();
            $Regions = Region::where('organization_id', $user->organization_id)->get();
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
        } else {
            $isAdmin = 0;
            $Cities = City::where('organization_id', $user->organization_id)->get();
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
                if ($select_role->title == 'expert') {
                    $isManager = true;
                }
            }
            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();


            if ($isVisitor == true) {
                $Regions = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->leader_id)->get();
                $Regions_ids = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->leader_id)->pluck('id');
            }
            if ($isLeader == true) {
                $Regions = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->id)->get();
                $Regions_ids = Region::whereIn('store_id', $storesUser)->where('leader_id', $user->id)->pluck('id');
            } else {
                $Regions = Region::whereIn('store_id', $storesUser)->get();
            }
        }

        $Users = $this->getLeaderUsersForOrganization($region->organization_id ?: $user->organization_id);



        return view('regions.edit', compact('Cities', 'Regions', 'roles', 'region', 'isAdmin', 'Users', 'isManager', 'organizations', 'stores'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region)
    {

        //        dd($request->all());
        $roles = Role::all();

        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $leaderIds = collect((array) $request->leader_ids)
            ->filter()
            ->values();

        $region->update([
            'name' => $request->name,
            'northEast_lat' => $request->northEast_lat,
            'northEast_lang' => $request->northEast_lang,
            'southWest_lat' => $request->southWest_lat,
            'southWest_lang' => $request->southWest_lang,
            'city_id' => $request->city_id,
            'leader_id' => $leaderIds->count() > 1
                ? json_encode($leaderIds->all())
                : $leaderIds->first(),
            'organization_id' => $request->organization_id,
            'store_id' => $request->store_id,
        ]);
        $region->roles()->sync($request->roles);
        $user = \Auth::user();
        ActivityLogService::safeLogModel('update', 'منطقه ویرایش شد' . '-' . $region->title, $region, ['section' => 'sales', 'event_key' => 'region.update']);

        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $Regions = Region::all();
        } else {
            foreach ($user->roles as $role) {
                $storesUser = DB::table('region_role')->where('role_id', $role->id)->pluck('region_id')->toArray();
            }
            $Regions = Region::whereIn('id', $storesUser)->get();
        }

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->route('regions.edit', compact('Regions', 'roles', 'region'));
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

    public function areasList($region_id)
    {
        $Region = Region::find($region_id);
        $Areas = Area::where('region_id', $region_id)->get();

        return view('regions.areasList', compact('Areas', 'Region'));
    }

    public function CustomersList($region_id)
    {
        $Region = Region::find($region_id);
        $Customers = $Region->customersThroughAreas;
        $CustomersCount = $Region->customersThroughAreas->count();
        $customersWithPurchaseCount = $Region->activeCustomersCount();
        $customersPurchaseCount = $Region->activeOrdersSum();

        return view('regions.customersList', compact('Region', 'Customers', 'CustomersCount', 'customersWithPurchaseCount', 'customersPurchaseCount'));
    }

    public function activeCustomersList($region_id)
    {
        $Region = Region::find($region_id);
        $Customers = $Region->activeCustomers();
        $CustomersCount = $Region->customersThroughAreas->count();
        $customersWithPurchaseCount = $Region->activeCustomersCount();
        $customersPurchaseCount = $Region->activeOrdersSum();

        return view('regions.customersList', compact('Region', 'Customers', 'CustomersCount', 'customersWithPurchaseCount', 'customersPurchaseCount'));
    }

    public function invoiceList($region_id)
    {

        $isVisitor = false;
        $isManager = false;
        $isLeader = false;

        $user = \Auth::user();

        $Region = Region::find($region_id);
        $PishFactors = $Region->activeOrders();
        $Cities = City::forOrganizations($user)->get();

        return view('invoices.PishFactors', compact('PishFactors', 'Cities', 'isManager', 'isLeader', 'isVisitor'));
    }


    public function destroy($id)
    {

        $user = \Auth::user();
        $Region = Region::findOrFail($id);
        $Region->delete();

        ActivityLogService::safeLogModel('delete', ' منطقه توسط ' . $user->name . ' حذف شد' . '-' . $Region->title, $Region, ['section' => 'sales', 'event_key' => 'region.deleted']);

        Alert::success('تشکر', 'منطقه با موفقیت حذف شد');
        return redirect()->back();
    }
}
