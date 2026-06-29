<?php

namespace App\Http\Controllers;

use App\Models\CustomerSegment;
use App\Models\Organization;
use App\Services\ActivityLogService;
use App\Services\CustomerSegmentService;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CustomerGroupController extends Controller
{
    public function __construct(private CustomerSegmentService $segmentService)
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $user = auth()->user();
        $groups = $this->segmentService->customerGroupsQuery($user)->get();
        $organizations = Organization::forOrganizations($user, 'id')->get();

        return view('customer-groups.index', compact('groups', 'organizations'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $organizationId = $user->organization_id
            ? (is_array(json_decode($user->organization_id, true))
                ? (int) reset(json_decode($user->organization_id, true))
                : (int) $user->organization_id)
            : Organization::forOrganizations($user, 'id')->value('id');

        $group = CustomerSegment::create([
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'organization_id' => $organizationId,
            'type' => 'customer_group',
            'title' => trim($request->title),
            'code' => substr('customer_group_' . md5(trim($request->title)), 0, 60),
            'description' => $request->description,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_default' => false,
            'isActive' => 1,
        ]);

        ActivityLogService::safeLogModel(
            'create',
            'گروه مشتری ایجاد شد - ' . $group->title,
            $group,
            ['section' => 'crm', 'event_key' => 'customer_group.create']
        );

        Alert::success('تشکر', 'گروه مشتری با موفقیت ایجاد شد');

        return back();
    }

    public function edit(CustomerSegment $customerGroup)
    {
        abort_unless($customerGroup->type === 'customer_group', 404);

        $user = auth()->user();
        $groups = $this->segmentService->customerGroupsQuery($user)->get();

        return view('customer-groups.edit', [
            'group' => $customerGroup,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, CustomerSegment $customerGroup)
    {
        abort_unless($customerGroup->type === 'customer_group', 404);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $customerGroup->update([
            'title' => trim($request->title),
            'description' => $request->description,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'isActive' => $request->boolean('isActive') || $request->input('isActive') === 'on' ? 1 : 0,
        ]);

        ActivityLogService::safeLogModel(
            'update',
            'گروه مشتری ویرایش شد - ' . $customerGroup->title,
            $customerGroup,
            ['section' => 'crm', 'event_key' => 'customer_group.update']
        );

        Alert::success('تشکر', 'گروه مشتری با موفقیت ویرایش شد');

        return redirect()->route('customer-groups.edit', $customerGroup);
    }

    public function destroy(CustomerSegment $customerGroup)
    {
        abort_unless($customerGroup->type === 'customer_group', 404);

        $title = $customerGroup->title;
        $customerGroup->delete();

        ActivityLogService::safeLog('delete', 'گروه مشتری حذف شد - ' . $title, auth()->id(), [
            'section' => 'crm',
            'event_key' => 'customer_group.delete',
        ]);

        Alert::success('تشکر', 'گروه مشتری حذف شد');

        return redirect()->route('customer-groups.index');
    }
}
