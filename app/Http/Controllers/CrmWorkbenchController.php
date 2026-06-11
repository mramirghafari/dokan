<?php

namespace App\Http\Controllers;

use App\Models\CrmCollaborationComment;
use App\Models\CrmCollaborationMention;
use App\Models\CrmWorkbenchPreference;
use App\Services\CrmWorkbenchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CrmWorkbenchController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'storeComment', 'updatePreference', 'readMention']);
    }

    public function index(Request $request, CrmWorkbenchService $service)
    {
        return view('crm.workbench.index', [
            'state' => $service->state(Auth::user(), $request->only(['focus_scope', 'target_type'])),
        ]);
    }

    public function storeComment(Request $request, CrmWorkbenchService $service)
    {
        $data = $request->validate([
            'target_type' => ['required', 'in:' . implode(',', array_keys(CrmWorkbenchService::TARGETS))],
            'target_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:4000'],
            'mentioned_user_ids' => ['nullable', 'array'],
            'mentioned_user_ids.*' => ['integer'],
            'visibility' => ['required', 'in:' . implode(',', array_keys(CrmCollaborationComment::VISIBILITIES))],
        ]);

        $service->storeComment(Auth::user(), $data);

        Alert::success('ثبت شد', 'کامنت CRM و mentionها ثبت شد.');

        return redirect()->route('crm.workbench.index');
    }

    public function updatePreference(Request $request, CrmWorkbenchService $service)
    {
        $data = $request->validate([
            'focus_scope' => ['required', 'in:' . implode(',', array_keys(CrmWorkbenchPreference::FOCUS_SCOPES))],
        ]);

        $service->updatePreference(Auth::user(), $data);

        Alert::success('ذخیره شد', 'تنظیمات کارتابل CRM ذخیره شد.');

        return redirect()->route('crm.workbench.index', ['focus_scope' => $data['focus_scope']]);
    }

    public function readMention(CrmCollaborationMention $mention, CrmWorkbenchService $service)
    {
        $service->markMentionRead(Auth::user(), $mention);

        Alert::success('خوانده شد', 'mention انتخاب شده از کارتابل خوانده نشده ها خارج شد.');

        return redirect()->route('crm.workbench.index');
    }
}
