<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\DistributionPromotion;
use App\Models\DistributionVisitPlan;
use App\Models\Product;
use App\Models\User;
use App\Services\DistributionSalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class DistributionController extends Controller
{
    public function advanced(Request $request)
    {
        $user = Auth::user();
        $plans = DistributionVisitPlan::with(['visitor', 'stops.customer', 'stops.pishfactor'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id))
            ->latest('planned_date_en')
            ->latest('id')
            ->paginate(10);
        $promotions = DistributionPromotion::with(['product', 'giftProduct'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where(function ($scope) use ($user) {
                $scope->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id ?: $user->tenants_id);
            }))
            ->latest('id')
            ->limit(20)
            ->get();
        $visitors = User::query()->orderBy('name')->limit(200)->get(['id', 'name', 'username']);
        $today = now()->toDateString();
        $totals = [
            'plans' => DistributionVisitPlan::when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id))->count(),
            'visited' => DistributionVisitPlan::when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id))->sum('visited_count'),
            'orders' => DistributionVisitPlan::when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id))->sum('ordered_count'),
            'promotions' => $promotions->where('status', 'active')->count(),
        ];

        return view('distribution.advanced', compact('plans', 'promotions', 'visitors', 'today', 'totals'));
    }

    public function storeVisitPlan(Request $request, DistributionSalesService $distributionSales)
    {
        $payload = $request->validate([
            'visitor_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:191'],
            'planned_date_en' => ['nullable', 'date'],
            'route_code' => ['nullable', 'string', 'max:80'],
            'area_id' => ['nullable', 'integer'],
            'region_id' => ['nullable', 'integer'],
            'sales_mode' => ['required', 'in:hot_sale,cold_sale,preorder'],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
            'description' => ['nullable', 'string'],
        ]);

        $plan = $distributionSales->createVisitPlan($payload, Auth::user());

        Alert::success('ثبت شد', 'برنامه ویزیت ' . $plan->plan_number . ' برای ویزیتور ساخته شد.');

        return redirect()->route('distribution.advanced');
    }

    public function storePromotion(Request $request)
    {
        $user = Auth::user();
        $payload = $request->validate([
            'code' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:191'],
            'promotion_type' => ['required', 'in:discount_percent,discount_amount,gift'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'gift_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'gift_quantity' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,expired'],
            'description' => ['nullable', 'string'],
        ]);

        DistributionPromotion::create(array_merge($payload, [
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'organization_id' => $user->organization_id,
            'code' => $payload['code'] ?: 'PRM-' . verta()->format('Ymd') . '-' . str_pad((string) (DistributionPromotion::count() + 1), 4, '0', STR_PAD_LEFT),
            'created_by' => $user->id,
        ]));

        Alert::success('ثبت شد', 'پروموشن پخش ثبت شد.');

        return redirect()->route('distribution.advanced');
    }
}
