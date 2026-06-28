<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Detail;
use App\Models\User;
use App\Models\City;
use App\Models\Area;
use App\Models\Region;
use App\Models\Organization;
use App\Models\Customers;
use App\Models\Product;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Store;
use App\Models\History;
use App\Models\Role;
use App\Models\PaymentTerminal;
use App\Models\Accounts;
use App\Services\AccountingPostingService;
use App\Services\InventoryLedgerService;
use App\Services\PishFactorListService;
use App\Services\SalesScenarioService;
use App\Services\TenantSettings;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Facades\Route;


class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:invoices,user')->only(['index']);
        $this->middleware('can:invoice-add,user')->only(['create', 'store', 'edit', 'update', 'destroy', 'pishFactorInfo']);
        $this->middleware('can:invoice-product-list,user')->only(['detailList', 'deleteSubDetail']);
        $this->middleware('can:add_visitor_factor,user')->only(['detailList', 'deleteSubDetail']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_manager_order_approval')) {
                Alert::warning('غیرفعال', 'تایید مدیریتی سفارش برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['index', 'waiting_orders', 'actions']);
    }
    public function all_invoices(Request $request)
    {
        return $this->pishFactorListPage($request, PishFactorListService::LIST_ALL, 'invoices.all_invoices');
    }

    public function index(Request $request)
    {
        return $this->pishFactorListPage($request, PishFactorListService::LIST_PENDING, 'invoices.index');
    }

    public function active_list(Request $request)
    {
        return $this->pishFactorListPage($request, PishFactorListService::LIST_ACTIVE, 'invoices.active_list');
    }

    public function denciled(Request $request)
    {
        return $this->pishFactorListPage($request, PishFactorListService::LIST_DECLINED, 'invoices.denciled');
    }

    public function compeleted(Request $request)
    {
        return $this->pishFactorListPage($request, PishFactorListService::LIST_COMPLETED, 'invoices.compeleted');
    }

    public function pishFactorsDatatable(Request $request)
    {
        $user = Auth::user();
        $service = app(PishFactorListService::class);
        $roles = $service->resolvePageContext($user);

        $listKey = (string) $request->input('list', PishFactorListService::LIST_PENDING);
        $allowedLists = [
            PishFactorListService::LIST_PENDING,
            PishFactorListService::LIST_ACTIVE,
            PishFactorListService::LIST_DECLINED,
            PishFactorListService::LIST_COMPLETED,
            PishFactorListService::LIST_ALL,
            PishFactorListService::LIST_CUSTOMER,
        ];

        if (!in_array($listKey, $allowedLists, true)) {
            $listKey = PishFactorListService::LIST_PENDING;
        }

        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 50), 10), 100);
        $orderColumn = (int) data_get($request->input('order.0.column'), 1);
        $orderDirection = data_get($request->input('order.0.dir'), 'desc') === 'asc' ? 'asc' : 'desc';

        $showStoreColumn = (int) $user->isAdmin === 1;
        $showVisitorColumn = empty($roles['isLeader']);
        $canDelete = (int) $user->isAdmin === 1 || (int) $user->isGod === 1;

        $result = $service->datatable(
            $user,
            $listKey,
            $request,
            $roles,
            $start,
            $length,
            $orderColumn,
            $orderDirection,
            $showStoreColumn,
            $showVisitorColumn,
            $canDelete
        );

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    private function pishFactorListPage(Request $request, string $listKey, string $backlinkRoute)
    {
        $user = Auth::user();
        $service = app(PishFactorListService::class);
        $roles = $service->resolvePageContext($user);
        $showStoreColumn = (int) $user->isAdmin === 1;
        $showVisitorColumn = empty($roles['isLeader']);

        session()->put('backlink', route($backlinkRoute));

        return view('invoices.PishFactors', array_merge($roles, [
            'listKey' => $listKey,
            'pishFactorsTotal' => $service->count($user, $listKey, $request, $roles),
            'filterValues' => $service->filterValues($request),
            'showStoreColumn' => $showStoreColumn,
            'showVisitorColumn' => $showVisitorColumn,
            'datatableColumnCount' => $service->datatableColumnCount($showStoreColumn, $showVisitorColumn),
        ]));
    }
    public function factor_reporter(Request $request)
    {

        $user = \Auth::user();
        $isVisitor = false;
        $isManager = false;
        $isLeader = false;

        // $invoices = Invoice::latest()->get();
        // $subDetails = Detail::all();
        $Cities = City::forOrganizations($user)->get();
        if ($request->query->count() > 0) {
            $PishFactors = Pishfactor::forOrganizations($user)->whereIn('status', [1, 4]);

            if ($request->has('from_date') && $request->from_date != null) {
                $PishFactors->when($request->from_date, function ($q) use ($request) {

                    $fromDate = str_replace("/", "-", $request->get('from_date'));

                    $jalaliFrom = explode("-", $fromDate);
                    $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
                    $ymF = $miladiFrom[0];
                    if (strlen($miladiFrom[1]) == 1) {
                        $mmF = "0" . $miladiFrom[1];
                    } else {
                        $mmF = $miladiFrom[1];
                    };
                    if (strlen($miladiFrom[2]) == 1) {
                        $dmF = "0" . $miladiFrom[2];
                    } else {
                        $dmF = $miladiFrom[2];
                    };


                    $toDate = str_replace("/", "-", $request->get('to_date'));

                    $jalaliTo = explode("-", $toDate);
                    $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
                    $ymT = $miladiTo[0];
                    if (strlen($miladiTo[1]) == 1) {
                        $mmT = "0" . $miladiTo[1];
                    } else {
                        $mmT = $miladiTo[1];
                    };
                    if (strlen($miladiTo[2]) == 1) {
                        $dmT = "0" . $miladiTo[2];
                    } else {
                        $dmT = $miladiTo[2];
                    };

                    $q->whereBetween('created_at', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"]);
                });
            }

            if ($request->delivery_from_date != null) {
                $PishFactors->when($request->delivery_from_date, function ($q) use ($request) {

                    $fromDate = str_replace("/", "-", $request->get('delivery_from_date'));

                    $jalaliFrom = explode("-", $fromDate);
                    $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
                    $ymF = $miladiFrom[0];
                    if (strlen($miladiFrom[1]) == 1) {
                        $mmF = "0" . $miladiFrom[1];
                    } else {
                        $mmF = $miladiFrom[1];
                    };
                    if (strlen($miladiFrom[2]) == 1) {
                        $dmF = "0" . $miladiFrom[2];
                    } else {
                        $dmF = $miladiFrom[2];
                    };


                    $toDate = str_replace("/", "-", $request->get('delivery_to_date'));

                    $jalaliTo = explode("-", $toDate);
                    $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
                    $ymT = $miladiTo[0];
                    if (strlen($miladiTo[1]) == 1) {
                        $mmT = "0" . $miladiTo[1];
                    } else {
                        $mmT = $miladiTo[1];
                    };
                    if (strlen($miladiTo[2]) == 1) {
                        $dmT = "0" . $miladiTo[2];
                    } else {
                        $dmT = $miladiTo[2];
                    };

                    $q->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"]);
                });
            }

            if ($request->leader_id != null) {

                $PishFactors->when($request->leader_id, function ($q) use ($request) {
                    $q->where('sarparast_id', $request->leader_id);
                });
            }

            if ($request->visitor_id != null) {
                $PishFactors->when($request->visitor_id, function ($q) use ($request) {

                    $q->where('visitor_id', $request->visitor_id);
                });
            }

            $PishFactors = $PishFactors->orderBy('id', 'desc')->get();
        } else {
            $PishFactors = null;
        }

        $Visitor_Role = Role::where('title', 'visitor')->first();
        $visitor_Users = DB::table('role_user')->where('role_id', $Visitor_Role->id)->pluck('user_id')->toArray();
        $Visitors = User::forOrganizations($user)->whereIn('id', $visitor_Users)->where('isActive', 1)->get();
        $leader_role = Role::where('title', 'leader')->first();
        $leader_Users = DB::table('role_user')->where('role_id', $leader_role->id)->pluck('user_id')->toArray();
        $Leaders = User::forOrganizations($user)->whereIn('id', $leader_Users)->where('isActive', 1)->get();

        session()->put('backlink', route('invoices.reporter'));
        return view('invoices.Reports', compact('PishFactors', 'Cities', 'isVisitor', 'isManager', 'isLeader', 'Visitors', 'Leaders', 'Visitors'));
    }

    public function myInvoices(Request $request)
    {

        $user = \Auth::user();
        $isVisitor = false;
        $isManager = false;
        $isLeader = false;
        $isAgent = false;

        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            if ($role->title == 'visitor') {
                $isVisitor = true;
            }
            if ($role->title == 'expert') {
                $isManager = true;
            }
            if ($role->title == 'leader') {
                $isLeader = true;
            }
            if ($this->isAgentLikeRole($role)) {
                $isAgent = true;
            }
        }

        $Cities = City::forOrganizations($user)->get();
        if ($request->query->count() > 0) {
            $PishFactors = Pishfactor::forOrganizations($user)->whereIn('status', [1, 4]);

            if ($isLeader) {
                $PishFactors->where('sarparast_id', $user->id);
            }

            // اگر ویزیتور باشد
            if ($isVisitor) {
                $PishFactors->where('visitor_id', $user->id);
            }

            if ($isAgent) {
                $PishFactors->where('visitor_id', $user->id);
            }

            if ($request->has('from_date') && $request->from_date != null) {
                $PishFactors->when($request->from_date, function ($q) use ($request) {

                    $fromDate = str_replace("/", "-", $request->get('from_date'));

                    $jalaliFrom = explode("-", $fromDate);
                    $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
                    $ymF = $miladiFrom[0];
                    if (strlen($miladiFrom[1]) == 1) {
                        $mmF = "0" . $miladiFrom[1];
                    } else {
                        $mmF = $miladiFrom[1];
                    };
                    if (strlen($miladiFrom[2]) == 1) {
                        $dmF = "0" . $miladiFrom[2];
                    } else {
                        $dmF = $miladiFrom[2];
                    };


                    $toDate = str_replace("/", "-", $request->get('to_date'));

                    $jalaliTo = explode("-", $toDate);
                    $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
                    $ymT = $miladiTo[0];
                    if (strlen($miladiTo[1]) == 1) {
                        $mmT = "0" . $miladiTo[1];
                    } else {
                        $mmT = $miladiTo[1];
                    };
                    if (strlen($miladiTo[2]) == 1) {
                        $dmT = "0" . $miladiTo[2];
                    } else {
                        $dmT = $miladiTo[2];
                    };

                    $q->whereBetween('created_at', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"]);
                });
            }

            if ($request->delivery_from_date != null) {
                $PishFactors->when($request->delivery_from_date, function ($q) use ($request) {

                    $fromDate = str_replace("/", "-", $request->get('delivery_from_date'));

                    $jalaliFrom = explode("-", $fromDate);
                    $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
                    $ymF = $miladiFrom[0];
                    if (strlen($miladiFrom[1]) == 1) {
                        $mmF = "0" . $miladiFrom[1];
                    } else {
                        $mmF = $miladiFrom[1];
                    };
                    if (strlen($miladiFrom[2]) == 1) {
                        $dmF = "0" . $miladiFrom[2];
                    } else {
                        $dmF = $miladiFrom[2];
                    };


                    $toDate = str_replace("/", "-", $request->get('delivery_to_date'));

                    $jalaliTo = explode("-", $toDate);
                    $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
                    $ymT = $miladiTo[0];
                    if (strlen($miladiTo[1]) == 1) {
                        $mmT = "0" . $miladiTo[1];
                    } else {
                        $mmT = $miladiTo[1];
                    };
                    if (strlen($miladiTo[2]) == 1) {
                        $dmT = "0" . $miladiTo[2];
                    } else {
                        $dmT = $miladiTo[2];
                    };

                    $q->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"]);
                });
            }


            if ($request->visitor_id != null) {
                $PishFactors->when($request->visitor_id, function ($q) use ($request) {

                    $q->where('visitor_id', $request->visitor_id);
                });
            }

            $PishFactors = $PishFactors->orderBy('id', 'desc')->get();
        } else {
            $PishFactors = null;
        }

        $Visitor_Role = Role::where('title', 'visitor')->first();
        $visitor_Users = DB::table('role_user')->where('role_id', $Visitor_Role->id)->pluck('user_id')->toArray();
        $Visitors = User::forOrganizations($user)->whereIn('id', $visitor_Users)->where('leader_id', $user->id)->where('isActive', 1)->get();


        session()->put('backlink', route('invoices.myInvoices'));
        return view('invoices.myInvoices', compact('PishFactors', 'Cities', 'isVisitor', 'isManager', 'isLeader', 'isAgent', 'Visitors', 'Visitors'));
    }

    public function create()
    {

        //Stores
        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $products = Product::where('isActive', 1)->get();
        } else {
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
        }

        $lastNum = Invoice::latest()->first();
        if (!isset($lastNum)) {
            $invNum = 1;
        } else {
            $invNum = ++$lastNum->id;
        }
        return view('invoices.create', compact('products', 'user', 'invNum'));
    }

    public function store(Request $request)
    {
        $user = \Auth::user();

        $request['buyDate'] = $this->to_english_numbers($request['buyDate']);
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);

        //////Field For Invoice
        $invoiceID   = $request->order_no;
        $user_id     = \Auth::user()->id;
        $shopName    = $request->order_receiver_name;
        $address     = $request->order_receiver_address;
        $phone       = $request->phone;
        $buyDate     = $request->buyDate;
        $inputDate   = $request->inputDate;
        $description = $request->description;

        $price = 0;
        //////Total Price
        for ($i = 0; $i <= ($request->total_item - 1); $i++) {
            $price = $request['order_item_actual_amount'][$i] + $price;
        }

        $invoice = Invoice::create([
            'invoiceID' => $invoiceID,
            'user_id' => $user_id,
            'shopName' => $shopName,
            'address' => $address,
            'phone' => $phone,
            'buyDate' => $buyDate,
            'inputDate' => $inputDate,
            'description' => $description,
            'price' => $price
        ]);

        //Upload File
        if ($request->file()) {
            $file = $request->file;
            $path = time() . $file->getClientOriginalName();
            $path = str_replace(' ', '-', $path);
            $file->move('storage/', $path);
            $path = '/almas/storage/app/public/uploads/' . $path;
            $invoice->update([
                'file' => $path
            ]);
        }

        //////Field For Details
        for ($i = 0; $i <= ($request->total_item - 1); $i++) {
            $detail = Detail::create([
                'invoice_id' => $invoice->id,
                'product_id' => $request['item_name'][$i],
                'garanty' => $request['order_item_garanty'][$i],
                'number' => $request['order_item_quantity'][$i],
                'price' => $request['order_item_price'][$i],
                'totalPrice' => $request['order_item_actual_amount'][$i],
            ]);

            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'increment',
                'store' => $detail->product->store->title,
                'description' => " برای کالای " . $detail->product->title . " افزایش موجودی طی فاکتور شماره: " . $invoice->invoiceID . " تعداد " . $detail->product->entity . " ثبت شد"
            ]);
        }
        //Add Product to Store
        for ($i = 0; $i <= ($request->total_item - 1); $i++) {
            $product = Product::find($request['item_name'][$i]);
            $product->update([
                'entity' => $product->entity + $request['order_item_quantity'][$i]
            ]);
        }
        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');

        $user = \Auth::user();
        ActivityLogService::safeLog('create', 'یک فاکتور جدید ایجاد شد' . '-' . $invoice->invoiceID, null, ['section' => 'system', 'event_key' => 'system.create']);

        return back();
    }

    public function edit(Invoice $invoice)
    {
        $products = Product::where('isActive', 1)->get();
        $details = Detail::where('invoice_id', $invoice->id)->get();
        return view('invoices.edit', compact('invoice', 'products', 'details'));
    }

    public function show(Invoice $invoice)
    {
        $products = Product::where('isActive', 1)->get();
        $details = Detail::where('invoice_id', $invoice->id)->get();
        return view('invoices.show', compact('invoice', 'products', 'details'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request['buyDate'] = $this->to_english_numbers($request['buyDate']);
        $request['inputDate'] = $this->to_english_numbers($request['inputDate']);

        //////Field For Invoice
        $invoiceID   = $request->order_no;
        $user_id     = \Auth::user()->id;
        $shopName    = $request->order_receiver_name;
        $address     = $request->order_receiver_address;
        $phone       = $request->phone;
        $buyDate     = $request->buyDate;
        $inputDate   = $request->inputDate;
        $description = $request->description;

        $invoice->update([
            'shopName' => $shopName,
            'address' => $address,
            'phone' => $phone,
            'buyDate' => $buyDate,
            'inputDate' => $inputDate,
            'description' => $description,
        ]);

        //Upload File
        if ($request->file()) {
            $fileName = time() . '_' . $request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
            $invoice->update([
                'file' => '/storage/' . $filePath
            ]);
        }

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        $user = \Auth::user();
        ActivityLogService::safeLog('create', 'فاکتور ویرایش ایجاد شد' . '-' . $invoice->invoiceID, null, ['section' => 'system', 'event_key' => 'system.create']);

        return back();
    }

    public function destroy(Invoice $invoice)
    {
        $details = Detail::where('invoice_id', $invoice->id)->get();

        foreach ($details as $detail) {
            $detail->product->update([
                'entity' => $detail->product->entity + $detail->number
            ]);
        }

        $user = \Auth::user();
        ActivityLogService::safeLog('delete', 'فاکتور حذف شد' . '-' . $invoice->invoiceID, null, ['section' => 'system', 'event_key' => 'system.delete']);

        $invoice->delete();
        Alert::success('تشکر', 'رکورد با موفقیت حذف شد');

        return back();
    }

    //حذف یک ردیف از فاکتور
    public function deleteDetail(Detail $detail)
    {
        //شرط کمتر از صفر شدن موجودی
        if ($detail->product->entity - $detail->number < 0) {
            Alert::error('خطا', 'موجودی غیر مجاز(زیر صفر)');
            return back();
        } else {
            $detail->product->update([
                'entity' => $detail->product->entity - $detail->number
            ]);
            $detail->invoice->update([
                'price' => $detail->invoice->price - $detail->totalPrice
            ]);

            $user = \Auth::user();
            ActivityLogService::safeLog('delete', 'یک ردیف از فاکتور حذف شد' . '-' . $detail->product->title, null, ['section' => 'system', 'event_key' => 'system.delete']);


            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'decrement',
                'store' => $detail->product->store->title,
                'description' => " برای کالای " . $detail->product->title . " کاهش موجودی طی فاکتور شماره: " . $detail->invoice->invoiceID . " تعداد " . $detail->product->entity . " کاهش یافت"
            ]);

            $detail->delete();
            Alert::success('تشکر', 'رکورد با موفقیت حذف شد');
            return back();
        }
    }

    //ویرایش یک ردیف از فاکتور
    public function editDetail(Detail $detail)
    {
        //Stores
        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $products = Product::where('isActive', 1)->get();
        } else {
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
        }


        return view('invoices.edit-detail', compact('detail', 'products'));
    }

    //آپدیت یک ردیف از فاکتور
    public function updateDetail(Detail $detail, Request $request)
    {
        //شرط کمتر از صفر شدن موجودی
        $number = $detail->product->entity - $detail->number;
        if ($number < 0) {
            Alert::error('خطا', 'موجودی کالا به زیر صفر می رسد!');
            return back();
        } else {

            //در صورت تغییر محصول ردیف
            if ($detail->product_id != $request->product_id) {

                $number = $detail->product->entity - $detail->number;
                if ($number < 0) {
                    Alert::error('خطا', 'موجودی کالا به زیر صفر می رسد!');
                    return back();
                }

                $product = Product::findOrFail($detail->product_id);

                $product->update([
                    'entity' => $product->entity - $detail->number
                ]);

                $detail->update([
                    'product_id' => $request->product_id,
                ]);
                $product = Product::findOrFail($detail->product_id);

                $product->update([
                    'entity' => $request->number + $product->entity
                ]);
            } else {
                $detail->product->update([
                    'entity' => $number + $request->number
                ]);
            }
            $entity = $detail->number;

            //ویرایش فیلد های ردیف فاکتور
            $detail->update([
                'number' => $request->number,
                'price' => $request->price,
                'garanty' => $request->garanty,
                'totalPrice' => $request->totalPrice
            ]);

            $user = \Auth::user();
            ActivityLogService::safeLog('update', 'یک ردیف از فاکتور ویرایش شد' . '-' . $detail->product->title, null, ['section' => 'system', 'event_key' => 'system.update']);

            if ($entity != $detail->number) {
                History::create([
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_id' => $user->id,
                    'action' => 'editEntity',
                    'store' => $detail->product->store->title,
                    'description' => " کالای " . $detail->product->title . " از تعداد " . $entity . " به تعداد " . $detail->number . " تغییر یافت" . " - از طریق ویرایش فاکتور شماره: " . $detail->invoice->invoiceID
                ]);
            }

            Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
            return back();
        }
    }


    public function detailList()
    {
        $details = Detail::all();
        return view('invoices.details', compact('details'));
    }

    //Delete SubDetail in Invoice List
    public function deleteSubDetail($id)
    {
        $detail = Detail::find($id);
        $detail->product->update([
            'entity' => $detail->product->entity + $detail->number
        ]);
        $detail->delete();
        Alert::success('تشکر', 'یک قلم از فاکتور با موفقیت حذف شد');

        $user = \Auth::user();
        ActivityLogService::safeLog('delete', 'یک ردیف از فاکتور حذف شد' . '-' . $detail->product->title, null, ['section' => 'system', 'event_key' => 'system.delete']);

        History::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'decrement',
            'store' => $detail->product->store->title,
            'description' => " کالای " . $detail->product->title . " تعداد " . $detail->number . " کسر شد"
        ]);

        return back();
    }

    public function addDetailGet(Invoice $invoice)
    {
        //Stores
        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $products = Product::where('isActive', 1)->get();
        } else {
            foreach ($user->roles as $role) {
                $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            }
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
        }

        return view('invoices.create-detail', compact('invoice', 'products'));
    }

    public function addDetailPost(Invoice $invoice, Request $request)
    {

        $invoice->update([
            'price' => $invoice->price + $request['totalPrice']
        ]);

        //////Field For Details
        $detail = Detail::create([
            'invoice_id' => $invoice->id,
            'product_id' => $request['product_id'],
            'garanty' => $request['garanty'],
            'number' => $request['number'],
            'price' => $request['price'],
            'totalPrice' => $request['totalPrice'],
        ]);

        //Add Product to Store
        $product = Product::find($request['product_id']);
        $product->update([
            'entity' => $product->entity + $request['number']
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');

        $user = \Auth::user();
        ActivityLogService::safeLog('create', 'یک ردیف جدید ایجاد شد' . '-' . $detail->product->title, null, ['section' => 'system', 'event_key' => 'system.create']);

        History::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'increment',
            'store' => $product->store->title,
            'description' => " برای کالای " . $detail->product->title . " افزایش موجودی طی فاکتور شماره: " . $detail->invoice->invoiceID . " تعداد " . $detail->product->entity . " ثبت شد"
        ]);

        return back();
    }

    public function waiting_orders()
    {

        $user = \Auth::user();
        $Factors = Pishfactor::forOrganizations($user)->where('visitor_id', $user->id)->where('status', 0)->get();
        session()->put('backlink', route('waiting_orders'));
        return view('invoices.invoices-waiting-for-leader', compact('Factors'));
    }

    function to_english_numbers(String $string): String
    {
        $persinaDigits1 = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $persinaDigits2 = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
        $allPersianDigits = array_merge($persinaDigits1, $persinaDigits2);
        $replaces = [...range(0, 9), ...range(0, 9)];

        return str_replace($allPersianDigits, $replaces, $string);
    }

    public function add_factor_visitor(Request $request)
    {
        $user = \Auth::user();
        $isVisitor = false;
        $isAgent = false;

        foreach ($user->roles as $role) {
            if ($role->title == 'visitor') {
                $isVisitor = true;
            }
            if ($this->isAgentLikeRole($role)) {
                $isAgent = true;
            }
        }

        $customer_id = $request->customer;
        $visitor_id = $user->id;
        $sarparast_id = $user->leader_id;
        $Customer = $customer_id ? Customers::find($customer_id) : null;
        $Area = $Customer ? Area::find($Customer->area) : null;
        $Region = $Area ? Region::find($Area->region_id) : null;

        $limitService = app(\App\Services\OrderDiscountLimitService::class);
        $parsedLines = [];
        $orderTotalRials = 0;

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || (float) $value == 0) {
                continue;
            }

            if (str_starts_with($key, 'pack_')) {
                $pr_id = (int) str_replace('pack_', '', $key);
                $parsedLines[$pr_id]['pack'] = (int) $value;
            } elseif (str_starts_with($key, 'item_')) {
                $pr_id = (int) str_replace('item_', '', $key);
                $parsedLines[$pr_id]['tedad'] = (int) $value;
            } elseif (str_starts_with($key, 'price_')) {
                $pr_id = (int) str_replace('price_', '', $key);
                $parsedLines[$pr_id]['price'] = (int) str_replace(',', '', (string) $value);
            } elseif (str_starts_with($key, 'discount_')) {
                $pr_id = (int) str_replace('discount_', '', $key);
                $parsedLines[$pr_id]['discount'] = (float) $value;
            }
        }

        foreach ($parsedLines as $pr_id => $line) {
            $pack = (int) ($line['pack'] ?? 0);
            $tedad = (int) ($line['tedad'] ?? 0);

            if ($pack <= 0 && $tedad <= 0) {
                unset($parsedLines[$pr_id]);
                continue;
            }

            $Product = Product::find($pr_id);
            if (!$Product) {
                unset($parsedLines[$pr_id]);
                continue;
            }

            if ($Product->resolveOrderQuantityMode() === 'none' && ($pack > 0 || $tedad > 0)) {
                $fixedQty = $limitService->resolveSubmittedQuantities($Product, $pack, $tedad);
                $pack = $fixedQty['pack'];
                $tedad = $fixedQty['tedad'];
                $parsedLines[$pr_id]['pack'] = $pack;
                $parsedLines[$pr_id]['tedad'] = $tedad;
            }

            $unitPrice = (int) ($line['price'] ?? $Product->price);
            $discountPercent = (float) ($line['discount'] ?? 0);
            $lineGross = $limitService->lineGrossRials($Product, $pack, $tedad, $unitPrice);
            $discountAmount = (int) round(($lineGross * $discountPercent) / 100);
            $lineNet = $lineGross - $discountAmount;
            $taxAmount = (int) round(($lineNet * (int) $Product->tax) / 100);
            $orderTotalRials += $lineNet + $taxAmount;

            $validationError = $limitService->validateLine($user, $Product, $Customer, $discountPercent, $lineGross);
            if ($validationError) {
                Alert::warning('خطا در ثبت سفارش', $validationError);

                return back()->withInput();
            }
        }

        if (empty($parsedLines)) {
            Alert::warning('خطا در ثبت سفارش', 'برای ثبت فاکتور حداقل باید یک محصول را انتخاب نمایید');

            return back()->withInput();
        }

        $purchaseError = $limitService->validatePurchaseAmount($Customer, $orderTotalRials);
        if ($purchaseError) {
            Alert::warning('خطا در ثبت سفارش', $purchaseError);

            return back()->withInput();
        }

        $LastInvoice = DB::table('pishfactors')->orderBy('id', 'desc')->first();
        if ($LastInvoice) {
            $LastInvoice = $LastInvoice->invoiceID;
        } else {
            $LastInvoice = 1;
        }
        $CustomerOrganization = $Customer
            ? Organization::find($Customer->organization_id)
            : Organization::find($user->organization_id);
        $isAgencyOrder = $isAgent && !$Customer;

        $ownerId = ($isVisitor || $isAgent) && $sarparast_id ? $sarparast_id : $user->id;

        $Pishfactor = Pishfactor::create([
            'visitor_id' => $user->id,
            'customer_id' => $Customer ? intval($request->customer) : null,
            'is_agency_order' => $isAgencyOrder,
            'agency_user_id' => $isAgencyOrder ? $user->id : null,
            'sarparast_id' => $ownerId,
            'organization_id' => $Customer ? $Customer->organization_id : $user->organization_id,
            'tenants_id' => $CustomerOrganization ? $CustomerOrganization->tenants_id : $user->tenants_id,
            'task_id' => $request->task_id ? $request->task_id : null,
            'region_id' => $Region ? $Region->id : 0,
            'area_id' => $Customer ? $Customer->area : 0,
            'city_id' => $Region ? $Region->city_id : 0,
            'create_lat' => $request->visitor_lat,
            'create_lng' => $request->visitor_lng,
            ...app(SalesScenarioService::class)->initialInvoicePayload($user),
            'invoiceID' => $LastInvoice + 1
        ]);

        foreach ($parsedLines as $pr_id => $line) {
            $Product = Product::find($pr_id);
            PishFactorItems::create([
                'pishfactor_id' => $Pishfactor->id,
                'pr_id' => $pr_id,
                'pack' => (int) ($line['pack'] ?? 0),
                'tedad' => (int) ($line['tedad'] ?? 0),
                'price' => (int) ($line['price'] ?? $Product->price),
                'discount' => (float) ($line['discount'] ?? 0),
            ]);
        }

        $factorItems = PishFactorItems::where('pishfactor_id', $Pishfactor->id)->get();
        $allpacks = 0;
        $allitems = 0;
        $allitems_full = 0;
        $item_fees = 0;
        $all_item_fees = 0;
        $all_item_tax = 0;
        $all_discounts  = 0;
        $all_pats = 0;
        $factor_price = 0;

        foreach ($factorItems as $item) {
            $pr = DB::table('products')->where('id', $item->pr_id)->first();
            $allpacks += intval($item->pack);
            $allitems += intval($item->tedad);
            $items = intval($pr->pack_items) * intval($item->pack) + intval($item->tedad);
            $allitems_full += intval($items);
            $item_fees += intval($item->price);
            $fee_price = intval($items) * intval($item->price);
            $all_item_fees += $fee_price;
            $disprice = (intval($items) * intval($item->price)) * intval($item->discount) / 100;
            $pat = intval($fee_price) - intval($disprice);
            $all_discounts += $disprice;
            $all_pats += $pat;
            $taxprice = intval(($pat * $pr->tax) / 100);
            $all_item_tax += $taxprice;
            $fullp = intval($pat) + intval($taxprice);
            $factor_price += intval($fullp);
        }

        $Pishfactor->update([
            'fullPrice' => $factor_price,
            'pat_price' => $all_pats,
        ]);

        ActivityLogService::safeLogModel('create', "پیش فاکتور جدیدی توسط" . $user->name . " ایجاد شد", $Pishfactor, ['section' => 'invoice', 'event_key' => 'pishfactor.created']);

        app(\App\Services\CrmAdvancedAutomationService::class)->handleAfterInvoice($Pishfactor->fresh(), $user);

        return redirect(asset('/pishFactorInfo/' . $Pishfactor->id));
    }

    public function pishFactorInfo(Pishfactor $PishFactor)
    {

        $user = \Auth::user();

        $isManager = false;
        $isStore = false;
        $isVisitor = false;
        $isLeader = false;
        foreach ($user->roles as $role) {
            if ($role->title == 'visitor') {
                $isVisitor = true;
            }
            if ($role->title == 'leader') {
                $isLeader = true;
            }
            if ($role->title == 'expert') {
                $isManager = true;
            }
            if ($role->title == 'store') {
                $isStore = true;
            }
        }

        $factorItems = PishFactorItems::where('pishfactor_id', $PishFactor->id)->get();
        $allpacks = 0;
        $allitems = 0;
        $allitems_full = 0;
        $item_fees = 0;
        $all_item_fees = 0;
        $all_item_tax = 0;
        $all_discounts  = 0;
        $all_pats = 0;
        $factor_price = 0;

        foreach ($factorItems as $item) {
            $pr = DB::table('products')->where('id', $item->pr_id)->first();
            $allpacks += intval($item->pack);
            $allitems += intval($item->tedad);
            $items = intval($pr->pack_items) * intval($item->pack) + intval($item->tedad);
            $allitems_full += intval($items);
            $item_fees += intval($item->price);
            $fee_price = intval($items) * intval($item->price);
            $all_item_fees += $fee_price;
            $disprice = (intval($items) * intval($item->price)) * intval($item->discount) / 100;
            $pat = intval($fee_price) - intval($disprice);
            $all_discounts += $disprice;
            $all_pats += $pat;
            $taxprice = intval(($pat * $pr->tax) / 100);
            $all_item_tax += $taxprice;
            $fullp = intval($pat) + intval($taxprice);
            $factor_price += intval($fullp);
        }

        if ($PishFactor->fullPrice != $factor_price) {

            $PishFactor->update([
                'fullPrice' => $factor_price,
                'pat_price' => $all_pats,
            ]);
        }


        if ($user->isAdmin == 1 || $isStore || $isManager) {
            $nextItem = Pishfactor::forOrganizations($user)->where('id', '>', $PishFactor->id)->orderBy('id')->first();
        } else {
            $nextItem = null;
            if ($PishFactor->sarparast_id != $user->id && $PishFactor->visitor_id != $user->id) {
                return redirect(route('invoices.index'));
            }
        }

        $Terminals = PaymentTerminal::forOrganizations($user)->where('isActive', 1)->get();

        $Items = PishFactorItems::where('pishfactor_id', $PishFactor->id)->get();
        $Visitor = User::find($PishFactor->visitor_id);

        if (!$PishFactor->is_agency_order && $PishFactor->customer_id) {
            $Customer = Customers::find($PishFactor->customer_id);
            $Customer_Factors = Pishfactor::forOrganizations($user)->where('customer_id', $PishFactor->customer_id)->whereIn('status', [1, 4])->Count();
            $CustomerFactorsPriceCount = Pishfactor::forOrganizations($user)->where('customer_id', $PishFactor->customer_id)->whereIn('status', [1, 4])->sum('fullPrice');
            $MandeCustomer = Pishfactor::forOrganizations($user)->where('customer_id', $PishFactor->customer_id)
                ->whereIn('status', [1, 4])
                ->where(function ($query) {
                    $query->whereNull('payment_type')
                        ->orWhere('payment_type', 3);
                })
                ->sum('fullPrice');
        } else {
            $Customer = $this->buildAgentCustomerProxy($PishFactor->agencyUser ?: $Visitor);
            $PishFactor->setRelation('customer', $Customer);
            $Customer_Factors = Pishfactor::forOrganizations($user)->where('agency_user_id', $PishFactor->agency_user_id ?: $PishFactor->visitor_id)
                ->where('is_agency_order', 1)
                ->whereIn('status', [1, 4])
                ->count();
            $CustomerFactorsPriceCount = Pishfactor::forOrganizations($user)->where('agency_user_id', $PishFactor->agency_user_id ?: $PishFactor->visitor_id)
                ->where('is_agency_order', 1)
                ->whereIn('status', [1, 4])
                ->sum('fullPrice');
            $MandeCustomer = Pishfactor::forOrganizations($user)->where('agency_user_id', $PishFactor->agency_user_id ?: $PishFactor->visitor_id)
                ->where('is_agency_order', 1)
                ->whereIn('status', [1, 4])
                ->where(function ($query) {
                    $query->whereNull('payment_type')
                        ->orWhere('payment_type', 3);
                })
                ->sum('fullPrice');
        }

        $FreezFactor = false;
        foreach ($Items as $Item) {
            $Pr = Product::find($Item->pr_id);
            if ($Pr->isFreez == 1) {
                $FreezFactor = true;
            }
        }


        $Driver_per = DB::table('roles')->where('title', 'driver')->first();
        $Drivers_ids = DB::table('role_user')->where('role_id', $Driver_per->id)->pluck('user_id');
        $Drivers = User::whereIn('id', $Drivers_ids)->get();

        $Cards = Accounts::where('level', 3)->where('isActive', 1)->get();


        $routeName = Route::currentRouteName(); // گرفتن نام روت
        if ($routeName === 'pishFactorInfo') {
            return view('invoices.pishfactor', compact('PishFactor', 'Customer', 'Items', 'Visitor', 'isVisitor', 'FreezFactor', 'Drivers', 'isLeader', 'isStore', 'Customer_Factors', 'CustomerFactorsPriceCount', 'nextItem', 'MandeCustomer', 'Terminals', 'Cards'));
        } elseif ($routeName === 'pishFactorView') {
            return view('invoices.pishfactorView', compact('PishFactor', 'Customer', 'Items', 'Visitor', 'isVisitor', 'FreezFactor', 'Drivers', 'isLeader', 'isStore', 'Customer_Factors', 'CustomerFactorsPriceCount', 'nextItem', 'Terminals', 'Cards'));
        }
    }

    private function buildAgentCustomerProxy(?User $visitor): Customers
    {
        $customer = new Customers([
            'name' => $visitor ? $visitor->name : 'نماینده',
            'tablo' => 'نماینده',
            'phone' => $visitor ? $visitor->mobile : '---',
            'zipcode' => '---',
            'buyer_econimic_code' => '---',
            'buyer_registration_number' => '---',
            'address' => 'سفارش به نام خود کاربر ثبت شده است.',
            'mapcode' => '---',
            'area' => null,
        ]);

        $customer->setRelation('region', new Region(['name' => 'ندارد']));
        $customer->setRelation('Area', new Area(['name' => 'ندارد']));

        return $customer;
    }

    private function isAgentLikeRole($role): bool
    {
        return in_array($role->title, ['agent', 'reseller'], true)
            || trim((string) ($role->description ?? '')) === 'نماینده';
    }

    public function EditFactor(Pishfactor $PishFactor)
    {
        $user = \Auth::user();
        $Organ = Organization::find($user->organization_id);

        $isStore = false;
        $isManager = false;
        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            if ($role->title == 'store') {
                $isStore = true;
            }
            if ($role->title == 'expert') {
                $isManager = true;
            }
        }

        if ($user->isAdmin == 1 || $isManager) {
        } else {
            if ($PishFactor->sarparast_id != $user->id && $PishFactor->visitor_id != $user->id) {
                return redirect(route('invoices.index'));
            }
        }

        $Customer = Customers::find($PishFactor->customer_id);
        $Items = PishFactorItems::where('pishfactor_id', $PishFactor->id)->get();
        $Visitor = User::find($PishFactor->visitor_id);

        $Products = Product::forOrganizations($user)->get();

        return view('invoices.edit_pishfactor', compact('PishFactor', 'Customer', 'Items', 'Visitor', 'Products', 'Organ'));
    }
    public function UpdateFactorItems(Request $request, Pishfactor $PishFactor)
    {

        //dd($request->all());
        $user = \Auth::user();


        $DeleteAllItems = PishFactorItems::where('pishfactor_id', $PishFactor->id)->delete();

        if ($request->has('old_pr_name')) {

            // dd($request->all());
            $old_Products = $request->old_pr_name;
            $old_pack = $request->old_pack;
            $old_item = $request->old_item;
            $old_price = $request->old_price;
            $old_discount = $request->old_discount;
            $x = 0;
            foreach ($old_Products as $Product) {
                $Product = Product::find($Product);
                if ($Product->item_sale_status == 1) {
                    $item =  isset($old_item[$x]) ? $old_item[$x] : 0;
                } else {
                    $item = 0;
                }
                PishFactorItems::create([
                    'pishfactor_id' => $PishFactor->id,
                    'pr_id' => $Product->id,
                    'pack' => isset($old_pack[$x]) ? $old_pack[$x] : 0,
                    'tedad' => $item,
                    'price' => isset($old_price[$x]) ? $old_price[$x] : $Product->price,
                    'discount' => $old_discount[$x]
                ]);
                $x++;
            }
        }


        if ($request->has('pr_name')) {

            $Products = $request->pr_name;
            $newpack = $request->newpack;
            $newitem = $request->newitem;
            $newdis = $request->newdis;
            $newprice = $request->price;
            $x = 0;
            foreach ($Products as $Product) {
                $Product = Product::find($Product);
                if ($Product->item_sale_status == 1) {
                    $item =  isset($newitem[$x]) ? $newitem[$x] : 0;
                } else {
                    $item = 0;
                }
                PishFactorItems::create([
                    'pishfactor_id' => $PishFactor->id,
                    'pr_id' => $Product->id,
                    'pack' => isset($newpack[$x]) ? $newpack[$x] : 0,
                    'tedad' => $item,
                    'price' => isset($newprice[$x]) ? $newprice[$x] : $Product->price,
                    'discount' => $newdis[$x]
                ]);
                $x++;
            }
        }

        $factorItems = PishFactorItems::where('pishfactor_id', $PishFactor->id)->get();
        $allpacks = 0;
        $allitems = 0;
        $allitems_full = 0;
        $item_fees = 0;
        $all_item_fees = 0;
        $all_item_tax = 0;
        $all_discounts  = 0;
        $all_pats = 0;
        $factor_price = 0;

        foreach ($factorItems as $item) {
            $pr = DB::table('products')->where('id', $item->pr_id)->first();
            $allpacks += intval($item->pack);
            $allitems += intval($item->tedad);
            $items = intval($pr->pack_items) * intval($item->pack) + intval($item->tedad);
            $allitems_full += intval($items);
            $item_fees += intval($item->price);
            $fee_price = intval($items) * intval($item->price);
            $all_item_fees += $fee_price;
            $disprice = (intval($items) * intval($item->price)) * intval($item->discount) / 100;
            $pat = intval($fee_price) - intval($disprice);
            $all_discounts += $disprice;
            $all_pats += $pat;
            $taxprice = intval(($pat * $pr->tax) / 100);
            $all_item_tax += $taxprice;
            $fullp = intval($pat) + intval($taxprice);
            $factor_price += intval($fullp);
        }


        $PishFactor->update([
            'fullPrice' => $factor_price,
            'pat_price' => $all_pats,
        ]);

        ActivityLogService::safeLogModel('update', "به روزرسانی لیست اجناس فاکتور توسط $user->name", $PishFactor, ['section' => 'invoice', 'event_key' => 'pishfactor.items_updated']);
        Alert::success('تشکر', 'لیست اجناس با موفقیت بروزرسانی شد');
        return redirect(asset('/pishFactorInfo/' . $PishFactor->id));
    }
    public function pishFactorUpdate(Request $request, Pishfactor $PishFactor)
    {

        // dd($request->all());
        $paymentContext = [];

        foreach ($_POST as $key => $value) {

            if (strpos($key, 'disitem_') === 0) {
                $item_id = str_replace("disitem_", '', $key);
                $val = $_POST[$key];
                $Item = PishFactorItems::find($item_id);
                $Item->discount = $val;
                $Item->save();
            }
        }

        if ($request->has('recive_date') && $request->recive_date != '') {
            $arrayDate = explode(" ", $request->recive_date);
            $fromFaHustDate = $arrayDate[0];
            $jalali = explode("/", $fromFaHustDate);
            $miladi = Verta::jalaliToGregorian($jalali[0], $jalali[1], $jalali[2]);
            $ym = $miladi[0];
            if (strlen($miladi[1]) == 1) {
                $mm = "0" . $miladi[1];
            } else {
                $mm = $miladi[1];
            };
            if (strlen($miladi[2]) == 1) {
                $dm = "0" . $miladi[2];
            } else {
                $dm = $miladi[2];
            };
            $recive_date_en = "$ym-$mm-$dm 00:00:00";

            $PishFactor->update([
                'recive_date' => $request->recive_date,
                'recive_date_en' => $recive_date_en,
            ]);
        } else {
            $recive_date_en = null;
        }

        if ($request->tozihat != '' && $request->tozihat != $PishFactor->tozihat) {
            $PishFactor->update([
                'tozihat' => $request->tozihat,
            ]);
        }


        if ($request->has('payment_type')) {
            $PishFactor->update([
                'fullPrice' => str_replace(',', '', $request->fullPrice),
                'pat_price' => str_replace(',', '', $request->pat_price),
                'payment_type' => $request->payment_type,
            ]);

            if ($request->payment_type == 4) {
                $terminalId = $request->getway;
            } else {
                $terminalId = null;
            }

            if ($request->hasFile('cheque_photo')) {

                $image = $request->file('cheque_photo');
                $imageName = time() . '.' . $image->getClientOriginalExtension() ?? 'jpg';
                //$image->move($_SERVER['DOCUMENT_ROOT'].'/tcn/avatars', $imageName);
                $image->move($_SERVER['DOCUMENT_ROOT'] . '/receipts', $imageName);
            } else {
                $imageName = null;
            }

            $paymentContext = [
                'payment_type' => $request->payment_type,
                'cashmoney_amount' => $request->cashmoney_amount,
                'chek_amount' => $request->chek_amount,
                'kartbekart_amount' => $request->kartbekart_amount,
                'getway_amount' => $request->getway_amount,
                'terminal_id' => $terminalId,
                'cheque_photo' => $imageName,
            ];
        }


        if ($request->has('status')) {
            $PishFactor->update([
                'status' => intval($request->status),
                'updated_by' => auth()->user()->id
            ]);
        }

        if ($request->has('sabtdatedate')) {
            $Time = explode(' ', $request->get('sabtdate'));
            $Jalali = explode('/', $Time[0]);
            $Miladi = Verta::jalaliToGregorian($Jalali[0], $Jalali[1], $Jalali[2]);
            $PishFactor->update([
                'created_at' => $Miladi[0] . '-' . $Miladi[1] . '-' . $Miladi[2] . ' ' . $Time[1],
            ]);
        }
        if ($request->has('step')) {
            $PishFactor->update([
                'step' => intval($request->step)
            ]);
        }

        if ($request->has('driver_id')) {
            $PishFactor->update([
                'driver_id' => intval($request->driver_id)
            ]);
        }

        $user = \Auth::user();
        app(InventoryLedgerService::class)->replacePishfactorMovements($PishFactor->fresh(['items.product']), $user->id);
        app(AccountingPostingService::class)->postPishfactorSaleVoucher($PishFactor->fresh(), $paymentContext, $user);
        app(AccountingPostingService::class)->postPishfactorCostOfGoodsVoucher($PishFactor->fresh(), $user);

        ActivityLogService::safeLogModel('update', "به روزرسانی فاکتور توسط $user->name", $PishFactor, ['section' => 'invoice', 'event_key' => 'pishfactor.updated']);

        Alert::success('تشکر', 'فاکتور با موفقیت بروزرسانی شد');
        return redirect(asset('/pishFactorInfo/' . $PishFactor->id));
    }

    public function DeleteFactor(Pishfactor $PishFactor)
    {
        app(InventoryLedgerService::class)->removePishfactorMovements($PishFactor);
        app(AccountingPostingService::class)->removePishfactorCostOfGoodsVoucher($PishFactor);

        $details = PishFactorItems::where('pishfactor_id', $PishFactor->id)->delete();



        $user = \Auth::user();
        ActivityLogService::safeLog('delete', 'فاکتور حذف شد' . '-' . $PishFactor->invoiceID, null, ['section' => 'system', 'event_key' => 'system.delete']);

        $PishFactor = Pishfactor::forOrganizations($user)->where('id', $PishFactor->id)->delete();

        Alert::success('تشکر', 'فاکتور با موفقیت حذف شد');

        return redirect()->back();
    }

    public function history_orders()
    {
        $user = \Auth::user();
        $PishFactors = Pishfactor::forOrganizations($user)->where('visitor_id', $user->id)->orderBy('id', 'desc')->get();
        session()->put('backlink', route('history_orders'));
        return view('invoices.my-history', compact('PishFactors'));
    }

    public function actions(Request $request)
    {
        //dd($request->all());
        $user = auth()->user();
        $ledger = app(InventoryLedgerService::class);
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'item_')) {
                $factor_id = str_replace("item_", "", $key);
                $Pishfactor = Pishfactor::find($factor_id);
                if ($request->has('accept')) {
                    $Pishfactor->status = 1;
                    $Pishfactor->updated_by = $user->id;
                }
                if ($request->has('dencil')) {
                    $Pishfactor->status = 3;
                    $Pishfactor->updated_by = $user->id;
                }
                if ($request->has('waiting')) {
                    $Pishfactor->status = 0;
                    $Pishfactor->updated_by = $user->id;
                }
                if ($request->has('assign_to_store')) {
                    $Pishfactor->status = 1;
                    $Pishfactor->step = 2;
                    $Pishfactor->updated_by = $user->id;
                }
                $Pishfactor->update();
                $ledger->replacePishfactorMovements($Pishfactor->fresh(['items.product']), $user->id);
                app(AccountingPostingService::class)->postPishfactorSaleVoucher($Pishfactor->fresh(), [], $user);
                app(AccountingPostingService::class)->postPishfactorCostOfGoodsVoucher($Pishfactor->fresh(), $user);
            }
        }
        Alert::success('تایید عملیات', 'عملیات دسته جمعی با موقیت انجام شد.');
        return redirect()->back();
    }

    protected $digit1 = array(
        0 => 'صفر',
        1 => 'یک',
        2 => 'دو',
        3 => 'سه',
        4 => 'چهار',
        5 => 'پنج',
        6 => 'شش',
        7 => 'هفت',
        8 => 'هشت',
        9 => 'نه',
    );
    protected $digit1_5 = array(
        1 => 'یازده',
        2 => 'دوازده',
        3 => 'سیزده',
        4 => 'چهارده',
        5 => 'پانزده',
        6 => 'شانزده',
        7 => 'هفده',
        8 => 'هجده',
        9 => 'نوزده',
    );
    protected $digit2 = array(
        1 => 'ده',
        2 => 'بیست',
        3 => 'سی',
        4 => 'چهل',
        5 => 'پنجاه',
        6 => 'شصت',
        7 => 'هفتاد',
        8 => 'هشتاد',
        9 => 'نود'
    );
    protected $digit3 = array(
        1 => 'صد',
        2 => 'دویست',
        3 => 'سیصد',
        4 => 'چهارصد',
        5 => 'پانصد',
        6 => 'ششصد',
        7 => 'هفتصد',
        8 => 'هشتصد',
        9 => 'نهصد',
    );
    protected $steps = array(
        1 => 'هزار',
        2 => 'میلیون',
        3 => 'بیلیون',
        4 => 'تریلیون',
        5 => 'کادریلیون',
        6 => 'کوینتریلیون',
        7 => 'سکستریلیون',
        8 => 'سپتریلیون',
        9 => 'اکتریلیون',
        10 => 'نونیلیون',
        11 => 'دسیلیون',
    );
    protected $t = array(
        'and' => 'و',
    );

    function number_format($number, $decimal_precision = 0, $decimals_separator = '.', $thousands_separator = ',')
    {
        $number = explode('.', str_replace(' ', '', $number));
        $number[0] = str_split(strrev($number[0]), 3);
        $total_segments = count($number[0]);
        for ($i = 0; $i < $total_segments; $i++) {
            $number[0][$i] = strrev($number[0][$i]);
        }
        $number[0] = implode($thousands_separator, array_reverse($number[0]));
        if (!empty($number[1])) {
            $number[1] = round($number[1], $decimal_precision);
        }
        return implode($decimals_separator, $number);
    }

    protected static function groupToWords($group)
    {
        $thiss = new InvoiceController();
        $d3 = floor($group / 100);
        $d2 = floor(($group - $d3 * 100) / 10);
        $d1 = $group - $d3 * 100 - $d2 * 10;

        $group_array = array();

        if ($d3 != 0) {
            $group_array[] = $thiss->digit3[$d3];
        }

        if ($d2 == 1 && $d1 != 0) { // 11-19
            $group_array[] = $thiss->digit1_5[$d1];
        } else if ($d2 != 0 && $d1 == 0) { // 10-20-...-90
            $group_array[] = $thiss->digit2[$d2];
        } else if ($d2 == 0 && $d1 == 0) { // 00
        } else if ($d2 == 0 && $d1 != 0) { // 1-9
            $group_array[] = $thiss->digit1[$d1];
        } else { // Others
            $group_array[] = $thiss->digit2[$d2];
            $group_array[] = $thiss->digit1[$d1];
        }

        if (!count($group_array)) {
            return FALSE;
        }

        return $group_array;
    }

    public static function numberToWords($number)
    {
        $foobar = new InvoiceController();
        $formated = $foobar->number_format($number, 0, '.', ',');
        $groups = explode(',', $formated);

        $steps = count($groups);

        $parts = array();
        foreach ($groups as $step => $group) {
            $group_words = self::groupToWords($group);
            if ($group_words) {
                $part = implode(' ' . $foobar->t['and'] . ' ', $group_words);
                if (isset($foobar->steps[$steps - $step - 1])) {
                    $part .= ' ' . $foobar->steps[$steps - $step - 1];
                }
                $parts[] = $part;
            }
        }
        return implode(' ' . $foobar->t['and'] . ' ', $parts);
    }
}
