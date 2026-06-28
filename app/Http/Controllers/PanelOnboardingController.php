<?php

namespace App\Http\Controllers;

use App\Services\PanelOnboardingService;
use App\Services\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PanelOnboardingController extends Controller
{
    public function __construct(
        private PanelOnboardingService $onboarding,
        private TenantContextService $tenantContext
    ) {
        $this->middleware('auth');
    }

    public function dismissWelcome(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->tenantId($request->user());
        abort_unless($this->onboarding->isYoungPanel($tenantId), 403);

        $this->onboarding->markWelcomeSeen($tenantId, $request->user()->id);

        return response()->json(['ok' => true]);
    }

    public function completeTour(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->tenantId($request->user());
        abort_unless($this->onboarding->isYoungPanel($tenantId), 403);

        $this->onboarding->markTourCompleted($tenantId, $request->user()->id);

        return response()->json(['ok' => true]);
    }

    public function completeSetup(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->tenantId($request->user());
        abort_unless($this->onboarding->isYoungPanel($tenantId), 403);

        $this->onboarding->completeOnboarding($tenantId, $request->user()->id);

        return response()->json([
            'ok' => true,
            'message' => 'راه‌اندازی پنل تکمیل شد. ویجت‌های داشبورد فعال شدند.',
        ]);
    }
}
