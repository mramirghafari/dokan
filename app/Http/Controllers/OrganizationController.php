<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use App\Models\Tenants;
use App\Models\Organization;
use App\Services\TenantSettings;
use RealRashid\SweetAlert\Facades\Alert;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:organizations,user')->only(['index', 'store', 'edit', 'update']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_branch_management')) {
                Alert::warning('غیرفعال', 'مدیریت شعب و واحدهای پخش برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $user = \Auth::user();
        if ($user->isGod == 1) {
            $Tenants = Tenants::all();
            $organizations = Organization::all();
        } else {
            $Tenants = Tenants::where('id', $user->tenants_id)->get();
            $organizations = Organization::forOrganizations($user, 'id')->get();
        }

        return view('organizations.index', compact('organizations', 'Tenants'));
    }

    public function store(Request $request)
    {
        $organization = Organization::create($request->all());
        $user = \Auth::user();
        ActivityLogService::safeLogModel('create', 'شعیه جدید ایجاد شد' . '-' . $organization->title, $organization, ['section' => 'organization', 'event_key' => 'organization.create']);

        Alert::success('تشکر', 'شعبه جدید با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Organization $organization)
    {
        $Tenants = Tenants::all();
        $organizations = Organization::all();
        return view('organizations.edit', compact('organization', 'organizations', 'Tenants'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $organization->update([
            'title' => $request->title,
            'type' => $request->type,
            'unit_order' => $request->unit_order,
            'sub_unit' => $request->sub_unit,
            'pr_type' => $request->pr_type,
            'currency_type' => $request->currency_type,
            'unit_display' => $request->unit_display,
            'description' => $request->description,
            'isActive' => $request->isActive,
            'tenants_id' => $request->tenants_id
        ]);
        $organizations = Organization::all();
        $user = \Auth::user();
        ActivityLogService::safeLogModel('update', 'شعبه ویرایش شد' . '-' . $organization->title, $organization, ['section' => 'organization', 'event_key' => 'organization.update']);

        Alert::success('تشکر', 'شعبه با موفقیت ویرایش شد');
        return redirect()->route('organizations.edit', compact('organization', 'organizations'));
    }

    public function destroy(Organization $organization)
    {

        $user = \Auth::user();

        $organization->delete();
        ActivityLogService::safeLogModel('delete', 'شعبه حذف شد' . '-' . $organization->title, $organization, ['section' => 'organization', 'event_key' => 'organization.delete']);

        Alert::success('تشکر', 'شعبه با موفقیت حذف شد');
        return redirect()->route('organizations.index');
    }
}
