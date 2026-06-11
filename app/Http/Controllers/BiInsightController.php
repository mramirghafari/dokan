<?php

namespace App\Http\Controllers;

use App\Models\BiAlertRule;
use App\Models\BiInsightAlert;
use App\Services\BiInsightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class BiInsightController extends Controller
{
    public function index(BiInsightService $insightService)
    {
        return view('bi.insights', $insightService->dashboardState(Auth::user()) + [
            'ruleTypes' => BiAlertRule::TYPES,
            'severities' => BiAlertRule::SEVERITIES,
            'alertStatuses' => BiInsightAlert::STATUSES,
        ]);
    }

    public function run(BiInsightService $insightService)
    {
        $result = $insightService->runAnalysis(Auth::user());

        Alert::success('تحلیل اجرا شد', number_format($result['alerts']) . ' هشدار و ' . number_format($result['forecasts']) . ' پیش بینی بروزرسانی شد.');

        return redirect()->route('bi.insights.index');
    }

    public function storeRule(Request $request, BiInsightService $insightService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'domain' => ['required', 'string', 'max:60'],
            'metric_key' => ['required', 'string', 'max:120'],
            'rule_type' => ['required', 'in:' . implode(',', array_keys(BiAlertRule::TYPES))],
            'operator' => ['nullable', 'in:gte,lte,above,below'],
            'threshold_value' => ['required', 'numeric', 'min:0'],
            'severity' => ['required', 'in:' . implode(',', array_keys(BiAlertRule::SEVERITIES))],
            'lookback_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'comparison_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'suggestion' => ['nullable', 'string'],
        ]);

        $insightService->createRule(Auth::user(), $data);

        Alert::success('Rule ثبت شد', 'قانون هشدار BI برای data mart اضافه شد.');

        return redirect()->route('bi.insights.index');
    }

    public function updateAlert(Request $request, BiInsightAlert $alert, BiInsightService $insightService)
    {
        $data = $request->validate(['status' => ['required', 'in:' . implode(',', array_keys(BiInsightAlert::STATUSES))]]);

        $insightService->updateAlertStatus(Auth::user(), $alert, $data['status']);

        Alert::success('هشدار بروزرسانی شد', 'وضعیت رسیدگی هشدار BI ثبت شد.');

        return redirect()->route('bi.insights.index');
    }
}
