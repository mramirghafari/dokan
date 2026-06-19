<?php

namespace App\Services;

use App\Models\Tenants;
use App\Models\TenantUserMembership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PanelMembershipService
{
    public const SESSION_TENANT_KEY = 'active_tenant_id';
    public const SESSION_ORGANIZATION_KEY = 'active_organization_id';
    public const SESSION_USER_KEY = 'active_panel_user_id';

    public function __construct(private TenantContextService $tenantContext)
    {
    }

    public function accessiblePanelsForMobile(string $mobile): Collection
    {
        $users = User::query()
            ->where('mobile', $mobile)
            ->where('isActive', 1)
            ->get();

        return $this->accessiblePanelsForUsers($users);
    }

    public function accessiblePanelsForUser(User $user): Collection
    {
        if ((int) $user->isGod === 1) {
            return Tenants::query()
                ->where('status', 1)
                ->orderBy('display_name')
                ->orderBy('name')
                ->get()
                ->map(fn (Tenants $tenant) => $this->panelPayload($user, $tenant->id, $user->id, true, $this->roleLabelForUser($user, $tenant->id)));
        }

        $users = User::query()
            ->where('mobile', $user->mobile)
            ->where('isActive', 1)
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $users = collect([$user]);
        }

        return $this->accessiblePanelsForUsers($users);
    }

    public function accessiblePanelsForUsers(Collection $users): Collection
    {
        $panels = collect();

        foreach ($users as $user) {
            if ($message = $this->panelBlockMessage($user)) {
                continue;
            }

            $memberships = TenantUserMembership::query()
                ->with(['tenant', 'organization'])
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->orderByDesc('is_admin')
                ->orderByDesc('last_used_at')
                ->orderBy('id')
                ->get();

            if ($memberships->isNotEmpty()) {
                foreach ($memberships as $membership) {
                    if (!$this->tenantIsAccessible($membership->tenant)) {
                        continue;
                    }

                    $panels->push($this->panelPayload(
                        $user,
                        (int) $membership->tenant_id,
                        (int) $user->id,
                        (bool) $membership->is_admin,
                        $this->roleLabelForUser($user, (int) $membership->tenant_id),
                        $membership->tenant,
                        $membership->organization_id
                    ));
                }

                continue;
            }

            $tenantId = (int) ($user->tenant_id ?: $user->tenants_id ?: 0);

            if ($tenantId <= 0) {
                continue;
            }

            $tenant = Tenants::query()->find($tenantId);

            if (!$this->tenantIsAccessible($tenant)) {
                continue;
            }

            $panels->push($this->panelPayload(
                $user,
                $tenantId,
                (int) $user->id,
                (int) $user->isAdmin === 1,
                $this->roleLabelForUser($user, $tenantId),
                $tenant,
                $this->tenantContext->organizationId($user)
            ));
        }

        return $panels
            ->sortByDesc('is_admin')
            // Panel switcher must list each tenant only once, even if a user has multiple rows/roles.
            ->unique(fn (array $panel) => (int) $panel['tenant_id'])
            ->sortBy('tenant_name')
            ->values();
    }

    public function activatePanel(User $currentUser, int $tenantId): User
    {
        $panels = $this->accessiblePanelsForUser($currentUser);
        $panel = $panels->firstWhere('tenant_id', $tenantId);

        if (!$panel) {
            abort(403, 'دسترسی به این پنل برای شما مجاز نیست.');
        }

        $user = User::query()->with(['roles', 'permissions'])->findOrFail($panel['user_id']);

        if ($message = $this->panelBlockMessage($user, $tenantId)) {
            abort(403, $message);
        }

        $organizationId = $panel['organization_id'] ?? $this->tenantContext->organizationId($user);

        session([
            self::SESSION_TENANT_KEY => $tenantId,
            self::SESSION_ORGANIZATION_KEY => $organizationId,
            self::SESSION_USER_KEY => $user->id,
        ]);

        $user->tenant_id = $tenantId;
        $user->tenants_id = $tenantId;

        if ($organizationId) {
            $user->organization_id = json_encode([(string) $organizationId, (int) $organizationId]);
        }

        $user->save();

        TenantUserMembership::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->update(['last_used_at' => now()]);

        Auth::login($user);

        return $user->fresh(['roles', 'permissions']);
    }

    public function redirectAfterLogin(User $user): RedirectResponse
    {
        $panels = $this->accessiblePanelsForUser($user);

        if ($panels->isEmpty()) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->with('error', 'هیچ پنل فعالی برای ورود شما یافت نشد.');
        }

        if ($panels->count() === 1) {
            $panel = $panels->first();
            $this->activatePanel($user, (int) $panel['tenant_id']);

            return redirect()
                ->route('index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'ورود موفق — به ' . ($panel['tenant_name'] ?? 'پنل') . ' خوش آمدید.',
                ]);
        }

        return redirect()->route('panel.select');
    }

    public function activeTenantId(?User $user = null): ?int
    {
        if (session()->has(self::SESSION_TENANT_KEY)) {
            return (int) session(self::SESSION_TENANT_KEY) ?: null;
        }

        return $this->tenantContext->tenantId($user);
    }

    public function activeOrganizationId(?User $user = null): ?int
    {
        if (session()->has(self::SESSION_ORGANIZATION_KEY)) {
            return (int) session(self::SESSION_ORGANIZATION_KEY) ?: null;
        }

        return $this->tenantContext->organizationId($user);
    }

    public function activePanel(?User $user = null): ?array
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return null;
        }

        $tenantId = $this->activeTenantId($user);

        if (!$tenantId) {
            return null;
        }

        return $this->accessiblePanelsForUser($user)->firstWhere('tenant_id', $tenantId);
    }

    public function roleLabelForActivePanel(?User $user = null): string
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return '';
        }

        if ((int) $user->isGod === 1) {
            return 'مدیر کل';
        }

        $panel = $this->activePanel($user);

        if ($panel && !empty($panel['role_label'])) {
            return $panel['role_label'];
        }

        $tenantId = $this->activeTenantId($user);

        return $this->roleLabelForUser($user, $tenantId);
    }

    public function roleLabelForUser(User $user, ?int $tenantId = null): string
    {
        if ((int) $user->isGod === 1) {
            return 'مدیر کل';
        }

        if ((int) $user->isAdmin === 1) {
            return 'مدیر پنل';
        }

        $roles = $user->relationLoaded('roles')
            ? $user->roles
            : $user->roles()->get();

        $filtered = $roles->filter(function ($role) use ($tenantId) {
            if (!$tenantId || empty($role->tenant_id)) {
                return true;
            }

            return (int) $role->tenant_id === (int) $tenantId;
        });

        $label = $filtered
            ->pluck('description')
            ->filter()
            ->unique()
            ->first();

        return $label ?: 'کاربر';
    }

    public function panelBlockMessage(User $user, ?int $tenantId = null): ?string
    {
        if ((int) $user->isActive === 0) {
            return 'کاربری توسط مدیریت غیرفعال شده است.';
        }

        if ((int) $user->isGod === 1) {
            return null;
        }

        $tenantId = $tenantId ?: (int) ($user->tenant_id ?: $user->tenants_id ?: 0);
        $tenant = $tenantId > 0 ? Tenants::query()->find($tenantId) : ($user->tenant ?: $user->legacyTenant);

        if (!$tenant) {
            return null;
        }

        if ((int) $tenant->status !== 1) {
            return 'پنل شما غیرفعال است. لطفا با پشتیبانی تماس بگیرید.';
        }

        if ($tenant->subscription_ends_at && \Carbon\Carbon::parse($tenant->subscription_ends_at)->endOfDay()->isPast()) {
            return 'اشتراک پنل شما به پایان رسیده است. لطفا برای تمدید با پشتیبانی تماس بگیرید.';
        }

        return null;
    }

    public function clearActivePanel(): void
    {
        session()->forget([
            self::SESSION_TENANT_KEY,
            self::SESSION_ORGANIZATION_KEY,
            self::SESSION_USER_KEY,
        ]);
    }

    public function syncMembership(User $user, int $tenantId, ?int $organizationId = null, bool $isAdmin = false): void
    {
        if ($tenantId <= 0) {
            return;
        }

        TenantUserMembership::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
            ],
            [
                'organization_id' => $organizationId,
                'is_admin' => $isAdmin,
                'is_active' => (int) $user->isActive === 1,
            ]
        );
    }

    private function tenantIsAccessible(?Tenants $tenant): bool
    {
        if (!$tenant || (int) $tenant->status !== 1) {
            return false;
        }

        if ($tenant->subscription_ends_at && \Carbon\Carbon::parse($tenant->subscription_ends_at)->endOfDay()->isPast()) {
            return false;
        }

        return true;
    }

    private function panelPayload(
        User $user,
        int $tenantId,
        int $userId,
        bool $isAdmin,
        string $roleLabel,
        ?Tenants $tenant = null,
        ?int $organizationId = null
    ): array {
        $tenant = $tenant ?: Tenants::query()->find($tenantId);

        return [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'tenant_name' => $tenant?->display_name ?: $tenant?->name ?: ('پنل #' . $tenantId),
            'tenant_code' => $tenant?->code,
            'role_label' => $roleLabel,
            'is_admin' => $isAdmin,
            'user_name' => $user->name,
        ];
    }
}
