<?php

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\Tenants;
use Illuminate\Support\Facades\Schema;

class FiscalYearService
{
    public function ensureDefaultForTenant(?int $tenantId): ?FiscalYear
    {
        if (!$tenantId || !Schema::hasTable('fiscal_years')) {
            return null;
        }

        $existing = FiscalYear::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('is_default')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createForJalaliYear($tenantId);
    }

    public function createForJalaliYear(?int $tenantId, ?int $jalaliYear = null): ?FiscalYear
    {
        if (!$tenantId) {
            return null;
        }

        [$startsAt, $endsAt, $title] = $this->resolveRange($tenantId, $jalaliYear);

        $fiscalYear = FiscalYear::firstOrCreate(
            ['tenant_id' => $tenantId, 'starts_at' => $startsAt],
            [
                'title' => $title,
                'ends_at' => $endsAt,
                'status' => 'open',
                'is_default' => !FiscalYear::query()->where('tenant_id', $tenantId)->where('is_default', true)->exists(),
            ]
        );

        if (!$fiscalYear->is_default && !FiscalYear::query()->where('tenant_id', $tenantId)->where('is_default', true)->exists()) {
            $fiscalYear->update(['is_default' => true]);
        }

        $this->syncTenantDefaults($tenantId, $fiscalYear, $startsAt, $endsAt);

        return $fiscalYear->fresh();
    }

    public function previewRange(?int $tenantId, ?int $jalaliYear = null): array
    {
        return $this->resolveRange($tenantId, $jalaliYear);
    }

    private function resolveRange(?int $tenantId, ?int $jalaliYear = null): array
    {
        $tenant = $tenantId ? Tenants::find($tenantId) : null;

        if ($tenant?->fiscal_year_start && $tenant?->fiscal_year_end) {
            $start = $tenant->fiscal_year_start->toDateString();
            $end = $tenant->fiscal_year_end->toDateString();
            $title = 'سال مالی ' . verta($start)->format('Y');

            return [$start, $end, $title];
        }

        $jalaliYear = $jalaliYear ?: (int) verta()->format('Y');
        $verta = verta()->parse($jalaliYear . '/01/01');
        $startsAt = $verta->startYear()->toCarbon()->toDateString();
        $endsAt = $verta->endYear()->toCarbon()->toDateString();
        $title = 'سال مالی ' . $jalaliYear;

        return [$startsAt, $endsAt, $title];
    }

    private function syncTenantDefaults(int $tenantId, FiscalYear $fiscalYear, string $startsAt, string $endsAt): void
    {
        if (!Schema::hasTable('tenants')) {
            return;
        }

        $updates = [];

        if (Schema::hasColumn('tenants', 'default_fiscal_year_id')) {
            $updates['default_fiscal_year_id'] = $fiscalYear->id;
        }

        $tenant = Tenants::find($tenantId);

        if (!$tenant) {
            return;
        }

        if (Schema::hasColumn('tenants', 'fiscal_year_start') && !$tenant->fiscal_year_start) {
            $updates['fiscal_year_start'] = $startsAt;
        }

        if (Schema::hasColumn('tenants', 'fiscal_year_end') && !$tenant->fiscal_year_end) {
            $updates['fiscal_year_end'] = $endsAt;
        }

        if ($updates !== []) {
            $tenant->update($updates);
        }
    }
}
