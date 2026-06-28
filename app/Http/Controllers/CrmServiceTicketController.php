<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmServiceTicket;
use App\Models\Customers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class CrmServiceTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'updateStatus']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = CrmServiceTicket::query()->with(['customer', 'assignedUser', 'creator']);

        if ((int) $user->isGod !== 1) {
            $baseQuery->forOrganizations($user);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('type'), fn($query) => $query->where('type', $request->type))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn($query) => $query->where('priority', $request->priority))
            ->when($request->filled('assigned_user_id'), fn($query) => $query->where('assigned_user_id', $request->assigned_user_id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', $search)
                        ->orWhere('subject', 'like', $search)
                        ->orWhere('contact_name', 'like', $search)
                        ->orWhere('contact_phone', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('resolution', 'like', $search);
                });
            });

        $tickets = $filteredQuery->orderByRaw("FIELD(status, 'open', 'pending', 'resolved', 'closed')")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'open' => (clone $baseQuery)->whereIn('status', ['open', 'pending'])->count(),
            'overdue' => (clone $baseQuery)->whereIn('status', ['open', 'pending'])->where('due_at', '<', now())->count(),
            'resolved_month' => (clone $baseQuery)->where('status', 'resolved')->whereBetween('resolved_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'satisfaction_avg' => round((float) (clone $baseQuery)->whereNotNull('satisfaction_score')->avg('satisfaction_score'), 1),
        ];

        $userColumns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));
        $usersQuery = User::query()->select($userColumns)->where('isActive', 1)->orderBy('name')->limit(200);
        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
        }

        return view('crm.service_tickets.index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'users' => $usersQuery->get(),
            'types' => CrmServiceTicket::TYPES,
            'channels' => CrmServiceTicket::CHANNELS,
            'priorities' => CrmServiceTicket::PRIORITIES,
            'statuses' => CrmServiceTicket::STATUSES,
            'filters' => $request->only(['type', 'status', 'priority', 'assigned_user_id', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'type' => ['required', 'in:' . implode(',', array_keys(CrmServiceTicket::TYPES))],
            'channel' => ['required', 'in:' . implode(',', array_keys(CrmServiceTicket::CHANNELS))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmServiceTicket::PRIORITIES))],
            'subject' => ['required', 'string', 'max:180'],
            'contact_name' => ['nullable', 'string', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'due_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $customer = !empty($data['customer_id']) ? $this->resolveCustomer($data['customer_id'], $user) : null;
        $nextId = (int) CrmServiceTicket::withTrashed()->max('id') + 1;
        $dueAt = !empty($data['due_at']) ? Carbon::parse($data['due_at']) : null;

        CrmServiceTicket::create([
            'tenant_id' => optional($customer)->tenant_id ?: $this->tenantId($user),
            'organization_id' => optional($customer)->organization_id ?: $this->organizationId($user),
            'customer_id' => optional($customer)->id,
            'assigned_user_id' => ($data['assigned_user_id'] ?? null) ?: $user->id,
            'code' => 'SD-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
            'type' => $data['type'],
            'channel' => $data['channel'],
            'priority' => $data['priority'],
            'status' => 'open',
            'subject' => $data['subject'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'description' => $data['description'] ?? null,
            'due_at' => $dueAt,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ActivityLogService::safeLog('create', 'CRM: store', null, ['section' => 'crm', 'event_key' => 'crm.store']);

        Alert::success('ثبت شد', 'تیکت خدمات پس از فروش ثبت شد.');

        return redirect()->route('crm.service-tickets.index');
    }

    public function updateStatus(Request $request, CrmServiceTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(CrmServiceTicket::STATUSES))],
            'resolution' => ['nullable', 'string'],
            'satisfaction_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'satisfaction_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $updates = [
            'status' => $data['status'],
            'resolution' => $data['resolution'] ?? $ticket->resolution,
            'satisfaction_score' => $data['satisfaction_score'] ?? $ticket->satisfaction_score,
            'satisfaction_note' => $data['satisfaction_note'] ?? $ticket->satisfaction_note,
            'updated_by' => Auth::id(),
        ];

        if (!$ticket->first_response_at && in_array($data['status'], ['pending', 'resolved', 'closed'], true)) {
            $updates['first_response_at'] = now();
        }

        if ($data['status'] === 'resolved') {
            $updates['resolved_at'] = $ticket->resolved_at ?: now();
            $updates['closed_at'] = null;
        } elseif ($data['status'] === 'closed') {
            $updates['resolved_at'] = $ticket->resolved_at ?: now();
            $updates['closed_at'] = $ticket->closed_at ?: now();
        } elseif (in_array($data['status'], ['open', 'pending'], true)) {
            $updates['closed_at'] = null;
        }

        $ticket->update($updates);

        ActivityLogService::safeLog('update', 'CRM: Status', null, ['section' => 'crm', 'event_key' => 'crm.updateStatus']);

        Alert::success('بروزرسانی شد', 'وضعیت تیکت خدمات تغییر کرد.');

        return redirect()->route('crm.service-tickets.index');
    }

    private function resolveCustomer($customerId, $user): Customers
    {
        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function authorizeTicket(CrmServiceTicket $ticket): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        abort_unless(CrmServiceTicket::query()->whereKey($ticket->id)->forOrganizations($user)->exists(), 403);
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
