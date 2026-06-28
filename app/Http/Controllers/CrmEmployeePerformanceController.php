<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmEmployeeCoachingPlan;
use App\Models\CrmEmployeePerformanceSnapshot;
use App\Services\CrmEmployeePerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CrmEmployeePerformanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'refresh', 'storeCoaching', 'updateCoaching']);
    }

    public function index(Request $request, CrmEmployeePerformanceService $service)
    {
        return view('crm.employee_performance.index', [
            'state' => $service->state(Auth::user(), $request->only(['period_start', 'period_end', 'role_scope', 'user_id'])),
        ]);
    }

    public function refresh(Request $request, CrmEmployeePerformanceService $service)
    {
        $data = $request->validate([
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'role_scope' => ['required', 'in:' . implode(',', array_keys(CrmEmployeePerformanceSnapshot::ROLE_SCOPES))],
            'user_id' => ['nullable', 'integer'],
        ]);

        $count = $service->refresh(Auth::user(), $data);

        Alert::success('بروزرسانی شد', number_format($count) . ' snapshot عملکرد کارمندان محاسبه شد.');

        return redirect()->route('crm.employee-performance.index', $data);
    }

    public function storeCoaching(Request $request, CrmEmployeePerformanceService $service)
    {
        $data = $request->validate([
            'performance_snapshot_id' => ['nullable', 'integer'],
            'user_id' => ['required', 'integer'],
            'type' => ['required', 'in:' . implode(',', array_keys(CrmEmployeeCoachingPlan::TYPES))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmEmployeeCoachingPlan::PRIORITIES))],
            'title' => ['required', 'string', 'max:180'],
            'target_metric' => ['nullable', 'string', 'max:80'],
            'target_value' => ['nullable', 'numeric'],
            'due_at' => ['nullable', 'date'],
            'action_plan' => ['nullable', 'string'],
        ]);

        $service->createCoaching(Auth::user(), $data);

        ActivityLogService::safeLog('create', 'CRM: Coaching', null, ['section' => 'crm', 'event_key' => 'crm.storeCoaching']);

        Alert::success('ثبت شد', 'برنامه coaching برای کارمند ثبت شد.');

        return redirect()->route('crm.employee-performance.index');
    }

    public function updateCoaching(Request $request, CrmEmployeeCoachingPlan $plan, CrmEmployeePerformanceService $service)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(CrmEmployeeCoachingPlan::STATUSES))],
            'outcome' => ['nullable', 'string'],
        ]);

        $service->updateCoachingStatus(Auth::user(), $plan, $data);

        ActivityLogService::safeLog('update', 'CRM: Coaching', null, ['section' => 'crm', 'event_key' => 'crm.updateCoaching']);

        Alert::success('بروزرسانی شد', 'وضعیت برنامه coaching تغییر کرد.');

        return redirect()->route('crm.employee-performance.index');
    }
}
