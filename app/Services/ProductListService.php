<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductListService
{
    public function __construct(
        private readonly ProductListColumnService $columnService,
    ) {}

    public function filterValues(Request $request): array
    {
        return [
            'status_filter' => $this->normalizeStatusFilter($request->input('status_filter', 'active')),
            'sales_filter' => $this->normalizeSalesFilter($request->input('sales_filter', 'all')),
            'store_id' => $request->filled('store_id') ? (int) $request->store_id : null,
        ];
    }

    public function scopedQuery(User $user): Builder
    {
        return Product::query()
            ->select('products.*')
            ->forOrganizations($user)
            ->where('products.isMaterial', 0);
    }

    public function filteredQuery(User $user, Request $request): Builder
    {
        return $this->applyFilters($this->scopedQuery($user), $request, $user);
    }

    public function count(User $user, Request $request): int
    {
        return (int) $this->filteredQuery($user, $request)->count('products.id');
    }

    public function datatable(
        User $user,
        Request $request,
        int $start,
        int $length,
        int $orderColumn,
        string $orderDirection
    ): array {
        $tenantId = $user->tenant_id ?: $user->tenants_id;
        $scopedQuery = $this->scopedQuery($user);
        $recordsTotal = (clone $scopedQuery)->count('products.id');

        $filteredQuery = $this->applyFilters(clone $scopedQuery, $request, $user);
        $recordsFiltered = (clone $filteredQuery)->count('products.id');

        $sortableColumns = $this->columnService->sortableColumnsMap($tenantId ? (int) $tenantId : null);

        $products = (clone $filteredQuery)
            ->when(isset($sortableColumns[$orderColumn]), function (Builder $query) use ($sortableColumns, $orderColumn, $orderDirection) {
                $query->orderBy($sortableColumns[$orderColumn], $orderDirection);
            }, function (Builder $query) {
                $query->orderByDesc('products.id');
            })
            ->skip($start)
            ->take($length)
            ->get();

        $storesById = $this->resolveStoresMap($products);
        $organizationsById = $this->resolveOrganizationsMap($products);
        $brandsById = $this->resolveBrandsMap($products);
        $categoriesById = $this->resolveCategoriesMap($products);

        $data = $products->values()->map(function (Product $product, int $index) use (
            $start,
            $storesById,
            $organizationsById,
            $brandsById,
            $categoriesById,
            $tenantId
        ) {
            return $this->columnService->buildDatatableRow(
                $product,
                $start + $index + 1,
                route('products.edit', $product->id),
                $storesById,
                $organizationsById,
                $brandsById,
                $categoriesById,
                $tenantId ? (int) $tenantId : null
            );
        });

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    private function applyFilters(Builder $query, Request $request, User $user): Builder
    {
        $tenantId = $user->tenant_id ?: $user->tenants_id;
        $statusFilter = $this->normalizeStatusFilter($request->input('status_filter', 'active'));
        $salesFilter = $this->normalizeSalesFilter($request->input('sales_filter', 'all'));

        if ($statusFilter === 'active') {
            $query->where('products.isActive', 1);
        } elseif ($statusFilter === 'inactive') {
            $query->where('products.isActive', 0);
        }

        if ($salesFilter === 'sold') {
            $query->whereHas('pishfactorItems', function (Builder $items) {
                $items->whereHas('pishfactor', function (Builder $invoice) {
                    $invoice->whereIn('status', [1, 4]);
                });
            });
        } elseif ($salesFilter === 'unsold') {
            $query->whereDoesntHave('pishfactorItems', function (Builder $items) {
                $items->whereHas('pishfactor', function (Builder $invoice) {
                    $invoice->whereIn('status', [1, 4]);
                });
            });
        }

        if (
            $this->columnService->warehouseModuleEnabled($tenantId ? (int) $tenantId : null)
            && $request->filled('store_id')
        ) {
            $storeId = (int) $request->store_id;
            $query->where(function (Builder $inner) use ($storeId) {
                $inner->where('products.store_id', 'like', '%"' . $storeId . '"%')
                    ->orWhere('products.store_id', 'like', '%[' . $storeId . '%');
            });
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('products.sku', 'like', '%' . $search . '%')
                    ->orWhere('products.title', 'like', '%' . $search . '%')
                    ->orWhere('products.display_name', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function normalizeStatusFilter(?string $statusFilter): string
    {
        return in_array($statusFilter, ['active', 'inactive', 'all'], true) ? $statusFilter : 'active';
    }

    private function normalizeSalesFilter(?string $salesFilter): string
    {
        return in_array($salesFilter, ['all', 'sold', 'unsold'], true) ? $salesFilter : 'all';
    }

    private function resolveStoresMap(Collection $products): Collection
    {
        $ids = $this->collectJsonIds($products, 'store_id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return Store::query()->whereIn('id', $ids)->pluck('title', 'id');
    }

    private function resolveOrganizationsMap(Collection $products): Collection
    {
        $ids = $this->collectJsonIds($products, 'organization_id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return Organization::query()->whereIn('id', $ids)->pluck('title', 'id');
    }

    private function resolveBrandsMap(Collection $products): Collection
    {
        $ids = $products
            ->pluck('brand_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Brand::query()->whereIn('id', $ids)->pluck('title', 'id');
    }

    private function resolveCategoriesMap(Collection $products): Collection
    {
        $ids = $products
            ->flatMap(fn (Product $product) => [(int) $product->parentCategory_id, (int) $product->childCategory_id])
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Category::query()->whereIn('id', $ids)->pluck('title', 'id');
    }

    private function collectJsonIds(Collection $products, string $attribute): Collection
    {
        return $products
            ->flatMap(function (Product $product) use ($attribute) {
                $decoded = json_decode((string) $product->{$attribute}, true);

                return is_array($decoded) ? $decoded : [];
            })
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();
    }
}
