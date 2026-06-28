<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\History;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Role;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_warehouse_management')) {
                Alert::warning('غیرفعال', 'مدیریت انبار و موجودی برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            if (!TenantSettings::enabled('feature_multi_warehouse')) {
                Alert::warning('غیرفعال', 'انتقال بین انبارها برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        $roles = Role::all();
        $admin = 0;
        foreach ($user->roles as $role) {
            if (in_array($role->title, ['admin', 'panel_manager'], true)) {
                $admin = 1;
                break;
            }
        }

        if ($admin == 1) {
            $transfers = Transfer::all();
        } else {
            $transfers = Transfer::where('approveUser', $user->id)->get();
        }
        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $users = User::where('isActive', 1)->get();

        //Stores
        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $fromOrganizations = Organization::where('isActive', 1)->get();
            $toOrganizations = Organization::where('isActive', 1)->get();
            $products = Product::latest()->get();
            $stocks = Stock::latest()->get();
            $transfers = Transfer::all();
        } else {
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $fromOrganizations = Organization::where('id', $user->organization_id)->where('isActive', 1)->get();
            $toOrganizations = Organization::where('isActive', 1)->get();
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
            $stocks = Stock::whereIn('store_id', $storesUser)->latest()->get();
            $transfers = Transfer::where('approveUser', \Auth::user()->id)->latest()->get();
        }


        return view('transfers.create', compact('toOrganizations', 'fromOrganizations', 'stocks', 'products', 'users', 'transfers'));
    }

    public function store(Request $request)
    {
        for ($i = 0; $i <= ($request->total_item - 1); $i++) {
            if ((isset($request['item_name'][$i])) && (isset($request['stock_name'][$i])) && ($request['item_name'][$i] == '') && ($request['stock_name'][$i] = '')) {
                Alert::error('خطا', 'امکان انتخاب کالای نو و دست دوم در یک سطر وجود ندارد');
                return back();
            }
        }

        //شرط کمتر از صفر شدن موجودی
        for ($i = 0; $i <= ($request->total_item - 1); $i++) {
            if (isset($request['item_name'][$i])) {
                $product = Product::where('id', $request['item_name'][$i])->first();
                $productEntity = $product->entity - $request['order_item_quantity'][$i];
                if ($productEntity < 0) {
                    Alert::error('خطا', 'موجودی کالای ' . $product->title . ' به زیر صفر می رسد');
                    return back();
                }
            } elseif (isset($request['stock_name'][$i])) {
                $stockEntity = Stock::where('id', $request['stock_name'][$i])->first();
                $stockEntity = $stockEntity->entity - $request['order_item_quantity'][$i];
                if ($stockEntity < 0) {
                    Alert::error('خطا', 'موجودی کالای ' . $stockEntity->title . ' به زیر صفر می رسد');
                    return back();
                }
            } else {
                Alert::error('خطا', 'باید یک کالا جهت انتقال انتخاب شود!');
                return back();
            }
        }

        $request['user_id'] = \Auth::user()->id;
        $request['transferDate'] = $this->to_english_numbers($request['transferDate']);

        //ثبت کالا در دیتابیس
        for ($i = 0; $i < $request->total_item; $i++) {
            if (isset($request['item_name'][$i])) {
                $transfer = Transfer::create([
                    'fromOrganization' => $request['fromOrganization'],
                    'toOrganization' => $request['toOrganization'],
                    'user_id' => $request['user_id'],
                    'approveUser' => $request['approveUser'],
                    'transferDate' => $request['transferDate'],
                    'description' => $request['description'],
                    'product_id' => $request['item_name'][$i],
                    'AmvalCode' => $request['order_item_amval'][$i],
                    'number' => $request['order_item_quantity'][$i]
                ]);
            } elseif (isset($request['stock_name'][$i])) {
                $transfer = Transfer::create([
                    'fromOrganization' => $request['fromOrganization'],
                    'toOrganization' => $request['toOrganization'],
                    'user_id' => $request['user_id'],
                    'approveUser' => $request['approveUser'],
                    'transferDate' => $request['transferDate'],
                    'description' => $request['description'],
                    'stock_id' => $request['stock_name'][$i],
                    'AmvalCode' => $request['order_item_amval'][$i],
                    'number' => $request['order_item_quantity'][$i]
                ]);
            }



            //Entity
            if ($transfer->product_id != null) {
                $entity = $transfer->product->entity;
                $transfer->product->update([
                    'entity' => $entity - $request['order_item_quantity'][$i]
                ]);
                $isProduct = 1;
            } else {
                $entity = $transfer->stock->entity;
                $transfer->stock->update([
                    'entity' => $entity - $request['order_item_quantity'][$i]
                ]);
                $isStock = 1;
            }
        }

        $fromOrganization = Organization::where('id', $transfer->fromOrganization)->first();
        $toOrganization = Organization::where('id', $transfer->toOrganization)->first();

        if ($isProduct == 1) {
            $tr = $transfer->product->title;
        } else {
            $tr = $transfer->stock->title;
        }
        ActivityLogService::safeLogModel('create', 'یک انتقال انبار به انبار ثبت اولیه شد' . '- از انبار ' . $fromOrganization->title . " به " . $toOrganization->title . " کالای " . $tr . " و تعداد " . $transfer->number, $fromOrganization, ['section' => 'system', 'event_key' => 'fromorganization.create']);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }


    public function approved(Transfer $transfer)
    {
        if ($transfer->isApproved == 0) {
            $transfer->update([
                'isApproved' => 1,
                'isRead' => 1
            ]);
        } else {
            $transfer->update([
                'isApproved' => 0,
                'denyUser' => \Auth::user()->id
            ]);
        }
        Alert::success('تشکر', 'عملیات با موفقیت انجام شد');
        return back();
    }

    public function read(Transfer $transfer)
    {
        if ($transfer->isRead == 0) {
            $transfer->update([
                'isRead' => 1
            ]);
        } else {
            $transfer->update([
                'isRead' => 0,
            ]);
        }
        Alert::success('تشکر', 'عملیات با موفقیت انجام شد');
        return back();
    }

    public function addProduct(Transfer $transfer)
    {
        $user = \Auth::user();

        if ($transfer->approveUser == $user->id) {
            $transfer->update(['isRead' => 1]);
        }
        $user = \Auth::user();

        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
        }

        if ($user->isAdmin == 1) {
            $products = Product::latest()->get();
            $stocks = Stock::latest()->get();
        } else {
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
            $stocks = Stock::whereIn('store_id', $storesUser)->latest()->get();
        }

        return view('transfers.add-product', compact('transfer', 'products', 'stocks'));
    }

    public function storeProduct(Transfer $transfer, Request $request)
    {
        //انتخاب کالا و کالای دست دوم با هم
        if ($request['product_id'] != '' && $request['stock_id'] != '') {
            Alert::error('خطا', 'لطفا یکی از موارد کالا یا کالای دست دوم را انتخاب کنید!');
            return back();
        }

        if (isset($request['product_id'])) {
            $productEntity = Product::where('id', $request['product_id'])->first();

            if ($request['approvedNumber'] != null) {
                $transfer->update(['isApproved' => 1]);
                $productEntity->update([
                    'entity' => $productEntity->entity + $request['approvedNumber']
                ]);
            } else {
                $transfer->update(['isApproved' => 1]);
                $productEntity->update([
                    'entity' => $productEntity->entity + $transfer->number
                ]);
            }
        } elseif (isset($request['stock_id'])) {
            $transfer->update(['isApproved' => 1]);
            $stockEntity = Stock::where('id', $request['stock_id'])->first();
            if ($request['approvedNumber'] != null) {
                $stockEntity->update([
                    'entity' => $stockEntity->entity + $request['approvedNumber']
                ]);
            } else {
                $transfer->update(['isApproved' => 1]);
                $stockEntity->update([
                    'entity' => $stockEntity->entity + $transfer->number
                ]);
            }
        } else {
            Alert::error('خطا', 'باید یک کالا جهت انجام عملیات انتخاب شود!');
            return back();
        }

        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $transfers = Transfer::all();
        } else {
            $transfers = Transfer::where('approveUser', \Auth::user()->id)->get();
        }

        Alert::success('تشکر', 'عملیات با موفقیت انجام شد');
        return redirect()->route('transfers.index', compact('transfers'));
    }

    public function denyTransfer(Transfer $transfer)
    {
        $transfer->update([
            'isDenied' => 1,
            'denyUser' => \Auth::user()->id,
        ]);

        if ($transfer->product_id != null) {
            $transfer->product->update([
                'entity' => $transfer->product->entity + $transfer->number
            ]);
        } else {
            $transfer->stock->update([
                'entity' => $transfer->stock->entity + $transfer->number
            ]);
        }

        $user = \Auth::user();
        if ($user->isAdmin == 1) {
            $transfers = Transfer::all();
        } else {
            $transfers = Transfer::where('approveUser', \Auth::user()->id)->get();
        }

        Alert::success('تشکر', 'عملیات با موفقیت انجام شد');
        return redirect()->route('transfers.index', compact('transfers'));
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
