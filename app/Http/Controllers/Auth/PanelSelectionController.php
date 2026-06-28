<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PanelMembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelSelectionController extends Controller
{
    public function __construct(private PanelMembershipService $panels)
    {
        $this->middleware('auth');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $availablePanels = $this->panels->accessiblePanelsForUser($user);

        if ($availablePanels->isEmpty()) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->with('error', 'هیچ پنل فعالی برای ورود شما یافت نشد.');
        }

        if ($availablePanels->count() === 1) {
            $panel = $availablePanels->first();
            $this->panels->activatePanel($user, (int) $panel['tenant_id']);

            return redirect()
                ->route('index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'ورود موفق — به ' . ($panel['tenant_name'] ?? 'پنل') . ' خوش آمدید.',
                ]);
        }

        $activePanel = $this->panels->activePanel($user);

        return view('auth.panel-select', [
            'availablePanels' => $availablePanels,
            'activePanel' => $activePanel,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'integer'],
        ]);

        $this->panels->activatePanel($request->user(), (int) $request->tenant_id);

        return redirect()
            ->route('index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'پنل فعال با موفقیت تغییر کرد.',
            ]);
    }
}
