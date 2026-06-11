<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbortionRequest;
use App\Models\Abortion;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\Log;
use App\Models\Organization;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class AbortionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:abortions,user')->only(['index', 'create', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $organizations = Organization::all();
            $abortions = Abortion::latest()->get();
            $stores = Store::all();
        } else {
            $organizations = Organization::where('id', $user->organization_id)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $abortions = Abortion::whereIn('store_id', $storesUser)->latest()->get();

            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
        }

        return view('abortions.index', compact('abortions', 'stores', 'categories', 'brands', 'parents'));
    }

    public function create()
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $stores = Store::where('isActive', 1)->get();
            $employees = Employee::where('isActive', 1)->get();
        } else {
            $organizations = Organization::where('id', $user->organization_id)->where('isActive', 1)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
            $employees = Employee::where('isActive', 1)->where('organization_id', $user->organization_id)->get();
        }

        return view('abortions.create', compact('employees', 'categories', 'stores', 'brands', 'parents', 'organizations'));
    }

    public function store(AbortionRequest $request)
    {
        $request['user_id'] = \Auth::user()->id;
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);

        $abortion = Abortion::create($request->all());
        $user = \Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'یک کالای اسقاطی ایجاد شد' . '-' . $abortion->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Abortion $abortion)
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $stores = Store::where('isActive', 1)->get();
        } else {
            $organizations = Organization::where('id', $user->organization_id)->where('isActive', 1)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
        }

        return view('abortions.edit', compact('abortion', 'categories', 'stores', 'brands', 'parents', 'organizations'));
    }

    public function update(AbortionRequest $request, Abortion $abortion)
    {
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);

        $abortion->update([
            'title' => $request->title,
            'description' => $request->description,
            'entity' => $request->entity,
            'brand_id' => $request->brand_id,
            'parentCategory_id' => $request->parentCategory_id,
            'chaildCategory_id' => isset($request->chaildCategory_id) ? $request->chaildCategory_id : $abortion->chaildCategory_id,
            'isActive' => $request->isActive,
            'organization_id' => $request->organization_id,
            'store_id' => isset($request->store_id) ? $request->store_id : $abortion->store_id,
            'inputDate' => $request->inputDate,
            'employee_id' => isset($request->employee_id) ? $request->employee_id : $abortion->employee_id,
        ]);

        $user = \Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'کالای اسقاطی ویرایش شد' . '-' . $abortion->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return back();
    }
    
    public function destroy($id)
    {
        $abortions = Abortion::findOrFail($id);
        $abortions->delete();
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
}
