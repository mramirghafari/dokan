<?php

namespace App\Http\Controllers;

use App\Services\UserHierarchyService;
use App\Services\TenantSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\Product;
use App\Models\Pishfactor;
use App\Models\Targets;
use App\Models\Tasks;
use App\Models\TargetProducts;
use App\Models\Region;
use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use App\Models\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Kavenegar;
use Hekmatinasser\Verta\Verta;


class TargetController extends Controller
{
    protected UserHierarchyService $service;

    public function __construct(UserHierarchyService $service)
    {
        $this->service = $service;
        $this->middleware('can:targets,user')->only(['index', 'store', 'edit', 'update', 'show', 'destroy', 'history']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_sales_targets')) {
                Alert::warning('غیرفعال', 'تارگت گذاری فروش برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $user = \Auth::user();

        $role = Role::where('title', 'visitor')->first();

        if ($user->isGod == 1) {
            $Targets = Targets::where('status', 1)->withAchievedAmount()->get();
        } elseif ($user->isAdmin == 1) {
            $Targets = Targets::forOrganizations($user)->where('status', 1)->withAchievedAmount()->get();
        } else {
            $Targets = Targets::where('status', 1)->where('leader_id', $user->id)->withAchievedAmount()->get();
        }


        return view('targets.index', compact('Targets'));
    }

    public function create()
    {

        $user = \Auth::user();


        if ($user->isGod == 1) {
            $Users = User::all();
            $Products = Product::all();
        } elseif ($user->isAdmin == 1) {
            $Users = User::forOrganizations($user)->get();
            $Products = Product::forOrganizations($user)->get();

            $Users = $this->getSubUsers(null, $user);
        } else {

            $isManager = false;
            $isLeader = false;
            $isVisitor = false;
            foreach ($user->roles as $role) {
                if ($role->title == 'expert') {
                    $isManager = true;
                }
                if ($role->title == 'leader') {
                    $isLeader = true;
                }
                if ($role->title == 'visitor') {
                    $isVisitor = true;
                }
            }



            $Products = Product::forOrganizations($user)->get();
            $Users = $this->getSubUsers($user->id, null);
        }

        $Target = Targets::forOrganizations($user)->where('status', 1)->where('user_id', $user->id)->first();
        if ($Target) {
            $MainTargetPrice = $Target->target_price;
            $MyTargetsForSubs = Targets::forOrganizations($user)->where('status', 1)->where('leader_id', $user->id)->sum('target_price');
            $Mande = $MainTargetPrice - $MyTargetsForSubs;
        } else {
            $MainTargetPrice = 0;
            $MyTargetsForSubs = 0;
            $Mande = $MainTargetPrice - 0;
        }


        return view('targets.create', compact('Products', 'Users', 'MainTargetPrice', 'MyTargetsForSubs', 'Mande'));
    }

    public function store(Request $request)
    {

        //dd($request->all());


        try {

            $user = \Auth::user();
            $request['leader_id'] = $user->id;
            $TargetedUser = User::find($request->user_id);
            $request['organization_id'] = $TargetedUser->organization_id;

            $jalali = explode("/", $request->start_date_fa);
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
            $request['start_date_en'] = "$ym-$mm-$dm 00:00:00";

            $jalali = explode("/", $request->end_date_fa);
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
            $request['end_date_en'] = "$ym-$mm-$dm 00:00:00";

            $request['target_price'] = str_replace(",", "", $request->get('target_price'));
            $request['min_order_price'] = str_replace(",", "", $request->get('min_order_price'));
            $Usere = User::find($request->user_id);
            $TargetCheck = Targets::forOrganizations($user)->where('user_id', $request->user_id)->where('status', 1)->count();
            if ($TargetCheck > 0) {
                Alert::warning('خطا در ثبت', "کاربر $Usere->name در حال حاضر تارگت فعال دارد.");
                return redirect()->route('targets.create');
            }
            $Target = Targets::create($request->all());

            if ($request->has('pr_id') && $request->pr_id != null) {
                $Ids = $request->input('pr_id');
                $order_count = $request->input('order_count');
                $price_count = $request->input('price_count');
                $x = 0;
                foreach ($Ids as $prid) {
                    if ($prid != null) {
                        $PrTarget = new TargetProducts();
                        $PrTarget->target_id = $Target->id;
                        $PrTarget->pr_id = $prid;
                        $PrTarget->order_count = $order_count[$x];
                        $PrTarget->order_price = $price_count[$x];
                        $PrTarget->status = 0;
                        $PrTarget->save();
                        $x++;
                    }
                }
            }

            /* $Uservisitor = User::find($request->user_id);
                $Cur_Area = Area::find($request->area_id);
                $area_name = str_replace(" ","",$Cur_Area->name);
                $receptor = $Uservisitor->mobile;
                $token = "$area_name";
                $token2 = "$request->date";
                $token3 = "";
                $template="NewTargetFromDokan";
                //Send null for tokens not defined in the template
                //Pass token10 and token20 as parameter 6th and 7th
                $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
                if($result){
                    foreach($result as $r){
                        echo "messageid = $r->messageid";
                        echo "message = $r->message";
                        echo "status = $r->status";
                        echo "statustext = $r->statustext";
                        echo "sender = $r->sender";
                        echo "receptor = $r->receptor";
                        echo "date = $r->date";
                        echo "cost = $r->cost";
                    }
                }
            }
            catch(\Kavenegar\Exceptions\ApiException $e){
                // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
                echo $e->errorMessage();
            }
                */
        } catch (\Kavenegar\Exceptions\HttpException $e) {
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            echo $e->errorMessage();
        }


        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => "تارگت جدید برای کاربران ایجاد شد."
        ]);

        Alert::success('تشکر', "تارگت جدید برای کاربر ایجاد شد.");
        return redirect()->route('targets.index');
    }

