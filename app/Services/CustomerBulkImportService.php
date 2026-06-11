<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CustomerBulkImportService
{
    public function __construct(private TenantContextService $tenantContext) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function importRows(array $rows, User $user, array $options = []): array
    {
        $tenantId = $this->tenantContext->tenantId($user);
        $organizationId = $options['organization_id'] ?? $this->tenantContext->organizationId($user);
        $updateExisting = (bool) ($options['update_existing'] ?? false);
        $defaultStatus = (int) ($options['default_status'] ?? 1);

        $summary = [
            'total' => count($rows),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        return TenantScope::forTenant($tenantId, function () use ($rows, $user, $tenantId, $organizationId, $updateExisting, $defaultStatus, $summary) {
            foreach ($rows as $index => $row) {
                $line = $index + 1;

                try {
                    $normalized = $this->normalizeRow($row);
                    $validator = Validator::make($normalized, [
                        'name' => ['required', 'string', 'max:191'],
                        'mobile' => ['nullable', 'string', 'max:30'],
                        'phone' => ['nullable', 'string', 'max:30'],
                        'customer_code' => ['nullable', 'string', 'max:60'],
                        'national_id' => ['nullable', 'string', 'max:20'],
                        'address' => ['nullable', 'string', 'max:500'],
                    ]);

                    if ($validator->fails()) {
                        $summary['failed']++;
                        $summary['errors'][] = ['line' => $line, 'messages' => $validator->errors()->all()];
                        continue;
                    }

                    $payload = array_merge($normalized, [
                        'tenant_id' => $tenantId,
                        'organization_id' => $organizationId,
                        'status' => $normalized['status'] ?? $defaultStatus,
                        'created_by' => $user->id,
                    ]);

                    $existing = $this->findDuplicate($tenantId, $payload);

                    if ($existing && !$updateExisting) {
                        $summary['skipped']++;
                        continue;
                    }

                    if ($existing) {
                        $existing->update(Arr::only($payload, [
                            'name', 'mobile', 'phone', 'customer_code', 'national_id', 'address', 'tablo', 'status', 'area', 'region_id',
                        ]));
                        $summary['updated']++;
                        continue;
                    }

                    Customers::create($payload);
                    $summary['created']++;
                } catch (\Throwable $exception) {
                    $summary['failed']++;
                    $summary['errors'][] = ['line' => $line, 'messages' => [$exception->getMessage()]];
                }
            }

            return $summary;
        });
    }

    private function normalizeRow(array $row): array
    {
        $mapped = [
            'name' => trim((string) ($row['name'] ?? $row['نام'] ?? '')),
            'mobile' => $this->normalizePhone($row['mobile'] ?? $row['موبایل'] ?? null),
            'phone' => $this->normalizePhone($row['phone'] ?? $row['تلفن'] ?? null),
            'customer_code' => trim((string) ($row['customer_code'] ?? $row['code'] ?? $row['کد'] ?? '')) ?: null,
            'national_id' => trim((string) ($row['national_id'] ?? $row['کدملی'] ?? '')) ?: null,
            'address' => trim((string) ($row['address'] ?? $row['آدرس'] ?? '')) ?: null,
            'tablo' => trim((string) ($row['tablo'] ?? $row['تابلو'] ?? '')) ?: null,
            'area' => isset($row['area']) ? (int) $row['area'] : (isset($row['area_id']) ? (int) $row['area_id'] : null),
            'region_id' => isset($row['region_id']) ? (int) $row['region_id'] : null,
            'status' => isset($row['status']) || isset($row['isActive']) ? (int) ($row['status'] ?? $row['isActive']) : null,
        ];

        return array_filter($mapped, function ($value, string $key) {
            if (in_array($key, ['status', 'area', 'region_id'], true)) {
                return $value !== null;
            }

            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function findDuplicate(?int $tenantId, array $payload): ?Customers
    {
        $query = Customers::withoutTenantScope()->where('tenant_id', $tenantId);

        if (!empty($payload['customer_code'])) {
            return (clone $query)->where('customer_code', $payload['customer_code'])->first();
        }

        if (!empty($payload['mobile'])) {
            return (clone $query)->where('mobile', $payload['mobile'])->first();
        }

        return null;
    }

    private function normalizePhone(mixed $value): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $value);

        return $phone !== '' ? $phone : null;
    }
}
