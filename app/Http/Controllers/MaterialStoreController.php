<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\MaterialStore;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class MaterialStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:materials_store,user')->only(['index','store','edit','update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();

        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive',1)->get();
            $stores = MaterialStore::all();
        } else {
            $organizations = Organization::where('isActive',1)->where('id', $user->organization_id)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id')->toArray();
            }
            $stores = MaterialStore::whereIn('id',$storesUser)->get();
        }

        return view('materials_stores.index',compact('stores','roles','organizations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $store = MaterialStore::create($request->all());
        $store->roles()->sync($request->roles);
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'انبار مواد اولیه ایجاد شد' . '-' . $store->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Store $store)
    {
        $roles = Role::all();

        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive',1)->get();
            $stores = Store::where('isActive',1)->get();
        } else {
            $organizations = Organization::where('isActive',1)->where('id', $user->organization_id)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id')->toArray();
            }
            $stores = Store::whereIn('id',$storesUser)->where('isActive',1)->get();
        }


        return view('stores.edit',compact('stores','store','roles','organizations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        $roles = Role::all();

        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $store->update([
            'title' => $request->title,
            'description' => $request->description,
            'organization_id' => $request->organization_id,
            'isActive' => $request->isActive
        ]);
        $store->roles()->sync($request->roles);
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'انبار ویرایش شد' . '-' . $store->title
        ]);

        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive',1)->get();
            $stores = Store::where('isActive',1)->get();
        } else {
            $organizations = Organization::where('isActive',1)->where('id', $user->organization_id)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id')->toArray();
            }
            $stores = Store::whereIn('id',$storesUser)->where('isActive',1)->get();
        }

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->route('stores.edit',compact('store','stores','roles','organizations'));
    }

    public function destroy($id)
    {

        $user = \Auth::user();
        $MaterialStore = MaterialStore::findOrFail($id);
        $MaterialStore->delete();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'delete',
            'description' => ' انبار مواد اولیه توسط '.$user->name.' حذف شد' . '-' . $MaterialStore->title
        ]);


        return back();
    }

}
