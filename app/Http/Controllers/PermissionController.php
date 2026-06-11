<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\PermissionScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionNamingService;
use RealRashid\SweetAlert\Facades\Alert;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:permissions,user')->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $scopeService = app(PermissionScopeService::class);
        $permissions = $scopeService->permissionsForUser(auth()->user())->orderBy('description')->get();
        $roles = $scopeService->rolesForUser(auth()->user())->where('isActive', 1)->orderBy('description')->get();
        return view('permissions.index', compact('permissions', 'roles'));
    }

    public function store(Request $request)
    {
        $scopeService = app(PermissionScopeService::class);
        $namingService = app(PermissionNamingService::class);
        $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'canonical_title' => ['nullable', 'string', 'max:190', $namingService->canonicalRule()],
            'description' => ['required', 'string', 'max:190'],
        ]);
        $permission = Permission::create(array_merge([
            'title' => $request->title,
            'description' => $request->description,
            'tenant_id' => $scopeService->targetTenantId($request),
            'isActive' => 1,
        ], $namingService->payload($request->title, $request->only(['canonical_title', 'module_key', 'resource_key', 'action_key']))));
        $permission->roles()->sync($request->input('roles', []));
        $namingService->syncAliases($permission->fresh());
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $request->ip(),
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'سطح دسترسی ایجاد شد' . '-' . $permission->description
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');

        return back();
    }

    public function edit(Permission $permission)
    {
        $scopeService = app(PermissionScopeService::class);
        $permissions = $scopeService->permissionsForUser(auth()->user())->orderBy('description')->get();
        $roles = $scopeService->rolesForUser(auth()->user())->where('isActive', 1)->orderBy('description')->get();

        return view('permissions.edit', compact('permission', 'permissions', 'roles'));
    }

    public function update(Request $request, Permission $permission)
    {
        $scopeService = app(PermissionScopeService::class);
        $namingService = app(PermissionNamingService::class);
        $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'canonical_title' => ['nullable', 'string', 'max:190', $namingService->canonicalRule()],
            'description' => ['required', 'string', 'max:190'],
        ]);
        $isActive = $request->input('isActive') === 'on' ? 1 : 0;
        $permission->update(array_merge([
            'title' => $request->title,
            'description' => $request->description,
            'tenant_id' => $permission->tenant_id ?: $scopeService->targetTenantId($request),
            'isActive' => $isActive
        ], $namingService->payload($request->title, $request->only(['canonical_title', 'module_key', 'resource_key', 'action_key']))));
        $permission->roles()->sync($request->input('roles', []));
        $namingService->syncAliases($permission->fresh());
        $roles = $scopeService->rolesForUser(auth()->user())->where('isActive', 1)->orderBy('description')->get();

        $permissions = $scopeService->permissionsForUser(auth()->user())->orderBy('description')->get();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $request->ip(),
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => 'سطح دسترسی ویرایش شد' . '-' . $permission->description
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        return redirect()->route('permissions.edit', compact('permission', 'permissions', 'roles'));
    }
}
