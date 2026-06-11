<?php

namespace App\Http\Controllers;

use App\Models\CrmCallLog;
use App\Models\CrmServiceTicket;
use App\Models\Customers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class CrmCallCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'updateOutcome']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = CrmCallLog::query()->with(['customer', 'serviceTicket', 'assignedUser', 'creator']);

        if ((int) $user->isGod !== 1) {
            $baseQuery->forOrganizations($user);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('direction'), fn($query) => $query->where('direction', $request->direction))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('result'), fn($query) => $query->where('result', $request->result))
            ->when($request->filled('assigned_user_id'), fn($query) => $query->where('assigned_user_id', $request->assigned_user_id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', $search)
                        ->orWhere('subject', 'like', $search)
                        ->orWhere('phone_number', 'like', $search)
                        ->orWhere('contact_name', 'like', $search)
                        ->orWhere('notes', 'like', $search)
                        ->orWhere('outcome', 'like', $search);
                });
            });

        $callLogs = $filteredQuery->orderByRaw("FIELD(status, 'open', 'needs_followup', 'completed', 'failed')")
            ->orderByDesc('call_started_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'today' => (clone $baseQuery)->whereDate('call_started_at', now()->toDateString())->count(),
            'open' => (clone $baseQuery)->whereIn('status', ['open', 'needs_followup'])->count(),
            'missed' => (clone $baseQuery)->where('direction', 'missed')->whereDate('call_started_at', now()->toDateString())->count(),
            'quality_avg' => round((float) (clone $baseQuery)->whereNotNull('quality_score')->avg('quality_score'), 1),
        ];

        $userColumns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));
        $ticketColumns = array_values(array_filter(['id', 'code', 'subject', Schema::hasColumn('crm_service_tickets', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'customer_id', 'status']));
        $usersQuery = User::query()->select($userColumns)->where('isActive', 1)->orderBy('name')->limit(200);
        $ticketsQuery = CrmServiceTicket::query()->select($ticketColumns)->whereIn('status', ['open', 'pending'])->latest('id')->limit(200);

        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
            $ticketsQuery->forOrganizations($user);
        }

        return view('crm.call_center.index', [
            'callLogs' => $callLogs,
            'stats' => $stats,
            'users' => $usersQuery->get(),
            'tickets' => $ticketsQuery->get(),
            'directions' => CrmCallLog::DIRECTIONS,
            'channels' => CrmCallLog::CHANNELS,
            'statuses' => CrmCallLog::STATUSES,
            'results' => CrmCallLog::RESULTS,
            'priorities' => CrmCallLog::PRIORITIES,
            'filters' => $request->only(['direction', 'status', 'result', 'assigned_user_id', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'service_ticket_id' => ['nullable', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'direction' => ['required', 'in:' . implode(',', array_keys(CrmCallLog::DIRECTIONS))],
            'channel' => ['required', 'in:' . implode(',', array_keys(CrmCallLog::CHANNELS))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmCallLog::PRIORITIES))],
            'subject' => ['required', 'string', 'max:180'],
            'phone_number' => ['nullable', 'string', 'max:40'],
            'contact_name' => ['nullable', 'string', 'max:180'],
            'call_started_at' => ['nullable', 'date'],
            'next_action_at' => ['nullable', 'date'],
            'recording_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $customer = !empty($data['customer_id']) ? $this->resolveCustomer($data['customer_id'], $user) : null;
        $ticket = !empty($data['service_ticket_id']) ? $this->resolveTicket($data['service_ticket_id'], $user) : null;
        $nextId = (int) CrmCallLog::withTrashed()->max('id') + 1;
        $startedAt = !empty($data['call_started_at']) ? Carbon::parse($data['call_started_at']) : now();
        $nextActionAt = !empty($data['next_action_at']) ? Carbon::parse($data['next_action_at']) : null;

        CrmCallLog::create([
            'tenant_id' => optional($customer)->tenant_id ?: optional($ticket)->tenant_id ?: $this->tenantId($user),
            'organization_id' => optional($customer)->organization_id ?: optional($ticket)->organization_id ?: $this->organizationId($user),
            'customer_id' => optional($customer)->id ?: optional($ticket)->customer_id,
            'service_ticket_id' => optional($ticket)->id,
            'assigned_user_id' => ($data['assigned_user_id'] ?? null) ?: $user->id,
            'code' => 'CALL-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
            'direction' => $data['direction'],
            'channel' => $data['channel'],
            'status' => $data['direction'] === 'missed' ? 'needs_followup' : 'open',
            'priority' => $data['priority'],
            'subject' => $data['subject'],
            'phone_number' => $data['phone_number'] ?? optional($customer)->mobile,
            'contact_name' => $data['contact_name'] ?? optional($customer)->name,
            'call_started_at' => $startedAt,
            'next_action_at' => $nextActionAt,
            'recording_url' => $data['recording_url'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Alert::success('ثبت شد', 'تماس در مرکز تماس CRM ثبت شد.');

        return redirect()->route('crm.call-center.index');
    }

    public function updateOutcome(Request $request, CrmCallLog $callLog)
    {
        $this->authorizeCallLog($callLog);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(CrmCallLog::STATUSES))],
            'result' => ['nullable', 'in:' . implode(',', array_keys(CrmCallLog::RESULTS))],
            'call_ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'next_action_at' => ['nullable', 'date'],
            'quality_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'outcome' => ['nullable', 'string'],
        ]);

        $endedAt = !empty($data['call_ended_at']) ? Carbon::parse($data['call_ended_at']) : ($data['status'] === 'completed' ? now() : $callLog->call_ended_at);
        $duration = $data['duration_seconds'] ?? $callLog->duration_seconds;

        if (!$duration && $endedAt && $callLog->call_started_at) {
            $duration = max(0, $callLog->call_started_at->diffInSeconds($endedAt));
        }

        $callLog->update([
            'status' => $data['status'],
            'result' => $data['result'] ?? $callLog->result,
            'call_ended_at' => $endedAt,
            'duration_seconds' => $duration ?: 0,
            'next_action_at' => !empty($data['next_action_at']) ? Carbon::parse($data['next_action_at']) : $callLog->next_action_at,
            'quality_score' => $data['quality_score'] ?? $callLog->quality_score,
            'outcome' => $data['outcome'] ?? $callLog->outcome,
            'updated_by' => Auth::id(),
        ]);

        Alert::success('بروزرسانی شد', 'نتیجه تماس ثبت شد.');

        return redirect()->route('crm.call-center.index');
    }

    private function resolveCustomer($customerId, $user): Customers
    {
        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function resolveTicket($ticketId, $user): CrmServiceTicket
    {
        $query = CrmServiceTicket::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($ticketId);
    }

    private function authorizeCallLog(CrmCallLog $callLog): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        abort_unless(CrmCallLog::query()->whereKey($callLog->id)->forOrganizations($user)->exists(), 403);
    }

    private function tenantId($user): ?int
    {
        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) ?: null : null;
    }

    private function organizationId($user): ?int
    {
        if (!$user || empty($user->organization_id)) {
            return null;
        }

        $decoded = json_decode((string) $user->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return (int) $user->organization_id ?: null;
    }
}
