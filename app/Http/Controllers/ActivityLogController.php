<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ActivityLogController extends Controller
{
    private const ACTION_LABELS = [
        'create' => 'ایجاد',
        'update' => 'ویرایش',
        'delete' => 'حذف',
        'restore' => 'بازیابی',
        'forceDelete' => 'حذف دائم',
        'login' => 'ورود',
        'logout' => 'خروج',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($this->canViewActivityLogs(Auth::user())) {
                return $next($request);
            }

            abort(403);
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Log::query()
            ->with(['user:id,name'])
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $teamUserIds = $this->teamUserIds($user);
            $query->whereIn('user_id', $teamUserIds);
        }

        $query->when($request->filled('user_id'), fn ($builder) => $builder->where('user_id', (int) $request->user_id))
            ->when($request->filled('action'), fn ($builder) => $builder->where('action', $request->action))
            ->when($request->filled('search'), function ($builder) use ($request) {
                $term = '%' . trim((string) $request->search) . '%';
                $builder->where(function ($inner) use ($term) {
                    $inner->where('description', 'like', $term)
                        ->orWhere('ip', 'like', $term)
                        ->orWhere('section', 'like', $term);
                });
            });

        $logs = $query->paginate(50)->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'users' => $this->teamUsers($user),
            'actionLabels' => self::ACTION_LABELS,
            'filters' => $request->only(['user_id', 'action', 'search']),
        ]);
    }

    public static function canViewActivityLogs(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $user->isGod === 1 || (int) $user->isAdmin === 1) {
            return true;
        }

        return $user->rolesForActiveTenant()->contains(fn ($role) => $role->title === 'panel_manager');
    }

    private function teamUserIds(User $user): array
    {
        return $this->teamUsersQuery($user)->pluck('id')->all();
    }

    private function teamUsers(User $user)
    {
        return $this->teamUsersQuery($user)->orderBy('name')->get(['id', 'name']);
    }

    private function teamUsersQuery(User $user)
    {
        $columns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'tenants_id', 'organization_id', 'isActive']));
        $query = User::query()->select($columns)->where('isActive', 1);

        if ((int) $user->isGod === 1) {
            return $query;
        }

        $tenantId = app(TenantContextService::class)->tenantId($user);

        if ($tenantId) {
            return $query->where(function ($inner) use ($tenantId) {
                $inner->where('tenant_id', $tenantId)
                    ->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->forOrganizations($user);
    }
}