    public function show($id) {}

    public function edit(UserHierarchyService $service, Targets $Target)
    {


        $user = \Auth::user();


        if ($user->isGod == 1) {
            $Users = User::all();
            $Products = Product::all();
        } elseif ($user->isAdmin == 1) {
            $Users = User::forOrganizations($user)->get();
            $Products = Product::forOrganizations($user)->get();
        } else {

            $Users = $this->getSubUsers($user->id, null);
            $Products = Product::forOrganizations($user)->get();
        }

        $PrTargets = TargetProducts::where('target_id', $Target->id)->get();

        $teamTree = $service->getSubtree($Target->user_id, $Target->start_date_en, $Target->end_date_en);

        $TargerUser = User::find($Target->user_id);
        $isManager = false;
        $isLeader = false;
        $isVisitor = false;
        $isDriver = false;
        $isStore = false;
        foreach ($TargerUser->roles as $role) {
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
        }
        if ($isVisitor) {
            $AllFactors = Pishfactor::where('visitor_id', $TargerUser->id)
                ->whereIn('status', [1, 4])
                ->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en])
                ->get();
        } elseif ($isLeader) {
            $AllFactors = Pishfactor::where('sarparast_id', $TargerUser->id)
                ->whereIn('status', [1, 4])
                ->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en])
                ->get();
        } elseif ($isManager) {
            $AllFactors = Pishfactor::forOrganizations($TargerUser)
                ->whereIn('status', [1, 4])
                ->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en])
                ->get();
        } else {
            $AllFactorPrices = 0;
            $AllFactorCount = 0;
        }

        $myUsers = $this->getSubUsersWithFactors($TargerUser->id, $Target);


        $AllFactorCount = count($AllFactors);

        $AllFactorPrices = 0;
        $PatPrices = 0;
        foreach ($AllFactors as $factor) {
            $AllFactorPrices += intval(str_replace(',', '', $factor->fullPrice));
            $PatPrices += intval(str_replace(',', '', $factor->pat_price));
        }

        $MyFactorsCount = Pishfactor::where('visitor_id', $TargerUser->id)->count();
        $MyAcceptedFactorsCount = Pishfactor::where('visitor_id', $TargerUser->id)->whereIn('status', [1, 4])->count();


        return view('targets.edit', compact('Products', 'Users', 'Target', 'PrTargets', 'myUsers', 'AllFactorPrices', 'AllFactorCount', 'AllFactors', 'teamTree'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Targets $Target)
    {

        $user = \Auth::user();
        $request->status == "on" ? $request->status = 1 : $request->status = 0;
        $user = \Auth::user();
        $request['leader_id'] = $user->id;
        $request['organization_id'] = $user->organization_id;

        $jalali = explode("/", $request->start_date_fa);
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
        $start_date_en = "$ym-$mm-$dm 00:00:00";


        $jalali = explode("/", $request->end_date_fa);
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
        $end_date_en = "$ym-$mm-$dm 00:00:00";

        $Usere = User::find($request->user_id);
        if ($request->user_id != $Target->user_id) {
            $TargetCheck = Targets::where('user_id', $request->user_id)->where('status', 1)->count();
            if ($TargetCheck > 0) {
                Alert::warning('خطا در ثبت', "کاربر $Usere->name در حال حاضر تارگت فعال دارد.");
                return redirect()->route('targets.edit', $Target->id);
            }
        }

        $Target->update([
            'leader_id' => $user->id,
            'user_id' => $request->user_id,
            'target_price' => str_replace(',', '', $request->target_price),
            'orders_count' => $request->orders_count,
            'min_order_price' => str_replace(',', '', $request->min_order_price),
            'start_date_fa' => $request->start_date_fa,
            'start_date_en' => $start_date_en,
            'end_date_fa' => $request->end_date_fa,
            'end_date_en' => $end_date_en,
            'status' => $request->status,
        ]);

        $PrTargets = TargetProducts::where('target_id', $Target->id)->delete();
        if ($request->has('pr_id') && $request->pr_id != null) {
            $Ids = $request->input('pr_id');
            $order_count = $request->input('order_count');
            $price_count = $request->input('order_price');
            $x = 0;
            foreach ($Ids as $prid) {
                $PrTarget = new TargetProducts();
                $PrTarget->target_id = $Target->id;
                $PrTarget->pr_id = $prid;
                $PrTarget->order_count = $order_count[$x];
                $PrTarget->order_price = $price_count[$x];
                $PrTarget->status = 0;
                $PrTarget->save();
                $x++;
            }
        }


        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'تارگت ویرایش شد'
        ]);

        Alert::success('تشکر', 'تارگت با موفقیت ویرایش شد');
        return redirect()->route('targets.edit', $Target->id);
    }

    // بهتر است تابع را به صورت private در کنترلر قرار دهید
    private function getSubUsersWithFactors($leaderId, $target)
    {
        $subs = User::where('leader_id', $leaderId)->where('isActive', 1)->get();
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
            foreach ($factorsCount as $factor) {
                $FactorPrices += intval(str_replace(',', '', $factor->fullPrice));
                $PatPrices += intval(str_replace(',', '', $factor->pat_price));
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

    public function MyTargets()
    {
        $user = \Auth::user();
        $Target = Targets::where('status', 1)->where('user_id', $user->id)->first();

        $myUsers = $this->getSubUsersWithFactors($user->id, $Target);
        $AllFactorPrices = 0;
        $AllFactorCount = 0;
        foreach ($myUsers as $user) {
            $AllFactorPrices += $user['FactorPrices'];
            $AllFactorCount += $user['factors_count'];
        }

        return view('targets.mytargets', compact('Target', 'myUsers', 'AllFactorPrices', 'AllFactorCount'));
    }

    public function history()
    {
        $user = \Auth::user();


        if ($user->isGod == 1) {
            $Targets = Targets::where('status', 0)->withAchievedAmount()->get();
        } elseif ($user->isAdmin == 1) {
            $Targets = Targets::forOrganizations($user)->where('status', 0)->withAchievedAmount()->get();
        } else {
            $Targets = Targets::where('status', 0)->where('leader_id', $user->id)->withAchievedAmount()->get();
        }


        return view('targets.history', compact('Targets'));
    }

    private function getSubUsers($leaderId, $user)
    {
        if ($leaderId != null) {
            $subs = User::where('leader_id', $leaderId)->where('isActive', 1)->get();
        } else {
            $leader_role = Role::where('title', 'leader')->first();
            $expert_role = Role::where('title', 'expert')->first();
            $leader_Users = DB::table('role_user')->whereIn('role_id', [$leader_role->id, $expert_role->id])->pluck('user_id')->toArray();
            $subs = User::forOrganizations($user)->whereIn('id', $leader_Users)->where('isActive', 1)->get();
        }

        $result = [];
        foreach ($subs as $sub) {
            // گرفتن رول یوزر (فرض بر این است که relation roles تعریف شده)
            $role = $sub->roles()->first();
            $roleTitle = $role ? $role->title : '-';


            // بازگشتی برای زیرمجموعه‌ها
            $children = $this->getSubUsers($sub->id, null);

            $result[] = [
                'id' => $sub->id,
                'name' => $sub->name,
                'role' => $roleTitle,
                'children' => $children
            ];
        }

        return $result;
    }
}
