<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\User;
use App\Models\Depot;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Store;
use App\Models\Brand;
use RealRashid\SweetAlert\Facades\Alert;

class DepotController extends Controller
{

    public function index() {
        $user = auth()->user();
        if($user->isGod = 1) {
            $Products = Product::where('isActive', 1)->get();
        }else {
            $Products = Product::where('isActive', 1)->where('organization_id',auth()->user()->organization_id)->get();
        }

        return view('stocks.depots', compact('Products'));
    }

    public function show($product) {

        $Product = Product::find($product);
        $Depots = Depot::where('pr_id', $product)->get();
        $Organ = Organization::find(auth()->user()->organization_id);

        $Stores = Store::where('tenants_id', $Organ->tenants_id)->where('isActive',1)->get();
        $Brands = Brand::where('organization_id', auth()->user()->organization_id)->where('isActive',1)->get();

        return view('stocks.product_depots', compact('Product','Depots','Stores','Brands'));


    }
    public function store(Request $request)
    {

       // dd($request->all());
        $user = \Auth::user();

        $Product = Product::find($request->pr_id);

        $request['leader_id'] = $user->id;
        $Depot = Depot::create($request->all());

        ActivityLogService::safeLogModel('create', "یک بار جدید برای". $Product->name."توسط ".$user->name." ایجاد شد ", $Product, ['section' => 'product', 'event_key' => 'product.create']);

        Alert::success('تشکر', "یک بار جدید برای این محصول افزوده شد");
        return back();
    }
}
