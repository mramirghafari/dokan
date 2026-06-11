<?php

namespace App\Services;

use App\Models\CrmLead;
use App\Models\Customers;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CrmLeadBulkImportService
{
    public function __construct(private TenantContextService $tenantContext) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function importRows(array $rows, User $user, array $options = []): array
    {
        $tenantId = $this->tenantContext->tenantId($user);
        $organizationId = $options['organization_id'] ?? $this->tenantContext->organizationId($user);
        $defaultSource = (string) ($options['default_source'] ?? 'campaign');
        $defaultCampaign = $options['default_campaign'] ?? null;
        $ownerUserId = (int) ($options['owner_user_id'] ?? $user->id);

        $summary = [
            'total' => count($rows),
            'created' => 0,
            'updated' => 0,
            'duplicate' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        return TenantScope::forTenant($tenantId, function () use ($rows, $user, $tenantId, $organizationId, $defaultSource, $defaultCampaign, $ownerUserId, $summary) {
            foreach ($rows as $index => $row) {
                $line = $index + 1;

                try {
                    $normalized = $this->normalizeRow($row, $defaultSource, $defaultCampaign);
                    $validator = Validator::make($normalized, [
                        'name' => ['required', 'string', 'max:180'],
                        'mobile' => ['nullable', 'string', 'max:30'],
                        'source' => ['required', 'in:' . implode(',', array_keys(CrmLead::SOURCES))],
                        'priority' => ['required', 'in:' . implode(',', array_keys(CrmLead::PRIORITIES))],
                    ]);

                    if ($validator->fails()) {
                        $summary['failed']++;
                        $summary['errors'][] = ['line' => $line, 'messages' => $validator->errors()->all()];
                        continue;
                    }

                    $duplicateCustomer = $this->duplicateCustomer($normalized['mobile'], $tenantId, $organizationId);
                    $duplicateLead = $this->duplicateLead($normalized['mobile'], $tenantId, $organizationId);
                    $status = $duplicateCustomer || $duplicateLead ? 'duplicate' : 'open';

                    if ($duplicateLead && !($options['update_existing'] ?? false)) {
                        $summary['duplicate']++;
                        continue;
                    }

                    if ($duplicateLead && ($options['update_existing'] ?? false)) {
                        $duplicateLead->update(Arr::only($normalized, [
                            'name', 'company_name', 'mobile', 'phone', 'email', 'city', 'source', 'campaign', 'score', 'priority', 'notes',
                        ]) + ['updated_by' => $user->id]);
                        $summary['updated']++;
                        continue;
                    }

                    $nextId = (int) CrmLead::withTrashed()->max('id') + 1;

                    CrmLead::create([
                        'tenant_id' => $tenantId,
                        'organization_id' => $organizationId,
                        'owner_user_id' => $ownerUserId,
                        'code' => 'LEAD-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
                        'name' => $normalized['name'],
                        'company_name' => $normalized['company_name'],
                        'mobile' => $normalized['mobile'],
                        'phone' => $normalized['phone'],
                        'email' => $normalized['email'],
                        'city' => $normalized['city'],
                        'source' => $normalized['source'],
                        'campaign' => $normalized['campaign'],
                        'score' => $normalized['score'],
                        'stage' => 'new',
                        'status' => $status,
                        'priority' => $normalized['priority'],
                        'duplicate_status' => $duplicateCustomer ? 'customer' : ($duplicateLead ? 'lead' : 'none'),
                        'duplicate_customer_id' => optional($duplicateCustomer)->id,
                        'duplicate_lead_id' => optional($duplicateLead)->id,
                        'notes' => $normalized['notes'],
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    $status === 'duplicate' ? $summary['duplicate']++ : $summary['created']++;
                } catch (\Throwable $exception) {
                    $summary['failed']++;
                    $summary['errors'][] = ['line' => $line, 'messages' => [$exception->getMessage()]];
                }
            }

            return $summary;
        });
    }

    public function templateHeaders(): array
    {
        return ['name', 'mobile', 'phone', 'email', 'company_name', 'city', 'source', 'campaign', 'score', 'priority', 'notes'];
    }

    private function normalizeRow(array $row, string $defaultSource, ?string $defaultCampaign): array
    {
        $source = trim((string) ($row['source'] ?? $row['منبع'] ?? $defaultSource));
        $priority = trim((string) ($row['priority'] ?? $row['اولویت'] ?? 'normal'));

        if (!isset(CrmLead::SOURCES[$source])) {
            $source = $defaultSource;
        }

        if (!isset(CrmLead::PRIORITIES[$priority])) {
            $priority = 'normal';
        }

        return [
            'name' => trim((string) ($row['name'] ?? $row['نام'] ?? '')),
            'mobile' => $this->normalizePhone($row['mobile'] ?? $row['موبایل'] ?? null),
            'phone' => $this->normalizePhone($row['phone'] ?? $row['تلفن'] ?? null),
            'email' => trim((string) ($row['email'] ?? $row['ایمیل'] ?? '')) ?: null,
            'company_name' => trim((string) ($row['company_name'] ?? $row['شرکت'] ?? '')) ?: null,
            'city' => trim((string) ($row['city'] ?? $row['شهر'] ?? '')) ?: null,
            'source' => $source,
            'campaign' => trim((string) ($row['campaign'] ?? $row['کمپین'] ?? $defaultCampaign ?? '')) ?: null,
            'score' => (int) ($row['score'] ?? $row['امتیاز'] ?? 0),
            'priority' => $priority,
            'notes' => trim((string) ($row['notes'] ?? $row['یادداشت'] ?? '')) ?: null,
        ];
    }

    private function normalizePhone(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    private function duplicateCustomer(?string $mobile, ?int $tenantId, ?int $organizationId): ?Customers
    {
        if (!$mobile) {
            return null;
        }

        return Customers::query()
            ->where('mobile', $mobile)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($organizationId, fn ($query) => $query->where('organization_id', $organizationId))
            ->first();
    }

    private function duplicateLead(?string $mobile, ?int $tenantId, ?int $organizationId): ?CrmLead
    {
        if (!$mobile) {
            return null;
        }

        return CrmLead::query()
            ->where('mobile', $mobile)
            ->whereIn('status', ['open', 'duplicate'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($organizationId, fn ($query) => $query->where('organization_id', $organizationId))
            ->latest('id')
            ->first();
    }
}
