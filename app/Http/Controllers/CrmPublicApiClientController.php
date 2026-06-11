<?php

namespace App\Http\Controllers;

use App\Models\CrmPublicApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class CrmPublicApiClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'toggle']);
    }

    public function index()
    {
        $user = Auth::user();
        $query = CrmPublicApiClient::query()->latest('id');

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return view('crm.public_api.index', [
            'clients' => $query->paginate(30),
            'scopes' => CrmPublicApiClient::SCOPES,
            'newToken' => session('crm_public_api_token'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['in:' . implode(',', array_keys(CrmPublicApiClient::SCOPES))],
            'allowed_ips' => ['nullable', 'string', 'max:500'],
        ]);
        $user = Auth::user();
        $token = Str::random(64);
        $client = CrmPublicApiClient::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'code' => 'crm-' . Str::lower(Str::random(10)),
            'title' => $data['title'],
            'token_hash' => hash('sha256', $token),
            'scopes' => $data['scopes'],
            'allowed_ips' => $data['allowed_ips'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Alert::success('کلاینت API ساخته شد', 'توکن فقط همین بار نمایش داده می شود.');

        return redirect()->route('crm.public-api.index')->with('crm_public_api_token', [
            'code' => $client->code,
            'token' => $token,
        ]);
    }

    public function toggle(CrmPublicApiClient $client)
    {
        $user = Auth::user();

        if ((int) $user->isGod !== 1) {
            abort_unless(CrmPublicApiClient::query()->whereKey($client->id)->forOrganizations($user)->exists(), 403);
        }

        $client->update([
            'is_active' => !$client->is_active,
            'updated_by' => $user->id,
        ]);

        Alert::success('وضعیت بروزرسانی شد', 'دسترسی API تغییر کرد.');

        return redirect()->route('crm.public-api.index');
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
