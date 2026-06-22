<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\Region;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CustomerListService
{
    public function scopeContext(User $user): array
    {
        if ((int) $user->isGod === 1) {
            return [
                'scope_type' => 'global',
                'scope_key' => 'global',
                'tenant_id' => null,
                'organization_id' => null,
            ];
        }

        $roleTitles = $user->roles->pluck('title')->toArray();
        if (in_array('leader', $roleTitles, true)) {
            return [
                'scope_type' => 'leader',
                'scope_key' => 'leader:' . $user->id,
                'tenant_id' => $user->tenant_id ? (int) $user->tenant_id : null,
                'organization_id' => $user->organization_id ? (int) $user->organization_id : null,
            ];
        }

        return [
            'scope_type' => 'organization',
            'scope_key' => 'org:' . (int) $user->organization_id,
            'tenant_id' => $user->tenant_id ? (int) $user->tenant_id : null,
            'organization_id' => $user->organization_id ? (int) $user->organization_id : null,
        ];
    }

    public function scopedQuery(User $user): Builder
    {
        $roleTitles = $user->roles->pluck('title')->toArray();
        $isLeader = in_array('leader', $roleTitles, true);

        $query = Customers::query()->select('customers.*');

        if ((int) $user->isGod === 1) {
            return $query;
        }

        if ($isLeader) {
            $regionIds = Region::where('leader_id', $user->id)->pluck('id');
            $areaIds = Area::whereIn('region_id', $regionIds)->pluck('id');

            return $query->whereIn('customers.area', $areaIds);
        }

        return Customers::forOrganizations($user)->select('customers.*');
    }

    public function scopedQueryForContext(array $context): Builder
    {
        $query = Customers::query()->select('customers.*');

        return match ($context['scope_type']) {
            'global' => $query,
            'leader' => $this->applyLeaderScope($query, (int) str_replace('leader:', '', $context['scope_key'])),
            default => $this->applyOrganizationScope($query, $context['tenant_id'], $context['organization_id']),
        };
    }

    public function applyFilters(Builder $query, Request $request, ?User $user = null): Builder
    {
        $table = (new Customers())->getTable();

        if ($request->filled('codename')) {
            $term = trim((string) $request->codename);
            $query->where(function (Builder $inner) use ($term, $table) {
                $inner->where($table . '.name', 'like', '%' . $term . '%')
                    ->orWhere($table . '.customer_code', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('area_id') && (int) $request->area_id !== 0) {
            $query->where($table . '.area', (int) $request->area_id);
        }

        if ($request->filled('leader_id') && (int) $request->leader_id !== 0) {
            $regionIds = Region::where('leader_id', (int) $request->leader_id)->pluck('id');
            $areaIds = Area::whereIn('region_id', $regionIds)->pluck('id');
            $query->whereIn($table . '.area', $areaIds);
        }

        if ($request->filled('visitor_id') && (int) $request->visitor_id !== 0) {
            $areaIds = Tasks::where('user_id', (int) $request->visitor_id)->pluck('area_id')->unique();
            $query->whereIn($table . '.area', $areaIds);
        }

        $datatableSearch = trim((string) $request->input('search.value', ''));
        if ($datatableSearch !== '') {
            $query->where(function (Builder $inner) use ($datatableSearch, $table) {
                $inner->where($table . '.name', 'like', '%' . $datatableSearch . '%')
                    ->orWhere($table . '.customer_code', 'like', '%' . $datatableSearch . '%')
                    ->orWhere($table . '.tablo', 'like', '%' . $datatableSearch . '%')
                    ->orWhere($table . '.mobile', 'like', '%' . $datatableSearch . '%');
            });
        }

        if ((int) $request->input('status') === 1) {
            $query->where($table . '.status', 1);
        }

        return $query;
    }

    public function appendListMetricSelects(Builder $query): Builder
    {
        $customerTable = (new Customers())->getTable();
        $invoiceTable = (new Pishfactor())->getTable();
        $priceExpression = "CAST(REPLACE(REPLACE(IFNULL({$invoiceTable}.fullPrice, '0'), ',', ''), ' ', '') AS DECIMAL(18,2))";

        $query->addSelect([
            'list_account_balance' => Pishfactor::query()
                ->selectRaw('COALESCE(SUM(' . $priceExpression . '), 0)')
                ->whereColumn("{$invoiceTable}.customer_id", "{$customerTable}.id")
                ->whereIn("{$invoiceTable}.status", [1, 4])
                ->where(function (Builder $inner) use ($invoiceTable) {
                    $inner->where("{$invoiceTable}.payment_type", 3)
                        ->orWhereNull("{$invoiceTable}.payment_type")
                        ->orWhereNull("{$invoiceTable}.settlement_status")
                        ->orWhereNotIn("{$invoiceTable}.settlement_status", ['settled', 'paid']);
                })
                ->limit(1),
            'list_subscription_end' => Pishfactor::query()
                ->select("{$invoiceTable}.recive_date_en")
                ->whereColumn("{$invoiceTable}.customer_id", "{$customerTable}.id")
                ->whereIn("{$invoiceTable}.status", [1, 4])
                ->whereNotNull("{$invoiceTable}.recive_date_en")
                ->orderByDesc("{$invoiceTable}.recive_date_en")
                ->limit(1),
        ]);

        return $query;
    }

    public function hasListFilters(Request $request): bool
    {
        if ($request->filled('codename') && trim((string) $request->codename) !== '') {
            return true;
        }

        if ($request->filled('area_id') && (int) $request->area_id !== 0) {
            return true;
        }

        if ($request->filled('leader_id') && (int) $request->leader_id !== 0) {
            return true;
        }

        if ($request->filled('visitor_id') && (int) $request->visitor_id !== 0) {
            return true;
        }

        if ((int) $request->input('status') === 1) {
            return true;
        }

        if (trim((string) $request->input('search.value', '')) !== '') {
            return true;
        }

        return false;
    }

    public function filterFingerprint(Request $request): string
    {
        return md5(json_encode([
            'codename' => trim((string) $request->input('codename', '')),
            'area_id' => (int) $request->input('area_id', 0),
            'leader_id' => (int) $request->input('leader_id', 0),
            'visitor_id' => (int) $request->input('visitor_id', 0),
            'status' => (int) $request->input('status', 2),
            'search' => trim((string) $request->input('search.value', '')),
        ], JSON_UNESCAPED_UNICODE));
    }

    private function applyLeaderScope(Builder $query, int $leaderId): Builder
    {
        $regionIds = Region::where('leader_id', $leaderId)->pluck('id');
        $areaIds = Area::whereIn('region_id', $regionIds)->pluck('id');

        return $query->whereIn('customers.area', $areaIds);
    }

    private function applyOrganizationScope(Builder $query, ?int $tenantId, ?int $organizationId): Builder
    {
        if ($organizationId) {
            $query->where('customers.organization_id', $organizationId);
        }

        if ($tenantId) {
            $query->where('customers.tenant_id', $tenantId);
        }

        return $query;
    }
}
