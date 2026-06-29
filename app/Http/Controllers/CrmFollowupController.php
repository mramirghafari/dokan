<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmFollowup;
use App\Models\Customers;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class CrmFollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'updateStatus']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = CrmFollowup::query()->with(['customer', 'employee', 'assignedUser', 'creator']);

        if ((int) $user->isGod !== 1) {
            $baseQuery->forOrganizations($user);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('subject_type'), fn($query) => $query->where('subject_type', $request->subject_type))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn($query) => $query->where('priority', $request->priority))
            ->when($request->filled('assigned_user_id'), fn($query) => $query->where('assigned_user_id', $request->assigned_user_id))
            ->when($request->get('due_bucket') === 'today', fn($query) => $query->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', now()->toDateString()))
            ->when($request->get('due_bucket') === 'overdue', fn($query) => $query->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', '<', now()->toDateString()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('outcome', 'like', $search);
                });
            });

        $followups = $filteredQuery->orderByRaw("FIELD(status, 'open', 'in_progress', 'done', 'canceled')")
            ->orderByRaw('due_date_en IS NULL')
            ->orderBy('due_date_en')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'open' => (clone $baseQuery)->whereIn('status', ['open', 'in_progress'])->count(),
            'overdue' => (clone $baseQuery)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', '<', now()->toDateString())->count(),
            'today' => (clone $baseQuery)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', now()->toDateString())->count(),
            'done_month' => (clone $baseQuery)->where('status', 'done')->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        $userColumns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));
        $usersQuery = User::query()->select($userColumns)->where('isActive', 1)->orderBy('name')->limit(200);
        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
        }

        return view('crm.followups.index', [
            'followups' => $followups,
            'stats' => $stats,
            'users' => $usersQuery->get(),
            'types' => CrmFollowup::TYPES,
            'priorities' => CrmFollowup::PRIORITIES,
            'statuses' => CrmFollowup::STATUSES,
            'filters' => $request->only(['subject_type', 'status', 'priority', 'assigned_user_id', 'search', 'due_bucket']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_type' => ['required', 'in:customer,employee'],
            'customer_id' => ['nullable', 'required_if:subject_type,customer', 'integer'],
            'employee_id' => ['nullable', 'required_if:subject_type,employee', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'type' => ['required', 'in:' . implode(',', array_keys(CrmFollowup::TYPES))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmFollowup::PRIORITIES))],
            'title' => ['required', 'string', 'max:180'],
            'due_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $subject = $this->resolveSubject($data['subject_type'], $data['customer_id'] ?? null, $data['employee_id'] ?? null, $user);
        $dueDate = !empty($data['due_date_en']) ? Carbon::parse($data['due_date_en'])->toDateString() : null;

        $followup = CrmFollowup::create([
            'tenant_id' => $subject->tenant_id ?: $user->tenant_id,
            'organization_id' => $subject->organization_id ?: $this->organizationId($user),
            'subject_type' => $data['subject_type'],
            'customer_id' => $data['subject_type'] === 'customer' ? $subject->id : null,
            'employee_id' => $data['subject_type'] === 'employee' ? $subject->id : null,
            'assigned_user_id' => $data['assigned_user_id'] ?: $user->id,
            'type' => $data['type'],
            'priority' => $data['priority'],
            'status' => 'open',
            'title' => $data['title'],
            'due_date_en' => $dueDate,
            'due_date_fa' => $dueDate ? verta($dueDate)->format('Y/m/d') : null,
            'description' => $data['description'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ActivityLogService::safeLogModel('create', 'ایجاد پیگیری CRM: ' . $followup->title, $followup, ['section' => 'crm', 'event_key' => 'crm.followup.created']);

        Alert::success('ثبت شد', 'پیگیری CRM با موفقیت ثبت شد.');

        return redirect()->route('crm.followups.index');
    }

    public function updateStatus(Request $request, CrmFollowup $followup)
    {
        $this->authorizeFollowup($followup);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(CrmFollowup::STATUSES))],
            'outcome' => ['nullable', 'string'],
        ]);

        $followup->update([
            'status' => $data['status'],
            'outcome' => $data['outcome'] ?? $followup->outcome,
            'completed_at' => $data['status'] === 'done' ? now() : null,
            'updated_by' => Auth::id(),
        ]);

        ActivityLogService::safeLogModel('update', 'تغییر وضعیت پیگیری CRM: ' . $followup->title, $followup, ['section' => 'crm', 'event_key' => 'crm.followup.status_updated']);

        Alert::success('بروزرسانی شد', 'وضعیت پیگیری تغییر کرد.');

        return redirect()->route('crm.followups.index');
    }

    private function resolveSubject(string $subjectType, $customerId, $employeeId, $user)
    {
        if ($subjectType === 'employee') {
            $query = Employee::query();

            if ((int) $user->isGod !== 1) {
                $query->forOrganizations($user);
            }

            return $query->findOrFail($employeeId);
        }

        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function authorizeFollowup(CrmFollowup $followup): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        $allowed = CrmFollowup::query()->whereKey($followup->id)->forOrganizations($user)->exists();

        abort_unless($allowed, 403);
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
