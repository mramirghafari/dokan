<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Store;
use App\Models\WarehouseLocation;
use App\Services\TenantSettings;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class WarehouseLocationController extends Controller
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
        });
    }

    public function index()
    {
        $user = auth()->user();
        $stores = Store::forOrganizations($user)->where('isActive', 1)->orderBy('title')->get();
        $locations = WarehouseLocation::forOrganizations($user)
            ->with(['store', 'parent'])
            ->orderBy('store_id')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
        $parents = $locations->where('is_active', 1);
        $types = $this->locationTypes();

        return view('warehouse_locations.index', compact('stores', 'locations', 'parents', 'types'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $this->validatedData($request);
        $store = $this->accessibleStore((int) $data['store_id'], $user);

        if (!$store) {
            Alert::warning('خطا در ثبت', 'انبار انتخاب شده در دسترس شما نیست');
            return back()->withInput();
        }

        $parent = $this->validParent((int) ($data['parent_id'] ?? 0), $store->id, $user);
        if (($data['parent_id'] ?? null) && !$parent) {
            Alert::warning('خطا در ثبت', 'مکان بالادستی انتخاب شده معتبر نیست');
            return back()->withInput();
        }

        $location = WarehouseLocation::create($this->payload($data, $store, $parent, $user, $request->has('is_active')));

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $request->ip(),
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'مکان انبار ایجاد شد' . '-' . $location->title,
        ]);

        Alert::success('تشکر', 'مکان انبار با موفقیت ایجاد شد');
        return back();
    }

    public function edit(WarehouseLocation $warehouse_location)
    {
        $user = auth()->user();
        $location = $this->accessibleLocation($warehouse_location->id, $user);

        if (!$location) {
            abort(403);
        }

        $stores = Store::forOrganizations($user)->where('isActive', 1)->orderBy('title')->get();
        $parents = WarehouseLocation::forOrganizations($user)
            ->where('store_id', $location->store_id)
            ->where('id', '!=', $location->id)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
        $types = $this->locationTypes();

        return view('warehouse_locations.edit', compact('location', 'stores', 'parents', 'types'));
    }

    public function update(Request $request, WarehouseLocation $warehouse_location)
    {
        $user = auth()->user();
        $location = $this->accessibleLocation($warehouse_location->id, $user);

        if (!$location) {
            abort(403);
        }

        $data = $this->validatedData($request);
        $store = $this->accessibleStore((int) $data['store_id'], $user);

        if (!$store) {
            Alert::warning('خطا در ثبت', 'انبار انتخاب شده در دسترس شما نیست');
            return back()->withInput();
        }

        $parent = $this->validParent((int) ($data['parent_id'] ?? 0), $store->id, $user, $location->id);
        if (($data['parent_id'] ?? null) && !$parent) {
            Alert::warning('خطا در ثبت', 'مکان بالادستی انتخاب شده معتبر نیست');
            return back()->withInput();
        }

        $location->update($this->payload($data, $store, $parent, $user, $request->has('is_active')));

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $request->ip(),
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'مکان انبار ویرایش شد' . '-' . $location->title,
        ]);

        Alert::success('تشکر', 'مکان انبار با موفقیت ویرایش شد');
        return redirect()->route('warehouse-locations.index');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'store_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:190'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function payload(array $data, Store $store, ?WarehouseLocation $parent, $user, bool $isActive): array
    {
        $code = trim($data['code']);
        $type = array_key_exists($data['type'], $this->locationTypes()) ? $data['type'] : 'rack';
        $tenantId = $store->tenant_id ?: $store->tenants_id ?: ($user->tenant_id ?: $user->tenants_id);
        $path = $parent ? trim($parent->path . '/' . $code, '/') : $code;

        return [
            'tenant_id' => $tenantId,
            'organization_id' => $this->primaryOrganizationId($store->organization_id),
            'store_id' => $store->id,
            'parent_id' => $parent ? $parent->id : null,
            'type' => $type,
            'code' => $code,
            'title' => trim($data['title']),
            'path' => $path,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $isActive,
        ];
    }

    private function accessibleStore(int $storeId, $user): ?Store
    {
        return Store::forOrganizations($user)->where('id', $storeId)->first();
    }

    private function accessibleLocation(int $locationId, $user): ?WarehouseLocation
    {
        return WarehouseLocation::forOrganizations($user)->where('id', $locationId)->first();
    }

    private function validParent(int $parentId, int $storeId, $user, ?int $excludeId = null): ?WarehouseLocation
    {
        if (!$parentId) {
            return null;
        }

        return WarehouseLocation::forOrganizations($user)
            ->where('store_id', $storeId)
            ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
            ->where('id', $parentId)
            ->first();
    }

    private function primaryOrganizationId($organizationId): ?int
    {
        $decoded = json_decode((string) $organizationId, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function locationTypes(): array
    {
        return [
            'zone' => 'سالن / محدوده',
            'aisle' => 'راهرو',
            'rack' => 'قفسه',
            'shelf' => 'طبقه',
            'bin' => 'باکس / خانه',
        ];
    }
}
