<?php

namespace App\Http\Middleware;

use App\Services\PanelMembershipService;
use Closure;
use Illuminate\Http\Request;

class EnsureActivePanel
{
    public function __construct(private PanelMembershipService $panels)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($request->routeIs('panel.select', 'panel.switch', 'logout')) {
            return $next($request);
        }

        $availablePanels = $this->panels->accessiblePanelsForUser($user);

        if ($availablePanels->isEmpty()) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->with('error', 'هیچ پنل فعالی برای ورود شما یافت نشد.');
        }

        if ($availablePanels->count() === 1) {
            $panel = $availablePanels->first();

            if (!$this->panels->activeTenantId($user)) {
                $this->panels->activatePanel($user, (int) $panel['tenant_id']);
            }

            return $next($request);
        }

        $activeTenantId = $this->panels->activeTenantId($user);
        $hasValidPanel = $activeTenantId
            && $availablePanels->contains(fn (array $panel) => (int) $panel['tenant_id'] === (int) $activeTenantId);

        if (!$hasValidPanel) {
            return redirect()->route('panel.select');
        }

        return $next($request);
    }
}
