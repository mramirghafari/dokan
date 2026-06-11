<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Repair;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\History;
use App\Models\Log;
use App\Models\Organization;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Requests\RepairRequest;

class RepairController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:repairs,user')->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
    }

    public function index()
    {
        $categories = Category::where('isActive', 1)->where('parent_id', Null)->get();
        $brands = Brand::where('isActive', 1)->get();
        $parents = Category::where('isActive', 1)->where('parent_id', '!=', Null)->get();

        //Stores
        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $repairs = Repair::where('isActive', 1)->get();
            $stores = Store::where('isActive', 1)->get();
        } else {
            $organizations = Organization::where('id', $user->organization_id)->where('isActive', 1)->get();
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $repairs = Repair::whereIn('store_id', $storesUser)->where('isActive', 1)->get();

            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
        }

        return view('repairs.index', compact('repairs', 'categories', 'brands', 'parents'));
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
            $employees = Employee::where('isActive', 1)->where('organization_id', $user->organization_id)->get();

            $stores = Store::whereIn('id', $storesUser)->where('isActive', 1)->get();
        }

        return view('repairs.create', compact('employees', 'categories', 'stores', 'brands', 'parents', 'organizations'));
    }

    public function store(RepairRequest $request)
    {
        $request['user_id'] = \Auth::user()->id;
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);
        $request['outputDate'] = $this->to_english_numbers($request['outputDate']);

        $repair = Repair::create($request->all());
        $user = \Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'یک کالا جهت تعمیر ایجاد شد' . '-' . $repair->title
        ]);

        History::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'entity',
            'store' => $repair->store->title,
            'description' => " برای کالای تعمیری " . $repair->title . " تعداد " . $repair->entity . " عدد ثبت شد"
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Repair $repair)
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

        return view('repairs.edit', compact('employees', 'repair', 'categories', 'stores', 'brands', 'parents', 'organizations'));
    }

    public function update(RepairRequest $request, Repair $repair)
    {
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);
        $request['outputDate'] = $this->to_english_numbers($request['outputDate']);
        $number = $repair->entity;

        $repair->update([
            'title' => $request->title,
            'description' => $request->description,
            'entity' => $request->entity,
            'brand_id' => $request->brand_id,
            'parentCategory_id' => $request->parentCategory_id,
            'chaildCategory_id' => isset($request->chaildCategory_id) ? $request->chaildCategory_id : $repair->chaildCategory_id,
            'isActive' => $request->isActive,
            'organization_id' => $request->organization_id,
            'store_id' => isset($request->store_id) ? $request->store_id : $repair->store_id,
            'inputDate' => $request->inputDate,
            'outputDate' => $request->outputDate,
            'employee_id' => isset($request->employee_id) ? $request->employee_id : $repair->employee_id,
        ]);

        $user = \Auth::user();

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'کالای تعمیری ویرایش شد' . '-' . $repair->title
        ]);

        if ($number != $repair->entity) {
            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'editEntity',
                'store' => $repair->store->title,
                'description' => " برای کالای تعمیری " . $repair->title . " از تعداد " . $number . " به تعداد " . $repair->entity . " تغییر یافت"
            ]);
        }

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return back();
    }


    public function getEmployee($id)
    {
        $employee_id = Employee::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($employee_id);
    }

    public function getCategory($id)
    {
        $childCategory_id = Category::where('parent_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($childCategory_id);
    }

    public function getStore($id)
    {
        $store_id = Store::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($store_id);
    }

    public function destroy($id)
    {
        $repairs = Repair::findOrFail($id);
        $repairs->delete();
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
