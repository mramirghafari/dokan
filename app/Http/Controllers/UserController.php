<?php

namespace App\Http\Controllers;

use App\Models\Tenants;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Store;
use App\Models\Pishfactor;
use App\Models\Targets;
use App\Models\Tasks;
use App\Models\Customers;
use App\Services\ActivityLogService;
use App\Services\PermissionScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Hekmatinasser\Verta\Verta;
use Kavenegar\Laravel\Facade as Kavenegar;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:users,user')->only(['index', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
    }

    public function index(Request $request)
    {
        $User = auth()->user();
        $scopeService = app(PermissionScopeService::class);
        $targetTenantId = $scopeService->targetTenantId($request);
        $scopeLabels = $scopeService->scopeLabels();
        $scopeOptions = $scopeService->scopeOptions($targetTenantId);
        $selectedUserScopes = collect($scopeLabels)->mapWithKeys(fn($label, $type) => [$type => []])->toArray();
        $leader_role = Role::where('title', 'leader')->first();
        $expert_role = Role::where('title', 'expert')->first();
        $leaderRoleIds = collect([$leader_role?->id, $expert_role?->id])->filter()->values()->all();
        $leader_Users = $leaderRoleIds ? DB::table('role_user')->whereIn('role_id', $leaderRoleIds)->pluck('user_id')->toArray() : [];
        $usersBaseQuery = $User->isGod == 1 ? User::query() : User::forOrganizations($User);
        if ($User->isGod == 1) {
            $Leaders = User::whereIn('id', $leader_Users)->select('id', 'name')->orderBy('name')->limit(300)->get();
            $organizations = Organization::all();
        } elseif ($User->isGod == 0 && $User->isAdmin == 1) {
            $Leaders = User::whereIn('id', $leader_Users)->forOrganizations($User)->select('id', 'name')->orderBy('name')->limit(300)->get();
            $organizations = Organization::forOrganizations($User, 'id')->get();
        } else {
            $Leaders = User::whereIn('id', $leader_Users)->forOrganizations($User)->select('id', 'name')->orderBy('name')->limit(300)->get();
            $organizations = Organization::forOrganizations($User, 'id')->get();
        }
        $roles = $scopeService->rolesForUser($User)->with('scopes')->orderBy('description')->get();
        $usersTotal = (clone $usersBaseQuery)->count();
        $activeUsersCount = (clone $usersBaseQuery)->where('isActive', 1)->count();
        $deactiveUsersCount = (clone $usersBaseQuery)->where('isActive', 0)->count();
        $users = (clone $usersBaseQuery)
            ->select(['id', 'username', 'name', 'mobile', 'email', 'isActive', 'organization_id', 'tenants_id'])
            ->with(['roles:id,title,description'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->q);
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                        ->orWhere('username', 'like', '%' . $term . '%')
                        ->orWhere('mobile', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->when($request->filled('status'), fn($query) => $query->where('isActive', (int) $request->status))
            ->when($request->filled('role_id'), fn($query) => $query->whereHas('roles', fn($roleQuery) => $roleQuery->where('roles.id', $request->role_id)))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();
        $userScopeSummaries = $users->getCollection()->mapWithKeys(fn($user) => [$user->id => $scopeService->describeUserScopes($user)]);

        return view('users.index', compact('users', 'roles', 'organizations', 'Leaders', 'usersTotal', 'activeUsersCount', 'deactiveUsersCount', 'scopeLabels', 'scopeOptions', 'selectedUserScopes', 'userScopeSummaries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:6'],
            'organization_id' => ['required'],
            'role_id' => ['required'],
        ], [
            'name.required' => 'نام کاربر الزامی است.',
            'email.required' => 'ایمیل کاربر الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.unique' => 'کاربری با این ایمیل قبلا ثبت شده است.',
            'mobile.required' => 'شماره همراه کاربر الزامی است.',
            'mobile.unique' => 'کاربری با این شماره همراه قبلا ثبت شده است.',
            'password.required' => 'رمز عبور کاربر الزامی است.',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
            'organization_id.required' => 'انتخاب شعبه الزامی است.',
            'role_id.required' => 'انتخاب نقش کاربری الزامی است.',
        ]);

        $currentUser = auth()->user();
        $scopeService = app(PermissionScopeService::class);
        $canChooseTenant = $currentUser->isGod == 1 || strcasecmp((string) $currentUser->email, 'admin@almas.com') === 0;
        $organizationId = $canChooseTenant ? $request->organization_id : $currentUser->organization_id;
        $tenantId = $canChooseTenant ? $request->tenants_id : $currentUser->tenants_id;

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization) {
                $tenantId = $organization->tenants_id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'personalID' => $request->password,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'tenants_id' => $tenantId,
            'organization_id' => $organizationId,
            'leader_id' => $request->leader_id,
            'password' => bcrypt($request->password)
        ]);
        $user->roles()->sync($request->role_id);
        app(\App\Services\PanelMembershipService::class)->syncMembership($user, (int) $tenantId, (int) $organizationId ?: null);
        $scopeService->syncUserScopes($user, $tenantId, $this->scopesFromRequest($request, $organizationId), $currentUser->id);

        ActivityLogService::logModel('create', 'یک کاربر ایجاد شد' . '-' . $user->name, $user);

        try {
            $receptor = $user->mobile;
            $token = "$user->email";
            $token2 = "$request->password";
            $token3 = "";
            $template = "UserWelcome";
            //Send null for tokens not defined in the template
            //Pass token10 and token20 as parameter 6th and 7th
            $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
            if ($result) {
                foreach ($result as $r) {
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
        } catch (\Kavenegar\Exceptions\ApiException $e) {
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            echo $e->errorMessage();
        } catch (\Kavenegar\Exceptions\HttpException $e) {
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            echo $e->errorMessage();
        }

        if ($request->has('leader_id') && $request->leader_id != null) {


            try {
                $Leader = User::find($request->leader_id);
                $receptor = $Leader->mobile;
                $token = str_replace(" ", "_", $user->name);
                $token2 = "";
                $token3 = "";
                $template = "LeaderNotif";
                //Send null for tokens not defined in the template
                //Pass token10 and token20 as parameter 6th and 7th
                $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
                if ($result) {
                    foreach ($result as $r) {
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
            } catch (\Kavenegar\Exceptions\ApiException $e) {
                // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
                echo $e->errorMessage();
            } catch (\Kavenegar\Exceptions\HttpException $e) {
                // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
                echo $e->errorMessage();
            }
        }

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(User $user)
    {

        $Tenants = Tenants::all();
        $users = User::all();
        $scopeService = app(PermissionScopeService::class);
        $targetTenantId = $user->tenant_id ?: $user->tenants_id ?: $scopeService->tenantIdForUser(auth()->user());
        $roles = $scopeService->rolesForUser(auth()->user())->with('scopes')->orderBy('description')->get();
        $scopeLabels = $scopeService->scopeLabels();
        $scopeOptions = $scopeService->scopeOptions($targetTenantId);
        $selectedUserScopes = $scopeService->userScopeValues($user);
        $userEdit = $user;

        $leaderRoleIds = Role::whereIn('title', ['leader', 'expert'])->pluck('id')->toArray();
        $leader_Users = $leaderRoleIds ? DB::table('role_user')->whereIn('role_id', $leaderRoleIds)->pluck('user_id')->toArray() : [];
        $editedUserTenantId = $user->tenants_id ?: $user->tenant_id;
        $Leaders = User::whereIn('id', $leader_Users)
            ->where('tenants_id', $editedUserTenantId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $role = $user->roles()->first();
        $roleTitle = $role ? $role->title : '-';
        $UserFactors = $this->getUserFactors($user->id, $roleTitle, null, null);

        if (auth()->user()->isGod == 1) {
            $Stores = Store::all();
            $organizations = Organization::all();
        } else {
            $Stores = Store::forOrganizations(auth()->user())->get();
            $organizations = Organization::forOrganizations(auth()->user(), 'id')->get();
        }


        return view('users.edit', compact('users', 'userEdit', 'roles', 'organizations', 'Leaders', 'Tenants', 'UserFactors', 'Stores', 'scopeLabels', 'scopeOptions', 'selectedUserScopes'));
    }

    public function updateU(Request $request, User $user)
    {


        $orgId = $request->input('organization_id');

        $organizationId = is_array($orgId) && count($orgId) === 1 ? reset($orgId) : $orgId;


        $Role = Role::find($request->role_id);
        $scopeService = app(PermissionScopeService::class);

        $isActive = $request->input('isActive') == "on" ? 1 : 0;
        if ($request->has('tenants_id')) {
            $user->update([
                'tenants_id' => $request->tenants_id
            ]);
            app(\App\Services\PanelMembershipService::class)->syncMembership(
                $user,
                (int) $request->tenants_id,
                is_numeric($organizationId) ? (int) $organizationId : null,
                (int) $user->isAdmin === 1
            );
        }
        $user->update([
            'name' => $request->name,
            'personalID' => $request->password,
            'username' => $request->username,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'organization_id' => $organizationId,
            'leader_id' => $request->leader_id,
            'isActive' => $isActive
        ]);
        if ($request->password != null) {
            $user->update([
                'password' => bcrypt($request->password)
            ]);
        }

        if ($user->leader_id != $request->leader_id) {

            try {
                $Leader = User::find($request->leader_id);
                $receptor = $Leader->mobile;
                $token = str_replace(" ", "_", $user->name);
                $token2 = "";
                $token3 = "";
                $template = "LeaderNotif";
                //Send null for tokens not defined in the template
                //Pass token10 and token20 as parameter 6th and 7th
                $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
                if ($result) {
                    foreach ($result as $r) {
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
            } catch (\Kavenegar\Exceptions\ApiException $e) {
                // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
                echo $e->errorMessage();
            } catch (\Kavenegar\Exceptions\HttpException $e) {
                // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
                echo $e->errorMessage();
            }
        }
        // $userEdit->roles()->sync($request->role_id,$userEdit->id);
        DB::table('role_user')
            ->where('user_id', $user->id)
            ->update(['role_id' => $request['role_id']]);
        $scopeService->syncUserScopes($user, $user->tenant_id ?: $user->tenants_id, $this->scopesFromRequest($request, $organizationId), auth()->id());


        if ($Role->title == 'driver') {
            $box_count_driver = $request->box_count_driver;
            $weight_count_driver = $request->weight_count_driver;


            $Cargo = Cargo::updateOrCreate(
                ['driver_id' => $user->id],
                [
                    'cartons'        => $box_count_driver,
                    'weight'  => $weight_count_driver,
                ]
            );
        }


        ActivityLogService::logModel('update', 'کاربر ویرایش شد' . '-' . $user->name, $user);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return back();
    }



    public function profile() {}

    public function profileUpdate() {}

    private function getSubUsersWithFactors($leaderId, $start, $end)
    {
        $subs = User::where('leader_id', $leaderId)->get();
        $result = [];
        $AllFactorPrices = 0;
        foreach ($subs as $sub) {
            // گرفتن رول یوزر (فرض بر این است که relation roles تعریف شده)
            $role = $sub->roles()->first();
            $roleTitle = $role ? $role->title : '-';

            if ($roleTitle == 'leader') {
                // گرفتن تعداد فاکتورهای یوزر
                if ($start != null && $end != null) {
                    $factorsCount = Pishfactor::where('sarparast_id', $sub->id)
                        ->whereBetween('created_at', [$start, $end])
                        ->get();
                } else {
                    $factorsCount = Pishfactor::where('sarparast_id', $sub->id)->get();
                }
            }

            if ($roleTitle == 'visitor') {
                // گرفتن تعداد فاکتورهای یوزر
                if ($start != null && $end != null) {
                    $factorsCount = Pishfactor::where('visitor_id', $sub->id)
                        ->whereBetween('created_at', [$start, $end])
                        ->get();
                } else {
                    $factorsCount = Pishfactor::where('visitor_id', $sub->id)->get();
                }
            }
            $FactorPrices = 0;
            foreach ($factorsCount as $factor) {
                $FactorPrices += intval(str_replace(',', '', $factor->fullPrice));
            }
            $AllFactorPrices += $FactorPrices;



            // بازگشتی برای زیرمجموعه‌ها
            $children = $this->getSubUsersWithFactors($sub->id, $start, $end);

            $result[] = [
                'id' => $sub->id,
                'name' => $sub->name,
                'role' => $roleTitle,
                'factors_count' => count($factorsCount),
                'factors' => $factorsCount,
                'children' => $children,
                'FactorPrices' => $FactorPrices
            ];
        }

        // سورت بر اساس تعداد فاکتور
        usort($result, function ($a, $b) {
            return $b['factors_count'] <=> $a['factors_count'];
        });

        return $result;
    }

    private function scopesFromRequest(Request $request, $organizationId): array
    {
        $scopes = $request->input('scopes', []);
        $organizationIds = collect(is_array($organizationId) ? $organizationId : [$organizationId])
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        if (empty($scopes['organization']) && $organizationIds->isNotEmpty()) {
            $scopes['organization'] = $organizationIds->all();
        }

        return $scopes;
    }

    private function getUserFactors($userId, $role, $start, $end)
    {

        $AllFactorPrices = 0;

        if ($role == 'leader') {
            if ($start != null && $end != null) {
                $factorsCount = Pishfactor::where('sarparast_id', $userId)
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            } else {
                $factorsCount = Pishfactor::where('sarparast_id', $userId)->get();
            }
        } elseif ($role == 'visitor') {
            if ($start != null && $end != null) {
                $factorsCount = Pishfactor::where('visitor_id', $userId)
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            } else {
                $factorsCount = Pishfactor::where('visitor_id', $userId)->get();
            }
        } else {
            $factorsCount = 0;
            $AllFactorPrices = 0;
        }

        $result = array(
            'factors_count' => is_array($factorsCount) ? count($factorsCount) : 0,
            'factors' => $factorsCount,
            'FactorPrices' => $AllFactorPrices
        );

        return $result;
    }

    public function userInvoiceList(Request $request, User $user)
    {
        $roleTitles = $user->roles->pluck('title')->all();
        $isVisitor = in_array('visitor', $roleTitles);
        $isLeader = in_array('leader', $roleTitles);
        $isManager = in_array('expert', $roleTitles);
        $hasOrganizationAccess = $isManager || $user->isAdmin == 1 || $user->isGod == 1;

        $Target = Targets::where('status', 1)->where('user_id', $user->id)->first();

        $MyTasks = collect();
        $AllFactors = collect();
        $AllFactorCount = 0;
        $AllFactorPrices = 0;
        $AcceptedFactorFullPrices = 0;
        $CompletedFactorFullPrices = 0;

        if ($isLeader) {
            $baseFactorsQuery = Pishfactor::where('sarparast_id', $user->id)
                ->whereIn('status', [1, 4]);

            $MyTasks = Tasks::where('leader_id', $user->id)->where('status', 1)->get();
        } elseif ($isVisitor) {
            $baseFactorsQuery = Pishfactor::where('visitor_id', $user->id)
                ->whereIn('status', [1, 4]);

            $MyTasks = Tasks::where('user_id', $user->id)->where('status', 1)->get();
        } elseif ($hasOrganizationAccess) {
            $baseFactorsQuery = Pishfactor::forOrganizations($user)
                ->whereIn('status', [1, 4]);
        } else {
            $baseFactorsQuery = Pishfactor::whereIn('status', [1, 4])
                ->whereRaw('1 = 0');
        }

        $PishFactors = clone $baseFactorsQuery;
        if ($Target) {
            $PishFactors->whereBetween('created_at', [$Target->start_date_en, Carbon::today()]);
        }

        $AllFactorsQuery = clone $baseFactorsQuery;
        if ($Target) {
            $AllFactorsQuery->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en]);
        }
        $AllFactors = $AllFactorsQuery->get();
        $AllFactorCount = $AllFactors->count();
        $AllFactorPrices = $AllFactors->sum(function ($item) {
            return (float) str_replace(',', '', $item->fullPrice);
        });

        $acceptedFactorsQuery = clone $baseFactorsQuery;
        $acceptedFactorsQuery->where('status', 1);
        if ($Target) {
            $acceptedFactorsQuery->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en]);
        }
        $AcceptedFactorFullPrices = $acceptedFactorsQuery->get()->sum(function ($item) {
            return (float) str_replace(',', '', $item->fullPrice);
        });

        $completedFactorsQuery = clone $baseFactorsQuery;
        $completedFactorsQuery->where('status', 4);
        if ($Target) {
            $completedFactorsQuery->whereBetween('created_at', [$Target->start_date_en, $Target->end_date_en]);
        }
        $CompletedFactorFullPrices = $completedFactorsQuery->get()->sum(function ($item) {
            return (float) str_replace(',', '', $item->fullPrice);
        });

        // =====================
        // فیلتر بر اساس تاریخ ایجاد فاکتور
        // =====================
        if ($request->from_date && $request->to_date) {
            $fromDate = str_replace("/", "-", $request->get('from_date'));
            $jalaliFrom = explode("-", $fromDate);
            $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
            $ymF = $miladiFrom[0];
            $mmF = strlen($miladiFrom[1]) == 1 ? "0" . $miladiFrom[1] : $miladiFrom[1];
            $dmF = strlen($miladiFrom[2]) == 1 ? "0" . $miladiFrom[2] : $miladiFrom[2];

            $toDate = str_replace("/", "-", $request->get('to_date'));
            $jalaliTo = explode("-", $toDate);
            $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
            $ymT = $miladiTo[0];
            $mmT = strlen($miladiTo[1]) == 1 ? "0" . $miladiTo[1] : $miladiTo[1];
            $dmT = strlen($miladiTo[2]) == 1 ? "0" . $miladiTo[2] : $miladiTo[2];

            $PishFactors->whereBetween('created_at', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"]);
        }

        // =====================
        // فیلتر بر اساس تاریخ تحویل فاکتور
        // =====================
        if ($request->delivery_from_date && $request->delivery_to_date) {
            $fromDate = str_replace("/", "-", $request->get('delivery_from_date'));
            $jalaliFrom = explode("-", $fromDate);
            $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
            $ymF = $miladiFrom[0];
            $mmF = strlen($miladiFrom[1]) == 1 ? "0" . $miladiFrom[1] : $miladiFrom[1];
            $dmF = strlen($miladiFrom[2]) == 1 ? "0" . $miladiFrom[2] : $miladiFrom[2];

            $toDate = str_replace("/", "-", $request->get('delivery_to_date'));
            $jalaliTo = explode("-", $toDate);
            $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
            $ymT = $miladiTo[0];
            $mmT = strlen($miladiTo[1]) == 1 ? "0" . $miladiTo[1] : $miladiTo[1];
            $dmT = strlen($miladiTo[2]) == 1 ? "0" . $miladiTo[2] : $miladiTo[2];

            $PishFactors->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF 00:00:00", "$ymT-$mmT-$dmT 23:59:59"]);
        }

        // در نهایت کوئری اجرا شود
        $PishFactors = $PishFactors->get();

        // محاسبه جمع قیمت‌ها
        $totalPrice = $AllFactors->sum(function ($item) {
            return (float)str_replace(',', '', $item->fullPrice);
        });

        $cur_user = $user;
        return view('users.invoiceList', compact('PishFactors', 'AllFactors', 'totalPrice', 'isVisitor', 'isLeader', 'cur_user', 'MyTasks', 'AllFactorCount', 'Target', 'AllFactorPrices', 'AcceptedFactorFullPrices', 'CompletedFactorFullPrices'));
    }

    public function destroy(User $user)
    {

        $me = Auth::user();

        $user->delete();
        ActivityLogService::logModel('delete', 'کاربر حذف شد' . '-' . $user->name, $user);

        Alert::success('تشکر', 'کاربر با موفقیت حذف شد');
        return redirect()->route('users.index');
    }
}
