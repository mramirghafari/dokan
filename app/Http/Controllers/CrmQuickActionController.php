<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmSalesBoardCard;
use App\Services\CrmQuickActionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CrmQuickActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user');
    }

    public function storeFollowup(Request $request, CrmQuickActionService $actions)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date_en' => ['nullable', 'date'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'redirect' => ['nullable', 'string', 'max:500'],
        ]);

        $followup = $actions->createFollowup(Auth::user(), $data);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'followup_id' => $followup->id,
                'message' => 'پیگیری ثبت شد.',
            ]);
        }

        ActivityLogService::safeLog('create', 'CRM: Followup', null, ['section' => 'crm', 'event_key' => 'crm.storeFollowup']);

        Alert::success('ثبت شد', 'پیگیری سریع برای مشتری ثبت شد.');

        return redirect($data['redirect'] ?? route('crm.followups.index'));
    }

    public function storeCardNote(Request $request, CrmSalesBoardCard $card, CrmQuickActionService $actions)
    {
        $this->authorizeCard($card);

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $actions->addCardNote(Auth::user(), $card, $data['comment']);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'یادداشت ثبت شد.']);
        }

        ActivityLogService::safeLog('create', 'CRM: Card Note', null, ['section' => 'crm', 'event_key' => 'crm.storeCardNote']);

        Alert::success('ثبت شد', 'یادداشت روی کارت ذخیره شد.');

        return redirect()->back();
    }

    private function authorizeCard(CrmSalesBoardCard $card): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        abort_unless(
            CrmSalesBoardCard::query()->whereKey($card->id)->forOrganizations($user)->exists(),
            403
        );
    }
}
