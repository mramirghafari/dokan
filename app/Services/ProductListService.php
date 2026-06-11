<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductListService
{
    public function filterValues(Request $request): array
    {
        return [
            'status_filter' => $this->normalizeStatusFilter($request->input('status_filter', 'active')),
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
        return $this->applyFilters($this->scopedQuery($user), $request);
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
        $scopedQuery = $this->scopedQuery($user);
        $recordsTotal = (clone $scopedQuery)->count('products.id');

        $filteredQuery = $this->applyFilters(clone $scopedQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count('products.id');

        $sortableColumns = [
            1 => 'products.sku',
            2 => 'products.title',
            6 => 'products.pr_unit',
            7 => 'products.pr_sub_unit',
            8 => 'products.price',
        ];

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

        $data = $products->values()->map(function (Product $product, int $index) use (
            $start,
            $storesById,
            $organizationsById
        ) {
            return $this->formatDatatableRow($product, $start + $index + 1, $storesById, $organizationsById);
        });

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $statusFilter = $this->normalizeStatusFilter($request->input('status_filter', 'active'));

        if ($statusFilter === 'active') {
            $query->where('products.isActive', 1);
        } elseif ($statusFilter === 'inactive') {
            $query->where('products.isActive', 0);
        }

        if ($request->filled('store_id')) {
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

    private function formatDatatableRow(
        Product $product,
        int $rowNumber,
        Collection $storesById,
        Collection $organizationsById
    ): array {
        $editUrl = route('products.edit', $product->id);
        $title = trim($product->title . ' ' . ($product->display_name ?? ''));
        $price = (int) $product->price > 0
            ? number_format((int) $product->price)
            : (string) (int) $product->price;

        return [
            (string) $rowNumber,
            '<a href="' . $editUrl . '">' . e($product->sku) . '</a>',
            '<a href="' . $editUrl . '">' . e($title) . '</a>',
            e($this->titlesFromJsonIds((string) $product->store_id, $storesById)),
            e($this->titlesFromJsonIds((string) $product->organization_id, $organizationsById)),
            (string) $product->currentStock(),
            e($product->pr_unit ?? ''),
            e($product->pr_sub_unit ?? ''),
            $price,
            '<a href="' . $editUrl . '" style="font-size:20px;float:right;margin-left:5px"><i class="fa fa-edit" style="color:#04a9f5;"></i></a>',
        ];
    }

    private function titlesFromJsonIds(string $rawValue, Collection $titlesById): string
    {
        $decoded = json_decode($rawValue, true);

        if (!is_array($decoded)) {
            return '';
        }

        return collect($decoded)
            ->map(fn ($id) => $titlesById->get((int) $id))
            ->filter()
            ->implode('، ');
    }
}
