<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Store;
use App\Models\Brand;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:brands,user')->only(['index','store','edit','update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        //Stores
        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive',1)->get();
            $stores = Store::where('isActive',1)->get();
            $brands = Brand::latest()->get();
        } else {
            $organizations = Organization::where('id', $user->organization_id)->where('isActive',1)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $stores = Store::whereIn('id',$storesUser)->where('isActive',1)->get();
            $brands = Brand::whereIn('store_id',$storesUser)->latest()->get();
        }


        return view('brands.index',compact('brands','stores','organizations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $request->organization_id = $user->organization_id;
        $brand = Brand::create($request->all());

        ActivityLogService::safeLogModel('create', 'برند ایجاد شد' . '-' . $brand->title, $brand, ['section' => 'product', 'event_key' => 'brand.create']);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {

        //Stores
        $user = \Auth::user();
        $organizations = Organization::forOrganizations($user, 'id')->get();
        if ($user->isAdmin == 1) {

            $stores = Store::forOrganizations($user)->get();

        } else {

            $stores = Store::whereIn('id',$organizations)->get()->where('isActive',1)->get();
        }

        $brands = Brand::forOrganizations($user)->get();

        return view('brands.edit',compact('brands','brand'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {

        //dd($request->all());
        $user = \Auth::user();
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;

        $brand->update([
            'title' => $request->title,
            'isActive' => 1,
            'organization_id' => $user->organization_id,
            'store_id' => $request->store_id
        ]);
        $user = \Auth::user();
        ActivityLogService::safeLogModel('update', 'برند ویرایش شد' . '-' . $brand->title, $brand, ['section' => 'product', 'event_key' => 'brand.update']);

        $brands = Brand::all();
        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->route('brands.edit',compact('brand','brands'));
    }

}
