<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogService
{
    private const ACTION_MAP = [
        'create' => 'create',
        'store' => 'create',
        'update' => 'update',
        'edit' => 'update',
        'approve' => 'update',
        'reject' => 'update',
        'cancel' => 'update',
        'void' => 'update',
        'send' => 'update',
        'sync' => 'update',
        'import' => 'create',
        'export' => 'create',
        'delete' => 'delete',
        'restore' => 'restore',
        'forceDelete' => 'forceDelete',
        'login' => 'login',
        'logout' => 'logout',
    ];

    public function record(string $eventKey, array $payload = [], ?Model $source = null, $user = null): ?Log
    {
        if (!Schema::hasTable('logs')) {
            return null;
        }

        $user = $user ?: auth()->user();

        if (!$user) {
            return null;
        }

        $attributes = [
            'ip' => Request::ip() ?: '127.0.0.1',
            'user_id' => $user->id,
            'action' => $this->legacyAction($eventKey, $payload['action'] ?? null),
            'description' => $payload['description'] ?? $this->description($eventKey, $source),
        ];

        $optional = [
            'tenant_id' => $payload['tenant_id'] ?? $this->valueFrom($source, ['tenant_id', 'tenants_id']) ?? ($user->tenant_id ?? $user->tenants_id ?? null),
            'organization_id' => $payload['organization_id'] ?? $this->valueFrom($source, ['organization_id']),
            'section' => $payload['section'] ?? ($source ? class_basename($source) : null),
            'section_id' => $payload['section_id'] ?? ($source?->getKey()),
            'event_key' => $eventKey,
            'source_type' => $source ? get_class($source) : ($payload['source_type'] ?? null),
            'source_id' => $source?->getKey() ?: ($payload['source_id'] ?? null),
            'payload_json' => $payload,
        ];

        foreach ($optional as $column => $value) {
            if (Schema::hasColumn('logs', $column)) {
                $attributes[$column] = $value;
            }
        }

        return Log::create($attributes);
    }

    private function legacyAction(string $eventKey, ?string $action): string
    {
        $candidate = $action ?: strtolower(str($eventKey)->afterLast('.')->toString());

        return self::ACTION_MAP[$candidate] ?? 'update';
    }

    private function description(string $eventKey, ?Model $source): string
    {
        $sourceName = $source ? class_basename($source) . '#' . $source->getKey() : 'system';

        return $eventKey . ' - ' . $sourceName;
    }

    private function valueFrom(?Model $source, array $keys)
    {
        if (!$source) {
            return null;
        }

        foreach ($keys as $key) {
            if (!empty($source->{$key})) {
                return $source->{$key};
            }
        }

        return null;
    }
}
