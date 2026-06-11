<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Store;
use App\Models\factorMaker;
use App\Models\Role;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
class FactorSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:factormanager,user')->only(['index','store','edit','update']);
    }

    public function index()
    {

        $User = auth()->user();
        if($User->isGod == 1) {
            $Organizations = Organization::all();
            $Factors = factorMaker::all();
            $Stores = Store::all();
        }elseif($User->isAdmin == 1) {
            $Organizations = Organization::forOrganizations($User,'id')->get();
            $Factors = factorMaker::forOrganizations($User)->get();
            $Stores = Store::forOrganizations($User)->get();
        }



        return view('FactorManager.index',compact('Organizations','Factors','Stores'));
    }

    public function store(Request $request)
    {

         // dd($request->all());

        $request['organization_id'] = json_encode($request['organization_id']);
        $request['store_id'] = json_encode($request['store_id']);
        $Factor = factorMaker::create($request->all());
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'تنظیمات فاکتور توسط '.$user->name.' ایجاد شد' . '-' . $Factor->name
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
    public function edit(Request $request,$id)
    {

        $User = \Auth::user();
        $factorMaker = factorMaker::find($id);

// پیدا کردن خانه‌های مشترک
        if($User->isGod == 0 && $User->organization_id != null && $factorMaker->organization_id != null) {
            $userOrgans = json_decode($User->organization_id);
            $FactorOrgans = json_decode($factorMaker->organization_id);
            $common = array_intersect($userOrgans, $FactorOrgans);
            if(empty($common)) {
                return redirect(route('FactorManager.index'));
            }
        }



        if($User->isGod == 1) {
            $Organizations = Organization::all();
            $Stores = Store::all();
        }elseif($User->isAdmin == 1) {
            $Organizations = Organization::forOrganizations($User,'id')->get();
            $Stores = Store::forOrganizations($User)->get();
        }



        return view('FactorManager.edit',compact('Organizations','factorMaker','Stores'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        //dd($request->all());

        //dd($id);
        $factorMaker = factorMaker::findorFail($id);
        $factorMaker->update([
            'name' => $request->name,
            'type'=> $request->type,
            'pr_type'=> $request->pr_type,
            'currency_type'=> $request->currency_type,
            'seller_name'=> $request->seller_name,
            'seller_economic_number'=> $request->seller_economic_number,
            'seller_registration_number'=> $request->seller_registration_number,
            'seller_id_number'=> $request->seller_id_number,
            'seller_address'=> $request->seller_address,
            'seller_zip_code'=> $request->seller_zip_code,
            'seller_phone'=> $request->seller_phone,
            'seller_fax'=> $request->seller_fax,
            'buyer_name'=> $request->buyer_name,
            'buyer_econimic_code'=> $request->buyer_econimic_code,
            'buyer_registration_number'=> $request->buyer_registration_number,
            'buyer_address'=> $request->buyer_address,
            'buyer_zip_code'=> $request->buyer_zip_code,
            'buyer_phone'=> $request->buyer_phone,
            'buyer_region_area'=> $request->buyer_region_area,
            'buyer_map_code'=> $request->buyer_map_code,
            'visitor_display'=> $request->visitor_display,
            'visitor_mobile'=> $request->visitor_mobile,
            'column_pr_code'=> $request->column_pr_code,
            'column_moadian'=> $request->column_moadian,
            'column_sub_unit'=> $request->column_sub_unit,
            'column_discount'=> $request->column_discount,
            'column_tax'=> $request->column_tax,
            'organization_id'=> $request->organization_id,
            'store_id'=> $request->store_id
        ]);

        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'تنظیمات فاکتور ویرایش شد' . '-' . $factorMaker->name
        ]);

        Alert::success('تشکر', 'مسیر با موفقیت ویرایش شد');
        return redirect()->back();
    }


}
