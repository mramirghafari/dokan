<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Delivery;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\History;
use App\Models\Log;
use App\Models\City;
use App\Models\Organization;
use App\Models\Shipments;
use App\Models\ShipmentRoute;
use App\Models\Transportation;
use App\Models\Role;
use App\Models\Stock;
use App\Models\Store;
use App\Services\TenantSettings;
use Auth;
use Illuminate\Support\Facades\DB;
use Mollie\Api\Resources\Shipment;
use RealRashid\SweetAlert\Facades\Alert;
use Hekmatinasser\Verta\Verta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use Symfony\Component\Mailer\Transport\Transports;


class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:store-delivery,user')->only(['index', 'store', 'edit', 'update', 'destroy']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_distribution')) {
                Alert::warning('غیرفعال', 'پخش و باربری برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            if (!TenantSettings::enabled('feature_warehouse_management')) {
                Alert::warning('غیرفعال', 'انبار و تامین برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only([
            'index',
            'active_list',
            'compeleted',
            'Outgoing',
            'preOrderOutput',
            'Outgoing_by_items',
            'dayOrders',
            'addShipment',
            'storeShipments',
            'shipments',
            'EditShipment',
            'myShipment',
            'shipmentRoute',
            'assigned_to_drivers',
        ]);
    }

    public function index()
    {

        /*
        $user = \Auth::user();
        $roles = Role::all();

        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
        }

        if ($user->isAdmin == 1) {
            $products = Product::latest()->get();
            $stocks = Stock::latest()->get();
            $deliveries = Delivery::latest()->get();
            $employees = Employee::where('isActive', 1)->get();
        } else {
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
            $stocks = Stock::whereIn('store_id', $storesUser)->latest()->get();
            $deliveries = Delivery::where('organization_id', $user->organization_id)->latest()->get();
            $employees = Employee::where('organization_id', $user->organization_id)->latest()->get();
        }
        $organizations = Organization::all();


        return view('deliveries.index', compact('products', 'employees', 'deliveries', 'stocks', 'organizations')); */

        $user = \Auth::user();
        // $invoices = Invoice::latest()->get();
        // $subDetails = Detail::all();
        $PishFactors = Pishfactor::whereIn('status', [1, 2])->whereIn('step', [1, 2])->forOrganizations($user)->orderBy('id', 'desc')->get();

        return view('deliveries.AmadeTahvil', compact('PishFactors'));
    }

    public function AmadeTahvilFilter(Request $request)
    {


        $user = \Auth::user();
        $day = verta()->format('Y/m/d');

        if ($request->has('from_date') && $request->has('to_date') && $request->from_date != null && $request->to_date != null) {

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

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);


            $PishFactors =  Pishfactor::whereIn('status', [1, 2])->whereIn('step', [1, 2])
                ->forOrganizations($user)
                ->whereBetween('created_at', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"])
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($request->has('delivery_from_date') && $request->has('delivery_to_date') && $request->delivery_from_date != null && $request->delivery_to_date != null) {

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

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);


            $PishFactors =  Pishfactor::whereIn('status', [1, 2])->whereIn('step', [1, 2])
                ->forOrganizations($user)
                ->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $PishFactors = Pishfactor::whereIn('status', [1, 2])->whereIn('step', [1, 2])
                ->forOrganizations($user)
                ->orderBy('id', 'desc')
                ->get();

            $fromDate = null;
            $toDate = null;
        }

        return view('deliveries.AmadeTahvil', compact('PishFactors'));
    }

    public function compeleted()
    {

        $user = \Auth::user();
        $PishFactors = Pishfactor::forOrganizations($user)->where('status', 1)->where('step', 4)->orderBy('id', 'desc')->get();
        $Cities = City::forOrganizations($user)->get();
        $isManager = false;
        $isLeader = false;
        $isVisitor = false;
        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
            if ($role->title == 'visitor') {
                $isVisitor = true;
            } else {
                $isVisitor = false;
            }
            if ($role->title == 'expert') {
                $isManager = true;
            } else {
                $isManager = false;
            }
            if ($role->title == 'leader') {
                $isLeader = true;
            } else {
                $isLeader = false;
            }
        }

        return view('invoices.PishFactors', compact('PishFactors', 'Cities', 'isManager', 'isLeader', 'isVisitor'));
    }

    public function Outgoing(Request $request)
    {
        $user = \Auth::user();
        $day = verta()->format('Y/m/d');

        if ($request->has('from_date') && $request->has('to_date') && $request->from_date != null && $request->to_date != null) {

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

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);


            $PishFactors =  Pishfactor::where('status', 1)
                ->forOrganizations($user)
                ->where('step', 2)
                ->whereBetween('created_at', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"])
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($request->has('delivery_from_date') && $request->has('delivery_to_date') && $request->delivery_from_date != null && $request->delivery_to_date != null) {

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

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);


            $PishFactors =  Pishfactor::where('status', 1)
                ->where('step', 2)
                ->forOrganizations($user)
                ->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $PishFactors = Pishfactor::where('status', 1)
                ->where('step', 2)
                ->forOrganizations($user)
                ->orderBy('id', 'desc')
                ->get();

            $fromDate = null;
            $toDate = null;
        }

        // استخراج محصولات و محاسبه مجموع tedad و pack
        $Items = $PishFactors->flatMap(function ($pishfactor) {
            return $pishfactor->items->map(function ($item) {
                return [
                    'product_id' => $item->pr_id, // شناسه محصول
                    'tedad' => $item->tedad,     // تعداد
                    'pack' => $item->pack        // تعداد بسته‌بندی
                ];
            });
        })->groupBy('product_id')->map(function ($group) {
            return [
                'total_tedad' => $group->sum('tedad'),
                'total_pack' => $group->sum('pack')
            ];
        });


        return view('invoices.Havale', compact('Items', 'fromDate', 'toDate'));
    }

    public function preOrderOutput(Request $request)
    {
        $user = \Auth::user();
        $day = verta()->format('Y/m/d');

        if ($request->has('from_date') && $request->has('to_date') && $request->from_date != null && $request->to_date != null) {

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

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);


            $PishFactors =  Pishfactor::where('status', 1)
                ->forOrganizations($user)
                ->whereIn('step', [0, null])
                ->whereBetween('created_at', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"])
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($request->has('delivery_from_date') && $request->has('delivery_to_date') && $request->delivery_from_date != null && $request->delivery_to_date != null) {

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

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);


            $PishFactors =  Pishfactor::where('status', 1)
                ->whereIn('step', [0, null])
                ->forOrganizations($user)
                ->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $PishFactors = Pishfactor::where('status', 1)
                ->whereIn('step', [0, null])
                ->forOrganizations($user)
                ->orderBy('id', 'desc')
                ->get();

            $fromDate = null;
            $toDate = null;
        }

        // استخراج محصولات و محاسبه مجموع tedad و pack
        $Items = $PishFactors->flatMap(function ($pishfactor) {
            return $pishfactor->items->map(function ($item) {
                return [
                    'product_id' => $item->pr_id, // شناسه محصول
                    'tedad' => $item->tedad,     // تعداد
                    'pack' => $item->pack        // تعداد بسته‌بندی
                ];
            });
        })->groupBy('product_id')->map(function ($group) {
            return [
                'total_tedad' => $group->sum('tedad'),
                'total_pack' => $group->sum('pack')
            ];
        });


        return view('invoices.preOrderOutput', compact('Items', 'fromDate', 'toDate'));
    }

    public function Outgoing_by_items(Request $request)
    {

        $user = \Auth::user();

        $driverRoleId = DB::table('roles')->where('title', 'driver')->value('id');

        if ($user->isGod) {
            $Cities = City::all();
            $Drivers = User::whereIn('id', function ($q) use ($driverRoleId) {
                $q->select('user_id')->from('role_user')->where('role_id', $driverRoleId);
            })->get();
        } else {
            $Cities = City::forOrganizations($user)->get();
            $Drivers = User::whereIn('id', function ($q) use ($driverRoleId) {
                $q->select('user_id')->from('role_user')->where('role_id', $driverRoleId);
            })->forOrganizations($user)->get();
        }

        $Factors = Pishfactor::with(['items.product', 'customer'])
            ->where('status', 1)
            ->where('step', 2)
            ->forOrganizations($user)
            ->when($request->city_id, fn($q) => $q->whereIn('city_id', (array)$request->city_id))
            ->when($request->from_date, function ($q) use ($request) {
                [$from, $to] = $this->convertJalaliRange($request->from_date, $request->to_date);
                $q->whereBetween('created_at', [$from, $to]);
            })
            ->when($request->delivery_from_date, function ($q) use ($request) {
                [$from, $to] = $this->convertJalaliRange($request->delivery_from_date, $request->delivery_to_date);
                $q->whereBetween('recive_date_en', [$from, $to]);
            })
            ->orderByDesc('recive_date_en')
            ->get();

        // Unique Items
        $uniqueItems = DB::table('pish_factor_items as pi')
            ->join('products as p', 'pi.pr_id', '=', 'p.id')
            ->join('pishfactors as pf', 'pf.id', '=', 'pi.pishfactor_id')
            ->whereIn('pf.id', $Factors->pluck('id'))
            ->select(
                'pi.pr_id',
                DB::raw("CASE WHEN p.display_name IS NULL OR p.display_name = '' THEN p.title ELSE p.display_name END AS product_name"),
                'p.sku',
                'p.pr_unit',
                'p.pr_sub_unit',
                'p.item_sale_status',
                'p.pack_sale_status'
            )
            ->distinct()
            ->get();

        // Preload all items into an array to avoid queries in view
        $factorItems = \App\Models\PishFactorItems::whereIn('pishfactor_id', $Factors->pluck('id'))
            ->get()
            ->groupBy(function ($item) {
                return $item->pishfactor_id . '_' . $item->pr_id;
            });


        session()->put('backlink', route('deliveries.Outgoing_by_items'));
        return view('invoices.Havale_By_Items', compact(
            'Factors',
            'uniqueItems',
            'Cities',
            'Drivers',
            'factorItems'
        ));
    }


    public function dayOrders(Request $request)
    {
        $user = \Auth::user();
        $day = verta()->format('Y/m/d');


        $PishFactors = Pishfactor::where('status', 1)
            ->where('step', 2)
            ->forOrganizations($user)
            ->orderBy('id', 'desc')
            ->whereDate('created_at', Carbon::today()) // فقط امروز
            ->get();

        $fromDate = null;
        $toDate = null;

        // استخراج محصولات و محاسبه مجموع tedad و pack
        $Items = $PishFactors->flatMap(function ($pishfactor) {
            return $pishfactor->items->map(function ($item) {
                return [
                    'product_id' => $item->pr_id, // شناسه محصول
                    'tedad' => $item->tedad,     // تعداد
                    'pack' => $item->pack        // تعداد بسته‌بندی
                ];
            });
        })->groupBy('product_id')->map(function ($group) {
            return [
                'total_tedad' => $group->sum('tedad'),
                'total_pack' => $group->sum('pack')
            ];
        });


        return view('invoices.dayOrders', compact('Items', 'fromDate', 'toDate'));
    }

    private function convertJalaliRange($from, $to)
    {
        $fromDateParts = explode("-", str_replace("/", "-", $from));
        $toDateParts = explode("-", str_replace("/", "-", $to));
        $fromMiladi = Verta::jalaliToGregorian(...$fromDateParts);
        $toMiladi = Verta::jalaliToGregorian(...$toDateParts);
        return [
            sprintf("%04d-%02d-%02d 00:00:00", ...$fromMiladi),
            sprintf("%04d-%02d-%02d 23:59:59", ...$toMiladi)
        ];
    }




    public function addShipment()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
        } else {
            $Stores = Store::forOrganizations($user)->get();
        }


        $DriverRole = DB::table('roles')->where('title', 'driver')->first();
        $DriversIds = DB::table('role_user')->where('role_id', $DriverRole->id)->pluck('user_id')->toArray();
        $Drivers = User::whereIn('id', $DriversIds)->forOrganizations($user)->get();

        $UsedFactorIds = ShipmentRoute::whereNotNull('factor_id')
            ->where('factor_id', '!=', 'start')
            ->where('factor_id', '!=', 'end')
            ->pluck('factor_id')
            ->toArray();

        $PishFactors = Pishfactor::where('status', 1)
            ->where('step', 2)
            ->forOrganizations($user)
            ->whereNotIn('id', $UsedFactorIds)
            ->get();

        return view('deliveries.shipments', compact('Drivers', 'Stores', 'PishFactors'));
    }

    public function storeShipments(Request $request)
    {

        //  dd($request->all());
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
        } else {
            $Stores = Store::forOrganizations($user)->get();
        }


        $DriverRole = DB::table('roles')->where('title', 'driver')->first();
        $DriversIds = DB::table('role_user')->where('role_id', $DriverRole->id)->pluck('user_id')->toArray();
        $Drivers = User::whereIn('id', $DriversIds)->forOrganizations($user)->get();
        $PishFactors = Pishfactor::where('status', 1)
            ->where('step', 2)
            ->forOrganizations($user)->get();


        $Store = Store::find($request->store_id);


        $ShipmentId = $request->number;
        $date = $request->date;
        $hours = $request->hours;
        $tozihat = $request->tozihat;

        // گرفتن ناوگان رانندگان
        $vehicles = User::whereIn('id', $request->driver_ids)->with('cargo')->get();

        // تعریف آرایه اولیه رانندگان
        $vehicleAssignments = [];
        foreach ($vehicles as $vehicle) {
            $vehicleAssignments[$vehicle->id] = [
                'capacity' => (int)($vehicle->cargo->cartons ?? 0),
                'used'     => 0,
                'jobs'     => [],
                'start_lat' => $Store->lat,
                'start_lng' => $Store->lang,
            ];
        }

        $UsedFactorIds = ShipmentRoute::whereNotNull('factor_id')
            ->where('factor_id', '!=', 'start')
            ->where('factor_id', '!=', 'end')
            ->pluck('factor_id')
            ->toArray();

        $Factors = Pishfactor::where('status', 1)
            ->where('step', 2)
            ->where('recive_date', $request->date)
            ->forOrganizations($user)
            ->whereNotIn('id', $UsedFactorIds)
            ->get();

        $unassignedFactors = [];

        foreach ($Factors as $factor) {

            // مقادیر از جدول فرزند استخراج
            $packs  = (int) PishFactorItems::where('pishfactor_id', $factor->id)->sum('pack');
            $tedad  = (int) PishFactorItems::where('pishfactor_id', $factor->id)->sum('tedad');

            // مختصات مقصد از ریلیشن customer
            $lat = optional($factor->customer)->shop_lat;
            $lng = optional($factor->customer)->shop_lng;

            if (!$lat || !$lng) {
                $unassignedFactors[] = [
                    'id' => $factor->id,
                    'packs' => $packs,
                    'items' => $tedad,
                    'message' => 'مختصات فروشگاه مشتری یافت نشد',
                ];
                continue;
            }

            // تخصیص فاکتور به راننده با ظرفیت کافی
            $assigned = false;
            foreach ($vehicleAssignments as $vehicleId => &$vehicle) {
                if (($vehicle['used'] + $packs) <= $vehicle['capacity'] * 10) {

                    // 👇 ساخت کامل job برای API نشان
                    $vehicle['jobs'][] = [
                        'id' => $factor->id,
                        'location' => [
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ],
                        'delivery' => $packs,
                        'pickup' => 0,
                        'priority' => 1,
                        'skills' => [1],
                        'setUpTime' => 0,
                        'serviceTime' => 0,
                        'timeWindows' => [
                            [
                                'from' => '09:00:00',
                                'to' => '16:00:00',
                            ],
                        ],
                    ];

                    $vehicle['used'] += $packs;
                    $assigned = true;
                    break;
                }
            }

            if (!$assigned) {
                $unassignedFactors[] = [
                    'id' => $factor->id,
                    'packs' => $packs,
                    'items' => $tedad,
                ];
            }
        }
        unset($vehicle); // رفع اشاره foreach



        // ✅ ساخت payload نهایی برای نشان
        $payload = [
            'vehicles' => [],
            'jobs' => [],
        ];

        foreach ($vehicleAssignments as $vehicleId => $vehicle) {
            $payload['vehicles'][] = [
                'id' => $vehicleId,
                'start' => [
                    'latitude' => $vehicle['start_lat'],
                    'longitude' => $vehicle['start_lng'],
                ],
                'end' => [
                    'latitude' => $vehicle['start_lat'], // در صورت نداشتن مقصد جدا می‌تونن همون مبدا باشن
                    'longitude' => $vehicle['start_lng'],
                ],
                'capacity' => $vehicle['capacity'],
                'skills' => [1],
                'timeWindow' => [
                    'from' => '09:00:00',
                    'to' => '16:00:00',
                ],
            ];

            foreach ($vehicle['jobs'] as $job) {
                $payload['jobs'][] = $job;
            }
        }



        // ✅ ارسال به API نشان
        $response = Http::withHeaders([
            'api-key' => 'service.e0404239fbd245a3aa943ad68eb3feda',
            'Content-Type' => 'application/json',
        ])->asJson()->post('https://api.neshan.org/v5/logistic', $payload);

        $Result = json_decode($response->body());

        dd($Result);



        $neshanRoutes = $Result->routes;
        $routes = [];

        foreach ($neshanRoutes as $trip) {

            // مراحل سفر (استارت، جاب‌ها، اند)
            $points = collect($trip->steps ?? [])
                ->map(function ($step) {
                    if ($step->type == 'job') {
                        $Pishfactor = Pishfactor::find($step->id);
                        $Customer = $Pishfactor->customer->name;
                    }

                    return [
                        'type' => $step->type,
                        'id' => $step->type == 'job' ? $step->id : null,
                        'lat'  => $step->location->latitude,
                        'lng'  => $step->location->longitude,
                        'load' => $step->load[0] ?? 0,
                    ];
                })
                ->values()
                ->toArray();

            $Driver = User::find($trip->vehicleId);
            $routes[] = [
                'vehicleId' => $trip->vehicleId,
                'driver' => $Driver->name,
                'points'    => $points,
                // 'geometry'  => $trip->geometry ?? null, // رشته‌ی مسیر یا polyline
                'distance'  => $trip->distance ?? null,
                'duration'  => $trip->duration ?? null,
                'cost'      => $trip->cost ?? null,
            ];

            $shipment = Shipments::create([
                'organization_id' => $Store->organization_id,
                'owner_id'        => auth()->id(),
                'driver_id'       => $trip->vehicleId,           // از متغیر یا ریکوئستت
                'number'          => 'D-' . time(),
                'tozihat'         => null,                 // توضیح اختیاری
                'date_fa'         => $request->date,
                'date_en'         => now()->format('Y-m-d'),
                'hours'           => $request->hours,
                'mabda'           => $Store->id,        // یا اسم مبدا دلخواه
                'origin_lat'      => $Store->lat,          // از متغیر خودت
                'origin_lang'     => $Store->lang,          // از متغیر خودت
                'status'          => 0                     // وضعیت اولیه
            ]);
        }

        foreach ($routes as $Nshipment) {

            // 📦 شیپمنت مرتبط با این راننده
            $shipment = Shipments::where('driver_id', $Nshipment['vehicleId'])
                ->latest()->first();

            if (!$shipment) {
                continue;
            }

            // ⛓ نقاط (start/job/end)
            $Shipment_points = $Nshipment['points'];

            // 🔹 ساخت مسیرها بین هر دو نقطه‌ی متوالی
            for ($i = 0; $i < count($Shipment_points) - 1; $i++) {

                $origin = $Shipment_points[$i];
                $destination = $Shipment_points[$i + 1];
                // اگر آیتم بعدی وجود نداره یعنی آخر سفره
                $isLast = ($i === count($points) - 1);

                ShipmentRoute::create([
                    'shipment_id'     => $shipment->id,
                    'factor_id'       => $origin['type'] == 'job'
                        ? $origin['id']
                        : ($origin['type'] == 'start'
                            ? 'start'
                            : ($isLast ? 'end' : null)),
                    'route_index'     => $i + 1,
                    'origin_lat'      => $origin['lat'],
                    'origin_lng'      => $origin['lng'],
                    'destination_lat' => $destination['lat'],
                    'destination_lng' => $destination['lng'],
                    'status'          => 0,
                    'extra_info'      => json_encode([
                        'origin_type'      => $origin['type'],
                        'destination_type' => $destination['type'],
                        'load'             => $origin['load'] ?? 0,
                        'driver'           => $Nshipment['driver'] ?? '',
                        'cost'             => $Nshipment['cost'] ?? 0,
                    ]),
                ]);
            }
        }




        Alert::success('سفر جدید', 'ثبت سفر جدید انجام شد.');

        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'ثبت سفر جدید'
        ]);

        return redirect()->route('deliveries.addShipment')->with('newShipment', 'ok');
    }

    public function DriverRouteChinesh()
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.neshan.org/v1/distance-matrix?type=car&origins=35.789394218908654%2C51.45366856167564&destinations=35.75619337066695%2C51.43376780486406%7C35.72138359396949%2C51.438265223530976%7C35.721038847944016%2C51.411796408751826%7C35.73712540619444%2C51.378108826305294%7C35.71122384415452%2C51.460694284405974%7C35.70133927323435%2C51.454041694595446%7C35.76855314932516%2C51.346467901911495%7C35.688019691875525%2C51.3114449039949%7C35.741249581227564%2C51.47657336584098%7C35.77666589245533%2C51.44921003690672',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Api-key: service.e0404239fbd245a3aa943ad68eb3feda'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }

    public function shipments()
    {
        $user = \Auth::user();

        // 🧭 پایه کوئری: فقط سفرهای باز (status = 0)
        $query = Shipments::query()
            ->where('status', 0)
            ->withCount([
                // تعداد کل مسیرها
                'routes',
                // تعداد مسیرهای انجام‌شده (status = 1)
                'routes as finished_routes_count' => function ($q) {
                    $q->where('status', 1);
                },
            ]);

        // 👑 فیلتر سازمان (فقط برای کاربران عادی)
        if ($user->isGod != 1) {
            $query->forOrganizations($user);
        }

        // لیست نهایی سفرها
        $Shipments = $query->get();

        return view('deliveries.shipments_list', compact('Shipments'));
    }

    public function EditShipment(\App\Models\Shipments $shipment)
    {
        $user = \Auth::user();

        // 🏬 گرفتن تمام انبارها بر اساس نقش و سطح دسترسی
        $Stores = $user->isGod == 1
            ? \App\Models\Store::all()
            : \App\Models\Store::forOrganizations($user)->get();

        // 🚚 گرفتن رانندگان براساس نقش driver
        $DriverRole = \DB::table('roles')->where('title', 'driver')->first();
        $DriversIds = \DB::table('role_user')->where('role_id', $DriverRole->id)->pluck('user_id')->toArray();
        $Drivers = \App\Models\User::whereIn('id', $DriversIds)->forOrganizations($user)->get();

        // 📦 فاکتورهایی که فعال هستند (مرحله دوم و status=1)
        $PishFactors = \App\Models\Pishfactor::where('status', 1)
            ->where('step', 2)
            ->forOrganizations($user)
            ->get();

        // 📍 مسیرهای همین سفر به همراه داده کامل فاکتور و مشتری
        $Routes = \App\Models\ShipmentRoute::with(['pishfactor.customer', 'pishfactor.items'])
            ->where('shipment_id', $shipment->id)
            ->get();

        // 🎯 ساخت داده مخصوص نمایش روی نقشه نِشان
        $routesForMap = $Routes->map(function ($route) {
            $factor      = $route->pishfactor;
            $customer    = $factor?->customer;
            $factorItems = $factor?->items;

            return [
                'id'               => $route->id,
                'factor_id'        => $route->factor_id,
                'status'           => $route->status,

                // ✅ اضافه شده: مختصات مبدأ هم برگردون
                'origin_lat'       => floatval($route->origin_lat),
                'origin_lng'       => floatval($route->origin_lng),

                'destination_lat'  => floatval($route->destination_lat),
                'destination_lng'  => floatval($route->destination_lng),

                'customer_name'    => $customer?->name ?? null,
                'address'          => $customer?->address ?? null,
                'invoiceID'        => $factor?->invoiceID ?? null,
                'tozihat'          => $factor?->tozihat ?? null,
                'total_details'    => (intval($route->factor_id) > 0)
                    ? ($factorItems?->count() ?? 0)
                    : 0,
                'total_packs'      => (intval($route->factor_id) > 0)
                    ? ($factorItems?->sum('pack') ?? 0)
                    : 0,
                'total_tedad'      => (intval($route->factor_id) > 0)
                    ? ($factorItems?->sum('tedad') ?? 0)
                    : 0,
            ];
        })
            ->filter(function ($r) {
                // ✅ حفظ مبدأ و فقط حذف نقاطی که هیچ مختصات ندارند
                $hasOrigin = isset($r['origin_lat'], $r['origin_lng']) &&
                    is_numeric($r['origin_lat']) && is_numeric($r['origin_lng']) &&
                    $r['origin_lat'] != 0 && $r['origin_lng'] != 0;

                $hasDestination = isset($r['destination_lat'], $r['destination_lng']) &&
                    is_numeric($r['destination_lat']) && is_numeric($r['destination_lng']) &&
                    $r['destination_lat'] != 0 && $r['destination_lng'] != 0;

                return $hasOrigin || $hasDestination || ($r['factor_id'] === 'start');
            })
            ->values()
            ->toArray();

        // 🟩 محاسبه عنوان انبار (مبدا)
        if ($shipment && intval($shipment->mabda) > 0) {
            $store = \App\Models\Store::find(intval($shipment->mabda));
            $shipment->mabda_title = $store?->title ?? 'انبار ناشناخته';
        } else {
            $shipment->mabda_title = $shipment->mabda ?? 'انبار ناشناخته';
        }

        // ✅ بازگشت به ویو واقعی پروژه‌ی تو
        return view('deliveries.edit_shipment', compact(
            'shipment',
            'Routes',
            'routesForMap',
            'Stores',
            'Drivers',
            'PishFactors'
        ));
    }

    public function assigned_to_drivers()
    {

        $user = \Auth::user();

        // گرفتن همه پیش‌فاکتورهای با وضعیت 2
        $Factors = Pishfactor::where('status', 2)->where('step', 3)->where('organization_id', $user->organization_id)->get();

        // جمع‌آوری آیتم‌ها
        $allItems = collect();
        foreach ($Factors as $factor) {
            foreach ($factor->items as $item) {
                $allItems->push([
                    'pr_id' => $item->pr_id,
                    'product_name' => $item->product ? $item->product->title . ' ' . $item->product->display_name : '-',
                ]);
            }
        }

        // حذف آیتم‌های تکراری بر اساس pr_id
        $uniqueItems = $allItems->unique('pr_id')->values();

        return view('invoices.Driver_assigned', compact('Factors', 'uniqueItems'));
    }


    public function myShipment(Shipments $shipment)
    {
        return view('deliveries.myShipment', compact('shipment'));
    }

    public function shipmentRoute(Pishfactor $factor)
    {

        $Items = PishFactorItems::where('pishfactor_id', $factor->id)->get();

        $Customer = Customers::find($factor->customer_id);
        $Customer_Factors = Pishfactor::where('customer_id', $factor->customer_id)->whereIn('status', [1, 4])->Count();
        $CustomerFactorsPriceCount = Pishfactor::where('customer_id', $factor->customer_id)->whereIn('status', [1, 4])->sum('fullPrice');
        $MandeCustomer = Pishfactor::where('customer_id', $factor->customer_id)
            ->whereIn('status', [1, 4])
            ->where(function ($query) {
                $query->whereNull('payment_type')
                    ->orWhere('payment_type', 3);
            })
            ->sum('fullPrice');

        return view('invoices.driverFactor', compact('factor', 'Items', 'Customer_Factors', 'CustomerFactorsPriceCount', 'MandeCustomer'));
    }


    public function create()
    {
        $user = \Auth::user();
        $roles = Role::all();

        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
        }

        if ($user->isAdmin == 1) {
            $products = Product::latest()->get();
            $stocks = Stock::latest()->get();
            $employees = Employee::where('isActive', 1)->get();
        } else {
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
            $stocks = Stock::whereIn('store_id', $storesUser)->latest()->get();
            $employees = Employee::where('organization_id', $user->organization_id)->latest()->get();
        }
        $organizations = Organization::all();

        return view('deliveries.create', compact('products', 'employees', 'stocks', 'organizations'));
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
                $stock = Stock::where('id', $request['stock_name'][$i])->first();
                $stockEntity = $stock->entity - $request['order_item_quantity'][$i];
                if ($stockEntity < 0) {
                    Alert::error('خطا', 'موجودی کالای ' . $stock->title . ' به زیر صفر می رسد');
                    return back();
                }
            }
        }


        $request['user_id'] = \Auth::user()->id;
        $request['deliverDate'] = $this->to_english_numbers($request['deliverDate']);

        //ثبت کالا در دیتابیس
        for ($i = 0; $i <= ($request->total_item - 1); $i++) {
            if (isset($request['item_name'][$i])) {
                $delivery = Delivery::create([
                    'organization_id' => $request['organization_id'],
                    'user_id' => $request['user_id'],
                    'employee_id' => $request['employee_id'],
                    'deliverDate' => $request['deliverDate'],
                    'description' => $request['description'],
                    'product_id' => $request['item_name'][$i],
                    'AmvalCode' => $request['order_item_amval'][$i],
                    'number' => $request['order_item_quantity'][$i]
                ]);
            } elseif (isset($request['stock_name'][$i])) {
                $delivery = Delivery::create([
                    'organization_id' => $request['organization_id'],
                    'user_id' => $request['user_id'],
                    'employee_id' => $request['employee_id'],
                    'deliverDate' => $request['deliverDate'],
                    'description' => $request['description'],
                    'stock_id' => $request['stock_name'][$i],
                    'AmvalCode' => $request['order_item_amval'][$i],
                    'number' => $request['order_item_quantity'][$i]
                ]);
            }


            //Entity
            if ($delivery->product_id != null) {
                $entity = $delivery->product->entity;
                $delivery->product->update([
                    'entity' => $entity - $request['order_item_quantity'][$i]
                ]);

                History::create([
                    'delivery_id' => $delivery->id,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_id' => $request['user_id'],
                    'action' => 'delivery',
                    'store' => $delivery->product->store->title,
                    'description' => "تعداد " . $request['order_item_quantity'][$i] . " از کالای " . $delivery->product->title . " تحویل شخص " . $delivery->employee->name . " داده شد"
                ]);
            } else {
                $entity = $delivery->stock->entity;
                $delivery->stock->update([
                    'entity' => $entity - $request['order_item_quantity'][$i]
                ]);

                History::create([
                    'delivery_id' => $delivery->id,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_id' => $request['user_id'],
                    'action' => 'delivery',
                    'store' => $delivery->stock->store->title,
                    'description' => "تعداد " . $request['order_item_quantity'][$i] . " از کالای " . $delivery->stock->title . " تحویل شخص " . $delivery->employee->name . " داده شد"
                ]);
            }
            Log::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => \Auth::user()->id,
                'action' => 'create',
                'description' => 'تحویل کالا انجام شد' . '-' . $delivery->employee->name
            ]);
        }

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Delivery $delivery)
    {
        $user = \Auth::user();
        $roles = Role::all();

        foreach ($user->roles as $role) {
            $storesUser = DB::table('role_store')->where('role_id', $role->id)->pluck('store_id');
        }

        if ($user->isAdmin == 1) {
            $products = Product::latest()->get();
            $stocks = Stock::latest()->get();
            $deliveries = Delivery::latest()->get();
            $employees = Employee::where('isActive', 1)->get();
        } else {
            $products = Product::whereIn('store_id', $storesUser)->latest()->get();
            $stocks = Stock::whereIn('store_id', $storesUser)->latest()->get();
            $deliveries = Delivery::where('organization_id', $user->organization_id)->latest()->get();
            $employees = Employee::where('organization_id', $user->organization_id)->latest()->get();
        }
        $organizations = Organization::all();

        return view('deliveries.edit', compact('products', 'employees', 'deliveries', 'delivery', 'organizations', 'stocks'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $user = \Auth::user();
        $request['deliverDate'] = $this->to_english_numbers($request['deliverDate']);
        //شرط کمتر از صفر شدن موجودی
        if (isset($request['product_id'])) {
            $productEntity = Product::where('id', $request['product_id'])->first();
            $productEntity = $productEntity->entity - $request['number'];
            if ($productEntity < 0) {
                Alert::error('خطا', 'موجودی کالا به زیر صفر می رسد!');
                return back();
            }
        } elseif (isset($request['stock_id'])) {
            $stockEntity = Stock::where('id', $request['stock_id'])->first();
            $stockEntity = $stockEntity->entity - $request['number'];
            if ($stockEntity < 0) {
                Alert::error('خطا', 'موجودی کالا به زیر صفر می رسد!');
                return back();
            }
        }

        //Entity
        if ($delivery->product_id != null) {
            $entity = $delivery->product->entity + $delivery->number;
            $delivery->product->update([
                'entity' => $entity
            ]);

            $entity = $delivery->product->entity;
            $delivery->product->update([
                'entity' => $entity - $request['number']
            ]);

            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'delivery',
                'store' => $delivery->product->store->title,
                'description' => " تعداد کالای " . $delivery->product->title . " از " . $entity . " به " . $request['number'] . " که تحویل شخص " . $delivery->employee->name . " داده شده بود، تغییر کرد"
            ]);
        } else {
            $entity = $delivery->stock->entity + $delivery->number;
            $delivery->stock->update([
                'entity' => $entity
            ]);

            $entity = $delivery->stock->entity;
            $delivery->stock->update([
                'entity' => $entity - $request['number']
            ]);

            History::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $user->id,
                'action' => 'delivery',
                'store' => $delivery->stock->store->title,
                'description' => " تعداد کالای " . $delivery->stock->title . " از " . $entity . " به " . $request['number'] . " که تحویل شخص " . $delivery->employee->name . " داده شده بود، تغییر کرد"
            ]);
        }

        //

        if ($request['product_id'] != $delivery->product_id) {
            $delivery->product->update([
                'entity' => $delivery->product->entity + $delivery->number
            ]);
            $product = Product::where('id', $request['product_id'])->first();
            $product->update([
                'entity' => $product->entity - $request['number']
            ]);

            $delivery->update([
                'product_id' => $request->product_id,
                'number' => $request->number,
                'employee_id' => $request->employee_id,
                'description' => $request->description,
                'deliverDate' => $request->deliverDate,
                'AmvalCode' => $request->AmvalCode
            ]);
            Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
            return back();
        } elseif ($request['stock_id'] != $delivery->stock_id) {
            $delivery->stock->update([
                'entity' => $delivery->stock->entity + $delivery->number
            ]);
            $stock = Stock::where('id', $request['stock_id'])->first();
            $stock->update([
                'entity' => $stock->entity - $request['number']
            ]);

            $delivery->update([
                'stock_id' => $request->stock_id,
                'number' => $request->number,
                'employee_id' => $request->employee_id,
                'description' => $request->description,
                'deliverDate' => $request->deliverDate,
                'AmvalCode' => $request->AmvalCode
            ]);
            Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
            return back();
        } else {
            $delivery->update([
                'number' => $request->number,
                'employee_id' => $request->employee_id,
                'description' => $request->description,
                'deliverDate' => $request->deliverDate,
                'AmvalCode' => $request->AmvalCode
            ]);
            Log::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => \Auth::user()->id,
                'action' => 'update',
                'description' => 'تحویل کالا ویرایش شد' . '-' . $delivery->employee->name
            ]);

            Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
            return back();
        }
    }

    public function destroy(Delivery $delivery)
    {

        //Entity
        if ($delivery->product_id != null) {
            $entity = $delivery->product->entity;
            $delivery->product->update([
                'entity' => $entity + $delivery['number']
            ]);
        } else {
            $entity = $delivery->stock->entity;
            $delivery->stock->update([
                'entity' => $entity + $delivery['number']
            ]);
        }

        $history = History::where('delivery_id', $delivery->id)->first();
        $history->delete();
        $delivery->delete();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => \Auth::user()->id,
            'action' => 'create',
            'description' => 'تحویل کالا حذف شد' . '-' . $delivery->employee->name
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت حذف شد');
        return back();
    }

    public function getEmployee($id)
    {
        $employee_id = Employee::where('organization_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($employee_id);
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
