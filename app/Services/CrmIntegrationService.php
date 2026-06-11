<?php

namespace App\Services;

use App\Models\CrmCallLog;
use App\Models\CrmFollowup;
use App\Models\CrmIntegrationConnection;
use App\Models\CrmIntegrationSyncLog;
use App\Models\Customers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CrmIntegrationService
{
    public function adminState($user): array
    {
        $connections = $this->scopeConnections(CrmIntegrationConnection::query(), $user)
            ->withCount('logs')
            ->latest('id')
            ->get();

        return [
            'connections' => $connections,
            'logs' => $this->scopeLogs(CrmIntegrationSyncLog::query()->with('connection'), $user)->latest('id')->limit(80)->get(),
            'followups' => $this->scopeFollowups(CrmFollowup::query()->whereIn('status', ['open', 'in_progress'])->latest('id')->limit(200), $user)->get(),
            'calls' => $this->scopeCalls(CrmCallLog::query()->latest('id')->limit(200), $user)->get(),
            'customers' => $this->scopeCustomers(Customers::query()->latest('id')->limit(200), $user)->get(),
            'stats' => [
                'active_connections' => $connections->where('is_active', true)->count(),
                'voip' => $connections->where('type', 'voip')->count(),
                'calendar' => $connections->where('type', 'calendar')->count(),
                'drive' => $connections->where('type', 'drive')->count(),
                'failed_logs' => $this->scopeLogs(CrmIntegrationSyncLog::query(), $user)->where('status', 'failed')->count(),
            ],
        ];
    }

    public function createConnection($user, array $data): array
    {
        $rawSecret = $data['webhook_secret'] ?? Str::random(40);

        $connection = CrmIntegrationConnection::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'type' => $data['type'],
            'provider' => $data['provider'] ?? 'generic',
            'title' => $data['title'],
            'endpoint_url' => $data['endpoint_url'] ?? null,
            'webhook_secret_hash' => hash('sha256', $rawSecret),
            'settings' => array_filter([
                'calendar_name' => $data['calendar_name'] ?? null,
                'drive_folder' => $data['drive_folder'] ?? null,
                'voip_line' => $data['voip_line'] ?? null,
                'voip_context' => $data['voip_context'] ?? null,
                'voip_caller_id' => $data['voip_caller_id'] ?? null,
            ]),
            'credentials' => array_filter([
                'outbound_auth_token' => $data['outbound_auth_token'] ?? null,
            ]),
            'scopes' => $data['scopes'] ?? $this->defaultScopes($data['type']),
            'is_active' => true,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);

        return [$connection, $rawSecret];
    }

    public function toggle(CrmIntegrationConnection $connection, $user): CrmIntegrationConnection
    {
        $this->authorizeConnection($connection, $user);
        $connection->update(['is_active' => !$connection->is_active, 'updated_by' => $user?->id]);

        return $connection->refresh();
    }

    public function handleVoipWebhook(CrmIntegrationConnection $connection, Request $request): CrmIntegrationSyncLog
    {
        abort_unless($connection->type === 'voip' && $connection->is_active, 404);
        $secret = (string) ($request->header('X-CRM-Webhook-Secret') ?: $request->input('secret'));
        abort_unless($connection->webhook_secret_hash && hash_equals($connection->webhook_secret_hash, hash('sha256', $secret)), 403);

        $payload = $request->except(['secret', '_token']);
        $externalId = (string) (Arr::get($payload, 'external_id') ?: Arr::get($payload, 'call_id') ?: Arr::get($payload, 'unique_id'));
        $existing = $externalId !== '' ? CrmIntegrationSyncLog::query()
            ->where('crm_integration_connection_id', $connection->id)
            ->where('integration_type', 'voip')
            ->where('external_id', $externalId)
            ->where('operation', 'voip_call_webhook')
            ->first() : null;

        if ($existing) {
            return $existing;
        }

        $customer = $this->customerByPhone($connection, (string) (Arr::get($payload, 'phone_number') ?: Arr::get($payload, 'caller') ?: Arr::get($payload, 'from')));
        $call = CrmCallLog::create([
            'tenant_id' => $connection->tenant_id,
            'organization_id' => $connection->organization_id,
            'customer_id' => $customer?->id,
            'code' => 'VOIP-' . now()->format('Y') . '-' . str_pad((string) ((int) CrmCallLog::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT),
            'direction' => $this->normalizeDirection((string) Arr::get($payload, 'direction', 'inbound')),
            'channel' => 'voip',
            'status' => Arr::get($payload, 'status') === 'missed' ? 'needs_followup' : 'completed',
            'result' => Arr::get($payload, 'result') ?: (Arr::get($payload, 'status') === 'missed' ? 'no_answer' : 'answered'),
            'priority' => Arr::get($payload, 'priority', 'normal'),
            'subject' => Arr::get($payload, 'subject') ?: 'تماس VoIP ورودی',
            'phone_number' => Arr::get($payload, 'phone_number') ?: Arr::get($payload, 'caller') ?: Arr::get($payload, 'from'),
            'contact_name' => Arr::get($payload, 'contact_name') ?: $customer?->name,
            'call_started_at' => Arr::get($payload, 'started_at') ?: now(),
            'call_ended_at' => Arr::get($payload, 'ended_at'),
            'duration_seconds' => (int) Arr::get($payload, 'duration_seconds', 0),
            'recording_url' => Arr::get($payload, 'recording_url'),
            'notes' => Arr::get($payload, 'notes'),
        ]);

        return $this->log($connection, 'inbound', 'voip_call_webhook', 'synced', $payload, ['call_log_id' => $call->id], $call, $externalId, 'تماس VoIP ثبت شد.');
    }

    public function syncFollowupToCalendar(CrmIntegrationConnection $connection, CrmFollowup $followup, $user = null): CrmIntegrationSyncLog
    {
        $this->authorizeConnection($connection, $user);
        abort_unless($connection->type === 'calendar' && $connection->is_active, 422);

        $payload = [
            'title' => $followup->title,
            'description' => $followup->description,
            'due_date' => optional($followup->due_date_en)->toDateString(),
            'priority' => $followup->priority,
            'status' => $followup->status,
            'customer_id' => $followup->customer_id,
            'employee_id' => $followup->employee_id,
            'assigned_user_id' => $followup->assigned_user_id,
        ];

        return $this->dispatchOutbound($connection, 'calendar_event_upsert', $payload, $followup, 'CAL-FOLLOWUP-' . $followup->id, $user);
    }

    public function syncDueCalendarEvents($user = null, int $limit = 200): int
    {
        $count = 0;
        $connections = $this->scopeConnections(CrmIntegrationConnection::query()->where('type', 'calendar')->where('is_active', true), $user)->get();

        foreach ($connections as $connection) {
            $followups = $this->scopeFollowups(CrmFollowup::query()
                ->whereIn('status', ['open', 'in_progress'])
                ->whereNotNull('due_date_en')
                ->whereDate('due_date_en', '>=', now()->subDay()->toDateString())
                ->whereDate('due_date_en', '<=', now()->addDays(30)->toDateString())
                ->limit($limit), $user ?: $connection)
                ->get();

            foreach ($followups as $followup) {
                $log = $this->syncFollowupToCalendar($connection, $followup, $user);
                if ($log->wasRecentlyCreated || in_array($log->status, ['queued', 'synced'], true)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function recordDriveLink(CrmIntegrationConnection $connection, array $data, $user): CrmIntegrationSyncLog
    {
        $this->authorizeConnection($connection, $user);
        abort_unless($connection->type === 'drive' && $connection->is_active, 422);

        $syncable = $this->resolveSyncable($data['target_type'], (int) $data['target_id'], $user);
        $externalId = $data['external_id'] ?? 'DRV-' . md5($data['external_url'] . '|' . get_class($syncable) . '|' . $syncable->getKey());
        $existing = CrmIntegrationSyncLog::query()
            ->where('crm_integration_connection_id', $connection->id)
            ->where('operation', 'drive_file_link')
            ->where('external_id', $externalId)
            ->where('syncable_type', get_class($syncable))
            ->where('syncable_id', $syncable->getKey())
            ->first();

        if ($existing) {
            return $existing;
        }

        $log = $this->log($connection, 'inbound', 'drive_file_link', 'synced', [
            'title' => $data['title'],
            'external_url' => $data['external_url'],
            'note' => $data['note'] ?? null,
        ], ['linked_at' => now()->toDateTimeString()], $syncable, $externalId, 'لینک فایل بیرونی به CRM وصل شد.', $user?->id);

        if ($connection->endpoint_url) {
            $this->dispatchOutbound($connection, 'drive_file_metadata_upsert', [
                'title' => $data['title'],
                'external_url' => $data['external_url'],
                'note' => $data['note'] ?? null,
                'target_type' => $data['target_type'],
                'target_id' => $syncable->getKey(),
            ], $syncable, $externalId, $user);
        }

        return $log;
    }

    public function startClickToCall(CrmIntegrationConnection $connection, array $data, $user): CrmIntegrationSyncLog
    {
        $this->authorizeConnection($connection, $user);
        abort_unless($connection->type === 'voip' && $connection->is_active, 422);

        $customer = !empty($data['customer_id']) ? $this->resolveSyncable('customer', (int) $data['customer_id'], $user) : null;
        $phone = trim((string) ($data['phone_number'] ?? $customer?->mobile ?? $customer?->phone));
        abort_unless($phone !== '', 422);

        $call = CrmCallLog::create([
            'tenant_id' => $connection->tenant_id ?: $this->tenantId($user),
            'organization_id' => $connection->organization_id ?: $this->organizationId($user),
            'customer_id' => $customer?->id,
            'assigned_user_id' => $user?->id,
            'code' => 'OUT-' . now()->format('Y') . '-' . str_pad((string) ((int) CrmCallLog::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT),
            'direction' => 'outbound',
            'channel' => 'voip',
            'status' => 'open',
            'priority' => $data['priority'] ?? 'normal',
            'subject' => $data['subject'] ?? 'تماس خروجی VoIP',
            'phone_number' => $phone,
            'contact_name' => $data['contact_name'] ?? $customer?->name,
            'call_started_at' => now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);

        return $this->dispatchOutbound($connection, 'voip_click_to_call', [
            'phone_number' => $phone,
            'customer_id' => $customer?->id,
            'contact_name' => $data['contact_name'] ?? $customer?->name,
            'subject' => $call->subject,
            'call_log_id' => $call->id,
            'agent_user_id' => $user?->id,
        ], $call, 'VOIP-OUT-' . $call->id, $user);
    }

    private function dispatchOutbound(CrmIntegrationConnection $connection, string $operation, array $payload, Model $syncable, string $externalId, $user = null): CrmIntegrationSyncLog
    {
        $existingSynced = CrmIntegrationSyncLog::query()
            ->where('crm_integration_connection_id', $connection->id)
            ->where('operation', $operation)
            ->where('external_id', $externalId)
            ->where('syncable_type', get_class($syncable))
            ->where('syncable_id', $syncable->getKey())
            ->where('status', 'synced')
            ->first();

        if ($existingSynced) {
            return $existingSynced;
        }

        $request = $this->providerRequest($connection, $operation, $payload, $externalId);

        if (!$connection->endpoint_url) {
            $existingQueued = CrmIntegrationSyncLog::query()
                ->where('crm_integration_connection_id', $connection->id)
                ->where('operation', $operation)
                ->where('external_id', $externalId)
                ->where('syncable_type', get_class($syncable))
                ->where('syncable_id', $syncable->getKey())
                ->where('status', 'queued')
                ->first();

            if ($existingQueued) {
                return $existingQueued;
            }

            return $this->log($connection, 'outbound', $operation, 'queued', $request['body'], ['queued_without_endpoint' => true, 'adapter' => $request['adapter']], $syncable, $externalId, 'endpoint تنظیم نشده؛ رویداد provider-specific برای sync بعدی در صف ثبت شد.', $user?->id);
        }

        try {
            $response = Http::timeout(15)->acceptJson()->withHeaders($request['headers'])->asJson()->send($request['method'], $request['url'], ['json' => $request['body']]);
            $body = $response->json() ?: ['body' => $response->body()];
            $status = $response->successful() ? 'synced' : 'failed';

            return $this->log($connection, 'outbound', $operation, $status, $request['body'], ['http_status' => $response->status(), 'body' => $body, 'adapter' => $request['adapter']], $syncable, $externalId, $status === 'synced' ? 'sync provider-specific موفق بود.' : 'provider پاسخ موفق نداد.', $user?->id);
        } catch (Throwable $exception) {
            return $this->log($connection, 'outbound', $operation, 'failed', $request['body'], ['error' => $exception->getMessage(), 'adapter' => $request['adapter']], $syncable, $externalId, 'خطا در ارتباط با provider.', $user?->id);
        }
    }

    private function providerRequest(CrmIntegrationConnection $connection, string $operation, array $payload, string $externalId): array
    {
        $method = 'POST';
        $headers = $this->authHeaders($connection) + ['X-CRM-External-Id' => $externalId];
        $body = $payload + ['external_id' => $externalId, 'operation' => $operation];
        $adapter = $connection->provider ?: 'generic';

        if ($operation === 'calendar_event_upsert') {
            $body = $this->calendarProviderPayload($connection, $payload, $externalId);
        } elseif ($operation === 'drive_file_metadata_upsert') {
            $body = $this->driveProviderPayload($connection, $payload, $externalId);
        } elseif ($operation === 'voip_click_to_call') {
            $body = $this->voipProviderPayload($connection, $payload, $externalId);
        }

        return [
            'adapter' => $adapter,
            'method' => $method,
            'url' => $connection->endpoint_url,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    private function calendarProviderPayload(CrmIntegrationConnection $connection, array $payload, string $externalId): array
    {
        $date = $payload['due_date'] ?: now()->toDateString();
        $calendarName = Arr::get($connection->settings ?: [], 'calendar_name');

        return match ($connection->provider) {
            'google_calendar' => [
                'summary' => $payload['title'],
                'description' => trim(($payload['description'] ?: '') . "\nCRM Followup #" . ($payload['customer_id'] ?: '-')),
                'start' => ['date' => $date],
                'end' => ['date' => $date],
                'extendedProperties' => ['private' => ['crm_external_id' => $externalId, 'crm_operation' => 'calendar_event_upsert']],
                'calendarId' => $calendarName ?: 'primary',
            ],
            'microsoft_calendar' => [
                'subject' => $payload['title'],
                'body' => ['contentType' => 'Text', 'content' => $payload['description'] ?: 'CRM followup'],
                'start' => ['dateTime' => $date . 'T09:00:00', 'timeZone' => config('app.timezone', 'UTC')],
                'end' => ['dateTime' => $date . 'T09:30:00', 'timeZone' => config('app.timezone', 'UTC')],
                'transactionId' => $externalId,
                'calendar' => $calendarName,
            ],
            default => $payload + ['external_id' => $externalId, 'operation' => 'calendar_event_upsert'],
        };
    }

    private function driveProviderPayload(CrmIntegrationConnection $connection, array $payload, string $externalId): array
    {
        $folder = Arr::get($connection->settings ?: [], 'drive_folder');

        return match ($connection->provider) {
            'google_drive' => [
                'name' => $payload['title'],
                'webViewLink' => $payload['external_url'],
                'description' => $payload['note'],
                'parents' => $folder ? [$folder] : [],
                'appProperties' => ['crm_external_id' => $externalId, 'target_type' => $payload['target_type'], 'target_id' => (string) $payload['target_id']],
            ],
            'dropbox' => [
                'path' => trim(($folder ?: '/CRM') . '/' . $payload['title'], '/'),
                'url' => $payload['external_url'],
                'note' => $payload['note'],
                'client_modified' => now()->toIso8601String(),
                'crm_external_id' => $externalId,
            ],
            default => $payload + ['external_id' => $externalId, 'operation' => 'drive_file_metadata_upsert'],
        };
    }

    private function voipProviderPayload(CrmIntegrationConnection $connection, array $payload, string $externalId): array
    {
        $settings = $connection->settings ?: [];
        $line = Arr::get($settings, 'voip_line');

        return match ($connection->provider) {
            'asterisk' => [
                'Action' => 'Originate',
                'Channel' => trim('SIP/' . ($line ?: 'default') . '/' . $payload['phone_number']),
                'Context' => Arr::get($settings, 'voip_context', 'from-internal'),
                'Exten' => $payload['phone_number'],
                'Priority' => 1,
                'CallerID' => Arr::get($settings, 'voip_caller_id', $line),
                'Async' => true,
                'Variable' => ['CRM_EXTERNAL_ID=' . $externalId, 'CRM_CALL_LOG_ID=' . $payload['call_log_id']],
            ],
            default => $payload + ['external_id' => $externalId, 'operation' => 'voip_click_to_call', 'line' => $line],
        };
    }

    private function authHeaders(CrmIntegrationConnection $connection): array
    {
        $token = Arr::get($connection->credentials ?: [], 'outbound_auth_token');

        if (!$token) {
            return [];
        }

        return ['Authorization' => Str::startsWith($token, 'Bearer ') ? $token : 'Bearer ' . $token];
    }

    private function log(CrmIntegrationConnection $connection, string $direction, string $operation, string $status, array $payload, array $response, ?Model $syncable = null, ?string $externalId = null, ?string $message = null, ?int $userId = null): CrmIntegrationSyncLog
    {
        $log = CrmIntegrationSyncLog::create([
            'crm_integration_connection_id' => $connection->id,
            'tenant_id' => $connection->tenant_id,
            'organization_id' => $connection->organization_id,
            'integration_type' => $connection->type,
            'provider' => $connection->provider,
            'direction' => $direction,
            'operation' => $operation,
            'status' => $status,
            'external_id' => $externalId,
            'syncable_type' => $syncable ? get_class($syncable) : null,
            'syncable_id' => $syncable?->getKey(),
            'payload_snapshot' => $payload,
            'response_snapshot' => $response,
            'message' => $message,
            'attempted_at' => now(),
            'synced_at' => $status === 'synced' ? now() : null,
            'created_by' => $userId,
        ]);

        $connection->update(['last_synced_at' => now()]);

        return $log;
    }

    private function resolveSyncable(string $targetType, int $targetId, $user): Model
    {
        $map = [
            'followup' => CrmFollowup::class,
            'call' => CrmCallLog::class,
            'customer' => Customers::class,
        ];
        abort_unless(isset($map[$targetType]), 422);
        $query = $map[$targetType]::query();

        if ((int) $user?->isGod !== 1 && method_exists($query->getModel(), 'scopeForOrganizations')) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($targetId);
    }

    private function authorizeConnection(CrmIntegrationConnection $connection, $user): void
    {
        if ((int) $user?->isGod === 1 || !$user) {
            return;
        }

        abort_unless($this->scopeConnections(CrmIntegrationConnection::query()->whereKey($connection->id), $user)->exists(), 403);
    }

    private function customerByPhone(CrmIntegrationConnection $connection, string $phone): ?Customers
    {
        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        return Customers::query()
            ->when($connection->tenant_id, fn($query) => $query->where(function ($inner) use ($connection) {
                if (Schema::hasColumn('customers', 'tenant_id')) {
                    $inner->where('tenant_id', $connection->tenant_id);
                }
                if (Schema::hasColumn('customers', 'tenants_id')) {
                    $inner->orWhere('tenants_id', $connection->tenant_id);
                }
            }))
            ->when($connection->organization_id, fn($query) => $query->where('organization_id', $connection->organization_id))
            ->where(function ($query) use ($phone) {
                $query->where('mobile', $phone)->orWhere('phone', $phone);
            })
            ->first();
    }

    private function scopeConnections($query, $user)
    {
        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        } elseif ($user instanceof CrmIntegrationConnection) {
            $query->where('tenant_id', $user->tenant_id)->where('organization_id', $user->organization_id);
        }

        return $query;
    }

    private function scopeLogs($query, $user)
    {
        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function scopeFollowups($query, $user)
    {
        if ($user instanceof CrmIntegrationConnection) {
            return $query->where('tenant_id', $user->tenant_id)->where('organization_id', $user->organization_id);
        }
        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function scopeCalls($query, $user)
    {
        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function scopeCustomers($query, $user)
    {
        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function defaultScopes(string $type): array
    {
        return match ($type) {
            'voip' => ['calls.inbound', 'recordings.link'],
            'calendar' => ['followups.sync', 'meetings.sync'],
            'drive' => ['files.link', 'attachments.sync'],
            default => [],
        };
    }

    private function normalizeDirection(string $direction): string
    {
        return in_array($direction, ['inbound', 'outbound', 'missed', 'internal'], true) ? $direction : 'inbound';
    }

    private function tenantId($user): ?int
    {
        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) ?: null : null;
    }

    private function organizationId($user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }
}
