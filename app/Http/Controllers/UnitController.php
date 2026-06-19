<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Organization;
use App\Models\Unit;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:units,user')->only(['index', 'store', 'edit', 'update']);
    }

    public function index(Request $request)
    {
        $usageScope = $this->resolveUsageScope($request);
        $user = \Auth::user();

        $unitsQuery = Unit::query()->where('usage_scope', $usageScope);

        if ($user->isGod != 1) {
            $unitsQuery = Unit::forOrganizations($user)->where('usage_scope', $usageScope);
        }

        $units = $unitsQuery->orderBy('title')->get();
        $parents = (clone $unitsQuery)->whereNull('parent_id')->get();
        $organizations = Organization::where('isActive', 1)->get();
        $scopeLabel = Unit::USAGE_SCOPE_LABELS[$usageScope] ?? 'واحد';
        $indexRoute = $usageScope === Unit::SCOPE_SHIPPING ? 'units.shipping.index' : 'units.product.index';

        return view('units.index', compact('units', 'parents', 'organizations', 'usageScope', 'scopeLabel', 'indexRoute'));
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        $usageScope = $this->resolveUsageScope($request);
        $organizationId = (int) ($request->organization_id ?: $user->organization_id);
        $tenantId = app(TenantContextService::class)->tenantId();

        $unit = Unit::create([
            'title' => $request->title,
            'code' => $request->code,
            'symbol' => $request->symbol ?: $request->title,
            'unit_type' => $request->unit_type ?: 'count',
            'usage_scope' => $usageScope,
            'parent_id' => $request->parent_id ?: null,
            'conversion_to_parent' => $request->conversion_to_parent ?: null,
            'description' => $request->description,
            'organization_id' => $organizationId,
            'tenant_id' => $tenantId,
            'isActive' => 1,
        ]);

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'واحد ایجاد شد' . '-' . $unit->title,
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');

        return redirect()->route($usageScope === Unit::SCOPE_SHIPPING ? 'units.shipping.index' : 'units.product.index');
    }

    public function edit(Unit $unit)
    {
        $user = \Auth::user();
        $usageScope = $unit->usage_scope ?: Unit::SCOPE_PRODUCT;

        $unitsQuery = Unit::query()->where('usage_scope', $usageScope);
        if ($user->isGod != 1) {
            $unitsQuery = Unit::forOrganizations($user)->where('usage_scope', $usageScope);
        }

        $units = $unitsQuery->orderBy('title')->get();
        $parents = (clone $unitsQuery)->whereNull('parent_id')->where('id', '!=', $unit->id)->get();
        $organizations = Organization::where('isActive', 1)->get();
        $scopeLabel = Unit::USAGE_SCOPE_LABELS[$usageScope] ?? 'واحد';
        $indexRoute = $usageScope === Unit::SCOPE_SHIPPING ? 'units.shipping.index' : 'units.product.index';

        return view('units.edit', compact('unit', 'units', 'parents', 'organizations', 'usageScope', 'scopeLabel', 'indexRoute'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->isActive == 'on' ? $request->isActive = 1 : $request->isActive = 0;
        $user = \Auth::user();
        $usageScope = $unit->usage_scope ?: Unit::SCOPE_PRODUCT;

        $unit->update([
            'title' => $request->title,
            'code' => $request->code,
            'symbol' => $request->symbol ?: $request->title,
            'unit_type' => $request->unit_type ?: $unit->unit_type,
            'description' => $request->description,
            'parent_id' => $request->parent_id ?: null,
            'conversion_to_parent' => $request->conversion_to_parent ?: null,
            'organization_id' => $request->organization_id ?: $user->organization_id,
            'isActive' => $request->isActive,
        ]);

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'واحد ویرایش شد' . '-' . $unit->title,
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        return redirect()->route('units.edit', $unit);
    }

    public function parents($id)
    {
        $parent_id = Unit::where('organization_id', $id)
            ->whereNull('parent_id')
            ->where('isActive', 1)
            ->get();

        return response()->json($parent_id);
    }

    private function resolveUsageScope(Request $request): string
    {
        $scope = $request->route('usage_scope')
            ?? $request->input('usage_scope')
            ?? $request->query('scope', Unit::SCOPE_PRODUCT);

        return in_array($scope, [Unit::SCOPE_PRODUCT, Unit::SCOPE_SHIPPING], true)
            ? $scope
            : Unit::SCOPE_PRODUCT;
    }
}
