<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Organization;
use App\Models\OrganizationScope;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Tenants;
use App\Models\Product;
use App\Services\TenantSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:stores,user')->only(['index', 'store', 'edit', 'update']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_warehouse_management')) {
                Alert::warning('غیرفعال', 'مدیریت انبار برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['index', 'store', 'edit', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();

        $user = Auth::user();
        if ($user->isGod == 1) {
            $organizations = Organization::all();
            $stores = Store::all();
            $Products = Product::where('isActive', 1)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->get();
            $stores = Store::forOrganizations($user)->get();
            $Products = Product::where('isActive', 1)->forOrganizations($user)->get();
        }

        $storeTypes = Store::STORE_TYPE_LABELS;
        $stockTrackingModes = Store::STOCK_TRACKING_LABELS;
        $transferPolicies = Store::TRANSFER_POLICY_LABELS;

        return view('stores.index', compact('stores', 'roles', 'organizations', 'Products', 'storeTypes', 'stockTrackingModes', 'transferPolicies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $this->validatedData($request);
        $organizationIds = $this->allowedOrganizationIds($data['organization_id'], $user);

        if (empty($organizationIds)) {
            Alert::warning('خطا در ثبت', 'حداقل یک شعبه معتبر برای انبار انتخاب کنید');
            return back()->withInput();
        }

        $tenantId = $this->resolveTenantId($organizationIds[0], $user);
        $store = Store::create($this->storePayload($data, $organizationIds, $tenantId, true));
        $store->roles()->sync($data['roles'] ?? []);
        $this->syncOrganizationScopes($store, $organizationIds, $tenantId);

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'انبار ایجاد شد' . '-' . $store->title
        ]);

        Alert::success('تشکر', 'انبار با موفقیت ایجاد شد');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Store $store)
    {
        $roles = Role::all();

        $user = Auth::user();
        if ($user->isGod == 1) {
            $organizations = Organization::all();
            $stores = Store::all();
            $Products = Product::where('isActive', 1)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->get();
            $stores = Store::forOrganizations($user)->get();
            $Products = Product::where('isActive', 1)->forOrganizations($user)->get();
        }

        $storeTypes = Store::STORE_TYPE_LABELS;
        $stockTrackingModes = Store::STOCK_TRACKING_LABELS;
        $transferPolicies = Store::TRANSFER_POLICY_LABELS;
        $selectedOrganizationIds = $store->legacyOrganizationIds();

        return view('stores.edit', compact('stores', 'store', 'roles', 'organizations', 'Products', 'storeTypes', 'stockTrackingModes', 'transferPolicies', 'selectedOrganizationIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        $user = Auth::user();
        $roles = Role::all();
        $data = $this->validatedData($request);
        $organizationIds = $this->allowedOrganizationIds($data['organization_id'], $user);

        if (empty($organizationIds)) {
            Alert::warning('خطا در ثبت', 'حداقل یک شعبه معتبر برای انبار انتخاب کنید');
            return back()->withInput();
        }

        $tenantId = $this->resolveTenantId($organizationIds[0], $user);
        $store->update($this->storePayload($data, $organizationIds, $tenantId, $request->has('isActive')));
        $store->roles()->sync($data['roles'] ?? []);
        $this->syncOrganizationScopes($store, $organizationIds, $tenantId);

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'انبار ویرایش شد' . '-' . $store->title
        ]);

        $user = Auth::user();
        if ($user->isGod == 1) {
            $organizations = Organization::where('isActive', 1)->get();
            $stores = Store::where('isActive', 1)->get();
        } else {
            $organizations = Organization::forOrganizations($user, 'id')->where('isActive', 1)->get();
            $stores = Store::forOrganizations($user)->where('isActive', 1)->get();
        }

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->route('stores.edit', compact('store', 'stores', 'roles', 'organizations'));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'code' => ['required', 'integer', 'min:1'],
            'organization_id' => ['required', 'array', 'min:1'],
            'organization_id.*' => ['integer', 'exists:organizations,id'],
            'store_type' => ['required', 'string', Rule::in(array_keys(Store::STORE_TYPE_LABELS))],
            'stock_tracking_mode' => ['required', 'string', Rule::in(array_keys(Store::STOCK_TRACKING_LABELS))],
            'transfer_policy' => ['required', 'string', Rule::in(array_keys(Store::TRANSFER_POLICY_LABELS))],
            'lat' => ['nullable', 'string', 'max:80'],
            'lang' => ['nullable', 'string', 'max:80'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);
    }

    private function storePayload(array $data, array $organizationIds, ?int $tenantId, bool $isActive): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'code' => $data['code'],
            'store_type' => $data['store_type'],
            'stock_tracking_mode' => $data['stock_tracking_mode'],
            'transfer_policy' => $data['transfer_policy'],
            'opening_inventory_status' => 'open',
            'organization_id' => $this->organizationStorageValue($organizationIds),
            'tenant_id' => $tenantId,
            'tenants_id' => $tenantId,
            'lat' => $data['lat'] ?? null,
            'lang' => $data['lang'] ?? null,
            'isActive' => $isActive ? 1 : 0,
        ];
    }

    private function allowedOrganizationIds(array $requestedOrganizationIds, $user): array
    {
        $requestedOrganizationIds = array_values(array_unique(array_filter(array_map('intval', $requestedOrganizationIds))));

        if ($user->isGod == 1 || $user->isAdmin == 1) {
            return Organization::whereIn('id', $requestedOrganizationIds)->pluck('id')->map(fn($id) => (int) $id)->all();
        }

        $allowedIds = Organization::forOrganizations($user, 'id')->pluck('id')->map(fn($id) => (int) $id)->all();

        return array_values(array_intersect($requestedOrganizationIds, $allowedIds));
    }

    private function organizationStorageValue(array $organizationIds): string
    {
        return json_encode(array_values($organizationIds));
    }

    private function resolveTenantId($organizationId, $user): ?int
    {
        $organization = $organizationId ? Organization::find($organizationId) : null;

        return $organization ? (int) ($organization->tenant_id ?: $organization->tenants_id) : (int) ($user->tenant_id ?: $user->tenants_id);
    }

    private function syncOrganizationScopes(Store $store, array $organizationIds, ?int $tenantId): void
    {
        OrganizationScope::where('scopeable_type', Store::class)
            ->where('scopeable_id', $store->id)
            ->delete();

        foreach ($organizationIds as $index => $organizationId) {
            OrganizationScope::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'scopeable_type' => Store::class,
                'scopeable_id' => $store->id,
                'is_primary' => $index === 0,
                'source' => 'store_form',
            ]);
        }
    }
}
