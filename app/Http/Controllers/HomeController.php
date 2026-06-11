<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Delivery;
use App\Models\Targets;
use App\Models\Tasks;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Customers;
use App\Models\Log;
use App\Models\Region;
use App\Models\Area;
use App\Models\Repair;
use App\Models\Shipments;
use App\Models\ShipmentRoute;
use App\Models\Cargo;
use App\Models\User;
use App\Services\WarehouseDashboardService;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Iyzipay\Model\Customer;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $User = Auth::user();
        $products = Product::count();
        $employees = Employee::count();
        $repairs = Repair::count();
        $logs = Log::where('user_id', $User->id)->latest()->take(100)->get();
        $isManager = false;
        $isLeader = false;
        $isVisitor = false;
        $isDriver = false;
        $isStore = false;
        $isAgent = false;
        foreach ($User->roles as $role) {
            if ($role->title == 'expert') {
                $isManager = true;
            }
            if ($role->title == 'leader') {
                $isLeader = true;
            }
            if ($role->title == 'visitor') {
                $isVisitor = true;
            }
            if ($role->title == 'driver') {
                $isDriver = true;
            }
            if ($role->title == 'store') {
                $isStore = true;
            }
            if ($role->title == 'agent') {
                $isAgent = true;
            }
        }

        $warehouseDashboard = app(WarehouseDashboardService::class)->forUser($User);

        if ($isVisitor) {
            $Target = Targets::where('status', 1)->where('user_id', $User->id)->first();

            if ($Target) {
                $myUsers = $this->getSubUsersWithFactors($User->id, $Target);


                $AllFactors = Pishfactor::where('visitor_id', $User->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en])
                    ->get();

                $AllFactorCount = count($AllFactors);

                $AllFactorPrices = 0;
                $PatPrices = 0;
                foreach ($AllFactors as $factor) {
                    $AllFactorPrices += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                    $PatPrices += intval(str_replace(',', '', $factor->pat_price ?? 0));
                }


                // امروز
                $todaySum = Pishfactor::where('status', 1)
                    ->where('visitor_id', $User->id)
                    ->whereDate('created_at', Carbon::today())
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                // این هفته
                $weekSum = Pishfactor::where('status', 1)
                    ->where('visitor_id', $User->id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                // این ماه
                $monthSum = Pishfactor::where('status', 1)
                    ->where('visitor_id', $User->id)
                    ->whereBetween('created_at', [$Target->start_date_en, Carbon::today()])
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));
            } else {
                $AllFactorPrices = 0;
                $AllFactorCount = 0;
                $todaySum = 0;
                $weekSum = 0;
                $monthSum = 0;
                $PatPrices = 0;
            }

            $MyFactorsCount = Pishfactor::where('visitor_id', auth()->user()->id)->count();
            $MyAcceptedFactorsCount = Pishfactor::where('visitor_id', auth()->user()->id)->whereIn('status', [1, 4])->count();
            $MyActiveCustomers = Pishfactor::select('customer_id')
                ->where('visitor_id', auth()->user()->id)
                ->whereIn('status', [1, 4])
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            $DayFactors = Pishfactor::where('visitor_id', $User->id)
                ->whereDate('created_at', Carbon::today())
                ->get();

            $MyTasks = Tasks::where('user_id', auth()->user()->id)->where('status', 1)->pluck('area_id');
            $MyCustomersCount = Customers::whereIn('area', $MyTasks)->get();

            $DriverFactors = Pishfactor::where('sarparast_id', $User->id)
                ->where('step', 3)
                ->whereDate('created_at', Carbon::today())->get();

            return view('welcome-visitor', compact('User', 'Target', 'employees', 'repairs', 'logs', 'AllFactorPrices', 'AllFactorCount', 'todaySum', 'weekSum', 'monthSum', 'MyCustomersCount', 'MyFactorsCount', 'DayFactors', 'MyTasks', 'MyAcceptedFactorsCount', 'MyActiveCustomers', 'DriverFactors', 'PatPrices'));
        } elseif ($isAgent) {

            $AgentFactors = Pishfactor::where('visitor_id', $User->id)
                ->orderBy('id', 'desc')
                ->get();

            $AllFactorCount = $AgentFactors->count();
            $AllFactorPrices = $AgentFactors->sum(function ($factor) {
                return intval(str_replace(',', '', $factor->fullPrice ?? 0));
            });
            $todaySum = Pishfactor::where('visitor_id', $User->id)
                ->whereDate('created_at', Carbon::today())
                ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));
            $weekSum = Pishfactor::where('visitor_id', $User->id)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));
            $monthSum = Pishfactor::where('visitor_id', $User->id)
                ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::today()])
                ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));
            $MyFactorsCount = $AllFactorCount;
            $MyAcceptedFactorsCount = Pishfactor::where('visitor_id', $User->id)
                ->whereIn('status', [1, 4])
                ->count();
            $DayFactors = Pishfactor::where('visitor_id', $User->id)
                ->whereDate('created_at', Carbon::today())
                ->orderBy('id', 'desc')
                ->get();

            return view('welcome-agent', compact('User', 'employees', 'repairs', 'logs', 'AllFactorPrices', 'AllFactorCount', 'todaySum', 'weekSum', 'monthSum', 'MyFactorsCount', 'MyAcceptedFactorsCount', 'DayFactors'));
        } elseif ($isLeader) {

            $Target = Targets::where('status', 1)->where('user_id', $User->id)->first();
            if ($Target) {
                $myUsers = $this->getSubUsersWithFactors($User->id, $Target);
                $AllFactorPrices = 0;
                $AllFactorCount = 0;
                foreach ($myUsers as $user) {
                    $AllFactorPrices += $user['FactorPrices'];
                    $AllFactorCount += $user['factors_count'];
                }

                // امروز
                $todaySum = Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $User->id)
                    ->whereDate('created_at', Carbon::today())
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                // این هفته
                $weekSum = Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $User->id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                // این ماه
                $monthSum = Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $User->id)
                    ->whereBetween('created_at', [$Target->start_date_en, Carbon::today()])
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                $MyVisitors = $this->getSubUsersWithFactors($User->id, $Target);
            } else {
                $AllFactorPrices = 0;
                $AllFactorCount = 0;
                $todaySum = 0;
                $weekSum = 0;
                $monthSum = 0;

                $MyVisitors = $this->getSubUsersWithFactorsAll($User->id);
            }

            $myFactorIds = Pishfactor::whereIn('status', [1, 4])
                ->where('sarparast_id', $User->id)
                ->pluck('id');

            $topProducts = PishFactorItems::whereIn('pishfactor_id', $myFactorIds)
                ->select('pr_id', \DB::raw('COUNT(*) as total_sales'))
                ->groupBy('pr_id')
                ->orderByDesc('total_sales')
                ->get();


            $topVisitors = Pishfactor::whereIn('status', [1, 4])
                ->where('sarparast_id', $User->id)
                ->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()])
                ->groupBy('visitor_id')
                ->select(
                    'visitor_id',
                    \DB::raw('COUNT(*) as total_factors'),
                    \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
                )
                ->orderByDesc('total_factors')
                ->get();

            $MyFactorsCount = Pishfactor::where('sarparast_id', auth()->user()->id)->count();
            $MyAcceptedFactorsCount = Pishfactor::where('sarparast_id', auth()->user()->id)->whereIn('status', [1, 4])->count();
            $MyActiveCustomers = Pishfactor::select('customer_id')
                ->where('sarparast_id', auth()->user()->id)
                ->whereIn('status', [1, 4])
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            $DayFactors = Pishfactor::where('visitor_id', $User->id)
                ->whereDate('created_at', Carbon::today())
                ->get();

            $MyTasks = Tasks::where('leader_id', auth()->user()->id)->where('status', 1)->pluck('area_id');
            $MyRegions = Region::where('leader_id', $User->id)->pluck('id');
            $MyAreas = Area::whereIn('region_id', $MyRegions)->pluck('id');
            $MyCustomersCount = Customers::whereIn('area', $MyAreas)->get();


            // dd($MyVisitors);

            // امروز
            $Factors = Pishfactor::where('sarparast_id', $User->id)
                ->whereDate('created_at', Carbon::today())->get();

            $DriverFactors = Pishfactor::where('sarparast_id', $User->id)
                ->where('step', 3)
                ->whereDate('created_at', Carbon::today())->get();


            return view('welcome-leader', compact('User', 'Target', 'employees', 'repairs', 'logs', 'AllFactorPrices', 'AllFactorCount', 'todaySum', 'weekSum', 'monthSum', 'topProducts', 'topVisitors', 'MyTasks', 'MyCustomersCount', 'MyAcceptedFactorsCount', 'MyActiveCustomers', 'MyFactorsCount', 'MyVisitors', 'Factors', 'DriverFactors'));
        } elseif ($isManager) {

            $Target = Targets::where('status', 1)->where('user_id', $User->id)->first();
            if ($Target) {
                $myUsers = $this->getSubUsersWithFactors($User->id, $Target);

                $AllFactorPrices = 0;
                $AllFactorCount = 0;
                $ActiveTasks = 0;
                foreach ($myUsers as $user) {
                    $AllFactorPrices += $user['FactorPrices'];
                    $AllFactorCount += $user['factors_count'];
                    $ActiveTasks += count($user['ActiveTasks']);
                }

                // امروز
                $todaySum = Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $User->id)
                    ->whereDate('created_at', Carbon::today())
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                // این هفته
                $weekSum = Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $User->id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));

                // این ماه
                $monthSum = Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $User->id)
                    ->whereBetween('created_at', [$Target->start_date_en, Carbon::today()])
                    ->sum(DB::raw("REPLACE(fullPrice, ',', '')"));
            } else {
                $AllFactorPrices = 0;
                $AllFactorCount = 0;
                $todaySum = 0;
                $weekSum = 0;
                $monthSum = 0;
                $ActiveTasks = 0;
            }

            $myFactorIds = Pishfactor::whereIn('status', [1, 4])
                ->where('sarparast_id', $User->id)
                ->pluck('id');

            $topProducts = PishFactorItems::whereIn('pishfactor_id', $myFactorIds)
                ->select('pr_id', \DB::raw('COUNT(*) as total_sales'))
                ->groupBy('pr_id')
                ->orderByDesc('total_sales')
                ->get();


            $MyLeadersIds = User::where('leader_id', $User->id)->pluck('id');
            $MyVisitors = User::whereIn('leader_id', $MyLeadersIds)->pluck('id');
            if ($Target) {
                $topVisitors = Pishfactor::whereIn('visitor_id', $MyVisitors)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en])
                    ->select(
                        'visitor_id',
                        \DB::raw('COUNT(*) as factors_count'),
                        \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
                    )
                    ->groupBy('visitor_id')
                    ->orderByDesc('factors_count')
                    ->orderByDesc('total_fullPrice')
                    ->get();
            } else {
                $topVisitors = Pishfactor::whereIn('visitor_id', $MyVisitors)
                    ->whereIn('status', [1, 4])
                    ->select(
                        'visitor_id',
                        \DB::raw('COUNT(*) as factors_count'),
                        \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
                    )
                    ->groupBy('visitor_id')
                    ->orderByDesc('factors_count')
                    ->orderByDesc('total_fullPrice')
                    ->get();
            }


            $MyFactorsCount = Pishfactor::where('sarparast_id', auth()->user()->id)->count();
            $MyAcceptedFactorsCount = Pishfactor::where('sarparast_id', auth()->user()->id)->whereIn('status', [1, 4])->count();
            $MyActiveCustomers = Pishfactor::select('customer_id')
                ->where('sarparast_id', auth()->user()->id)
                ->whereIn('status', [1, 4])
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            $DayFactors = Pishfactor::where('visitor_id', $User->id)
                ->whereDate('created_at', Carbon::today())
                ->get();

            $MyRegions = Region::where('leader_id', $User->id)->pluck('id');
            $MyAreas = Area::whereIn('region_id', $MyRegions)->pluck('id');
            $MyCustomersCount = Customers::whereIn('area', $MyAreas)->get();

            if ($Target) {
                $MyLeaders = $this->getSubUsersWithFactors($User->id, $Target);
            } else {
                $MyLeaders = $this->getSubUsersWithFactorsAll($User->id);
            }

            // dd($MyVisitors);

            $Factors = $this->ManagerTeamFactors();

            $DriverFactors = Pishfactor::where('sarparast_id', $User->id)
                ->where('step', 3)
                ->whereDate('created_at', Carbon::today())->get();


            return view('welcome-manager', compact('User', 'Target', 'employees', 'repairs', 'logs', 'AllFactorPrices', 'AllFactorCount', 'todaySum', 'weekSum', 'monthSum', 'topProducts', 'topVisitors', 'ActiveTasks', 'MyCustomersCount', 'MyAcceptedFactorsCount', 'MyActiveCustomers', 'MyFactorsCount', 'Factors', 'DriverFactors', 'MyLeaders'));
        } elseif ($isDriver) {

            $Cargo = Cargo::where('driver_id', $User->id)->first();

            $DayShipments = Shipments::where('driver_id', $User->id)
                ->whereDate('created_at', Carbon::today())
                ->withCount([
                    'routes', // کل مسیرها
                    'routes as done_routes_count' => function ($q) {
                        $q->where('status', 1); // فقط انجام‌شده‌ها
                    }
                ])
                ->orderBy('id', 'desc')
                ->get();

            $ActiveShipments = Shipments::where('driver_id', $User->id)->where('status', 0)->withCount('routes')->orderBy('id', 'desc')->get();

            // استخراج شناسه‌ها فقط برای کوئری جمع‌گیری
            $DayShipmentIds = $DayShipments->pluck('id');
            $ActiveShipmentIds = $ActiveShipments->pluck('id');

            $TotalDayRoutes = ShipmentRoute::whereIn('shipment_id', $DayShipmentIds)->count();
            $DoneDayRoutes  = ShipmentRoute::whereIn('shipment_id', $DayShipmentIds)->where('status', 1)->count();

            $TotalActiveRoutes = ShipmentRoute::whereIn('shipment_id', $ActiveShipmentIds)->count();
            $DoneActiveRoutes  = ShipmentRoute::whereIn('shipment_id', $ActiveShipmentIds)->where('status', 1)->count();

            return view('welcome-driver', compact('User', 'Cargo', 'DayShipments', 'ActiveShipments', 'TotalDayRoutes', 'DoneDayRoutes', 'TotalActiveRoutes', 'DoneActiveRoutes'));
        } elseif ($isStore) {
            if ($User->isGod == 1) {
                $MyOrgans = Organization::all();
            } else {
                $MyOrgans = Organization::forOrganizations($User, 'id')->get();
            }

            $OrganInfos = array();
            $FullTargetsPrices = 0;
            $AllFactorPrices = 0;
            $StartTarget = null;
            $EndTarget = null;
            foreach ($MyOrgans as $morgan) {

                $OrganManagers = User::where('organization_id', $morgan->id)
                    ->whereHas('roles', function ($q) {
                        $q->where('title', 'expert');
                    })
                    ->pluck('id')
                    ->toArray();

                $ManagersWithTarget = [];
                foreach ($OrganManagers as $managerId) {
                    $target = Targets::where('user_id', $managerId)->where('status', 1)->first();
                    if ($target) {
                        $ManagersWithTarget[] = $target;
                        // اگر فقط اولین آیدی با تارگت را می‌خواهید:
                        // break;
                    }
                }

                // اگر فقط اولین آیدی با تارگت را می‌خواهید:
                $Targets = !empty($ManagersWithTarget) ? $ManagersWithTarget[0] : null;
                if ($Targets) {
                    $StartTarget = $Targets->start_date_en;
                    $EndTarget = $Targets->end_date_en;
                }

                $TargetPrices = Targets::whereIn('user_id', $OrganManagers)->where('status', 1)->sum('target_price');
                $FullTargetsPrices += intval($TargetPrices);

                $OrganInfo = array();
                $OrganInfo['id'] = $morgan->id;
                $OrganInfo['title'] = $morgan->title;
                $OrganInfo['AcceptedFactorFullPrices'] = 0;
                $OrganInfo['CompletedFactorFullPrices'] = 0;
                $OrganInfo['FactorFullPrices'] = 0;
                $OrganInfo['FactorPatPrices'] = 0;
                $OrganInfo['LastFactorFullPrices'] = 0;
                $OrganInfo['LastFactorPatPrices'] = 0;
                $FactorPrices = 0;
                $PatPrices = 0;
                if ($Targets) {
                    $OrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [$Targets->start_date_en, $Targets->end_date_en])
                        ->get();

                    // کل مبالغ (هر دو وضعیت با هم)
                    $OrganInfo['FactorFullPrices'] = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $OrganInfo['FactorPatPrices']  = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->pat_price ?? 0));

                    // 🔸 جداگانه برای status = 1
                    $OrganInfo['AcceptedFactorFullPrices'] = $OrganFactors
                        ->where('status', 1)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));

                    // 🔸 جداگانه برای status = 4
                    $OrganInfo['CompletedFactorFullPrices'] = $OrganFactors
                        ->where('status', 4)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));

                    $startDate30DaysBefore = Carbon::parse($Targets->start_date_en)->subDays(30);
                    $StartDateFormatted = $startDate30DaysBefore->format('Y-m-d H:i:s');

                    $endDate30DaysBefore = Carbon::parse($Targets->end_date_en)->subDays(30);
                    $endDateFormatted = $endDate30DaysBefore->format('Y-m-d H:i:s');

                    $LastMonthOrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [$StartDateFormatted, $endDateFormatted])
                        ->get();
                    foreach ($LastMonthOrganFactors as $factor) {
                        $OrganInfo['LastFactorFullPrices'] += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                        $OrganInfo['LastFactorPatPrices'] += intval(str_replace(',', '', $factor->pat_price ?? 0));
                    }
                } else {
                    $OrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [$StartTarget, $EndTarget])
                        ->get();

                    $OrganInfo['FactorFullPrices'] = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $OrganInfo['FactorPatPrices']  = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->pat_price ?? 0));

                    // 🔸 جداگانه برای status = 1
                    $OrganInfo['AcceptedFactorFullPrices'] = $OrganFactors
                        ->where('status', 1)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));

                    // 🔸 جداگانه برای status = 4
                    $OrganInfo['CompletedFactorFullPrices'] = $OrganFactors
                        ->where('status', 4)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));

                    $LastMonthOrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])
                        ->get();
                    foreach ($LastMonthOrganFactors as $factor) {
                        $OrganInfo['LastFactorFullPrices'] += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                        $OrganInfo['LastFactorPatPrices'] += intval(str_replace(',', '', $factor->pat_price ?? 0));
                    }
                }
                $AllFactorPrices += intval(str_replace(',', '', $OrganInfo['FactorFullPrices']));
                array_push($OrganInfos, $OrganInfo);
            }

            //dd($OrganInfos);

            $topVisitors = Pishfactor::select(
                'visitor_id',
                \DB::raw('COUNT(*) as total_factors'),
                \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
            )
                ->whereIn('status', [1, 4])
                ->forOrganizations($User)
                ->whereBetween('created_at', [$StartTarget, $EndTarget])
                ->groupBy('visitor_id')
                ->orderBy('total_fullPrice', 'desc')
                ->get();

            $MyLeaders = Pishfactor::select(
                'sarparast_id',
                \DB::raw('COUNT(*) as total_factors'),
                \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
            )
                ->whereIn('status', [1, 4])
                ->forOrganizations($User)
                ->whereBetween('created_at', [$StartTarget, $EndTarget])
                ->groupBy('sarparast_id')
                ->orderBy('total_fullPrice', 'desc')
                ->get();

            $Factors = Pishfactor::whereIn('status', [1, 4])
                ->forOrganizations($User)
                ->whereBetween('created_at', [$StartTarget, $EndTarget])
                ->get();


            return view('welcome-store', compact('employees', 'repairs', 'logs', 'OrganInfos', 'FullTargetsPrices', 'AllFactorPrices', 'EndTarget', 'topVisitors', 'MyLeaders', 'Factors', 'warehouseDashboard'));
        } else {

            if ($User->isGod == 1) {
                $MyOrgans = Organization::all();
            } else {
                $MyOrgans = Organization::forOrganizations($User, 'id')->get();
            }

            $OrganInfos = array();
            $FullTargetsPrices = 0;
            $AllFactorPrices = 0;
            $StartTarget = null;
            $EndTarget = null;
            $totalSalesQty = 0;
            $totalSalesAmount = 0;
            $ManagersWithTarget = [];

            $OrganInfo = array();
            $OrganInfo['id'] = 0;
            $OrganInfo['title'] = 0;
            $OrganInfo['AcceptedFactorFullPrices'] = 0;
            $OrganInfo['CompletedFactorFullPrices'] = 0;
            $OrganInfo['FactorFullPrices'] = 0;
            $OrganInfo['FactorPatPrices'] = 0;
            $OrganInfo['LastFactorFullPrices'] = 0;
            $OrganInfo['LastFactorPatPrices'] = 0;
            $OrganInfo['totalSalesQty'] = 0;
            $OrganInfo['totalSalesAmount'] = 0;
            $AcceptedFactorFullPrices = 0;
            $CompletedFactorFullPrices = 0;

            foreach ($MyOrgans as $morgan) {

                $OrganManagers = User::whereJsonContains('organization_id', ["$morgan->id"])
                    ->whereHas('roles', function ($q) {
                        $q->where('title', 'expert');
                    })
                    ->pluck('id')
                    ->toArray();


                foreach ($OrganManagers as $managerId) {
                    $target = Targets::where('user_id', $managerId)
                        ->where('status', 1)
                        ->first();

                    if ($target && !in_array($managerId, array_column($ManagersWithTarget, 'user_id'))) {
                        $ManagersWithTarget[] = $target;
                        $TargetPrices = Targets::whereIn('user_id', $OrganManagers)->where('status', 1)->sum('target_price');

                        $FullTargetsPrices += intval($TargetPrices);
                    }
                }



                // اگر فقط اولین آیدی با تارگت را می‌خواهید:
                $Targets = !empty($ManagersWithTarget) ? $ManagersWithTarget[0] : null;
                if ($Targets) {
                    $StartTarget = $Targets->start_date_en;
                    $EndTarget = $Targets->end_date_en;
                }



                $OrganInfo['id'] = $morgan->id;
                $OrganInfo['title'] = $morgan->title;

                $FactorPrices = 0;
                $PatPrices = 0;
                if ($Targets) {

                    $OrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [$Targets->start_date_en, date('Y-m-d H:i:s')])
                        ->get();

                    // کل مبالغ (هر دو وضعیت با هم)
                    $OrganInfo['FactorFullPrices'] = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $OrganInfo['FactorPatPrices']  = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->pat_price ?? 0));

                    // 🔸 جداگانه برای status = 1
                    $OrganInfo['AcceptedFactorFullPrices'] = $OrganFactors
                        ->where('status', 1)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $AcceptedFactorFullPrices += $OrganInfo['AcceptedFactorFullPrices'];

                    // 🔸 جداگانه برای status = 4
                    $OrganInfo['CompletedFactorFullPrices'] = $OrganFactors
                        ->where('status', 4)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $CompletedFactorFullPrices += $OrganInfo['CompletedFactorFullPrices'];

                    $startDate30DaysBefore = Carbon::parse($Targets->start_date_en)->subDays(30);
                    $StartDateFormatted = $startDate30DaysBefore->format('Y-m-d H:i:s');

                    $endDate30DaysBefore = Carbon::parse($Targets->end_date_en)->subDays(30);
                    $endDateFormatted = $endDate30DaysBefore->format('Y-m-d H:i:s');

                    $LastMonthOrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [$StartDateFormatted, $endDateFormatted])
                        ->get();
                    foreach ($LastMonthOrganFactors as $factor) {
                        $OrganInfo['LastFactorFullPrices'] += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                        $OrganInfo['LastFactorPatPrices'] += intval(str_replace(',', '', $factor->pat_price ?? 0));
                    }
                } else {
                    $OrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [$StartTarget, $EndTarget])
                        ->get();

                    $OrganInfo['FactorFullPrices'] = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $OrganInfo['FactorPatPrices']  = $OrganFactors->sum(fn($f) => (int) str_replace(',', '', $f->pat_price ?? 0));

                    // 🔸 جداگانه برای status = 1
                    $OrganInfo['AcceptedFactorFullPrices'] = $OrganFactors
                        ->where('status', 1)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $AcceptedFactorFullPrices += $OrganInfo['AcceptedFactorFullPrices'];

                    // 🔸 جداگانه برای status = 4
                    $OrganInfo['CompletedFactorFullPrices'] = $OrganFactors
                        ->where('status', 4)
                        ->sum(fn($f) => (int) str_replace(',', '', $f->fullPrice ?? 0));
                    $CompletedFactorFullPrices += $OrganInfo['CompletedFactorFullPrices'];


                    $LastMonthOrganFactors = Pishfactor::where('organization_id', $morgan->id)
                        ->whereIn('status', [1, 4])
                        ->whereBetween('created_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])
                        ->get();
                    foreach ($LastMonthOrganFactors as $factor) {
                        $OrganInfo['LastFactorFullPrices'] += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                        $OrganInfo['LastFactorPatPrices'] += intval(str_replace(',', '', $factor->pat_price ?? 0));
                    }
                }

                /* $products = Product::withoutGlobalScope('withCurrentStock') // حذف موجودی پیش‌فرض
                 ->forOrganizations($morgan, 'products.organization_id')
                     ->where('products.isMaterial', 0)
                     ->withSalesReport() // این شامل موجودی + فروش میشه
                     ->get();

                 $OrganInfo['totalSalesQty'] += $products->sum('total_qty');
                 $OrganInfo['totalSalesAmount'] += $products->sum('total_amount'); */

                $AllFactorPrices += intval(str_replace(',', '', $OrganInfo['FactorFullPrices']));
                array_push($OrganInfos, $OrganInfo);
            }




            $topVisitors = Pishfactor::select(
                'visitor_id',
                \DB::raw('COUNT(*) as total_factors'),
                \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
            )
                ->whereIn('status', [1, 4])
                ->forOrganizations($User)
                ->whereBetween('created_at', [$StartTarget, $EndTarget])
                //->newCustomers($StartTarget)
                ->groupBy('visitor_id')
                ->orderBy('total_fullPrice', 'desc')
                ->get();

            $MyLeaders = Pishfactor::select(
                'sarparast_id',
                \DB::raw('COUNT(*) as total_factors'),
                \DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_fullPrice")
            )
                ->whereIn('status', [1, 4])
                ->forOrganizations($User)
                ->whereBetween('created_at', [$StartTarget, $EndTarget])
                //->newCustomers($StartTarget)
                ->groupBy('sarparast_id')
                ->orderBy('total_fullPrice', 'desc')
                ->get();

            $Factors = Pishfactor::whereIn('status', [1, 4])
                ->forOrganizations($User)
                ->whereBetween('created_at', [$StartTarget, $EndTarget])
                ->limit(50)
                ->get();


            return view('welcome', compact('employees', 'repairs', 'logs', 'OrganInfos', 'FullTargetsPrices', 'AllFactorPrices', 'StartTarget', 'EndTarget', 'topVisitors', 'MyLeaders', 'Factors', 'AcceptedFactorFullPrices', 'CompletedFactorFullPrices', 'warehouseDashboard'));
        }
    }

    private function getSubUsersWithFactors($leaderId, $target)
    {
        $subs = User::where('leader_id', $leaderId)->where('isActive', 1)->get();
        $result = [];

        foreach ($subs as $sub) {

            $AllFactorPrices = 0;
            $AllPatPrices = 0;
            $factorsCount = array();
            $activeTaskCount = array();
            // گرفتن رول یوزر (فرض بر این است که relation roles تعریف شده)
            $role = $sub->roles()->first();
            $roleTitle = $role ? $role->title : '-';

            if ($roleTitle == 'leader') {
                // گرفتن تعداد فاکتورهای یوزر
                $factorsCount = Pishfactor::where('sarparast_id', $sub->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en])
                    ->get();

                $activeTaskCount = Tasks::where('leader_id', $sub->id)->where('status', 1)->get();
            }

            if ($roleTitle == 'visitor') {
                // گرفتن تعداد فاکتورهای یوزر
                $factorsCount = Pishfactor::where('visitor_id', $sub->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en])
                    ->get();

                $activeTaskCount = Tasks::where('user_id', $sub->id)->where('status', 1)->get();
            }
            $FactorPrices = 0;
            $PatPrices = 0;
            if (count($factorsCount) > 0) {
                foreach ($factorsCount as $factor) {
                    $FactorPrices += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                    $PatPrices += intval(str_replace(',', '', $factor->pat_price ?? 0));
                }
            }

            $AllFactorPrices += $FactorPrices;
            $AllPatPrices += $PatPrices;



            // بازگشتی برای زیرمجموعه‌ها
            $children = $this->getSubUsersWithFactors($sub->id, $target);

            $result[] = [
                'id' => $sub->id,
                'name' => $sub->name,
                'username' => $sub->username,
                'role' => $roleTitle,
                'factors_count' => count($factorsCount),
                'children' => $children,
                'FactorPrices' => $AllFactorPrices,
                'PatPrices' => $AllPatPrices,
                'ActiveTasks' => $activeTaskCount,
                'isActive' => $sub->isActive
            ];
        }

        // سورت بر اساس تعداد فاکتور
        usort($result, function ($a, $b) {
            return $b['factors_count'] <=> $a['factors_count'];
        });

        return $result;
    }

    private function getSubUsersWithFactorsAll($leaderId)
    {

        $User = auth()->user();
        $subs = User::where('leader_id', $leaderId)->get();
        $result = [];

        foreach ($subs as $sub) {

            $AllFactorPrices = 0;
            $AllPatPrices = 0;

            // گرفتن رول یوزر (فرض بر این است که relation roles تعریف شده)
            $role = $sub->roles()->first();
            $roleTitle = $role ? $role->title : '-';


            if ($roleTitle == 'leader') {
                // گرفتن تعداد فاکتورهای یوزر
                $factorsCount = Pishfactor::where('sarparast_id', $sub->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$sub->created_at, Carbon::today()])
                    ->get();
            }

            if ($roleTitle == 'visitor') {
                // گرفتن تعداد فاکتورهای یوزر
                $factorsCount = Pishfactor::where('visitor_id', $sub->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$sub->created_at, Carbon::today()])
                    ->get();
            }
            if ($roleTitle == 'expert') {
                // گرفتن تعداد فاکتورهای یوزر
                $factorsCount = Pishfactor::forOrganizations($User)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$sub->created_at, Carbon::today()])
                    ->get();
            }
            $FactorPrices = 0;
            $PatPrices = 0;
            if ($factorsCount) {
                foreach ($factorsCount as $factor) {
                    $FactorPrices += intval(str_replace(',', '', $factor->fullPrice ?? 0));
                    $PatPrices += intval(str_replace(',', '', $factor->pat_price ?? 0));
                }
                $AllFactorPrices += $FactorPrices;
                $AllPatPrices += $PatPrices;
            }




            // بازگشتی برای زیرمجموعه‌ها
            $children = $this->getSubUsersWithFactorsAll($sub->id);

            $result[] = [
                'id' => $sub->id,
                'name' => $sub->name,
                'username' => $sub->username,
                'role' => $roleTitle,
                'factors_count' => count($factorsCount),
                'children' => $children,
                'FactorPrices' => $AllFactorPrices,
                'PatPrices' => $AllPatPrices,
                'isActive' => $sub->isActive
            ];
        }

        // سورت بر اساس تعداد فاکتور
        usort($result, function ($a, $b) {
            return $b['factors_count'] <=> $a['factors_count'];
        });

        return $result;

        dd($result);
    }

    private function ManagerTeamFactors()
    {

        $userId = auth()->user()->id;
        // گرفتن آیدی سرپرست‌های زیرمجموعه من
        $leaders = User::where('leader_id', $userId)
            ->whereHas('roles', function ($q) {
                $q->where('title', 'leader');
            })
            ->pluck('id');

        // گرفتن آیدی ویزیتورهای زیرمجموعه سرپرست‌ها
        $visitors = User::whereIn('leader_id', $leaders)
            ->whereHas('roles', function ($q) {
                $q->where('title', 'visitor');
            })
            ->pluck('id');

        // گرفتن فاکتورهای ویزیتورها
        $pishfactors = Pishfactor::whereIn('visitor_id', $visitors)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->orderBy('id', 'desc')->get();

        return $pishfactors;
    }



    public function getOrgData(Request $request, $id)
    {
        $user = \Auth::user();

        // تاریخ‌های ارسالی از ای‌جکس
        $startTarget = $request->input('startDate');
        $endTarget   = $request->input('endDate');

        if ($startTarget && $endTarget) {
            $startDate = \Carbon\Carbon::parse($startTarget);
            $endDate   = \Carbon\Carbon::parse($endTarget);
        } else {
            $endDate   = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::now()->subDays(30);
        }

        // بازه ماه گذشته (۳۰ روز قبل از بازه فعلی)
        $lastMonthStart = $startDate->copy()->subDays(30);
        $lastMonthEnd   = $startDate->copy();

        // ✅ آمار بازه فعلی (تلفیقی)
        $stats = Product::withoutGlobalScope('withCurrentStock')
            ->whereJsonContains('products.organization_id', ["$id"])
            ->where('products.isMaterial', 0)
            ->join('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->join('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->whereIn('pf.status', [1, 4])
            ->whereBetween('pf.created_at', [$startDate, $endDate])
            ->selectRaw("
            COUNT(DISTINCT pf.id) as total_invoices,
            ROUND(SUM(
                (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price
                - (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price * (COALESCE(pfi.discount,0)/100)))
                * (1 + (COALESCE(products.tax,0)/100))
            ), 0) as total_amount
        ")
            ->first();

        // ✅ آمار بازه فعلی فقط برای status = 1 (تایید‌شده)
        $stats_status1 = Product::withoutGlobalScope('withCurrentStock')
            ->whereJsonContains('products.organization_id', ["$id"])
            ->where('products.isMaterial', 0)
            ->join('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->join('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->where('pf.status', 1)
            ->whereBetween('pf.created_at', [$startDate, $endDate])
            ->selectRaw("
            COUNT(DISTINCT pf.id) as total_invoices_status1,
            ROUND(SUM(
                (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price
                - (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price * (COALESCE(pfi.discount,0)/100)))
                * (1 + (COALESCE(products.tax,0)/100))
            ), 0) as total_amount_status1
        ")
            ->first();

        // ✅ آمار بازه فعلی فقط برای status = 4 (تحویل‌شده)
        $stats_status4 = Product::withoutGlobalScope('withCurrentStock')
            ->whereJsonContains('products.organization_id', ["$id"])
            ->where('products.isMaterial', 0)
            ->join('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->join('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->where('pf.status', 4)
            ->whereBetween('pf.created_at', [$startDate, $endDate])
            ->selectRaw("
            COUNT(DISTINCT pf.id) as total_invoices_status4,
            ROUND(SUM(
                (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price
                - (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price * (COALESCE(pfi.discount,0)/100)))
                * (1 + (COALESCE(products.tax,0)/100))
            ), 0) as total_amount_status4
        ")
            ->first();

        // ✅ آمار ماه گذشته (تلفیقی)
        $lastMonthStats = Product::withoutGlobalScope('withCurrentStock')
            ->whereJsonContains('products.organization_id', ["$id"])
            ->where('products.isMaterial', 0)
            ->join('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->join('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->whereIn('pf.status', [1, 4])
            ->whereBetween('pf.created_at', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw("
            COUNT(DISTINCT pf.id) as last_month_invoices,
            ROUND(SUM(
                (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price
                - (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price * (COALESCE(pfi.discount,0)/100)))
                * (1 + (COALESCE(products.tax,0)/100))
            ), 0) as last_month_total
        ")
            ->first();

        // ✅ آمار ماه گذشته فقط برای status = 1
        $lastMonthStats_status1 = Product::withoutGlobalScope('withCurrentStock')
            ->whereJsonContains('products.organization_id', ["$id"])
            ->where('products.isMaterial', 0)
            ->join('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->join('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->where('pf.status', 1)
            ->whereBetween('pf.created_at', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw("
            COUNT(DISTINCT pf.id) as last_month_invoices_status1,
            ROUND(SUM(
                (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price
                - (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price * (COALESCE(pfi.discount,0)/100)))
                * (1 + (COALESCE(products.tax,0)/100))
            ), 0) as last_month_total_status1
        ")
            ->first();

        // ✅ آمار ماه گذشته فقط برای status = 4
        $lastMonthStats_status4 = Product::withoutGlobalScope('withCurrentStock')
            ->whereJsonContains('products.organization_id', ["$id"])
            ->where('products.isMaterial', 0)
            ->join('pish_factor_items as pfi', 'products.id', '=', 'pfi.pr_id')
            ->join('pishfactors as pf', 'pfi.pishfactor_id', '=', 'pf.id')
            ->where('pf.status', 4)
            ->whereBetween('pf.created_at', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw("
            COUNT(DISTINCT pf.id) as last_month_invoices_status4,
            ROUND(SUM(
                (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price
                - (((pfi.pack * products.pack_items) + pfi.tedad) * pfi.price * (COALESCE(pfi.discount,0)/100)))
                * (1 + (COALESCE(products.tax,0)/100))
            ), 0) as last_month_total_status4
        ")
            ->first();

        return response()->json([
            // 🎯 داده‌های فعلی شما بدون هیچ تغییری
            'total_invoices'       => $stats->total_invoices ?? 0,
            'total_amount'         => $stats->total_amount ?? 0,
            'last_month_invoices'  => $lastMonthStats->last_month_invoices ?? 0,
            'last_month_total'     => $lastMonthStats->last_month_total ?? 0,

            // 🆕 داده‌های جداگانه برای status = 1 و 4
            'invoices_Accepted'     => $stats_status1->total_invoices_status1 ?? 0,
            'amount_Accepted'       => $stats_status1->total_amount_status1 ?? 0,
            'invoices_Completed'     => $stats_status4->total_invoices_status4 ?? 0,
            'amount_Completed'       => $stats_status4->total_amount_status4 ?? 0,

            'last_invoices_Accepted' => $lastMonthStats_status1->last_month_invoices_status1 ?? 0,
            'last_amount_Accepted'   => $lastMonthStats_status1->last_month_total_status1 ?? 0,
            'last_invoices_Completed' => $lastMonthStats_status4->last_month_invoices_status4 ?? 0,
            'last_amount_Completed'   => $lastMonthStats_status4->last_month_total_status4 ?? 0,

            // بازه‌ها برای اطمینان
            'period_start' => $startDate->toDateString(),
            'period_end'   => $endDate->toDateString(),
        ]);
    }


    public function changeInfoGet()
    {
        $user = \Auth::user();
        return view('auth.changeInfo', compact('user'));
    }

    public function changeInfoPost(Request $request)
    {
        $user = \Auth::user();
        $user->update([
            'name' => $request->name
        ]);
        if (isset($request->password)) {
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            $user->update([
                'password' => Hash::make($request['password']),
            ]);
        }
        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        return back();
    }
}
