<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\City;
use App\Models\Role;
use App\Models\Log;
use App\Services\TenantSettings;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:cities,user')->only(['index', 'store', 'edit', 'update']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_city_management')) {
                Alert::warning('غیرفعال', 'مدیریت شهرها برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {

        $user = \Auth::user();
        $roles = Role::all();
        if ($user->isGod == 1) {
            $Cities = City::all();
            $organizations = Organization::all();
        } elseif ($user->isAdmin == 1) {
            $Cities = City::forOrganizations($user)->get();
            $isAdmin = 1;
            $organizations = Organization::forOrganizations($user, 'id')->get();
        }

        return view('cities.index', compact('Cities', 'organizations'));
    }

    public function store(Request $request)
    {
        $City = City::create($request->all());
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'شهر ایجاد شد' . '-' . $City->name
        ]);

        Alert::success('تشکر', 'شهر با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(City $city)
    {

        $user = \Auth::user();
        $roles = Role::all();
        if ($user->isGod == 1) {
            $Cities = City::all();
            $organizations = Organization::all();
        } elseif ($user->isAdmin == 1) {
            $Cities = City::where('organization_id', $user->organization_id)->get();
            $isAdmin = 1;
            $organizations = Organization::where('id', $user->organization_id)->get();
        } else {
        }

        return view('cities.edit', compact('city', 'organizations', 'Cities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, City $city)
    {

        $city->update([
            'name' => $request->name,
            'organization_id' => $request->organization_id,
        ]);
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'شهر ویرایش شد' . '-' . $city->name
        ]);



        Alert::success('تشکر', 'شهر با موفقیت ویرایش شد');
        return redirect()->back();
    }

    public function getAreasByRegion($region_id)
    {

        $Region = Region::find($region_id);
        $Areas = Area::where('region_id', $region_id)->get();
        return response()->json($Areas);
    }
}
