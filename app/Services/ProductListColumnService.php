<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProductListColumnService
{
    public const SETTING_KEY = 'product_list_visible_columns';

    public function warehouseModuleEnabled(?int $tenantId = null): bool
    {
        return TenantSettings::enabled('feature_warehouse_management', $tenantId);
    }

    /**
     * @return array<int, array{key: string, label: string, index: int, description?: string, fixed?: bool, toggleable?: bool, sortable?: string, feature?: string, default?: bool}>
     */
    public function resolvedColumns(?int $tenantId = null): array
    {
        $columns = [];
        $index = 0;

        foreach ($this->definitions() as $definition) {
            if (!empty($definition['feature']) && !TenantSettings::enabled($definition['feature'], $tenantId)) {
                continue;
            }

            $columns[] = array_merge($definition, ['index' => $index++]);
        }

        return $columns;
    }

    /**
     * @return array<string, array{label: string, index: int, description: string}>
     */
    public function catalog(?int $tenantId = null): array
    {
        $catalog = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            if (($column['toggleable'] ?? true) === false) {
                continue;
            }

            $catalog[$column['key']] = [
                'label' => $column['label'],
                'index' => $column['index'],
                'description' => $column['description'] ?? '',
            ];
        }

        return $catalog;
    }

    /**
     * @return array<int, array{key: string, label: string, index: int}>
     */
    public function headers(?int $tenantId = null): array
    {
        return array_map(
            fn (array $column) => [
                'key' => $column['key'],
                'label' => $column['label'],
                'index' => $column['index'],
            ],
            $this->resolvedColumns($tenantId)
        );
    }

    public function columnCount(?int $tenantId = null): int
    {
        return count($this->resolvedColumns($tenantId));
    }

    /**
     * @return array<int, int>
     */
    public function fixedColumnIndexes(?int $tenantId = null): array
    {
        return array_values(array_map(
            fn (array $column) => $column['index'],
            array_filter(
                $this->resolvedColumns($tenantId),
                fn (array $column) => (bool) ($column['fixed'] ?? false)
            )
        ));
    }

    /**
     * @return array<int, string>
     */
    public function sortableColumnsMap(?int $tenantId = null): array
    {
        $map = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            if (!empty($column['sortable'])) {
                $map[$column['index']] = $column['sortable'];
            }
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    public function defaultVisibleKeys(?int $tenantId = null): array
    {
        $keys = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            if (($column['toggleable'] ?? true) === false) {
                continue;
            }

            if ($column['default'] ?? false) {
                $keys[] = $column['key'];
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return array<int, string>
     */
    public function visibleKeys(?int $tenantId = null): array
    {
        $saved = TenantSettings::get(self::SETTING_KEY, $tenantId);

        if (!is_array($saved) || $saved === []) {
            return $this->defaultVisibleKeys($tenantId);
        }

        return $this->normalizeKeys($saved, $tenantId);
    }

    /**
     * @return array<int, int>
     */
    public function hiddenColumnIndexes(?int $tenantId = null): array
    {
        $catalog = $this->catalog($tenantId);
        $visible = $this->visibleKeys($tenantId);
        $hidden = [];

        foreach ($catalog as $key => $column) {
            if (!in_array($key, $visible, true)) {
                $hidden[] = (int) $column['index'];
            }
        }

        return $hidden;
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function saveVisibleKeys(array $keys, User $user): array
    {
        $tenantId = app(TenantContextService::class)->tenantId($user);
        $normalized = $this->normalizeKeys($keys, $tenantId);
        $context = app(TenantContextService::class)->fromUser($user);
        unset($context['user_id']);
        $context['updated_by'] = $user->id;

        app(SettingService::class)->set(
            self::SETTING_KEY,
            $normalized,
            $context,
            'json',
            'products'
        );

        return $normalized;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    public function normalizeKeys(array $keys, ?int $tenantId = null): array
    {
        $allowed = array_keys($this->catalog($tenantId));

        return array_values(array_filter(
            array_unique(array_map('strval', $keys)),
            fn (string $key) => in_array($key, $allowed, true)
        ));
    }

    /**
     * @return array<int, string|int>
     */
    public function buildDatatableRow(
        Product $product,
        int $rowNumber,
        string $editUrl,
        Collection $storesById,
        Collection $organizationsById,
        Collection $brandsById,
        Collection $categoriesById,
        ?int $tenantId = null
    ): array {
        $title = trim($product->title . ' ' . ($product->display_name ?? ''));
        $categoryTitle = $this->categoryTitle($product, $categoriesById);

        $values = [
            'row' => (string) $rowNumber,
            'sku' => '<a href="' . $editUrl . '">' . e($product->sku ?: '—') . '</a>',
            'title' => '<a href="' . $editUrl . '">' . e($title ?: '—') . '</a>',
            'display_name' => e($product->display_name ?: '—'),
            'brand' => e($brandsById->get((int) $product->brand_id) ?: '—'),
            'category' => e($categoryTitle ?: '—'),
            'store' => e($this->titlesFromJsonIds((string) $product->store_id, $storesById) ?: '—'),
            'organization' => e($this->titlesFromJsonIds((string) $product->organization_id, $organizationsById) ?: '—'),
            'stock' => (string) $product->currentStock(),
            'pr_unit' => e($product->pr_unit ?: '—'),
            'pr_sub_unit' => e($product->pr_sub_unit ?: '—'),
            'pack_items' => $product->pack_items ? number_format((int) $product->pack_items) : '—',
            'price' => $this->formatPriceCell($product->price),
            'purchase_price' => $this->formatPriceCell($product->purchase_price),
            'cost_price' => $this->formatPriceCell($product->cost_price),
            'wholesale_price' => $this->formatPriceCell($product->wholesale_price),
            'consumer_price' => $this->formatPriceCell($product->consumer_price),
            'representative_price' => $this->formatPriceCell($product->representative_price),
            'product_type' => e(Product::PRODUCT_TYPE_LABELS[$product->product_type] ?? ($product->product_type ?: '—')),
            'stock_tracking' => e(Product::STOCK_TRACKING_LABELS[$product->stock_tracking_mode] ?? ($product->stock_tracking_mode ?: '—')),
            'item_sale' => $this->formatSaleFlag((int) $product->item_sale_status),
            'pack_sale' => $this->formatSaleFlag((int) $product->pack_sale_status),
            'status' => (int) $product->isActive === 1
                ? '<span class="badge bg-label-success">فعال</span>'
                : '<span class="badge bg-label-danger">غیرفعال</span>',
            'created_at' => e($this->formatDateTime($product->getRawOriginal('created_at') ?? $product->created_at)),
            'actions' => '<a href="' . $editUrl . '" style="font-size:20px;float:right;margin-left:5px;color:#04a9f5;display:inline-flex">'
                . \App\Support\UiIcon::html('fa-edit') . '</a>',
        ];

        $row = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            $row[] = $values[$column['key']] ?? '—';
        }

        return $row;
    }

    private function categoryTitle(Product $product, Collection $categoriesById): string
    {
        $parts = [];

        if ($product->parentCategory_id) {
            $parts[] = $categoriesById->get((int) $product->parentCategory_id);
        }

        if ($product->childCategory_id) {
            $parts[] = $categoriesById->get((int) $product->childCategory_id);
        }

        return collect($parts)->filter()->implode(' / ');
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

    private function formatPriceCell(mixed $price): string
    {
        $amount = (int) str_replace([',', ' '], '', (string) ($price ?: 0));

        if ($amount <= 0) {
            return '<span class="text-muted">0</span>';
        }

        return '<span class="text-end d-inline-block" dir="ltr">' . number_format($amount) . '</span>';
    }

    private function formatSaleFlag(int $status): string
    {
        return $status === 1
            ? '<span class="badge bg-label-success">فعال</span>'
            : '<span class="badge bg-label-secondary">غیرفعال</span>';
    }

    private function formatDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('Y/m/d H:i');
        } catch (\Throwable) {
            return '—';
        }
    }

    /**
     * @return array<int, array{key: string, label: string, description?: string, fixed?: bool, toggleable?: bool, sortable?: string, feature?: string, default?: bool}>
     */
    private function definitions(): array
    {
        return [
            ['key' => 'row', 'label' => '#', 'fixed' => true, 'toggleable' => false, 'sortable' => 'products.id'],
            ['key' => 'sku', 'label' => 'کد کالا', 'description' => 'شناسه یکتای محصول در پنل', 'sortable' => 'products.sku', 'default' => true],
            ['key' => 'title', 'label' => 'نام کالا', 'description' => 'عنوان اصلی و نام نمایشی محصول', 'fixed' => true, 'toggleable' => false, 'sortable' => 'products.title'],
            ['key' => 'display_name', 'label' => 'نام نمایشی', 'description' => 'عنوان تکمیلی روی فاکتور یا کاتالوگ', 'default' => false],
            ['key' => 'brand', 'label' => 'برند', 'description' => 'برند ثبت‌شده برای محصول', 'default' => false],
            ['key' => 'category', 'label' => 'دسته‌بندی', 'description' => 'دسته والد و فرعی محصول', 'default' => false],
            ['key' => 'store', 'label' => 'انبار', 'description' => 'انبار(های) مرتبط با کالا', 'feature' => 'feature_warehouse_management', 'default' => true],
            ['key' => 'organization', 'label' => 'واحد پخش', 'description' => 'واحد سازمانی مالک محصول', 'feature' => 'feature_branch_management', 'default' => true],
            ['key' => 'stock', 'label' => 'موجودی کالا', 'description' => 'موجودی فعلی بر اساس تنظیمات انبار', 'feature' => 'feature_warehouse_management', 'default' => true],
            ['key' => 'pr_unit', 'label' => 'واحد اصلی', 'description' => 'واحد شمارش پایه', 'sortable' => 'products.pr_unit', 'default' => true],
            ['key' => 'pr_sub_unit', 'label' => 'واحد فرعی', 'description' => 'واحد بسته‌بندی یا فرعی', 'sortable' => 'products.pr_sub_unit', 'default' => true],
            ['key' => 'pack_items', 'label' => 'تعداد در بسته', 'description' => 'تعداد واحد اصلی داخل هر بسته', 'sortable' => 'products.pack_items', 'default' => false],
            ['key' => 'price', 'label' => 'قیمت', 'description' => 'قیمت پایه فروش', 'sortable' => 'products.price', 'default' => true],
            ['key' => 'purchase_price', 'label' => 'قیمت خرید', 'description' => 'آخرین/پایه قیمت خرید', 'sortable' => 'products.purchase_price', 'default' => false],
            ['key' => 'cost_price', 'label' => 'بهای تمام‌شده', 'description' => 'هزینه تمام‌شده کالا', 'sortable' => 'products.cost_price', 'default' => false],
            ['key' => 'wholesale_price', 'label' => 'قیمت عمده', 'description' => 'قیمت فروش عمده', 'feature' => 'feature_multi_price', 'sortable' => 'products.wholesale_price', 'default' => false],
            ['key' => 'consumer_price', 'label' => 'قیمت مصرف‌کننده', 'description' => 'قیمت فروش خرده', 'feature' => 'feature_multi_price', 'sortable' => 'products.consumer_price', 'default' => false],
            ['key' => 'representative_price', 'label' => 'قیمت نماینده', 'description' => 'قیمت ویژه نمایندگان', 'feature' => 'feature_multi_price', 'sortable' => 'products.representative_price', 'default' => false],
            ['key' => 'product_type', 'label' => 'نوع محصول', 'description' => 'کالا، خدمت، مواد اولیه و …', 'sortable' => 'products.product_type', 'default' => false],
            ['key' => 'stock_tracking', 'label' => 'کنترل موجودی', 'description' => 'روش ردیابی موجودی کالا', 'feature' => 'feature_warehouse_management', 'default' => false],
            ['key' => 'item_sale', 'label' => 'فروش تکی', 'description' => 'امکان فروش به واحد اصلی', 'default' => false],
            ['key' => 'pack_sale', 'label' => 'فروش بسته‌ای', 'description' => 'امکان فروش به واحد فرعی/بسته', 'default' => false],
            ['key' => 'status', 'label' => 'وضعیت', 'fixed' => true, 'toggleable' => false, 'sortable' => 'products.isActive'],
            ['key' => 'created_at', 'label' => 'تاریخ ثبت', 'description' => 'زمان ایجاد محصول در سیستم', 'sortable' => 'products.created_at', 'default' => false],
            ['key' => 'actions', 'label' => 'عملیات', 'fixed' => true, 'toggleable' => false],
        ];
    }
}
