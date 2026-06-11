<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Material;
use App\Models\Product;
use App\Models\Organization;
use App\Models\City;
use App\Models\MaterialStore;
use App\Models\Store;
use App\Models\Role;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
class MaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:regions,user')->only(['index','store','edit','update']);
    }

    public function index()
    {
        $user = \Auth::user();
        $roles = Role::all();
        $isVisitor = false;
        $isLeader = false;
        $isManager = false;
        if ($user->isGod == 1) {
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
            $Materials = Product::forOrganizations($user)->where('isMaterial',1)->get();
        }elseif ($user->isAdmin == 1) {
            $isAdmin = 1;
            $organizations = Organization::forOrganizations($user, 'id')->get();
            $stores = Store::all();
            $Materials = Product::forOrganizations($user)->where('isMaterial',1)->get();
        } else {
            $isAdmin = 0;
            $organizations = Organization::forOrganizations($user, 'id')->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
                $select_role = Role::find($role->id);
                if($select_role->title == 'visitor') {
                    $isVisitor = true;
                }
                if($select_role->title == 'leader') {
                    $isLeader = true;
                }
                if($select_role->title == 'expert') {
                    $isManager = true;
                }
            }
            $Materials = Product::forOrganizations($user)->where('isMaterial',1)->where('isActive',1)->get();

        }

        return view('materials.index',compact('Materials','stores'));
    }
    
    public function store(Request $request)
    {

        //dd($request->all());
        $request['organization_id'] = auth()->user()->organization_id;
        $Material = Material::create($request->all());
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'ماده اولیه ایجاد شد' . '-' . $Material->name
        ]);

        Alert::success('تشکر', 'ماده اولیه با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Material $Material)
    {
        $user = \Auth::user();
        $roles = Role::all();
        $isVisitor = false;
        $isLeader = false;
        $isManager = false;
        if ($user->isGod == 1) {
            $isAdmin = 1;
            $organizations = Organization::all();
            $stores = Store::all();
            $Materials = Material::all();
        }elseif ($user->isAdmin == 1) {
            $isAdmin = 1;
            $organizations = Organization::forOrganizations($user,'id')->get();
            $stores = Store::all();
            $Materials = Material::forOrganizations($user)->get();
        } else {
            $isAdmin = 0;

            $organizations = Organization::forOrganizations($user,'id')->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
                $select_role = Role::find($role->id);
                if($select_role->title == 'visitor') {
                    $isVisitor = true;
                }
                if($select_role->title == 'leader') {
                    $isLeader = true;
                }
                if($select_role->title == 'expert') {
                    $isManager = true;
                }
            }
            $Materials = Material::forOrganizations($user)->get();

        }



        return view('materials.edit',compact('Materials','stores','Material'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Material $material)
    {

//        dd($request->all());
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $material->update([
            'name' => $request->name,
            'unit' => $request->unit,
            'sub_unit' => $request->sub_unit,
            'entity' => $request->entity,
            'entity_sub_unit' => $request->entity_sub_unit,
            'pack_items' => $request->pack_items,
            'pack_weight' => $request->pack_weight,
            'pack_weight_unit' => $request->pack_weight_unit,
            'price' => $request->price,
            'material_store_id' => $request->material_store_id,
            'organization_id' => auth()->user()->organization_id,
        ]);
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'ماده اولیه ویرایش شد' . '-' . $material->name
        ]);


        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->back();
    }


    public function destroy($id)
    {

        $user = \Auth::user();
        $Region = Region::findOrFail($id);
        $Region->delete();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'delete',
            'description' => ' منطقه توسط '.$user->name.' ایکس حذف شد' . '-' . $Region->title
        ]);


        return back();
    }


}
