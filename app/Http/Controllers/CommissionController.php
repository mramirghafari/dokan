<?php

namespace App\Http\Controllers;

use App\Models\Targets;
use App\Services\CommissionCalculationService;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CommissionController extends Controller
{
    public function index(Request $request, CommissionCalculationService $service)
    {
        $user = $request->user();
        $targets = Targets::query()
            ->with(['user.roles', 'commissionPlan'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->forOrganizations($user))
            ->when($request->filled('status'), fn($query) => $query->where('settlement_status', $request->status))
            ->orderByDesc('start_date_en')
            ->limit(100)
            ->get();

        $reports = $service->calculateMany($targets);

        return view('commissions.index', compact('reports'));
    }

    public function calculate(Request $request, Targets $target, CommissionCalculationService $service)
    {
        $this->authorizeTarget($request, $target);

        $service->calculateTarget($target, null, true);
        Alert::success('محاسبه شد', 'پورسانت تارگت محاسبه و settlement آن بروزرسانی شد.');

        return redirect()->route('commissions.index');
    }

    private function authorizeTarget(Request $request, Targets $target): void
    {
        $user = $request->user();

        if ((int) $user->isGod === 1) {
            return;
        }

        $allowed = Targets::forOrganizations($user)->whereKey($target->id)->exists();

        abort_unless($allowed, 403);
    }
}
