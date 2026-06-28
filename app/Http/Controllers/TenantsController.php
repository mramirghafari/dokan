<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\Organization;
use App\Models\User;
use App\Models\Tenants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class TenantsController extends Controller
{
    private const SUBSCRIPTION_TYPES = [
        'demo_3_days' => ['label' => 'دموی 3 روزه', 'days' => 3],
        '1_month' => ['label' => '1 ماهه', 'months' => 1],
        '3_months' => ['label' => '3 ماهه', 'months' => 3],
        '6_months' => ['label' => '6 ماهه', 'months' => 6],
        '1_year' => ['label' => '1 ساله', 'months' => 12],
        '2_years' => ['label' => '2 ساله', 'months' => 24],
        '3_years' => ['label' => '3 ساله', 'months' => 36],
        '5_years' => ['label' => '5 ساله', 'months' => 60],
        'permanent' => ['label' => 'دائمی', 'months' => null],
    ];

    public function __construct()
    {
        $this->middleware('can:tenants,user')->only(['index', 'store', 'edit', 'update']);
    }


    public function index()
    {
        $user = Auth::user();

        if (!$user || $user->isGod != 1) {
            abort(403);
        }

        $Tenants = Tenants::orderBy('id', 'desc')
            ->get()
            ->map(function ($tenant) {
                $tenant->dashboard_stats = $this->tenantStats($tenant);
                return $tenant;
            });

        $subscriptionOptions = self::SUBSCRIPTION_TYPES;
        $activeTenantsCount = $Tenants->where('status', 1)->count();
        $inactiveTenantsCount = $Tenants->where('status', 0)->count();

        return view('tenants.index', compact('Tenants', 'subscriptionOptions', 'activeTenantsCount', 'inactiveTenantsCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'subscription_type' => ['required', Rule::in(array_keys(self::SUBSCRIPTION_TYPES))],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'admin_mobile' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/', 'unique:users,mobile'],
            'admin_email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_national_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'admin_address' => ['nullable', 'string', 'max:1000'],
            'admin_postal_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'admin_password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'نام پنل الزامی است.',
            'legal_name.required' => 'نام شرکت الزامی است.',
            'phone.regex' => 'شماره تماس پنل فقط باید شامل عدد باشد.',
            'subscription_type.required' => 'نوع اشتراک پنل الزامی است.',
            'admin_name.required' => 'نام مدیر کل پنل الزامی است.',
            'admin_username.required' => 'نام کاربری مدیر کل پنل الزامی است.',
            'admin_username.unique' => 'این نام کاربری قبلا ثبت شده است.',
            'admin_mobile.regex' => 'شماره همراه مدیر کل فقط باید شامل عدد باشد.',
            'admin_mobile.unique' => 'این شماره همراه قبلا برای کاربر دیگری ثبت شده است.',
            'admin_email.email' => 'فرمت ایمیل مدیر کل صحیح نیست.',
            'admin_email.unique' => 'این ایمیل قبلا برای کاربر دیگری ثبت شده است.',
            'admin_national_code.regex' => 'کد ملی فقط باید شامل عدد باشد.',
            'admin_postal_code.regex' => 'کد پستی فقط باید شامل عدد باشد.',
            'admin_password.required' => 'رمز عبور مدیر کل پنل الزامی است.',
            'admin_password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
        ]);

        $user = Auth::user();

        $tenant = DB::transaction(function () use ($request, $user) {
            $tenant = Tenants::create([
                'name' => $request->name,
                'display_name' => $request->name,
                'legal_name' => $request->legal_name,
                'phone' => $request->phone,
                'mobile' => $request->admin_mobile,
                'subscription_type' => $request->subscription_type,
                'subscription_started_at' => Carbon::today(),
                'subscription_ends_at' => $this->subscriptionEndsAt($request->subscription_type),
                'status' => 1,
                'created_by' => $user ? $user->id : null,
            ]);

            $organization = Organization::create([
                'title' => $request->legal_name ?: $request->name,
                'description' => 'شعبه مرکزی ' . $request->name,
                'type' => 1,
                'isActive' => 1,
                'tenants_id' => $tenant->id,
                'tenant_id' => $tenant->id,
            ]);

            User::create([
                'name' => $request->admin_name,
                'username' => $request->admin_username,
                'email' => $request->admin_email,
                'mobile' => $request->admin_mobile,
                'personalID' => $request->admin_national_code,
                'national_code' => $request->admin_national_code,
                'address' => $request->admin_address,
                'postal_code' => $request->admin_postal_code,
                'password' => Hash::make($request->admin_password),
                'organization_id' => $organization->id,
                'tenants_id' => $tenant->id,
                'tenant_id' => $tenant->id,
                'isActive' => 1,
                'isAdmin' => 1,
                'isGod' => 0,
            ]);

            return $tenant;
        });

        ActivityLogService::safeLogModel('create', 'پنل و مدیر کل آن ایجاد شد' . '-' . $tenant->name, $tenant, ['section' => 'organization', 'event_key' => 'tenant.create']);

        Alert::success('تشکر', 'پنل و مدیر کل آن با موفقیت ایجاد شد');
        return back();
    }

    public function edit($id)
    {
        $Tenant = Tenants::findOrFail($id);
        $stats = $this->tenantStats($Tenant);
        $admins = User::where(function ($query) use ($Tenant) {
            $query->where('tenant_id', $Tenant->id)
                ->orWhere('tenants_id', $Tenant->id);
        })->where('isAdmin', 1)->orderBy('name')->get();

        $subscriptionOptions = self::SUBSCRIPTION_TYPES;

        return view('tenants.edit', compact('Tenant', 'stats', 'admins', 'subscriptionOptions'));
    }

    public function update(Request $request, Tenants $Tenant)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'subscription_type' => ['nullable', Rule::in(array_keys(self::SUBSCRIPTION_TYPES))],
            'wallet_balance' => ['nullable', 'numeric', 'min:0'],
            'sms_unit_price_toman' => ['nullable', 'numeric', 'min:0'],
        ], [
            'name.required' => 'نام پنل الزامی است.',
            'phone.regex' => 'شماره تماس پنل فقط باید شامل عدد باشد.',
        ]);

        $subscriptionType = $request->subscription_type ?: $Tenant->subscription_type;
        $subscriptionChanges = [
            'subscription_type' => $subscriptionType,
        ];

        if ($subscriptionType && ($subscriptionType !== $Tenant->subscription_type || !$Tenant->subscription_started_at)) {
            $subscriptionChanges['subscription_started_at'] = Carbon::today();
            $subscriptionChanges['subscription_ends_at'] = $this->subscriptionEndsAt($subscriptionType);
        }

        $Tenant->update([
            'name' => $request->name,
            'display_name' => $request->name,
            'legal_name' => $request->legal_name,
            'phone' => $request->phone,
            'unit_order' => $request->unit_order,
            'sub_order' => $request->sub_order,
            'currency_type' => $request->currency_type,
            'wallet_balance' => $request->wallet_balance ?? 0,
            'sms_unit_price_toman' => $request->sms_unit_price_toman ?? 0,
            'tozihat' => $request->tozihat,
            'status' => (int) $request->status === 1 ? 1 : 0,
        ] + $subscriptionChanges);
        $user = Auth::user();
        ActivityLogService::safeLogModel('update', 'پنل ویرایش شد' . '-' . $Tenant->name, $Tenant, ['section' => 'organization', 'event_key' => 'tenant.update']);

        Alert::success('تشکر', 'شعبه با موفقیت ویرایش شد');
        return redirect()->back();
    }




    public function destroy($id)
    {

        $user = Auth::user();
        $Tenants = Tenants::findOrFail($id);
        $Tenants->delete();

        ActivityLogService::safeLogModel('delete', ' پنل توسط ' . $user->name . ' حذف شد' . '-' . $Tenants->title, $Tenants, ['section' => 'organization', 'event_key' => 'tenant.deleted']);


        return back();
    }

    private function tenantStats(Tenants $tenant): array
    {
        $usersCount = $this->countTenantRows('users', $tenant->id);
        $organizationsCount = $this->countTenantRows('organizations', $tenant->id);
        $customersCount = $this->countTenantRows('customers', $tenant->id);
        $citiesCount = $this->countTenantRows('cities', $tenant->id);
        $regionsCount = $this->countTenantRows('regions', $tenant->id);
        $areasCount = $this->countTenantRows('areas', $tenant->id);
        $pishfactorQuery = $this->tenantQuery('pishfactors', $tenant->id);
        $pishfactorsCount = $pishfactorQuery ? (clone $pishfactorQuery)->count() : 0;
        $totalSales = 0;

        if ($pishfactorQuery && Schema::hasColumn('pishfactors', 'fullPrice')) {
            $totalSalesQuery = clone $pishfactorQuery;

            if (Schema::hasColumn('pishfactors', 'status')) {
                $totalSalesQuery->whereIn('status', [1, 4]);
            }

            $totalSales = (float) $totalSalesQuery->sum('fullPrice');
        }

        return [
            'users_count' => $usersCount,
            'organizations_count' => $organizationsCount,
            'customers_count' => $customersCount,
            'pishfactors_count' => $pishfactorsCount,
            'total_sales' => $totalSales,
            'cities_count' => $citiesCount,
            'regions_count' => $regionsCount,
            'areas_count' => $areasCount,
            'geo_summary' => $citiesCount . ' شهر / ' . $regionsCount . ' منطقه / ' . $areasCount . ' مسیر',
        ];
    }

    private function subscriptionEndsAt(?string $subscriptionType): ?Carbon
    {
        $days = self::SUBSCRIPTION_TYPES[$subscriptionType]['days'] ?? null;
        $months = self::SUBSCRIPTION_TYPES[$subscriptionType]['months'] ?? null;

        if ($days) {
            return Carbon::today()->addDays($days);
        }

        return $months ? Carbon::today()->addMonthsNoOverflow($months) : null;
    }

    private function countTenantRows(string $table, int $tenantId): int
    {
        $query = $this->tenantQuery($table, $tenantId);

        return $query ? (int) $query->count() : 0;
    }

    private function tenantQuery(string $table, int $tenantId)
    {
        if (!Schema::hasTable($table)) {
            return null;
        }

        $hasTenantId = Schema::hasColumn($table, 'tenant_id');
        $hasLegacyTenantId = Schema::hasColumn($table, 'tenants_id');

        if (!$hasTenantId && !$hasLegacyTenantId) {
            return null;
        }

        return DB::table($table)->where(function ($query) use ($table, $tenantId, $hasTenantId, $hasLegacyTenantId) {
            if ($hasTenantId) {
                $query->where($table . '.tenant_id', $tenantId);
            }

            if ($hasLegacyTenantId) {
                $method = $hasTenantId ? 'orWhere' : 'where';
                $query->{$method}($table . '.tenants_id', $tenantId);
            }
        });
    }
}
