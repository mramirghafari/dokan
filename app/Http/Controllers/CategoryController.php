<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Log;
use App\Models\Organization;
use App\Models\Store;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:categories,user')->only(['index','store','edit','update']);
    }

    public function index()
    {

        //Stores
        $user = \Auth::user();
        if ($user->isGod == 1) {
            $organizations = Organization::all();
            $stores = Store::where('isActive',1)->get();

            $categories = Category::all();

            $parents = Category::where('parent_id' , Null)->where('isActive',1)->get();

        }elseif ($user->isGod == 0 && $user->isAdmin == 1) {
            $organizations = Organization::forOrganizations($user, 'id')->get();
            $stores = Store::forOrganizations($user)->get();

            $categories = Category::all();

             $parents = Category::where('parent_id' , Null)->where('isActive',1)->get();

        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive',1)->pluck('id');
            $stores = Store::whereIn('id',$organizations)->where('isActive',1)->get();
            $categories = Category::forOrganizations($user)->get();

             $parents = Category::forOrganizations($user)->where('parent_id' , Null)->where('isActive',1)->get();
        }



        return view('categories.index',compact('categories','parents','organizations','stores'));
    }

    public function store(Request $request)
    {
        $category = Category::create($request->all());
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'دسته بندی ایجاد شد' . '-' . $category->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Category $category)
    {
        $categories = Category::all();
        $parents = Category::where('parent_id' , Null)->where('id','!=',$category->id)
                    ->where('isActive',1)->get();

        return view('categories.edit',compact('category','categories','parents'));
    }

    public function update(Request $request, Category $category)
    {
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $category->update([
            'title' => $request->title,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'isActive' => $request->isActive,
            'organization_id' => $request->organization_id
        ]);
        $categories = Category::all();
        $parents = Category::where('parent_id' , Null)->get();
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'دسته بندی ویرایش شد' . '-' . $category->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->route('categories.edit',compact('category','categories','parents'));
    }


}
