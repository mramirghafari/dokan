<?php

namespace App\Services;

use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\CrmPublicApiClient;
use App\Models\CrmPublicApiRequestLog;
use App\Models\CrmServiceTicket;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CrmPublicApiService
{
    public function resolveClient(string $clientCode, Request $request, string $scope): CrmPublicApiClient
    {
        $client = CrmPublicApiClient::where('code', $clientCode)->where('is_active', true)->firstOrFail();
        $token = $request->bearerToken() ?: $request->header('X-CRM-Token') ?: $request->query('token');

        abort_unless($token && hash_equals($client->token_hash, hash('sha256', $token)), 401, 'Invalid CRM API token.');
        abort_unless($client->hasScope($scope), 403, 'CRM API scope is not allowed for this client.');

        if ($client->allowed_ips && !$this->ipAllowed($client->allowed_ips, $request->ip())) {
            abort(403, 'CRM API source IP is not allowed.');
        }

        $client->forceFill([
            'request_count' => (int) $client->request_count + 1,
            'last_used_at' => now(),
        ])->save();

        return $client;
    }

    public function storeLead(CrmPublicApiClient $client, array $data, Request $request): CrmLead
    {
        return DB::transaction(function () use ($client, $data, $request) {
            $duplicateCustomer = $this->duplicateCustomer($data['mobile'] ?? null, $client);
            $duplicateLead = $this->duplicateLead($data['mobile'] ?? null, $client);
            $status = $duplicateCustomer || $duplicateLead ? 'duplicate' : 'open';
            $nextId = (int) CrmLead::withTrashed()->max('id') + 1;

            $lead = CrmLead::create([
                'tenant_id' => $client->tenant_id,
                'organization_id' => $client->organization_id,
                'owner_user_id' => $data['owner_user_id'] ?? $client->created_by,
                'code' => 'LEAD-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'company_name' => $data['company_name'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'city' => $data['city'] ?? null,
                'source' => $data['source'] ?? 'website',
                'campaign' => $data['campaign'] ?? null,
                'score' => $data['score'] ?? 0,
                'stage' => 'new',
                'status' => $status,
                'priority' => $data['priority'] ?? 'normal',
                'duplicate_status' => $duplicateCustomer ? 'customer' : ($duplicateLead ? 'lead' : 'none'),
                'duplicate_customer_id' => optional($duplicateCustomer)->id,
                'duplicate_lead_id' => optional($duplicateLead)->id,
                'notes' => $data['notes'] ?? null,
                'created_by' => $client->created_by,
                'updated_by' => $client->created_by,
            ]);

            $this->log($client, 'leads', $request, $data['external_id'] ?? null, $lead, $status);

            return $lead;
        });
    }

    public function storeTicket(CrmPublicApiClient $client, array $data, Request $request): CrmServiceTicket
    {
        return DB::transaction(function () use ($client, $data, $request) {
            $customer = !empty($data['customer_id']) ? $this->resolveCustomer((int) $data['customer_id'], $client) : null;
            $nextId = (int) CrmServiceTicket::withTrashed()->max('id') + 1;
            $dueAt = !empty($data['due_at']) ? Carbon::parse($data['due_at']) : null;

            $ticket = CrmServiceTicket::create([
                'tenant_id' => optional($customer)->tenant_id ?: $client->tenant_id,
                'organization_id' => optional($customer)->organization_id ?: $client->organization_id,
                'customer_id' => optional($customer)->id,
                'assigned_user_id' => $data['assigned_user_id'] ?? $client->created_by,
                'code' => 'SD-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
                'type' => $data['type'] ?? 'support',
                'channel' => $data['channel'] ?? 'website',
                'priority' => $data['priority'] ?? 'normal',
                'status' => 'open',
                'subject' => $data['subject'],
                'contact_name' => $data['contact_name'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'description' => $data['description'] ?? null,
                'due_at' => $dueAt,
                'created_by' => $client->created_by,
                'updated_by' => $client->created_by,
            ]);

            $this->log($client, 'tickets', $request, $data['external_id'] ?? null, $ticket);

            return $ticket;
        });
    }

    public function storeOpportunity(CrmPublicApiClient $client, array $data, Request $request): CrmOpportunity
    {
        return DB::transaction(function () use ($client, $data, $request) {
            $customer = $this->resolveCustomer((int) $data['customer_id'], $client);
            $nextId = (int) CrmOpportunity::withTrashed()->max('id') + 1;

            $opportunity = CrmOpportunity::create([
                'tenant_id' => $customer->tenant_id ?: $client->tenant_id,
                'organization_id' => $customer->organization_id ?: $client->organization_id,
                'customer_id' => $customer->id,
                'assigned_user_id' => $data['assigned_user_id'] ?? $client->created_by,
                'code' => 'OPP-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
                'title' => $data['title'],
                'stage' => $data['stage'] ?? 'new',
                'priority' => $data['priority'] ?? 'normal',
                'status' => 'open',
                'amount' => $data['amount'] ?? 0,
                'probability_percent' => $data['probability_percent'] ?? 20,
                'expected_close_date_en' => $data['expected_close_date_en'] ?? null,
                'expected_close_date_fa' => !empty($data['expected_close_date_en']) ? verta($data['expected_close_date_en'])->format('Y/m/d') : null,
                'next_action_date_en' => $data['next_action_date_en'] ?? null,
                'next_action_date_fa' => !empty($data['next_action_date_en']) ? verta($data['next_action_date_en'])->format('Y/m/d') : null,
                'description' => $data['description'] ?? null,
                'created_by' => $client->created_by,
                'updated_by' => $client->created_by,
            ]);

            $this->log($client, 'opportunities', $request, $data['external_id'] ?? null, $opportunity);

            return $opportunity;
        });
    }

    private function resolveCustomer(int $customerId, CrmPublicApiClient $client): Customers
    {
        return Customers::query()
            ->whereKey($customerId)
            ->when($client->tenant_id, fn($query) => $query->where('tenant_id', $client->tenant_id))
            ->when($client->organization_id, fn($query) => $query->where('organization_id', $client->organization_id))
            ->firstOrFail();
    }

    private function duplicateCustomer(?string $mobile, CrmPublicApiClient $client): ?Customers
    {
        if (!$mobile) {
            return null;
        }

        return Customers::where('mobile', $mobile)
            ->when($client->tenant_id, fn($query) => $query->where('tenant_id', $client->tenant_id))
            ->when($client->organization_id, fn($query) => $query->where('organization_id', $client->organization_id))
            ->first();
    }

    private function duplicateLead(?string $mobile, CrmPublicApiClient $client): ?CrmLead
    {
        if (!$mobile) {
            return null;
        }

        return CrmLead::where('mobile', $mobile)
            ->whereIn('status', ['open', 'duplicate'])
            ->when($client->tenant_id, fn($query) => $query->where('tenant_id', $client->tenant_id))
            ->when($client->organization_id, fn($query) => $query->where('organization_id', $client->organization_id))
            ->latest('id')
            ->first();
    }

    private function ipAllowed(string $allowedIps, string $ip): bool
    {
        return collect(explode(',', $allowedIps))->map(fn($item) => trim($item))->filter()->contains($ip);
    }

    private function log(CrmPublicApiClient $client, string $endpoint, Request $request, ?string $externalId, $model, string $status = 'processed'): void
    {
        CrmPublicApiRequestLog::create([
            'crm_public_api_client_id' => $client->id,
            'tenant_id' => $client->tenant_id,
            'organization_id' => $client->organization_id,
            'endpoint' => $endpoint,
            'method' => $request->method(),
            'external_id' => $externalId,
            'status' => $status,
            'ip_address' => $request->ip(),
            'reference_id' => $model->id,
            'reference_type' => get_class($model),
            'payload_snapshot' => collect($request->except(['token']))->only(['external_id', 'name', 'subject', 'title', 'mobile', 'priority', 'source'])->all(),
        ]);
    }
}
