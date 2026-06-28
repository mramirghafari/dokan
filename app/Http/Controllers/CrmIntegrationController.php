<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmFollowup;
use App\Models\CrmIntegrationConnection;
use App\Models\CrmIntegrationSyncLog;
use App\Services\CrmIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CrmIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['voipWebhook']);
        $this->middleware('can:customers,user')->except(['voipWebhook']);
    }

    public function index(CrmIntegrationService $service)
    {
        return view('crm.integrations.index', $service->adminState(Auth::user()) + [
            'types' => CrmIntegrationConnection::TYPES,
            'providers' => CrmIntegrationConnection::PROVIDERS,
            'statuses' => CrmIntegrationSyncLog::STATUSES,
        ]);
    }

    public function store(Request $request, CrmIntegrationService $service)
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(CrmIntegrationConnection::TYPES))],
            'provider' => ['required', 'in:' . implode(',', array_keys(CrmIntegrationConnection::PROVIDERS))],
            'title' => ['required', 'string', 'max:180'],
            'endpoint_url' => ['nullable', 'url', 'max:500'],
            'webhook_secret' => ['nullable', 'string', 'min:12', 'max:160'],
            'calendar_name' => ['nullable', 'string', 'max:180'],
            'drive_folder' => ['nullable', 'string', 'max:260'],
            'voip_line' => ['nullable', 'string', 'max:120'],
            'voip_context' => ['nullable', 'string', 'max:120'],
            'voip_caller_id' => ['nullable', 'string', 'max:120'],
            'outbound_auth_token' => ['nullable', 'string', 'max:1000'],
            'scopes' => ['nullable', 'array'],
            'scopes.*' => ['string', 'max:80'],
        ]);

        [$connection, $secret] = $service->createConnection(Auth::user(), $data);

        ActivityLogService::safeLog('create', 'CRM: store', null, ['section' => 'crm', 'event_key' => 'crm.store']);

        Alert::success('Integration ساخته شد', 'Secret فقط همین بار نمایش داده می شود: ' . $secret);

        return redirect()->route('crm.integrations.index', ['connection' => $connection->id])
            ->with('crm_integration_secret', $secret)
            ->with('crm_integration_connection_id', $connection->id);
    }

    public function toggle(CrmIntegrationConnection $connection, CrmIntegrationService $service)
    {
        $service->toggle($connection, Auth::user());

        Alert::success('بروزرسانی شد', 'وضعیت integration تغییر کرد.');

        return redirect()->route('crm.integrations.index');
    }

    public function syncFollowup(Request $request, CrmIntegrationService $service)
    {
        $data = $request->validate([
            'connection_id' => ['required', 'integer'],
            'followup_id' => ['required', 'integer'],
        ]);

        $connection = CrmIntegrationConnection::findOrFail((int) $data['connection_id']);
        $followup = CrmFollowup::findOrFail((int) $data['followup_id']);
        $log = $service->syncFollowupToCalendar($connection, $followup, Auth::user());

        Alert::success('ثبت شد', 'رویداد تقویم با وضعیت ' . $log->statusText() . ' ثبت شد.');

        return redirect()->route('crm.integrations.index');
    }

    public function recordDriveLink(Request $request, CrmIntegrationService $service)
    {
        $data = $request->validate([
            'connection_id' => ['required', 'integer'],
            'target_type' => ['required', 'in:customer,followup,call'],
            'target_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'external_url' => ['required', 'url', 'max:800'],
            'external_id' => ['nullable', 'string', 'max:180'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $connection = CrmIntegrationConnection::findOrFail((int) $data['connection_id']);
        $log = $service->recordDriveLink($connection, $data, Auth::user());

        Alert::success('لینک ثبت شد', 'فایل بیرونی با وضعیت ' . $log->statusText() . ' به CRM وصل شد.');

        return redirect()->route('crm.integrations.index');
    }

    public function clickToCall(Request $request, CrmIntegrationService $service)
    {
        $data = $request->validate([
            'connection_id' => ['required', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'phone_number' => ['required', 'string', 'max:40'],
            'contact_name' => ['nullable', 'string', 'max:180'],
            'subject' => ['nullable', 'string', 'max:180'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $connection = CrmIntegrationConnection::findOrFail((int) $data['connection_id']);
        $log = $service->startClickToCall($connection, $data, Auth::user());

        Alert::success('تماس خروجی ثبت شد', 'درخواست click-to-call با وضعیت ' . $log->statusText() . ' ثبت شد.');

        return redirect()->route('crm.integrations.index');
    }

    public function voipWebhook(Request $request, CrmIntegrationConnection $connection, CrmIntegrationService $service): JsonResponse
    {
        $log = $service->handleVoipWebhook($connection, $request);

        return response()->json([
            'ok' => true,
            'status' => $log->status,
            'log_id' => $log->id,
            'call_log_id' => $log->syncable_id,
        ]);
    }
}
