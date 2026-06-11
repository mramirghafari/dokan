<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Permission;
use App\Models\Tenants;
use App\Services\PermissionScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use RealRashid\SweetAlert\Facades\Alert;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:roles,user')->only(['index', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
    }

    public function index(Request $request)
    {
        $scopeService = app(PermissionScopeService::class);
        $targetTenantId = $scopeService->targetTenantId($request);
        $tenants = $this->availableTenants();
        $scopeOptions = $scopeService->scopeOptions($targetTenantId);
        $scopeLabels = $scopeService->scopeLabels();
        $selectedRoleScopes = collect($scopeLabels)->mapWithKeys(fn($label, $type) => [$type => []])->toArray();
        $roles = $scopeService->rolesForUser(auth()->user())->with('scopes')->orderBy('description')->get();
        $roleScopeSummaries = $roles->mapWithKeys(fn($role) => [$role->id => $scopeService->describeRoleScopes($role)]);
        $permissions = $scopeService->permissionsForUser(auth()->user())->orderBy('description')->get();
        return view('roles.index', compact('roles', 'permissions', 'tenants', 'targetTenantId', 'scopeOptions', 'scopeLabels', 'selectedRoleScopes', 'roleScopeSummaries'));
    }

    public function store(Request $request)
    {
        $scopeService = app(PermissionScopeService::class);
        $tenantId = $scopeService->targetTenantId($request);

        $role = Role::create([
            'title' => $request->title,
            'description' => $request->description,
            'tenant_id' => $tenantId,
            'scope_type' => 'tenant',
            'isActive' => 1,
        ]);
        $role->permissions()->sync($request->input('permissions', []));
        $scopeType = $scopeService->syncRoleScopes($role, $tenantId, $request->input('scopes', []), auth()->id());
        $role->update(['scope_type' => $scopeType]);
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $request->ip(),
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'نقش جدید ایجاد شد' . '-' . $role->description
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');

        return back();
    }

    public function edit(Role $role)
    {
        $scopeService = app(PermissionScopeService::class);
        $targetTenantId = $role->tenant_id ?: $scopeService->tenantIdForUser(auth()->user());
        $tenants = $this->availableTenants();
        $scopeOptions = $scopeService->scopeOptions($targetTenantId);
        $scopeLabels = $scopeService->scopeLabels();
        $selectedRoleScopes = $scopeService->roleScopeValues($role);
        $roles = $scopeService->rolesForUser(auth()->user())->with('scopes')->orderBy('description')->get();
        $roleScopeSummaries = $roles->mapWithKeys(fn($role) => [$role->id => $scopeService->describeRoleScopes($role)]);
        $permissions = $scopeService->permissionsForUser(auth()->user())->orderBy('description')->get();
        return view('roles.edit', compact('role', 'roles', 'permissions', 'tenants', 'targetTenantId', 'scopeOptions', 'scopeLabels', 'selectedRoleScopes', 'roleScopeSummaries'));
    }

    public function update(Request $request, Role $role)
    {
        $scopeService = app(PermissionScopeService::class);
        $tenantId = $role->tenant_id ?: $scopeService->targetTenantId($request);
        $isActive = $request->input('isActive') === 'on' ? 1 : 0;
        $role->update([
            'title' => $request->title,
            'description' => $request->description,
            'tenant_id' => $tenantId,
            'scope_type' => $role->scope_type ?: 'tenant',
            'isActive' => $isActive
        ]);
        $role->permissions()->sync($request->input('permissions', []));
        $scopeType = $scopeService->syncRoleScopes($role, $tenantId, $request->input('scopes', []), auth()->id());
        $role->update(['scope_type' => $scopeType]);
        $roles = $scopeService->rolesForUser(auth()->user())->with('scopes')->orderBy('description')->get();
        $permissions = $scopeService->permissionsForUser(auth()->user())->orderBy('description')->get();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $request->ip(),
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => 'نقش ویرایش شد' . '-' . $role->description
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        return redirect()->route('roles.edit', compact('role', 'roles', 'permissions'));
    }

    private function availableTenants()
    {
        $user = auth()->user();

        if ($user && $user->isGod == 1) {
            return Tenants::orderBy('name')->get();
        }

        $tenantId = $user ? ($user->tenant_id ?: $user->tenants_id) : null;

        return $tenantId ? Tenants::where('id', $tenantId)->get() : collect();
    }
}
